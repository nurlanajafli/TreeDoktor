<div id="payrollEmployees" class="panel panel-default p-n col-md-3 col-sm-4" style="max-height: 90%; overflow-y: auto;">
	<header class="panel-heading">Select Employee</header>
	<ul class="nav">
		<?php $status = NULL; ?>
		<?php foreach ($employees as $key => $val) : ?>
			<?php if($status != $val['emp_status']) : ?>
				<?php $status = $val['emp_status']; ?>
				<div class="wrapper b-b header font-bold text-center"><?php echo ucfirst($status); ?></div>
			<?php endif; ?>
			<li class="b-b b-light<?php if($employee_id == $val['employee_id']) : ?> active<?php endif; ?>">
				<a data-emp="<?php echo $val['employee_id']; ?>" href="<?php echo base_url('employees/payroll/' . $val['employee_id']); ?>">
					<i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>
					<?php echo $val['emp_name']; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<div class="clearfix"></div>
	<?php if($employee_id) : ?>
		<script>
			$parentDiv = $('#payrollEmployees');
			$innerListItem = $('[data-emp="<?php echo $employee_id; ?>"]');
			$parentDiv.scrollTop($parentDiv.scrollTop() + $innerListItem.offset().top - 95);
		</script>
	<?php endif; ?>
</div>
