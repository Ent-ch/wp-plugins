/*jslint unparam: true, sloppy: true, vars: true, white: true*/
/*global $, jQuery, alert*/

(function ($) {
    $(function () {
        var full_el_list, cur_el_list;

        function mark_brand(el) {
            $.get('/wp-admin/admin-ajax.php?action=commgr-get-brand&pcid=' + el, function (data) {
                var arr_el = $.parseJSON(data);
                $('#sel-brand option').each(function () {
                    var opt = $(this);
                    var zn = parseInt(opt.prop('value'), 10);
                    opt.prop('selected', false);
                    if ($.inArray(zn, arr_el) >= 0) {
                        opt.prop('selected', true);

                    }
                });
            });
        }

        function hide_elem(el) {
            $.get('/wp-admin/admin-ajax.php?action=commgr-get-brand&pcid=' + el, function (data) {
                var arr_el = $.parseJSON(data);
                $('#comm-sel-element').html(full_el_list);
                cur_el_list = '';
                $('#comm-sel-element option').each(function () {
                    var opt = $(this);
                    var zn = parseInt(opt.prop('value'), 10);
                    if ($.inArray(zn, arr_el) >= 0) {
                        cur_el_list += $('<div>').append(opt.clone()).html();
                    }
                });
                $('#comm-sel-element').html(cur_el_list);
            });
        }

        $('#comm-sel-brand').change(function () {
            var el = $(this).val();
            hide_elem(el);
        });

        $('#sel-element').change(function () {
            var el = $(this).val();
            mark_brand(el);
        });

        $('#selectgr').submit(function () {
            var fdata = $(this).serialize();
            $.post("/wp-admin/admin-ajax.php", fdata + '&action=commgr-set-brand')
                .done(function (data) {
                    $("#comm-message").hide();
                    $("#comm-message").html('Данные обновлены').slideToggle().delay(2000).slideToggle();
                });

            return false;
        });


        full_el_list = $('#comm-sel-element').html();

        if ($('#comm-sel-brand').length) {
            hide_elem($('#comm-sel-brand').val());
        }

        if ($('#sel-element').length){
            mark_brand($('#sel-element').val());
        }

        $('#rating-a').barrating();
        $('.bxslider').bxSlider({
            pager: false,
            minSlides: 6,
            maxSlides: 6,
            slideWidth: 125,
            slideMargin: 15
        });

    });
}(jQuery));