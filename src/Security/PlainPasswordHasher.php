<?php

namespace App\Security;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * Password hasher that doesn't hash passwords (plain text)
 * Used for compatibility with legacy noe2 database
 * TODO: Remove this after migration and re-hash all passwords
 */
class PlainPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return $plainPassword;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $hashedPassword === $plainPassword;
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
