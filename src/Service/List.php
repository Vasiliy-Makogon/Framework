<?php

/**
 * Сервис получения списка записей на основе Request-данных, таких как сортировка, лимитирование и пр.
 * Пример испольования:
 *
 * $list = new Krugozor_Module_Advert_Service_List(
 *    $this->getRequest(),
 *    $this->getMapper('Advert/Advert'),
 *    Krugozor_Pagination_Adapter::getManager($this->getRequest(), 15, 10)
 *);
 */
abstract class Krugozor_Service_List
{
    /**
     * @var Krugozor_Http_Request
     */
    protected $request;

    /**
     * @var Krugozor_Mapper
     */
    protected $mapper;

    /**
     * @var \Krugozor\Pagination\Manager
     */
    protected $pagination;

    /**
     * Буфер для строк SQL запроса на условие WHERE.
     *
     * @var array
     */
    protected $sql_where_string_buffer = array();

    /**
     * Буфер для аргументов SQL запроса на условие WHERE.
     *
     * @var array
     */
    protected $sql_where_args_buffer = array();

    /**
     * Параметры сортировки по-умолчанию.
     * Указывается алиас поля и порядок сортировки.
     *
     * @var array
     */
    protected $default_order_options = array
    (
        'field_name' => 'id',
        'sort_order' => 'DESC',
    );

    /**
     * Массив перечислений объявляемых в конкретном классе, где ключом является алиас из запроса,
     * а значением - имя реального столбца в БД в виде '`coil_name`' или '`table_name`.`col_name`'.
     *
     * @var array
     */
    protected $order_options = array('id' => 'id');

    /**
     * Список найденных записей.
     *
     * @var Krugozor_Cover_Array
     */
    protected $list;

    /**
     * @param object Krugozor_Http_Request $request
     * @param object Krugozor_Mapper $mapper
     * @param object \Krugozor\Pagination\Manager $pagination
     */
    public function __construct(Krugozor_Http_Request $request,
                                Krugozor_Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->request = $request;
        $this->mapper = $mapper;
        $this->pagination = $pagination;

        $this->list = new Krugozor_Cover_Array();

        $this->declareDefaultSortOptions();
    }

    /**
     * Находит список записей.
     *
     * @abstract
     * @param void
     * @return Krugozor_Service_List
     */
    abstract public function findList();

    /**
     * Возвращает объект менеджера пагинации.
     *
     * @param void
     * @return \Krugozor\Pagination\Manager
     */
    final public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Возвращает список найденных записей.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    final public function getList()
    {
        return $this->list;
    }

    /**
     * Возвращает реальное имя поля сортировки.
     *
     * @param void
     * @return string
     */
    public function getFieldName()
    {
        return isset($this->order_options[$this->request->getRequest('field_name')])
            ? $this->order_options[$this->request->getRequest('field_name')]
            : $this->default_order_options['field_name'];
    }

    /**
     * Возвращает алиас поля сортировки.
     *
     * @param void
     * @return string
     */
    public function getAlias()
    {
        return isset($this->order_options[$this->request->getRequest('field_name')])
            ? $this->request->getRequest('field_name')
            : $this->default_order_options['field_name'];
    }

    /**
     * Возвращает порядок сортировки.
     *
     * @param void
     * @return string
     */
    public function getOrder()
    {
        switch ($this->request->getRequest('sort_order')) {
            case 'ASC':
                return 'ASC';

            case 'DESC':
            default:
                return 'DESC';
        }
    }

    /**
     * Создает универсальный массив параметров для передачи в Mapper.
     *
     * @param void
     * @return array
     */
    protected function createParams()
    {
        $params['order'][$this->getFieldName()] = $this->getOrder();

        $params['limit'] = array('start' => $this->pagination->getStartLimit(),
            'stop' => $this->pagination->getStopLimit());

        if ($this->sql_where_string_buffer && $this->sql_where_args_buffer) {
            $params['where'][implode(' AND ', $this->sql_where_string_buffer)] = $this->sql_where_args_buffer;
        }

        return $params;
    }

    /**
     * В случае, если в Request не определены параметры сортировки, то определяим их
     * согласно значениям по умолчанию ($this->default_order_options).
     *
     * @param void
     * @return void
     */
    private function declareDefaultSortOptions()
    {
        foreach ($this->default_order_options as $key => $value) {
            if (!isset($this->request->getRequest()->$key)) {
                $this->request->getRequest()->$key = $value;
            }
        }
    }
}