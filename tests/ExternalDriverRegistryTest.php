<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Data\ExternalVerificationRequest;
use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\Support\ExternalDriverRegistry;
use KycAi\Laravel\Tests\Support\AcceptingExternalVerifier;

final class ExternalDriverRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ExternalDriverRegistry::flush();
    }

    protected function tearDown(): void
    {
        ExternalDriverRegistry::flush();

        parent::tearDown();
    }

    public function test_register_and_resolve_driver(): void
    {
        ExternalDriverRegistry::register('test-accept', fn (): AcceptingExternalVerifier => new AcceptingExternalVerifier);

        $result = ExternalDriverRegistry::resolve('test-accept')
            ->verify(new ExternalVerificationRequest('sa', '1001244084'));

        $this->assertTrue($result->passed());
        $this->assertSame('test-accept', $result->provider());
    }

    public function test_unknown_driver_throws(): void
    {
        $this->expectException(KycException::class);
        $this->expectExceptionMessage('shufti');

        ExternalDriverRegistry::resolve('shufti');
    }

    public function test_manager_resolves_registered_driver(): void
    {
        ExternalDriverRegistry::register('test-accept', fn (): AcceptingExternalVerifier => new AcceptingExternalVerifier);

        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.default' => 'test-accept',
        ]);

        $this->refreshKycContainer();

        $verifier = app(\KycAi\Laravel\KycManager::class)->externalVerifier('test-accept');

        $this->assertInstanceOf(AcceptingExternalVerifier::class, $verifier);
    }

    private function refreshKycContainer(): void
    {
        $this->app->forgetInstance(\KycAi\Laravel\KycManager::class);
        $this->app->forgetInstance(\KycAi\Laravel\KycVerifier::class);
        $this->app->forgetInstance(\KycAi\Laravel\Kyc::class);
    }
}
