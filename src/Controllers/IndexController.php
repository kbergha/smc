<?php declare(strict_types=1);

namespace Smc\Controllers;

class IndexController implements ControllerInterface
{
    public static function allowedMethods(): array
    {
        return ['index' => ['GET']];
    }

    /**
     * @noinspection PhpUnused
     */
    public function index(): void
    {
        echo "Hello world!";
    }
}
