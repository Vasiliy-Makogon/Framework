<?php
class Krugozor_Module_Advert_Controller_View extends Krugozor_Controller
{
    public function run()
    {
        $advert_data = $this->getMapper('Advert/Advert')->findByIdForView(
            $this->getRequest()->getRequest('id', 'decimal')
        );

        // Объявление не найдено.
        if (!$advert_data['advert']->getId()) {
            $this->getView()->getLang()->loadI18n('404/404')->addTitle();
            $this->getResponse()->setHttpStatusCode(404);

            $this->getView()->current_user = $this->getCurrentUser();
            $this->getView()->setTemplateFile($this->getRealLocalTemplatePath('FrontendAdvertNotFound'));
            return $this->getView();
        }

        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            'Advert/FrontendCommon',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->getRequest()->getGet('notif', 'decimal')) {
            $this->getResponse()->unsetHeader('Last-Modified');
            $this->getResponse()->unsetHeader('Expires');
            $this->getResponse()->unsetHeader('Cache-Control');
            $this->getResponse()->unsetHeader('Pragma');

            if (!Krugozor_Http_Request::IfModifiedSince($advert_data['advert']->getLastModifiedDate())) {
                return $this->getResponse()->setHttpStatusCode(304);
            }

            $this->getResponse()->setHeader('Last-Modified', $advert_data['advert']->getLastModifiedDate()->formatHttpDate());
            $this->getResponse()->setHeader('Cache-Control', 'no-cache, must-revalidate');
        }

        $this->getView()->region_paths = new Krugozor_Cover_array();

        foreach ($advert_data as $key => $object) {
            $this->getView()->$key = $object;

            if (isset($object->name_en)) {
                $this->getView()->region_path .= '/' . $object->getNameEn();
                $this->getView()->region_paths[$key] = $this->getView()->region_path;
            }
        }

        // Получаем дерево объектов от корневой категории до категории $category->getId().
        $this->getView()->path_to_category = $this->getMapper('Category/Category')->loadPath(
            $advert_data['category']->getId()
        );

        // Добавляем в title информацию о городе для SEO.
        $this->getView()->getHelper('Krugozor_Html_Title')->add(
            Krugozor_Static_String::trimPunctuation($advert_data['advert']->getHeader()) .
            $this->getView()->getLang()['content']['in'] .
            $advert_data['city']->getNameRu2()
        );

        $this->getView()->current_user = $this->getCurrentUser();

        // Похожие объявления.
        $this->getView()->similar_adverts = $this->getMapper('Advert/Advert')->findLastSimilarAdverts(
            $advert_data['advert'], $advert_data['category']
        );

        // Если пользователь скрыл объявление, то уведомляем об этом.
        if (!$advert_data['advert']->getActive()) {
            $notification = $this->createNotification()
                                 ->setType(Krugozor_Notification::TYPE_WARNING);

            if ($this->getView()->advert->getIdUser() == $this->getCurrentUser()->getId()) {
                $notification->setMessage($this->getView()->getLang()['notification']['message']['advert_close_for_author']);
                $notification->addParam('advert_header', $this->getView()->advert->getHeader());
            } else {
                $notification->setMessage($this->getView()->getLang()['notification']['message']['advert_close_for_user']);
            }

            $this->getView()->setNotification($notification);
        }
        // Если пользователь заблокирован, уведомляем.
        else if (!$advert_data['user']->getActive() && !$advert_data['user']->isGuest()) {
            $notification = $this->createNotification()
                                 ->setType(Krugozor_Notification::TYPE_WARNING)
                                 ->setMessage($this->getView()->getLang()['notification']['message']['advert_close_user_ban']);
            $this->getView()->setNotification($notification);
        }
        // Иначе показ объявления увеличиваем на 1.
        else {
            if (!$this->getCurrentUser()->isGuest() &&
                $advert_data['advert']->getIdUser() != $this->getCurrentUser()->getId()
                OR $this->getCurrentUser()->isGuest()) {
                    $this->getMapper('Advert/Advert')->incrementViewCount($advert_data['advert']);
            }
        }

        // Если администратор просмотрел объявление, то делаем отметку для административной части.
        if ($this->getCurrentUser()->isAdministrator() && !$advert_data['advert']->getWasModerated()) {
            $advert_data['advert']->setWasModerated(1);
            $this->getMapper('Advert/Advert')->saveModel($advert_data['advert']);
        }

        $this->getView()->near_adverts = array(
            'prev' => $this->getMapper('Advert/Advert')->findPrevAdvert($advert_data['advert']),
            'next' => $this->getMapper('Advert/Advert')->findNextAdvert($advert_data['advert'])
        );

        $this->getView()->adverts = [$advert_data];

        return $this->getView();
    }
}
