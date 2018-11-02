<?php

namespace Krugozor\Framework\Module\Category\Helper;

use Krugozor\Cover\CoverArray;

/**
 * Создание древовидного списка подкатегорий раздела на основании дерева
 * с разбиением на столбцы посредством тега table.
 */
class SubcategoriesCols extends Subcategories
{
    /**
     * Количество столбцов в таблице.
     *
     * @var int
     */
    private $num_of_columns = 2;

    /**
     * Максимальное кол-во категорий для вывода в виде обычного списка.
     * При превышении данного количества подкатегории будут разбиваться на столбцы таблицы.
     *
     * @var int
     */
    private static $max_num_categories = 4;

    /**
     * @return string
     */
    public function getHtml(): string
    {
        if ($this->tree->count() <= self::$max_num_categories) {
            return $this->createSubcategories($this->tree);
        } else {
            return $this->createSubcategoriesWithCols($this->tree);
        }
    }

    /**
     * Устанавливает количество столбцов в таблице.
     *
     * @param int $num
     * @return SubcategoriesCols
     */
    public function setNumOfColumns($num): self
    {
        $this->num_of_columns = (int)$num;

        return $this;
    }

    /**
     * Создает список категорий на основе дерева категорий $tree,
     * разбитых на $this->num_of_columns столбцов.
     *
     * @param CoverArray $tree
     * @return string
     */
    private function createSubcategoriesWithCols(CoverArray $tree): string
    {
        if (!$tree->count()) {
            return '';
        }

        $str = '';

        //Сколько в каждом столбце будет категорий
        $count = ceil($this->tree->count() / $this->num_of_columns);

        for ($i = 0; $i < $this->num_of_columns; $i++) {
            $str .= '<td><ul>';

            for ($j = 0; $j < $count; $j++) {
                $index = $i + $j + $i * ($count - 1);

                if ($this->tree->item($index)) {
                    if ($this->current_category && $this->current_category->getId() == $this->tree->item($index)->getId()) {
                        $current_category = '<b>' . $this->tree->item($index)->getName() . '</b>';
                    } else {
                        $current_category = '<a href="' . $this->prefix_url . $this->tree->item($index)->getUrl() . '">' .
                            $this->tree->item($index)->getName() . '</a>';
                    }

                    $str .= '<li>' . $current_category . '&nbsp;<span>[' . $this->tree->item($index)->getAdvertCount() . ']</span>';
                    $str .= $this->createSubcategories($this->tree->item($index)->getTree()) . '</li>';
                }
            }

            $str .= '</ul></td>';
        }

        return "<table><tbody><tr>$str</tr></tbody></table>";
    }
}