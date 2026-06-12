<?php

declare(strict_types=1);

namespace KycAi\Laravel;

use Illuminate\Foundation\Bus\PendingDispatch;
use KycAi\Laravel\Data\KycRequestData;
use KycAi\Laravel\Jobs\ProcessKycDocument;
use KycAi\Laravel\Results\KycResult;
use KycAi\Laravel\Support\DocumentSource;

final class KycPipeline
{
    private ?DocumentSource $document = null;

    private ?string $nationalId = null;

    private string $country = 'sa';

    private ?KycLevel $level = null;

    private ?string $extractionDriver = null;

    private ?string $externalDriver = null;

    private ?string $matchAgainst = null;

    private bool $deleteAfterVerify = false;

    private ?int $userId = null;

    public function __construct(
        private readonly KycManager $manager,
        private readonly KycVerifier $verifier,
    ) {}

    public function document(mixed $source): self
    {
        $clone = clone $this;
        $clone->document = DocumentSource::fromMixed($source);

        return $clone;
    }

    public function number(string $value): self
    {
        $clone = clone $this;
        $clone->nationalId = $value;

        return $clone;
    }

    public function country(string $country): self
    {
        $clone = clone $this;
        $clone->country = strtolower($country);

        return $clone;
    }

    public function level(KycLevel|string $level): self
    {
        $clone = clone $this;
        $clone->level = is_string($level) ? KycLevel::from($level) : $level;

        return $clone;
    }

    public function extractWith(string $driver): self
    {
        $clone = clone $this;
        $clone->extractionDriver = $driver;

        return $clone;
    }

    public function verifyWith(string $driver): self
    {
        $clone = clone $this;
        $clone->externalDriver = $driver;

        return $clone;
    }

    public function matchAgainst(string $value): self
    {
        $clone = clone $this;
        $clone->matchAgainst = $value;

        return $clone;
    }

    public function deleteAfterVerify(bool $delete = true): self
    {
        $clone = clone $this;
        $clone->deleteAfterVerify = $delete;

        return $clone;
    }

    public function forUser(int $userId): self
    {
        $clone = clone $this;
        $clone->userId = $userId;

        return $clone;
    }

    public function verify(): KycResult
    {
        return $this->verifier->verifyPipeline($this);
    }

    public function dispatch(): PendingDispatch
    {
        return ProcessKycDocument::dispatch($this->toRequestData()->toArray());
    }

    public function toRequestData(): KycRequestData
    {
        return new KycRequestData(
            documentPath: $this->document?->path(),
            nationalId: $this->nationalId,
            country: $this->country,
            level: $this->level?->value,
            extractionDriver: $this->extractionDriver,
            externalDriver: $this->externalDriver,
            matchAgainst: $this->matchAgainst,
            deleteAfterVerify: $this->deleteAfterVerify,
            userId: $this->userId,
        );
    }

    /**
     * @return array{
     *     document: ?DocumentSource,
     *     national_id: ?string,
     *     country: string,
     *     level: KycLevel,
     *     extraction_driver: ?string,
     *     external_driver: ?string,
     *     match_against: ?string,
     *     delete_after_verify: bool,
     *     user_id: ?int
     * }
     */
    public function state(): array
    {
        $config = $this->manager->config();

        return [
            'document' => $this->document,
            'national_id' => $this->nationalId,
            'country' => $this->country,
            'level' => $this->level ?? KycLevel::from((string) ($config['default_level'] ?? 'standard')),
            'extraction_driver' => $this->extractionDriver,
            'external_driver' => $this->externalDriver,
            'match_against' => $this->matchAgainst,
            'delete_after_verify' => $this->deleteAfterVerify,
            'user_id' => $this->userId,
        ];
    }
}
