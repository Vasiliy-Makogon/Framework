<?php

namespace Krugozor\Framework\Module\User\Service;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Mapper;
use Krugozor\Framework\Service\ListAbstract;

class CityList extends ListAbstract
{
    /**
     * CityList constructor.
     * @param Request $request
     * @param Mapper $mapper
     * @param \Krugozor\Pagination\Manager $pagination
     */
    public function __construct(Request $request,
                                Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->order_options['id'] = 'user-city.id';
        $this->order_options['name_ru'] = 'user-city.city_name_ru';
        $this->order_options['name_ru2'] = 'user-city.city_name_ru2';
        $this->order_options['name_ru3'] = 'user-city.city_name_ru3';
        $this->order_options['name_en'] = 'user-city.city_name_en';

        $this->default_order_options = array(
            'field_name' => 'weight',
            'sort_order' => 'DESC',
        );

        parent::__construct($request, $mapper, $pagination);
    }

    /**
     * @return ListAbstract
     */
    public function findList(): ListAbstract
    {
        $this->processSortSearchCondition();
        $this->list = $this->mapper->findListForBackend($this->createParams());
        $this->pagination->setCount($this->mapper->getFoundRows());

        return $this;
    }

    /**
     * Установка параметров для условия WHERE.
     */
    private function processSortSearchCondition()
    {
        if ($id = $this->request->getRequest('id_region', 'decimal')) {
            $this->sql_where_string_buffer[] = 'id_region = ?i';
            $this->sql_where_args_buffer[] = $id;
        }
    }
}