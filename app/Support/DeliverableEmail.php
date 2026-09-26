<?php

namespace App\Support;

/**
 * Tells us whether an address is a REAL mailbox we should try to email,
 * or a placeholder (used while the real email of a seeded account is still unknown).
 *
 * Placeholder examples: admin@infra-inv.local, supply@capstone-1.test, inspector@example.com
 */
class DeliverableEmail
{
    private const FAKE_SUFFIXES = ['.local', '.test', '.invalid', '.localhost', '.example'];
    private const FAKE_DOMAINS  = ['example.com', 'example.org', 'example.net'];

    public static function check(?string $email): bool
    {
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = strtolower(substr(strrchr($email, '@'), 1));

        if (in_array($domain, self::FAKE_DOMAINS, true)) {
            return false;
        }

        foreach (self::FAKE_SUFFIXES as $suffix) {
            if (str_ends_with($domain, $suffix)) {
                return false;
            }
        }

        return true;
    }
}