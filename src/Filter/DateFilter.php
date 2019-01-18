<?php

namespace Creativestyle\AdminListBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilter extends AbstractFilter
{
    const STRATEGY_EQUAL = "eq";
    const STRATEGY_GREATHER_THAN = "gt";
    const STRATEGY_LESS_THAN = "lt";
    const STRATEGY_GREATHER_THAN_OR_EQUAL = "gte";
    const STRATEGY_LESS_THAN_OR_EQUAL = "lte";

    const FORMAT = 'd.m.Y';

    protected static $strategies = [
        self::STRATEGY_EQUAL => '=',
        self::STRATEGY_GREATHER_THAN => '>',
        self::STRATEGY_LESS_THAN => '<',
        self::STRATEGY_GREATHER_THAN_OR_EQUAL => '>=',
        self::STRATEGY_LESS_THAN_OR_EQUAL => '<=',
    ];

    /**
     * @param QueryBuilder $queryBuilder
     * @throws \Exception
     */
    protected function handleApply(QueryBuilder $queryBuilder)
    {
        $value = trim($this->getValue());

        if (empty($value)) {
            return;
        }

        $date = \DateTime::createFromFormat(self::FORMAT, $value);

        if (false === $date) {
            return;
        }

        $date->setTime(0, 0, 0);

        $strategy = $this->getOption('strategy');

        if ($strategy === self::STRATEGY_EQUAL) {
            /* No date() function in doctrine so working around it :) */
            $dateTo = clone $date;
            $dateTo->setTime(23, 59, 59);

            $queryBuilder->andWhere(sprintf('%s BETWEEN :from AND :to', $this->getOption('field')));

            $queryBuilder->setParameter('from', $date);
            $queryBuilder->setParameter('to', $dateTo);

            return;
        }

        $queryBuilder->andWhere(sprintf('%s %s :%s',
            $this->getOption('field'),
            self::$strategies[$strategy],
            $this->getName()
        ));

        $queryBuilder->setParameter($this->getName(), $date);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'template' => '@CreativestyleAdminList/Filter/date.html.twig',
            'empty_label' => null,
            'empty_value' => null,
            'strategy' => self::STRATEGY_EQUAL
        ]);

        $optionsResolver->setAllowedValues('strategy', array_keys(self::$strategies));

        $optionsResolver->setRequired([
            'field',
        ]);
    }
}