<?php

namespace App\Plugin\Pagination;

use Doctrine\ORM\QueryBuilder;

/**
 * Data repositories should implement this interface to provide the pagination ability.
 */
interface PaginationEntityRepositoryInterface
{
    /**
     * Get the QueryBuilder.
     */
    public function getPaginatorQueryBuilder(?array $filters = null) : QueryBuilder;
    public function fetchPageData(int $page, int $limit, array $fields, array $sort, ?array $filters = null) : array;
}
