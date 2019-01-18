<?php

namespace Creativestyle\AdminListBundle\Paginator;

use Creativestyle\Utilities\ArrayHelpers;
use Creativestyle\AdminListBundle\Paginator\Results\PaginatedResultsInterface;
use Creativestyle\AdminListBundle\Paginator\Results\SlidingPaginationResults;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimplePaginator extends AbstractPaginator
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return PaginatedResultsInterface
     */
    public function handlePagination(QueryBuilder $queryBuilder)
    {
        $page = $this->getPage() - 1;
        $limit = $this->getOption('limit');
        $start = $limit * $page;

        $queryBuilder->setFirstResult($start);
        $queryBuilder->setMaxResults($limit);

        $query = $queryBuilder->getQuery();

        $this->applyFetchModes($query);

        $paginator = new Paginator($query, $this->getOption('fetch_join_collection'));

        $results = iterator_to_array($paginator);
        $count = $paginator->count();

        return new SlidingPaginationResults(
            $this->getPage(),
            $limit,
            $count,
            $results,
            $this->getTemplating(),
            ArrayHelpers::pick(['controls_template'], $this->getOptions())
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'fetch_join_collection' => false,
        ]);

        $optionsResolver->setDefined([
            'controls_template',
        ]);

        $optionsResolver->setAllowedTypes('controls_template', 'string');
        $optionsResolver->setAllowedTypes('sort_controls_template', 'string');
        $optionsResolver->setAllowedTypes('fetch_join_collection', 'bool');
    }
}