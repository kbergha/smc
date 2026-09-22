<?php declare(strict_types=1);

namespace Smc\Router;

use Smc\Controllers\ControllerInterface;
use Smc\Controllers\ErrorController;
use Smc\Controllers\IndexController;
use Smc\Controllers\OptionsController;
use Smc\Controllers\PageController;
use Smc\Controllers\PaginationController;

class Router
{
    /**
     * Segments that take a slug in the segment directly after them, so that '/tag/php/' becomes '/tag/{slug}/'
     * while an ordinary page path such as '/about/' is left for the PageController to resolve.
     *
     * @var list<string>
     */
    private const array SLUG_PREFIXES = [];

    public function __construct()
    {

    }

    public function handle(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = self::normalizePath(
            parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
        );

        [$pattern, $captures] = self::extractCaptures($path);
        // $captures can contain more than one item, map to parameters where relevant.

        try {
            // Try known frontend paths
            $controller = match ($pattern) {
                '/' => [
                    'class' => IndexController::class,
                    'action' => 'index',
                    'parameters' => [],
                ],
                '/page/{number}/' => [
                    'class' => PaginationController::class,
                    'action' => 'index',
                    'parameters' => ['offset' => $captures[0]],
                ],

                default => null,
            };

            // Try known backend paths
            if ($controller === null && str_starts_with($path, '/smc/')) {
                // default => null,
                // @todo:
            }

            // Try article / slug (will throw NotFoundException if no article is found)
            if ($controller === null) {
                $controller = [
                    'class' => PageController::class,
                    'action' => 'show',
                    'parameters' => [
                        'path' => $path,
                    ]
                ];
            }

            // OPTIONS is answered generically: the Allow header is the whole response, so the resolved action never runs.
            //
            // This does not verify that the resource exists. Every unmatched path resolves to PageController,
            // so OPTIONS on an unknown slug answers 204 where GET would answer 404. Accepted for now...
            if ($method === 'OPTIONS') {
                self::invoke([
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

            // Invoking inside the try is what lets the catches below see a NotFoundException thrown by the controller itself,
            // such as PageController failing to find an article with the path / slug.
            self::invoke($controller);

            return;

        } catch (NotFoundException $e) {
            $controller = [
                'class' => ErrorController::class,
                'action' => 'show',
                'parameters' => [
                    'code' => 404, // Move to exception.
                    'path' => $path,
                    'message' => $e->getMessage(),
                ]
            ];
        } catch (MethodNotAllowedException $e) {
            $controller = [
                'class' => ErrorController::class,
                'action' => 'show',
                'parameters' => [
                    'code' => 405, // Move to exception.
                    'path' => $path,
                    'message' => $e->getMessage(),
                    'allowed' => $e->allowed,
                ]
            ];
        }

        // Invoke the error controller from the catch statements above.
        // It is dispatched directly so assertMethodAllowed() does not apply to it.
        self::invoke($controller);
    }

    /**
     * @param array{class: class-string<ControllerInterface>, action: string, parameters: array} $controller
     */
    private static function invoke(array $controller): void
    {
        new $controller['class']()->{$controller['action']}(...$controller['parameters']);
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
     * Everything an action accepts: what it declares, plus what the protocol
     * implies. Also used to build the Allow header for 405 and OPTIONS.
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
     *   '/page/12/'        -> ['/page/{number}/',            [12]]
     *   '/tag/php/'        -> ['/tag/{slug}/',               ['php']]    (with 'tag' in SLUG_PREFIXES)
     *   '/some/123/path/'  -> ['/some/{number}/path/',       [123]]      ('path' is not after a prefix, so it stays literal)
     *   '/tag/php/page/2/' -> ['/tag/{slug}/page/{number}/', ['php', 2]] (with 'tag' in SLUG_PREFIXES)
     *   '/about/'          -> ['/about/',                    []]
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

                continue;
            }

            // A slug is not, so it is only recognised where a prefix says one
            // belongs. Otherwise, every path would become '/{slug}/'.
            if ($index > 0 && in_array($original[$index - 1], self::SLUG_PREFIXES, true)) {
                $captures[] = $segment;
                $segments[$index] = '{slug}';
            }
        }

        return ['/' . implode('/', $segments) . '/', $captures];
    }

    protected static function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        $path = "/{$path}/";

        return preg_replace('#/{2,}#', '/', $path);
    }
}
