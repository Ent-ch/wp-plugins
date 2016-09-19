/*global jQuery, console */
(function ($) {
    'use strict';
    $(document).ready(function () {
        $('.gallery').each(function () {
            $(this).magnificPopup({
                delegate: 'a', // the selector for gallery item
                type: 'image',
                tLoading: '',
                gallery: {
                    enabled: true
                },
                mainClass: 'mfp-zoom-in',
                removalDelay: 300,
                callbacks: {
                    beforeOpen: function () {
                        $('#portfolio a').each(function () {
                            $(this).attr('title', $(this).find('img').attr('alt'));
                        });
                    },
                    open: function () {
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
            $('.attachment-galery-thumb2, .attachment-galery-thumb3').not('.zoomIn').each(function () {
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
            if (($(this).scrollTop() + h) >= $('.attachment-galery-thumb2, .attachment-galery-thumb3').offset().top) {
                galeryAnim();
            }
        });
  
    });
}(jQuery));