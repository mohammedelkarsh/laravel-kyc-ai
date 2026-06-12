<?php

declare(strict_types=1);

namespace KycAi\Laravel\Results;

final class InternalVerificationResult
{
    /**
     * @param  list<string>  $errors
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private readonly bool $passed,
        private readonly string $nationalId,
        private readonly array $errors = [],
        private readonly array $metadata = [],
        private readonly ?string $matchFailure = null,
    ) {}

    public function passed(): bool
    {
        return $this->passed;
    }

    public function nationalId(): string
    {
        return $this->nationalId;
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return $this->metadata;
    }

    public function metaValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function matchFailure(): ?string
    {
        return $this->matchFailure;
    }
}
