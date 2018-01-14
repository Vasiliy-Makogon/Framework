<?php
abstract class Krugozor_Module_Category_Controller_BackendCommon extends Krugozor_Controller
{
    /**
     * @var Krugozor_Module_Category_Model_Category
     */
    protected $category;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                            ->setNotificationUrl('/category/backend-main/')
                            ->run();
            }

            $this->category = $this->getMapper('Category/Category')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->category->getId()) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                            ->setNotificationUrl('/category/backend-main/')
                            ->run();
            }
        }
    }
}