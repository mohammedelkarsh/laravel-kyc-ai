<?php

declare(strict_types=1);

namespace KycAi\Laravel\Results;

final class ExternalVerificationResult
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        private readonly bool $passed,
        private readonly string $provider,
        private readonly ?string $ownerName = null,
        private readonly ?bool $ownerNameMatched = null,
        private readonly array $meta = [],
        private readonly ?string $failureReason = null,
    ) {}

    public function passed(): bool
    {
        return $this->passed;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function ownerName(): ?string
    {
        return $this->ownerName;
    }

    public function ownerNameMatched(): ?bool
    {
        return $this->ownerNameMatched;
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return $this->meta;
    }

    public function failureReason(): ?string
    {
        return $this->failureReason;
    }
}
