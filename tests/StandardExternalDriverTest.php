<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Support\Facades\Http;
use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\KycManager;

final class StandardExternalDriverTest extends TestCase
{
    public function test_standard_level_with_verify_with_shufti_when_enabled(): void
    {
        Http::fake([
            'https://api.shuftipro.com/status' => Http::response(['event' => 'verification.accepted']),
        ]);

        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.drivers.shufti' => [
                'client_id' => 'id',
                'secret' => 'secret',
            ],
        ]);

        $this->refreshKycContainer();

        $this->kyc()->fake()->willExtractId('1001244084');

        $result = $this->kyc()->document($this->tempFile('1001244084.jpg'))
            ->country('sa')
            ->level(KycLevel::Standard)
            ->verifyWith('shufti')
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

    public function test_shufti_rejected_event_fails_verification(): void
    {
        Http::fake([
            'https://api.shuftipro.com/status' => Http::response(['event' => 'verification.declined']),
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

        $this->assertFalse($result->passed());
        $this->assertSame('kyc.external.rejected', $result->failureReason());
    }

    private function refreshKycContainer(): void
    {
        $this->app->forgetInstance(KycManager::class);
        $this->app->forgetInstance(\KycAi\Laravel\KycVerifier::class);
        $this->app->forgetInstance(\KycAi\Laravel\Kyc::class);
    }
}
