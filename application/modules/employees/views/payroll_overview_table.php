<?php $count = 0; ?>
<?php $totalPayrollBonuses = 0; ?>
<?php $totalBonuses = 0; ?>
<?php $payrollTotalToPay = 0; ?>
	<div class="table-responsive">
		<table class="table table-hover m-b-none">
		<?php foreach($payroll_overview_report as $key => $payroll) : ?>
			<tr class="bg-light">
				<th colspan="<?php if(!$pdf) : ?>10<?php else : ?>9<?php endif; ?>" class="text-center font-bold">
					<?php echo ucfirst($key); ?>
				</th>
			</tr>

			<?php if(!empty($payroll['employees'])) : ?>

				<tr>
					<th class="text-center" width="20px">#</th>
					<th class="text-center">Name</th>
					<th class="text-center">Hours</th>
					<th class="text-center">Rate</th>
					<th class="text-center">Deductions</th>
					<th class="text-center">To Pay</th>
					<th class="text-center">AVG MHR</th>
					<th class="text-center">Total R</th>
					<th class="text-center">Late</th>

					<?php if(!$pdf) : ?>
					<th class="text-center">Report</th>
					<?php endif; ?>
				</tr>

				<?php foreach($payroll['employees'] as $employee) : ?>
					<tr>
						<td class="text-center"><?php echo ++$count; ?></td>
						<td>
							<?php if(!$pdf) : ?>
							<a href="<?php echo base_url('employees/payroll/' . $employee->employee_id . '/' . $payroll_id); ?>" target="_blank">
							<?php endif; ?>
								<?php echo $employee->emp_name; ?>
							<?php if(!$pdf) : ?>
							</a>
							<?php endif; ?>
						</td>
						<td><?php echo $employee->worked_payed; ?></td>
						<?php if(isAdmin()) : ?>
							<td><?php echo money(round($employee->worked_rate, 4)); ?></td>
						<?php else : ?>
							<td> - </td>
						<?php endif; ?>
						
						<?php if(isAdmin()) : ?>
							<td><?php echo money($employee->deduction_amount ? $employee->deduction_amount : 0); ?></td>
						<?php else: ?>
							<td> - </td>
						<?php endif; ?>
						<?php if(isAdmin()) : ?>
							<td><?php echo money(round($employee->worked_total_pay, 2)); ?></td>
						<?php else: ?>
							<td> - </td>
						<?php endif; ?>
						<?php if(isAdmin()) : ?>
						<td>
							<?php if(isset($employee->empmhrs)) : ?>
								<div>
                                    <?php echo money(element('mhrs_return2', $employee->empmhrs, 0)); ?>
								</div>
							<?php endif; ?>

							<?php if(isset($employee->estmhrs)) : ?>
								<div>
                                    <?php echo money(element('mhrs_return', $employee->estmhrs, 0)); ?>
								</div>
							<?php endif; ?>
						</td>
						<?php else: ?>
							<td> - </td>
						<?php endif; ?>
						<?php if(isAdmin()) : ?>
						<td>
							<?php if(isset($employee->empmhrs)) : ?>
								<div>
                                    <?php echo money((element('total2', $employee->empmhrs, 0) + element('total2',
                                                $employee->empmhrs, 0)) / 2); ?>
								</div>
							<?php endif; ?>

							<?php if(isset($employee->estmhrs)) : ?>
								<div>
                                    <?php echo money(element('total', $employee->estmhrs, 0)); ?>
								</div>
							<?php endif; ?>
						</td>
						<?php else: ?>
							<td> - </td>
						<?php endif; ?>
						<td><?php echo $employee->worked_lates; ?></td>

						<?php if(!$pdf) : ?>
						<td>
							<?php if(isAdmin()) : ?>
							<a target="_blank" class="btn btn-default btn-xs" href="<?php echo base_url('employees/payroll/' . $employee->employee_id . '/' . $payroll_id . '/1'); ?>">
								<i class="fa fa-book"></i>
							</a>
							<?php endif; ?>
						</td>
						<?php endif; ?>

						<?php unset($users[$employee->employee_id]); ?>
						<?php /*if(isAdmin()) : ?>
							<?php $totalPayrollBonuses += $empPayrollBonuses; ?>
							<?php $totalBonuses += $empBonuses;*/ ?>
						<?php /*endif;*/ ?>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="<?php if(!$pdf) : ?>10<?php else : ?>9<?php endif; ?>" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
			<tr>
				<td colspan="2" class="font-bold">Total hours for '<?php echo ucfirst($key); ?>':</td>
				<td class="font-bold"><?php echo isset($payroll['total']->worked_payed) ? $payroll['total']->worked_payed : 0; ?></td>
				<?php if(isAdmin()) : ?>
				<td colspan="2" class="font-bold">Total to pay for '<?php echo ucfirst($key); ?>':</td>
				
					<td class="font-bold"><?php echo money(isset($payroll['total']->worked_total_pay) ? $payroll['total']->worked_total_pay : 0); ?></td>
					<?php if(isset($payroll['total']->worked_total_pay)) : ?><?php $payrollTotalToPay += $payroll['total']->worked_total_pay; ?> <?php endif; ?>
				<?php else : ?>
					<td colspan="3">&nbsp;</td>
				<?php endif; ?>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>

				<?php if(!$pdf) : ?>
				<td>&nbsp;</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>

		</table>
	</div>
	<div class="table-responsive">
		<table class="table table-hover">
			<tr class="bg-light">
				<th colspan="<?php if(!$pdf) : ?>6<?php else : ?>5<?php endif; ?>" class="text-center font-bold">
					Did Not Work
				</th>
			</tr>
			<tr>
				<th class="text-center">#</th>
				<th class="text-center">Name</th>
				<?php if(isAdmin()) : ?><th class="text-center">Rate</th><?php endif; ?>
				<?php if(isAdmin()) : ?><th class="text-center">Total Bonuses</th><?php endif; ?>
				<th class="text-center">Type</th>

				<?php if(!$pdf) : ?>
				<th class="text-center">Report</th>
				<?php endif; ?>
			</tr>
			<?php if(!empty($users)) : ?>
				<?php foreach($users as $user) : ?>
					<tr>
						<td><?php echo ++$count; ?></td>
						<td><?php echo $user->emp_name; ?></td>
						<?php if(isAdmin()) : ?><td><?php echo money($user->emp_hourly_rate ? $user->emp_hourly_rate : 0); ?></td><?php endif; ?>
						<?php if(isAdmin()) : ?>
							<td>
								<?php echo money(0); ?>
								<?php /*$empBonuses = collectedBonuses($user->employee_id, $payroll_id);*/ ?>
								<?php //echo $empBonuses; ?>
							</td>
						<?php endif; ?>
						<td><?php echo ucfirst($user->emp_type); ?></td>

						<?php if(!$pdf) : ?>
						<td>
							<?php if(isAdmin()) : ?>
							<a target="_blank" class="btn btn-default btn-xs" href="<?php echo base_url('employees/payroll/' . $user->employee_id . '/' . $payroll_id . '/1'); ?>">
								<i class="fa fa-book"></i>
							</a>
							<?php endif; ?>
						</td>
						<?php endif; ?>
					</tr>
					<?php /*if(isAdmin()) : ?>
						<?php $totalBonuses += $empBonuses; ?>
					<?php endif;*/ ?>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="<?php if(!$pdf) : ?>6<?php else : ?>5<?php endif; ?>" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
		</table>
	</div>
	<div class="table-responsive">
		<table class="table">
			<tr class="bg-dark">
				<td>
					<strong>Total Hours: <?php echo isset($total['sum_hours']) ? $total['sum_hours'] : 0; ?></strong>
				</td>
				<?php if(isAdmin()) : ?>
				<td>
					<strong>Total To Pay: <?php echo money(isset($payrollTotalToPay) ? round($payrollTotalToPay, 2) : 0); ?></strong>
				</td>
				<td>
					<strong>AVG To Pay: <?php echo money($count ? round($payrollTotalToPay / $count, 2) : 0); ?></strong>
				</td>
				
				<td>
					<strong>Payroll Bonuses: <?php echo money(0); //echo $totalPayrollBonuses; ?></strong>
				</td>
				<td>
					<strong>Total Bonuses: <?php echo money(0); //echo $totalBonuses; ?></strong>
				</td>
				<?php endif; ?>
			</tr>
		</table>
	</div>
