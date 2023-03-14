<?php

namespace App\Service;

use ArrayIterator;
use Doctrine\ORM\QueryBuilder;
use Traversable;

class Paginator
{
    private const PAGE_SIZE = 20;
    private ArrayIterator|Traversable $result;
    private int $currentPage;
    private ?int $numResult;

    /**
     * @param QueryBuilder $queryBuilder
     * @param int $pagesize
     */
    public function __construct(
        private QueryBuilder $queryBuilder,
        private int $pagesize = self::PAGE_SIZE
    ){}

    /**
     * @param int $page
     * @return $this
     * @throws \Exception
     */
    public final function pagination(int $page = 1): self
    {
        $this->currentPage = max(1, $page);
        $firstResult = ($this->currentPage-1) * $this->pagesize;

        $query = $this->queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($this->pagesize)
            ->getQuery();
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, true);

        $this->result = $paginator->getIterator();
        $this->numResult = $paginator->count();

        return $this;
    }

    /**
     * @return Traversable|ArrayIterator
     */
    public final function getResult(): Traversable|ArrayIterator
    {
        return $this->result;
    }

    /**
     * @return int
     */
    public final function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int|null
     */
    public final function getNumResult(): ?int
    {
        return $this->numResult;
    }

    /**
     * @return int
     */
    public final function getLastPage(): int
    {
        return (int) ceil($this->numResult / $this->pagesize);
    }

    /**
     * @return int
     */
    public final function getPageSize(): int
    {
        return $this->pagesize;
    }

    /**
     * @return bool
     */
    public final function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * @return bool
     */
    public final function hasNextPage(): bool
    {
        return $this->currentPage < $this->getLastPage();
    }

    /**
     * @return int
     */
    public final function getPreviousPage(): int
    {
        return max(1, $this->currentPage-1);
    }

    /**
     * @return int
     */
    public final function getNextPage(): int
    {
        return min($this->getLastPage(), $this->currentPage+1);
    }

    /**
     * @return bool
     */
    public final function hasToPaginate(): bool
    {
        return $this->numResult > $this->pagesize;
    }

}