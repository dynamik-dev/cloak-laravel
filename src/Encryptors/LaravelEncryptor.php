<?php

declare(strict_types=1);

namespace DynamikDev\Cloak\Laravel\Encryptors;

use DynamikDev\Cloak\Contracts\EncryptorInterface;
use Illuminate\Support\Facades\Crypt;

/**
 * Encryptor implementation using Laravel's Crypt facade.
 */
class LaravelEncryptor implements EncryptorInterface
{
    public function encrypt(string $value): string
    {
        return Crypt::encryptString($value);
    }

    public function decrypt(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }
}
