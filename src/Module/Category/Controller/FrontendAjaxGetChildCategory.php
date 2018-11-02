<?php

namespace Krugozor\Framework\Module\Category\Controller;

use Krugozor\Framework\Controller\Ajax;

class FrontendAjaxGetChildCategory extends Ajax
{
    public function run()
    {
        $this->getView(
            'FrontendAjaxGetChildCategory',
            'Krugozor\Framework\Module\Category\View\FrontendAjaxGetChildCategory'
        );

        $this->getView()->categories = $this->getMapper('Category/Category')->getActiveChildCategories(
            $this->getRequest()->getRequest('id')
        );

        return $this->getView();
    }
}