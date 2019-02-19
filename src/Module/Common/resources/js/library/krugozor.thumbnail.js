"use strict";

var Krugozor = window.Krugozor || {};

/**
 Объект, оперирующий функционалом загрузки изображений через iframe.
 Описание работы:
 
 На основной странице должны располагаться:
 
 iframe вида:
 <iframe width="0" height="0" name="iframe"></iframe>
 
 Форма для загрузки изображений с кнопкой выбора файла:
 <form id="file_upload_form" method="post" enctype="multipart/form-data" target="iframe">
     <input type="hidden" name="MAX_FILE_SIZE" value="...." />
     <input type="file" name="file" id="file" checked="0" />
 </form>
 
 Основная форма, в скрытых полях которой будет храниться информация о загруженных изображениях
 (именно с помощью этой формы будет связаны изображения с сущностью, к которой они прикрепляются):
 <form method="post" action="..." id="main_form">
     <!-- 
         сюда будут вставляться hidden-поля вида
         <input name="thumbnail[]" value="177" type="hidden"> 
     -->
 </form>
 
 Блок показа ошибок при загрузке изображений:
 <div id="thumbnail_errors"></div>
 
 
 Инициализация объекта на основной странице должна происходить следующим образом,
 с указанием всех необходимых параметров, необходимых для корректной работы:
 
    Krugozor.thumbnail.setContext(window);
    Krugozor.thumbnail.setIsRobot(window.is_guest == '1');          // доп. проверка на анонимного пользователя. 
                                                                    // Фокус на поле загрузки изображения даст checked=1, что дает защиту от ботов
    Krugozor.thumbnail.form_action = "/thumbnail/advert/";          // Путь к обработчику загрузки изображений для iframe
    Krugozor.thumbnail.setUploadedImagesBlockId('uploaded_images'); // ID блока с изображениями
    Krugozor.thumbnail.setErrorsBlockId('thumbnail_errors');        // ID блока с ошибками
    Krugozor.thumbnail.setMainFormId('main_form');                  // ID основной формы сущности, к которой закрепляются изображения
    Krugozor.thumbnail.setFileUploadFormId('file_upload_form');     // ID формы для загрузки изображений с кнопкой выбора файла
    Krugozor.thumbnail.setMaxFiles(<?=\Krugozor\Framework\Registry::getInstance()->UPLOAD['MAX_FILES_COUNT']?>); // Макс. колв-во загружаемых файлов
    Krugozor.thumbnail.observer();
    
Инициализация на странице в iframe:
    Krugozor.thumbnail.setContext(window.parent);
    Krugozor.thumbnail.setUploadedImagesBlockId('uploaded_images');
    Krugozor.thumbnail.setErrorsBlockId('thumbnail_errors');
    Krugozor.thumbnail.setMainFormId('main_form');
    Krugozor.thumbnail.setFileUploadFormId('file_upload_form');
    Krugozor.thumbnail.setMaxFiles(<?=\Krugozor\Framework\Registry::getInstance()->UPLOAD['MAX_FILES_COUNT']?>);
 
В коде основной страницы 2 обработчика: 

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
 
В случае успешной загрузки в iframe нужно вызвать: 
    Krugozor.thumbnail.uploadSuccess(<?=$this->thumbnail_id?>, '<?=$this->path_to_image?>');
иначе:
    Krugozor.thumbnail.uploadFail('<?=\Krugozor\Framework\Helper\Format::js($this->error)?>');
 */
