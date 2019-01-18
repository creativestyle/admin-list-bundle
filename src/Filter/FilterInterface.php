<?php

namespace Creativestyle\AdminListBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\EngineInterface;

interface FilterInterface
{
    /**
     * Renders the filter html.
     *
     * @return string
     */
    public function render();

    /**
     * Applies the filtering on the doctrine query.
     *
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack);

    /**
     * @param EngineInterface $templating
     */
    public function setTemplating(EngineInterface $templating);
}