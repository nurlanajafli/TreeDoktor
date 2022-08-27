<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('user/active'); ?>">Users</a></li>
		<li class="active">Report</li>
	</ul>
	<section class="col-md-12 panel panel-default p-n">
		<header class="panel-heading">Support vs Field Payrol Report</header>

		<canvas id="line" height="500" width="900" style="height: 500px;"></canvas>
		
		<canvas id="line1" height="500" width="900" style="height: 500px;" class="m-t-sm"></canvas>
		
		<canvas id="line2" height="500" width="900" style="height: 500px;" class="m-t-sm"></canvas>

		<script>
			$('#line').attr('width', $('#line').parent().width() - 50);
			$('#line1').attr('width', $('#line').parent().width() - 50);
			$('#line2').attr('width', $('#line').parent().width() - 50);

			 var config = {
	            type: 'line',
	            data: {
					labels: [
						<?php foreach ($payrollsDates as $key => $value) {
							echo '"' . date('j M', strtotime($value->payroll_start_date)) . ' - ' . date('j M', strtotime($value->payroll_end_date)) . '", ';
						} ?>],
						/*"1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26"],*/

					datasets: [
						<?php foreach($report as $key => $val) : ?>
						{
							<?php
							$color = 201;
							$r = ($color + ($key + ($key % 3)) * 101) % 255;
							$g = ($color + ($key + ($key % 3)) * 82) % 255;
							$b = ($color + ($key + ($key % 3)) * 63) % 255;
							?>
							label: '<?php echo $key; ?> Support',
							fill: false,
							backgroundColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 0.7)",
							borderColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba(151,187,205,1)",
							data: [
								<?php foreach ($val as $payrollNum => $payrollData) {
									echo $payrollData['two_weekly_support'] . ",";
								}?>
							]
						},
						{
							
							label: '<?php echo $key; ?> Worker',
							year: <?php echo $key; ?>,
							/*closed: true,*/
							fill: false,
							backgroundColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 0.7)",
							borderColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba(151,187,205,1)",
							borderDash: [5, 5],
							data: [
								<?php foreach ($val as $payrollNum => $payrollData) {
									echo $payrollData['two_weekly_worker'] . ",";
								}?>
							]
						},
						<?php endforeach; ?>
						
					]
				},
	            options: {
	            	scaleLabel: 0,
	                responsive: true,
	                title:{
	                    display:true,
	                    text:'Worker Report'
	                },
	                tooltips: {
	                    mode: 'index',
	                    intersect: false,
	                    callbacks: {
		                    label: function(tooltipItems, data) {
								if(data.datasets[tooltipItems.datasetIndex].closed != undefined) 
									return data.datasets[tooltipItems.datasetIndex].year + ' - ' + Common.money(tooltipItems.yLabel);
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
			var ctx = document.getElementById("line").getContext("2d");
            window.myLine = new Chart(ctx, config);
			 
		</script>
	</section>
</section>
<?php $this->load->view('includes/footer'); ?>
