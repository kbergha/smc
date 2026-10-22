<?php declare(strict_types = 1);

namespace Smc\Input;

use Smc\Database\Connection;

final class Validate
{
    public static function emailAddress(string $emailAddress): bool
    {
        // Remove any invalid characters.
        $processed = filter_var($emailAddress, FILTER_SANITIZE_EMAIL);

        // Sanitation failed.
        if (!$processed) {
            return false;
        }

        // Validation after sanitation.
        if (!filter_var($processed, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // One or more characters were removed by the filter.
        // Something fishy is going on.
        if ($processed !== $emailAddress) {
            return false;
        }

        // Get domain, and
        $domain = mb_substr($emailAddress, strpos($emailAddress, '@') + 1);
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return false;
        }

        // Note to future self:
        // Do not use getmxrr() / dns_get_mx() / checkdnsrr() to check for actual MX records
        // Why? The PHP doc for getmxrr() says:
        //
        // This function should not be used for the purposes of address verification.
        // Only the mailexchangers found in DNS are returned, however,
        // according to RFC 2821 when no mail exchangers are listed,
        // hostname itself should be used as the only mail exchanger with a priority of 0.

        // Note to future self:
        // This is probably why sending an actual email to the validated email address is the way to go if needed.

        return true;
    }

    public static function slug(string $slug, ?int $pageID): bool
    {
        // Check for existing usage.
        $pdo = Connection::getPdo();

        $statement = $pdo->prepare('SELECT id FROM pages WHERE slug = :slug LIMIT 1');
        if ($pageID !== null) {
            $statement = $pdo->prepare('SELECT id FROM pages WHERE slug = :slug and id != :id LIMIT 1');
            $statement->bindParam(':id', $pageID);
        }

        $statement->bindParam(':slug', $slug);
        $statement->execute();

        $result = $statement->fetch();
        unset($statement, $pdo);

        return !$result;
    }

}
