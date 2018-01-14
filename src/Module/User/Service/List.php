<?php

class Krugozor_Module_User_Service_List extends Krugozor_Service_List
{
    /**
     * Поля таблицы, содержащие ID территорий.
     *
     * @var array
     */
    private $territory = array
    (
        'user_city',
        'user_region',
        'user_country'
    );

    /**
     * Поля таблицы, по которым проходит поиск.
     *
     * @var array
     */
    private $text_search_cols = array
    (
        '`user`.`id`',
        '`user`.`user_login`',
        '`user`.`user_first_name`',
        '`user`.`user_last_name`',
        '`user`.`user_email`',
        '`user`.`user_url`',
        '`user`.`user_icq`'
    );

    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_List::__construct()
     */
    public function __construct(Krugozor_Http_Request $request,
                                Krugozor_Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->order_options['id'] = 'user.id';
        $this->order_options['first_name'] = 'user.user_first_name';
        $this->order_options['ip'] = 'user.user_ip';

        parent::__construct($request, $mapper, $pagination);
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_List::findList()
     */
    public function findList()
    {
        $this->processTerritirySearchCondition();
        $this->processUserActiveSearchCondition();
        $this->processTextSearchCondition();
        $this->processAnonymousExceptionCondition();

        $this->list = $this->mapper->getUsersListWithResidence($this->createParams());
        $this->pagination->setCount($this->mapper->getFoundRows());

        return $this;
    }

    /**
     * Установка параметров поиска по анонимным пользователям для условия WHERE.
     *
     * @param void
     * @return void
     */
    private function processAnonymousExceptionCondition()
    {
        $this->sql_where_string_buffer[] = '`user`.`id` <> ?i';
        $this->sql_where_args_buffer[] = -1;
    }

    /**
     * Установка параметров поиска по строке для условия WHERE.
     *
     * @param void
     * @return void
     */
    private function processTextSearchCondition()
    {
        if ($this->request->getRequest('search', 'string') !== '') {
            $search = urldecode($this->request->getRequest()->item('search'));

            if ($this->request->getRequest()->item('col') != 'id_user') {
                if ($this->request->getRequest()->item('col') == 'all') {
                    $this->sql_where_string_buffer[] = 'CONCAT_WS(",", ' .
                        implode(', ', $this->text_search_cols) .
                        ') LIKE "%?s%"';
                    $this->sql_where_args_buffer[] = $search;
                } else {
                    $this->sql_where_string_buffer[] = $this->request->getRequest()->item('col') . ' LIKE "%?s%"';
                    $this->sql_where_args_buffer[] = $search;
                }
            } else if ($this->request->getRequest()->item('col') == 'id_user' && $id_user = $this->request->getRequest('search', 'decimal')) {
                $this->sql_where_string_buffer[] = '`user`.`id` = ?i';
                $this->sql_where_args_buffer[] = $id_user;
            }
        }
    }

    /**
     * Установка параметров поиска по активности пользователя для условия WHERE.
     *
     * @param void
     * @return void
     */
    private function processUserActiveSearchCondition()
    {
        if (Krugozor_Static_Numeric::is_decimal($this->request->getRequest('user_active'))) {
            $this->sql_where_string_buffer[] = '`user_active` = ?i';
            $this->sql_where_args_buffer[] = $this->request->getRequest('user_active', 'decimal');
        }
    }

    /**
     * Установка параметров поиска по территориям для условия WHERE.
     *
     * @param void
     * @return void
     */
    private function processTerritirySearchCondition()
    {
        foreach ($this->territory as $territory_field_name) {
            if (Krugozor_Static_Numeric::is_decimal($this->request->getRequest($territory_field_name))) {
                $this->sql_where_string_buffer[] = '`user`.`' . $territory_field_name . '` = ?i';
                $this->sql_where_args_buffer[] = $this->request->getRequest($territory_field_name);
            }
        }
    }
}