var jQuery;
(function($) {
$(function() {

    $( "#accordion" ).accordion();
    $('.clbtn').click(function (){
        $.get('/wp-admin/admin-ajax.php?action=simple_cart-button&clcart=1', function(data){
            $('.scart-items').hide();
            $('#txttovs').val('');
        });
    });
    $('.bybtn').click(function (){
        var btn = $(this);
        var prid = btn.data('pid');
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type : "post",
            data: {
                'action':'simple_cart-button',
                'prid' : prid
            },
            success:function(data) {
                btn.hide();
                $('.cstatus').html('Товар добавлен');
                
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });  

    });
    
    $('#menu-item-407 a').click(function(){
       $(this).attr('target', '_blanck');
    });
});
})(jQuery);
