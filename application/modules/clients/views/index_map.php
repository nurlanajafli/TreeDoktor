<?php $this->load->view('includes/header'); ?>
<script type="text/javascript" src="<?php echo base_url('assets/js/markerclusterer.js'); ?>"></script>
<script type="text/javascript">
	var map;
	var iw = new google.maps.InfoWindow();
	var clients = <?php echo $json; ?>;
	var markers = [];
	var markerCluster;
	var num = 0;

	function initialize() {
		var latlng = new google.maps.LatLng(MAP_CENTER_LAT, MAP_CENTER_LON);
		var myOptions = {
			zoom: 5,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = window.map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	}

	function createMarker(markerOptions) {
		var marker = new google.maps.Marker(markerOptions);
		//markers.push(marker);
		//lat_longs.push(marker.getPosition());
		return marker;
	}

	$(document).ready(function(){
		initialize();
		$.each(clients, function(key, val){
			if(val.lat >= 43 && val.lat <= 45 && val.lon <= -78 && val.lon >= -81)
			{
				var latlng = new google.maps.LatLng(val.lat, val.lon);
				var markerOptions = {
					map: map,
					position: latlng
				};
				markers[num] = createMarker(markerOptions);
				markers[num].set("content", '<a href="' + baseUrl + val.client_id + '">' + val.name + '</a><div>Created: ' + val.client_date + '</div><div>Address: ' + val.address + '</div>');
				google.maps.event.addListener(markers[num], "click", function(event) {
					iw.setContent(this.get("content"));
					iw.open(map, this);
				});
				num++;
			}
		});
		var clusterOptions = {
			gridSize: 60,
			minimumClusterSize: 2
		};
		markerCluster = new MarkerClusterer(map, markers, clusterOptions);
	});

</script>

<section class="scrollable p-sides-15 p-n mapper" style="top: 9px;">
	<div id="map_canvas" style="width: 100%; height: 100%"></div>
</section>

<?php $this->load->view('includes/footer'); ?>
