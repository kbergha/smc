<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Guards\LoginGuard;
use Smc\Guards\SessionGuard;
use Smc\Renderer\Renderer;

class DashboardController extends Controller
{
    /**
     * @inheritDoc
     */
    public static function allowedMethods(): array
    {
        return [
            'index' => ['GET'],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function requiredGuards(): array
    {
        return [
            '*' => [SessionGuard::class, LoginGuard::class],
        ];
    }

    /**
     * @noinspection PhpUnused
     */
    public function index(): void
    {
        new Renderer('@backend/dashboard.html.twig')->render();
    }
}
