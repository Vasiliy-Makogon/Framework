<?php
/**
 * Класс для построения гиперссылок в формате HTML, которые
 * используются в качестве управляющего элемента сортировки
 * по конкретному полю таблицы админ-интерфейса.
 * Класс вызывается вшаблоне в следующем контексте:
 *
 * <td>
 * <?php
 * $linker = new Krugozor_Helper_SortLink()
 *           // имя поля сортировки этой ссылки
 *           ->setFieldName('id')
 *           // якорь ссылки
 *           ->setAnchor('ID пользователя')
 *           // url ссылки
 *           ->setUrl('/admin/user/')
 *           // путь к директории с иконками ASC и DESC
 *           ->setIconSrc('/http/image/system/icon/')
 *           // имя поля, по которому в данный момент проходит сортировка
 *           ->setCurrentFieldName($_REQUEST['field_name'])
 *           // текущий метод сортировки (ASC и DESC)
 *           ->setCurrentSortOrder($_REQUEST['sort_order'])
 *           // дополнительные параметры для Query String
 *           ->setQueryStringFromArray(array(
 *               'search' => '123',
 *           ));
 *
 * echo $linker->getHtml();
 * ?>
 * </td>
 */
class Krugozor_Helper_SortLink
{
    /**
     * URL-адрес гиперссылки.
     *
     * @var string
     */
    protected $url;

    /**
     * Якорь гиперссылки.
     *
     * @var string
     */
    protected $anchor;

    /**
     * Путь к директории с иконками.
     *
     * @var string
     */
    protected $icon_src;

    /**
     * Имена иконок типа сортировки по умолчанию.
     *
     * @var array
     */
    protected $icons_name = array('asc' => 'asc.png', 'desc' => 'desc.png');

    /**
     * Имя поля.
     *
     * @var string
     */
    protected $field_name;

    /**
     * Текущий столбец поля таблицы,
     * по которому происходит сортировка.
     *
     * @var string
     */
    protected $current_field_name;

    /**
     * Текущий порядок сортировки,
     * по которому происходит сортировка.
     *
     * @var string
     */
    protected $current_sort_order;

    /**
     * Параметры QUERY_STRING в виде ассоциативного массива
     * array('key' => 'value'), которые будут добавлены к гиперссылке.
     *
     * @var array
     */
    protected $query_string = array();

    /**
     * @param array массив настроек
     */
    public function __construct(){}

    /**
     * Устанавливает URL ссылки.
     *
     * @param $url
     * @return Krugozor_Helper_SortLink
     */
    public function setUrl($url)
    {
        $this->url = (string) $url;

        return $this;
    }

    /**
     * Устанавливает якорь ссылки.
     *
     * @param $anchor
     * @return Krugozor_Helper_SortLink
     */
    public function setAnchor($anchor)
    {
        $this->anchor = (string) $anchor;

        return $this;
    }

    /**
     * Путь до директории с изображениями-иконками.
     * В данной директории должны лежать два изображения
     * обозначающие порядок сортировки ASC и DESC.
     *
     * @param string
     * @return Krugozor_Helper_SortLink
     */
    public function setIconSrc($icon_src)
    {
        $this->icon_src = (string) $icon_src;

        return $this;
    }

    /**
     * Устанавливает имя столбца, по которому будет происходить сортировка.
     *
     * @param string
     * @return Krugozor_Helper_SortLink
     */
    public function setFieldName($field_name)
    {
        $this->field_name = (string) $field_name;

        return $this;
    }

    /**
     * Имя столбца, по которому в данный момент происходит сортировка.
     * Подразумевается, что $current_field_name берется из запроса.
     *
     * @param $current_field_name
     * @return Krugozor_Helper_SortLink
     */
    public function setCurrentFieldName($current_field_name)
    {
        $this->current_field_name = (string) $current_field_name;

        return $this;
    }

    /**
     * Тип сортировки (ASC или DESC) в данный момент.
     * Подразумевается, что это значение берется из запроса.
     *
     * @param $current_field_name
     * @return Krugozor_Helper_SortLink
     */
    public function setCurrentSortOrder($current_sort_order)
    {
        $this->current_sort_order = strtoupper($current_sort_order);

        return $this;
    }

    /**
     * Принимает ассоциативный массив одного уровня вложенности, который представляет собой
     * набор ключей и значений, из которых будет сформирован QUERY_STRING.
     *
     * @param array $data
     * @return Krugozor_Helper_SortLink
     */
    public function setQueryStringFromArray(array $data)
    {
        $this->query_string = $data;

        return $this;
    }

    /**
     * Возвращает HTML-код ссылки.
     *
     * @param void
     * @return string
     */
    public function getHtml()
    {
        ob_start();
        ?>
        <a href="<?=$this->url?>?<?=$this->makeQueryString()?>"><?=$this->anchor?></a><!--
         --><?php if ($this->current_field_name == $this->field_name): ?>&nbsp;<img alt="<?php echo $this->current_sort_order?>" src="<?php echo $this->icon_src.$this->icons_name[strtolower($this->current_sort_order)]?>" /><?php endif; ?>
        <?php
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Создает QUERY_STRING.
     *
     * @param void
     * @return string
     */
    protected function makeQueryString()
    {
        $data = $this->query_string;

        $data['sort_order'] = $this->current_sort_order == 'DESC' ? 'ASC' : 'DESC';

        $data['field_name'] = $this->field_name;

        return http_build_query($data);
    }
}