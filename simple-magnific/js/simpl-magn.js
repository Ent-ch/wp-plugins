/*global jQuery, console */
(function ($) {
    'use strict';
    $(document).ready(function () {
        $('.gallery').each(function () {
            $(this).magnificPopup({
                delegate: 'a', // the selector for gallery item
                type: 'image',
                tClose: 'Fechar (Esc)',
                tLoading: '',
                gallery: {
                    enabled: true
        //            tPrev: 'Anterior (Seta esquerda)',
        //            tNext: 'Próximo (Seta direita',
        //            tCounter: '%curr% de %total%'
                },
                image: {
                    tError: 'A imagem não pode ser carregada.'
                },
                mainClass: 'mfp-zoom-in',
                removalDelay: 300, //delay removal by X to allow out-animation
                callbacks: {
                    beforeOpen: function () {
                        $('#portfolio a').each(function () {
                            $(this).attr('title', $(this).find('img').attr('alt'));
                        });
                    },
                    open: function () {
                        //overwrite default prev + next function. Add timeout for css3 crossfade animation
                        $.magnificPopup.instance.next = function () {
                            var self = this;
                            self.wrap.removeClass('mfp-image-loaded');
                            setTimeout(function () { $.magnificPopup.proto.next.call(self); }, 120);
                        };
                        $.magnificPopup.instance.prev = function () {
                            var self = this;
                            self.wrap.removeClass('mfp-image-loaded');
                            setTimeout(function () { $.magnificPopup.proto.prev.call(self); }, 120);
                        };
                    },
                    imageLoadComplete: function () {
                        var self = this;
                        setTimeout(function () { self.wrap.addClass('mfp-image-loaded'); }, 16);
                    }
                }
            });
        });
        
        var h = $(window).height();
        function galeryAnim() {
            var i = 1;
            $('.attachment-galery-thumb2').not('.zoomIn').each(function () {
                var el = $(this);

                if (($(window).scrollTop() + h) >= el.offset().top) {
                    setTimeout(function () {
                        el.addClass('zoomIn');
                    }, 100 * i);
                    i++;
                }
            });
        }
        
        galeryAnim();
        
        $(window).scroll(function () {
            if (($(this).scrollTop() + h) >= $('.gallery').offset().top) {
                galeryAnim();
            }
        });
  
    });
}(jQuery));