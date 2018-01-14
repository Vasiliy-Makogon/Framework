<?php
/**
 * Создание древовидного списка подкатегорий раздела на основании дерева с разбиением на столбцы посредством тега table.
 */
class Krugozor_Module_Category_Helper_SubcategoriesCols extends Krugozor_Module_Category_Helper_Subcategories
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
     * (non-PHPdoc)
     * @see Krugozor_Module_Category_Helper_Subcategories::getHtml()
     */
    public function getHtml()
    {
        if ($this->tree->count() <= self::$max_num_categories)
        {
            return $this->createSubcategories($this->tree);
        }
        else
        {
            return $this->createSubcategoriesWithCols($this->tree);
        }
    }

    /**
     * Устанавливает количество столбцов в таблице.
     *
     * @param int $num
     * @return Krugozor_Module_Category_Helper_SubcategoriesCols
     */
    public function setNumOfColumns($num)
    {
        $this->num_of_columns = (int)$num;

        return $this;
    }

    /**
     * Создает список категорий на основе дерева категорий $tree, разбитых на $this->num_of_columns столбцов.
     *
     * @param Krugozor_Cover_Array $tree
     * @return string
     */
    private function createSubcategoriesWithCols(Krugozor_Cover_Array $tree)
    {
        if (!$tree->count())
        {
            return '';
        }

        $str = '';

        //Сколько в каждом столбце будет категорий
        $count = ceil($this->tree->count() / $this->num_of_columns);

        for ($i=0; $i < $this->num_of_columns; $i++)
        {
            $str .= '<td><ul>';

            for ($j = 0; $j < $count; $j++)
            {
                $index = $i + $j + $i * ($count-1);

                if ($this->tree->item($index))
                {
                    if ($this->current_category && $this->current_category->getId() == $this->tree->item($index)->getId())
                    {
                        $current_category = '<b>' . $this->tree->item($index)->getName() .'</b>';
                    }
                    else
                    {
                        $current_category = '<a href="' . $this->prefix_url . $this->tree->item($index)->getUrl() . '">' .
                                            $this->tree->item($index)->getName() . '</a>';
                    }

                    $str .= '<li>' . $current_category . '&nbsp;<span>[' . $this->tree->item($index)->getAdvertCount() . ']</span>';
                    $str .= $this->createSubcategories($this->tree->item($index)->getTree()).'</li>';
                }
            }

            $str .= '</ul></td>';
        }

        return "<table><tbody><tr>$str</tr></tbody></table>";
    }
}