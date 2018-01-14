<?php
class Krugozor_Module_Advert_Controller_FrontendAjaxGetEmail extends Krugozor_Controller_Ajax
{
    private static $hacker_message = 'Hacker? Ha-ha-ha!';

    public function run()
    {
        $this->getView('Ajax');

        $params = array(
            'what' => 'id, advert_id_user, advert_email, advert_main_email',
            'where' => array('id = ?i' => array($this->getRequest()->getRequest('id')))
        );

        $advert = $this->getMapper('Advert/Advert')->findModelByParams($params);

        if (!$advert->getEmail()->getValue() && $advert->getId() > 0 or $advert->getMainEmail()) {
            $params = array(
                'what' => 'user_email',
                'where' => array('id = ?i' => array($advert->getIdUser()))
            );

            $user = $this->getMapper('User/User')->findModelByParams($params);

            $email = $user->getEmail();
        } else {
            $email = $advert->getEmail();
        }

        if ($email->getMailHashForAccessView() !== $this->getRequest()->getRequest('hash')) {
            $email->setValue(self::$hacker_message);
        }

        $this->getView()->email = $email->getValue();

        return $this->getView();
    }
}