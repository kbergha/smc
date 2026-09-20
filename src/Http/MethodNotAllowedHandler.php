<?php declare(strict_types=1);

namespace Smc\Http;

use Smc\Router\MethodNotAllowedException;

final readonly class MethodNotAllowedHandler
{
    public function __construct(
        private string $title = 'Method not allowed',
        private bool $showDetails = true,
    ) {
    }

    public function handle(MethodNotAllowedException $exception): void
    {
        // OPTIONS is answered generically for every known path: the Allow header
        // is the whole response, so 204 (No Content) rather than 405.
        $isOptions = $exception->method === 'OPTIONS';

        if (!headers_sent()) {
            // RFC 9110 requires a 405 to advertise the methods that are supported.
            header('Allow: ' . implode(', ', $exception->allowed));
            http_response_code($isOptions ? 204 : 405);

            if (!$isOptions) {
                header('Content-Type: text/html; charset=utf-8');
            }
        }

        // A 204 must not carry a body.
        if ($isOptions) {
            return;
        }

        echo ErrorPage::render(405, $this->title, $this->detail($exception));
    }

    private function detail(MethodNotAllowedException $exception): string
    {
        if (!$this->showDetails) {
            return '';
        }

        $method = ErrorPage::escape($exception->method);
        $path = ErrorPage::escape($exception->path);
        $allowed = ErrorPage::escape(implode(', ', $exception->allowed));

        return "<p><code>{$method}</code> is not allowed for <code>{$path}</code>.</p>"
            . "<p>Try one of: <code>{$allowed}</code>.</p>";
    }
}
