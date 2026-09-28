<?php declare(strict_types=1);

namespace Smc\Router;

use Smc\Controllers\ControllerInterface;
use Smc\Controllers\DashboardController;
use Smc\Controllers\ErrorController;
use Smc\Controllers\LoginController;
use Smc\Controllers\OptionsController;
use Smc\Controllers\RedirectController;
use Smc\Guards\GuardException;
use Smc\Renderer\RendererException;

class Router
{
    public function __construct()
    {

    }

    public function handle(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = self::normalizePath(
            parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'
        );

        [$pattern, $captures] = self::extractCaptures($path);
        // $captures can contain more than one item, map to controller parameters where relevant.

        try {
            $controller = match ($pattern) {
                '/' => [
                    'class' => LoginController::class,
                    'action' => 'loginForm',
                    'parameters' => [],
                ],
                '/login/' => [
                    'class' => LoginController::class,
                    'action' => 'login',
                    'parameters' => [],
                ],
                '/dashboard/' => [
                    'class' => DashboardController::class,
                    'action' => 'index',
                    'parameters' => [],
                ],
                default => throw new NotFoundException($path),
            };

            // OPTIONS is answered generically: the Allow header is the whole response, so the resolved action never runs.
            //
            // This does not verify that the resource exists. Every unmatched path resolves to PageController,
            // so OPTIONS on an unknown slug / path answers 204 where GET would answer 404. Accepted for now...
            if ($method === 'OPTIONS') {
                self::dispatch([
                    'class' => OptionsController::class,
                    'action' => 'show',
                    'parameters' => [
                        'allowed' => self::allowedMethodsFor($controller['class'], $controller['action']),
                    ],
                ]);

                return;
            }

            // Make sure the current method is allowed for the current action.
            self::assertMethodAllowed($controller['class'], $controller['action'], $method, $path);

            // Dispatching / invoking inside the try is what lets the catches below see a NotFoundException thrown by the controller itself.
            self::dispatch($controller);

            return;
        } catch (RedirectException $e) {
            self::dispatch([
                'class' => RedirectController::class,
                'action' => 'redirect',
                'parameters' => [
                    'location' => $e->location,
                    'code' => $e->getCode(),
                    'method' => $method,
                ],
            ]);
            return;
        } catch (NotFoundException|GuardException $e) {
            $controller = [
                'class' => ErrorController::class,
                'action' => 'show',
                'parameters' => [
                    'code' => $e->getCode(),
                    'path' => $path,
                    'message' => $e->getMessage(),
                ]
            ];
        } catch (MethodNotAllowedException $e) {
            $controller = [
                'class' => ErrorController::class,
                'action' => 'show',
                'parameters' => [
                    'code' => $e->getCode(),
                    'path' => $path,
                    'message' => $e->getMessage(),
                    'allowed' => $e->allowed,
                ]
            ];
        }

        // Invoke / dispatch the error controller from the catch statements above.
        // It is dispatched directly so assertMethodAllowed() does not apply to it.
        self::dispatch($controller);
    }

    /**
     * Hands a resolved route to its controller.
     *
     * @param array{class: class-string<ControllerInterface>, action: string, parameters: array<string, mixed>} $controller
     */
    private static function dispatch(array $controller): void
    {
        new $controller['class']()->dispatch($controller['action'], $controller['parameters']);
    }

    /**
     * Rejects a request whose method the target action does not accept.
     *
     * HEAD and OPTIONS are derived here rather than declared per controller,
     * since they are protocol rules identical for every action.
     *
     * @param class-string<ControllerInterface> $class
     */
    private static function assertMethodAllowed(string $class, string $action, string $method, string $path): void
    {
        $allowed = self::allowedMethodsFor($class, $action);

        if (!in_array($method, $allowed, true)) {
            throw new MethodNotAllowedException($path, $method, $allowed);
        }
    }

    /**
     * Everything an action accepts: what it declares, plus what the protocol implies.
     * Also used to build the Allow header for 405 and OPTIONS.
     *
     * @param  class-string<ControllerInterface> $class
     * @return list<string>
     */
    private static function allowedMethodsFor(string $class, string $action): array
    {
        // An action absent from allowedMethods() accepts nothing.
        $allowed = $class::allowedMethods()[$action] ?? [];

        if ($allowed === []) {
            return [];
        }

        // RFC 9110: supporting GET means supporting HEAD on the same resource.
        if (in_array('GET', $allowed, true) && !in_array('HEAD', $allowed, true)) {
            $allowed[] = 'HEAD';
        }

        $allowed[] = 'OPTIONS';
        sort($allowed);

        return $allowed;
    }

    /**
     * Rewrites dynamic segments to placeholders so a path can still be matched
     * as a literal string, and returns the values that were replaced.
     *
     *   '/page/12/'        -> ['/page/{number}/',       [12]]
     *   '/some/123/path/'  -> ['/some/{number}/path/',  [123]]
     *   '/dashboard/'      -> ['/dashboard/',           []]
     *
     * Only digits are dynamic. Text segments stay literal, since the backend
     * addresses everything by id and nothing resolves a slug any more.
     *
     * Captures are positional and in path order: the matching route decides what
     * each one means.
     *
     * @return array{string, list<int|string>}
     */
    protected static function extractCaptures(string $path): array
    {
        if ($path === '/') {
            return ['/', []];
        }

        $original = explode('/', trim($path, '/'));
        $segments = $original;
        $captures = [];

        foreach ($original as $index => $segment) {
            // Digits are a closed character class, so a number is recognisable
            // without knowing anything about the routes.
            if (ctype_digit($segment)) {
                $captures[] = (int) $segment;
                $segments[$index] = '{number}';
            }
        }

        return ['/' . implode('/', $segments) . '/', $captures];
    }

    protected static function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        $path = "/{$path}/";

        // Null means PCRE itself failed; the uncollapsed path is still a usable answer.
        return preg_replace('#/{2,}#', '/', $path) ?? $path;
    }
}
