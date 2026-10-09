<?php declare(strict_types=1);

namespace Smc\Twig;

use Smc\Pages\Page;
use Smc\Pages\Pages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PagesExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        // Define custom Twig functions
        // (see https://twig.symfony.com/doc/3.x/advanced.html#functions)
        return [
            new TwigFunction('pagesGetPages', [$this, 'getPages']),
        ];
    }

    /**
     * @return ?array<Page>
     */
    public function getPages(int $limit = 10, int $offset = 0): ?array
    {
        $pages = new Pages();
        return $pages->getPages();
    }
}
