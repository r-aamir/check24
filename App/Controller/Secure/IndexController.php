<?php

namespace App\Controller\Secure;

use App\Controller\BaseController;
use App\DependencyInjection\Authenticator\SessionAuthenticator;
use App\Factory\ViewRendererFactory;
use App\Model\Articles;
use App\Plugin\DataTableGrid\ArticleDataGridTable;
use App\Plugin\Pagination\PaginationProvider;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends BaseController
{
    public function __construct(ViewRendererFactory $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;
    }

    public function getControllerModule() : string
    {
        return 'secure';
    }

    public function indexAction(EntityManagerInterface $entityManager, SessionAuthenticator $sessionAuthenticator) : Response
    {
        if ($sessionAuthenticator->getAuthenticatedUser() === null) {
            return $this->renderView('home', ['is_guest' => true]);
        }

        /**
         * Load Articles - Paginated
         */
        $request = $this->getRequest();
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

        return $this->renderView(
            'articles',
            compact('sortDef', 'sortColumn', 'page', 'pages', 'limit', 'total', 'list')
        );
    }
}
