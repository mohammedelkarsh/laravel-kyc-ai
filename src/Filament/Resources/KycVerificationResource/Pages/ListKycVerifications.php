<?php

declare(strict_types=1);

namespace KycAi\Laravel\Filament\Resources\KycVerificationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use KycAi\Laravel\Filament\Resources\KycVerificationResource;

final class ListKycVerifications extends ListRecords
{
    protected static string $resource = KycVerificationResource::class;
}
