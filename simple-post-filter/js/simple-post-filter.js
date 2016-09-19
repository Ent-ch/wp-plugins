(function($) {
$(function() {
	function formatDate(date) {
		var dd = date.getDate()
		if ( dd < 10 ) dd = '0' + dd;

		var mm = date.getMonth()+1
		if ( mm < 10 ) mm = '0' + mm;

		var yy = date.getFullYear();

		return dd+'.'+mm+'.'+yy;
	}

	$.datepicker.regional['ru'] = {
		closeText: 'Закрыть',
		prevText: '&#x3c;Пред',
		nextText: 'След&#x3e;',
		currentText: 'Сегодня',
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
		'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
		'Июл','Авг','Сен','Окт','Ноя','Дек'],
		dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
		dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
		dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
		weekHeader: 'Не',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ru']);

    $( ".spfilter-category" ).SumoSelect({
			placeholder: 'Выберите категории',
			csvDispCount: 10 
			});
	
    $( ".spfilter-post_tag" ).SumoSelect({
			placeholder: 'Выберите теги',
			csvDispCount: 10 
			});
	
    $( "#sp-filter-from" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      onClose: function( selectedDate ) {
        $( "#sp-filter-to" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#sp-filter-to" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      onClose: function( selectedDate ) {
        $( "#sp-filter-from" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
	
	$( ".spfilter2-post_tag" ).click(function(){
		alert('yop');
	});

	$( ".spfilter-submit" ).click(function(){
		var param = [];
		var alltag = [];
/*		$(".spfilter-category option:selected").each(function () {
		   var $this = $(this);
		   if ($this.length) {
			selText += $this.text() + ',';
		   }
		}); */
		
		var selcats = $(".spfilter-category").val();
		if (selcats != undefined){
			var seltxt = selcats.join(',');
			param.push('fcats=' + seltxt);
		}
		
/*		var seltags = $(".spfilter-post_tag").val();
		if (seltags != undefined){
			var seltxt = seltags.join(',');
			param.push('ftags=' + seltxt);
		} */
		$(".spfilter-post_tag").each(function(){
			var seltags = $(this).val();
			if (seltags != undefined){
				alltag = alltag.concat(seltags);
			}		
		});

		if (alltag.length > 0){
			var seltagtxt = alltag.join(',');
			param.push('ftags=' + seltagtxt);
		}
		
		var seldatefrom = $("#sp-filter-from").val();
		if (seldatefrom != ''){
			param.push('datefrom=' + seldatefrom);
		}
		
		var seldateto = $("#sp-filter-to").val();
		if (seldateto != ''){
			param.push('dateto=' + seldateto);
		}
		
		
		if (param.length > 0){
			var newUrl = param.join('&');
			window.location.href = '?' + newUrl;
		}


	});
	$('.spfilter-selper a').click(function(){
		var cid = $(this).attr('id');
		$('.spfilter-selper a').removeClass('sfa-sel');
		$(this).addClass('sfa-sel');
		
		var now = new Date();
		var before = new Date();
		switch (cid) {
		   case 'sp-filter-w':
			  before.setDate( now.getDate() - 7);
			  break;
		   case 'sp-filter-m':
			  before.setDate( now.getDate() - 30);
			  break;
		   case 'sp-filter-y':
			  before.setDate( now.getDate() - 365);
			  break;
		}
		$('#sp-filter-from').val(formatDate(before));
//		$('#sp-filter-to').val(formatDate(now));
		return false;
	});
	
})
})(jQuery)

