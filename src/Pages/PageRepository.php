<?php declare(strict_types=1);

namespace Smc\Pages;

use PDO;
use Smc\Database\Connection;

final class PageRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::get();
    }

    public function findBySlug(string $slug): ?Page
    {
        $statement = $this->pdo->prepare(
            'SELECT id, title, slug, user_id FROM pages WHERE slug = :slug;'
        );
        $statement->execute(['slug' => $slug]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : Page::fromRow($row);
    }
}
