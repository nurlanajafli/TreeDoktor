<?php $this->load->view('includes/header'); ?>

	<section class="scrollable p-sides-15">
		<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
			<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
			<li class="active">General</li>
		</ul>
		<section class="panel panel-default p-n">
		<header class="panel-heading">Filter
			<div class="pull-right" style="margin-top:-14px;">
				<form id="dates" method="post" action="<?php echo base_url('equipment/map'); ?>" class="input-append m-t-xs inline" >
					<select name="truck" class="form-control truck inline" style="width: 106px;">
						
						<?php foreach($items as $k=>$v) : ?>
							<option <?php if(isset($truck) && $truck->eq_id == $v->eq_id) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->eq_code;?>">
								<?php echo $v->eq_code;?>
							</option>
						<?php endforeach; ?>
					</select>
					<label>
						<input name="date" class="datepicker form-control date-input-client date" type="text" readonly
							   value="<?php if ($date) : echo date('Y-m-d', strtotime($date));
							   else : echo date('Y-m-d'); endif; ?>">
					</label>
					
					<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
					<button class="d-inline-block pull-right btn btn-danger reset">Reset</button>
				</form>
			</div>
		</header>
		<script>
			$(document).ready(function () {
				$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
			});
		</script>
		</section>
	
			
		<?php if(isset($route) && isset($parkings)) : ?>
			<script>
				var map; // Global declaration of the map
				var iw = new google.maps.InfoWindow(); // Global declaration of the infowindow
				var lat_longs = new Array();
				var markers = new Array();
				var markersParkings = new Array();
				var gpsData = <?php echo $route; ?>;
				var parkingsData = <?php echo $parkings; ?>;
				var planCoordinates = [];
				$.each(gpsData, function(key, val){
					planCoordinates.push({lat:parseFloat(val.lat), lng:parseFloat(val.lon)});
				});
				function initialize_map() {
					var directionsService = new google.maps.DirectionsService;
			        var directionsDisplay = new google.maps.DirectionsRenderer;

					$('#map_canvas').css('height', $(window).height());

					var myLatlng = new google.maps.LatLng(MAP_CENTER_LAT, MAP_CENTER_LON);
					var myOptions = {
				  		zoom: 11,
						center: myLatlng,
				  		mapTypeId: google.maps.MapTypeId.ROADMAP
				  	}
					map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

					$.each(parkingsData.data, function(key, val){
						marker = new google.maps.Marker({
				        	map: map,
				        	icon: '<?php echo mappin_svg('#6991FD', 'P', FALSE, '#fff'); ?>',
				            position: new google.maps.LatLng(val[3],val[4])
				        });
				        var content = `
				        	<div style="text-align: center;"><strong>Device: ` + val[0] + `</strong></div>
				        	Period: ` + val[6] + `min.<br>
				        	Start Time: ` + val[1] + `<br>
				        	End Time: ` + val[2] + `<br>
				        `;
				        marker.set("content", content);
					    google.maps.event.addListener(marker, "click", function(event) {
					    	iw.setContent(this.get("content"));
					    	iw.open(map, this);
						});
			    		markersParkings.push(marker);
					});

			        $.each(gpsData, function(key, val){
			    		var icon = getIcon(val.direction);
			    		if(!key && gpsData.length > 1) {
			    			icon = '<?php echo mappin_svg('#00b167', 'A', FALSE, '#000'); ?>';
			    			val.lat = parseFloat(val.lat);
			    			val.lon = parseFloat(val.lon) + 0.000003;
			    		}
			    		if(key + 1 == gpsData.length) {
			    			icon = '<?php echo mappin_svg('#FF0000', 'B', FALSE, '#000');?>';
			    			val.lat = parseFloat(val.lat) + 0.000003;
			    			val.lon = parseFloat(val.lon);
			    		}
			    		marker = new google.maps.Marker({
				        	map: map,
				        	icon: icon,
				            position: new google.maps.LatLng(val.lat,val.lon)
				        });
				        var content = `
				        	<div style="text-align: center;"><strong>Device: <?php echo $truck->item_name; ?></strong></div>
				        	Speed: ` + val.speed + `km/h<br>
				        	GPS Time: ` + val.gpsDateString + `<br>
				        `;
				        marker.set("content", content);
					    google.maps.event.addListener(marker, "click", function(event) {
					    	iw.setContent(this.get("content"));
					    	iw.open(map, this);
						});
			    		markers.push(marker);

			    		if(gpsData.length == 1) {
			    			icon = '<?php echo mappin_svg('#00b167', 'A', FALSE, '#000'); ?>';
			    			val.lat = parseFloat(val.lat);
			    			val.lon = parseFloat(val.lon) + 0.000003;

			    			marker = new google.maps.Marker({
					        	map: map,
					        	icon: icon,
					            position: new google.maps.LatLng(val.lat,val.lon)
					        });

			    			var content = `
					        	<div style="text-align: center;"><strong>Device: <?php echo $truck->item_name; ?></strong></div>
					        	Speed: ` + val.speed + `km/h<br>
					        	GPS Time: ` + val.gpsDateString + `<br>
					        `;
					        marker.set("content", content);
						    google.maps.event.addListener(marker, "click", function(event) {
						    	iw.setContent(this.get("content"));
						    	iw.open(map, this);
							});
			    			markers.push(marker);
			    		}
					});

			        var path = new google.maps.Polyline({
			          path: planCoordinates,
			          geodesic: true,
			          strokeColor: '#FF0000',
			          strokeOpacity: 1.0,
			          strokeWeight: 2
			        });

			        path.setMap(map);
				}

				function getIcon(direction) {
			        var baseImagePath = "/assets/img/tracker/blue_";
			        if (direction >= 337.5 || direction < 22.5) {
			            baseImagePath += "n.png";
			        } else if (direction >= 22.5 && direction < 67.5) {
			            baseImagePath += "ne.png";
			        } else if (direction >= 67.5 && direction < 112.5) {
			            baseImagePath += "e.png";
			        } else if (direction >= 112.5 && direction < 157.5) {
			            baseImagePath += "se.png";
			        } else if (direction >= 157.5 && direction < 202.5) {
			            baseImagePath += "s.png";
			        } else if (direction >= 202.5 && direction < 247.5) {
			            baseImagePath += "sw.png";
			        } else if (direction >= 247.5 && direction < 292.5) {
			            baseImagePath += "w.png";
			        } else if (direction >= 292.5 && direction < 337.5) {
			            baseImagePath += "nw.png";
			        }
			        return baseImagePath;
				}
				
				window.onload = initialize_map;
					
			</script>
			<div id="map_canvas" style="width:100%; height:100%;"></div>
			
			<?php else : ?>
				<section class="panel panel-default">
					<div class="m-bottom-10 m-top-10 p-sides-10 text-center">
						<strong>Please choose date and equipment</strong>
					</div>
				</section>
				
			<?php endif; ?>
			
	
	

	</section>
	<script>
		$(document).ready(function(){
			$('#dates').submit(function(){
				url = baseUrl + 'equipment/map/' + $('.date').val() + '/' + $('.truck').val();
				location.href = url;
				return false;
			});
			$('.reset').on('click', function(){
				location.href = baseUrl + 'equipment/map';
				return false;
			});
		});
	</script>

<?php $this->load->view('includes/footer'); ?>
