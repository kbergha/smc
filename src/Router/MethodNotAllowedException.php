<?php declare(strict_types=1);

namespace Smc\Router;

use RuntimeException;

final class MethodNotAllowedException extends RuntimeException
{
    /** @param list<string> $allowed */
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly array $allowed,
    ) {
        parent::__construct("{$method} is not allowed for {$path}");
    }
}
