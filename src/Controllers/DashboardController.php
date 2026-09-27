<?php declare(strict_types=1);

namespace Smc\Controllers;

use Smc\Guards\LoginGuard;
use Smc\Guards\SessionGuard;

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
        echo "Hello dashboard world!";
    }
}
