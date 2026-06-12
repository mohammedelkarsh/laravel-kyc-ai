<?php

declare(strict_types=1);

namespace KycAi\Laravel;

use Illuminate\Support\ServiceProvider;
use KycAi\Laravel\Audit\KycAuditRecorder;

final class KycServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kyc.php', 'kyc');

        $this->app->singleton(KycManager::class, function ($app): KycManager {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('kyc', []);

            return new KycManager($config);
        });

        $this->app->singleton(KycAuditRecorder::class, function ($app): KycAuditRecorder {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('kyc', []);

            return new KycAuditRecorder($config);
        });

        $this->app->singleton(KycVerifier::class, function ($app): KycVerifier {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('kyc', []);

            return new KycVerifier(
                manager: $app->make(KycManager::class),
                config: $config,
                events: $app->bound('events') ? $app['events'] : null,
                audit: $app->make(KycAuditRecorder::class),
            );
        });

        $this->app->singleton(Kyc::class, function ($app): Kyc {
            return Kyc::make(
                $app->make(KycManager::class),
                $app->make(KycVerifier::class),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/kyc.php' => config_path('kyc.php'),
            ], 'kyc-config');

            $this->publishes([
                __DIR__.'/../database/migrations/2024_01_01_000000_create_kyc_verifications_table.php' => database_path('migrations/2024_01_01_000000_create_kyc_verifications_table.php'),
            ], 'kyc-migrations');
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'kyc');

        if (config('kyc.routes.api', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        if (config('kyc.routes.demo', false)) {
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'kyc');
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }
}
