<?php

namespace Creativestyle\AdminListBundle\Filter;

use Creativestyle\AdminListBundle\Paginator\PaginatorInterface;
use Creativestyle\AdminListBundle\Paginator\Results\PaginatedResultsInterface;
use Creativestyle\AdminListBundle\Paginator\SimplePaginator;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\EngineInterface;

/**
 * Manages filters.
 */
class FilterManager
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var FilterInterface[]
     */
    protected $filterChain = [];

    /**
     * @var FilterInterface[]
     */
    protected $filters = [];

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @param RequestStack $requestStack
     * @param EngineInterface $templating
     */
    public function __construct(RequestStack $requestStack, EngineInterface $templating)
    {
        $this->requestStack = $requestStack;
        $this->templating = $templating;

        $this->paginator = new SimplePaginator();
        $this->paginator->setRequestStack($this->requestStack);
        $this->paginator->setTemplating($this->templating);
    }

    /**
     * Adds filter to the last position in the chain.
     *
     * @param FilterInterface $filter
     * @return FilterManager
     */
    public function registerFilter(FilterInterface $filter)
    {
        $filter->setTemplating($this->templating);
        $filter->setRequestStack($this->requestStack);

        $this->filterChain[] = $filter;
        $this->filters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * Applies the filter chain to the query.
     *
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilters(QueryBuilder $queryBuilder)
    {
        foreach ($this->filterChain as $filter) {
            $filter->apply($queryBuilder);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return PaginatedResultsInterface
     */
    public function getPaginatedResults(QueryBuilder $queryBuilder)
    {
        return $this->paginator->paginate($queryBuilder);
    }

    /**
     * Returns filter by name.
     *
     * @param $name
     * @return FilterInterface
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->filters)) {
            throw new \RuntimeException("Request a non-existent filter '$name'.");
        }

        return $this->filters[$name];
    }

    /**
     * @param string $name
     * @return string
     */
    public function renderFilter($name)
    {
        return $this->get($name)->render();
    }

    /**
     * @param PaginatorInterface $paginator
     * @return FilterManager
     */
    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;

        $this->paginator->setRequestStack($this->requestStack);
        $this->paginator->setTemplating($this->templating);

        return $this;
    }

    /**
     * @param string $field
     * @param string $label
     * @return string
     */
    public function renderSortControls($field, $label = null)
    {
        return $this->paginator->renderSortControls($field, $label);
    }

    /**
     * @return FilterInterface[]
     */
    public function all()
    {
        return $this->filterChain;
    }
}