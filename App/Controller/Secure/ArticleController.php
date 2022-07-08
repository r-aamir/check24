<?php

declare(strict_types=1);

namespace App\Controller\Secure;

use App\Controller\BaseController;
use App\Factory\FormFactory;
use App\Factory\ViewRendererFactory;
use App\Form\ArticleEditorForm;
use App\Model\Articles;
use App\Model\Users;
use App\Plugin\DataTableGrid\ArticleDataGridTable;
use App\Plugin\Pagination\PaginationProvider;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function compact;

class ArticleController extends BaseController
{
    public function __construct(protected ViewRendererFactory $viewRenderer)
    {
    }

    public function getControllerModule() : string
    {
        return 'secure';
    }

    public function indexAction(Request $request, EntityManagerInterface $entityManager) : Response
    {
        $sortDef          = [];
        $orderBy          = ArticleDataGridTable::sortOrder($sortDef, $request->get('sort', ''));
        $sortColumn = $orderBy[0];

        /** @var ArticlesRepository $articlesRepository */
        $articlesRepository = $entityManager->getRepository(Articles::class);

        $paginator = new PaginationProvider($articlesRepository, 3);
        $list      = $paginator->getPaginatedResultSet(
            $request->get('page'),
            $request->get('limit'),
            null,
            $sortDef['names'],
            $orderBy
        );
        $page = $paginator->getPage();
        $limit = $paginator->getLimit();
        $pages = $paginator->getLastPage();
        $total = $paginator->getTotal();

        unset($sortDef['names']);

        return $this->renderView(
            'articles',
            compact('sortDef', 'sortColumn', 'page', 'pages', 'limit', 'total', 'list')
        );
    }

    public function editAction(EntityManagerInterface $entityManager, FormFactory $formFactory)
    {
        $request = $this->getRequest();

        /** @var ArticlesRepository $articlesRepository */
        $articlesRepository = $entityManager->getRepository(Articles::class);

        /** @var Users $authorEntity */
        $authorEntity = $request->getSession()->get('auth.user');

        if ('create' === $articleId = $request->get('articleId')) {
            $articleId = null;
        } elseif (null === $article = $articlesRepository->selectArticleById($articleId)) {
            return new RedirectResponse($this->getRoute('secure.articles'));
        }

        /** @var ArticleEditorForm $editForm */
        $editForm = $formFactory->buildForm('form.article_edit', $article ?? []);

        $error = null;

        if ($editForm->isValid()) {
            $formData = $editForm->getForm()->getData();
            $routeUrl = $this->getRoute('secure.home');
            if ($articleId === null) {
                $articlesRepository->create($authorEntity->getId(), $formData);
                $routeUrl .= '?limit=3&sort=d1';
            } else {
                $articlesRepository->update($articleId, $formData['title'], $formData['content']);
            }

            return new RedirectResponse($routeUrl);
        }

        return $this->renderView('article-edit', [
            'form'  => $editForm->createView(),
            'error' => $error,
        ]);
    }
}
