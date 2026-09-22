<?php declare(strict_types=1);

namespace Smc\Controllers;

class PaginationController implements ControllerInterface
{
    public static function allowedMethods(): array
    {
        return ['index' => ['GET']];
    }

    /**
     * @noinspection PhpUnused
     */
    public function index(int $offset = 0): void
    {
        echo "Pagination, offset {$offset}";
    }
}
