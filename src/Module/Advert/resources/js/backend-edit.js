"use strict";

/**
 * Назначение события для ссылок установки VIP-статуса в текстовые поля формы.
 * @param a_class класс ссылок с data-атрибутами времени
 * @param i_class ID текстового поля
 */
function setVipDate(a_class, i_class)
{
    var date_intervals = Krugozor.DOM.getElementsByClassName(document, a_class);
    for (var i=0; i < date_intervals.length; i++) {
        Krugozor.Events.attachEvent(date_intervals[i], 'click', function(e){
            var event = Krugozor.Events.getEvent(e);
            var target = Krugozor.Events.getTarget(e);

            document.getElementById(i_class).value = target.getAttribute('data-time');
            Krugozor.Events.preventEvent(event);
        });
    }
}

window.onload = function(){

    Krugozor.UI.popup.ajaxselect.initSelect(document.getElementById('advert_category'));

    setVipDate('set_vip_date', 'vip_date');
    setVipDate('set_special_date', 'special_date');

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

    if (document.getElementById('advert_category')) {
        Krugozor.UI.popup.ajaxselect.initSelect(document.getElementById('advert_category'));
    }

    Krugozor.thumbnail.setContext(window);
    Krugozor.thumbnail.setIsRobot(window.is_guest);
    Krugozor.thumbnail.form_action = "/advert/thumbnail/";
    Krugozor.thumbnail.setUploadedImagesBlockId('uploaded_images');
    Krugozor.thumbnail.setErrorsBlockId('thumbnail_errors');
    Krugozor.thumbnail.setMainFormId('main_form');
    Krugozor.thumbnail.setFileUploadFormId('file_upload_form');
    Krugozor.thumbnail.setMaxFiles(max_upload_files);
    Krugozor.thumbnail.observer();
};

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