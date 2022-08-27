<?php $this->load->view('includes/header'); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<?php $sum_total_due_invoices = $sum_total_invoices = 0; ?>
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
     table.invoice-table td{
         white-space: nowrap;
     }
    </style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
		<li class="active">Invoices</li>
	</ul>
	<section class="col-sm-7 col-xs-12 col-md-8 panel panel-default p-n">
		<header class="panel-heading">
            Invoicess
            <div class="pull-right reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; margin-top: -6px; margin-right: -11px;" data-url="<?php echo base_url('/business_intelligence/invoices_report/');?>"
                 data-dates="<?php echo isset($dates) ? htmlspecialchars(json_encode($dates)) : ''; ?>">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <i class="fa fa-caret-down"></i>
            </div>
        </header>
		<div class="table-responsive">
			<table class="table table-striped invoice-table">
				<thead>
				<tr>
					<th>Invoice Status</th>
					<th>Qty</th>
					<th>Total Services</th>
					<th>Discounts</th>
					<th>Tax</th>
                    <th>Total</th>
                    <th>Already Paid</th>
					<th>Total Due</th> 
				</tr>
				</thead>
				<tbody>
					<script> var pieLabels = []; </script>
					<script> var pieLoss = []; </script>
					<?php $revenue_total_invoices = $total_invoices = $total_due_invoices = $sum_payments = $sum_hst = $sum_discount = $sum_service = $revenue_total_due_invoices = 0; ?>
					<?php if(isset($invoice_by_statuses) && !empty($invoice_by_statuses)) : ?>
						<?php foreach($invoice_by_statuses as $k=>$v) : ?>
							<tr>
								<td><?php echo $v->invoice_status_name; ?> Invoices:</td>
								<td><?php echo $v->count; ?></td>
                                <td><?php echo money($v->sum_service); ?></td>
                                <td><?php echo money($v->sum_discount); ?></td>
                                <td><?php echo money($v->sum_hst); ?></td>
                                <td><?php echo money($v->sum_total); ?></td>
                                <td><?php echo money($v->sum_payments); ?></td>
                                <td><?php echo money($v->sum_due); ?></td>
								<?php $total_invoices += $v->count;?>
								<?php $sum_service += $v->sum_service;?>
								<?php $sum_discount += $v->sum_discount;?>
								<?php $sum_hst += $v->sum_hst;?>
								<?php $sum_total_invoices += $v->sum_total;?>
								<?php $sum_payments += $v->sum_payments;?>
								<?php if((int)$v->completed != 1) : ?>
									<?php $total_due_invoices += $v->count;?>
									<script>pieLabels.push("<?php echo $v->invoice_status_name; ?>");</script>
									<script>pieLoss.push("<?php echo $v->sum_due; ?>");</script>
									<?php $sum_total_due_invoices += $v->sum_due;?>
								<?php endif;?>
							</tr>
						<?php endforeach;?>
					<?php endif;?>
					<tr>
						<td>Total:</td>
						<td><?php echo $total_invoices; ?></td>
                        <td><?php echo money($sum_service); ?></td>
						<td><?php echo money($sum_discount); ?></td>
						<td><?php echo money($sum_hst); ?></td>
						<td><?php echo money($sum_total_invoices); ?></td>
						<td><?php echo money($sum_payments); ?></td>
						<td>&nbsp;</td>
					</tr>
					<tr class="font-bold">
						<td>Total Due:</td>
						<td><?php echo $total_due_invoices; ?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
                        <td><?php echo money($sum_total_due_invoices); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</section>

	<section class="col-sm-5 col-xs-12 col-md-4 panel panel-default text-center pull-right p-n"  >
		<header class="panel-heading" style="height: 255px;">
			<canvas id="pie" height="130" width="355"></canvas>
		</header>
		<script>
            var fromDate = "<?php echo isset($from_date) ? $from_date : '' ?>";
            var toDate = "<?php echo isset($to_date) ? $to_date : '' ?>";

            Common.initDateRangePicker('.reportrange', fromDate, toDate);


			$('#pie').attr('width', $('#pie').parent().width() - 50);
			
			var config = {
		        type: 'pie',
		        options: {
		        	responsive: true,
    				maintainAspectRatio: true
		        },
		        data: {
		        	labels: pieLabels,
		            datasets: [{
		            	data: pieLoss,
		            	backgroundColor: ["#F38630", "#E0E4CC", "#69D2E7"]}
						
				]},

			};

			var ctx1 = document.getElementById("pie").getContext("2d");
            window.myPie = new Chart(ctx1, config);

			//var myPie = new Chart(document.getElementById("pie").getContext("2d")).Pie(pieData);

		</script>

	</section>
	<!-- /grid_7 Pie Chart -->


	<section class="col-sm-12 col-xs-12 col-md-12 panel panel-default p-n">
		<header class="panel-heading">Statistics:</header>
		<div class="table-responsive">
			<canvas id="line" height="500" width="900" style="height: 500px;"></canvas>
			<canvas id="line2" height="500" width="900" style="height: 500px;"></canvas>
		</div>
		<script>
			$('#line').attr('width', $('#line').parent().width() - 50);
			$('#line2').attr('width', $('#line').parent().width() - 50);

			var config = {
	            type: 'line',
	            data: {
					labels: [
						<?php foreach ($payrollsDates as $key => $value) {
							echo '"' . date('j M', strtotime($value->payroll_start_date)) . ' - ' . date('j M', strtotime($value->payroll_end_date)) . '", ';
						} ?>],
					datasets: [
						<?php foreach($report as $key => $val) : ?>
						{
							<?php
							$color = 222;
							$r = ($color + ($key + 5) * 25) % 255;
							$g = ($color + ($key + 4) * 452) % 255;
							$b = ($color + ($key + 3) * 53) % 255;
							?>
							label: <?php echo $val[0]['invoices_year']; ?>,
							fill: false,
							backgroundColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							borderColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							pointColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba(151,187,205,1)",
							data: [
								<?php foreach ($val as $payrollNum => $payrollData) {
									echo $payrollData['revenue_invoices'] . ",";
								}?>
							]
						},
						<?php endforeach; ?>
					]
				},
	            options: {
	                responsive: true,
	                title:{
	                    display:true,
	                    text:'Created Invoices'
	                },
	                tooltips: {
	                    mode: 'index',
	                    intersect: false,
	                    callbacks: {
		                    label: function(tooltipItems, data) { 
		                        return data.datasets[tooltipItems.datasetIndex].label+' - '+Common.money(tooltipItems.yLabel);
		                    }
		                }
	                },
	                hover: {
	                    mode: 'nearest',
	                    intersect: true
	                },
	                scales: {
				        yAxes: [{
				          ticks: {
				            beginAtZero: true,
				            callback: function(value, index, values) {
				            	return Common.money(value);
				            }
				          }
				        }]
				    }
	            }
			};
			var ctx = document.getElementById("line").getContext("2d");
            window.myLine = new Chart(ctx, config);


			var config = {
	            type: 'line',
	            data: {
					labels: [
						<?php foreach ($payrollsDates as $key => $value) {
							echo '"' . date('j M', strtotime($value->payroll_start_date)) . ' - ' . date('j M', strtotime($value->payroll_end_date)) . '", ';
						} ?>],
					datasets: [
						<?php foreach($report as $key => $val) : ?>
						{
							<?php
							$color = 222;
							$r = ($color + ($key + 5) * 25) % 255;
							$g = ($color + ($key + 4) * 452) % 255;
							$b = ($color + ($key + 3) * 53) % 255;
							?>
							label: <?php echo $val[0]['invoices_year']; ?>,
							fill: false,
							backgroundColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							borderColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							pointColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba(151,187,205,1)",
							data: [
								<?php foreach ($val as $payrollNum => $payrollData) {
									echo $payrollData['payments'] . ",";
								}?>
							]
						},
						<?php endforeach; ?>
					]
				},
	            options: {
	                responsive: true,
	                title:{
	                    display:true,
	                    text:'Received Payments'
	                },
	                tooltips: {
	                    mode: 'index',
	                    intersect: false,
	                    callbacks: {
		                    label: function(tooltipItems, data) { 
		                        return data.datasets[tooltipItems.datasetIndex].label + ' - ' + Common.money(tooltipItems.yLabel);
		                    }
		                }
	                },
	                hover: {
	                    mode: 'nearest',
	                    intersect: true
	                },
	                scales: {
				        yAxes: [{
				          ticks: {
				            beginAtZero: true,
				            callback: function(value, index, values) {
				              return Common.money(value);
				            }
				          }
				        }]
				    }
	            }
			};
			var ctx = document.getElementById("line2").getContext("2d");
            window.myLine = new Chart(ctx, config);
		</script>

	</section>
	<?php /*
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
					center: new google.maps.LatLng(MAP_CENTER),
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
	</section>*/?>
	
</section>
<?php $this->load->view('includes/footer'); ?>
