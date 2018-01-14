<?php

class Krugozor_Module_Module_Service_List extends Krugozor_Service_List
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_List::__construct()
     */
    public function __construct(Krugozor_Http_Request $request,
                                Krugozor_Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->order_options['id'] = 'module.id';
        $this->order_options['name'] = 'module.module_name';
        $this->order_options['key'] = 'module.module_key';

        $this->default_order_options = array(
            'field_name' => 'name',
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