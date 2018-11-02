<?php

namespace Krugozor\Framework\Module\User\Service;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Mapper;
use Krugozor\Framework\Service\ListAbstract;

class CountryList extends ListAbstract
{
    /**
     * CountryList constructor.
     * @param Request $request
     * @param Mapper $mapper
     * @param \Krugozor\Pagination\Manager $pagination
     */
    public function __construct(Request $request,
                                Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->order_options['id'] = 'user-country.id';
        $this->order_options['name_ru'] = 'user-country.country_name_ru';
        $this->order_options['name_ru2'] = 'user-country.country_name_ru2';
        $this->order_options['name_en'] = 'user-country.country_name_en';
        $this->order_options['active'] = 'user-country.country_active';

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
        $this->list = $this->mapper->findListForBackend($this->createParams());
        $this->pagination->setCount($this->mapper->getFoundRows());

        return $this;
    }
}