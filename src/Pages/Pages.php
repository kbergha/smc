<?php declare(strict_types=1);

namespace Smc\Pages;

use PDO;
use RuntimeException;
use Smc\Database\Connection;
use Smc\User\User;

// todo: class Pages implements \Iterator ?
class Pages
{
    protected ?PDO $pdo = null;
    protected string $table = 'pages';

    private function pdo(): PDO
    {
        return $this->pdo ??= Connection::getPdo();
    }

    // todo: array of Page
    public function getPages(int $limit = 10, int $offset = 0): ?array
    {
        $statement = $this->pdo()->prepare('SELECT id FROM '.$this->table.' LIMIT :limit OFFSET :offset');
        $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
        $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        // todo
        return [];
    }

    public function createNewPage(string $title, string $slug): Page
    {
        $page = new Page(null);
        $user = new User();
        $page->set($title, $slug, $user->loggedInUserId() ?? 0, null);

        if (!$page->save()) {
            throw new RuntimeException('Unable to create new page.');
        }

        return $page;

    }

    public function doesPageExist(int $pageID): bool
    {
        $page = new Page($pageID);
        return $page->load();
    }

}
