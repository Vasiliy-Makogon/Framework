<?php
return [
    'content' => [
        'in' => ' в ',
        'in_category' => ' в раздел "{category_name}" ',
    ],

    'notification' => [
        'message' => [
            'bad_id_advert' => '<p>Указан некорректный идентификатор объявления.</p>',
            'advert_does_not_exist' => '<p>Запрошенное объявление не существует.</p>',

            'advert_delete' => '<p>Объявление &laquo;<strong>{advert_header}</strong>&raquo; удалено.</p>',

            'advert_date_create_update' => '<p>Объявление &laquo;<strong>{advert_header}</strong>&raquo; поднято в 
                результатах поиска на сайте. Это значит, его увидят больше посетителей сайта ' .
                \Krugozor\Framework\Registry::getInstance()->HOSTINFO['HOST_SIMPLE'] . ' . 
                Следующее поднятие данного объявления в поиске возможно через один час.</p>',

            'advert_date_create_not_update' => '<p>Объявление &laquo;<strong>{advert_header}</strong>&raquo; не может 
                быть поднято, т.к. недавно создано или уже было поднято в результатах поиска менее одного часа назад. 
                Повторите попытку после {date} минут.</p>',

            'advert_active_0' => '<p>Показ объявления &laquo;<strong>{advert_header}</strong>&raquo; приостановлен. 
                Объявление скрыто и недоступно для просмотра пользователями сайта.</p>',
            'advert_active_1' => '<p>Показ объявления  &laquo;<strong>{advert_header}</strong>&raquo; возобновлён. 
                Объявление доступно для просмотра всеми пользователями сайта.</p>',

            'advert_close_for_user' => '<p>Показ объявления приостановлен автором.</p>',

            'advert_close_for_author' => '<p>Показ объявления &laquo;<strong>{advert_header}</strong>&raquo; приостановлен. 
                Объявление скрыто и недоступно для просмотра посетителями сайта.</p>
                <p>Для того, что бы объявление было доступно для поиска, нажмите ссылку 
                &laquo;<strong class="space_nowrap">Возобновить показ</strong>&raquo; 
                в панели управления объявлением.</p>',

            'advert_close_user_ban' => '<p>Показ объявления приостановлен, т.к. автор объявления был заблокирован в 
                связи с нарушением <a href="/help/terms_of_service">пользовательского соглашения</a> сайта.</p>',

            'advert_save_without_vip' => '<p>Объявление &laquo;<strong>{advert_header}</strong>&raquo; успешно сохранено 
                и доступно для поиска.</p>
    
            <div class="paragraph">
                <h3>Хотите привлечь больше внимания на своё объявление и повысить его эффективность?</h3>
                <p>За <span class="price">' . \Krugozor\Framework\Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_TOP'] . ' рублей</span> 
                вы можете сделать ваше объявление более заметным &mdash; объявление будет выделено особым цветом, 
                будет находиться выше других бесплатных объявлений, соответственно его увидят больше посетителей сайта:</p>
                <div class="center">
                    <img alt="" src="/http/image/desing/vip.jpg" />
                </div>
                <div class="center submit_block">
                    <input 
                        onclick="window.location.href=\'{kassa_auth_url_vip}\'" 
                        type="button" 
                        value="Выделить объявление и разместить в VIP-линейке" />
                </div>
            </div>
    
            <div class="paragraph">
                <p>За <span class="price">' . \Krugozor\Framework\Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_SPECIAL'] . ' рублей</span> 
                вы можете сделать ваше объявление более заметным, разместив его в блоке &laquo;Спецпредложений&raquo; &mdash; объявление будет
                отображаться на всех основных страницах в увеличенных пропорциях, максимально привлекая внимание посетителей сайта:</p>
                <div class="center">
                    <img alt="" src="/http/image/desing/special.jpg" />
                </div>
                <div class="center submit_block">
                    <input 
                        onclick="window.location.href=\'{kassa_auth_url_special}\'" 
                        type="button" 
                        value="Разместить объявление в &laquo;Спецпредложении&raquo;" />
                </div>
            </div>
    
            <div class="paragraph">
            <h3>Также обратите внимание</h3>
                <p>Наибольшая эффективность от поданного объявления достигается только в том случае, если Ваше объявление 
                    увидит как можно больше людей. Вы можете сами повысить количество просмотров объявления путём 
                    размещения ссылки на объявление в социальных сетях, форумах или в блогах. 
                    Для этого воспользуйтесь следующим кодом:</p>
                <dl class="notification_advert_share">
                    <dt><p><strong>Для размещения в социальные сети:</strong></p></dt>
                    <dd>
                        <script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
                        <script src="//yastatic.net/share2/share.js"></script>
                        <div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,gplus,twitter,linkedin,lj"></div>
                    </dd>
                    <dt><p><strong>Код для вставки в форумы и блоги, поддерживающие BB-теги:</strong></p></dt>
                    <dd><div class="codes_for_blogs">[b][url=' . \Krugozor\Framework\Registry::getInstance()->HOSTINFO['HOST_URL'] . '/advert/{id}.xhtml]{advert_header}[/url][/b]</div></dd>
                    <dt><p><strong>Код для вставки в форумы и блоги, поддерживающие HTML-код:</strong></p></dt>
                    <dd><div class="codes_for_blogs">&lt;p&gt;&lt;a href="' . \Krugozor\Framework\Registry::getInstance()->HOSTINFO['HOST_URL'] . '/advert/{id}.xhtml"&gt;&lt;strong&gt;{advert_header}&lt;/strong&gt;&lt;/a&gt;&lt;/p&gt;</div></dd>
                </dl>
            </div>',

            'advert_save_with_vip' => '<p>Объявление &laquo;<strong>{advert_header}</strong>&raquo; успешно сохранено 
                и доступно для поиска.</p>
            <div class="paragraph">
                <h3>Обратите внимание</h3>
                <p>Наибольшая эффективность от поданного объявления достигается только в том случае, если Ваше объявление 
                    увидит как можно больше людей. Вы можете сами повысить количество просмотров объявления путём 
                    размещения ссылки на объявление в социальных сетях, форумах или в блогах. 
                    Для этого воспользуйтесь следующим кодом:</p>
                <dl class="notification_advert_share">
                    <dt><p><strong>Для размещения в социальные сети:</strong></p></dt>
                    <dd>
                        <script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
                        <script src="//yastatic.net/share2/share.js"></script>
                        <div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,gplus,twitter,linkedin,lj"></div>
                    </dd>
                    <dt><p><strong>Код для вставки в форумы и блоги, поддерживающие BB-теги:</strong></p></dt>
                    <dd><div class="codes_for_blogs">[b][url=' . \Krugozor\Framework\Registry::getInstance()->HOSTINFO['HOST_URL'] . '/advert/{id}.xhtml]{advert_header}[/url][/b]</div></dd>
                    <dt><p><strong>Код для вставки в форумы и блоги, поддерживающие HTML-код:</strong></p></dt>
                    <dd><div class="codes_for_blogs">&lt;p&gt;&lt;a href="' . \Krugozor\Framework\Registry::getInstance()->HOSTINFO['HOST_URL'] . '/advert/{id}.xhtml"&gt;&lt;strong&gt;{advert_header}&lt;/strong&gt;&lt;/a&gt;&lt;/p&gt;</div></dd>
                </dl>
            </div>',

            'advert_need_payment_header' => 'Необходимо оплатить услугу активации объявления',
            'advert_need_payment' => '
             <p>Ваше объявление &laquo;<b>{advert_header}</b>&raquo; добавлено, но на сайте пока ещё не отображается &mdash;
                за размещение объявлений в раздел &laquo;{category_name}&raquo; взимается разовая плата в размере 
                <span style="color:green">' .
                    \Krugozor\Framework\Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_ACTIVATE'] .
                '&nbsp;руб</span>.</p>
             <p>Что бы объявление было доступно всему интернету, пожалуйста, произведите процедуру оплаты любым удобным 
             для Вас способом:</p>
             <div class="center submit_block">
                 <input 
                    onclick="window.location.href=\'{kassa_auth_url_payment}\'" 
                    type="button" 
                    value="Активировать объявление" />
             </div>',
        ]
    ],
];