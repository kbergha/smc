<?php declare(strict_types=1);

namespace Smc\Controllers;

use InvalidArgumentException;
use Smc\Router\Header;

class RedirectController extends Controller
{
    /**
     * Never reached through a route - the router dispatches this directly
     * So it is exempt from the method check and this list stays empty.
     */
    public static function allowedMethods(): array
    {
        return [];
    }

    /**
     * Sends the browser somewhere else. Never renders a body - the Location header is the whole response.
     *
     * @noinspection PhpUnused
     */
    public function redirect(int $code, string $location, string $method): void
    {
        // RFC 9110: after a POST, 303 is what tells the browser to re-issue the request as a GET.
        if ($code === 302 && $method === 'POST') {
            $code = 303;
        }

        if (!in_array($code, [301, 302, 303, 307, 308], true)) {
            throw new InvalidArgumentException("Not a redirect status: {$code}", 400);
        }

        Header::setStatusCode($code);
        Header::setHeader('Location', $location);

        // A cached "you are not logged in" served to a logged-in user is a bad day.
        Header::setNoCache();
    }
}
