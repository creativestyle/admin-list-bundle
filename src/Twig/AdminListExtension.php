<?php

namespace Creativestyle\AdminListBundle\Twig;

use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AdminListExtension extends AbstractExtension
{
    /**
     * @param Request $request
     * @param array $parameters
     * @return string
     */
    public function getModifiedQueryUrl(Request $request, $parameters)
    {
        $queryParams = $request->query->all();

        foreach ($parameters as $key => $value) {
            if (null === $value) {
                unset($queryParams[$key]);
            } else {
                $queryParams[$key] = $value;
            }
        }

        return
            $request->getSchemeAndHttpHost() .
            $request->getBaseUrl() .
            $request->getPathInfo() .
            (empty($queryParams) ? '' : '?' . http_build_query($queryParams))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('modify_query', [$this, 'getModifiedQueryUrl']),
        ];
    }
}
