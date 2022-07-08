<?php

namespace App\Repository;

use App\Model\Articles;
use App\Plugin\Pagination\PaginationEntityRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<Articles>
 *
 * @method Articles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Articles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Articles[]    findAll()
 * @method Articles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticlesRepository extends BaseRepository implements PaginationEntityRepositoryInterface
{
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Articles::class);
    }

    public function create(array $data) : void
    {
        pr($data);
    }

    public function update(int|string $articleId, string $title, string $content) : void
    {
            $sql = "UPDATE {$this->getTableName()} " . <<<'SQL'
SET title=:title, content=:content WHERE id=:articleId
SQL;

        $connection = $this->getEntityManager()->getConnection();
        $connection->executeStatement($sql, compact('articleId', 'title', 'content'));
    }

    /**
     * @todo make use of filters
     */
    public function getPaginatorQueryBuilder(?array $filters = null) : QueryBuilder
    {
        return $this->createQueryBuilder('paginator');
    }

    /**
     * On Articles `author_id` and `article_date` fields are not being reflected
     * 
     * @todo: figure out the issue, and get rid of following method.
     * https://stackoverflow.com/questions/23107952
     */
    public function selectArticleById(int|string $articleId): array
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManager();

        return $entityManager
                ->getConnection()
                ->fetchAssociative("SELECT * FROM {$this->getTableName()} WHERE id=?", [$articleId]);
    }

    /**
     * @todo: figure out the issue, and get rid of following method.
     * https://stackoverflow.com/questions/23107952
     */
    public function fetchPageData(int $page, int $limit, array $fields, array $sort, ?array $filters = null) : array
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManager();
        $sql = "SELECT a.id,CONCAT(u.name,' ',u.surname) AS author,a.article_date,
                CASE WHEN LENGTH(a.content) > 1000
                    THEN
                        CONCAT(SUBSTRING(a.content, 1, 1000), '...')
                    ELSE
                        a.content
                    END AS content FROM {$this->getTableName()} a
                LEFT JOIN my_users u ON u.id=a.author_id ORDER BY {$sort[0]} {$sort[1]} LIMIT " . (--$page * $limit) . ',' . $limit;

        return $entityManager->getConnection()->fetchAllAssociative($sql);
    }
}
