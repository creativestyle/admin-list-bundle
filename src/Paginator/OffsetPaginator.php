<?php

namespace Creativestyle\AdminListBundle\Paginator;

use Creativestyle\Utilities\ArrayHelpers;
use Creativestyle\AdminListBundle\Paginator\Results\OffsetPaginationResults;
use Creativestyle\AdminListBundle\Paginator\Results\PaginatedResultsInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffsetPaginator extends AbstractPaginator
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


        if ($this->getOption('fetch_join_collection')) {
            $idField = $this->getOption('id_field');

            $idQueryBuilder = clone $queryBuilder;
            $idQueryBuilder->select(sprintf('DISTINCT(%s)', $idField));
            $idQueryBuilder->setFirstResult($start);
            $idQueryBuilder->setMaxResults($limit + 1);

            $ids = array_map('current', $idQueryBuilder->getQuery()->getResult());

            $whereIdCondition = sprintf('%s in (:pagination_ids)', $idField);

            if ($this->getOption('filter_joined_collections')) {
                $queryBuilder
                    ->andWhere($whereIdCondition)
                    ->setParameter('pagination_ids', $ids)
                ;
            } else {
                $queryBuilder
                    ->where($whereIdCondition)
                    ->setParameters(['pagination_ids' => $ids])
                ;
            }

            $query = $queryBuilder->getQuery();
        } else {
            $queryBuilder->setFirstResult($start);
            $queryBuilder->setMaxResults($limit + 1);

            $query = $queryBuilder->getQuery();
        }

        $this->applyFetchModes($query);
        $results = $query->getResult();

        $hasNextPage = count($results) > $limit;

        return new OffsetPaginationResults(
            $this->getPage(),
            $limit,
            $hasNextPage,
            array_slice($results, 0, $limit),
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
            'filter_joined_collections' => false,
        ]);

        $optionsResolver->setDefined([
            'controls_template',
            'id_field'
        ]);

        $optionsResolver->setAllowedTypes('controls_template', 'string');
        $optionsResolver->setAllowedTypes('sort_controls_template', 'string');
        $optionsResolver->setAllowedTypes('fetch_join_collection', 'bool');
    }
}