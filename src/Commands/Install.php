<?php declare(strict_types=1);

namespace Smc\Commands;

use Error;
use Exception;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use SQLite3;

class Install implements CommandInterface
{
    protected string $databaseFilePath;

    public function __construct(string $database = "smc") {
        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $database) !== 1) {
            throw new InvalidArgumentException(
                "Database must be a plain file name, got: {$database}"
            );
        }

        $this->databaseFilePath = dirname(__DIR__, 2)."/sqlite/".$database;
    }

    public function execute(): bool
    {
        if ($this->isInstalled() === false) {
            echo "One or more installation checks returned false, starting installation\n";
            echo "Preparing DB-file and applying installation migration\n";
            $this->prepareDbFile();
            $this->applyInstallMigration();
            echo "Application installed!\n";
        } else {
            echo "Application was already installed\n";
        }

        return true;
    }

    protected function isInstalled(): bool
    {
        if (!file_exists($this->databaseFilePath)) {
            echo "Install check 1 of 3: Database file {$this->databaseFilePath} does not exist.\n";
            return false;
        }
        echo "Install check 1 of 3: Database file {$this->databaseFilePath} already exists.\n";

        $pdo = new PDO(
            "sqlite:$this->databaseFilePath",
            options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        try {
            $result = $pdo->query("SELECT count(name) FROM sqlite_master WHERE type = 'table' AND name = 'migrations';");
            // @phpstan-ignore method.nonObject (ERRMODE_EXCEPTION makes a failed query throw, so query() cannot return false here - PHPStan does not read the attribute)
            if ((int) $result->fetchColumn() !== 1) {
                echo "Install check 2 of 3: Migrations table is missing\n";
                return false;
            }
            echo "Install check 2 of 3: Migrations table exists\n";

            $result = $pdo->query("SELECT id, applied_at FROM migrations WHERE migration_name = '0000_install';");
            // @phpstan-ignore method.nonObject (as above - ERRMODE_EXCEPTION, so false is not reachable)
            $installMigration = $result->fetch(PDO::FETCH_ASSOC);
            if ($installMigration === false) {
                echo "Install check 3 of 3: Migrations table exists, but install migration is not done\n";
                return false;
            }
            echo "Install check 3 of 3: Install migration with ID: {$installMigration['id']} has previously been done at {$installMigration['applied_at']} (UTC)\n";

        } catch (Error|Exception $e) {
            echo "Error / exception: ".$e->getMessage()."\n";
            return false;
        }

        echo "3 of 3 checks for installation met, installation is already done\n";
        return true;
    }

    protected function prepareDbFile(): void
    {
        if (!file_exists($this->databaseFilePath)) {
            echo "Touching and chmoding {$this->databaseFilePath}\n";
            touch($this->databaseFilePath);
            chmod($this->databaseFilePath, 0600);
        }
    }

    protected function applyInstallMigration(): void
    {
        // Read before opening the database, so an unreadable migration fails
        // before anything has been touched rather than mid-transaction.
        $migration = file_get_contents(dirname(__DIR__, 2) . "/migrations/0000_install.sql");

        if ($migration === false) {
            throw new RuntimeException("Could not read migrations/0000_install.sql");
        }

        $db = new SQLite3($this->databaseFilePath, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $db->enableExceptions(true);
        $db->exec("BEGIN TRANSACTION;");
        $db->exec($migration);
        $db->exec("INSERT INTO migrations (migration_name) VALUES ('0000_install');");
        $db->exec("COMMIT;");
        $db->close();
    }
}
