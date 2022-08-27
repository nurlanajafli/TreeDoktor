
	<?php foreach($weeks as $key => $week) : ?>
		<?php $week['number'] = $key; ?>
		<div style="height: 106mm;<?php if(!$key) : ?><?php else : ?>margin-top: 2mm;<?php endif; ?>">
		<?php $this->load->view('payroll_report_pdf', $week);?>
		</div>
		<?php if(!$key) : ?>
			<div style="border-bottom: 1px solid #D2D2D2;"></div>
		<?php endif; ?>
	<?php endforeach; ?>
