<?php

namespace Krugozor\Framework\Pagination;

use Krugozor\Framework\Http\Request;
use Krugozor\Pagination\Manager;

class Adapter
{
    /**
     * @param Request $request
     * @param int $limit
     * @param int $link_count
     * @param string $page_var_name
     * @param string $separator_var_name
     * @return \Krugozor\Pagination\Manager
     */
    public static function getManager(
        Request $request,
        $limit = 10,
        $link_count = 10,
        $page_var_name = 'page',
        $separator_var_name = 'sep'): \Krugozor\Pagination\Manager
    {
        return new Manager(
            $limit, $link_count, $request->getRequest()->getDataAsArray(), $page_var_name, $separator_var_name
        );
    }
}