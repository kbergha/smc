<?php declare(strict_types=1);

namespace Smc\Pages;

/**
 * Converts between the HTTP representation of a page address and the slug as it is stored in the database.
 */
final class Slug
{
    public static function fromPath(string $path): string
    {
        return trim($path, '/');
    }

    public static function toPath(string $slug): string
    {
        return '/' . trim($slug, '/') . '/';
    }

    // Future:
    // - validation / keyword restrictions. Tie into router and checks when saving/updating article slug.
    // - slugify from string / title.
}
