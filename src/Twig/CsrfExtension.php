<?php declare(strict_types=1);

namespace Smc\Twig;

use Smc\User\Csrf;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CsrfExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        // Define custom Twig functions
        // (see https://twig.symfony.com/doc/3.x/advanced.html#functions)
        return [
            new TwigFunction('csrfTokenValue', [$this, 'getCsrfTokenValue']),
            new TwigFunction('csrfTokenName', [$this, 'getCsrfTokenName']),

        ];
    }

    public function getCsrfTokenValue(): string
    {
        return Csrf::generateToken();
    }

    public function getCsrfTokenName(): string
    {
        return Csrf::getTokenName();
    }
}
