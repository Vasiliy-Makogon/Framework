<?php
return [
    'title' => [
        'common website title'
    ],
    'meta' => [
        'keywords' => "common keyword1, common keyword 2, common keyword 3",
        'description' => "common description of website."
    ],
    'notification' => [
        'header' => [
            'action_complete' => 'Действие выполнено',
            'action_failed' => 'Действие не может быть выполнено',
            'action_warning' => 'Предупреждение'
        ],
        'message' => [
            'unknown_error' => '<p>Системная ошибка</p>',
            'element_does_not_exist' => '<p>Запрошенный элемент не существует</p>',
            'data_saved' => '<p>Данные сохранены</p>',
            'post_errors' => '<p>Произошли ошибки заполнения формы. Пояснения приводятся ниже.</p>',
            'forbidden_access' => '<p>У вас нет прав доступа к данному действию или Вы не авторизовались под своим логином и паролем.</p>',
            'inside_system' => '<p>Вы успешно вошли на сайт</p>',
            'outside_system' => '<p>Вы успешно завершили сеанс работы с сайтом.</p>'
        ],
    ],
    'content' => [
        'date' => [
            'months_genitive' => [
                1 => 'Января',
                2 => 'Февраля',
                3 => 'Марта',
                4 => 'Апреля',
                5 => 'Мая',
                6 => 'Июня',
                7 => 'Июля',
                8 => 'Августа',
                9 => 'Сентября',
                10 => 'Октября',
                11 => 'Ноября',
                12 => 'Декабря'
            ],

            'days_nominative' => [
                1 => 'понедельник',
                2 => 'вторник',
                3 => 'среда',
                4 => 'четверг',
                5 => 'пятница',
                6 => 'суббота',
                7 => 'воскресенье'
            ],
        ],
    ],
];