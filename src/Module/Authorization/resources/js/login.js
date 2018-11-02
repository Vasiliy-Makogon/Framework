"use strict";

var Autologin = {
    checkbox: null,
    block_with_prompt: null,
    block_active_prompt: null,
    hidden_field: null,
    
    set: function(id_checkbox, id_block_with_prompt, id_block_active_prompt, id_hidden){
        if (id_checkbox) {
            this.checkbox = document.getElementById(id_checkbox);
        }
        
        this.block_with_prompt = document.getElementById(id_block_with_prompt);
        this.block_active_prompt = document.getElementById(id_block_active_prompt);
        this.hidden_field = document.getElementById(id_hidden);
        
        var _this = this;
        
        Krugozor.Events.attachEvent(this.checkbox, 'click', function(e){
            var event = e || window.event;
            var target = event.currentTarget || event.srcElement;
            _this.block_with_prompt.style.display = target.checked ? 'block' : 'none'; 
        });
        
        Krugozor.Events.attachEvent(this.block_active_prompt, 'click', function(e){
            var event = e || window.event;
            var target = event.currentTarget || event.srcElement;
            
            var str = 0;
            if (str = prompt('Введите, на какое количество дней необходимо запомнить пароль на этом компьютере', ''))
            {
                if (isNaN(str)) {
                    return false;
                }
                else if (Math.round(str) > 365 || Math.round(str) <= 0) {
                    return false;
                }
                else {
                    _this.block_active_prompt.firstChild.nodeValue = Math.round(str);
                    _this.hidden_field.value = Math.round(str);
                }
            }
        });
    }
};

window.onload = function(){
	// Если это страница неавторизированного пользователя
	if (document.getElementById('autologin')) {
		Autologin.set('autologin', 'change_cookie_days', 'CookieDays', 'ml_autologin');
	}
};