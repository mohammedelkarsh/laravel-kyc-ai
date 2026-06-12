<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Data\ExtractedDocument;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\Results\ExternalVerificationResult;
use KycAi\Laravel\Results\InternalVerificationResult;
use KycAi\Laravel\Results\KycResult;

final class KycResultTest extends TestCase
{
    public function test_to_array_contains_expected_keys(): void
    {
        $result = new KycResult(
            passed: true,
            country: 'sa',
            level: KycLevel::Standard,
            extraction: new ExtractedDocument('1001244084', 0.9, 'fake', ['name_en' => 'Ali']),
            internal: new InternalVerificationResult(true, '1001244084', metadata: ['type' => 'citizen']),
            external: null,
        );

        $array = $result->toArray();

        $this->assertSame('passed', $array['status']);
        $this->assertTrue($array['approved']);
        $this->assertSame('1001244084', $array['national_id']);
        $this->assertSame('fake', $array['extraction']['driver']);
        $this->assertSame('citizen', $array['internal']['meta']['type']);
    }

    public function test_pending_review_status_and_message(): void
    {
        $result = new KycResult(
            passed: true,
            country: 'sa',
            level: KycLevel::Standard,
            extraction: new ExtractedDocument('1001244084', 0.4, 'fake'),
            internal: new InternalVerificationResult(true, '1001244084'),
            external: null,
            needsManualReview: true,
        );

        $this->assertSame('pending_review', $result->status());
        $this->assertFalse($result->approved());
        $this->assertTrue($result->needsManualReview());
        $this->assertSame(__('kyc::kyc.pending_review'), $result->userMessage());
    }

    public function test_failure_reason_from_internal_errors(): void
    {
        $result = new KycResult(
            passed: false,
            country: 'sa',
            level: KycLevel::Internal,
            extraction: null,
            internal: new InternalVerificationResult(false, '1001244080', errors: ['kyc.match.mismatch'], matchFailure: 'kyc.match.mismatch'),
            external: null,
            failureReason: 'kyc.match.mismatch',
        );

        $this->assertSame('kyc.match.mismatch', $result->failureReason());
        $this->assertSame(__('kyc::kyc.match.mismatch'), $result->userMessage());
    }

    public function test_external_failure_reason_is_used(): void
    {
        $result = new KycResult(
            passed: false,
            country: 'sa',
            level: KycLevel::Full,
            extraction: null,
            internal: new InternalVerificationResult(true, '1001244084'),
            external: new ExternalVerificationResult(false, 'shufti', failureReason: 'kyc.external.rejected'),
            failureReason: 'kyc.external.rejected',
        );

        $this->assertSame('kyc.external.rejected', $result->failureReason());
    }

    public function test_warnings_merge_extraction_warnings(): void
    {
        $result = new KycResult(
            passed: true,
            country: 'sa',
            level: KycLevel::Standard,
            extraction: new ExtractedDocument('1001244084', 0.9, 'fake', warnings: ['low_confidence']),
            internal: new InternalVerificationResult(true, '1001244084'),
            external: null,
            warnings: ['data_sent_for_extraction'],
        );

        $warnings = $result->warnings();

        $this->assertContains('low_confidence', $warnings);
        $this->assertContains('data_sent_for_extraction', $warnings);
    }
}
