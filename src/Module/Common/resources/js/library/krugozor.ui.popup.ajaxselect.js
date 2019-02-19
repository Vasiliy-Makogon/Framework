"use strict";

var Krugozor = window.Krugozor || {
    UI: {
        popup: {}
    }
};

Krugozor.UI.popup.ajaxselect = {
    // HTMLSelectElement
    select: null,
    // Всплывающий обрамляющий select блок 
    wrapper: null,
    // Блок, в который обёрнут HTMLSelectElement.
    // Блок (желательно) не должен содержать иных элементов, помимо HTMLSelectElement,
    // т.к. в данный блок новый HTMLSelectElement будет внедряться посредством метода wrapper.appendChild().
    parentBlock: null,
    // Функция на кнопку "Продолжить", которая сработает после установки select-списка 
    // в основной поток (в this.wrapper). Контекстом функции будет this.
    button_callback: null,
    
    // ID всплывающего блока (для назначения ему стилей из внешней CSS таблицы).
    // @todo: убрать после правок в CSS
    POPUP_BLOCK_ID: 'krugozor_popup_ajaxselect',
    
    // CSS стили всплывающего блока. В него обёрнут select-список.
    CSS_WRAPPER: {
        backgroundColor: '#f5f5f5',
        borderRadius: '10px',
        padding: '10px',
        boxShadow: '0 0 10px #fff'
    },
    
    // Настройки кнопки "Продолжить".
    BUTTON_SETTINGS: {
        label: 'Продолжить',
        CSS: {
            display: 'block',
            position: 'absolute',
            right: '10px',
            bottom: '10px',
            boxShadow: 'none',
            borderRadius: '5px 0 0 0'
        }
    },
    
    /**
     * Инициализация select-списка для показа в новом окне: назначение события на focus.
     * 
     * @param HTMLSelectElement
     * @paran callback при нажатии на кнопку "Продолжить"
     *        this функции будет равен this объекта Krugozor.UI.popup.ajaxselect
     * @return this
     */
    initSelect: function(HTMLSelectElement, callback) {
        if (callback == undefined || typeof callback != 'function') {
            callback = function(){};
        }
        
        this.button_callback = callback;
        
        var _this = this;
        Krugozor.Events.attachEvent(HTMLSelectElement, 'focus', function(e){
            var event = e || window.event;
            var target = e.target || e.srcElement;
            
            _this.runPopup(target);
        });
        
        return this;
    },
    
    /**
     * Выделяет select-список из обжщего потока и показывает его в popup-окне.
     * 
     * @param HTMLSelectElement
     * @return void
     */
    runPopup: function(target) {
        if (this.wrapper !== null) {
            return;
        }
        
        var overlay = Krugozor.Overlay.addOverlay(0.6);
        
        this.select = target;
        
        this.selectedIndex = this.select.selectedIndex; 
        this.parentBlock = target.parentNode;
        
        this.wrapper = document.createElement('DIV');
        this.wrapper.setAttribute('id', this.POPUP_BLOCK_ID);
        Krugozor.Helper.attachCss(this.wrapper, this.CSS_WRAPPER);
        
        this.select.size = this.select.options.length;
        this.select.style.height = '100%';
        this.select.style.width = '100%';
        
        var clone_select = this.select.cloneNode(true);
        this.wrapper.appendChild(clone_select);
        this.select.parentNode.removeChild(this.select);
        this.select = clone_select;
        // В случае повторной активации select-списка, устанавливаем актуальный selectedIndex,
        // т.к. при клонировании он пропадает.
        this.select.selectedIndex = this.selectedIndex;
        
        if (parseInt(this.select.options[0].value) !== NaN && parseInt(this.select.options[0].value) === 0) {
            this.select.removeChild(this.select.options[0]);
        }
        
        this.attachEventChange(this.select);
        
        var button = document.createElement('input');
        button.setAttribute('type', 'button');
        button.setAttribute('value', this.BUTTON_SETTINGS.label);
        Krugozor.Helper.attachCss(button, this.BUTTON_SETTINGS.CSS);
        this.wrapper.appendChild(button);
        
        var _this = this;
        Krugozor.Events.attachEvent(button, 'click', function(e){
            var event = e || window.event;
            var target = e.target || e.srcElement;

            if (_this.select.selectedIndex == -1) {
                return false;
            }

            _this.select.style.height = 'auto';
            _this.select.style.width = 'auto';
            _this.select.size = '1';
            
            // На странице, в родительский блок select, вставляем select элемент
            _this.parentBlock.insertBefore(_this.select, _this.parentBlock.firstChild);
            
            // Удаляем popup-блок
            document.body.removeChild(_this.wrapper);
            _this.wrapper = null;
            
            overlay.removeOverlay();
            
            Krugozor.Events.attachEvent(_this.parentBlock.firstChild, 'focus', function(e){
                var event = e || window.event;
                var target = e.target || e.srcElement;
                
                _this.runPopup(target);
            });
            
            _this.button_callback.call(_this);
        });
        
        this.wrapper.style.height = Math.ceil(Krugozor.Browser.getClientHeight() / 1.5) + 'px';
        this.wrapper.style.width = Math.ceil(Krugozor.Browser.getClientWidth() / 2) + 'px';
        this.wrapper.style.left = Math.ceil((Krugozor.Browser.getClientWidth() - parseInt(this.wrapper.style.width)) / 2) + 'px';
        this.wrapper.style.top = Math.ceil((Krugozor.Browser.getClientHeight() - parseInt(this.wrapper.style.height)) / 2) + 'px';
        this.wrapper.style.position = 'fixed';
        this.wrapper.style.zIndex = '1000';
        
        document.body.appendChild(this.wrapper);
    },
    
    /**
     * По факту выбора option-элемента инициирует ajax-запрос для полуения подчинённых узлов. 
     * 
     * @param HTMLSelectElement
     * @returns
     */
    attachEventChange: function(element) {
        var _this = this;
        
        Krugozor.Events.attachEvent(element, 'change', function(e){
            var event = e || window.event;
            var target = e.target || e.srcElement;
            
            var selectedIndex = target.selectedIndex;
            var selectedValue = parseInt(target.options[selectedIndex].value);
            
            if (target.options[selectedIndex].hasAttribute('completed') || 
               (target.options[selectedIndex].hasAttribute('data-haschilds') && parseInt(target.options[selectedIndex].getAttribute('data-haschilds')) == 0)) {
                _this.select = target;
                return;
            }
            
            target.options[selectedIndex].setAttribute('completed', 1);
            
            var overlay = Krugozor.Overlay.createOverlay();
            overlay.setOpacity(0.6);
            overlay.setZIndex(1000);
            overlay.appendOverlay();
            
            var preloader = Krugozor.Preloader.createPreloader();
            preloader.setImagePath('/img/local/preloader.gif');
            preloader.setZIndex(100001);
            preloader.addPreloaderTo(overlay.overlay);	
            
            var ajax = new Krugozor.Ajax();
            
            ajax.setObserverState(function(ajx, xhr){
                var response = ajx.getJson();
                if (!response) {
                	return false;
                }
                
                window.setTimeout(function(){
                if (Krugozor.Helper.getCountElements(response)) {
                    var select = document.createElement('SELECT');
                    select.size = target.options.length;
                    select.style.height='100%';
                    select.style.width='100%';
                    select.id = target.id;
                    select.name = target.name;
                    var scrollTop = target.scrollTop;
                    _this.attachEventChange(select);

                    var to = select;
                    var excluded_options = new Array();
                    for (var i=0; i < target.childNodes.length; i++){
                        switch (target.childNodes[i].tagName.toUpperCase()){
                            case 'OPTGROUP':
                                var optgroup = document.createElement('OPTGROUP');
                                optgroup.label = target.childNodes[i].label;

                                for (var j = 0; j < target.childNodes[i].childNodes.length; j++) {
                                    // option's с данными ID не будут созданы с помощью клонирования, т.к.
                                    // они созданы из response по клику.
                                    if (Krugozor.Helper.inObject(target.childNodes[i].childNodes[j].value, excluded_options)) {
                                        continue;
                                    }
                                    
                                    var option = document.createElement('OPTION');
                                    option.appendChild(document.createTextNode(target.childNodes[i].childNodes[j].text));
                                    option.setAttribute('value', target.childNodes[i].childNodes[j].value);
                                    if (target.childNodes[i].childNodes[j].hasAttribute('completed')) {
                                        option.setAttribute('completed', target.childNodes[i].childNodes[j].getAttribute('completed'));
                                    }
                                    option.setAttribute('data-haschilds', target.childNodes[i].childNodes[j].getAttribute('data-haschilds'));
                                    optgroup.appendChild(option);

                                    if (target.childNodes[i].childNodes[j].value == selectedValue) {
                                          for (var k in response) if (response.hasOwnProperty(k)) {
                                              var option = document.createElement('OPTION');
                                              option.appendChild(document.createTextNode(response[k].name));
                                              option.setAttribute('value', response[k].id);
                                              option.setAttribute('pid', response[k].pid);
                                              option.setAttribute('data-haschilds', response[k].haschilds);
                                              optgroup.appendChild(option);
                                              
                                              excluded_options.push(response[k].id);
                                          }
                                    }
                                }

                                to.appendChild(optgroup);
                                break;
                           case 'OPTION':
                                var option = target.childNodes[i].cloneNode(true);
                                to.appendChild(option);
                                break;
                        }
                    }

                    target.parentNode.replaceChild(select, target);
                    select.scrollTop = scrollTop;
                    
                    // Оставляем selectedIndex на том option, на котором кликнули
                    select.selectedIndex = selectedIndex;
                    select.options[selectedIndex].focus();
                } // end if (Krugozor.Helper.getCountElements(response))
                
                overlay.removeOverlay();
                
                }, 400); // end timeout 
                
                // Возвращаем ссылку на select в основной объяект.
                _this.select = target;
            });
            
            ajax.get('/category/frontend-ajax-get-child-category/id/' + selectedValue);
        });
    }
};