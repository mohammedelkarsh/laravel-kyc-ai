<?php

declare(strict_types=1);

namespace KycAi\Laravel;

use Illuminate\Contracts\Events\Dispatcher;
use KycAi\Laravel\Audit\KycAuditRecorder;
use KycAi\Laravel\Data\ExternalVerificationRequest;
use KycAi\Laravel\Data\ExtractionRequest;
use KycAi\Laravel\Events\KycFailed;
use KycAi\Laravel\Events\KycVerified;
use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\Results\KycResult;
use KycAi\Laravel\Support\DocumentSource;
use KycAi\Laravel\Verifiers\InternalVerifierResolver;

final class KycVerifier
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly KycManager $manager,
        private readonly array $config,
        private readonly ?Dispatcher $events = null,
        private readonly ?KycAuditRecorder $audit = null,
    ) {}

    public function verifyPipeline(KycPipeline $pipeline): KycResult
    {
        return $this->run($pipeline);
    }

    private function run(KycPipeline $pipeline): KycResult
    {
        $state = $pipeline->state();

        $level = $state['level'] ?? KycLevel::from((string) ($this->config['default_level'] ?? 'standard'));
        $warnings = [];
        $extraction = null;
        $document = $state['document'];

        if ($level->requiresExtraction()) {
            if ($document === null) {
                throw KycException::invalidDocument('A document is required for this verification level.');
            }

            $driver = $this->manager->extractionDriver($state['extraction_driver']);
            $extraction = $driver->extract(new ExtractionRequest($document, $state['country']));

            if ($driver->sendsDataExternally()) {
                $warnings[] = 'data_sent_for_extraction';
            }
        }

        $nationalId = $state['national_id'] ?? $extraction?->nationalId();

        if ($nationalId === null || $nationalId === '') {
            throw KycException::missingNationalId();
        }

        $internalVerifier = (new InternalVerifierResolver)->resolve($state['country']);
        $internal = $internalVerifier->verify($nationalId, $state['match_against']);

        $external = null;
        $externalRequested = $level->requiresExternal() || $state['external_driver'] !== null;

        if ($externalRequested) {
            $verifier = $this->manager->externalVerifier($state['external_driver']);

            if ($verifier === null) {
                throw KycException::externalVerificationDisabled();
            }

            $external = $verifier->verify(new ExternalVerificationRequest(
                country: $state['country'],
                nationalId: $internal->nationalId(),
                context: $extraction?->fields() ?? [],
                document: $document,
            ));

            if ($verifier->sendsDataExternally()) {
                $warnings[] = 'data_sent_for_external_verification';
            }
        }

        if ($extraction !== null) {
            $threshold = (float) ($this->config['confidence_threshold'] ?? 0.75);

            if ($extraction->confidence() < $threshold) {
                $warnings[] = 'below_confidence_threshold';
            }
        }

        $passed = $internal->passed()
            && ($external === null || $external->passed());

        $failureReason = null;

        if (! $internal->passed()) {
            $failureReason = $internal->matchFailure() ?? $internal->firstError();
        } elseif ($external !== null && ! $external->passed()) {
            $failureReason = $external->failureReason();
        }

        if ($state['delete_after_verify'] || ($this->config['delete_document_after_verify'] ?? false)) {
            $document?->delete();
        }

        $manualThreshold = (float) ($this->config['manual_review_below'] ?? 0.60);
        $confidence = $extraction?->confidence();
        $needsReview = $confidence !== null && $confidence < $manualThreshold;

        if ($needsReview) {
            $warnings[] = 'manual_review_recommended';
        }

        $result = new KycResult(
            passed: $passed,
            country: $state['country'],
            level: $level,
            extraction: $extraction,
            internal: $internal,
            external: $external,
            warnings: $warnings,
            failureReason: $failureReason,
            needsManualReview: $needsReview,
        );

        $this->audit?->record($result, $state['user_id'] ?? null);
        $this->dispatchEvents($result);

        return $result;
    }

    private function dispatchEvents(KycResult $result): void
    {
        if ($this->events === null) {
            return;
        }

        if ($result->approved()) {
            $this->events->dispatch(new KycVerified($result));

            return;
        }

        $this->events->dispatch(new KycFailed($result));
    }
}
