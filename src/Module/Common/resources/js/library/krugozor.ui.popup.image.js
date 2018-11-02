"use strict";

var Krugozor = window.Krugozor || {
    UI: {
        popup: {}
    }
};

Krugozor.UI.popup.image = {

    // Основная обертка
    CSS_WRAPPER: {
        width: '100%',
        height: '100%',
        position: 'fixed',
        top: 0,
        left: 0,
        border: 0,
        background: 'transparent',
        zIndex: 1001
    },

    // Обертка над фото
    CSS_IMAGE_WRAPPER: {
        // Не назначайте здесь свойства height, width, top и left
        padding: '15px',
        position: 'fixed',
        display: 'block',
        border: '1px solid #666',
        backgroundColor: '#ffffff',
        boxShadow: '0 0 10px #CCC'
    },

    CSS_IMAGE: {
        cursor: 'pointer'
    },

    // CSS стили блока с кнопкой "закрыть".
    CSS_DIV_CLOSE: {
        fontSize: '11px',
        lineHeight: '15px',
        color: '#666',
        marginBottom: '10px',
        fontFamily: 'Arial, Verdata, sans-serif',
        textAlign: 'right'
    },

    // CSS стили блока кнопки "закрыть".
    CSS_SPAN_CLOSE: {
        cursor: 'pointer',
        letterSpacing: '2px'
    },

    CSS_THUMBNAILS_BLOCK: {
        width: '170px',
        position: 'absolute',
        right: '10px',
        top: '10px',
        textAlign: 'center',
        background: 'white'
    },

    // CSS стили гиперссылок превью
    CSS_THUMBNAILS_ANCHOR: {
        margin: '10px auto',
        display: 'block',
        width: '150px',
        border: '1px solid #666'
    },

    // CSS стили изображений превью
    CSS_THUMBNAILS_IMAGE: {
        display: 'block'
    },

    // Ассоциативный массив изображений найденных по классу
    // вида `путь_к_изображению_из_тега` => `объект изображения`.
    // Сохраняются все изображения, вне зависимости от того, подгрузились они или нет.
    images: [],

    // Массив thumbnails
    thumbnails: [],

    // Объект Krugozor.Overlay
    overlay: null,

    wrapper: null,

    /**
     * Загружает скрытые изображения по селектору, находя пути к ним в следующих тегах/аттрибутах:
     * A:href
     * IMG:src
     *
     * @param string selector селектор для поиска
     * @param string имя аттрибута, где лежит URL источника изображения.
     * @param bool ставить ли обработчик события onclick на элементы с указанными классами.
     */
    loadImagesBySelector: function(selector, attribute, set_onclick_event){
        if (set_onclick_event === undefined) {
            set_onclick_event = true;
        }

        var elements = document.querySelectorAll(selector);
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].tagName.toUpperCase() == 'IMG') {
                this.loadImage(elements[i].getAttribute('src'));
            } else if (elements[i].tagName.toUpperCase() == 'A') {
                this.loadImage(elements[i].getAttribute('href'));
            }

            var clone_element = elements[i].cloneNode(true);

            var _this = this;
            if (set_onclick_event) {
                var onclick_event = function(e) {
                    var event = Krugozor.Events.getEvent(e);

                    if (event.currentTarget) { // элемент A
                        var target = event.currentTarget;
                    // IE8 не имеет аналога `currentTarget`, поэтому из самого нижнего элемента в узле поднимаемся
                    // выше до тех пор, пока не обнаружим элемент A или IMG.
                    } else {
                    	var target = Krugozor.Events.getTarget(e);

                        while (target = target.parentNode){
                            if (target.nodeType == 1 && (target.tagName.toUpperCase() == 'IMG' || target.tagName.toUpperCase() == 'A')) {
                                break;
                            }
                        }
                    }

                    var source = target.getAttribute('href') || target.getAttribute('src') || null;

                    if (source) {
                        _this.showImage(source);
                        Krugozor.Events.preventEvent(event);
                    }
                };

                Krugozor.Events.attachEvent(elements[i], "click", onclick_event);
                // По стандарту cloneNode не клонирует события узла, поэтому назначаем их вручную.
                Krugozor.Events.attachEvent(clone_element, "click", onclick_event);
            }

            this.thumbnails.push(clone_element);
        }

        return this;
    },

    /**
     * Возвращает true, если картинок больше 1
     *
     * @param void
     * @return bool
     */
    imagesGtOne: function(){
        return Krugozor.Helper.getCountElements(this.images) > 1;
    },

    /**
     * Показывает изображение по адресу source
     *
     * @param string
     * @return void
     */
    showImage: function(source) {
        var _this = this;

        if (!this.images[source] || !this.images[source].loaded) {
            alert('Извините, изображение ещё не загрузилось, попробуйте ещё раз');
            return false;
        }

        if (this.overlay === null || this.overlay.overlay === null) {
            this.overlay = Krugozor.Overlay.addOverlay(0.6, function(){
                _this.wrapper.style.display = "none";
                _this.overlay.removeOverlay();
            });
        }

        var real_wrapper_width = this.images[source].width + parseInt(this.CSS_IMAGE_WRAPPER.padding) * 2;

        var real_wrapper_height = this.images[source].height +
                                  parseInt(this.CSS_DIV_CLOSE.lineHeight) +
                                  parseInt(this.CSS_DIV_CLOSE.marginBottom) +
                                  parseInt(this.CSS_IMAGE_WRAPPER.padding) * 2;

        var positions = Krugozor.Browser.getPositionBlockByCenter(real_wrapper_width, real_wrapper_height, false);

        if (this.imagesGtOne()) {
            positions.left = positions.left - ((parseInt(this.CSS_THUMBNAILS_BLOCK.width) + parseInt(this.CSS_THUMBNAILS_BLOCK.right)) / 2);
        }

        if (this.wrapper === null) {
            this.wrapper = document.createElement("DIV");
            Krugozor.Helper.attachCss(this.wrapper, this.CSS_WRAPPER);
            document.body.appendChild(this.wrapper);

            // Обрамляющий изображение div
            var image_wrapper = document.createElement("DIV");
            Krugozor.Helper.attachCss(image_wrapper, this.CSS_IMAGE_WRAPPER);
            this.wrapper.appendChild(image_wrapper);

            // div закрытия окна
            var div_close_node = document.createElement("DIV");
            Krugozor.Helper.attachCss(div_close_node, this.CSS_DIV_CLOSE);
            var span_close_node = document.createElement("SPAN");
            span_close_node.appendChild(document.createTextNode('[закрыть]'));
            Krugozor.Helper.attachCss(span_close_node, this.CSS_SPAN_CLOSE);
            div_close_node.appendChild(span_close_node);
            image_wrapper.appendChild(div_close_node);

            Krugozor.Events.attachEvent(span_close_node, "click", function() {
                _this.wrapper.style.display = "none";
                _this.overlay.removeOverlay();
            });

            // Изображение
            var img_node = document.createElement('IMG');
            Krugozor.Helper.attachCss(img_node, this.CSS_IMAGE);
            image_wrapper.appendChild(img_node);

            Krugozor.Events.attachEvent(img_node, "click", function(e){
                var event = e || window.event;
                var target = e.target || e.srcElement;
                Krugozor.Events.preventEvent(event);

                for (var i in _this.thumbnails) {
                    if (target.dataset && _this.thumbnails[i].getAttribute('href') == target.dataset.source) {
                        var index = parseInt(i) + 1;
                        var index = _this.thumbnails[index] !== undefined  ? index : 0;
                        _this.showImage(_this.thumbnails[index].getAttribute('href'));
                        break;
                    }
                }
            });

            // Thumbnails
            if (this.imagesGtOne()){
                var thumbnails_node = document.createElement("DIV");
                for (var i in this.thumbnails) {
                    if (this.thumbnails.hasOwnProperty(i)){
                        Krugozor.Helper.attachCss(this.thumbnails[i], this.CSS_THUMBNAILS_ANCHOR);
                        Krugozor.Helper.attachCss(this.thumbnails[i].firstElementChild, this.CSS_THUMBNAILS_IMAGE);
                        thumbnails_node.appendChild(this.thumbnails[i]);
                    }
                }

                Krugozor.Helper.attachCss(thumbnails_node, this.CSS_THUMBNAILS_BLOCK);
                this.wrapper.appendChild(thumbnails_node);
            }
        } else {
            var img_node = this.wrapper.firstChild.lastChild;
        }

        img_node.setAttribute('src', source);
        if (img_node.dataset) {
            img_node.dataset.source = source;
        }

        this.wrapper.firstChild.style.top = positions.top + 'px';
        this.wrapper.firstChild.style.left = positions.left + 'px';

        this.wrapper.style.display = "block";
    },

    /**
     * Загружает изображение по адресу source и сохраняет его объект в хранилище this.images.
     *
     * @param string source URL-адрес к изображению
     * @return object this
     */
    loadImage: function(source){
        var img = new Image();
        img.loaded = false;
        img.onload = function(){
            this.loaded = true;
        };
        img.src = source;
        this.images[source] = img;
        return this;
    }
};