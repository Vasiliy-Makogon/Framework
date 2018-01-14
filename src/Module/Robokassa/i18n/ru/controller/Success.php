<?php
return array
(
    'title' => array('Оплата услуг'),

    'notification_header_fail' => 'Ошибка проведения платежа',

    'bad_signature' => '<p>Неверная сигнатура платежа.</p>',
    'not_found_advert_id' => '<p>Не указан ID объявления.</p>',
    'not_found_advert' => '<p>Объявления с ID {id} на сайте нет, проверьте правильность набора данных.</p>',
    'undefined_action' => '<p>Неизвестный параметр action.</p>',

    'advert_set_vip' => '<p>Платёж успешно совершён.</p>' .
                        '<p>Объявление «<b>{advert_header}</b>» выделено и поднято в поиске. ' .
                        'Это значит, что его увидят больше посетителей сайта и Ваше объявление с большей вероятностью попадёт в кэш поисковых систем интернета, что со временем даст постоянный приток поситителей на Ваше объявление.</p>' .
                        '<p>С уважением, команда {http_host}</p>' .
                        '<p>&nbsp;</p>' .
                        '<p>Сейчас Вы можете: <a href="/advert/{id}.xhtml">Посмотреть своё объявление</a> | <a href="/my/adverts/edit/">Подать еще одно объявление</a> | <a href="/my/">Перейти в свой личный кабинет</a></p>',

    'advert_set_payment' => '<p>Платёж успешно совершён.</p>' .
                            '<p>Объявление «<b>{advert_header}</b>» активировано и отображается на сайте.</p>' .
                            '<p>С уважением, команда {http_host}</p>' .
                            '<p>&nbsp;</p>' .
                            '<p>Сейчас Вы можете: <a href="/advert/{id}.xhtml">Посмотреть своё объявление</a> | <a href="/my/adverts/edit/">Подать еще одно объявление</a> | <a href="/my/">Перейти в свой личный кабинет</a></p>'
);