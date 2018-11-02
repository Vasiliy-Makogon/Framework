<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Controller\Ajax;

class FrontendAjaxGetPhone extends Ajax
{
    public function run()
    {
        $this->getView('Ajax');

        $params = array(
            'what' => 'id, advert_id_user, advert_phone',
            'where' => array('id = ?i' => array($this->getRequest()->getRequest('id')))
        );

        $advert = $this->getMapper('Advert/Advert')->findModelByParams($params);

        $phone = $advert->getPhone();

        if (!$phone && $advert->getId() > 0) {
            $params = array(
                'what' => 'user_phone',
                'where' => array('id = ?i' => array($advert->getIdUser()))
            );

            $user = $this->getMapper('User/User')->findModelByParams($params);

            $phone = $user->getPhone();
        }

        $this->getView()->phone = $phone;

        return $this->getView();
    }
}