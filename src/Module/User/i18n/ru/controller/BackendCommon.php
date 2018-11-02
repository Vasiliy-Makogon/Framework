<?php
return [
    'title' => ['Пользователи'],

    'notification' => [
        'message' => [
            'user_edit_ok' => '<p>Данные пользователя <strong><a href="/user/backend-edit/?id={id_user}">{user_name}</a></strong> сохранены.</p>',
            'bad_id_user' => '<p>Указан некорректный идентификатор пользователя.</p>',
            'user_does_not_exist' => '<p>Пользователя с идентификатором <strong>{id_user}</strong> не существует.</p>',
            'id_user_not_exists' => '<p>Не указан идентификатор пользователя.</p>',
            'user_delete' => '<p>Пользователь <strong>{user_name}</strong> удален.</p>',
        ]
    ],
];