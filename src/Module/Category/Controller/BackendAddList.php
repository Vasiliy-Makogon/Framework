<?php
class Krugozor_Module_Category_Controller_BackendAddList extends Krugozor_Controller
{
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
                        ->setNotificationUrl('/category/backend-main/')
                        ->run();
        }

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->tree = $this->getMapper('Category/Category')->findActiveCategories();

        return $this->getView();
    }

    protected function post()
    {
        if ($id = $this->getRequest()->getRequest('data')->category) {
            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                            ->setNotificationUrl('/category/backend-main/')
                            ->run();
            }

            $parent_category = $this->getMapper('Category/Category')->findModelById($id);

            if (!$parent_category->getId()) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                            ->setNotificationUrl('/category/backend-main/')
                            ->run();
            }
        } else {
            return $this->createNotification()
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setMessage($this->getView()->getLang()['notification']['message']['id_element_not_exists'])
                        ->setNotificationUrl('/category/backend-main/')
                        ->run();
        }

        if (!$this->getRequest()->getPost('data')->list) {
            return $this->createNotification()
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setMessage($this->getView()->getLang()['notification']['message']['list_is_empty'])
                        ->setNotificationUrl('/category/backend-main/')
                        ->run();
        }

        $categories = explode("\n", $this->getRequest()->getPost('data')->list);
        $categories = array_map('trim', $categories);
        sort($categories);
        $categories = array_reverse($categories);

        foreach ($categories as $category_name) {
            if ($category_name === '') {
                continue;
            }

            $category = $this->getMapper('Category/Category')->createModel();
            $category->setName($category_name);
            $category->setAlias($category_name);
            $category->setPid($parent_category->getId());
            $category->setIndent($parent_category->getIndent() + 1);
            $this->getMapper('Category/Category')->save($category);
        }

        return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_NORMAL)
                    ->setMessage($this->getView()->getLang()['notification']['message']['categories_added'])
                    ->setNotificationUrl($this->getRequest()->getRequest('referer') ?: '/category/backend-main/')
                    ->run();
    }
}