<?php

declare(strict_types=1);

namespace KycAi\Laravel\Contracts;

use KycAi\Laravel\Results\InternalVerificationResult;

interface InternalVerifier
{
    public function verify(string $nationalId, ?string $matchAgainst = null): InternalVerificationResult;
}
