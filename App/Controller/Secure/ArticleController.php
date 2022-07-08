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

    public function editAction(EntityManagerInterface $entityManager, FormFactory $formFactory)
    {
        $request = $this->getRequest();

        /** @var ArticlesRepository $articlesRepository */
        $articlesRepository = $entityManager->getRepository(Articles::class);

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

            /** @var Users $authorEntity */
            $authorEntity = $request->getSession()->get('auth.user');
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
