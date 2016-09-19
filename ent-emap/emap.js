var arrlocations;
var zoomlevel = 8;
var clat = 43.24; 
var clon = 76.87;
var mrkdraggable = true;
var lsnclick = true;
var markers = [];
var catimgs = [];

(function($) {

function emap_setMarkers(map, locations) {
  var myCoordsLenght = 6;
  for (var i = 0; i < locations.length; i++) {
    var loc = locations[i];
	var image = {
		url: catimgs[loc[4]],
		size: new google.maps.Size(20, 20),
		origin: new google.maps.Point(0,0),
		anchor: new google.maps.Point(10, 20)
	  };
	
    var myLatLng = new google.maps.LatLng(loc[1], loc[2]);
    var marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
		draggable: mrkdraggable,
        icon: image,
        poiurl: loc[3],
        catid: loc[4],
        title: loc[0]
    });

	if(mrkdraggable) {
		google.maps.event.addListener(marker, 'dragend', function(evt){
			$('#emap-coordinates').val( evt.latLng.lat().toFixed(myCoordsLenght) + ',' + evt.latLng.lng().toFixed(myCoordsLenght) );
			$('#emap-zoomlevel').val( map.getZoom() );
		});
	}

	if(lsnclick) {
		google.maps.event.addListener(marker, 'click', function() {
			window.location.href = this.poiurl;
		});
	}
	markers.push(marker);
  }
}

function setAllMap(map, catid) {
  for (var i = 0; i < markers.length; i++) {
	if(markers[i].catid == catid || catid == 0)
		markers[i].setMap(map);
	else
		markers[i].setMap(null);
  }
}

$(function() {
	
	$('.emap-filter').click(function(){
		var cid = $(this).data('cid');
//		alert(cid);
		setAllMap(map, cid);
		
		return false;
	});


	function emap_initialize(myLatlng) {
	  var mapOptions = {
		zoom: zoomlevel,
		center: myLatlng
	  };
	  map = new google.maps.Map($('#map-canvas')[0], mapOptions);
	  return map;
	  
	}

	if (($("#map-canvas").length > 0)){
		myLatlng = new google.maps.LatLng(clat, clon);
		map = emap_initialize(myLatlng);
		
		if (arrlocations == undefined){
			emap_setMarkers(map, [['Новая позиция', clat, clon]]);
		}
		else {
			emap_setMarkers(map, arrlocations);
		}
	}

})
})(jQuery);
