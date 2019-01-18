<?php

namespace Creativestyle\AdminListBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceFilter extends AbstractFilter
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

        $optionsResolver->setDefaults([
            'template' => function(Options $options) {
                if ($options['tabbed']) {
                    return '@CreativestyleAdminList/Filter/choiceTabbed.html.twig';
                }

                return '@CreativestyleAdminList/Filter/choiceDropdown.html.twig';
            },
            'empty_label' => function (Options $options) {
                return $options['default_value'];
            },
            'empty_value' => null,
            'tabbed' => true,
            'disabled' => false,
            'menu_align' => 'left' // Only for dropdowns
        ]);

        $optionsResolver->setRequired([
            'choices',
            'callback'
        ]);

        $optionsResolver->setAllowedTypes('choices', 'array');
    }
}