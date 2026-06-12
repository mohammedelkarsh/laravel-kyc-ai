<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Jobs\ProcessKycDocument;
use KycAi\Laravel\Kyc;
use KycAi\Laravel\KycLevel;

final class ProcessKycDocumentJobTest extends TestCase
{
    protected function tearDown(): void
    {
        Kyc::resetFakeState();

        parent::tearDown();
    }

    public function test_job_runs_verification(): void
    {
        $job = new ProcessKycDocument([
            'national_id' => '1001244084',
            'country' => 'sa',
            'level' => KycLevel::Internal->value,
        ]);

        $result = $job->handle(app(\KycAi\Laravel\KycManager::class), app(\KycAi\Laravel\KycVerifier::class));

        $this->assertTrue($result->passed());
    }
}
