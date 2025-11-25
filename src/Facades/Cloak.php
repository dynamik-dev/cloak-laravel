<?php

declare(strict_types=1);

namespace DynamikDev\Cloak\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string cloak(string $text, ?array $detectors = null)
 * @method static string uncloak(string $text)
 *
 * @see \DynamikDev\Cloak\Cloak
 */
class Cloak extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DynamikDev\Cloak\Cloak::class;
    }
}
