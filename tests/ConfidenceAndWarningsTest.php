<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Data\ExtractedDocument;

final class ConfidenceAndWarningsTest extends TestCase
{
    public function test_below_confidence_threshold_adds_warning(): void
    {
        config(['kyc.confidence_threshold' => 0.80]);

        $this->kyc()->fake()->willExtract(new ExtractedDocument(
            nationalId: '1001244084',
            confidence: 0.70,
            driver: 'fake',
        ));

        $result = $this->kyc()->document($this->tempFile('warn.jpg'))->country('sa')->verify();

        $this->assertContains('below_confidence_threshold', $result->warnings());
        $this->assertTrue($result->passed());
        $this->assertTrue($result->approved());
    }

    public function test_manual_review_threshold_marks_pending_review(): void
    {
        config(['kyc.manual_review_below' => 0.60]);

        $this->kyc()->fake()->willExtractId('1001244084', 0.55);

        $result = $this->kyc()->document($this->tempFile('review.jpg'))->country('sa')->verify();

        $this->assertTrue($result->passed());
        $this->assertFalse($result->approved());
        $this->assertContains('manual_review_recommended', $result->warnings());
    }

    public function test_delete_after_verify_removes_temp_file(): void
    {
        $path = $this->tempFile('delete-me.jpg');
        $this->assertFileExists($path);

        $this->kyc()->fake()->willExtractId('1001244084');

        $this->kyc()->document($path)->country('sa')->deleteAfterVerify()->verify();

        $this->assertFileDoesNotExist($path);
    }

    public function test_global_delete_after_verify_config(): void
    {
        config(['kyc.delete_document_after_verify' => true]);

        $path = $this->tempFile('delete-config.jpg');
        $this->kyc()->fake()->willExtractId('1001244084');

        $this->kyc()->document($path)->country('sa')->verify();

        $this->assertFileDoesNotExist($path);
    }
}
