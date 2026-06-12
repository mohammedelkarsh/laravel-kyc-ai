<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use KycAi\Laravel\Data\ExtractedDocument;
use KycAi\Laravel\Drivers\OpenAiVisionDriver;
use KycAi\Laravel\Drivers\TesseractExtractionDriver;
use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\KycManager;
use KycAi\Laravel\Support\CountryFakeIds;
use KycAi\Laravel\Support\DocumentSource;

final class ExtractionDriversTest extends TestCase
{
    public function test_fake_driver_reads_id_from_filename_per_country(): void
    {
        $cases = [
            ['sa', '1001244084-front.jpg', '1001244084'],
            ['ae', '784199000000002-eid.png', '784199000000002'],
            ['eg', '29001011234564-id.jpg', '29001011234564'],
        ];

        foreach ($cases as [$country, $filename, $expectedId]) {
            $path = $this->tempFile($filename);
            $result = $this->kyc()->document($path)
                ->country($country)
                ->extractWith('fake')
                ->verify();

            $this->assertTrue($result->passed(), "Failed for {$country}");
            $this->assertSame($expectedId, $result->nationalId(), "Wrong ID for {$country}");
            $this->assertSame('fake', $result->extraction()?->driver());
        }
    }

    public function test_fake_driver_generates_valid_id_when_filename_has_no_digits(): void
    {
        foreach (['sa', 'ae', 'eg'] as $country) {
            $path = $this->tempFile("no-id-{$country}.jpg");
            $result = $this->kyc()->document($path)->country($country)->verify();

            $this->assertNotNull($result->nationalId());
            $this->assertTrue($result->passed(), "Generated ID invalid for {$country}");
        }
    }

    public function test_fake_state_overrides_extraction(): void
    {
        $this->kyc()->fake()->willExtract(new ExtractedDocument(
            nationalId: '1001244084',
            confidence: 0.99,
            driver: 'fake',
            fields: ['name_en' => 'Test User'],
            warnings: ['test_warning'],
        ));

        $result = $this->kyc()->document($this->tempFile('x.jpg'))->country('sa')->verify();

        $this->assertSame('Test User', $result->extraction()?->field('name_en'));
        $this->assertContains('test_warning', $result->warnings());
    }

    public function test_openai_driver_extracts_and_verifies(): void
    {
        config([
            'kyc.extraction.drivers.openai.api_key' => 'test-key',
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'national_id' => '1001244084',
                            'name_ar' => 'محمد',
                            'name_en' => 'Mohammed',
                            'birth_date' => '1990-01-01',
                            'expiry_date' => '2030-01-01',
                            'confidence' => 0.91,
                        ], JSON_THROW_ON_ERROR),
                    ],
                ]],
            ]),
        ]);

        $path = $this->tempFile('openai.jpg');
        $result = $this->kyc()->document($path)
            ->country('sa')
            ->extractWith('openai')
            ->verify();

        $this->assertTrue($result->passed());
        $this->assertSame('1001244084', $result->nationalId());
        $this->assertSame('openai', $result->extraction()?->driver());
        $this->assertContains('data_sent_for_extraction', $result->warnings());
        $this->assertSame('Mohammed', $result->extraction()?->field('name_en'));
    }

    public function test_openai_driver_requires_api_key(): void
    {
        config(['kyc.extraction.drivers.openai.api_key' => null]);

        $driver = new OpenAiVisionDriver(config('kyc.extraction.drivers.openai'));

        $this->expectException(KycException::class);

        $driver->extract(new \KycAi\Laravel\Data\ExtractionRequest(
            DocumentSource::fromMixed($this->tempFile('x.jpg')),
            'sa',
        ));
    }

    public function test_tesseract_driver_fails_when_binary_missing(): void
    {
        $driver = new TesseractExtractionDriver([
            'binary' => 'tesseract-binary-that-does-not-exist-xyz',
            'language' => 'eng',
            'timeout' => 5,
        ]);

        $this->expectException(KycException::class);
        $this->expectExceptionMessage('Tesseract OCR is unavailable');

        $driver->extract(new \KycAi\Laravel\Data\ExtractionRequest(
            DocumentSource::fromMixed($this->tempFile('scan.jpg')),
            'sa',
        ));
    }

    public function test_openai_driver_sends_data_externally(): void
    {
        $manager = app(KycManager::class);

        $this->assertTrue($manager->extractionDriver('openai')->sendsDataExternally());
        $this->assertFalse($manager->extractionDriver('fake')->sendsDataExternally());
        $this->assertFalse($manager->extractionDriver('tesseract')->sendsDataExternally());
    }

    public function test_country_fake_ids_from_filename_helpers(): void
    {
        $this->assertSame('1001244084', CountryFakeIds::fromFilename('id-1001244084.jpg', 'sa'));
        $this->assertSame('784199000000002', CountryFakeIds::fromFilename('784199000000002.png', 'ae'));
        $this->assertNull(CountryFakeIds::fromFilename('no-digits.jpg', 'sa'));
    }

    public function test_standard_level_requires_document(): void
    {
        $this->expectException(KycException::class);

        $this->kyc()->number('1001244084')
            ->country('sa')
            ->level(KycLevel::Standard)
            ->verify();
    }

    public function test_missing_national_id_after_empty_extraction_throws(): void
    {
        $this->kyc()->fake()->willExtract(new ExtractedDocument(
            nationalId: null,
            confidence: 0.1,
            driver: 'fake',
        ));

        $this->expectException(KycException::class);
        $this->expectExceptionMessage('No national ID');

        $this->kyc()->document($this->tempFile('empty.jpg'))->country('sa')->verify();
    }

    public function test_document_accepts_uploaded_file_instance(): void
    {
        $this->kyc()->fake()->willExtractId('1001244084');

        $file = UploadedFile::fake()->image('1001244084.jpg');
        $result = $this->kyc()->document($file)->country('sa')->verify();

        $this->assertTrue($result->passed());
    }
}
