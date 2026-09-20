<?php declare(strict_types=1);

namespace Smc\Http;

use Smc\Router\NotFoundException;

final readonly class NotFoundHandler
{
    public function __construct(
        private string $title = 'Page not found',
        private bool $showPath = true,
    ) {
    }

    public function handle(NotFoundException $exception): void
    {
        // A controller may already have written output before failing. Sending
        // headers then would emit a warning and change nothing.
        if (!headers_sent()) {
            http_response_code(404);
            header('Content-Type: text/html; charset=utf-8');
        }

        $detail = $this->showPath
            ? '<p>There is nothing at <code>' . ErrorPage::escape($exception->path) . '</code>.</p>'
            : '';

        echo ErrorPage::render(404, $this->title, $detail);
    }
}
