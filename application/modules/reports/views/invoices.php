<?php $this->load->view('includes/header'); ?>
 <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=visualization"></script>
 <style>
      html, body, #map-canvas {
        height: 100%;
        margin: 0px;
        padding: 0px
      }
      #panel {
        position: absolute;
        top: 0px;
        right: 0;
		padding-top: 2px;
      }
    </style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
		<li class="active">Invoices</li>
	</ul>
	<section class="col-sm-7 panel panel-default p-n">
		<header class="panel-heading">Corporate Productivity:&nbsp; Overal</header>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Invoice Status</th>
				<th>Qty:</th>
				<th>Revenue/Loss</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>Total:</td>
				<td><?php echo $total_invoices; ?></td>
				<td><?php echo money($revenue_total_invoices); ?></td>
			</tr>
			<tr>
				<td>Issued Invoices:</td>
				<td><?php echo $issued_invoices; ?></td>
				<td><?php echo money($revenue_issued_invoices); ?></td>
			</tr>
			<tr>
				<td>Sent Invoices:</td>
				<td><?php echo $sent_invoices; ?></td>
				<td><?php echo money($revenue_sent_invoices); ?></td>
			</tr>
			<tr>
				<td>Paid Invoices:</td>
				<td><?php echo $paid_invoices; ?></td>
				<td><?php echo money($revenue_paid_invoices); ?></td>
			</tr>
			<tr>
				<td>Overdue Invoices:</td>
				<td><?php echo $overdue_invoices; ?></td>
				<td><?php echo money($revenue_overdue_invoices); ?></td>
			</tr>
			<tr>
				<td>Unpaid Invoices:</td>
				<td><?php echo($total_invoices - $paid_invoices); ?></td>
				<td><?php echo money($revenue_total_invoices - $revenue_paid_invoices); ?></td>
			</tr>
			</tbody>
		</table>
	</section>

	<section class="panel panel-default text-center pull-right p-n" style="width:40%">
		<header class="panel-heading" style="height: 255px;">
			<canvas id="pie" height="200" width="200"></canvas>
		</header>
		<script>
			var pieData = [
				{
					value: <?= $revenue_issued_invoices ?>,
					color: "#F38630"
				},
				{
					value: <?= $revenue_sent_invoices ?>,
					color: "#E0E4CC"
				},
				{
					value: <?= $revenue_paid_invoices ?>,
					color: "#69D2E7"
				}

			];

			var myPie = new Chart(document.getElementById("pie").getContext("2d")).Pie(pieData);

		</script>

	</section>
	<!-- /grid_7 Pie Chart -->


	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Monthly Revenue:</header>
		<canvas id="line" height="500" width="900" style="height: 500px;margin-left: 25px;"></canvas>

		<script>
			$('#line').attr('width', $('#line').parent().width() - 50);
			var lineChartData = {
				labels: ["May", "June", "July", "August", "September", "October", "November", "December", "January", "February", "March", "April"],
				
				datasets: [
					<?php foreach($may_revenue_invoices as $key => $val) : ?>
					{
						label: "My dataset",
						<?php if(!$key) : ?>
						fillColor: "rgba(151,187,205,0.2)",
						strokeColor: "rgba(151,187,205,1)",
						pointColor: "rgba(151,187,205,1)",
						<?php elseif($key == 1) : ?>
						fillColor: "rgba(220,220,220,0.2)",
						strokeColor: "rgba(220,220,220,1)",
						pointColor: "rgba(220,220,220,1)",
						<?php else : ?>
						fillColor: "rgba(212, 13, 255, 0.2)",
						strokeColor: "rgba(212, 13, 255, 1)",
						pointColor: "rgba(212, 13, 255, 1)",
						<?php endif; ?>
						pointStrokeColor: "#fff",
						pointHighlightFill: "#fff",
						pointHighlightStroke: "rgba(151,187,205,1)",
						data: [<?= isset($may_revenue_invoices[$key]) ? $may_revenue_invoices[$key] : 0; ?>,
							<?= isset($june_revenue_invoices[$key]) ? $june_revenue_invoices[$key] : 0; ?>,
							<?= isset($july_revenue_invoices[$key]) ? $july_revenue_invoices[$key] : 0; ?>,
							<?= isset($august_revenue_invoices[$key]) ? $august_revenue_invoices[$key] : 0; ?>,
							<?= isset($september_revenue_invoices[$key]) ? $september_revenue_invoices[$key] : 0; ?>,
							<?= isset($october_revenue_invoices[$key]) ? $october_revenue_invoices[$key] : 0; ?>,
							<?= isset($november_revenue_invoices[$key]) ? $november_revenue_invoices[$key] : 0; ?>,
							<?= isset($december_revenue_invoices[$key]) ? $december_revenue_invoices[$key] : 0; ?>,
							<?= isset($january_revenue_invoices[$key]) ? $january_revenue_invoices[$key] : 0; ?>,
							<?= isset($february_revenue_invoices[$key]) ? $february_revenue_invoices[$key] : 0; ?>,
							<?= isset($march_revenue_invoices[$key]) ? $march_revenue_invoices[$key] : 0; ?>,
							<?= isset($april_revenue_invoices[$key]) ? $april_revenue_invoices[$key] : 0; ?>]
					},
					<?php endforeach; ?>
				]

			}

			var myLine = new Chart(document.getElementById("line").getContext("2d")).Line(lineChartData);
		</script>

	</section>
	<section class="col-sm-12 panel panel-default p-n heatmap-block" style="height:41px;padding-bottom: 39px!important;">
		<header class="panel-heading">Heatmap:</header>
		<div id="panel">
		  <button class="btn btn-info" id="showHideHeatmap" onclick="showHideHeatmap()">Show Heatmap</button>
		  <button class="btn btn-info" disabled="disabled" onclick="toggleHeatmap()">Toggle Heatmap</button>
		  <button class="btn btn-info" disabled="disabled" onclick="changeGradient()">Change gradient</button>
		  <button class="btn btn-info" disabled="disabled" onclick="changeRadius()">Change radius</button>
		  <button class="btn btn-info" disabled="disabled" onclick="changeOpacity()">Change opacity</button>
		</div>
		<div id="map-canvas"></div>
		<script>
		var map, pointarray, heatmap;
		var taxiData = [] ;
		$(document).ready(function(){
			var invoices = <?php echo json_encode($invoices); ?>;
			
			$.each(invoices, function (key, val) {
				taxiData.push(new google.maps.LatLng(val.latitude, val.longitude));
			});
		});	
			function initialize() {
				var mapOptions = {
					zoom: 10,
					center: new google.maps.LatLng(MAP_CENTER_LAT, MAP_CENTER_LON),
					mapTypeId: google.maps.MapTypeId.SATELLITE
				};

				map = new google.maps.Map(document.getElementById('map-canvas'),
				mapOptions);

				var pointArray = new google.maps.MVCArray(taxiData);

				heatmap = new google.maps.visualization.HeatmapLayer({
					data: pointArray
				});

				heatmap.setMap(map);
			}

			function toggleHeatmap() {
				heatmap.setMap(heatmap.getMap() ? null : map);
			}

			function changeGradient() {
				var gradient = [
					'rgba(0, 255, 255, 0)',
					'rgba(0, 255, 255, 1)',
					'rgba(0, 191, 255, 1)',
					'rgba(0, 127, 255, 1)',
					'rgba(0, 63, 255, 1)',
					'rgba(0, 0, 255, 1)',
					'rgba(0, 0, 223, 1)',
					'rgba(0, 0, 191, 1)',
					'rgba(0, 0, 159, 1)',
					'rgba(0, 0, 127, 1)',
					'rgba(63, 0, 91, 1)',
					'rgba(127, 0, 63, 1)',
					'rgba(191, 0, 31, 1)',
					'rgba(255, 0, 0, 1)'
				]
					heatmap.set('gradient', heatmap.get('gradient') ? null : gradient);
				}

			function changeRadius() {
				heatmap.set('radius', heatmap.get('radius') ? null : 20);
			}

			function changeOpacity() {
				heatmap.set('opacity', heatmap.get('opacity') ? null : 0.2);
			}
			function showHideHeatmap()
			{
				obj = $('.heatmap-block');
				if(obj.css('height') == '41px')
				{
					obj.css('height', '100%');
					initialize();
					$('#panel button').removeAttr('disabled');
					$('#showHideHeatmap').text('Hide Heatmap');
				}
				else
				{
					obj.css('height', '41px');
					$('#showHideHeatmap').text('Show Heatmap');
					$('#panel button').attr('disabled', 'disabled');
					$('#showHideHeatmap').removeAttr('disabled');
				}	
				return false;
				
			}

			//google.maps.event.addDomListener(window, 'load', initialize);

		</script>
	</section>
	
</section>



<?php $this->load->view('includes/footer'); ?>



