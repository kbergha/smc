<?php declare(strict_types=1);

namespace Smc\Renderer;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;

class Renderer
{
    private TemplateWrapper $template;

    public function __construct(string $templateToRender)
    {
        try {
            $loader = new FilesystemLoader();
            $loader->addPath(dirname(__DIR__, 2).'/templates/backend/', 'backend');

            // todo: options, like cache.
            $twig = new Environment($loader);
            //$twig->addExtension(new Extension());

            $this->template = $twig->load($templateToRender);
        } catch (LoaderError|SyntaxError|RuntimeError $e) {
            throw new RendererException($e->getMessage(), 500, $e);
        }
    }

    public function render(): void
    {
        // todo: tidy, men med html5
        echo $this->template->render();
    }
}