Krugozor.thumbnail = {
    // Путь к прелоадеру изображений.
    thumbnail_icon_path: '/img/local/thumbnail_load_icon.gif',
    
    // Путь к обработчику загрузки изображений, т.е. к iframe.
    form_action: null,
    
    // Делать ли проверку на бота.
    is_robot: true,
    
    /**
     * Устанавливает глобальную область видимости, с которой работаем.
     * 
     * @param window.parent для iframe или window для основной страницы
     */
    setContext: function(context){
        this.context = context;
    },
    
    /**
     * Установка ID блока, куда вставляются изображения после обработки.
     * 
     * @param string
     */
    setUploadedImagesBlockId: function(id){
        this.images_block_id = id;
    },
    
    /**
     * Установка ID блока показа ошибок загрузки.
     * 
     * @param string
     */
    setErrorsBlockId: function(id){
        this.errors_block_id = id;
    },
    
    /**
     * Установка ID главной формы.
     * 
     * @param string
     */
    setMainFormId: function(id){
        this.main_form_id = id;
    },
    
    /**
     * Установка ID формы с полем загрузки файла.
     * 
     * @param string
     */
    setFileUploadFormId: function(id){
        this.upload_form_id = id;
    },
    
    /**
     * Устанавливает максимально-допусимое кол-во файлов, которое можно загрузить.
     * 
     * @param int
     */
    setMaxFiles: function(max_files){
        this.max_files = max_files;
    },
    
    /**
     * Делать ли проверку на робота.
     * 
     * @param bool
     */
    setIsRobot: function(is_robot) {
        this.is_robot = !!is_robot;
    },
        
    /**
     * Событие onchange на кнопку выбора изображения, которое приводит к загрузки файла, т.е.
     * к submit формы.
     * 
     * @param _this ссылка на элемент input, на котором произошло событие.
     * @return void
     */
    processUpload: function(_this) {
        // Если аттрибут checked в 0, значит это робот.
    	// В клиентском коде должено быть проставлено событие на поле изображения,
    	// и должен быть проставлен аттрибут checked=1
        if (this.is_robot && !parseInt(_this.getAttribute('checked'))) {
            return;
        }
        
        _this.form.action = this.form_action;
        
        this.setUploadErrorState();
        
        // добавляем прелоадер в страницу
        var icon = this.createThumbnailImageIcon();
        var wrap = this.createThumbnailWrap();
        wrap.appendChild(icon);
        this.context.document.getElementById(this.images_block_id).style.display = "block";
        this.context.document.getElementById(this.images_block_id).appendChild(wrap);
        
        _this.form.submit();
        _this.form.elements.namedItem('file').disabled = true;
    },
    
    /**
     * Ошибка загрузки. Функция для содержимого iframe.
     * 
     * @param string текст сообщения об ошибке.
     */
    uploadFail: function(error) {
        this.setUploadErrorState(error);
        var uploaded_images_block = this.context.document.getElementById(this.images_block_id);
        
        if (this.isWrap(uploaded_images_block.lastChild)) {
            uploaded_images_block.removeChild(uploaded_images_block.lastChild);
        }
        
        this.checkNodesInImagesBlock(uploaded_images_block);
        this.context.document.getElementById(this.upload_form_id).elements.namedItem('file').disabled = false;
    },
    
    /**
     * Успешная загрузка. Функция для содержимого iframe.
     * 
     * @param ID изображения
     * @param путь к изображению
     */
    uploadSuccess: function(thumbnail_id, thumbnail_path) {
        var remove_link = this.createThumbnailRemoveLink(thumbnail_id);
        var uploaded_images_block = this.context.document.getElementById(this.images_block_id);
        
        // Заменяем иконку загрузки, если она есть.
        if (this.isWrap(uploaded_images_block.lastChild))
        {
            uploaded_images_block.lastChild.firstChild.setAttribute('src', "/i/150x100" + thumbnail_path);
            uploaded_images_block.lastChild.appendChild(remove_link);
        }
        // Тут иконки прелоадера нет - ситуация когда был загружен недопустимый файл
        // и прелоадер был убран динамически в методе this.uploadFail().
        else {
            var img = createThumbnailImage("/i/150x100" + thumbnail_path);
            var wrap = this.createThumbnailWrap();
            wrap.appendChild(img);
            wrap.appendChild(remove_link);

            uploaded_images_block.appendChild(wrap);
        }
        
        // Добавление hidden-полей в основную форму.
        var input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('name', 'thumbnail[]');
        input.setAttribute('value', thumbnail_id);
        this.context.document.getElementById(this.main_form_id).appendChild(input);

        this.checkDisabled();
    },
    
    /**
     * Установка на ссылки удаления изображений обработчиков событий.
     * Каждые полсекунды проверяется, имеется ли ссылка без установленного события и если таковая есть,
     * то на данную ссылку ставится обработчик.
     */
    observer: function(){
        this.checkDisabled();
        this.createThumbnailImageIcon();
        
        var _this = this;
        setInterval(function() {
            var wraps = _this.context.document.getElementById(_this.images_block_id).getElementsByTagName('span');
            for (var j=0; j < wraps.length; j++) {
                // Ссылка удаления изображения еще не подгрузилась в DOM.
                if (wraps[j].lastChild.tagName.toUpperCase() != 'A') {
                    return;
                }
                
                // Не даем проставится событию на элемен более одного раза.
                // И не ставим событие на span обрамления изображения.
                if (wraps[j].lastChild.dataset.eventIsSet) {
                    continue;
                }
                
                Krugozor.Events.attachEvent(wraps[j].lastChild, 'click', function(e){
                    _this.process_remove_thumbnail(e);
                });
                
                // Флаг, что событие проставлено на элемент.
                wraps[j].lastChild.dataset.eventIsSet = true;
            }
            
        }, 500);
    },
    
    /**
     * Обработчик нажатия кнопки удаления изображения.
     * 
     * @param event 
     */
    process_remove_thumbnail: function(e){
    	var event = Krugozor.Events.getEvent(e);
        var target = Krugozor.Events.getTarget(e);
        var images_block = target.parentNode.parentNode;
        var id = target.dataset.id;
        var _this = this;
        
        // Если идет попытка удалить изображение, привязанное к объявлению - делаем Ajax запрос.
        if (id && target.dataset.advert) {
            var ajax = new Krugozor.Ajax();
            ajax.setObserverState(function(ajx, xhr) {
                var response = ajx.getJson();
                if (response.result) {
                    $(target.parentNode).fadeOut(400, function(){
                        target.parentNode.parentNode.removeChild(target.parentNode);
                        _this.checkNodesInImagesBlock(images_block);
                    });
                }
            });
            ajax.get('/advert/thumbnail-unlink/?id=' + id);
        } else {
            // В противном случае просто скрываем изображение.
            $(target.parentNode).fadeOut(400, function(){
                target.parentNode.parentNode.removeChild(target.parentNode);
                _this.checkNodesInImagesBlock(images_block);
            });
        }
        
        // Получаем скрытые поля и удаляем поле с ID удаленного изображения.
        var hidden_thumbnail_list = document.getElementsByName('thumbnail[]');
        
        for (var j=0; j < hidden_thumbnail_list.length; j++) {
            if (hidden_thumbnail_list[j].nodeType == 1 && hidden_thumbnail_list[j].value == id) {
                hidden_thumbnail_list[j].parentNode.removeChild(hidden_thumbnail_list[j]);
                break;
            }
        }
        
        this.checkDisabled();
        Krugozor.Events.preventEvent(event);
    },
    
    /**
     * Ставит или снимает disabled на кнопку загрузки файла изображения, в зависимости от того, достигнут лимит загруженных 
     * изображений или нет.
     * 
     * @param void
     */
    checkDisabled: function() {
        this.context.document.getElementById(this.upload_form_id).elements.namedItem('file').disabled = (
            this.context.document.getElementsByName('thumbnail[]').length >= this.max_files
        );
    },
    
    /**
     * Создание элемента прелоадера и его подгрузка.
     * 
     * @param void
     * @return HTMLImageElement
     */
    createThumbnailImageIcon: function(){
        var thumbnail_icon = new Image();
        thumbnail_icon.src = this.thumbnail_icon_path;
        
        return thumbnail_icon;
    },
    
    /**
     * Создание элемента изображения.
     * 
     * @param string путь к изображению
     * @return HTMLImageElement
     */
    createThumbnailImage: function(path){
        var img = new Image();
        img.setAttribute('alt', '');
        img.setAttribute('src', path);
        
        return img;
    },
    
    /**
     * Создание обрамления изображения.
     * 
     * @param void
     * @return HTMLSpanElement
     */
    createThumbnailWrap: function(){
        var thumbnail_wrap = document.createElement('span');
        return thumbnail_wrap;
    },
    
    /**
     * Создание ссылки на удаление изображения.
     * 
     * @param int ID изображения
     * @return HTMLAnchorElement
     */
    createThumbnailRemoveLink: function(thumbnail_id){
        var link = document.createElement('a');
        link.setAttribute('href', "#");
        link.setAttribute('title', "Удалить изображение");
        link.setAttribute('data-id', thumbnail_id);
        link.setAttribute('data-advert', '');
        return link;
    },
    
    /**
     * Возвращает true, если elenet - обрамляющий тег изображения.
     * 
     * @param HTMLElement
     * @return true
     */
    isWrap: function(element) {
        return element.nodeType == 1 && element.tagName.toUpperCase() == 'SPAN';
    },
    
    /**
     * Инициализация текстового узла сообщения об ошибке и установка сообщения об ошибке error.
     * 
     * @param string
     */
    setUploadErrorState: function(error){
        var errors_block = this.context.document.getElementById(this.errors_block_id);
        
        if (errors_block.childNodes.length == 0) {
            errors_block.appendChild(document.createTextNode(''));
        }
        
        errors_block.firstChild.nodeValue = error || '';
        errors_block.style.display = error ? 'block' : 'none';
    },
    
    /**
     * Проверяет наличие детей в блоке изображений.
     * Если их нет - скрывает блок.
     * 
     * @param object блок с изображениями
     * @return void
     */
    checkNodesInImagesBlock: function(images_block) {
    	if (images_block.childNodes.length == 0) {
    		images_block.style.display = 'none';
    		return;
    	}
    	for (var i = 0; i < images_block.childNodes.length; i++) {
    		if (images_block.childNodes[i].nodeType == 1) {
    			images_block.style.display = 'true';
    			return;
    		}
    	}
    	
    	images_block.style.display = 'none';
    }
}