<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('employees'); ?>">Employees</a></li>
		<li class="active">Client Communications</li>
	</ul>
	<!--Top Menu -->
	<section class="panel panel-default p-n">
		<div class="p-left-5">
			<div class="form-inline">
				<div class="p-10">
					<form name="dates" method="post" action="<?php echo base_url('clients/client_communications'); ?>"
					      class="input-append m-t-xs">
						<label>From:&nbsp;&nbsp;&nbsp;
							<input name="from" class="datepicker form-control date-input-client" type="text" readonly
							       value="<?php if ($from) : echo $from;
							       else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?>">
						</label>
						<label>To:&nbsp;&nbsp;&nbsp;
							<input name="to" class="datepicker form-control date-input-client" type="text" readonly
							       value="<?php if ($to) : echo $to;
							       else : echo date('Y-m-d'); endif; ?>">
						</label>
						<button type="submit" class="btn btn-info date-input-client" style="width:114px;">GO!</button>
					</form>
				</div>
				<script>
					$(document).ready(function () {
						$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
					});
				</script>
			</div>
		</div>
	</section>


	<?php $this->load->view('employees/calls_statistic'); ?>

	<?php $this->load->view('includes/footer'); ?>
