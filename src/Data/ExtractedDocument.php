<?php

declare(strict_types=1);

namespace KycAi\Laravel\Data;

final class ExtractedDocument
{
    /**
     * @param  array<string, mixed>  $fields
     * @param  list<string>  $warnings
     */
    public function __construct(
        private readonly ?string $nationalId,
        private readonly float $confidence,
        private readonly string $driver,
        private readonly array $fields = [],
        private readonly array $warnings = [],
    ) {}

    public function nationalId(): ?string
    {
        return $this->nationalId;
    }

    public function confidence(): float
    {
        return $this->confidence;
    }

    public function driver(): string
    {
        return $this->driver;
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function field(string $key, mixed $default = null): mixed
    {
        return $this->fields[$key] ?? $default;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
