<?php

namespace Creativestyle\AdminListBundle\Filter;

use Creativestyle\Utilities\Configurable;
use Creativestyle\AdminListBundle\Paginator\AbstractPaginator;
use Creativestyle\Utilities\StringHelpers;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;

abstract class AbstractFilter implements FilterInterface
{
    use Configurable;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var
     */
    private $name;

    /**
     * @param string$name
     * @param array $options
     */
    public function __construct($name, array $options)
    {
        $this->name = $name;

        $this->resolveConfiguration($options);
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
     * @param string $name
     * @param array $context
     * @return string
     */
    protected function renderTemplate($name, array $context)
    {
        if (null === $this->templating) {
            throw new \LogicException('Templating needed but not injected.');
        }

        return $this->templating->render($name, $context);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }

    /**
     * @return mixed|null
     */
    protected function getValue()
    {
        $value = $this->getRequest()->query->get($this->getName());

        if (null === $value) {
            return $this->getOption('default_value');
        }

        return $value;
    }

    /**
     * Returns parameters that are to be passed to tempalte.
     *
     * @return array
     */
    protected function getRenderingParameters()
    {
        return array_merge([
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'label' => $this->getOption('label'),
            'options' => $this->getOptions(),
            'request' => $this->getRequest(),
            'page_parameter' => AbstractPaginator::PAGE_PARAMETER, // Filters need to reset page on change
        ], $this->getOption('parameters'));
    }
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->renderTemplate(
            $this->getOption('template'),
            $this->getRenderingParameters()
        );
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    abstract protected function handleApply(QueryBuilder $queryBuilder);

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function applyJoins(QueryBuilder $queryBuilder)
    {
        foreach ($this->getOption('joins') as $join) {
            list($path, $alias) = $join;

            if (!in_array($alias, $queryBuilder->getAllAliases())) {
                $queryBuilder->leftJoin($path, $alias);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder)
    {
        if (!empty($this->getValue())) {
            $this->applyJoins($queryBuilder);
        }

        if (null === $this->getOption('callback')) {
            return $this->handleApply($queryBuilder);
        }

        return call_user_func($this->getOption('callback'), $queryBuilder, $this->getValue());
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
            'callback' => null,
            'default_value' => null,
            'label' => StringHelpers::humanize($this->getName()),
            'parameters' => [],
            'joins' => [],
        ]);

        $optionsResolver->setRequired([
            'template',
        ]);

        $optionsResolver->setAllowedTypes('template', 'string');
    }
}