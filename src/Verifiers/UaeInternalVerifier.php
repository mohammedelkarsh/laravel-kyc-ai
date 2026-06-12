<?php

declare(strict_types=1);

namespace KycAi\Laravel\Verifiers;

use Validators\Ae\EmiratesId;
use Validators\Core\ValidationResult;

final class UaeInternalVerifier extends AbstractInternalVerifier
{
    protected function validate(string $nationalId): ValidationResult
    {
        return EmiratesId::check($nationalId);
    }
}
