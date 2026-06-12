<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Support\Facades\Bus;
use KycAi\Laravel\Facades\Kyc as KycFacade;
use KycAi\Laravel\Jobs\ProcessKycDocument;
use KycAi\Laravel\Kyc;
use KycAi\Laravel\KycLevel;

final class KycFacadeAndDispatchTest extends TestCase
{
    public function test_facade_resolves_to_entry_class(): void
    {
        $this->assertInstanceOf(Kyc::class, KycFacade::getFacadeRoot());
    }

    public function test_pipeline_entry_point_matches_document_helper(): void
    {
        $pipeline = $this->kyc()->pipeline()->country('sa')->number('1001244084');
        $result = $pipeline->level(KycLevel::Internal)->verify();

        $this->assertTrue($result->passed());
    }

    public function test_dispatch_pushes_job_to_queue(): void
    {
        Bus::fake();

        $this->kyc()->number('1001244084')
            ->country('sa')
            ->level(KycLevel::Internal)
            ->dispatch();

        Bus::assertDispatched(ProcessKycDocument::class, function (ProcessKycDocument $job): bool {
            return $job->payload['national_id'] === '1001244084'
                && $job->payload['country'] === 'sa';
        });
    }

    public function test_reset_fake_state_clears_overrides(): void
    {
        $this->kyc()->fake()->willExtractId('1001244084');

        Kyc::resetFakeState();

        $path = $this->tempFile('no-digits.jpg');
        $result = $this->kyc()->document($path)->country('sa')->verify();

        $this->assertNotSame('1001244084', $result->nationalId());
    }
}
