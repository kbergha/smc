<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Smc\Http\MethodNotAllowedHandler;
use Smc\Http\NotFoundHandler;
use Smc\Router\MethodNotAllowedException;
use Smc\Router\NotFoundException;
use Smc\Router\Router;

try {
    new Router()->handle();
} catch (MethodNotAllowedException $e) {
    new MethodNotAllowedHandler()->handle($e);
} catch (NotFoundException $e) {
    new NotFoundHandler()->handle($e);
}
