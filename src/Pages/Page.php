<?php declare(strict_types=1);

namespace Smc\Pages;

use PDO;
use Smc\Database\Connection;
use Smc\User\User;

class Page
{
    protected ?PDO $pdo = null;
    protected string $table = 'pages';

    public ?int $pageID = null {
        get {
            return $this->pageID;
        }
    }
    public string $title = '[title]';
    public string $slug = '[slug]';

    protected ?int $userID = null;
    protected ?string $content = null;

    private function pdo(): PDO
    {
        return $this->pdo ??= Connection::getPdo();
    }

    public function __construct(?int $pageID = null)
    {
        // todo: new syntax/structure for get/set?
        $this->pageID = $pageID;
    }

    public function set(string $title, string $slug, int $userID, ?string $content): void
    {
        // todo: clean / validate / sanitize
        $this->title = $title;
        $this->slug = $slug;
        $this->userID = $userID;
        $this->content = $content;
    }

    public function load(): bool
    {
        if ($this->pageID === null) {
            return false;
        }

        $result = $this->loadFromDb();

        if ($result === false) {
            return false;
        }

        $this->title = $result['title'];
        $this->slug = $result['slug'];
        $this->userID = $result['user_id'];
        $this->content = null;

        unset($result, $statement);

        return true;
    }

    public function save(): bool
    {
        // todo: validate slug.
        // todo: add content column
        if ($this->pageID === null) {
            // New page
            $statement = $this->pdo()->prepare('INSERT INTO ' . $this->table . ' (title, slug, user_id) VALUES (:title, :slug, :user_id)');
        } else {
            // Existing page
            $statement = $this->pdo()->prepare('UPDATE ' . $this->table . ' SET title = :title, slug = :slug, user_id = :user_id WHERE id = :id');
            $statement->bindValue(':id', $this->pageID, PDO::PARAM_INT);
        }

        $statement->bindValue(':title', $this->title);
        $statement->bindValue(':slug', $this->slug);
        $statement->bindValue(':user_id', $this->userID, PDO::PARAM_INT);

        $result = $statement->execute();

        if ($this->pageID === null) {
            // New page, get last insert id.
            $this->pageID = $this->pdo->lastInsertId() ? (int) $this->pdo->lastInsertId() : null;
        }

        return $result;
    }

    protected function loadFromDb()
    {
        $statement = $this->pdo()->prepare('SELECT * FROM '.$this->table.' WHERE id = :id');
        $id = $this->pageID; // Or else: indirect property modification
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
