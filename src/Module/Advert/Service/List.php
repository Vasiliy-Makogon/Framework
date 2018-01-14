<?php
class Krugozor_Module_Advert_Service_List extends Krugozor_Service_List
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_List::__construct()
     */
    public function __construct(Krugozor_Http_Request $request,
                                Krugozor_Mapper $mapper,
                                \Krugozor\Pagination\Manager $pagination)
    {
        $this->order_options['id'] = 'advert.id';
        $this->order_options['header'] = 'advert.advert_header';
        $this->order_options['category'] = 'category.category_name';
        $this->order_options['active'] = 'advert.advert_active';
        $this->order_options['vip'] = 'advert.advert_vip_date';
        $this->order_options['special'] = 'advert.advert_special_date';
        $this->order_options['image'] = 'advert.advert_thumbnail_count';
        $this->order_options['user_name'] = 'user.user_first_name';
        $this->order_options['payment'] = 'advert.advert_payment';
        $this->order_options['was_moderated'] = 'advert.advert_was_moderated';
        $this->order_options['advert_create_date'] = 'advert.advert_create_date';

        parent::__construct($request, $mapper, $pagination);
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_List::findList()
     */
    public function findList()
    {
        $this->processSortSearchCondition();
        $this->list = $this->mapper->findListForBackend($this->createParams());
        $this->pagination->setCount($this->mapper->getFoundRows());

        return $this;
    }

    /**
     * Установка параметров для условия WHERE.
     *
     * @param void
     * @return void
     */
    private function processSortSearchCondition()
    {
        if (Krugozor_Static_Numeric::is_decimal($this->request->getRequest('id_category'))) {
            $this->sql_where_string_buffer[] = '`advert`.`advert_category` = ?i';
            $this->sql_where_args_buffer[] = $this->request->getRequest('id_category');
        }

        if (Krugozor_Static_Numeric::is_decimal($this->request->getRequest('id_user'), true)) {
            $this->sql_where_string_buffer[] = '`advert`.`advert_id_user` = ?i';
            $this->sql_where_args_buffer[] = $this->request->getRequest('id_user');
        }
    }
}