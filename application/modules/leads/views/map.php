<?php $this->load->view('includes/header'); ?>
<script async src="<?php echo base_url('assets/js/StyledMarker.js'); ?>"></script>
<script async src="<?php echo base_url('assets/js/label.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/wsgps.js?v1.01'); ?>"></script>
<section class="scrollable p-sides-15 p-n mapper" style="top: 9px;">
	<?php echo $map['html']; ?>
	<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_owner() || is_cl_permission_all()) : ?>
		<div class="affix" style="top: 55px; right: 7%;">
			<ul class="nav navbar-nav navbar-right m-n nav-user">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle p-10 bg-white b-a pull-right" data-toggle="dropdown"
					   style="padding: 5px 10px;">
						<i class="fa fa-gears"></i>
					</a>
					<ul class="dropdown-menu on animated fadeInRight scrollable" id="note-list" style="right: -70px; height: 500px;">
						<span class="arrow top"></span>
						<li>
							<a>
								<label class="checkbox m-n">
									<input type="checkbox" class="showAll" value="0" checked> - Check/Uncheck All
								</label>
							</a>
						</li>
						<li>
							<a>
								<label class="checkbox m-n"><input type="checkbox" class="showTasks" value="0" checked> - <img height="20"
									src="<?php echo mappin_svg('#ffffff', 'T', FALSE, '#000'); ?>"> 
									<!-- <img src="http://maps.google.com/mapfiles/ms/icons/blue-dot.png">-->&nbsp;Tasks
								</label>
							</a>
						</li>
						<?php foreach ($users as $user) : ?>
							<?php if($user->system_user) continue; ?>
							<li>
								<a>
									<label class="checkbox m-n">
									<input type="checkbox" class="showPoint" value="<?php echo $user->id; ?>" checked> -
									 <img height="20"
									     src="<?php echo mappin_svg($user->color, '&#9899;', FALSE, '#000');?>">&nbsp;<?php echo $user->firstname . ' ' . $user->lastname; ?>
									    <!-- <img src="http://maps.google.com/mapfiles/ms/icons/green-dot.png">&nbsp;<?php echo $user->firstname . ' ' . $user->lastname; ?>-->
									</label>
								</a>
							</li>
						<?php endforeach; ?>
						<li>
							<a>
								<label class="checkbox m-n">
								<input type="checkbox" class="showPoint" value="Emergency" checked>
										<img  height="20" src="<?php echo mappin_svg('#FD7567', '&#9899;', FALSE, '#000');?>">&nbsp;Emergency
								</label>
							</a>
						</li>
						<li>
							<a>
								<label class="checkbox m-n">
									<input type="checkbox" class="showPoint" value="Priority" checked> - 
										<img  height="20" src="<?php echo mappin_svg('#FDF569', '&#9899;', FALSE, '#000');?>">&nbsp;Priority
								</label>
							</a>
						</li>
						<li>
							<a>
								<label class="checkbox m-n"><input type="checkbox" class="showPoint" value="0" checked> -  <img height="20"
									src="<?php echo mappin_svg('#00E64D', '&#9899;', FALSE, '#000');?>"> 
									<!--<img src="http://maps.google.com/mapfiles/ms/icons/green-dot.png">-->&nbsp;Not
								Assigned
								</label>
							</a>
						</li>
						
						<li>
							<a>
								<label class="checkbox m-n"><input type="checkbox" class="showVehicles" value="0" checked> - <img height="20"
									src="<?php echo base_url(); ?>uploads/trackericon/cam_bleue.png">&nbsp;Vehicles
								</label>
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<div class="affix bg-white b-a pull-right toggle-parent" style="top: 110px; right: 10px;">
			<label id="toggle_gps" class="switch" style="margin-bottom: 0 !important; padding-left: 5px; padding-right: 5px; font-size: 10px">
				GPS Tracking<br>
				<input style="margin: 0 auto !important" type="checkbox">
				<span></span>
			</label>			
		</div>
		<div class="affix bg-white b-a pull-right toggle-parent" style="top: 170px; right: 10px; display: none;">
			<label id="toggle_offline_gps" class="switch" style="margin-bottom: 0 !important; padding-left: 5px; padding-right: 5px; font-size: 10px">
				Show Offline<br>
				<input style="margin: 0 auto !important" type="checkbox">
				<span></span>
			</label>			
		</div>
	<?php endif; ?>
