<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Guards\SessionGuard;

class LoginController extends Controller
{
    /**
     * @inheritDoc
     */
    public static function allowedMethods(): array
    {
        return [
            'loginForm' => ['GET'],
            'login' => ['POST'],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function requiredGuards(): array
    {
        return [
            '*' => SessionGuard::class,
        ];
    }

    /**
     * @noinspection PhpUnused
     */
    public function login(): void
    {
    }

    /**
     * @noinspection PhpUnused
     */
    public function loginForm(): void
    {
        echo "Hello world!";
    }
}
