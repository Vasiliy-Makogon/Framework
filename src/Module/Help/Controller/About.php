<?php

namespace Krugozor\Framework\Module\Help\Controller;

use Krugozor\Framework\Controller;

class About extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral'
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();
        $this->getView()->categories = $this->getMapper('Category/Category')->findCategoriesFirstLevel();

        return $this->getView();
    }
}