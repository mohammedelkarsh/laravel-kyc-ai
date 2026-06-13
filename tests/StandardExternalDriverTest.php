<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\Support\ExternalDriverRegistry;
use KycAi\Laravel\Tests\Support\AcceptingExternalVerifier;
use KycAi\Laravel\Tests\Support\RejectingExternalVerifier;

final class StandardExternalDriverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ExternalDriverRegistry::flush();
        ExternalDriverRegistry::register('test-accept', fn (): AcceptingExternalVerifier => new AcceptingExternalVerifier);
        ExternalDriverRegistry::register('test-reject', fn (): RejectingExternalVerifier => new RejectingExternalVerifier);
    }

    protected function tearDown(): void
    {
        ExternalDriverRegistry::flush();

        parent::tearDown();
    }

    public function test_standard_level_with_verify_with_external_when_enabled(): void
    {
        config([
            'kyc.external_verification.enabled' => true,
        ]);

        $this->refreshKycContainer();

        $this->kyc()->fake()->willExtractId('1001244084');

        $result = $this->kyc()->document($this->tempFile('1001244084.jpg'))
            ->country('sa')
            ->level(KycLevel::Standard)
            ->verifyWith('test-accept')
            ->verify();

        $this->assertTrue($result->passed());
        $this->assertTrue($result->external()?->passed());
        $this->assertContains('data_sent_for_external_verification', $result->warnings());
    }

    public function test_full_level_without_document_throws(): void
    {
        $this->expectException(KycException::class);

        $this->kyc()->number('1001244084')
            ->country('sa')
            ->level(KycLevel::Full)
            ->verify();
    }

    public function test_external_rejected_event_fails_verification(): void
    {
        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.default' => 'test-reject',
        ]);

        $this->refreshKycContainer();

        $this->kyc()->fake()->willExtractId('1001244084');

        $result = $this->kyc()->document($this->tempFile('1001244084.jpg'))
            ->country('sa')
            ->level(KycLevel::Full)
            ->verify();

        $this->assertFalse($result->passed());
        $this->assertSame('kyc.external.rejected', $result->failureReason());
    }

    private function refreshKycContainer(): void
    {
        $this->app->forgetInstance(\KycAi\Laravel\KycManager::class);
        $this->app->forgetInstance(\KycAi\Laravel\KycVerifier::class);
        $this->app->forgetInstance(\KycAi\Laravel\Kyc::class);
    }
}
