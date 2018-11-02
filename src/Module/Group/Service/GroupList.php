<?php

namespace Krugozor\Framework\Module\Group\Service;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Service\ListAbstract;
use Krugozor\Framework\Module\Group\Mapper\Group as GroupMapper;

class GroupList extends ListAbstract
{
    public function __construct(Request $request,
                                GroupMapper $mapper,
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
     * @return CoverArray
     */
    public function findList(): ListAbstract
    {
        $this->list = $this->mapper->findListForBackend($this->createParams());
        $this->pagination->setCount($this->mapper->getFoundRows());

        return $this;
    }
}