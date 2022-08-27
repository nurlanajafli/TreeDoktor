<nav class="nav-primary animated fadeInRight">
    <ul class="nav">
	<li>
		<a title="Edit Status" href="#changeWorkorderStatus" role="button" 
			data-toggle="modal"><i class="fa fa-pencil"><b class="bg-success"></b></i>&nbsp;&nbsp;Update Status&nbsp;&nbsp;</a>
	</li>
	
		<?php if(!isset($schedule_event)) : ?>
			<li>
				<?php echo anchor($workorder_data->workorder_no . '/pdf', '<i class="fa fa-file"><b class="bg-dark"></b></i>&nbsp;&nbsp;Download PDF', '  type="button"'); ?>
			</li>
			<li>
				<?php echo anchor('workorders/partial_invoice_pdf/' . $workorder_data->id, '<i class="fa fa-file"><b class="bg-dark"></b></i>&nbsp;&nbsp;Invoice PDF', 'type="button"'); ?>
			</li>
		<?php else : ?>
			<li>
				<?php echo anchor($workorder_data->workorder_no . '/pdf/'. $schedule_event['id'], '<i class="fa fa-file"><b class="bg-dark"></b></i>&nbsp;&nbsp;Download PDF', ' type="button"'); ?>
			</li>
			<li>
				<?php echo anchor('workorders/partial_invoice_pdf/' . $workorder_data->id, '<i class="fa fa-file"><b class="bg-dark"></b></i>&nbsp;&nbsp;Partial Invoice PDF', ' type="button"'); ?>
			</li>
		<?php endif; ?>
        <?php //if ($client_estimates && $client_estimates->num_rows()) : ?>
			<li>
				<a href="#new_payment" role="button"  data-toggle="modal"
				   style="margin-top: 10px"><i class="fa fa-credit-card"><b class="bg-success"></b></i>Add Payment</a>
			</li>
		<?php // endif; ?>
			
			<li>
				<?php echo anchor('workorders/', '<i class="fa fa-times"><b class="bg-info"></b></i>Close'); ?>
			</li>
		<?php if ($this->session->userdata['user_type'] == 'admin') : ?>
			<li>
				<a href="#tracking" role="button"  data-toggle="modal"><i class="fa fa-truck"><b class="bg-warning"></b></i>Tracking</a>
			</li>
		<?php endif; ?>
</ul>
</nav>
