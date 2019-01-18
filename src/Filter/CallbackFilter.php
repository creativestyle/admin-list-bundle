<?php

namespace Creativestyle\AdminListBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CallbackFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function handleApply(QueryBuilder $queryBuilder)
    {
        throw new \RuntimeException('Not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setRequired([
            'callback'
        ]);
    }
}