<?php

namespace App\Plugin\Pagination;

use Doctrine\ORM\Query;

/**
 * Class PaginationProvider.
 */
class PaginationProvider
{
    private ?int $limit;
    private ?int $total;
    private ?int $page;

    private int $minimumLimit;
    private int $maximumLimit;

    /**
     * @param PaginationProviderInterface $provider
     * @param int                         $minimumLimit The minimum number of rows per-page
     * @param int                         $maximumLimit The maximum number of rows per-page
     */
    public function __construct(
        private PaginationEntityRepositoryInterface $provider,
        int $minimumLimit = 2,
        int $maximumLimit = 96
    ) {
        $this->setLimitBounds($minimumLimit, $maximumLimit);
    }

    /**
     * Get the page number
     */
    public function getPage() : int
    {
        return $this->page;
    }

    /**
     * Get limit
     */
    public function getLimit() : int
    {
        return $this->limit;
    }

    /**
     * Get the last page
     */
    public function getLastPage() : int
    {
        if ($this->total > $this->limit) {
            return (int) ceil($this->total / $this->limit);
        }

        return 1;
    }

    /**
     * Get the total number of rows
     */
    public function getTotal() : int
    {
        return $this->total;
    }
    /**
     * Validates and sets the requested page
     *
     * @return string If necessary a SQL limit/offset statement is given,
     *   or NULL otherwise.
     */
    private function setPage($page) : void
    {
        if ($this->total <= $this->limit) {
            $this->page = 1;
            return;
        }

        $thisPage = is_numeric($page) ? intval($page) : 1;
        $lastPage = ceil($this->total / $this->limit);

        if ($thisPage < 1 || $thisPage > $lastPage) {
            $thisPage = 1;
        }

        $this->page = $thisPage;
    }

    /**
     * Set per-page limit
     *
     * @param mixed    $page         The requested page.
     * @param mixed    $limit        The requested limit.
     * @param int|null $defaultLimit The default limit, which will be used if the validation of $limit fails
     * @return arrary The result-set
     */
    public function getPaginatedResultSet($page, $limit, ?int $defaultLimit, array $fields, array $sort, ?array $filters = null) : array
    {
        $this->validateLimit($limit, $defaultLimit);

        $queryBuilder = $this->provider->getPaginatorQueryBuilder($filters);

        /**
         * Get total number of rows
         */
        $this->total = $queryBuilder
                ->select('COUNT(paginator)')
                ->getQuery()
                ->getResult(Query::HYDRATE_SINGLE_SCALAR);

        if (!$this->total) {
            return [];
        }

        /**
         * Set page and offset
         */
        $this->setPage($page);

        return $this->provider->fetchPageData($this->page, $this->limit, $fields, $sort, $filters);
    }

    /**
     * Set the minimum pagination limit
     */
    public function setLimitBounds(int $minimumLimit, int $maximumLimit) : void
    {
        if ($minimumLimit > $maximumLimit || $minimumLimit < 2) {
            throw new LogicException(
                sprintf('The minimum should be between 2 and the maximum limit (%s).', $maximumLimit)
            );
        }
        if ($maximumLimit < $minimumLimit) {
            throw new LogicException(
                sprintf('The max should not be less than minimum of %s.', $maximumLimit)
            );
        }

        $this->minimumLimit = $minimumLimit;
        $this->maximumLimit = $maximumLimit;
        $this->limit        = null;
    }

    /**
     * Validates the requested per-page limit, and uses either:
     *  - the validated value
     *  - the $defaultValue, or
     *  - the minimumLimit
     * <br>
     * <br>
     * <b>Note:</b> Whichever validates first in the order explained above.
     */
    private function validateLimit($limit, ?int $defaultValue): void
    {
        $validatedLimit = filter_var($limit, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $this->minimumLimit, 'max_range' => $this->maximumLimit],
        ]);

        if (false !== $validatedLimit) {
            $this->limit = $validatedLimit;
        } elseif ($defaultValue === null || $defaultValue < $this->minimumLimit || $defaultValue > $this->maximumLimit) {
            $this->limit = $this->minimumLimit;
        } else {
            $this->limit = $defaultValue;
        }
    }
}