</section>

<?php $this->load->view('clients/client_sms_modal'); ?>

<script>
	var checked = true;
	var scheduledProcess = false;
	var sms = <?php echo json_encode($sms); ?>;
	var maxLeadId = <?php echo $maxLeadId; ?>;
	var trackerItems = <?php echo json_encode($vehicles); ?>;
	var infowindow = false;
	var vehMarkers = [];
	var vehLabels = [];
	var newMarker = [];

	$(document).ready(function(){

		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_get_traking_position',
			data: {trucks:trackerItems},
			global: false,
			success: function(resp){
				vehicles = resp;
				if(map !== undefined)
					displayVehicles();
				return false;
			},
			dataType: 'json'
		});
		
	});
	$(document).on("click", ".addLeadSms", function() {
		var obj = $(this);
		var href = $(this).attr('data-href');
		var phone = $(this).attr('data-phone');
		var email = $(this).attr('data-email');
		var name = $(this).attr('data-name');
		var company = $(this).attr('data-company');
		var cphone = $(this).attr('data-company-phone');
		var str = sms.sms_text;
		str = str.replace('[NAME]', name);
		str = str.replace('[EMAIL]', email);
		str = str.replace('[COMPANY_NAME]', company);
		str = str.replace('[COMPANY_PHONE]', cphone);
		$(href + ' .panel-heading').text('SMS to ' + name);
		$(href + ' .client_number').val(phone);
		$(href + ' .client_number').parent().parent().find('.control-label').text('Sms to ' + name);
		$(href + ' .sms_text').val(str);
		$(href).modal().show();
		return false;
	});

    $(document).on('change', '.lead_status_change', function () {
        var id = $(this).parents('.marker').attr('id');
        var val_id = $(this).val();
        if(val_id == 1) {
            $('#' + id).find('.lead_reason_change').css('display', 'none');
            $('#' + id).find('.lead_reason_change_block').css('display', 'none');
            $('#'+ id).find('.submitLead').css('display', 'none');
        }
        else if($('#' + id).find('.lead_reason_change-' + val_id).length < 1)
        {

            $('#' + id).find('.lead_reason_change').css('display', 'none');
            $('#' + id).find('.lead_reason_change_block').css('display', 'none');
            $('#'+ id).find('.submitLead').css('display', 'inline-block');
        }

        else
        {
            $('#' + id).find('.lead_reason_change_block').css('display', 'inline-block');
            $('#' + id).find('.lead_reason_change').css('display', 'inline-block');
            $('#'+ id).find('.submitLead').css('display', 'inline-block');

        }
        return false;
    });


	var noop = function(){};



	function check_any_updates()
	{
		var id = maxLeadId;

		/*var request = $.ajax({
	        url:baseUrl + 'leads/ajax_check_any_updates',
	        timeout: 1000,
	        data: {maxLeadId:id},
	        type:"POST",
	        dataType:"json",
	        success:function(resp){          
				if(resp.maxLeadId > id)
				{
					
					var maxLeadId = resp.maxLeadId;
					$.each(resp.marker, function(key, val){
						var latLng = new google.maps.LatLng(val.lat, val.lng);
						newMarker[key] = new google.maps.Marker({
							position: latLng,
							map: map,
							icon: val.icon
							
						});
						
						google.maps.event.addListener(newMarker[key], 'click', function() {
							if (infowindow) infowindow.close();
							infowindow = new google.maps.InfoWindow({
								content: val.infowindow_content,
							});
							infowindow.open(map, this);
						});
						
					})
				}
				setTimeout(check_any_updates, 60000);
				return false;
	        }
	    });*/


		/*request.onreadystatechange = noop;
    	request.abort = noop;
    	request = null;*/

		/*
		$.ajax({
			type: 'POST',
			url: baseUrl + 'leads/ajax_check_any_updates',
			data: {max_id:maxLeadId},
			global: false,
			success: function(resp){
				
			},
			dataType: 'json'
		});*/
		return false;
	}

	$(document).ready(function () {
		setTimeout(check_any_updates, 60000);

        setTimeout(function () {
            var zoom = map.getZoom();
            map.setZoom(zoom < 3 ? 3 : zoom);
            var center = new google.maps.LatLng(MAP_CENTER_LAT, MAP_CENTER_LON);
            map.setCenter(center);
        }, 1000);



        $(document).on('click', '.submitLead', function () {
            //$('.submit').click(function(){
            var id = $(this).parents('.marker').attr('id');
            if($(this).text() == 'Close')
            {
                $('#'+ id).find('.lead_status_change').val(1);
                $('#'+ id).find('.lead_reason_change').css('display', 'none');
                $('#'+ id).find('.submit').css('display', 'none');
            }
            else
            {
                var status = $('#'+ id).find('.lead_status_change').val();
                var reason = $('#'+ id).find('.lead_reason_change').val();

                $.post(baseUrl + 'leads/update_lead_status', {lead_id : id, lead_status : status, lead_reason_status : reason}, function (resp) {

                    if(resp.status == 'success')
                    {

                        var visible = false;
                        if (status == 1)
                            visible = true;
                        $.each(markers, function (key, val) {
                            var lead = $(val.content).attr('id');
                            if (lead == id)
                            {
                                iw.close();
                                markers[key].setVisible(visible);
                                return false;
                            }
                        });
                    }
                    else
                        alert(resp.msg);
                }, 'json');
                //}
            }

            return false;
        });


		$('.addCall').on('click', function(){
			var obj = $(this);
			var sound = $(obj).data('voice');
			var number = $(obj).data('number');
			$.post(baseUrl + 'client_calls/send_voice_msg', {PhoneNumber:number, voice:sound}, function (resp) {
				
				return false;
			}, 'json');
			return false;
		});
        $(document).on('click', '.assign_lead_btn',  function(){
			var assigned_to = $(this).parents('.assign_lead').find('[name="assigned_to"]').val();
			var assigned_what = $(this).parents('.assign_lead').find('[name="assigned_what"]').val();
			$.post(baseUrl + 'leads/assign_lead', {assigned_to:assigned_to, assigned_what:assigned_what}, function (resp) {
                if(resp.status == 'ok')
                    location.href = resp.url;
				return false;
			}, 'json');
			return false;
		});
		$(document).on('change', '.showTasks', function () {
			var visible = false;
			if ($(this).is(':checked'))
				visible = true;
			$.each(markers, function (key, val) {
				var task = $(val.content).data('task');
				if (task)
					markers[key].setVisible(visible);
			});
		});
		$(document).on('change', '.showVehicles', function () {
			var checked = $(this).is(':checked');
			$.each(vehMarkers, function (key, val) {
				vehMarkers[key].setVisible(checked);
				var mapInstance = checked ? map : null;
				vehLabels[key].setMap(mapInstance);
			});
		});
		$(document).on('change', '.showPoint', function () {
			var crewId = $(this).val();
			var visible = false;
			if ($(this).is(':checked'))
				visible = true;
			$.each(markers, function (key, val) {
				var currCrew = $(val.content).data('user');
				if (currCrew == crewId)
					markers[key].setVisible(visible);
			});
		});
		$(document).on('change', '.showAll', function () {
			var obj = $(this);
			var checked = $(this).is(':checked');
			$.each($('.showPoint'), function (key, val) {
				$(val).prop('checked', checked);
			});
			$('.showVehicles').prop('checked', checked);
			$('.showTasks').prop('checked', checked);
			$.each(markers, function (key, val) {
				markers[key].setVisible(checked);
			});
			$.each(vehMarkers, function (key, val) {
				vehMarkers[key].setVisible(checked);
				var mapInstance = checked ? map : null;
				vehLabels[key].setMap(mapInstance);
			});
		});
		$(document).on('change', '.scheduledLead', function(){
			if(scheduledProcess)
				return false;
			var lead_id = $(this).data('lead_id');
			scheduledProcess = true;
			result = 1;
			if($(this).is(':checked'))
				$(this).next().addClass('checked');
			else
			{
				$(this).next().removeClass('checked');
				result = 0;
			}
			$.post(baseUrl + 'leads/ajax_scheduled_lead',{scheduled:result,lead_id:lead_id},function(resp){
				if(resp.status != 'ok')
					alert('Ooops! Error.');
				else
				{
					location.reload();
				}
			},'json');
		});

		$(document).on('change', '.callLead', function(){
			if(scheduledProcess)
				return false;
			var lead_id = $(this).data('lead_id');
			scheduledProcess = true;

			var $this = $(this);
			result = 1;
			if($(this).is(':checked'))
				$(this).next().addClass('checked');
			else
			{
				$(this).next().removeClass('checked');
				result = 0;
			}
			$.post(baseUrl + 'leads/ajax_call_lead',{call:result,lead_id:lead_id},function(resp){
				if(resp.status != 'ok')
					alert('Ooops! Error.');
				else
				{
				    /*
				    console.log(markers);
				    console.log(result);
				    console.log(lead_id);
				    console.log(resp);
                    */
                    location.reload();
				}
			},'json');
		});

		/*$(document).on('change', '.showPoint', function () {
			var crewId = $(this).val();
			var visible = false;
			if ($(this).is(':checked'))
				visible = true;
			$.each(markers, function (key, val) {
				var currCrew = $(val.content).data('user');
				if (currCrew == crewId)
					markers[key].setVisible(visible);
			});
		});
		$(document).on('change', '.showAll', function () {
			$.each($('.showPoint'), function (key, val) {
				if (checked)
					$(val).prop('checked', false);
				else
					$(val).prop('checked', true);
				$(val).change();
			});
			checked = checked ? false : true;
		});*/
		$(document).on('change', '.task_status_change', function () {
			//$('.task_status_change').change(function(){
			var id = $(this).parents('.marker').attr('id');
			if($(this).val() == 'new')
			{

				$('#'+ id).find('.new_status_desc').css('display', 'none');
				$('#'+ id).find('.submit').css('display', 'none');
				$('#'+ id).find('form:first').css('padding-bottom', '0px');

			}
			else
			{
				$('#'+ id).find('.new_status_desc').css('display', 'block');
				$('#'+ id).find('.submit').css('display', 'inline-block');
				$('#'+ id).find('form:first').css('padding-bottom', '40px');
			}
			return false;
		});
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
				/*if(text == '')
				{
					alert('Description is required!');
					return false;
				}
				else
				{*/
					$.post(baseUrl + 'tasks/ajax_change_status', {id : id, status : status, text : text}, function (resp) {
						if(resp.status == 'ok') {
                            var visible = false;
                            if (status == 'new')
                                visible = true;
                            $.each(markers, function (key, val) {
                                var task = $(val.content).attr('id');
                                if (task == id)
                                {
                                    iw.close();
                                    markers[key].setVisible(visible);
                                    return false;
                                }
                            });
						}
						else
							alert(resp.msg);
					}, 'json');
				//}
			}

			return false;
		});
		
		
	});
	
</script>
<?php $this->load->view('includes/footer'); ?>
