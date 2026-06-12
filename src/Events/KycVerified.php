<?php

declare(strict_types=1);

namespace KycAi\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use KycAi\Laravel\Results\KycResult;

final class KycVerified
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly KycResult $result,
    ) {}
}
