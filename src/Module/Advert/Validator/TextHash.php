<?php

namespace Krugozor\Framework\Module\Advert\Validator;

use Krugozor\Framework\Module\Advert\Mapper\Advert as AdvertMapper;
use Krugozor\Framework\Module\Advert\Model\Advert;
use Krugozor\Framework\Validator\ValidatorAbstract;

/**
 * Проверка на наличие в базе объявления с таким же хэшем.
 */
class TextHash extends ValidatorAbstract
{
    protected $error_key = 'BAD_TEXT_HASH';

    /**
     * TextHash constructor.
     * @param Advert $advert
     * @param AdvertMapper $mapper
     */
    public function __construct(Advert $advert, AdvertMapper $mapper)
    {
        parent::__construct($advert);

        $this->mapper = $mapper;
    }

    /**
     * Возвращает false (факт ошибки), если найдено объявление с таким хэшем.
     *
     * @return bool
     */
    public function validate(): bool
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