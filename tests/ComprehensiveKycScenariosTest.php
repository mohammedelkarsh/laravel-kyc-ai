<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\KycLevel;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * End-to-end matrix across countries, levels, and outcomes.
 */
final class ComprehensiveKycScenariosTest extends TestCase
{
    #[DataProvider('standardDocumentScenarios')]
    public function test_standard_document_flow(
        string $country,
        string $filename,
        string $nationalId,
        bool $shouldPass,
    ): void {
        $this->kyc()->fake()->willExtractId($nationalId, 0.95);

        $result = $this->kyc()->document($this->tempFile($filename))
            ->country($country)
            ->level(KycLevel::Standard)
            ->extractWith('fake')
            ->verify();

        $this->assertSame($shouldPass, $result->passed());
        $this->assertSame($nationalId, $result->nationalId());
        $this->assertSame(KycLevel::Standard, $result->level());
        $this->assertNotNull($result->extraction());
        $this->assertNotNull($result->internal());
    }

    public function test_pipeline_clone_does_not_mutate_original(): void
    {
        $manager = app(\KycAi\Laravel\KycManager::class);
        $verifier = app(\KycAi\Laravel\KycVerifier::class);

        $base = $manager->pipeline($verifier)->country('sa');
        $withNumber = $base->number('1001244084');

        $result = $withNumber->level(KycLevel::Internal)->verify();

        $this->assertTrue($result->passed());
    }

    public function test_unsupported_country_on_extraction_throws_from_fake_ids(): void
    {
        $this->expectException(KycException::class);

        $this->kyc()->document($this->tempFile('x.jpg'))->country('xx')->verify();
    }

    public function test_invalid_document_type_throws(): void
    {
        $this->expectException(KycException::class);

        $this->kyc()->document(12345)->country('sa')->verify();
    }

    public function test_match_against_fails_when_values_differ(): void
    {
        $this->kyc()->fake()->willExtract(new \KycAi\Laravel\Data\ExtractedDocument(
            nationalId: '1001244084',
            confidence: 0.99,
            driver: 'fake',
        ));

        $result = $this->kyc()->document($this->tempFile('id.jpg'))
            ->country('sa')
            ->matchAgainst('1001244080')
            ->verify();

        $this->assertFalse($result->passed());
        $this->assertSame('kyc.match.mismatch', $result->failureReason());
    }

    public function test_result_json_shape_for_successful_standard_flow(): void
    {
        $this->kyc()->fake()->willExtractId('1001244084', 0.92);

        $array = $this->kyc()->document($this->tempFile('1001244084.jpg'))
            ->country('sa')
            ->verify()
            ->toArray();

        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('approved', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('extraction', $array);
        $this->assertArrayHasKey('internal', $array);
        $this->assertTrue($array['approved']);
    }

    public static function standardDocumentScenarios(): array
    {
        return [
            'sa_valid' => ['sa', 'id.jpg', '1001244084', true],
            'sa_invalid' => ['sa', 'id.jpg', '1001244080', false],
            'ae_valid' => ['ae', 'eid.jpg', '784199000000002', true],
            'ae_invalid' => ['ae', 'eid.jpg', '784199000000001', false],
            'eg_valid' => ['eg', 'nid.jpg', '29001011234564', true],
            'eg_invalid' => ['eg', 'nid.jpg', '29001011234560', false],
        ];
    }
}
