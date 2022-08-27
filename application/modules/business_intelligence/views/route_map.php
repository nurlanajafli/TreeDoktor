<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15 p-n mapper" style="top: 9px;">
    <header class="panel-heading">
        <div style="display:inline-block; padding-top:10px"><label><strong>Tracking Map</label></strong></div>
		<div style="display:inline-block" class="pull-right" >                
				<select id="gps_user_select" class="form-control inline" style="width:200px">					
					<?php foreach($users as $k=>$v) : ?>
						<option value="<?php echo $v->id;?>" <?php if($v->id == $this->session->userdata('user_id')) {echo 'selected';} ?>>
							<?php echo $v->firstname . ' ' . $v->lastname;?>
						</option>
					<?php endforeach; ?>
				</select>
				<label>
					<input id="gps_date_select" class="datepicker form-control date text-center" type="text" readonly
						   value="<?php echo date(getDateFormat()); ?>">
				</label>
				
				<button id="show_pins" class="btn btn-info">GO!</button>
				
			
		</div>
		<div class="clear"></div>
        <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
	</header>
	<?php echo $map['html']; ?>	
</section>

<script>
    $(document).ready(function(){
        
        var current_user = '<?php echo $this->session->userdata('user_id'); ?>';
        var current_date = '<?php echo date('Y-m-d'); ?>';
        var utrack_pins = [];
        var connect_coords = [];
        var path;
        var infowindow;
        var last_response_user = current_user;
        var last_response_date = current_date;
        var default_lat = parseFloat("<?php echo config_item('map_lat');?>");
        var default_lng = parseFloat("<?php echo config_item('map_lon');?>");
        
        $('#map_canvas').css('height', '90%');
        
        $('#gps_date_select').datepicker({
			format: $('#php-variable').val()
		});
        
        function createTrackingMarker(val, index = false, num) {
			
			var iconPic = baseUrl + "assets/img/circle.png";
			if(index == 1)
				iconPic = baseUrl + "assets/img/pin-start.png";
			else if(index == 2)
				iconPic = baseUrl + "assets/img/pin-end.png";
            var image = {
                url: iconPic,                
                anchor: new google.maps.Point(16, 32)
            };
            
            var info_content = '<div><strong>' + $('#gps_user_select option:selected').text() + '</strong><br>Date: ' + val.ut_date + '</span></div>';
        
            var latlng = new google.maps.LatLng(val.ut_lat, val.ut_lng);
            var markerOptions = {
                map: map,
                position: latlng,
                icon: image
            };
            var tr_marker = new google.maps.Marker(markerOptions);
            
            google.maps.event.addListener(tr_marker, 'click', function() {
                if (infowindow) infowindow.close();
                infowindow = new google.maps.InfoWindow({
                    content: info_content,
                });
                infowindow.open(map, this);
            });
            
            utrack_pins.push(tr_marker);
            
            if(typeof connect_coords[num] == 'undefined')
				connect_coords[num] = [];
			connect_coords[num].push({lat:parseFloat(val.ut_lat), lng:parseFloat(val.ut_lng), ut_date:val.ut_date});
		
        }
        
        ws.send({method:'getUserTrackPins', params:{user:current_user, date:current_date}});
         
        function setUserPin(data) { //callback.getUserTrackPinsCallback = function(data) {
			var colors = ['#FF0000', '#0000FF', '#8A2BE2',  '#FF7F50', '#A52A2A', '#D2691E', '#6495ED', '#B8860B', '#00008B', '#8B008B', '#FF8C00', '#9400D3', '#1E90FF', '#228B22', '#B22222'];
            if(typeof data.coords != 'undefined'){                                     
                utrack_pins = [];
                connect_coords = [];
                
                var bound = new google.maps.LatLngBounds();
                $.each(data.coords, function(jkey, jval){
					$.each(jval, function(key, val){                
						bound.extend( new google.maps.LatLng(val['ut_lat'], val['ut_lng']) );
						var index = false;
						if(key == 0)
							index = 1;
						else if(jval.length - 1 == key)
							index = 2;
						createTrackingMarker(val, index, jkey);                           
					});
                });
                $.each(connect_coords, function(key, val){
					 
					path = new google.maps.Polyline({
						path: val,
						geodesic: true,
						strokeColor: colors[key],
						strokeOpacity: 1.0,
						strokeWeight: 2,
						icons: [{
							icon: {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
							offset: '100%',
							repeat: '60px'
						}]
					});
					path.setMap(map); 
				});
                
                if(data.coords.length)
					map.fitBounds(bound);
				else
					map.setCenter({lat: default_lat, lng: default_lng});
				
                var listener = google.maps.event.addListener(map, "idle", function() { 
				  map.setZoom(13); 
				  google.maps.event.removeListener(listener); 
				});
            }
            
        }
        
        $('button#show_pins').click(function(){
			 
			$.post(baseUrl + 'business_intelligence/ajax_get_coords', {user_id: $('#gps_user_select').val(), date:$('#gps_date_select').val()}, function (resp) {
				console.log(utrack_pins);
				if (resp.status == 'ok')
				{
					$.each(utrack_pins, function(key, val){
						utrack_pins[key].setMap(null);
					});
					if(utrack_pins.length)
						path.setMap(null); 
					var get_date = $('#gps_date_select').val();
					var do_date = moment(get_date, '<?php echo getMomentJSDateFormat(); ?>').format('YYYY-MM-DD');
					setUserPin(resp);
					//ws.send({method:'getUserTrackPins', params:{user:$('#gps_user_select').val(), date:do_date.toString()}});
					last_response_user = $('#gps_user_select').val(); //when button is clicked, considered we're trying to show this user's pins
					last_response_date = do_date;
					
				}
				else
				{
					$.each(utrack_pins, function(key, val){
						utrack_pins[key].setMap(null);
					});
					if(utrack_pins.length)
						path.setMap(null);
				}
				return false;
			}, 'json');
			
        });
        
        callback.newTrackingDataCallback = function(data) {
        
            if(typeof data.ut_user_id != 'undefined'){
                
                //last chosen user = callback's user? last chosen date = current date?                
                if(last_response_user == data.ut_user_id && last_response_date == moment().format('YYYY-MM-DD')){ 
                    createTrackingMarker(data);
                    path = new google.maps.Polyline({
                        path: connect_coords,
                        geodesic: true,
                        strokeColor: '#FF0000',
                        strokeOpacity: 1.0,
                        strokeWeight: 2,
                        icons: [{
                            icon: {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
                            offset: '100%',
                            repeat: '20px'
                        }]
                    });
                    path.setMap(map);
                }                        
            }
        }
        setTimeout(function () {
            $('button#show_pins').trigger('click');
        }, 0);

    });
</script>

<?php $this->load->view('includes/footer'); ?>
