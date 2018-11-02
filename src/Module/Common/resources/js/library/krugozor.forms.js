"use strict";

var Krugozor = window.Krugozor || {};

Krugozor.Forms = {
    /**
     * Функция вырезает все не числовые символы из поля ввода.
     * 
     * @param object
     * @return void
     */
    filterDigit: function(_this){
        var newstr = '';
        var str = _this.value;
        var len = _this.value.length;
        var k = 0;

        for (var i = 0; i < len; i++){
            var chr = str.substring(i, i+1);

            if (/[0-9]/.test(chr)) {
                newstr = newstr + chr;
            } else {
                if (!k) {
                    k = 1;
                }
            }
        }

        _this.value = newstr;
        _this.focus();
    },
    
    /**
     * Для полей fields_id меняет тип и текстовую метку, в котрой произошло событие _this 
     * 
     * @param string ID полей через запятую 
     * @param label тег, по которому произошел клик
     * @return false
     */
    hidePassChars: function(fields_ids, label)
    {
    	var field;
        var fields = fields_ids.split(/, ?/);
        
        for (var i in fields) {
        	field = document.getElementById(fields[i]);
        	if (!field) {
        		continue;
        	}
        	
        	field.type  = field.type == 'password' ? 'text' : 'password';
        	label.firstChild.nodeValue = field.type == 'password' ? 'показать пароль' : 'скрыть пароль';
        }
        
        return false;
    }
};

Krugozor.Forms.Checker = function(form_id){
    this.error_messages = {
        'empty_input_fields': "Заполнены не все поля формы"
    };

    this.text_fields = [];
    this.text_fields_types = ["text", "password", "email", "url", "textarea", "search"];

    this.form = document.getElementById(form_id);

    if (this.form === null) {
        throw "Form element whith id `" + form_id + "` not found in DOM";
    }

    /**
    * Метод сканирует поля формы и помещает в массив ссылки на текстовые
    * области типа text, password и textarea.
    * 
    * @param void
    * @return array массив со ссылками на текстовые поля
    */
    this.getTextFields = function(){
        this.text_fields = [];
        
        for (var i=0; i < this.form.elements.length; i++){
            if (this.form.elements[i].type && Krugozor.Helper.inObject(this.form.elements[i].type.toLowerCase(), this.text_fields_types)){
                this.text_fields.push(this.form.elements[i]);
            }
        }
        
        return this;
    };
    this.getTextFields();
 
    /**
    * Метод устанавливает фокус на незаполненные текстовые поля формы.
    * 
    * @param void
    * @return void
    */
    this.putFocus = function(){
        for (var i=0; i < this.text_fields.length; i++){
            if (this.text_fields[i].value && !Krugozor.Helper.String.isEmpty(this.text_fields[i].value)){
                continue;
            } else {
                this.text_fields[i].focus();
                break;
            }
        }
    };

    /**
     * Метод проходит по форме.
     * Если хотя бы одно поле пустое (не содержит данных или содержит проблы и пр. не word-символы),
     * то функция возвращает false.
     * В качестве аргументов метода можно указать список имён или ID полей,
     * на которых действие функции не должны распростроняться.
     * 
     * @param void
     * @return boolean
     */
    this.checkTextFieldsOnEmpty = function(){
        for (var i=0; i < this.text_fields.length; i++) {
            if (arguments.length && (
                    Krugozor.Helper.inObject.call(arguments, this.text_fields[i].name) !== false
                    ||
                    Krugozor.Helper.inObject.call(arguments, this.text_fields[i].id) !== false)
                ) {
                continue;
            }

            if (!this.text_fields[i].value.length || Krugozor.Helper.String.isEmpty(this.text_fields[i].value)) {
                alert(this.error_messages['empty_input_fields']);
                this.text_fields[i].focus();
                return false;
            }
        }

        return true;
    };

    /**
     * Очищает форму.
     * Текстовые поля любого рода очищаются, значение select становится в 0-й элемент option,
     * с radio и checkbox снимается выделение.
     * 
     * @param void
     * @return void
     */
    this.clear = function(){
        for (var i=0; i<this.form.elements.length; i++){
            var item = this.form.elements.item(i);
            
            switch(item.tagName.toUpperCase()){
                case 'INPUT':
                    if (Krugozor.Helper.inObject(item.type.toLowerCase(), this.text_fields_types)) {
                        item.value = '';
                    } else if (item.type.toLowerCase() == 'checkbox' || item.type.toLowerCase() == 'radio') {
                        item.checked = false;
                    }
                    break;
                
                case 'SELECT':
                    if (item.multiple) {
                        item.selectedIndex = -1;
                    } else {
                        item.selectedIndex = 0;
                    }
                    break;
            }
        }
    };
};

/**
* Функция вырезает все символы за исключением a-z,0-9,-,_ из поля ввода,
* на который указывает _this (ссылка на this поля ввода).
* Вызывается по событию onkeyup, например.
* Вторым параметром является сообщение, которое будет показано пользователю.
* 
* @param object 
* @return void

function filterFieldAlnum(_this, _alert)
{
    var newstr = '';
    var str = _this.value;
    var len = _this.value.length;
    var k = 0;

    for (var i = 0; i<len; i++)
    {
        var chr = str.substring(i, i+1);

        if (/[0-9a-z_\-]/i.test(chr))
        {    
            newstr = newstr + chr;
        }
        else
        {
            if (!k)
            {
                k = 1;
            }
        }
    }

    _this.value = newstr;
    _this.focus();
}*/