<?php

declare(strict_types=1);

namespace KycAi\Laravel\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use KycAi\Laravel\Filament\Resources\KycVerificationResource;

final class KycFilamentPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'kyc-ai-laravel';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            KycVerificationResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
