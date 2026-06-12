<?php

declare(strict_types=1);

namespace KycAi\Laravel;

enum KycLevel: string
{
    case Internal = 'internal';
    case Standard = 'standard';
    case Full = 'full';

    public function requiresExtraction(): bool
    {
        return $this !== self::Internal;
    }

    public function requiresExternal(): bool
    {
        return $this === self::Full;
    }
}
