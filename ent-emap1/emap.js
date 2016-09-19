var arrlocations;
var zoomlevel = 8;
var clat = 46.94; 
var clon = 142.72;
var mrkdraggable = true;
var simple_gmap;


(function($) {

function emap_setMarkers(map, locations) {
  var myCoordsLenght = 6;
  for (var i = 0; i < locations.length; i++) {
	
    var loc = locations[i];
    var myLatLng = new google.maps.LatLng(loc[1], loc[2]);
    var marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
		draggable: mrkdraggable,
//        icon: image,
        title: loc[0]
    });

	if(mrkdraggable) {
		google.maps.event.addListener(marker, 'dragend', function(evt){
			$('#emap-coordinates').val( evt.latLng.lat().toFixed(myCoordsLenght) + ',' + evt.latLng.lng().toFixed(myCoordsLenght) );
		});
	}
	
  }
}


$(function() {

function emap_initialize(myLatlng) {
  var mapOptions = {
    zoom: zoomlevel,
    center: myLatlng
  };
  map = new google.maps.Map($('#map-canvas')[0], mapOptions);
  return map;
  
}


$('#e-map-show').click(function(){
    if (simple_gmap === undefined){
        myLatlng = new google.maps.LatLng(clat, clon);
        simple_gmap = emap_initialize(myLatlng);
        emap_setMarkers(simple_gmap, arrlocations);
    }
});

if (($("#map-canvas").length > 0) && $('#e-map-show').length === 0){
	myLatlng = new google.maps.LatLng(clat, clon);
	map = emap_initialize(myLatlng);
	
	if (arrlocations === undefined){
		emap_setMarkers(map, [['Новая позиция', clat, clon]]);
	}
	else {
		emap_setMarkers(map, arrlocations);
	}
}

});
})(jQuery);
