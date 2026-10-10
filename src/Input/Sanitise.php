<?php declare(strict_types = 1);

namespace Smc\Input;

final class Sanitise
{
    const array SLUG_CHARACTER_REPLACEMENT_MAP = [
        'æ' => 'ae', // LATIN SMALL LETTER AE (U+00E6)
        'ø' => 'oe', // LATIN SMALL LETTER O WITH STROKE (U+00F8)
        'å' => 'aa', // LATIN SMALL LETTER A WITH RING ABOVE (U+00E5)
        'å' => 'aa', // LATIN SMALL LETTER A (U+0061) + COMBINING RING ABOVE (U+030A)
    ];

    public static function string(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function slug(string $slug): ?string
    {
        $search = array_keys(self::SLUG_CHARACTER_REPLACEMENT_MAP);
        $replace = array_values(self::SLUG_CHARACTER_REPLACEMENT_MAP);

        $processed = mb_strtolower(trim($slug), 'UTF-8');
        $processed = preg_replace(
            array_map(fn($s) => '/' . preg_quote($s, '/') . '/u', $search), // i.e. "/æ/u"
            $replace,
            $processed
        );

        // Some sort of error from preg_replace.
        if ($processed === null) {
            return null;
        }

        // Remove anything not a to z, 0 to 9 and -
        return preg_replace('/[^a-z0-9-]+/', '-', $processed);
    }
}
