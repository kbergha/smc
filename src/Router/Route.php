<?php declare(strict_types=1);

namespace Smc\Router;

use Smc\Controllers\ControllerInterface;

final readonly class Route
{
    /**
     * @param class-string<ControllerInterface> $controller
     * @param string                            $action     Public, non-static method on $controller.
     * @param list<mixed>                       $arguments  Passed to the action. Empty for static routes.
     */
    public function __construct(
        public string $controller,
        public string $action,
        public array $arguments = [],
    ) {
    }

    public function __toString(): string
    {
        return "{$this->controller}::{$this->action}()";
    }
}
