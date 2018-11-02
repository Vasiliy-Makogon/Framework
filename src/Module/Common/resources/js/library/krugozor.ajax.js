"use strict";

var Krugozor = window.Krugozor || {};

/*
    Пример использования:

    <!DOCTYPE html>
    <html><head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="/krugozor.ajax.js"></script>
    <body>
    <script>
    function doo(){
        var ajax = new Krugozor.Ajax();
        ajax.setObserverState(
            function(ajx, xhr) {
               // Демонстрация применения обоих подходов - вызов setObserverState с разным параметром call вернет один и тот же результат,
               // но this пользовательской функции будет разным.
               document.getElementsByTagName('a')[0].firstChild.nodeValue = (this.var1 || ajx.getJson()['var1']) + ' ' + (this.var2 || ajx.getJson()['var2']);
            }, true
        );
        ajax.get("/response.php");
    }
    </script>
    <a href="#" onclick="doo()">xxxxxxx</a>
    </body>
    </html>
*/
Krugozor.Ajax = function()
{
    /**
     * Экземпляр XMLHttpRequest.
     * 
     * @var XMLHttpRequest
     */
    var xhr;

    /**
     * Добавлять ли уникальную числовую строку к запросу в QUERY_STRING.
     * 
     * @var bool
     */
    this.addUniqueQS = false;
    
    /**
     * Создает объект XMLHttpRequest
     * 
     * @param void
     * @throws exception
     * @return void
     */
    (function(){
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        }
        // only IE 6 =< 
        else if (window.ActiveXObject) {
            try {
                var aVersions = ["MSXML2.XMLHttp.5.0", "MSXML2.XMLHttp.4.0", 
                                 "MSXML2.XMLHttp.3.0", "MSXML2.XMLHttp",
                                 "Msxml2.XMLHTTP", 'Microsoft.XMLHTTP'];

                for (var j in aVersions){
                    try {
                        xhr = new ActiveXObject(aVersions[j]);
                        break;
                    } catch (e){}
                }
            } catch (e){}
        }

        if (!xhr) {
            throw "XMLHttprequest does not work in your browser";
        }
    })();
    
    /**
     * Предопределяемый метод, привязанный к обработчику onreadystatechange.
     * См this.setObserverState()
     *
     * @param void
     * @return mixed
     */
    this.observerState = function() {
        throw 'ObserverState method should be predetermined before the use of the facility';
    };
    
    /**
     * Устанавливает наблюдатель состояния.
     * Метод принимает в качестве аргумента анонимную функцию,
     * которая привязывается к обработчику onreadystatechange.
     * Функция должна иметь интерфейс для принятия двух параметров:
     * - ссылка на данный объект
     * - ссылка на объект xmlHttpRequest
     * 
     * @param function 
     * @param bool вызывать ли функцию func через call. true - вызывать, false - нет. 
     *             Если функция вызывается через call, то в this - объект ответа данных. 
     * @return Krugozor.Ajax
     */
    this.setObserverState = function(func, сall) {
        if (сall == undefined) {
            сall = false;
        }
        
        var that = this;

        this.observerState = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    if (сall) {
                        func.call(that.getJson(), that, xhr);
                    } else {
                        func(that, xhr);
                    }
                }
            }
        };

        return this;
    };

    /**
     * Отправляет GET-запрос по адресу url.
     * 
     * @param string url
     * @param boolean синхронность запроса. true - асинхронный, false - синхронный.
     *                По умолчанию - асинхронный.
     * @return Krugozor.Ajax
     */
    this.get = function(url, synchronicity) {
        if (synchronicity == undefined) {
            synchronicity = true;
        }

        if (!!this.addUniqueQS){
            url += (url.indexOf('?') == -1 ? '?' : '&') + Math.floor(Math.random() * 1000);   
        }

        xhr.open('GET', url, !!synchronicity);
        xhr.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
        xhr.setRequestHeader("Cache-Control", "no-cache");
        
        if (synchronicity) {
            xhr.onreadystatechange = this.observerState;
        }

        xhr.send(null);

        return this;
    };

    /**
     * Возвращает объект с данными, если responseText был в формате JSON.
     *
     * @param void
     * @return object
     */
    this.getJson = function() {
        return window.JSON && typeof window.JSON.parse == 'function' ? JSON.parse(xhr.responseText) : eval( "(" + xhr.responseText + ")" );
    };
}