<?php declare(strict_types=1);

namespace Smc\Controllers;

use RuntimeException;
use Smc\Guards\LoginGuard;
use Smc\Guards\SessionGuard;
use Smc\Pages\Page;
use Smc\Pages\Pages;
use Smc\Renderer\Renderer;
use Smc\Router\RedirectException;

class PageController extends Controller
{
    /**
     * @inheritDoc
     */
    public static function allowedMethods(): array
    {
        return [
            'index' => ['GET'],
            'new' => ['GET', 'POST'],
            'page' => ['GET'],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function requiredGuards(): array
    {
        return [
            '*' => [SessionGuard::class, LoginGuard::class],
        ];
    }

    /**
     * @noinspection PhpUnused
     */
    public function index(): void
    {
        new Renderer('@backend/pages.html.twig')->render();
    }

    /**
     * @noinspection PhpUnused
     */
    public function page(int $id): void
    {
        $page = new Page($id);
        if (!$page->load()) {
            throw new RuntimeException('Page not found');
        }
        new Renderer('@backend/page.html.twig')->render(['page' => $page]);
    }

    /**
     * @noinspection PhpUnused
     */
    public function new(string $method): void
    {
        if ($method === 'POST') {
            $pages = new Pages();
            $page = $pages->createNewPage($_POST['title'], $_POST['slug']);
            throw new RedirectException('/page/'. $page->pageID .'/', 302);
        } else {
            new Renderer('@backend/pageNew.html.twig')->render();
        }
    }
}
