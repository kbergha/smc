<?php declare(strict_types = 1);

namespace Smc\Input;

use RuntimeException;
use Transliterator;

final class Sanitise
{

    // Not really needed due to Twig autoescaping?
    public static function string(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function slug(string $slug): string
    {
        // https://bartvanraaij.dev/2020-10-17-converting-utf8-strings-to-ascii-using-icu-transliterator/
        // https://unicode-org.github.io/icu/userguide/transforms/general/#overview
        $rules = [
            ':: Any-Latin', // Any script to Latin
            ':: NFKD', // Split characters into base letters and combining marks. Normalization Form Compatibility Decomposition
            ':: [:Nonspacing Mark:] Remove', // Remove combining marks (accents and diacritics)
            ':: Latin-ASCII', // Latin script to ASCII
            ':: Lower()', // To lower case
            "[^a-z0-9]+ > '-'", // Any run of non-matching characters is replaced by one dash, e.g: "bob !" -> "bob-"
        ];
        $transliterator = Transliterator::createFromRules(implode(';', $rules) . ';');

        if ($transliterator === null) {
            throw new RuntimeException('Transliterator rules not valid', 500);
        }

        $slug = $transliterator->transliterate($slug);

        if ($slug === false) {
            throw new RuntimeException('Transliterator failed', 500);
        }

        // Remove any leading and trailing dash, e.g: "-bob-" -> "bob"
        return trim($slug, '-');
    }
}
