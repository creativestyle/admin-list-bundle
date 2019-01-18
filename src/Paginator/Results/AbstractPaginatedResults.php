<?php

namespace Creativestyle\AdminListBundle\Paginator\Results;

use Creativestyle\Utilities\Configurable;
use Creativestyle\AdminListBundle\Paginator\AbstractPaginator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

abstract class AbstractPaginatedResults implements PaginatedResultsInterface
{
    use Configurable;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var array
     */
    private $results;

    /**
     * Iterator index.
     *
     * @var int
     */
    private $_index = 0;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param int $page
     * @param int $limit
     * @param array $results
     * @param EngineInterface $templating
     * @param array $options
     */
    public function __construct($page, $limit, array $results, EngineInterface $templating, array $options)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->results = $results;
        $this->templating = $templating;

        $this->resolveConfiguration($options);
    }

    /**
     * @param string $name
     * @param array $context
     * @return string
     */
    protected function renderTemplate($name, array $context)
    {
        return $this->templating->render($name, $context);
    }

    /**
     * @return array
     */
    abstract protected function getRenderingParameters();

    /**
     * @return string
     */
    public function renderPaginationControls()
    {
        return $this->renderTemplate($this->getOption('controls_template'), array_merge([
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
            'options' => $this->getOptions(),
            'page_parameter' => AbstractPaginator::PAGE_PARAMETER,
        ], $this->getRenderingParameters()));
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->results[$this->_index];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->_index;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->_index;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return array_key_exists($this->_index, $this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->_index = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Configures the options resolver setting default options, etc.
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
            'page_parameter' => 'page',
        ]);

        $optionsResolver->setRequired([
            'controls_template',
        ]);

        $optionsResolver->setAllowedTypes('page_parameter', 'string');
        $optionsResolver->setAllowedTypes('controls_template', 'string');
    }
}