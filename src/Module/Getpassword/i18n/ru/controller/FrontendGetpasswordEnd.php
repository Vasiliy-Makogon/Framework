<?php
return [
    'title' => ['Восстановление пароля к аккаунту'],
    'mail' => [
        'header' => [
            'send_mail_user' => 'Ваш новый пароль на сайт ' . \Krugozor\Framework\Registry::getInstance()->HOSTINFO['HOST_SIMPLE'],
        ]
    ],
    'notification' => [
        'header' => [
            'bad_hash_header' => 'Устаревший запрос',
        ],
        'message' => [
            'bad_hash_message' => '<p>Запрос на восстановление данных по этой ссылке уже выполнен. Если Вы — инициатор запроса, проверяйте почту.</p>',
            'getpassword_send_message' => '<p>На указанную Вами при регистрации почту высланы данные для авторизации на сайте. Проверяйте почту.</p>',
        ]
    ]
];