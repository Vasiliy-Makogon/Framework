<?php

class Krugozor_Module_Group_Controller_BackendEdit extends Krugozor_Module_Group_Controller_BackendCommon
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Controller::run()
     */
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral',
            'Group/BackendCommon',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/group/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->group)) {
            $this->group = $this->getMapper('Group/Group')->createModel();
        }

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->group = $this->group;
        $this->getView()->modules = $this->getMapper('Module/Module')->findModelListByParams();
        $this->getView()->return_on_page = $this->getRequest()->getRequest('return_on_page');

        return $this->getView();
    }

    protected function post()
    {
        // Второй параметр - исключаем возможность записать из POST-запроса денормализованный массив прав.
        $this->group->setData($this->getRequest()->getPost('group'), array('access'));

        $validator = new Krugozor_Validator('common/general');
        $validator->addModelErrors($this->group->getValidateErrors());

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification->setType(Krugozor_Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('Group/Group')->saveModel($this->group);

            return $notification
                ->setMessage($this->getRequest()->getRequest('return_on_page')
                    ? $this->getView()->getLang()['notification']['message']['group_edit_ok_no_link']
                    : $this->getView()->getLang()['notification']['message']['group_edit_ok'])
                ->addParam('group_name', $this->group->getName())
                ->addParam('id', $this->group->getId())
                ->setNotificationUrl($this->getRequest()->getRequest('return_on_page')
                    ? '/group/backend-edit/?id=' . $this->group->getId()
                    : ($this->getRequest()->getRequest('referer') ?: '/group/backend-main/')
                )
                ->run();
        }
    }
}