<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\KycLevel;
use PHPUnit\Framework\Attributes\DataProvider;

final class InternalVerificationScenariosTest extends TestCase
{
    #[DataProvider('validInternalCases')]
    public function test_valid_national_ids_pass_internal_level(string $country, string $input, string $normalized): void
    {
        $result = $this->kyc()->number($input)
            ->country($country)
            ->level(KycLevel::Internal)
            ->verify();

        $this->assertTrue($result->passed());
        $this->assertTrue($result->approved());
        $this->assertSame('passed', $result->status());
        $this->assertSame($normalized, $result->nationalId());
        $this->assertNull($result->extraction());
        $this->assertTrue($result->internal()?->passed());
    }

    #[DataProvider('invalidInternalCases')]
    public function test_invalid_national_ids_fail_internal_level(string $country, string $nationalId): void
    {
        $result = $this->kyc()->number($nationalId)
            ->country($country)
            ->level(KycLevel::Internal)
            ->verify();

        $this->assertFalse($result->passed());
        $this->assertSame('failed', $result->status());
        $this->assertFalse($result->internal()?->passed());
        $this->assertNotNull($result->failureReason());
    }

    #[DataProvider('validInternalCases')]
    public function test_match_against_succeeds_when_values_match(string $country, string $input, string $normalized): void
    {
        $result = $this->kyc()->number($input)
            ->country($country)
            ->level(KycLevel::Internal)
            ->matchAgainst($normalized)
            ->verify();

        $this->assertTrue($result->passed());
    }

    public function test_saudi_identity_type_meta_is_present(): void
    {
        $result = $this->kyc()->number('1001244084')
            ->country('sa')
            ->level(KycLevel::Internal)
            ->verify();

        $this->assertSame('citizen', $result->internal()?->metaValue('type'));
    }

    public function test_uae_formatted_meta_is_present(): void
    {
        $result = $this->kyc()->number('784199000000002')
            ->country('ae')
            ->level(KycLevel::Internal)
            ->verify();

        $this->assertSame('784-1990-0000000-2', $result->internal()?->metaValue('formatted'));
    }

    public function test_egypt_birth_date_meta_is_present(): void
    {
        $result = $this->kyc()->number('29001011234564')
            ->country('eg')
            ->level(KycLevel::Internal)
            ->verify();

        $this->assertSame('1990-01-01', $result->internal()?->metaValue('birth_date'));
    }

    public static function validInternalCases(): array
    {
        return [
            'sa_citizen' => ['sa', '1001244084', '1001244084'],
            'sa_with_dashes' => ['sa', '1-001-244-084', '1001244084'],
            'ae_strict' => ['ae', '784199000000002', '784199000000002'],
            'ae_with_dashes' => ['ae', '784-1990-0000000-2', '784199000000002'],
            'eg' => ['eg', '29001011234564', '29001011234564'],
            'eg_spaced' => ['eg', '2 900 101 123 456 4', '29001011234564'],
        ];
    }

    public static function invalidInternalCases(): array
    {
        return [
            'sa_bad_checksum' => ['sa', '1001244080'],
            'sa_wrong_length' => ['sa', '100124408'],
            'sa_wrong_prefix' => ['sa', '3001244084'],
            'ae_bad_luhn' => ['ae', '784199000000001'],
            'ae_wrong_prefix' => ['ae', '123199000000002'],
            'eg_bad_checksum' => ['eg', '29001011234560'],
            'eg_wrong_century' => ['eg', '10201011234567'],
        ];
    }
}
