<?php $this->load->view('includes/header'); ?>
<script async src="<?php echo base_url('/assets/js/StyledMarker.js'); ?>"></script>
<script async src="<?php echo base_url('/assets/js/label.js'); ?>"></script>

<section class="scrollable p-sides-15 mapper" style="top: 9px;">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">General</li>
	</ul>
	<section class="panel panel-default p-n">
	<header class="panel-heading">Filter
		<div class="pull-right" >
			<form id="dates" method="post" action="<?php echo base_url('equipment/map'); ?>" class="input-append m-t-xs inline" >
				<select name="truck" class="form-control truck inline" style="width: 106px;">
					<option value="">Equipment</option>
					<?php foreach($items as $k=>$v) : ?>
						<option <?php if(isset($truck) && $truck->item_code == $v->eq_code) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->eq_code;?>">
							<?php echo $v->eq_code;?>
						</option>
					<?php endforeach; ?>
				</select>
				<label>
					<input name="date" class="datepicker form-control date-input-client date" type="text" readonly
						   value="<?php if (isset($date)) : echo date('Y-m-d', strtotime($date));
						   else : echo date('Y-m-d'); endif; ?>">
				</label>
				
				<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
				<a class="d-inline-block pull-right btn btn-danger reset">Reset</a>
			</form>
		</div>
		<div class="clear"></div>
	</header>
	<script>
		$(document).ready(function () {
			$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
		});
	</script>
	</section>
	
	<?php echo $map['html']; ?>
	
</section>
<script>
	map = new google.maps.Map(document.getElementById("map_canvas"));
	var trackerItems = <?php echo json_encode($tracks); ?>;
	var infowindow = false;
	var vehMarkers = [];
	$(document).ready(function(){
		$('#processing-modal').modal();
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_get_traking_position',
			data: {trucks:trackerItems},
			global: false,
			success: function(resp){
				vehicles = resp;
				displayVehicles();
				return false;
			},
			dataType: 'json'
		});
		google.maps.event.addDomListener(window, 'load', function(){
		});
		
		$('#dates').submit(function(){
			url = baseUrl + 'equipment/map/' + $('.date').val() + '/' + $('.truck').val();
			 
			location.href = url;
			return false;
		});
		$('.reset').on('click', function(){
			location.href = baseUrl + 'equipment/map';
		});
		
	});

	setInterval(function() {
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_get_traking_position',
			data: {trucks:trackerItems},
			global: false,
			success: function(resp){
                $.each(vehMarkers, function(key, val){
                    val.setMap(null);
                    vehLabels[key].setMap(null);
                });
				vehicles = resp;
				displayVehicles();
				return false;
			},
			dataType: 'json'
		});
	}, 30000);

</script>
<?php $this->load->view('includes/footer'); ?>
