<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\KycLevel;
use KycAi\Laravel\Models\KycVerification;

final class KycAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createKycVerificationsTable();
        config(['kyc.audit.enabled' => true]);
    }

    public function test_audit_record_is_created_on_verify(): void
    {
        $result = $this->kyc()->number('1001244084')
            ->country('sa')
            ->level(KycLevel::Internal)
            ->forUser(42)
            ->verify();

        $record = KycVerification::query()->first();

        $this->assertNotNull($record);
        $this->assertSame('1001244084', $record->national_id);
        $this->assertSame('sa', $record->country);
        $this->assertSame('passed', $record->status);
        $this->assertTrue($record->passed);
        $this->assertSame(42, $record->user_id);
        $this->assertSame($result->level()->value, $record->level);
    }

    public function test_audit_stores_pending_review_status(): void
    {
        $this->kyc()->fake()->willExtractId('1001244084', 0.45);

        $this->kyc()->document($this->tempFile('review.jpg'))
            ->country('sa')
            ->forUser(7)
            ->verify();

        $record = KycVerification::query()->first();

        $this->assertSame('pending_review', $record->status);
        $this->assertSame(0.45, (float) $record->confidence);
    }

    public function test_audit_is_skipped_when_disabled(): void
    {
        config(['kyc.audit.enabled' => false]);

        $this->kyc()->number('1001244084')->country('sa')->level(KycLevel::Internal)->verify();

        $this->assertSame(0, KycVerification::query()->count());
    }

    public function test_model_mark_reviewed_updates_status(): void
    {
        $record = KycVerification::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'country' => 'sa',
            'national_id' => '1001244084',
            'level' => 'standard',
            'status' => 'pending_review',
            'passed' => true,
        ]);

        $record->markReviewed(99, true);

        $record->refresh();

        $this->assertSame('passed', $record->status);
        $this->assertTrue($record->passed);
        $this->assertSame(99, $record->reviewed_by);
        $this->assertNotNull($record->reviewed_at);
    }

    public function test_pending_review_scope(): void
    {
        KycVerification::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'country' => 'sa',
            'level' => 'standard',
            'status' => 'pending_review',
            'passed' => false,
        ]);

        KycVerification::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'country' => 'sa',
            'level' => 'standard',
            'status' => 'passed',
            'passed' => true,
        ]);

        $this->assertSame(1, KycVerification::query()->pendingReview()->count());
    }
}
