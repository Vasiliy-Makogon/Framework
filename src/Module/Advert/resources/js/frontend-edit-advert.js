"use strict";

/**
 * Событие onclick на ссылку быстрого выбора местоположения.
 *
 * @param int country ID страны
 * @param int region ID региона
 * @param int city ID города
 * @return Boolean
 */
function simple_city_checked(country, region, city)
{
    Krugozor.Location.addCheckedUserLocation(1, country);
    Krugozor.Location.addCheckedUserLocation(2, region);
    Krugozor.Location.addCheckedUserLocation(3, city);

    selectCountryObj.create(1, 0);
    selectRegionObj.create(2, country);
    selectCityObj.create(3, region);

    return false;
}

/**
 * Устанавливает свойство прозрачности для элемента.
 *
 * @param string elementId
 * @return void
 */
function set_contact_input_state(elementId)
{
    var lnk = document.getElementById(elementId);
    var op = lnk.style[getOpacityProperty()];
    setElementOpacity(lnk, (op == 1 || !op ? 0.5 : 1));
}

/**
 * После регистрации пользователя напоминает в notofications
 * только что установленные им логин и пароль.
 *
 * @param _this
 * @param login
 * @param password
 * @return bool
 */
function view_login_password(_this, login, password)
{
    var span = document.createElement('SPAN');
    var b = document.createElement('B');
    b.appendChild(document.createTextNode(login));
    span.appendChild(b);
    span.appendChild(document.createTextNode(' '));
    var b = document.createElement('B');
    b.appendChild(document.createTextNode(password));
    span.appendChild(b);
    _this.parentNode.replaceChild(span, _this);
    return false;
}

window.onload = function(){

    if (document.getElementById('file')) {
        // Событие onfocus для предотвращения работы ботов - бот фокус не поставит.
        Krugozor.Events.attachEvent(document.getElementById('file'), 'focus', function(e) {
            var target = Krugozor.Events.getTarget(e);
            target.setAttribute('checked', 1);
        });

        // Событие на выбор файла.
        Krugozor.Events.attachEvent(document.getElementById('file'), 'change', function(e) {
            Krugozor.thumbnail.processUpload(Krugozor.Events.getTarget(e));
        });
    }

    // Событие на кнопку подачи объявления.
    if (document.getElementsByName('submit_normal')[0]) {
        Krugozor.Events.attachEvent(document.getElementsByName('submit_normal')[0], 'click', function(e){
            var event = Krugozor.Events.getEvent(e);

            var thumbnails = document.getElementById('uploaded_images').getElementsByTagName('img');
            if (!thumbnails.length) {
                var confirm_text = "Вы не загрузили изображения для Вашего объявления. Наличие изображения в объявлении ЗНАЧИТЕЛЬНО повышает эффективность объявления.\n" +
                (window.is_guest ? '' : "Поскольку Вы зарегистрированный пользователь, то Вы можете добавить изображения позже -- при редактировании объявления.\n") +
                 "\nOK -- разместить объявление без загрузки изображений.\nОтмена -- вернуться и загрузить изображения.";

                if (!confirm(confirm_text)) {
                    Krugozor.Events.preventEvent(event);
                    document.getElementById('file').click();
                    return;
                }
            }

            document.forms["main_form"].submit();
        });
    }

    if (document.getElementById('advert_category')) {
        Krugozor.UI.popup.ajaxselect.initSelect(document.getElementById('advert_category'));
    }

    Krugozor.thumbnail.setContext(window);
    Krugozor.thumbnail.setIsRobot(window.is_guest == '1');
    Krugozor.thumbnail.form_action = "/advert/thumbnail/";
    Krugozor.thumbnail.setUploadedImagesBlockId('uploaded_images');
    Krugozor.thumbnail.setErrorsBlockId('thumbnail_errors');
    Krugozor.thumbnail.setMainFormId('main_form');
    Krugozor.thumbnail.setFileUploadFormId('file_upload_form');
    Krugozor.thumbnail.setMaxFiles(max_upload_files);
    Krugozor.thumbnail.observer();
};