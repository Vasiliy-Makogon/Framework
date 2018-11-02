<?php

namespace Krugozor\Framework\Module\Category\Helper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Helper\Format;
use Krugozor\Framework\Helper\HelperAbstract;
use Krugozor\Framework\Registry;
use Krugozor\Framework\View;

class TreeBackendTable extends HelperAbstract
{
    /**
     * @var CoverArray
     */
    private $tree;

    /**
     * @var View
     */
    private $view;

    /**
     * TreeBackendTable constructor.
     * @param CoverArray $tree
     * @param View $view
     */
    public function __construct(CoverArray $tree, View $view)
    {
        $this->tree = $tree;
        $this->view = $view;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->createRows($this->tree);
    }

    /**
     * @param CoverArray $tree
     * @return string
     */
    private function createRows(CoverArray $tree): string
    {
        $str = '';

        foreach ($tree as $item) {
            ob_start();
            ?>
            <tr id="category_<?= $item->getId() ?>" class="color_hover">
                <td class="center" id="category_<?= $item->getId() ?>"><?= $item->getId() ?></td>
                <td class="center">
                    <? if ($item->getPaid()): ?>
                        <?= $this->view->getLang()['content']['yes'] ?>
                    <? else: ?>
                        <span class="lighttext"><?= $this->view->getLang()['content']['no'] ?></span>
                    <? endif; ?>
                </td>
                <td style="padding-left:<?= ($item->getIndent() * 15) ?>px; <? if (!$item->getPid()) : ?>font-weight:bold;<? endif; ?>"><?= Format::hsc($item->getName()) ?>
                    (<a class="external" target="_blank"
                        href="/catalog<?= $item->getUrl() ?>"><?= $item->getAdvertCount() ?></a>)
                </td>
                <td class="td_actions"><a
                            href="/category/backend-edit/?id=0&amp;pid=<?= $item->getId() ?>&amp;referer=<?= $this->view->getRequest()->getRequestUri()->getUrlencodeUriValue() ?>#category_<?= $item->getPid() ?>"><img
                                src="<?= Registry::getInstance()->APPLICATION['SYSTEM_ICONS'] ?>add.png" alt=""/></a>
                </td>
                <td class="td_actions"><a
                            href="/category/backend-add-list/?pid=<?= $item->getId() ?>&amp;referer=<?= $this->view->getRequest()->getRequestUri()->getUrlencodeUriValue() ?>#category_<?= $item->getPid() ?>"><img
                                src="<?= Registry::getInstance()->APPLICATION['SYSTEM_ICONS'] ?>add.png" alt=""/></a>
                </td>
                <td class="td_actions"><a
                            href="/category/backend-edit/?id=<?= $item->getId() ?>&amp;pid=<?= $item->getPid() ?>&amp;referer=<?= $this->view->getRequest()->getRequestUri()->getUrlencodeUriValue() ?>#category_<?= $item->getPid() ?>"><img
                                alt="" src="<?= Registry::getInstance()->APPLICATION['SYSTEM_ICONS'] ?>edit.png"/></a>
                </td>
                <td class="td_actions">
                    <?php if ($item->getAdvertCount() || $item->getAllChilds()): ?>
                        <img title="Невозможно удалить категорию, пока в ней есть элементы или категории-потомки"
                             src="<?= Registry::getInstance()->APPLICATION['SYSTEM_ICONS'] ?>delete_empty.png" alt=""/>
                    <?php else: ?>
                        <?php
                        $msg = 'Вы действительно хотите удалить категорию &laquo;{title}&raquo; (id: {id})?';
                        $data = ['title' => $item->getName(), 'id' => $item->getId()];
                        $msg = Format::js($msg, $data);
                        ?>
                        <a onclick="return confirm('<?= $msg ?>')"
                           href="/category/backend-delete/?id=<?= $item->getId() ?>&amp;referer=<?= $this->view->getRequest()->getRequestUri()->getUrlencodeUriValue() ?>#category_<?= $item->getPid() ?>">
                            <img src="<?= Registry::getInstance()->APPLICATION['SYSTEM_ICONS'] ?>delete.png" alt=""/>
                        </a>
                    <?php endif; ?>
                </td>
                <td class="td_actions"><a
                            href="/category/backend-motion/?tomotion=up&amp;id=<?= $item->getId() ?>&amp;pid=<?= $item->getPid() ?>&amp;referer=<?= $this->view->getRequest()->getRequestUri()->getUrlencodeUriValue() ?>#category_<?= $item->getPid() ?>"><img
                                src="<?= Registry::getInstance()->APPLICATION['SYSTEM_ICONS'] ?>up.gif"
                                title="Поднять запись на одну позицию выше" alt=""/></a></td>
                <td class="td_actions"><a
                            href="/category/backend-motion/?tomotion=down&amp;id=<?= $item->getId() ?>&amp;pid=<?= $item->getPid() ?>&amp;referer=<?= $this->view->getRequest()->getRequestUri()->getUrlencodeUriValue() ?>#category_<?= $item->getPid() ?>"><img
                                src="<?= Registry::getInstance()->APPLICATION['SYSTEM_ICONS'] ?>down.gif" alt=""/></a>
                </td>
            </tr>
            <?php
            $str .= ob_get_contents();
            ob_end_clean();

            if ($item->getTree() && $item->getTree()->count()) {
                $str .= $this->createRows($item->getTree());
            }
        }

        return $str;
    }
}