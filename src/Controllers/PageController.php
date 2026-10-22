<?php declare(strict_types=1);

namespace Smc\Controllers;

use RuntimeException;
use Smc\Guards\CsrfGuard;
use Smc\Guards\LoginGuard;
use Smc\Guards\SessionGuard;
use Smc\Pages\Page;
use Smc\Pages\Pages;
use Smc\Renderer\Renderer;
use Smc\Router\RedirectException;
use Smc\User\Session;
use Smc\User\User;

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
            'page' => ['GET', 'POST'],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function requiredGuards(): array
    {
        return [
            '*' => [SessionGuard::class, LoginGuard::class],
            // @todo: but only for post requests.
            //'new' => [CsrfGuard::class],
            //'page' => [CsrfGuard::class],
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
    public function page(int $id, string $method): void
    {
        $page = new Page($id);

        if ($method === 'POST') {
            // Update page

            $page->set(
                $_POST['title'],
                $_POST['slug'],
                new User()->loggedInUserId() ?? 0,
                null,
            );

            if ($page->save() === false) {
                throw new RuntimeException('Failed to save page.', 500);
            }

            Session::setFlashMessage('Page saved successfully.');

            throw new RedirectException('/page/'. $page->pageID .'/', 302);
        } else {
            if (!$page->load()) {
                throw new RuntimeException('Page not found');
            }
            new Renderer('@backend/page.html.twig')->render(['page' => $page]);
        }
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
