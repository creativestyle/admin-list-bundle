<?php

namespace Creativestyle\AdminListBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TabbedChoiceFilter extends ChoiceFilter
{
    /**
     * @param QueryBuilder $queryBuilder
     * @throws \Exception
     */
    protected function handleApply(QueryBuilder $queryBuilder)
    {
        throw new \Exception('Not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->remove('tabbed');
        $optionsResolver->setDefault('template', '@CreativestyleAdminList/Filter/choiceTabbed.html.twig');
    }
}