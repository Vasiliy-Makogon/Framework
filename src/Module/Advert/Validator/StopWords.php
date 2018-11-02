<?php

namespace Krugozor\Framework\Module\Advert\Validator;

use Krugozor\Framework\Validator\ValidatorAbstract;

/**
 * Проверка на плохие слова в тексте объявления.
 */
class StopWords extends ValidatorAbstract
{
    protected $error_key = 'BAD_WORDS';

    protected $words = array(
        'кредит', 'кредиты', 'кредита', 'кредитов', 'кредиту', 'кредитам', 'кредитом', 'кредите', 'кредитах',
        'кредитование', 'кредитной', 'кредитная', 'кредитные',
        'займ', 'займы', 'займа', 'займов', 'займам', 'займом', 'займе', 'займах', 'залог', 'залога', 'залоги',
        'заемщик', 'заемщика', 'заем',
        'заёмщик', 'заёмщика', 'заём',
        'курсовая', 'курсовые', 'курсовых',
        'диплом', 'дипломы', 'дипломов',
        'реферат', 'рефераты', 'рефератов',
        'долг', 'долги', 'задолжность', 'задолженность',
        'осаго',
    );

    /**
     * Возвращает false (факт ошибки), если найдено объявление с плохими словами в строке.
     * @return bool
     */
    public function validate(): bool
    {
        $texts = preg_split('~\s~', $this->value);

        array_walk($texts, function (&$val, $key) {
            $val = trim($val, '.,:;!?');
            $val = mb_strtolower($val);
        });

        return !(bool)array_intersect($texts, $this->words);
    }
}