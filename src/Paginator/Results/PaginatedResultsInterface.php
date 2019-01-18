<?php

namespace Creativestyle\AdminListBundle\Paginator\Results;

interface PaginatedResultsInterface extends \Iterator, \Countable
{
    /**
     * @return array
     */
    public function getResults();

    /**
     * @return int
     */
    public function getPage();

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @return string
     */
    public function renderPaginationControls();
}