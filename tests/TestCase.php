<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use KycAi\Laravel\Kyc;
use KycAi\Laravel\KycServiceProvider;
use KycAi\Laravel\Support\ExternalDriverRegistry;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [KycServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('kyc', require __DIR__.'/../config/kyc.php');
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('kyc.extraction.default', 'fake');
        $app['config']->set('kyc.external_verification.enabled', false);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Kyc::resetFakeState();
        ExternalDriverRegistry::flush();

        parent::tearDown();
    }

    protected function kyc(): Kyc
    {
        return app(Kyc::class);
    }

    protected function tempFile(string $name, string $contents = 'fake-image'): string
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('kyc_', true).'_'.$name;
        file_put_contents($path, $contents);

        return $path;
    }

    protected function createKycVerificationsTable(): void
    {
        if (Schema::hasTable('kyc_verifications')) {
            return;
        }

        Schema::create('kyc_verifications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('country', 2);
            $table->string('national_id', 32)->nullable();
            $table->string('level', 20);
            $table->string('status', 20)->index();
            $table->boolean('passed')->default(false);
            $table->decimal('confidence', 5, 4)->nullable();
            $table->string('extraction_driver', 50)->nullable();
            $table->json('warnings')->nullable();
            $table->string('failure_reason')->nullable();
            $table->json('extracted_fields')->nullable();
            $table->json('internal_meta')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();
        });
    }
}
