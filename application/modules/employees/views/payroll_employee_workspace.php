<div id="workspace" style=" border: none!important;">
	<?php foreach($weeks as $key => $week) : ?>
		<?php $week['num'] = $key + 1; ?>
		<?php $this->load->view('payroll_week_table', $week);?>
	<?php endforeach; ?>
    <?php if (isAdmin() || $this->router->fetch_class() == 'cron' || is_cli()) : ?><?php if(!isset($pdf)) : ?>
		<?php $this->load->view('payroll_biweekly_table');?>
        <?php else: ?>
        <?php $this->load->view('payroll_biweekly_table_pdf');?>
        <?php endif; ?>
	<?php endif; ?>
</div>
