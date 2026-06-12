<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Support\Facades\Event;
use KycAi\Laravel\Events\KycFailed;
use KycAi\Laravel\Events\KycVerified;
use KycAi\Laravel\KycLevel;

final class KycEventsTest extends TestCase
{
    public function test_kyc_verified_event_is_dispatched_on_success(): void
    {
        Event::fake([KycVerified::class, KycFailed::class]);

        $this->kyc()->number('1001244084')
            ->country('sa')
            ->level(KycLevel::Internal)
            ->verify();

        Event::assertDispatched(KycVerified::class, function (KycVerified $event): bool {
            return $event->result->nationalId() === '1001244084';
        });

        Event::assertNotDispatched(KycFailed::class);
    }

    public function test_kyc_failed_event_is_dispatched_on_invalid_id(): void
    {
        Event::fake([KycVerified::class, KycFailed::class]);

        $this->kyc()->number('1001244080')
            ->country('sa')
            ->level(KycLevel::Internal)
            ->verify();

        Event::assertDispatched(KycFailed::class);
        Event::assertNotDispatched(KycVerified::class);
    }

    public function test_kyc_failed_event_is_dispatched_for_manual_review(): void
    {
        Event::fake([KycVerified::class, KycFailed::class]);

        $this->kyc()->fake()->willExtractId('1001244084', 0.45);

        $this->kyc()->document($this->tempFile('low.jpg'))
            ->country('sa')
            ->verify();

        Event::assertDispatched(KycFailed::class, function (KycFailed $event): bool {
            return $event->result->needsManualReview();
        });
    }
}
