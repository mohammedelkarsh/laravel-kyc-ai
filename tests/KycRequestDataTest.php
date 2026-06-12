<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Data\KycRequestData;
use KycAi\Laravel\Jobs\ProcessKycDocument;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\KycManager;
use KycAi\Laravel\KycVerifier;

final class KycRequestDataTest extends TestCase
{
    public function test_to_array_and_from_array_roundtrip(): void
    {
        $original = new KycRequestData(
            documentPath: '/tmp/id.jpg',
            nationalId: '1001244084',
            country: 'sa',
            level: 'standard',
            extractionDriver: 'fake',
            externalDriver: null,
            matchAgainst: '1001244084',
            deleteAfterVerify: true,
            userId: 5,
        );

        $restored = KycRequestData::fromArray($original->toArray());

        $this->assertSame($original->toArray(), $restored->toArray());
    }

    public function test_to_pipeline_builds_verifiable_request(): void
    {
        $data = new KycRequestData(
            nationalId: '1001244084',
            country: 'sa',
            level: KycLevel::Internal->value,
        );

        $result = $data->toPipeline(app(KycManager::class), app(KycVerifier::class))->verify();

        $this->assertTrue($result->passed());
    }

    public function test_job_payload_executes_same_as_pipeline(): void
    {
        $payload = (new KycRequestData(
            nationalId: '29001011234564',
            country: 'eg',
            level: KycLevel::Internal->value,
        ))->toArray();

        $job = new ProcessKycDocument($payload);
        $result = $job->handle(app(KycManager::class), app(KycVerifier::class));

        $this->assertTrue($result->passed());
        $this->assertSame('eg', $result->country());
    }

    public function test_pipeline_to_request_data_matches_dispatch_payload(): void
    {
        $data = $this->kyc()->number('1001244084')
            ->country('sa')
            ->level(KycLevel::Internal)
            ->forUser(3)
            ->toRequestData()
            ->toArray();

        $this->assertSame('1001244084', $data['national_id']);
        $this->assertSame('sa', $data['country']);
        $this->assertSame('internal', $data['level']);
        $this->assertSame(3, $data['user_id']);
    }
}
