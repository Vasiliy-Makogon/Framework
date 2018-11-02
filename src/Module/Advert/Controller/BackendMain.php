<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Module\Advert\Service\AdvertList;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Pagination\Adapter;

class BackendMain extends BackendCommon
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
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $list = new AdvertList(
            $this->getRequest(),
            $this->getMapper('Advert/Advert'),
            Adapter::getManager($this->getRequest(), 15, 10)
        );

        $this->getView()->tree = $this->getMapper('Category/Category')->findActiveCategories(2);
        $this->getView()->advertsList = $list->findList();
        $this->getView()->id_category = $this->getRequest()->getRequest('id_category');
        $this->getView()->id_user = $this->getRequest()->getRequest('id_user');

        // В случае когда ID категории передается методом get,
        // в двухуровневое дерево $this->getView()->tree добавляем путь к указанному узлу
        // для корректного вывода в select-списке.
        if ($this->getRequest()->getRequest('id_category')) {
            $path_to_category = $this->getMapper('Category/Category')->loadPath($this->getRequest()->getRequest('id_category'));

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

        return $this->getView();
    }
}