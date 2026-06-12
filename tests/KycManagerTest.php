<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\KycManager;
use KycAi\Laravel\Verifiers\InternalVerifierResolver;

final class KycManagerTest extends TestCase
{
    public function test_unknown_extraction_driver_throws(): void
    {
        $this->expectException(KycException::class);

        app(KycManager::class)->extractionDriver('unknown-driver');
    }

    public function test_unknown_external_driver_throws_when_enabled(): void
    {
        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.default' => 'unknown',
        ]);

        $manager = new KycManager(config('kyc'));

        $this->expectException(KycException::class);

        $manager->externalVerifier('unknown');
    }

    public function test_external_verifier_returns_null_when_disabled(): void
    {
        $this->assertNull(app(KycManager::class)->externalVerifier('shufti'));
    }

    public function test_unsupported_country_throws_from_resolver(): void
    {
        $this->expectException(KycException::class);

        (new InternalVerifierResolver)->resolve('us');
    }

    public function test_config_is_exposed(): void
    {
        $config = app(KycManager::class)->config();

        $this->assertArrayHasKey('extraction', $config);
        $this->assertArrayHasKey('audit', $config);
    }
}
