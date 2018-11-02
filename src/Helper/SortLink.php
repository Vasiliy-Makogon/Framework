<?php

namespace Krugozor\Framework\Helper;

/**
 * Класс для построения гиперссылок в формате HTML, которые
 * используются в качестве управляющего элемента сортировки
 * по конкретному полю таблицы админ-интерфейса.
 * Класс вызывается вшаблоне в следующем контексте:
 *
 * <?php
 * $linker = new SortLink()
 *     // имя поля сортировки этой ссылки
 *     ->setFieldName('id')
 *     // якорь ссылки
 *     ->setAnchor('ID пользователя')
 *     // url ссылки
 *     ->setUrl('/admin/user/')
 *     // путь к директории с иконками ASC и DESC
 *     ->setIconSrc(\Krugozor\Framework\Registry::getInstance()->APPLICATION['SYSTEM_ICONS'])
 *     // имя поля, по которому в данный момент проходит сортировка
 *     ->setCurrentFieldName($_REQUEST['field_name'])
 *     // текущий метод сортировки (ASC и DESC)
 *     ->setCurrentSortOrder($_REQUEST['sort_order'])
 *     // дополнительные параметры для Query String
 *     ->setQueryStringFromArray(array(
 *         'param1' => 1,
 *         'param2' => 2,
 *     ));
 *
 * echo $linker->getHtml();
 * ?>
 */
class SortLink extends HelperAbstract
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
     * Устанавливает URL ссылки.
     *
     * @param string $url
     * @return SortLink
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Устанавливает якорь ссылки.
     *
      * @param string $anchor
     * @return SortLink
     */
    public function setAnchor(string $anchor): self
    {
        $this->anchor = $anchor;

        return $this;
    }

    /**
     * Путь до директории с изображениями-иконками.
     * В данной директории должны лежать два изображения
     * обозначающие порядок сортировки ASC и DESC.
     *
     * @param string $icon_src
     * @return SortLink
     */
    public function setIconSrc(string $icon_src): self
    {
        $this->icon_src = $icon_src;

        return $this;
    }

    /**
     * Устанавливает имя столбца, по которому будет происходить сортировка.
     *
     * @param string $field_name
     * @return SortLink
     */
    public function setFieldName(string $field_name): self
    {
        $this->field_name = $field_name;

        return $this;
    }

    /**
     * Имя столбца, по которому в данный момент происходит сортировка.
     * Подразумевается, что $current_field_name берется из запроса.
     *
     * @param string $current_field_name
     * @return SortLink
     */
    public function setCurrentFieldName(string $current_field_name): self
    {
        $this->current_field_name = $current_field_name;

        return $this;
    }

    /**
     * Тип сортировки (ASC или DESC) в данный момент.
     * Подразумевается, что это значение берется из запроса.
     *
     * @param string $current_sort_order
     * @return SortLink
     */
    public function setCurrentSortOrder(string $current_sort_order): self
    {
        $this->current_sort_order = strtoupper($current_sort_order);

        return $this;
    }

    /**
     * Принимает ассоциативный массив одного уровня вложенности, который представляет собой
     * набор ключей и значений, из которых будет сформирован QUERY_STRING.
     *
     * @param array $data
     * @return SortLink
     */
    public function setQueryStringFromArray(array $data): self
    {
        $this->query_string = $data;

        return $this;
    }

    /**
     * Возвращает HTML-код ссылки.
     *
     * @return string
     */
    public function getHtml(): string
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
     * @return string
     */
    protected function makeQueryString(): string
    {
        $data = $this->query_string;
        $data['sort_order'] = $this->current_sort_order == 'DESC' ? 'ASC' : 'DESC';
        $data['field_name'] = $this->field_name;

        return http_build_query($data);
    }
}