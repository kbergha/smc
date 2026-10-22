<?php

namespace Smc\Twig;

use Smc\User\Session;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlashMessageExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        // Define custom Twig functions
        // (see https://twig.symfony.com/doc/3.x/advanced.html#functions)
        return [
            new TwigFunction('flashGetMessage', [$this, 'getMessage']),
            new TwigFunction('flashDeleteMessage', [$this, 'deleteMessage']),
        ];
    }

    public function getMessage(): ?string
    {
        return Session::getFlashMessage();
    }

    public function deleteMessage(): void
    {
        Session::deleteFlashMessage();
    }
}
