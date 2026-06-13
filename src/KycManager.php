<?php

declare(strict_types=1);

namespace KycAi\Laravel;

use KycAi\Laravel\Contracts\ExtractionDriver;
use KycAi\Laravel\Contracts\ExternalVerifier;
use KycAi\Laravel\Drivers\FakeExtractionDriver;
use KycAi\Laravel\Drivers\OpenAiVisionDriver;
use KycAi\Laravel\Drivers\TesseractExtractionDriver;
use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\Support\ExternalDriverRegistry;
use KycAi\Laravel\Verifiers\NullExternalVerifier;

final class KycManager
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function pipeline(KycVerifier $verifier): KycPipeline
    {
        return new KycPipeline($this, $verifier);
    }

    public function extractionDriver(?string $name = null): ExtractionDriver
    {
        $name ??= (string) ($this->config['extraction']['default'] ?? 'fake');

        return match ($name) {
            'fake' => new FakeExtractionDriver,
            'openai' => new OpenAiVisionDriver($this->config['extraction']['drivers']['openai'] ?? []),
            'tesseract' => new TesseractExtractionDriver($this->config['extraction']['drivers']['tesseract'] ?? []),
            default => throw KycException::unknownExtractionDriver($name),
        };
    }

    public function externalVerifier(?string $name = null): ?ExternalVerifier
    {
        $external = $this->config['external_verification'] ?? [];

        if (! ($external['enabled'] ?? false)) {
            return null;
        }

        $name ??= $external['default'] ?? null;

        if ($name === null || $name === '') {
            return null;
        }

        $drivers = $this->config['external_verification']['drivers'] ?? [];

        return match ($name) {
            'null', 'none' => new NullExternalVerifier,
            default => ExternalDriverRegistry::resolve((string) $name, $drivers[$name] ?? []),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return $this->config;
    }
}
