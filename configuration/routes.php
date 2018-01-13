<?php
return [
    [
        'pattern' => '~^/$~',
        'module' => 'index',
        'controller' => 'index'
    ],
    [
        'pattern' => '~^/([a-z_\-]+)/categories/?$~i',
        'module' => 'category',
        'controller' => 'frontend-categories-list',
        'aliases' => ['country_name_en']
    ],
    [
        'pattern' => '~^/([a-z_\-]+)/([a-z_\-]+)/categories/?$~i',
        'module' => 'category',
        'controller' => 'frontend-categories-list',
        'aliases' => ['country_name_en', 'region_name_en']
    ],
    [
        'pattern' => '~^/([a-z_\-]+)/([a-z_\-]+)/([a-z_\-]+)/categories/?$~i',
        'module' => 'category',
        'controller' => 'frontend-categories-list',
        'aliases' => ['country_name_en', 'region_name_en', 'city_name_en']
    ],
    [
        'pattern' => '~^/([a-z_\-]+)/categories(/[a-z0-9_/\-]+/)$~i',
        'module' => 'advert',
        'controller' => 'frontend-category-list',
        'aliases' => ['country_name_en', 'category_url']
    ],
    [
        'pattern' => '~^/([a-z_\-]+)/([a-z_\-]+)/categories(/[a-z0-9_/\-]+/)$~i',
        'module' => 'advert',
        'controller' => 'frontend-category-list',
        'aliases' => ['country_name_en', 'region_name_en', 'category_url']
    ],
    [
        'pattern' => '~^/([a-z_\-]+)/([a-z_\-]+)/([a-z_\-]+)/categories(/[a-z0-9_/\-]+/)$~i',
        'module' => 'advert',
        'controller' => 'frontend-category-list',
        'aliases' => ['country_name_en', 'region_name_en', 'city_name_en', 'category_url'],
    ],
    [
        'pattern' => '~^/advert/([0-9]+)\.xhtml$~',
        'module' => 'advert',
        'controller' => 'view',
        'aliases' => ['id'],
    ],
    [
        'pattern' => '~^/payment/([0-9]+)\.xhtml$~',
        'module' => 'advert',
        'controller' => 'payment',
        'aliases' => ['id'],
    ],
    [
        'pattern' => '~^/add\.xhtml$~',
        'module' => 'advert',
        'controller' => 'frontend-add',
    ],
    [
        'pattern' => '~^/my/?$~',
        'module' => 'authorization',
        'controller' => 'frontend-login',
    ],
    [
        'pattern' => '~^/my/adverts/?$~',
        'module' => 'advert',
        'controller' => 'frontend-user-adverts-list',
    ],
    [
        'pattern' => '~^/my/adverts/edit/?(?:([0-9]+)\.xhtml)?$~',
        'module' => 'advert',
        'controller' => 'frontend-edit-advert',
        'aliases' => ['id'],
    ],
    [
        'pattern' => '~^/my/adverts/up/([0-9]+)\.xhtml$~',
        'module' => 'advert',
        'controller' => 'frontend-up-advert',
        'aliases' => ['id'],
    ],
    [
        'pattern' => '~^/my/adverts/active/([0-9]+)\.xhtml$~',
        'module' => 'advert',
        'controller' => 'frontend-active-advert',
        'aliases' => ['id'],
    ],
    [
        'pattern' => '~^/my/adverts/delete/([0-9]+)\.xhtml$~',
        'module' => 'advert',
        'controller' => 'frontend-delete-advert',
        'aliases' => ['id'],
    ],
    [
        'pattern' => '~^/my/info/?$~',
        'module' => 'user',
        'controller' => 'frontend-edit',
    ],
    [
        'pattern' => '~^/registration\.xhtml$~',
        'module' => 'user',
        'controller' => 'frontend-registration',
    ],
    [
        'pattern' => '~^/getpassword/?$~',
        'module' => 'getpassword',
        'controller' => 'frontend-getpassword',
    ],
    [
        'pattern' => '~^/getpassword/([a-z0-9]{32,32})/?$~',
        'module' => 'getpassword',
        'controller' => 'frontend-getpassword-end',
        'aliases' => ['hash'],
    ],
    [
        'pattern' => '~/admin/?$~',
        'module' => 'authorization',
        'controller' => 'backend-login'
    ],
    [
        'pattern' => '~/logout/?~',
        'module' => 'authorization',
        'controller' => 'logout'
    ],
    [
        'pattern' => '~/feedback/?~',
        'module' => 'feedback',
        'controller' => 'feedback'
    ],

];