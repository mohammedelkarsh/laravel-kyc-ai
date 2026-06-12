<?php

declare(strict_types=1);

namespace KycAi\Laravel\Contracts;

use KycAi\Laravel\Data\ExternalVerificationRequest;
use KycAi\Laravel\Results\ExternalVerificationResult;

interface ExternalVerifier
{
    public function verify(ExternalVerificationRequest $request): ExternalVerificationResult;

    public function sendsDataExternally(): bool;
}
