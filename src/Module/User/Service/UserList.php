<?php

namespace Krugozor\Framework\Module\User\Service;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Mapper;
use Krugozor\Framework\Service\ListAbstract;

class UserList extends ListAbstract
{
    /**
     * Алиасы из запроса и поля таблицы, содержащие имена столбцов территорий.
     * @var array
     */
    private $territory = [
        'user_city' => 'user.user_city',
        'user_region' => 'user.user_region',
        'user_country' => 'user.user_country',
    ];

    /**
     * Поля таблицы, по которым проходит поиск.
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
     * UserList constructor.
     * @param Request $request
     * @param Mapper $mapper
     * @param \Krugozor\Pagination\Manager $pagination
     */
    public function __construct(Request $request,
                                Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->order_options['id'] = 'user.id';
        $this->order_options['first_name'] = 'user.user_first_name';
        $this->order_options['ip'] = 'user.user_ip';

        parent::__construct($request, $mapper, $pagination);
    }

    /**
     * @return ListAbstract
     */
    public function findList(): ListAbstract
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
     */
    private function processAnonymousExceptionCondition()
    {
        $this->sql_where_string_buffer[] = '?f <> ?i';
        $this->sql_where_args_buffer[] = 'user.id';
        $this->sql_where_args_buffer[] = -1;
    }

    /**
     * Установка параметров поиска по строке для условия WHERE.
     */
    private function processTextSearchCondition()
    {
        $search = $this->request->getRequest('search', Request::SANITIZE_STRING);
        if ($search !== '') {
            if ($this->request->getRequest('col', Request::SANITIZE_STRING) != 'id_user') {
                if ($this->request->getRequest('col', Request::SANITIZE_STRING) == 'all') {
                    $this->sql_where_string_buffer[] = 'CONCAT_WS(",", ' .
                        implode(', ', $this->text_search_cols) .
                    ') LIKE "%?s%"';
                    $this->sql_where_args_buffer[] = $search;
                } else {
                    $this->sql_where_string_buffer[] = '?f LIKE "%?s%"';
                    $this->sql_where_args_buffer[] = $this->request->getRequest('col');
                    $this->sql_where_args_buffer[] = $search;
                }
            } else if ($this->request->getRequest('col') == 'id_user' && $id_user = $this->request->getRequest('search', 'decimal')) {
                $this->sql_where_string_buffer[] = '`user`.`id` = ?i';
                $this->sql_where_args_buffer[] = $id_user;
            }
        }
    }

    /**
     * Установка параметров поиска по активности пользователя для условия WHERE.
     */
    private function processUserActiveSearchCondition()
    {
        if ($this->request->getRequest('user_active', Request::SANITIZE_STRING) !== '') {
            $this->sql_where_string_buffer[] = '?f = ?i';
            $this->sql_where_args_buffer[] = 'user.user_active';
            $this->sql_where_args_buffer[] = $this->request->getRequest('user_active', Request::SANITIZE_INT);
        }
    }

    /**
     * Установка параметров поиска по территориям для условия WHERE.
     */
    private function processTerritirySearchCondition()
    {
        foreach ($this->territory as $territory_key => $territory_field_name) {
            if ($this->request->getRequest($territory_key, Request::SANITIZE_INT)) {
                $this->sql_where_string_buffer[] = '?f = ?i';
                $this->sql_where_args_buffer[] = $territory_field_name;
                $this->sql_where_args_buffer[] = $this->request->getRequest($territory_key, Request::SANITIZE_INT);
            }
        }
    }
}