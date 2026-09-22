<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Smc\Router\Router;

try {
    new Router()->handle();
} catch (Exception $e) {
    echo "It's dead Jim!<br>";
    echo $e->getMessage();
}
