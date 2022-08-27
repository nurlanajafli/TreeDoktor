<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('invoices'); ?>">Invoices</a></li>
		<li class="active">Late Payment Fees</li>
	</ul>
	<section class="col-md-12 panel panel-default p-n">
		<header class="panel-heading">Late Payment Fees</header>

		<canvas id="line" height="500" width="900" style="height: 500px;"></canvas>
		
		
		<?php $total = 0; ?>
		<script>
			$('#line').attr('width', $('#line').parent().width() - 50);
			
			 var config = {
	            type: 'line',
	            data: {
					labels: [		
							"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
						],
						/*"1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26"],*/

					datasets: [
						<?php foreach($report['yearly'] as $k => $v) : ?>
						{
							<?php
							$color = 201;
							$r = ($color + ($k + ($k % 3)) * 101) % 255;
							$g = ($color + ($k + ($k % 3)) * 82) % 255;
							$b = ($color + ($k + ($k % 3)) * 63) % 255;
							$total += $v;
							?>
							label: '<?php echo $k . ': ' . money($v); ?>',
							
							fill: false,
							backgroundColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 0.7)",
							borderColor: "rgba(<?php echo $r; ?>, <?php echo $g; ?>, <?php echo $b; ?>, 1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba(151,187,205,1)",
							data: [
								<?php foreach($report[$k] as $key => $val) { 
									echo $val . ",";
								}?>
							]
						},
						<?php if(!isset($report['yearly'][$k+1])) : ?>
							{
							label: 'Total : $ <?php echo $total; ?>',
							backgroundColor: "rgba(63, 255, 0, 0.7)",
							borderColor: "rgba(63, 255, 0, 1)",
							fill: false,
							
						},
						<?php endif; ?>
						<?php endforeach; ?>
						
					]
				},
	            options: {
	            	scaleLabel: 0,
	                responsive: true,
	                title:{
	                    display:true,
	                    text:'Late Payment Fees'
	                },
	                tooltips: {
	                    mode: 'index',
	                    intersect: false,
	                    callbacks: {
		                    label: function(tooltipItems, data) {
								if(data.datasets[tooltipItems.datasetIndex].closed != undefined)
									 return Common.money(tooltipItems.yLabel);
								return Common.money(0);
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
	<section class="col-md-12 panel panel-default p-n">
		<header class="panel-heading bg-light">
			<div class="pull-left">Invoices</div>
			<div class="p-left-5 pull-right">
				<div class="form-inline">
					<div class="p-10">
						<div id="dates" class="input-append m-t-xs">
							<label>From:&nbsp;&nbsp;&nbsp;
								<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
									   value="<?php if ($from) : echo $from;
									   else : echo date('01-01'); endif; ?>">
							</label>
							<label>To:&nbsp;&nbsp;&nbsp;
								<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
									   value="<?php if ($to) : echo $to;
									   else : echo date('01-31'); endif; ?>">
							</label>
							
							<span id="date_submit" type="submit" class="btn btn-info pull-right" style="width:114px;">GO!</span>
						</div>
					</div>
					<script>
						$(document).ready(function () {
							$('.datepicker').datepicker({format: 'mm-dd'});
						});
					</script>
				</div>
			</div>
			<div class="clear"></div>
			<div>
				<ul class="nav nav-tabs nav-justified">
					<?php foreach($report['yearly'] as $k => $year) : ?>
						<li class="<?php if($type == $k) : ?>active<?php endif; ?>"><a href="#<?php echo $k; ?>" data-toggle="tab"><?php echo $k; ?></a></li>
					<?php endforeach;?>
				</ul>
			</div>
		</header>
	<div class="panel-body" style="padding: 15px 0 0 0;">
		  <div class="tab-content">
			<?php foreach($report['yearly'] as $k => $year) : ?>
				<div class="tab-pane <?php if($type == $k) : ?>active<?php endif; ?>" id="<?php echo $k;?>">
					<div class="table-responsive">
						<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
							<thead>
								<tr>
									<th>Client Name</th>
									<th width="300px">Address</th>
									<th width="">Phone</th>
									<th width="">Est.</th>
									<th width="105px">No</th>
									<th width="120px">Total</th>
									<th width="120px">Due</th>
									<th width="100px"><a href="#">Date</a></th>
									<?php //if($type == 'overdue') : ?>
										<th>Overdue</th>
									<?php //endif;?>
									<th>QA</th>
									
									
									
									<th width="78px">Act</th>
								</tr>
							</thead>
							<tbody class="data-<?php echo $k; ?>">
								<?php $this->load->view('tab_invoice_overvue', ['invoices' => isset($report['invoices'][$k]) ? $report['invoices'][$k] : []]); ?>
							</tbody>
						</table>
					</div>
				</div>
			<?php endforeach;?>
		  </div>
	</div>
  </section>
  <script>
	$(document).ready(function () {
		$(document).on('click', '#date_submit', function () {
			//var year = $('.nav-justified').find('.active').text();
			var from = $('#dates [name="from"]').val();
			var to = $('#dates [name="to"]').val();
			var allYears = '<?php echo json_encode($report['yearly']); ?>';
			$.post(baseUrl + 'invoices/ajax_get_invoices', {from:from, to:to, all_years:allYears}, function (resp) {
				$.each(resp, function (key, val) {
					
					$('.'+key).html('');
					$('.'+key).html(val.html);
				});
			}, 'json');
			return false;
		});
		
	});
  </script>
</section>
<?php $this->load->view('includes/footer'); ?>
