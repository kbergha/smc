<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Pages\Page;
use Smc\Pages\PageRepository;
use Smc\Pages\Slug;
use Smc\Router\NotFoundException;

readonly class PageController implements ControllerInterface
{
    public function __construct(
        private PageRepository $pages = new PageRepository(),
    ) {
    }

    public static function allowedMethods(): array
    {
        return ['show' => ['GET']];
    }

    /**
     * @noinspection PhpUnused
     */
    public function show(string $path): void
    {
        $page = $this->pages->findBySlug(Slug::fromPath($path));

        if ($page === null) {
            // No article found. This is last in the route lookup, so this is a 404.
            throw new NotFoundException($path);
        }

        echo $this->render($page);
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
