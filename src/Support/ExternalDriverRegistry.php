<?php

declare(strict_types=1);

namespace KycAi\Laravel\Support;

use KycAi\Laravel\Contracts\ExternalVerifier;
use KycAi\Laravel\Exceptions\KycException;

final class ExternalDriverRegistry
{
    /** @var array<string, callable(array<string, mixed>): ExternalVerifier> */
    private static array $drivers = [];

    /**
     * @param  callable(array<string, mixed>): ExternalVerifier  $factory
     */
    public static function register(string $name, callable $factory): void
    {
        self::$drivers[$name] = $factory;
    }

    public static function has(string $name): bool
    {
        return isset(self::$drivers[$name]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function resolve(string $name, array $config = []): ExternalVerifier
    {
        $factory = self::$drivers[$name] ?? null;

        if ($factory === null) {
            throw KycException::unknownExternalDriver($name);
        }

        return $factory($config);
    }

    public static function flush(): void
    {
        self::$drivers = [];
    }
}
