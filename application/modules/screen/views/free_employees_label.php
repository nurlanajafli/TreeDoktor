<?php foreach ($employees as $employee) : ?>
	<li class="label bg-primary ui-draggable addMember b-a" style="text-shadow: 1px 1px #626262;border-color: #000;"
	    data-emp_id="<?php echo $employee->employee_id; ?>"><?php echo $employee->emp_name; ?></li>
<?php endforeach; ?>