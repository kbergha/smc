<?php declare(strict_types=1);

namespace Smc\Controllers;

class IndexController implements ControllerInterface
{
    public static function routes(): array
    {
        return [
            'GET' => [
                '/' => 'index',
            ],
        ];
    }

    public function index(): bool
    {
        echo "Hello world!";
        return true;
    }
}
