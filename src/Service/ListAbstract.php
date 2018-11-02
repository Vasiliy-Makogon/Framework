<?php

namespace Krugozor\Framework\Service;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Mapper;

/**
 * Сервис получения списка записей на основе Request-данных, таких как сортировка, лимитирование и пр.
 * Пример испольования:
 *
 * $list = new List(
 *    $this->getRequest(),
 *    $this->getMapper('Advert/Advert'),
 *    Krugozor\Framework\Pagination\Adapter::getManager($this->getRequest(), 15, 10)
 *);
 */
abstract class ListAbstract
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Mapper
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
     * @var CoverArray
     */
    protected $list;

    /**
     * ListAbstract constructor.
     * @param Request $request
     * @param Mapper $mapper
     * @param \Krugozor\Pagination\Manager $pagination
     */
    public function __construct(Request $request,
                                Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->request = $request;
        $this->mapper = $mapper;
        $this->pagination = $pagination;

        $this->list = new CoverArray();

        $this->declareDefaultSortOptions();
    }

    /**
     * Находит список записей.
     *
     * @return ListAbstract
     */
    abstract public function findList(): ListAbstract;

    /**
     * Возвращает объект менеджера пагинации.
     *
     * @return \Krugozor\Pagination\Manager
     */
    final public function getPagination(): \Krugozor\Pagination\Manager
    {
        return $this->pagination;
    }

    /**
     * Возвращает список найденных записей.
     *
     * @return CoverArray
     */
    final public function getList(): CoverArray
    {
        return $this->list;
    }

    /**
     * Возвращает реальное имя поля сортировки.
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return
            isset($this->order_options[$this->request->getRequest('field_name')])
            ? $this->order_options[$this->request->getRequest('field_name')]
            : $this->default_order_options['field_name'];
    }

    /**
     * Возвращает алиас поля сортировки.
     *
     * @return string
     */
    public function getAlias(): string
    {
        return
            isset($this->order_options[$this->request->getRequest('field_name')])
            ? $this->request->getRequest('field_name')
            : $this->default_order_options['field_name'];
    }

    /**
     * Возвращает порядок сортировки.
     *
     * @return string
     */
    public function getOrder(): string
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
     * @return array
     */
    protected function createParams(): array
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