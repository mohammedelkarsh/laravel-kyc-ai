<?php

declare(strict_types=1);

namespace KycAi\Laravel\Verifiers;

use Validators\Core\ValidationResult;
use Validators\Eg\EgyptianNationalId;

final class EgyptInternalVerifier extends AbstractInternalVerifier
{
    protected function validate(string $nationalId): ValidationResult
    {
        return EgyptianNationalId::check($nationalId);
    }
}
