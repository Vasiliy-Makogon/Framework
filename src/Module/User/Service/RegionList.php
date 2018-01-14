<?php

class Krugozor_Module_User_Service_RegionList extends Krugozor_Service_List
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_List::__construct()
     */
    public function __construct(Krugozor_Http_Request $request,
                                Krugozor_Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->order_options['id'] = 'user-region.id';
        $this->order_options['country'] = 'user-region.id_country';
        $this->order_options['weight'] = 'user-region.weight';
        $this->order_options['name_ru'] = 'user-region.region_name_ru';
        $this->order_options['name_ru2'] = 'user-region.region_name_ru2';
        $this->order_options['name_en'] = 'user-region.region_name_en';

        $this->default_order_options = array(
            'field_name' => 'weight',
            'sort_order' => 'DESC',
        );

        parent::__construct($request, $mapper, $pagination);
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor/Module/Common/Service/Krugozor_Service_List::findList()
     */
    public function findList()
    {
        $this->processSortSearchCondition();
        $this->list = $this->mapper->findListForBackend($this->createParams());
        $this->pagination->setCount($this->mapper->getFoundRows());

        return $this;
    }

    /**
     * Установка параметров для условия WHERE.
     *
     * @param void
     * @return void
     */
    private function processSortSearchCondition()
    {
        if ($id = $this->request->getRequest('id_country', 'decimal')) {
            $this->sql_where_string_buffer[] = 'id_country = ?i';
            $this->sql_where_args_buffer[] = $id;
        }
    }
}