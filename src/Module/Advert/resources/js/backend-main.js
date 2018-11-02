"use strict";

window.onload = function(){

    // select список категорий для перехода между категориями
    var top_category = Krugozor.Helper.clone(Krugozor.UI.popup.ajaxselect);
    top_category.initSelect(document.getElementById('advert_category'), function(){
        window.location.href = "?id_category=" + this.select.value;
    });

    // select список категорий изменения категорий у объявлений
    if (document.getElementById('category')) {
        var bottom_category = Krugozor.Helper.clone(Krugozor.UI.popup.ajaxselect);
        bottom_category.initSelect(document.getElementById('category'));
    }

    // Сделать отдельной функцией, как только возникнет необходимость 
    // повторять подобные действия - выбирать все checkbox на странице.
    // @todo: добавить возможность указывать класс
    var label = document.getElementById('js_advert_delete_all');

    if (label) {
        Krugozor.Events.attachEvent(label, 'click', function(e){
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].type == 'checkbox') {
                    inputs[i].checked = !inputs[i].checked;
                }
            }
            
        	var event = Krugozor.Events.getEvent(e);
            Krugozor.Events.preventEvent(event);
        });
    }
    
    // Отправка письма анонимному пользователю с предложением зарегестрироваться
    var invites = Krugozor.DOM.getElementsByClassName(document, 'invite-anonymous-user')
    Krugozor.Events.attachEvents(invites, 'click', function(e){
    	Krugozor.Events.preventEvent(e);
    	var ajax = new Krugozor.Ajax();
    	ajax.setObserverState(
            function(ajx, xhr) {
            	alert(this.message)
            }, true
        );
    	ajax.get(Krugozor.Events.getEvent(e).currentTarget.getAttribute('href'));
    })
    
};