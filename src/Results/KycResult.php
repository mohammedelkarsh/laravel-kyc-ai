<?php

declare(strict_types=1);

namespace KycAi\Laravel\Results;

use Illuminate\Contracts\Support\Arrayable;
use KycAi\Laravel\Data\ExtractedDocument;
use KycAi\Laravel\KycLevel;

/**
 * @implements Arrayable<string, mixed>
 */
final class KycResult implements Arrayable
{
    /**
     * @param  list<string>  $warnings
     */
    public function __construct(
        private readonly bool $passed,
        private readonly string $country,
        private readonly KycLevel $level,
        private readonly ?ExtractedDocument $extraction,
        private readonly ?InternalVerificationResult $internal,
        private readonly ?ExternalVerificationResult $external,
        private readonly array $warnings = [],
        private readonly ?string $failureReason = null,
        private readonly bool $needsManualReview = false,
    ) {}

    public function passed(): bool
    {
        return $this->passed;
    }

    public function approved(): bool
    {
        return $this->passed && ! $this->needsManualReview;
    }

    public function needsManualReview(): bool
    {
        return $this->needsManualReview;
    }

    public function status(): string
    {
        if ($this->needsManualReview) {
            return 'pending_review';
        }

        return $this->passed ? 'passed' : 'failed';
    }

    public function country(): string
    {
        return $this->country;
    }

    public function level(): KycLevel
    {
        return $this->level;
    }

    public function extraction(): ?ExtractedDocument
    {
        return $this->extraction;
    }

    public function internal(): ?InternalVerificationResult
    {
        return $this->internal;
    }

    public function external(): ?ExternalVerificationResult
    {
        return $this->external;
    }

    public function nationalId(): ?string
    {
        return $this->internal?->nationalId()
            ?? $this->extraction?->nationalId();
    }

    public function confidence(): ?float
    {
        return $this->extraction?->confidence();
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return array_values(array_unique([
            ...$this->warnings,
            ...($this->extraction?->warnings() ?? []),
        ]));
    }

    public function failureReason(): ?string
    {
        return $this->failureReason
            ?? $this->internal?->firstError()
            ?? $this->external?->failureReason();
    }

    public function userMessage(): string
    {
        if ($this->needsManualReview) {
            return __('kyc::kyc.pending_review');
        }

        $reason = $this->failureReason();

        if ($reason === null && $this->passed) {
            return __('kyc::kyc.passed');
        }

        if ($reason !== null && str_starts_with($reason, 'kyc.')) {
            return __('kyc::kyc.'.substr($reason, 4));
        }

        return $reason ?? __('kyc::kyc.failed');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status(),
            'approved' => $this->approved(),
            'passed' => $this->passed(),
            'needs_manual_review' => $this->needsManualReview(),
            'country' => $this->country,
            'level' => $this->level->value,
            'national_id' => $this->nationalId(),
            'confidence' => $this->confidence(),
            'warnings' => $this->warnings(),
            'failure_reason' => $this->failureReason(),
            'message' => $this->userMessage(),
            'extraction' => $this->extraction ? [
                'driver' => $this->extraction->driver(),
                'fields' => $this->extraction->fields(),
            ] : null,
            'internal' => $this->internal ? [
                'passed' => $this->internal->passed(),
                'meta' => $this->internal->meta(),
            ] : null,
        ];
    }
}
