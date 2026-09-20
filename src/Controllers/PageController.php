<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Pages\Page;
use Smc\Pages\PageRepository;
use Smc\Router\NotFoundException;

readonly class PageController implements ControllerInterface
{
    public function __construct(
        private PageRepository $pages = new PageRepository(),
    ) {
    }

    /**
     * Pages are matched dynamically by slug, so there is no static path table.
     * The router reaches this controller as a fallback, not through routes().
     */
    public static function routes(): array
    {
        return [];
    }

    /**
     * Dispatched dynamically by Smc\Router\Router as the frontend fallback via Router::FRONTEND_FALLBACK_ACTION
     *
     * @noinspection PhpUnused
     */
    public function show(string $path): bool
    {
        $page = $this->pages->findBySlug(self::slug($path));

        if ($page === null) {
            // No static route and no page: this really is a 404.
            throw new NotFoundException($path);
        }

        echo $this->render($page);

        return true;
    }

    private static function slug(string $path): string
    {
        return trim($path, '/');
    }

    private function render(Page $page): string
    {
        $title = htmlspecialchars($page->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>{$title}</title>
            </head>
            <body>
                <h1>{$title}</h1>
            </body>
            </html>
            HTML;
    }
}
