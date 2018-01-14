<?php
return array
(
    'title' => array('Подача объявления. Шаг 1 из 2. Регистрация'),
    'sex' => array('male' => 'Мужчина', 'female' => 'Женщина'),

    'send_mail_user_header' => 'Ваши регистрационные данные на сайте ' . Krugozor_Registry::getInstance()->HOSTINFO['HOST_SIMPLE'],

    'notification' => array
    (
        'you_registration_ok' => 'Поздравляем с успешной регистрацией на сайте!',
        'you_registration_with_email' => '<p>Вы указали при регистрации email-адрес, на него придет письмо с Вашим логином и паролем. Обязательно сохраните данное письмо в надёжном месте, что бы в будущем иметь возможность управлять своими объявлениями на сайте <strong>' . Krugozor_Registry::getInstance()->HOSTINFO['HOST'] . '</strong>. На всякий случай напоминаем, Ваш логин и пароль: <a href="#" onclick="return view_login_password(this, \'{login}\', \'{password}\')">[показать]</a></p>',
        'you_registration_without_email' => '<p>Что бы в будущем иметь возможность управлять своими объявлениями на сайте <strong>' . Krugozor_Registry::getInstance()->HOSTINFO['HOST'] . '</strong>, сохраните свой логин и пароль в надёжном месте. На всякий случай напоминаем, Ваш логин и пароль: <a href="#" onclick="return view_login_password(this, \'{login}\', \'{password}\')">[показать]</a></p>',
    ),
);