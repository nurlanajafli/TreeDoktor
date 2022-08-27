<?php $this->load->view('includes/header'); ?>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script async src="<?php echo base_url('assets/js/StyledMarker.js'); ?>"></script>
<script async src="<?php echo base_url('assets/js/label.js'); ?>"></script>

<section class="scrollable p-sides-15 p-n mapper" style="top: 9px;">
	<section class="panel panel-default p-n" style="margin: 0;">
		<header class="panel-heading">Current Jobs
			<div class="pull-right" style="margin-top:-14px;">
				<form id="dates" method="post" action="<?php echo base_url('schedule/current_jobs'); ?>"
					  class="input-append m-t-xs">
					
					<label>
						<select class="form-control date-input-client user" name="est_id">
							<option value="">Select Estimator</option>
							<?php foreach($estimators as $est) : ?>
								<option <?php if(isset($current_est) && $current_est == $est['id']) : ?>selected="selected"<?php endif; ?> value="<?php echo $est['id']; ?>"><?php echo $est['firstname'] . ' ' . $est['lastname']; ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="m-r-xs">
						<select class="form-control date-input-client employee" name="team_id">
							<option value="">Select Team</option>
							<?php foreach($teams as $team) : ?>
								<option <?php if(isset($current_team) && $current_team == $team['team_id']) : ?>selected="selected"<?php endif; ?> style="background-color:<?php echo $team['team_color']; ?>" value="<?php echo $team['team_id']; ?>">
									<?php echo $team['crew_name']; ?>
									<?php if($team['emp_name']) : ?>
										 - <?php echo $team['emp_name']; ?>
									<?php endif; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
					<input id="date_submit" type="submit" class="btn btn-info pull-right" value="GO!">
				</form>
			</div>
		</header>
	</section>
	<?php echo $map['html']; ?>

</section>


	</div>
	
	

<script>
	var checked = true;
	var trucks = <?php echo json_encode($items); ?>;
	var infowindow = false;
	var markersParkings = new Array();
	var vehMarkers = new Array();
	var iwP = [];
	var office = [
			{'lat' : 43.606330, lng : -79.524709},
			{'lat' : 43.604768, lng : -79.515833},
		];
	var parkingIcon = $(window).width() >= 1200 ? "/assets/img/tracker/parking.png" : "/assets/img/tracker/parking_11.png";
	var bounds  = new google.maps.LatLngBounds();
	$(document).ready(function () {
		
		$(document).on('click', '.submit', function () {
			//$('.submit').click(function(){
			var id = $(this).parents('.marker').attr('id');
			if($(this).text() == 'Close')
			{
				$('#'+ id).find('.task_status_change').val('new');
				$('#'+ id).find('.new_status_desc').css('display', 'none');
				$('#'+ id).find('.submit').css('display', 'none');
				$('#'+ id).find('form:first').css('padding-bottom', '0px');
			}
			else
			{
				status = $('#'+ id).find('.task_status_change').val();
				text = $('#'+ id).find('.new_status_desc').val();
				if(text == '')
				{
					alert('Description is required!');
					return false;
				}
				else
				{
					$.post(baseUrl + 'tasks/ajax_change_status', {id : id, status : status, text : text}, function (resp) {
						if(resp.status == 'ok')
							location.reload();
						else
							alert(resp.msg);
					}, 'json');
				}
			}

			return false;
		});
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_get_currentJobs_track',
			data: {trucks:trucks},
			global: false,
			success: function(resp){
				vehicles = resp;
				/*if(map !== undefined)
					displayParkings(resp.parkings);*/
				displayVehiclesPos(resp.positions);
				return false;
			},
			dataType: 'json'
		});
	});


	function displayVehiclesPos(positions){
		num = 0;

		$.each(trucks, function(teamId, teamTrucks){

			$.each(teamTrucks, function(key, truck){

                if (truck.eq_gps_id) {
                    var latLng = new google.maps.LatLng(positions[truck.eq_gps_id].lat, positions[truck.eq_gps_id].lng);
					/*if(vehMarkers[key] !== undefined) {
						vehMarkers[key].setMap(null);
						vehMarkers[key].label.setMap(null);
					}*/

					var icon = baseUrl + 'uploads/trackericon/cam_bleue.png';
                    if (truck.eq_name.indexOf("VHC 06") === 0 || truck.eq_name.indexOf("VHC 12") === 0 || truck.eq_name.indexOf("VHC 21") === 0)
						icon = baseUrl + 'assets/img/car.png';
                    if (truck.eq_name.indexOf("VHC 09") === 0 || truck.eq_name.indexOf("VHC 14") === 0 || truck.eq_name.indexOf("VHC 15") === 0 || truck.eq_name.indexOf("VHC 19") === 0 || truck.eq_name.indexOf("VHC 22") === 0 || truck.eq_name.indexOf("VHC 24") === 0 || truck.eq_name.indexOf("VHC 25") === 0 || truck.eq_name.indexOf("VHC 26") === 0 || truck.eq_name.indexOf("VHC 29") === 0)
							icon = baseUrl + 'assets/img/pick_up.png';
					
					vehMarker = new google.maps.Marker({
						position: latLng,
						map: map,
                        title: truck.eq_name + "<br>Last Update: " + positions[truck.eq_gps_id].date,
                        code: truck.eq_code,
						icon: icon
					});

					vehMarkers[num] = vehMarker;

					var label = new Label({
						map: map
					});
					label.bindTo('position', vehMarker, 'position');
					label.bindTo('text', vehMarker, 'code');

					vehMarkers[num].label = label;
					vehMarkers[num].label.setMap(map);

					google.maps.event.addListener(vehMarker, 'click', function() {
						if (infowindow) infowindow.close();
						infowindow = new google.maps.InfoWindow({
							content: '<div>' + this.title + '</div>',
						});
						infowindow.open(map, this);
					});
					google.maps.event.addListener(label, 'click', function() {
						if (infowindow) infowindow.close();
						infowindow = new google.maps.InfoWindow({
							content: '<div>' + this.title + '</div>',
						});
						infowindow.open(map, this);
					});
					num++;
				}				
			});

		});
		//$('#processing-modal').modal('hide');
	}


	function displayParkings(tracks)
	{
		
		$.each(tracks, function(k, v){
			
			$.each(v.data, function(key, val){
				
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
				markerCluster = new MarkerClusterer(map, markersParkings, {
					imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
					gridSize: 20,
				});
			});
		});
	}
</script>
<?php $this->load->view('includes/footer'); ?>
