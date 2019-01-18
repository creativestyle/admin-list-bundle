<?php

namespace Creativestyle\AdminListBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StringFilter extends AbstractFilter
{
    /**
     * @param string $field
     * @return string
     */
    protected function coalesceToString($field)
    {
        return sprintf("COALESCE(%s, '')", $field);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $fields
     * @return Expr|string
     */
    protected function createConcatExpr(QueryBuilder $queryBuilder, array $fields)
    {
        if (count($fields) == 1) {
            return $this->coalesceToString(current($fields));
        }

        return $queryBuilder->expr()->concat(
            $this->coalesceToString(array_shift($fields)),
            $this->createConcatExpr($queryBuilder, $fields)
        );
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Expr|string $fieldsExpr
     * @param $userQuery
     * @return Expr
     */
    protected function createComparisonExpr(QueryBuilder $queryBuilder, $fieldsExpr, $userQuery)
    {
        $userQueryExpr = $queryBuilder->expr()->literal($userQuery);

        if ($this->getOption('exact')) {
            return $queryBuilder->expr()->eq($fieldsExpr, $userQueryExpr);
        }

        return $expr = $queryBuilder->expr()->like($fieldsExpr, $userQueryExpr);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @throws \Exception
     */
    protected function handleApply(QueryBuilder $queryBuilder)
    {
        $concat = $this->getOption('concat');
        $fields = $this->getOption('fields');
        $exact = $this->getOption('exact');
        $wildcard = $this->getOption('wildcard');
        $userQuery = trim($this->getValue());

        if(empty($userQuery)) {
            return;
        }

        if (!$exact) {
            if ($wildcard) {
                $userQuery = str_replace('*', '%', $userQuery);
            } else {
                $userQuery = '%' . str_replace(' ', '%', $userQuery) . '%';
            }
        }

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        if ($concat) {
            $fieldsExpr = $this->createConcatExpr($queryBuilder, $fields);
            $expr = $this->createComparisonExpr($queryBuilder, $fieldsExpr, $userQuery);
        } else {
            $expr = $queryBuilder->expr()->orX();

            foreach ($fields as $field) {
                $expr->add($this->createComparisonExpr($queryBuilder, $field, $userQuery));
            }
        }

        $queryBuilder->andWhere($expr);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'template' => '@CreativestyleAdminList/Filter/string.html.twig',
            'concat' => false,
            'wildcard' => false,
            'exact' => function (Options $options) {
                if ($options['concat'] || $options['wildcard']) {
                    return false;
                }

                return true;
            },
        ]);

        $optionsResolver->setRequired([
            'fields',
        ]);

        $optionsResolver->setAllowedTypes('fields', ['array', 'string']);
    }
}