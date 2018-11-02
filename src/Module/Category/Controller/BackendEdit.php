<?php

namespace Krugozor\Framework\Module\Category\Controller;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Validator;

class BackendEdit extends BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/category/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->category)) {
            $this->category = $this->getMapper('Category/Category')->createModel();
            $this->category->setPid(
                $this->getRequest()->getRequest('pid', 'decimal')
            );
        }

        if (Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->tree = $this->getMapper('Category/Category')->findActiveCategories();
        $this->getView()->category = $this->category;
        $this->getView()->return_on_page = $this->getRequest()->getRequest('return_on_page');

        return $this->getView();
    }

    protected function post()
    {
        if (!$this->getRequest()->getPost('category')->alias) {
            $this->getRequest()->getPost('category')->alias = $this->getRequest()->getPost('category')->name;
        }

        $this->category->setData($this->getRequest()->getPost('category'));

        if (!$this->category->getId()) {
            $parent = $this->getMapper('Category/Category')->findModelById($this->category->getPid());
            $this->category->setIndent($parent->getIndent() + 1);
        }

        $validator = new Validator('common/general');
        $validator->addModelErrors($this->category->getValidateErrors());
        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('Category/Category')->save($this->category);

            $url =
                $this->getRequest()->getRequest('return_on_page')
                    ? '/category/backend-edit/?id=' . $this->category->getId()
                    : ($this->getRequest()->getRequest('referer') ?: '/category/backend-main/');

            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl($url)
                ->run();
        }
    }
}