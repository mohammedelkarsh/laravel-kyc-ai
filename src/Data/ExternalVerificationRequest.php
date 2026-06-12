<?php

declare(strict_types=1);

namespace KycAi\Laravel\Data;

final class ExternalVerificationRequest
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        private readonly string $country,
        private readonly string $nationalId,
        private readonly array $context = [],
    ) {}

    public function country(): string
    {
        return $this->country;
    }

    public function nationalId(): string
    {
        return $this->nationalId;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
