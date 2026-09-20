<?php declare(strict_types=1);

use Smc\Commands\Install;

require_once dirname(__DIR__) . '/bootstrap.php';

$install = new Install();

try {
    exit($install->execute() ? 0 : 1);
} catch (Throwable $e) {
    fwrite(STDERR, "Install failed: {$e->getMessage()}\n");
    exit(1);
}
