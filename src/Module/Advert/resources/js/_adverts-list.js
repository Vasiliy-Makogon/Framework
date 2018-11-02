"use strict";

/**
 * Позиционирование окна popup.
 */
var popup = function(jQueryElement, effectFunction)
{
    var widthEl = jQueryElement.width();
    var hightEl = jQueryElement.height();
    
    var vertical = ($(window).height() - hightEl) / 2;
    var horizontal = ($(window).width() - widthEl) / 2;
    
    if (jQueryElement.css('position') != 'fixed') {
        vertical = $(window).scrollTop() + vertical;
        horizontal = $(window).scrollLeft() + horizontal;
    }
    
    jQueryElement.css('top', vertical);
    jQueryElement.css('left', horizontal);

    jQueryElement.doEffect = effectFunction;
    jQueryElement.doEffect();
};

$(document).ready(function(){
	if ($().lazyload) {
        $("img.lazy").lazyload({
        	effect : "fadeIn"
        });
    }

    $('li.advert_vip a').each(function(index, element) {
        $(element).click(function() {
            var overlay = Krugozor.Overlay.addOverlay(0.6, function(){
                $('aside#payment_popup').slideUp(300, function(){
                    overlay.removeOverlay();
                });
            });

            var url = $(this).attr('href');
            popup($('aside#payment_popup'), function() {
                this.find('input[type=button]').on('click', function(){
                    window.location.href = url;
                });
                this.slideDown(400);
            });

            return false;
        });
    });

    // назначаем cобытие на ссылку "выделить объявление"
    $('li.advert_special a').each(function(index, element) {
        $(element).click(function() {
            var overlay = Krugozor.Overlay.addOverlay(0.6, function(){
                $('aside#special_popup').slideUp(300, function(){
                    overlay.removeOverlay();
                });
            });

            var url = $(this).attr('href');
            popup($('aside#special_popup'), function() {
                this.find('input[type=button]').on('click', function(){
                    window.location.href = url;
                });
                this.slideDown(400);
            });

            return false;
        });
    });
});