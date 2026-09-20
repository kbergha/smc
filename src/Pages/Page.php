<?php declare(strict_types=1);

namespace Smc\Pages;

final readonly class Page
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?int $userId,
    ) {
    }

    /**
     * @param array{id: int|string, title: string, slug: string, user_id: int|string|null} $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['title'],
            (string) $row['slug'],
            $row['user_id'] === null ? null : (int) $row['user_id'],
        );
    }
}
