<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\KycLevel;
use KycAi\Laravel\Support\ExternalDriverRegistry;
use KycAi\Laravel\Tests\Support\AcceptingExternalVerifier;
use KycAi\Laravel\Tests\Support\NotConfiguredExternalVerifier;

final class ExternalVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ExternalDriverRegistry::flush();
        ExternalDriverRegistry::register('test-accept', fn (): AcceptingExternalVerifier => new AcceptingExternalVerifier);
        ExternalDriverRegistry::register('test-not-configured', fn (): NotConfiguredExternalVerifier => new NotConfiguredExternalVerifier);
    }

    protected function tearDown(): void
    {
        ExternalDriverRegistry::flush();

        parent::tearDown();
    }

    public function test_full_level_with_external_not_configured_fails(): void
    {
        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.default' => 'test-not-configured',
        ]);

        $this->refreshKycContainer();

        $this->kyc()->fake()->willExtractId('1001244084');

        $result = $this->kyc()->document($this->tempFile('1001244084.jpg'))
            ->country('sa')
            ->level(KycLevel::Full)
            ->verify();

        $this->assertFalse($result->passed());
        $this->assertSame('kyc.external.not_configured', $result->failureReason());
        $this->assertContains('data_sent_for_external_verification', $result->warnings());
    }

    public function test_full_level_with_external_accepted_passes(): void
    {
        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.default' => 'test-accept',
        ]);

        $this->refreshKycContainer();

        $this->kyc()->fake()->willExtractId('1001244084');

        $result = $this->kyc()->document($this->tempFile('1001244084.jpg'))
            ->country('sa')
            ->level(KycLevel::Full)
            ->verify();

        $this->assertTrue($result->passed());
        $this->assertTrue($result->external()?->passed());
    }

    public function test_verify_with_external_when_disabled_throws(): void
    {
        $this->expectException(\KycAi\Laravel\Exceptions\KycException::class);

        $this->kyc()->number('1001244084')
            ->country('sa')
            ->verifyWith('test-accept')
            ->verify();
    }

    private function refreshKycContainer(): void
    {
        $this->app->forgetInstance(\KycAi\Laravel\KycManager::class);
        $this->app->forgetInstance(\KycAi\Laravel\KycVerifier::class);
        $this->app->forgetInstance(\KycAi\Laravel\Kyc::class);
    }
}
