<?php

namespace Creativestyle\AdminListBundle\Paginator\Results;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class OffsetPaginationResults extends AbstractPaginatedResults
{
    /**
     * @var bool
     */
    private $hasNextPage;

    /**
     * @param int $page
     * @param int $limit
     * @param bool $hasNextPage
     * @param array $results
     * @param EngineInterface $templating
     * @param array $options
     */
    public function __construct(
        $page,
        $limit,
        $hasNextPage,
        array $results,
        EngineInterface $templating,
        array $options
    ) {
        parent::__construct($page, $limit, $results, $templating, $options);

        $this->hasNextPage = $hasNextPage;
    }

    /**
     * @return boolean
     */
    public function hasNextPage()
    {
        return $this->hasNextPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRenderingParameters()
    {
        $proximity = $this->getOption('proximity_pages');
        $page = $this->getPage();

        $startPage = $page - $proximity;

        if ($startPage <= 1) {
            $startPage = 1;
        }

        return [
            'count' => count($this),
            'page' => $page,
            'has_next_page' => $this->hasNextPage,
            'start_page' => $startPage,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'controls_template' => '@CreativestyleAdminList/Paginator/offsetPaginationControls.html.twig',
            'proximity_pages' => 3,
        ]);
    }
}