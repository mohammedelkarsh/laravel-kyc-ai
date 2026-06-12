<?php

declare(strict_types=1);

namespace KycAi\Laravel\Data;

use KycAi\Laravel\Support\DocumentSource;

final class ExtractionRequest
{
    public function __construct(
        private readonly DocumentSource $document,
        private readonly string $country,
    ) {}

    public function document(): DocumentSource
    {
        return $this->document;
    }

    public function country(): string
    {
        return $this->country;
    }
}
