<?php

class Krugozor_Pagination_Adapter extends Krugozor\Pagination\Manager
{
    /**
     * @param Krugozor_Http_Request $request
     * @param int $limit
     * @param int $link_count
     * @param string $page_var_name
     * @param string $separator_var_name
     * @return \Krugozor\Pagination\Manager
     */
    public static function getManager(
        Krugozor_Http_Request $request,
        $limit = 10,
        $link_count = 10,
        $page_var_name = 'page',
        $separator_var_name = 'sep')
    {
        return new Krugozor\Pagination\Manager(
            $limit, $link_count, $request->getRequest()->getDataAsArray(), $page_var_name, $separator_var_name
        );
    }
}