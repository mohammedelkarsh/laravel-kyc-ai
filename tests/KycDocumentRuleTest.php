<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Http\UploadedFile;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\Rules\KycDocument;

final class KycDocumentRuleTest extends TestCase
{
    public function test_validation_rule_passes_with_fake_extraction(): void
    {
        $this->kyc()->fake()->willExtractId('1001244084');

        $file = UploadedFile::fake()->image('national-id.jpg');
        $rule = new KycDocument(country: 'sa', level: KycLevel::Standard);
        $failed = null;

        $rule->validate('id_front', $file, function (string $message) use (&$failed): void {
            $failed = $message;
        });

        $this->assertNull($failed);
        $this->assertTrue($rule->lastResult()?->passed());
    }

    public function test_validation_rule_fails_for_invalid_extracted_id(): void
    {
        $this->kyc()->fake()->willExtractId('1001244080');

        $rule = new KycDocument(country: 'sa', level: KycLevel::Standard);
        $failed = null;

        $rule->validate('id_front', UploadedFile::fake()->image('bad.jpg'), function (string $message) use (&$failed): void {
            $failed = $message;
        });

        $this->assertNotNull($failed);
        $this->assertFalse($rule->lastResult()?->passed());
    }

    public function test_validation_rule_match_field_enforced(): void
    {
        $this->kyc()->fake()->willExtractId('1001244084');

        $rule = (new KycDocument(country: 'sa', level: KycLevel::Standard, matchField: 'national_id'))
            ->setData(['national_id' => '1001244080']);

        $failed = null;

        $rule->validate('id_front', UploadedFile::fake()->image('id.jpg'), function (string $message) use (&$failed): void {
            $failed = $message;
        });

        $this->assertNotNull($failed);
        $this->assertSame('kyc.match.mismatch', $rule->lastResult()?->failureReason());
    }

    public function test_validation_rule_works_for_uae(): void
    {
        $this->kyc()->fake()->willExtractId('784199000000002');

        $rule = new KycDocument(country: 'ae', level: KycLevel::Standard);
        $failed = null;

        $rule->validate('id_front', UploadedFile::fake()->image('ae.jpg'), function (string $message) use (&$failed): void {
            $failed = $message;
        });

        $this->assertNull($failed);
        $this->assertSame('784199000000002', $rule->lastResult()?->nationalId());
    }
}
