<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Audit\KycAuditRecorder;
use KycAi\Laravel\Kyc;
use KycAi\Laravel\KycManager;
use KycAi\Laravel\KycVerifier;

final class PackageBindingsTest extends TestCase
{
    public function test_services_are_registered_as_singletons(): void
    {
        $this->assertSame(app(Kyc::class), app(Kyc::class));
        $this->assertSame(app(KycManager::class), app(KycManager::class));
        $this->assertSame(app(KycVerifier::class), app(KycVerifier::class));
        $this->assertSame(app(KycAuditRecorder::class), app(KycAuditRecorder::class));
    }

    public function test_config_is_merged(): void
    {
        $this->assertIsArray(config('kyc.extraction.drivers'));
        $this->assertArrayHasKey('fake', config('kyc.extraction.drivers'));
        $this->assertArrayHasKey('openai', config('kyc.extraction.drivers'));
        $this->assertArrayHasKey('tesseract', config('kyc.extraction.drivers'));
    }

    public function test_translations_are_loaded(): void
    {
        $this->assertSame('Identity verification passed.', __('kyc::kyc.passed'));
        $this->assertNotEmpty(__('kyc::kyc.pending_review'));
    }
}
