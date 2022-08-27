<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/snazzy-info-window.min.css'); ?>?v=1.01">
	<script src="<?php echo base_url('assets/js/jquery.js'); ?>"></script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('gmaps_key'); ?>&language=en&region=CA&libraries=places"></script>
	<script src="<?php echo base_url('assets/js/snazzy-info-window.js'); ?>?v=1.01"></script>
	<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
	
	<style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map_canvas {
        height: 100%;
      }
    </style>
	<title>Tree Doctors Routes</title>
</head>
<body style="margin: 0">
	<script>
	var map; // Global declaration of the map
	var iw = {};
	var iwP = [];
	var lat_longs = new Array();
	var markers = new Array();
	var markersParkings = new Array();
	var gpsData = <?php echo $route; ?>;
	var parkingsData = <?php echo $parkings; ?>;
	var planCoordinates = [];
	var markerCluster = {};
    var MAP_CENTER_LAT = '<?php echo config_item('map_lat') ? config_item('map_lat') : '0'; ?>';
    var MAP_CENTER_LON = '<?php echo config_item('map_lon') ? config_item('map_lon') : '0'; ?>';
    var OFFICE_LAT = '<?php echo config_item('office_lat') ? config_item('office_lat') : '0'; ?>';
    var OFFICE_LON = '<?php echo config_item('office_lon') ? config_item('office_lon') : '0'; ?>';

	//console.log(parkingsData.data);
	/*delete parkingsData.data[5];
	delete parkingsData.data[4];
	delete parkingsData.data[3];
	delete parkingsData.data[2];
	delete parkingsData.data[1];
	delete parkingsData.data[0];*/
	
	var offsetCenter = function(dx, dy, center) {
        return { lat: center.lat + dx, lng: center.lng + dy };
    };
    var dx = 0.001;
    var dy = 0.001;

	$.each(gpsData, function(key, val){
		planCoordinates.push({lat:parseFloat(val.lat), lng:parseFloat(val.lon)});
	});
	function initialize_map() {
		iw = new google.maps.InfoWindow();
		var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;

		/*$('#map_canvas').css('height', $(window).height());*/

		var coords = {
			lat: MAP_CENTER_LAT,
			lng: MAP_CENTER_LON
		};
		var parkingIcon = $(window).width() >= 1200 ? "/assets/img/tracker/parking.png" : "/assets/img/tracker/parking_11.png";
		var aIcon = $(window).width() >= 1200 ? "/assets/img/tracker/a.png" : "/assets/img/tracker/a_11.png";
		var bIcon = $(window).width() >= 1200 ? "/assets/img/tracker/b.png" : "/assets/img/tracker/b_11.png";


		if(parkingsData.data.length && parkingsData.data[0])
			coords = {
				lat: parkingsData.data[0][3],
				lng: parkingsData.data[0][4]
			};

		var myLatlng = new google.maps.LatLng(coords.lat, coords.lng);
		var myOptions = {
	  		zoom: 11,
			center: myLatlng,
	  		mapTypeId: google.maps.MapTypeId.ROADMAP,
	  		disableDefaultUI: true
	  	}

	  	bounds  = new google.maps.LatLngBounds();
	  	//bounds.extend(myLatlng);
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

		var baloonFrames = [ 
			{ type: 'top', LatLng: offsetCenter(dx, 0, coords) },
			{ type: 'right', LatLng: offsetCenter(0, dy, coords) },
			{ type: 'bottom', LatLng: offsetCenter(-dx, 0, coords) },
			{ type: 'left', LatLng: offsetCenter(0, -dy, coords) }
		];

		var office = [
			{'lat' : 43.606330, lng : -79.524709},
			{'lat' : 43.604768, lng : -79.515833},
		];

		$.each(parkingsData.data, function(key, val){
			
			if(val) {
				iwP[key] = new google.maps.InfoWindow();
				var parkingTime = parseInt((new Date(val[2]).getTime() - new Date(val[1]).getTime()) / 1000);
				//var parkingTimeHrs = parseInt((new Date(val[2]).getTime() - new Date(val[1]).getTime()) / 1000 / 60 / 60);
				//parkingTimeMins = parkingTimeMins.toString().length == 1 ? '0' + parkingTimeMins.toString() : parkingTimeMins.toString();
				//parkingTimeHrs = parkingTimeHrs.toString().length == 1 ? '0' + parkingTimeHrs.toString() : parkingTimeHrs.toString();
				//var parkingTime = parkingTimeHrs + ':' + parkingTimeMins
				//var parkingIcon = 'https://chart.googleapis.com/chart?chst=d_bubble_icon_text_small&chld=parking|' + (baloonFrames[key % 4]) + '|' + parkingTime + 'min|4285f4|e9f3fb';
				if(val[3] > office[0].lat || val[3] < office[1].lat ||
					val[4] < office[0].lng || val[4] > office[1].lng) {
					marker = new google.maps.Marker({
			        	map: map,
			        	icon: parkingIcon,
			            position: new google.maps.LatLng(val[3],val[4])
			        });
			        bounds.extend(new google.maps.LatLng(val[3],val[4]));
			        var content = '<div style="text-align: center;"><strong>Device: ' + val[0] + '</strong></div>' +
			        	'Period: ' + val[6] + 'min.<br>' +
			        	'Start Time: ' + val[1] + '<br>' + 
			        	'End Time: ' + val[2] + '<br>';
			        marker.set("content", content);
			        marker.set("parkingTime", parkingTime);
				
		        /*if(val[3] > office[0].lat || val[3] < office[1].lat ||
					val[4] < office[0].lng || val[4] > office[1].lng) {
		        	var content = parkingTimeHrs + ':' + parkingTimeMins + '<div class="pos-abt badge bg-success si-t" style="font-size: 8px;padding: 1px 0;top: 1px;right: 1px;">' + (key + 1) + '</div>';
			        var info = new SnazzyInfoWindow($.extend({}, {
			            marker: marker,
			            placement: 'top',//baloonFrames[key % 4].type,
			            content: content,//marker.get("content"),
			            panOnOpen: false,
			            closeOnMapClick: false,
			        }));
			        //console.log(info);
			        info.open();
		        }*/
		        
				    google.maps.event.addListener(marker, "click", function(event) {
				    	iwP[key].setContent(this.get("content"));
				    	iwP[key].open(map, this);
					});
		    		markersParkings.push(marker);
		    	}
			}

		});
		markerCluster = new MarkerClusterer(map, markersParkings, {
			imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
			gridSize: 20,
		});

      	$.each(gpsData, function(key, val){
    		var icon = getIcon(val.direction);
    		if(!key && gpsData.length > 1) {
    			icon = aIcon;
    			val.lat = parseFloat(val.lat);
    			val.lon = parseFloat(val.lon) + 0.000003;
    		}
    		if(key + 1 == gpsData.length) {
    			icon = bIcon;
    			val.lat = parseFloat(val.lat) + 0.000003;
    			val.lon = parseFloat(val.lon);
    		}
    		marker = new google.maps.Marker({
	        	map: map,
	        	icon: icon,
	            position: new google.maps.LatLng(val.lat,val.lon)
	        });
	        bounds.extend(new google.maps.LatLng(val.lat,val.lon));
	        var content = '<div style="text-align: center;"><strong>Device: <?php echo $truck->item_name; ?></strong></div>' + 
	        	'Speed: ' + val.speed + 'km/h<br>' + 
	        	'GPS Time: ' + val.gpsDateString + '<br>';

	        marker.set("content", content);
		    google.maps.event.addListener(marker, "click", function(event) {
		    	iw.setContent(this.get("content"));
		    	iw.open(map, this);
			});
    		markers.push(marker);

    		if(gpsData.length == 1) {
    			icon = aIcon;
    			val.lat = parseFloat(val.lat);
    			val.lon = parseFloat(val.lon) + 0.000003;

    			marker = new google.maps.Marker({
		        	map: map,
		        	icon: icon,
		            position: new google.maps.LatLng(val.lat,val.lon)
		        });
		        bounds.extend(new google.maps.LatLng(val.lat,val.lon));

    			var content = '<div style="text-align: center;"><strong>Device: <?php echo $truck->item_name; ?></strong></div>' + 
	        	'Speed: ' + val.speed + 'km/h<br>' + 
	        	'GPS Time: ' + val.gpsDateString + '<br>';

		        marker.set("content", content);
			    google.maps.event.addListener(marker, "click", function(event) {
			    	iw.setContent(this.get("content"));
			    	iw.open(map, this);
				});
    			markers.push(marker);
    		}
		});

		map.fitBounds(bounds);
		map.panToBounds(bounds);

        var path = new google.maps.Polyline({
          path: planCoordinates,
          geodesic: true,
          strokeColor: '#FF0000',
          strokeOpacity: 1.0,
          strokeWeight: 2
        });

        path.setMap(map);

        setTimeout(function(){
        	$.each(markerCluster.clusters_, function(k, cluster){
				var time = 0;
				var clusterLat = cluster.center_.lat();
				var clusterLng = cluster.center_.lng();
				//console.log(cluster);

				if(clusterLat > office[0].lat || clusterLat < office[1].lat || clusterLng < office[0].lng || clusterLng > office[1].lng) {

					var clusterBound = new google.maps.LatLngBounds();

					$.each(cluster.markers_, function(key, marker) {
						time += parseInt(marker.parkingTime);
						clusterBound.extend(new google.maps.LatLng(marker.position.lat(), marker.position.lng()));
					});

					var parkingTimeMins = parseInt(time / 60 % 60);
					var parkingTimeHrs = parseInt(time / 60 / 60);
					parkingTimeMins = parkingTimeMins.toString().length == 1 ? '0' + parkingTimeMins.toString() : parkingTimeMins.toString();
					parkingTimeHrs = parkingTimeHrs.toString().length == 1 ? '0' + parkingTimeHrs.toString() : parkingTimeHrs.toString();
					var parkingTimeString = parkingTimeHrs + ':' + parkingTimeMins;

					//console.log(minLatCoords.lat, marker.position.lng);
					cluster.position = clusterBound.getCenter();//new google.maps.LatLng(clusterLat, clusterLng);

					var content = parkingTimeString;// + '<div class="pos-abt badge bg-success si-t" style="font-size: 8px;padding: 1px 0;top: 1px;right: 1px;">' + /*(key + 1) +*/ '</div>';
			        var info = new SnazzyInfoWindow($.extend({}, {
			            marker: cluster,
			            placement: 'top',//baloonFrames[key % 4].type,
			            content: content,//marker.get("content"),
			            panOnOpen: false,
			            closeOnMapClick: false,
			        }));
			        info.open();
				}
			});
        }, 1000);
        
	}

	function getIcon(direction) {
        var baseImagePath = "/assets/img/tracker/blue_";
        if (direction >= 337.5 || direction < 22.5) {
            baseImagePath += $(window).width() >= 1366 ? "n.png" : "n_8.png";
        } else if (direction >= 22.5 && direction < 67.5) {
            baseImagePath += $(window).width() >= 1366 ? "ne.png" : "ne_8.png";
        } else if (direction >= 67.5 && direction < 112.5) {
            baseImagePath += $(window).width() >= 1366 ? "e.png" : "e_8.png";
        } else if (direction >= 112.5 && direction < 157.5) {
            baseImagePath += $(window).width() >= 1366 ? "se.png" : "se_8.png";
        } else if (direction >= 157.5 && direction < 202.5) {
            baseImagePath += $(window).width() >= 1366 ? "s.png" : "s_8.png";
        } else if (direction >= 202.5 && direction < 247.5) {
            baseImagePath += $(window).width() >= 1366 ? "sw.png" : "sw_8.png";
        } else if (direction >= 247.5 && direction < 292.5) {
            baseImagePath += $(window).width() >= 1366 ? "w.png" : "w_8.png";
        } else if (direction >= 292.5 && direction < 337.5) {
            baseImagePath += $(window).width() >= 1366 ? "nw.png" : "nw_8.png";
        }
        return baseImagePath;
	}
	
	window.onload = initialize_map;
		
	</script>
	<div id="map_canvas" style="width:100%; height:100%;"></div>
</body>
</html>
