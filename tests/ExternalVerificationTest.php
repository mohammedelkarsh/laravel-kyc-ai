<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Support\Facades\Http;
use KycAi\Laravel\External\ShuftiExternalVerifier;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\KycManager;

final class ExternalVerificationTest extends TestCase
{
    public function test_shufti_returns_not_configured_without_credentials(): void
    {
        $verifier = new ShuftiExternalVerifier([]);

        $result = $verifier->verify(new \KycAi\Laravel\Data\ExternalVerificationRequest('sa', '1001244084'));

        $this->assertFalse($result->passed());
        $this->assertSame('shufti', $result->provider());
        $this->assertSame('kyc.external.not_configured', $result->failureReason());
        $this->assertTrue($verifier->sendsDataExternally());
    }

    public function test_shufti_accepts_verification_response(): void
    {
        Http::fake([
            'https://api.shuftipro.com/status' => Http::response(['event' => 'verification.accepted']),
        ]);

        $verifier = new ShuftiExternalVerifier([
            'client_id' => 'id',
            'secret' => 'secret',
        ]);

        $result = $verifier->verify(new \KycAi\Laravel\Data\ExternalVerificationRequest('sa', '1001244084'));

        $this->assertTrue($result->passed());
    }

    public function test_shufti_handles_provider_error(): void
    {
        Http::fake([
            'https://api.shuftipro.com/status' => Http::response(['error' => 'bad'], 500),
        ]);

        $verifier = new ShuftiExternalVerifier([
            'client_id' => 'id',
            'secret' => 'secret',
        ]);

        $result = $verifier->verify(new \KycAi\Laravel\Data\ExternalVerificationRequest('sa', '1001244084'));

        $this->assertFalse($result->passed());
        $this->assertSame('kyc.external.provider_error', $result->failureReason());
    }

    public function test_full_level_with_shufti_not_configured_fails(): void
    {
        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.default' => 'shufti',
            'kyc.external_verification.drivers.shufti' => [],
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

    public function test_full_level_with_shufti_accepted_passes(): void
    {
        Http::fake([
            'https://api.shuftipro.com/status' => Http::response(['event' => 'verification.accepted']),
        ]);

        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.default' => 'shufti',
            'kyc.external_verification.drivers.shufti' => [
                'client_id' => 'id',
                'secret' => 'secret',
            ],
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

    private function refreshKycContainer(): void
    {
        $this->app->forgetInstance(KycManager::class);
        $this->app->forgetInstance(\KycAi\Laravel\KycVerifier::class);
        $this->app->forgetInstance(\KycAi\Laravel\Kyc::class);
    }

    public function test_verify_with_external_when_disabled_throws(): void
    {
        $this->expectException(\KycAi\Laravel\Exceptions\KycException::class);

        $this->kyc()->number('1001244084')
            ->country('sa')
            ->verifyWith('shufti')
            ->verify();
    }
}
