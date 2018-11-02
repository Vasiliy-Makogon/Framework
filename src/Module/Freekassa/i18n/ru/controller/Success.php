<?php
return [
    'title' => ['Оплата услуг'],

    'bad_signature' => '<p>Неверная сигнатура платежа.</p>',
    'not_found_advert_id' => '<p>Не указан ID объявления.</p>',
    'not_found_advert' => '<p>Объявления с ID {id} на сайте нет, проверьте правильность набора данных.</p>',
    'undefined_action' => '<p>Неизвестный параметр action.</p>',

    'advert_pay_success' => '<p>Платёж успешно совершён, запущен процесс активации объявления.</p>' .
                            '<p>&nbsp;</p>' .
                            '<p>Сейчас Вы можете: <a href="/advert/frontend-edit-advert/">Подать еще одно объявление</a> | <a href="/authorization/frontend-login/">Перейти в свой личный кабинет</a></p>' .
                            '<p>С уважением, команда {http_host}</p>',
];