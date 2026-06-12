<?php

declare(strict_types=1);

namespace KycAi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use KycAi\Laravel\Kyc as KycManagerEntry;
use KycAi\Laravel\KycPipeline;
use KycAi\Laravel\Support\Testing\FakeKycBuilder;

/**
 * @method static KycPipeline document(mixed $source)
 * @method static KycPipeline number(string $value)
 * @method static FakeKycBuilder fake()
 *
 * @see KycManagerEntry
 */
final class Kyc extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return KycManagerEntry::class;
    }
}
