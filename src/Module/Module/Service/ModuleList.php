<?php

namespace Krugozor\Framework\Module\Module\Service;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Module\Module\Mapper\Module as ModuleMapper;
use Krugozor\Framework\Service\ListAbstract;

class ModuleList extends ListAbstract
{
    /**
     * ModuleList constructor.
     * @param Request $request
     * @param ModuleMapper $mapper
     * @param \Krugozor\Pagination\Manager $pagination
     */
    public function __construct(Request $request,
                                ModuleMapper $mapper,
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
     * @return ListAbstract
     */
    public function findList(): ListAbstract
    {
        $this->list = $this->mapper->findListForBackend($this->createParams());
        $this->pagination->setCount($this->mapper->getFoundRows());

        return $this;
    }
}