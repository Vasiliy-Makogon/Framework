window.onload = function(){
	
	// "Подать объявление бесплатно и без регистрации в раздел" - имитация ссылок для пользователей, что бы не учитывались ПС.
    if (document.getElementById('js_add_advert')) {
        Krugozor.Events.attachEvent(document.getElementById('js_add_advert'), 'click', function(e){
        	
        	var event = Krugozor.Events.getEvent(e);
            Krugozor.Events.preventEvent(event);
            
            var set = event.currentTarget.getElementsByTagName('div')[0].dataset;
            var uri = '';
            
            for (var i in set) {
                if (!set[i]) {
                    continue;
                }
                
                uri += i + '=' + set[i] + '&';
            }
            
            var link = event.currentTarget.getElementsByTagName('div')[0].getElementsByTagName('a')[0].getAttribute('href');
            window.location.href = link + (uri ? '?' + uri : '');
        });
    }

};