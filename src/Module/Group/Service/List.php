<?php

class Krugozor_Module_Group_Service_List extends Krugozor_Service_List
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_List::__construct()
     */
    public function __construct(Krugozor_Http_Request $request,
                                Krugozor_Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->order_options['name'] = 'group_name';
        $this->order_options['active'] = 'group_active';

        $this->default_order_options = array(
            'field_name' => 'id',
            'sort_order' => 'ASC',
        );

        parent::__construct($request, $mapper, $pagination);
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_List::findList()
     */
    public function findList()
    {
        $this->list = $this->mapper->findListForBackend($this->createParams());
        $this->pagination->setCount($this->mapper->getFoundRows());

        return $this;
    }
}