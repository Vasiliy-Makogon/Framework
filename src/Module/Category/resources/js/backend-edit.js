"use strict";

window.onload = function(){

    // Событие на текстовое поле "ключевые слова".
    Krugozor.Events.attachEvent(document.getElementById('category_keywords'), 'keyup', function(e){
        var target = Krugozor.Events.getTarget(e);
        var value = target.value;
        value = value.toLowerCase();
        target.value = value.replace(/(\r?\n)+/, ', ');
    });  
    
};