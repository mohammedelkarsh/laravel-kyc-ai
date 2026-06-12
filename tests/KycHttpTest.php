<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Http\UploadedFile;
use KycAi\Laravel\Kyc;

final class KycHttpTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('kyc.routes.api', true);
        $app['config']->set('kyc.routes.demo', true);
    }

    public function test_api_verify_with_national_id_only(): void
    {
        $response = $this->postJson('/api/kyc/verify', [
            'country' => 'sa',
            'national_id' => '1001244084',
        ]);

        $response->assertOk()
            ->assertJsonPath('passed', true)
            ->assertJsonPath('national_id', '1001244084')
            ->assertJsonPath('level', 'internal');
    }

    public function test_api_verify_with_document_and_fake_driver(): void
    {
        $this->kyc()->fake()->willExtractId('1001244084');

        $response = $this->post('/api/kyc/verify', [
            'country' => 'sa',
            'extraction_driver' => 'fake',
            'document' => UploadedFile::fake()->image('1001244084.jpg'),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('approved', true)
            ->assertJsonPath('extraction.driver', 'fake');
    }

    public function test_api_returns_422_for_invalid_id(): void
    {
        $response = $this->postJson('/api/kyc/verify', [
            'country' => 'sa',
            'national_id' => '1001244080',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('passed', false);
    }

    public function test_api_validates_country(): void
    {
        $response = $this->postJson('/api/kyc/verify', [
            'country' => 'us',
            'national_id' => '1001244084',
        ]);

        $response->assertUnprocessable();
    }

    public function test_demo_page_is_accessible(): void
    {
        $response = $this->get('/kyc/demo');

        $response->assertOk();
        $response->assertSee('laravel-kyc-ai Demo');
    }

    public function test_demo_form_submission_shows_result(): void
    {
        $this->kyc()->fake()->willExtractId('1001244084');

        $response = $this->post('/kyc/demo', [
            'country' => 'sa',
            'extraction_driver' => 'fake',
            'document' => UploadedFile::fake()->image('1001244084.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('kyc_result');

        /** @var array<string, mixed> $result */
        $result = session('kyc_result');

        $this->assertTrue($result['approved']);
        $this->assertSame('1001244084', $result['national_id']);
    }
}
