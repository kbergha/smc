<?php declare(strict_types=1);

namespace Smc\Router;

use InvalidArgumentException;
use Smc\Controllers\ControllerInterface;
use Smc\Controllers\IndexController;
use Smc\Controllers\PageController;

class Router
{
    /**
     * Consulted only when no static frontend route matches, so a static path
     * always wins over a page slug.
     */
    private const string FRONTEND_FALLBACK = PageController::class;
    private const string FRONTEND_FALLBACK_ACTION = 'show';

    /** @var list<string> Methods the fallback may answer. Pages are read-only. */
    private const array FALLBACK_METHODS = ['GET', 'HEAD'];

    /** @var list<class-string<ControllerInterface>> */
    private const array FRONTEND_CONTROLLERS = [
        IndexController::class,
    ];

    /** @var list<class-string<ControllerInterface>> */
    private const array SMC_CONTROLLERS = [];

    /** @var array<string, array<string, Route>> */
    protected array $frontendRoutes;

    /** @var array<string, array<string, Route>> */
    protected array $smcRoutes;

    public function __construct()
    {
        $this->frontendRoutes = $this->collect(self::FRONTEND_CONTROLLERS);
        $this->smcRoutes = $this->collect(self::SMC_CONTROLLERS);
    }

    public function handle(): bool
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = self::normalizePath(
            parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
        );

        $isSmc = str_starts_with($path, '/smc/');
        $routes = $isSmc ? $this->smcRoutes : $this->frontendRoutes;

        $route = $this->matchWithFallback($routes, $method, $path, $isSmc);

        $class = $route->controller;
        $action = $route->action;

        return new $class()->$action(...$route->arguments);
    }

    /**
     * @param array<string, array<string, Route>> $routes
     */
    private function matchWithFallback(array $routes, string $method, string $path, bool $isSmc): Route
    {
        try {
            return $this->match($routes, $method, $path);
        } catch (NotFoundException $exception) {
            // A path claimed by a static route never reaches the fallback, and an
            // unsupported method surfaces as a 405 rather than falling through.
            if ($isSmc || !in_array($method, self::FALLBACK_METHODS, true)) {
                throw $exception;
            }

            // The fallback decides for itself whether the path exists, and throws
            // NotFoundException in turn when it does not.
            return new Route(self::FRONTEND_FALLBACK, self::FRONTEND_FALLBACK_ACTION, [$path]);
        }
    }

    /**
     * @param  list<class-string<ControllerInterface>> $controllers
     * @return array<string, array<string, Route>>
     */
    private function collect(array $controllers): array
    {
        $routes = [];

        foreach ($controllers as $controller) {
            foreach ($controller::routes() as $method => $actions) {
                $method = strtoupper($method);

                foreach ($actions as $path => $action) {
                    $normalized = self::normalizePath($path);

                    if (!method_exists($controller, $action)) {
                        throw new InvalidArgumentException(
                            "{$method} {$normalized} points at {$controller}::{$action}(), which does not exist."
                        );
                    }

                    if (isset($routes[$method][$normalized])) {
                        throw new InvalidArgumentException(
                            "{$method} {$normalized} is claimed by both {$routes[$method][$normalized]} and {$controller}::{$action}()."
                        );
                    }

                    $routes[$method][$normalized] = new Route($controller, $action);
                }
            }
        }

        return $routes;
    }

    /**
     * @param array<string, array<string, Route>> $routes
     */
    private function match(array $routes, string $method, string $path): Route
    {
        // RFC 9110: a server supporting GET must support HEAD on the same resource.
        $lookup = $method === 'HEAD' ? 'GET' : $method;

        if (isset($routes[$lookup][$path])) {
            return $routes[$lookup][$path];
        }

        $allowed = self::allowedMethods($routes, $path);

        if ($allowed !== []) {
            throw new MethodNotAllowedException($path, $method, $allowed);
        }

        throw new NotFoundException($path);
    }

    /**
     * @param  array<string, array<string, Route>> $routes
     * @return list<string>
     */
    private static function allowedMethods(array $routes, string $path): array
    {
        $allowed = [];

        foreach ($routes as $method => $paths) {
            if (isset($paths[$path])) {
                $allowed[] = $method;
            }
        }

        // An unknown path supports nothing, and must stay a 404 rather than
        // picking up the implicit methods below and becoming a 405.
        if ($allowed === []) {
            return [];
        }

        if (in_array('GET', $allowed, true) && !in_array('HEAD', $allowed, true)) {
            $allowed[] = 'HEAD';
        }

        // The front controller answers OPTIONS generically for every known path.
        if (!in_array('OPTIONS', $allowed, true)) {
            $allowed[] = 'OPTIONS';
        }

        sort($allowed);

        return $allowed;
    }

    protected static function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        $path = "/{$path}/";

        return preg_replace('#/{2,}#', '/', $path);
    }
}
