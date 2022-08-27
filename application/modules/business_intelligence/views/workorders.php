<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('workorders'); ?>">Workorders</a></li>
	<li class="active">Workorders</li>
</ul>

<section class="col-sm-7">
	<section class="panel panel-default p-n">
		<header class="panel-heading">Corporate Productivity:&nbsp; Overall</header>
		<div class="table-responsive">
			<table class="table table-striped bg-whote count-table">
				<thead>
				<tr>
					<th>Workorder Status</th>
					<th>Qty:</th>
					<th>Revenue/Loss</th>
					<th>Due</th>
				</tr>
				</thead>
				<tbody>
				<?php $total_due = $total_sum = $total_count = $sum = $due = 0; ?>
				<?php foreach($total_workorders as $key=>$val): ?>
					<tr>
						<td><?php echo $val->status; ?>:</td>
						<td><?php echo $val->count; ?></td>
						<td><?php echo money($val->sum); ?></td>
						<td><?php echo money($val->due); ?></td>
					</tr>
					<?php if($val->wo_status) : ?>
					<?php $sum += $val->sum;?>
					<?php $due += $val->due;?>
					<?php endif; ?>
					<?php $total_sum += $val->sum;?>
					<?php $total_count += $val->count;?>
					<?php $total_due += $val->due;?>
				<?php endforeach; ?>
				
				<tr>
					<td></td>
					<td></td>
					<td><?php echo money($sum);?></td>
					<td><?php echo money($due);?></td>
				</tr>
				</tbody>
			</table>
		</div>
	</section>
</section>

<?php $bar_total = $total_sum - $sum; ?>

<section class="col-sm-5">
	<section class="panel panel-default p-n">
		<header class="panel-heading">&nbsp;</header>
		<table class="table table-striped bg-whote ">
			<thead>
			<tr>
				<th>&nbsp;</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<?php $bar_total = $bar_total ? $bar_total : 1;?>
			<?php foreach($total_workorders as $key=>$val): ?>
			<tr>
				<td>
					<?php $bar = round($val->sum * 100 / $bar_total, 2);?>
					<div class="progress progress-striped active m-n h-18">
						<div class="progress-bar progress-bar-info"
						     style="width: <?php echo $bar; ?>%"></div>
						&nbsp;-&nbsp;<?php echo $bar; ?>%
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<td>&nbsp;</td>
			</tr>
			</tbody>
		</table>
	</section>
</section>

</section>

	<script>
		var totalCount = '<?php echo $total_count; ?>';
		var totalSum = '<?php echo money($total_sum); ?>';
		var totalDue = '<?php echo money($total_due); ?>';
		$('#count_month').on('change', function(){
			var count = parseInt($(this).val());
			var date = new Date();
			var yyyy = date.getFullYear().toString();
			var currYear = date.getFullYear();
			var mm = (date.getMonth() + 1).toString();
			var mmFrom = (date.getMonth() + 1 - count).toString();
			var dd = date.getDate().toString();
			var mmChars = mm.split('');
			
			if (mmChars[1] && mm > 12) {
				while (mmChars[1] && mm > 12) {
					mm -= 12;
					mm = mm.toString();
					delete mmChars;
					var mmChars = mm.split('');
					yyyy = (parseInt(yyyy) + 1).toString();
				}
			}
			if(mmFrom.length == 1)
				mmFrom = '0' + mmFrom;
			if(count)
			{
				
				
				var ModMonth = date.getMonth() + 1 - count;
				if (ModMonth < 0)
				{ 
					ModMonth = 12 + ModMonth;
					currYear = yyyy - 1;
				}
				ModMonth = ModMonth.toString();
				if(ModMonth.length == 1)
					ModMonth = '0' + ModMonth;
				var ddChars = dd.split('');
				var to = yyyy + '-' + (mmChars[1] ? mm : "0" + mmChars[0]) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]);
				var from = currYear + '-' + (ModMonth) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]);

				
				//console.log(from, to, count); return false;
				
				$('#dates [name="to"]').val(to);
				$('#dates [name="from"]').val(from);
				$('#count_month').val(count);
				$('#dates').submit();
			}
			else
			{
				var ddChars = dd.split('');
				var frMm = date.getMonth();
				frMm = frMm.toString();
				if(frMm.length == 1)
					frMm = '0' + frMm;
				$('#dates [name="to"]').val(yyyy + '-' + (mmChars[1] ? mm : "0" + mmChars[0]) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]));
				$('#dates [name="from"]').val(yyyy + '-' + frMm + '-' + (ddChars[1] ? dd : "0" + ddChars[0]));
				$('#count_month').val(0);
				$('#dates').submit();
			}
			return false;
		});
		$(document).ready(function () {
			$('.count-table').find('tbody').prepend('<tr><td>Total:</td><td>' + totalCount + '</td><td>' + totalSum + '</td><td>' + totalDue + '</td></tr>')
		});
	</script>

<?php $this->load->view('includes/footer'); ?>
