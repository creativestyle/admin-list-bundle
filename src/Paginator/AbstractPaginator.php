<?php

namespace Creativestyle\AdminListBundle\Paginator;

use Creativestyle\Utilities\Configurable;
use Creativestyle\AdminListBundle\Paginator\Results\PaginatedResultsInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

abstract class AbstractPaginator implements PaginatorInterface
{
    use Configurable;

    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    const PAGE_PARAMETER = 'page';
    const ORDER_PARAMETER = 'order';
    const DIRECTION_PARAMETER = 'direction';

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->resolveConfiguration($options);
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->getRequest()->query->get(self::PAGE_PARAMETER, 1);
    }

    /**
     * @return string|null
     */
    protected function getOrder()
    {
        return $this->getRequest()->query->get(self::ORDER_PARAMETER, $this->getOption('order_by'));
    }

    /**
     * @return string|null
     */
    protected function getDirection()
    {
        $direction = $this->getRequest()->query->get(self::DIRECTION_PARAMETER, $this->getOption('sort_direction'));

        if (in_array($direction, [self::ORDER_ASC, self::ORDER_DESC])) {
            return $direction;
        }

        return $this->getOption('sort_direction');
    }


    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param EngineInterface $templating
     */
    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return PaginatedResultsInterface
     */
    abstract protected function handlePagination(QueryBuilder $queryBuilder);


    /**
     * @param QueryBuilder $queryBuilder
     * @return PaginatedResultsInterface
     */
    public function paginate(QueryBuilder $queryBuilder)
    {
        $order = $this->getOrder();
        $direction = $this->getDirection() === self::ORDER_ASC ? 'ASC' : 'DESC';

        if(null !== $this->getOrder()) {
            $queryBuilder->addOrderBy($order, $direction);
        }

        return $this->handlePagination($queryBuilder);
    }

    /**
     * @return EngineInterface
     */
    protected function getTemplating()
    {
        if (null === $this->templating) {
            throw new \LogicException('Templating needed but not injected.');
        }

        return $this->templating;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        if (null === $this->requestStack) {
            throw new \LogicException('Request needed but not injected.');
        }

        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @param Query $query
     */
    protected function applyFetchModes(Query $query)
    {
        foreach($this->getOption('fetch_modes') as $fetchMode) {
            $query->setFetchMode($fetchMode['class'], $fetchMode['association'], $fetchMode['mode']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function renderSortControls($field, $label = null)
    {
        $order = $this->getOrder();
        $direction = $this->getDirection();

        return $this->getTemplating()->render($this->getOption('sort_controls_template'), [
            'field' => $field,
            'label' => $label,
            'order_parameter' => self::ORDER_PARAMETER,
            'direction_parameter' => self::DIRECTION_PARAMETER,
            'is_asc' => $direction == self::ORDER_ASC,
            'is_desc' => $direction == self::ORDER_DESC,
            'order' => $order,
            'direction' => $direction,
            'new_direction' => $direction == self::ORDER_ASC ? self::ORDER_DESC : self::ORDER_ASC,
            'active' => $order === $field,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
            'limit' => 50,
            'sort_controls_template' => '@CreativestyleAdminList/Paginator/theadSortControls.html.twig',
            'order_by' => null,
            'sort_direction' => self::ORDER_ASC,
            'fetch_modes' => []
        ]);

        $optionsResolver->setAllowedTypes('limit', 'integer');
        $optionsResolver->setAllowedTypes('fetch_modes', 'array');
    }
}