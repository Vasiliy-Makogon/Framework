<?php
class Krugozor_Module_Advert_Controller_FrontendEditAdvert extends Krugozor_Module_Advert_Controller_FrontendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            'Advert/FrontendCommon'
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                        ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setNotificationUrl($this->getRequest()->getRequest('referrer') ?: '/my/')
                        ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        $this->advert = $this->advert ?: $this->getMapper('Advert/Advert')->createModel(
            $this->getCurrentUser()
        );

        if ($this->getCurrentUser()->getId() !== $this->advert->getIdUser() OR
            ($this->advert->getId() &&
             $this->getCurrentUser()->isGuest() &&
             $this->getCurrentUser()->getId() === $this->advert->getIdUser())
           )
        {
            return $this->createNotification()
                        ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setNotificationUrl('/my/')
                        ->run();
        }

        if ($this->getCurrentUser()->isGuest()) {
            $this->getView()->session_name = Krugozor_Session::getInstance('EDITADVERT')->getName();
            $this->getView()->session_id = Krugozor_Session::getInstance()->getId();
        }

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        // Добавление объявления "В этот раздел" (переход по ссылке с параметрами).
        if (!$this->advert->getId()) {
            $id_category = $this->getRequest()->getGet('category', 'decimal') ?: $this->advert->getCategory();
            $category = $this->getMapper('Category/Category')->findModelById($id_category);
            $this->advert->setCategory($id_category);

            $id_country = $this->getRequest()->getGet('country', 'decimal') ?: $this->advert->getPlaceCountry();
            $country = $this->getMapper('User/Country')->findModelById($id_country);
            $this->advert->setPlaceCountry($country->getId());

            $id_region = $this->getRequest()->getGet('region', 'decimal') ?: $this->advert->getPlaceRegion();
            $region = $this->getMapper('User/Region')->findModelById($id_region);
            $this->advert->setPlaceRegion($region->getId());

            $id_city = $this->getRequest()->getGet('city', 'decimal') ?: $this->advert->getPlaceCity();
            $city = $this->getMapper('User/City')->findModelById($id_city);
            $this->advert->setPlaceCity($city->getId());

            // Строка для SEO, в какой регион подается объявление.
            $this->getView()->header_postfix_text = '';

            if ($country->getId()) {
                $this->getView()->header_postfix_text = $country->getIsDefaultCountry()
                                                        ? null
                                                        : $this->getView()->getLang()['content']['in'] . $country->getNameRu2();
            }

            if ($country->getId() && $region->getId()) {
                $this->getView()->header_postfix_text = $this->getView()->getLang()['content']['in'] . $region->getNameRu2();
            }

            if ($country->getId() && $region->getId() && $city->getId()) {
                $this->getView()->header_postfix_text = $this->getView()->getLang()['content']['in'] . $city->getNameRu2();
            }

            if ($category->getId()) {
                $this->getView()->header_postfix_text .= Krugozor_Static_String::createMessageFromParams(
                    $this->getView()->getLang()['content']['in_category'],
                    array('category_name' => $category->getName())
                );
            }
        }

        $this->getView()->tree = $this->getMapper('Category/Category')->findActiveCategories(2);

        // В случае ошибочного post-запроса или когда ID категории передается методом get,
        // в двухуровневое дерево $this->getView()->tree добавляем путь к указанному узлу
        // для корректного вывода в select-списке.
        if ($this->advert->getCategory()) {
            $path_to_category = $this->getMapper('Category/Category')->loadPath($this->advert->getCategory());

            if ($path_to_category) {
                foreach ($this->getView()->tree as $category) {
                    foreach ($category->getTree() as $subcategory) {
                        if ($category->getId() == $path_to_category->item(0)->getId() &&
                            $subcategory->getId() == $path_to_category->item(0)->getTree()->item(0)->getId()) {
                                $subcategory->setTree($path_to_category->item(0)->getTree()->item(0)->getTree());
                                break 2;
                        }
                    }
                }
            }
        }

        $this->getView()->current_user = $this->getCurrentUser();
        $this->getView()->advert = $this->advert;

        $this->getView()->from_registration = $this->getRequest()->getRequest()->from_registration;
        $this->getView()->max_file_size = Krugozor_Utility_Upload_File::getBytesFromString(
            Krugozor_Registry::getInstance()->UPLOAD['MAX_FILE_SIZE']
        );

        return $this->getView();
    }

    protected function post()
    {
        $data = $this->getRequest()->getPost('advert')->getDataAsArray();
        $this->advert->setData($data, array('id', 'vip_date', 'special_date', 'id_user', 'active', 'payment'));

        // Добавление объектов изображений в объект объявления на основе массива идентификаторов изображений из формы.
        if ($this->getRequest()->getPost('thumbnail')) {
            $this->advert->loadThumbnailsListByIds($this->getRequest()->getPost('thumbnail'));
        }

        $validator = new Krugozor_Validator('common/general', 'advert/edit', 'captcha/common');

        if ($this->getCurrentUser()->isGuest() &&
            !$this->advert->getIcq() && !$this->advert->getPhone() &&
            !$this->advert->getEmail()->getValue() && !$this->advert->getUrl() &&
            !$this->advert->getSkype()
            OR
            !$this->getCurrentUser()->isGuest() &&
            !$this->advert->getIcq() && !$this->advert->getPhone() &&
            !$this->advert->getEmail()->getValue() && !$this->advert->getUrl()->getValue() &&
            !$this->advert->getSkype() &&
            !$this->advert->getMainIcq() && !$this->advert->getMainPhone() &&
            !$this->advert->getMainEmail() && !$this->advert->getMainUrl() &&
            !$this->advert->getMainSkype()
           )
        {
            $validator->addError('contact_info', 'EMPTY_CONTACT_INFO');
        }

        $validator->addModelErrors($this->advert->getValidateErrors());

        if ($this->getCurrentUser()->isGuest()) {
            $validator->add('captcha', new Krugozor_Module_Captcha_Validator_Captcha(
                $this->getRequest()->getPost('captcha_code'), Krugozor_Session::getInstance()->code
            ));
        }

        $validator->add('text', new Krugozor_Module_Advert_Validator_TextHash(
            $this->advert, $this->getMapper('Advert/Advert')
        ));

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                                 ->setType(Krugozor_Notification::TYPE_ALERT)
                                 ->setMessage($this->getView()->getLang()['notification']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            if ($this->getCurrentUser()->isGuest()) {
                Krugozor_Session::getInstance()->destroy();
            }

            $this->advert->setIdUser($this->getCurrentUser()->getId());
            $this->advert->setUniqueUserCookieId($this->getCurrentUser()->getUniqueCookieId());

            $category = $this->getMapper('Category/Category')->findModelById($this->advert->getCategory());

            if (!$category->getPaid()) {
                $this->advert->setPayment(1);
            }

            if ($this->advert->getId()) {
                $this->advert->setEditDate(new Krugozor_Type_Datetime());
            } else {
                $this->advert->setCurrentCreateDateDiffSecond();
            }

            $this->getMapper('Advert/Advert')->saveModel($this->advert);
            $this->advert->saveThumbnails();

            $kassa = new Krugozor_Module_Freekassa_Service_Freekassa();
            $kassa->setAdvert($this->advert);

            $action_vip = $action_special = $action_payment = null;

            if ($this->advert->getPayment()) {
                $header = null;
                $message = $this->advert->getVipDate() && $this->advert->getVipDate() > new Krugozor_Type_Datetime()
                           ? $this->getView()->getLang()['notification']['message']['advert_save_with_vip']
                           : $this->getView()->getLang()['notification']['message']['advert_save_without_vip'];
                $remove_notification_flag = true;
                $notification_url = '/advert/' . $this->advert->getId() . '.xhtml';

                $action_vip = Krugozor_Module_Freekassa_Service_Freekassa::ACTION_TOP;
                $action_special = Krugozor_Module_Freekassa_Service_Freekassa::ACTION_SPECIAL;
            } else {
                $header = $this->getView()->getLang()['notification']['message']['advert_need_payment_header'];
                $message = $this->getView()->getLang()['notification']['message']['advert_need_payment'];
                $remove_notification_flag = false;
                $notification_url = '/payment/' . $this->advert->getId() . '.xhtml';

                $action_payment = Krugozor_Module_Freekassa_Service_Freekassa::ACTION_ACTIVATE;
            }

            if ($referrer = $this->getRequest()->getRequest('referrer', 'string')) {
                $notification_url = $referrer;
            }

            return $this->createNotification()
                        ->setHeader($header)
                        ->setMessage($message)
                        ->setRemoveNotificationFlag($remove_notification_flag)
                        ->addParam('id', $this->advert->getId())
                        ->addParam('advert_header', $this->advert->getHeader())
                        ->addParam('category_name', $category->getName())
                        ->setNotificationUrl($notification_url)
                        ->addParam('kassa_auth_url_vip', $action_vip ? $kassa->getMerchantUrl($action_vip) : null)
                        ->addParam('kassa_auth_url_special', $action_special ? $kassa->getMerchantUrl($action_special) : null)
                        ->addParam('kassa_auth_url_payment', $action_payment ? $kassa->getMerchantUrl($action_payment) : null)
                        ->run();
        }
    }
}