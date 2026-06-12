<?php

declare(strict_types=1);

namespace KycAi\Laravel\Verifiers;

use Validators\Core\ValidationResult;
use Validators\Sa\SaudiNationalId;

final class SaudiInternalVerifier extends AbstractInternalVerifier
{
    protected function validate(string $nationalId): ValidationResult
    {
        return SaudiNationalId::check($nationalId);
    }
}
