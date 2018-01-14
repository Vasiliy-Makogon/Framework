<?php
class Krugozor_Module_Category_Model_Category extends Krugozor_Model
{
    protected static $db_field_prefix = 'category';

    /**
     * Дерево подкатегорий данного узла.
     *
     * @var Krugozor_Cover_Array
     */
    protected $tree;

    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'db_field_name' => 'id',
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'pid' => array(
            'db_element' => true,
            'db_field_name' => 'pid',
            'default_value' => 0,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'active' => array(
            'db_element' => true,
            'db_field_name' => 'category_active',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'name' => array(
            'db_element' => true,
            'db_field_name' => 'category_name',
            'default_value' => null,
            'validators' => array(
                'IsNotEmpty' => array(),
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::VARCHAR_MAX_LENGTH),
            )
        ),

        'alias' => array(
            'db_element' => true,
            'db_field_name' => 'category_alias',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::VARCHAR_MAX_LENGTH),
            )
        ),

        'url' => array(
            'db_element' => true,
            'db_field_name' => 'category_url',
            'default_value' => '_temp_',
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::VARCHAR_MAX_LENGTH),
            )
        ),

        'description' => array(
            'db_element' => true,
            'db_field_name' => 'category_description',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => 3000),
            )
        ),

        'text' => array(
            'db_element' => true,
            'db_field_name' => 'category_text',
            'default_value' => null,
            'validators' => [],
        ),

        'keywords' => array(
            'db_element' => true,
            'db_field_name' => 'category_keywords',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => 3000),
            )
        ),

        /* Количество объявлений в этой категории */
        'advert_count' => array(
            'db_element' => false,
            'db_field_name' => 'category_advert_count',
            'default_value' => 0,
            'record_once' => true,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        /* Выводить на главной странице объявления из этой категории */
        'show_on_index' => array(
            'db_element' => true,
            'db_field_name' => 'category_show_on_index',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        /* Выводить ли эту категорию при открытии прародителя */
        'show_on_grandparent' => array(
            'db_element' => true,
            'db_field_name' => 'category_show_on_grandparent',
            'default_value' => 0,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        /* Уровень вложенности. Для родительских узлов уровень начинается с 1 */
        'indent' => array(
            'db_element' => true,
            'db_field_name' => 'category_indent',
            'default_value' => -1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => true),
            )
        ),

        /* Идентификаторы прямых потомков данного узла */
        'childs' => array(
            'db_element' => true,
            'db_field_name' => 'category_childs',
            'default_value' => null,
            'validators' => array()
        ),

        /* Идентификаторы прямых потомков данного узла, которые будут выведены при открытии родительского узла */
        'view_childs' => array(
            'db_element' => true,
            'db_field_name' => 'category_view_childs',
            'default_value' => null,
            'validators' => array()
        ),

        /* Идентификаторы всех потомков данного узла */
        'all_childs' => array(
            'db_element' => true,
            'db_field_name' => 'category_all_childs',
            'default_value' => null,
            'validators' => array()
        ),

        /* Платная категория или нет */
        'paid' => array(
            'db_element' => true,
            'db_field_name' => 'category_paid',
            'default_value' => 0,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),
    );

    public function __construct()
    {
        parent::__construct();

        $this->tree = new Krugozor_Cover_Array();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return trim($this->description, '.');
    }

    /**
     * Возвращает true, если эта категория первого уровня.
     *
     * @param void
     * @return bool
     */
    public function isTopCategory()
    {
        return $this->getPid() == 0;
    }

    /**
     * Возвращает дерево подкатегорий данного узла.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * Присваивает дерево подкатегорий данному узлу.
     *
     * @param Krugozor_Cover_Array
     * @return Krugozor_Module_Category_Model_Category
     */
    public function setTree(Krugozor_Cover_Array $tree)
    {
        $this->tree = $tree;

        return $this;
    }

    /**
     * Возвращает свойство $this->data[childs] как массив идентификаторов.
     *
     * @param void
     * @return array
     */
    public function getChildsAsArray()
    {
        if (!$this->getChilds())
        {
            return array();
        }

        return explode(',', $this->getChilds());
    }

    /**
     * Возвращает свойство $this->data[view_childs] как массив идентификаторов.
     *
     * @param void
     * @return array
     */
    public function getViewChildsAsArray()
    {
        if (!$this->getViewChilds())
        {
            return array();
        }

        return explode(',', $this->getViewChilds());
    }

    /**
     * Возвращает массив всех потомков узла на основании строки-свойства $this->data[all_childs].
     *
     * @param void
     * @return array
     */
    public function getAllChildsAsArray()
    {
        if (!$this->getAllChilds())
        {
            return array();
        }

        return explode(',', $this->getAllChilds());
    }

    /**
     * Возвращает имя категории для HTML элемента option
     * с иммитацией padding-left в виде многоточия.
     *
     * @param int $repeat
     * @return string
     */
    public function getNameForOptionElement($repeat=3)
    {
        return str_repeat('.', $this->getIndent() * $repeat) . $this->getName();
    }

    /**
     * Возвращает URL родительской категории.
     *
     * @param void
     * @return string
     */
    public function getParentCategoryUrl()
    {
        $parent = dirname($this->getUrl());

        if ($parent == DIRECTORY_SEPARATOR)
        {
            return '/';
        }

        return $parent . "/";
    }

    /**
     * Создает URL-адрес на основании массива $tree
     * который является деревом-путём.
     *
     * @param array
     */
    public function setUrlFromTreePath(Krugozor_Cover_Array $tree)
    {
        $aliases = self::getAliasesFromTree($tree);

        // Поскольку levels берутся из базы, то последний элемент -
        // alias данной категории может отличаться от текущего $this->alias.
        // Перезаписываем последний элемент level на актуальный $this->alias
        if ($this->alias)
        {
            $aliases[count($aliases)-1] = $this->alias;
        }

        $this->setUrl('/' . implode('/', $aliases) . '/');
    }


    /**
     * Извлекает из каждого элемента дерева значение с помощью метода
     * $method_name и помещает его в результирующий массив-список.
     * Подразумевается, что в каждом элементе дерева есть метод $method_name.
     *
     * @param Cover_Array $tree дерево объектов, из которых необходимо получать значение
     * @param string $method_name имя get-метода получения свойства объекта
     * @return array
     */
    public static function getElementsInTree($tree, $method_name)
    {
        $data = array();

        foreach ($tree as $element)
        {
            $data[] = $element->$method_name();

            if ($element->getTree() && $element->getTree()->count())
            {
                $data = array_merge($data, self::getElementsInTree($element->getTree(), $method_name));
            }
        }

        return $data;
    }

    /**
     * Устанавливает локальный URL категории, транслитилируя значение.
     * explicit-метод.
     *
     * @param string $alias имя категории
     * @return string имя категории в транслите
     */
    protected function _setAlias($alias)
    {
        return Krugozor_Static_Translit::UrlTranslit($alias);
    }

    /**
     * Установка ключевых слов с сортировкой и отсеиванием дубликатов.
     * explicit-метод.
     *
     * @param string $value
     * @return NULL|string
     */
    protected function _setKeywords($value)
    {
        if (!$value)
        {
            return null;
        }

        $data = explode(',', $value);

        $keywords = array();

        foreach ($data as $word)
        {
            if ($word = mb_strtolower(trim($word)))
            {
                $keywords[] = $word;
            }
        }

        sort($keywords);

        return implode(', ', array_unique($keywords, SORT_STRING));
    }

    /**
     * Возвращает массив, состоящий из алиасов узлов дерева-пути.
     *
     * @param Cover_Array
     * @return array
     * @todo: заменить вызов на getElementsInTree
     */
    protected static function getAliasesFromTree(Krugozor_Cover_Array $tree)
    {
        if (!$tree instanceof Krugozor_Cover_Array || !$tree->count())
        {
            return false;
        }

        $aliases = array();

        foreach ($tree as $category)
        {
            $aliases[] = $category->getAlias();

            if ($category->getTree() && $category->getTree()->count())
            {
                $aliases = array_merge($aliases, self::getAliasesFromTree($category->getTree()));
            }
        }

        return $aliases;
    }
}