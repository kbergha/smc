<?php declare(strict_types=1);

namespace Smc\Database;

use PDO;

final class Connection
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        return self::$pdo ??= self::create();
    }

    private static function create(): PDO
    {
        $pdo = new PDO(
            'sqlite:' . dirname(__DIR__, 2) . '/sqlite/smc',
            options: [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        );

        // SQLite defaults foreign key enforcement to off, and it is a per-connection
        // setting, so it has to be switched on here rather than in a migration.
        $pdo->exec('PRAGMA foreign_keys = ON;');

        return $pdo;
    }
}
