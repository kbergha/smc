<?php declare(strict_types=1);

namespace Smc\Router;

use InvalidArgumentException;
use RuntimeException;

final class RedirectException extends RuntimeException
{
    public function __construct(public readonly string $location, int $code = 302)
    {
        // Only same-site absolute paths to prevent forwarding of user input an open redirect
        if (!str_starts_with($location, '/') || str_starts_with($location, '//')) {
            throw new InvalidArgumentException("Redirect location must be an absolute path: {$location}", 400);
        }

        parent::__construct("Redirecting to {$location}", $code);
    }
}
