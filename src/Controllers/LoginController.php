<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Guards\CsrfGuard;
use Smc\Guards\SessionGuard;
use Smc\Renderer\Renderer;
use Smc\User\User;

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
            'login' => CsrfGuard::class,
        ];
    }

    /**
     * @noinspection PhpUnused
     */
    public function login(): void
    {
        $user = new User();
        $user->login($_POST['username'] ?? null, $_POST['password'] ?? null);
    }

    /**
     * @noinspection PhpUnused
     */
    public function loginForm(): void
    {
        new Renderer('@backend/loginForm.html.twig')->render();
    }
}
