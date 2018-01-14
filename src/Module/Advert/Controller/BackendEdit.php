<?php
class Krugozor_Module_Advert_Controller_BackendEdit extends Krugozor_Module_Advert_Controller_BackendCommon
{
    /**
     * @var Krugozor_Module_Advert_Model_Advert
     */
    private $user;

    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/advert/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->advert)) {
            $this->advert = $this->getMapper('Advert/Advert')->createModel($this->getMapper('User/User')->findModelById(-1));
        } else {
            // Если администратор просмотрел объявление, то делаем отметку для административной части.
            if ($this->getCurrentUser()->isAdministrator() && !$this->advert->getWasModerated()) {
                $this->advert->setWasModerated(1);
                $this->getMapper('Advert/Advert')->saveModel($this->advert);
            }
        }

        $this->user = $this->getMapper('User/User')->findModelById($this->advert->getIdUser());

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
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

        $this->getView()->advert = $this->advert;
        $this->getView()->user = $this->user;
        $this->getView()->return_on_page = $this->getRequest()->getRequest('return_on_page');
        $this->getView()->max_file_size = Krugozor_Utility_Upload_File::getBytesFromString(
            Krugozor_Registry::getInstance()->UPLOAD['MAX_FILE_SIZE']
        );

        return $this->getView();
    }

    protected function post()
    {
        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl($this->getRequest()->getUri()->getSimpleUriValue() . '?id=' . $this->advert->getId())
                ->run();
        }

        $this->advert->setData($this->getRequest()->getPost('advert')->getDataAsArray());

        // Добавление объектов изображений в объект объявления на основе массива идентификаторов изображений из формы.
        if ($this->getRequest()->getPost('thumbnail')) {
            $this->advert->loadThumbnailsListByIds($this->getRequest()->getPost('thumbnail'));
        }

        $validator = new Krugozor_Validator('common/general', 'advert/edit', 'user/common');

        if ($this->user->isGuest() AND
            !$this->advert->getIcq() && !$this->advert->getPhone() &&
            !$this->advert->getEmail()->getValue() && !$this->advert->getUrl()) {
                $validator->addError('contact_info', 'EMPTY_CONTACT_INFO');
        }

        // Проверка на затирание special-даты администратором в момент редактирования только что поданного объявления.
        if (!$this->advert->getSpecialDate() && $this->advert->getTrack()->special_date) {
            if ($this->advert->getTrack()->special_date->getTimestamp() > (new Krugozor_Type_Datetime())->getTimestamp()) {
                $validator->addError('special_date', 'AFFECT_REWRITE_SPECIAL_DATE_NULL_VALUE');
                $this->advert->setSpecialDate($this->advert->getTrack()->special_date);
            }
        }

        // Проверка на затирание vip-даты администратором в момент редактирования только что поданного объявления.
        if (!$this->advert->getVipDate() && $this->advert->getTrack()->vip_date) {
            if ($this->advert->getTrack()->vip_date->getTimestamp() > (new Krugozor_Type_Datetime())->getTimestamp()) {
                $validator->addError('vip_date', 'AFFECT_REWRITE_VIP_DATE_NULL_VALUE');
                $this->advert->setVipDate($this->advert->getTrack()->vip_date);
            }
        }

        $validator->addModelErrors($this->advert->getValidateErrors());

        if (!$this->advert->getValidateErrorsByKey('id_user')) {
            $validator->add('id_user', new Krugozor_Module_User_Validator_UserIdExists(
                $this->advert->getIdUser(), $this->getMapper('User/User')
            ));
        }

        $validator->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification->setType(Krugozor_Notification::TYPE_ALERT)
                         ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            if (!$this->advert->getId()) {
                $this->advert->setCurrentCreateDateDiffSecond();
            } else {
                $this->advert->setEditDate(new Krugozor_Type_Datetime());
            }

            // Администратор перенес объявление в другой раздел каталога,
            // информируем об этом пользователя.
            if (!$this->advert->getTrack()->compare('category', $this->advert->getCategory())) {
                $path = $this->getMapper('Category/Category')->loadPath($this->advert->getCategory());
                $bread_crumbs = new Krugozor_Module_Category_Helper_BreadCrumbs($path, '', '/');
                $bread_crumbs->setOnlyPlainText(true)->addFirstSeparator(false);

                if ($this->advert->getIdUser() != Krugozor_Module_User_Model_User::GUEST_USER_ID) {
                    $user = $this->getMapper('User/User')->findModelById($this->advert->getIdUser());

                    if (!$user->getEmail()->getValue() && $this->advert->getEmail()->getValue()) {
                        $user->setEmail($this->advert->getEmail());
                    }
                } else {
                    $user = $this->getMapper('User/User')->createModel();
                    $user->setFirstName($this->advert->getUserName());
                    $user->setEmail($this->advert->getEmail());
                }

                if ($user->getEmail()->getValue()) {
                    $mail = new Krugozor_Mail();
                    $mail->setTo($user->getEmail()->getValue());
                    $mail->setFrom(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                    $mail->setReplyTo(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                    $mail->setHeader($this->getView()->getLang()['mail']['header']['send_mail_advert_transfer']);
                    $mail->setTemplate($this->getRealLocalTemplatePath('AdvertTransfer'));
                    $mail->bread_crumbs = $bread_crumbs->getHtml();
                    $mail->user = $user;
                    $mail->advert = $this->advert;
                    $mail->host = Krugozor_Registry::getInstance()->HOSTINFO['HOST'];
                    $mail->send();
                }
            }

            $this->getMapper('Advert/Advert')->saveModel($this->advert);
            $this->advert->saveThumbnails();

            return $notification->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                                ->setNotificationUrl(
                                    $this->getRequest()->getRequest('return_on_page')
                                    ? '/advert/backend-edit/?id=' . $this->advert->getId()
                                    : ($this->getRequest()->getRequest('referer') ?: '/advert/backend-main/')
                                )
                                ->run();
        }
    }
}