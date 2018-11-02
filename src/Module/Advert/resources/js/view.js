var imitationLink = function(_this){
    if (_this.getAttribute("data-url")) {
       window.location.href = _this.getAttribute("data-url");
    }
};

var viewAdvertEmail = function(_this, id_advert, hash){
    var ajax = new Krugozor.Ajax();

    var img = document.createElement('IMG');
    img.src = '/http/image/desing/icon/ajax-loader-small.gif';
    
    var parent = _this.parentNode;
    parent.replaceChild(img, _this.parentNode.firstChild);
    
    ajax.setObserverState(function(ajx, xhr) {
        var email = this.email || 'Не могу получить email-адрес';

        var a = document.createElement('A');
        a.setAttribute('href', 'mailto:' + email);
        a.appendChild(document.createTextNode(email));
        setTimeout(function(){
        	parent.replaceChild(a, img);
        }, 800);
    }, true);
    
    ajax.get('/advert/frontend-ajax-get-email/id/' + id_advert + '/hash/' + hash);
    
    return false;
};

var viewAdvertPhone = function(_this, id_advert){
    var img = document.createElement('IMG');
    img.src = '/http/image/desing/icon/ajax-loader-small.gif';
    
    var parent = _this.parentNode;
    parent.replaceChild(img, _this.parentNode.firstChild);

    var ajax = new Krugozor.Ajax();
    
    ajax.setObserverState(function(ajx, xhr) {
        var span = document.createElement('SPAN');
        span.appendChild(document.createTextNode(this.phone || 'Не могу получить телефон'));
        setTimeout(function(){parent.replaceChild(span, img);}, 800);
    }, true);
    
    ajax.get('/advert/frontend-ajax-get-phone/id/' + id_advert);
    
    return false;
};