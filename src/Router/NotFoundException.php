<?php declare(strict_types=1);

namespace Smc\Router;

use RuntimeException;

final class NotFoundException extends RuntimeException
{
    public function __construct(public readonly string $path)
    {
        $this->code = 404;
        parent::__construct("No route for {$path}");
    }
}
