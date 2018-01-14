<?php
/**
 * Проверка на наличие в базе объявления с таким же хэшем.
 */
class Krugozor_Module_Advert_Validator_TextHash extends Krugozor_Validator_Abstract
{
    protected $error_key = 'BAD_TEXT_HASH';

    /**
     * @param int $value Krugozor_Module_Advert_Model_Advert $value
     * @param Krugozor_Mapper $mapper
     */
    public function __construct(Krugozor_Module_Advert_Model_Advert $advert, Krugozor_Mapper $mapper)
    {
        parent::__construct($advert);

        $this->mapper = $mapper;
    }

    /**
     * Возвращает false (факт ошибки), если найдено объявление с таким хэшем.
     *
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        $params = array(
            'where' => array('`advert_hash` = "?s"' => array($this->value->getHash())),
            'what' => 'id',
        );

        if ($this->value->getId() !== null) {
            $params['where']['AND id <> ?i'] = array($this->value->getId());
        }

        if ($this->mapper->findModelByParams($params)->getId()) {
            return false;
        }

        return true;
    }
}