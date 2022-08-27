<div class="row p-5">
	<div class="<?= (bool)$employee->emp_field_estimator ? 'col-md-8' : 'col-md-9'?> m-b-xs text-center">
		<div class="table-responsive">
			<table class="table m-b-none"<?php if(isset($pdf)) : ?> style="width: 50%;"<?php endif; ?>>
				<tbody>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;border-top: 1px solid #cfcfcf;">
						Payday:
					</td>
					<td class="b-a text-center" style="border-top: 1px solid #cfcfcf;">
<!--						--><?php //echo $payroll->payroll_day; ?>
						<?php echo getDateTimeWithDate($payroll->payroll_day, 'Y-m-d'); ?>
					</td>
				</tr>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171;">
						Total:
					</td>
					<td class="b-a text-center" style="border-top: 1px solid #cfcfcf;">
						<?php echo isset($payroll_total_data->worked_payed) ? $payroll_total_data->worked_payed : 0; ?> hrs.
					</td>
				</tr>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						Hourly Rate:
					</td>
					<td class="b-a text-center">
						<?php $workPayed = isset($payroll_total_data->worked_payed) && (float)$payroll_total_data->worked_payed ? $payroll_total_data->worked_payed : 1; ?>
						<?php echo money(isset($payroll_total_data->worked_total_pay_pdf) ? round($payroll_total_data->worked_total_pay_pdf / $workPayed, 2) : 0); ?>
					</td>
				</tr>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						Subtotal:
					</td>
					<td class="b-a text-center">
						<?php echo money(isset($payroll_total_data->worked_rate) && isset($payroll_total_data->worked_payed) ? round($payroll_total_data->worked_rate * $payroll_total_data->worked_payed, 2) : 0); ?>
					</td>
				</tr>
				<tr>
                    <td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						Late:
					</td>
					<td class="b-a text-center">
						<?php echo isset($payroll_total_data->worked_lates) ? $payroll_total_data->worked_lates : 0; ?>
					</td>
				</tr>
<?php if ($deduction_state === true || isset($deductions)):?>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="vertical-align:middle; font-weight: bold; background: #f1f1f1;color: #717171;">
						Deductions: 
					</td>
					<td class="b-a text-center">
						<?php $deduction_id = isset($deductions->deduction_id) ? $deductions->deduction_id : NULL; ?>
						<?php $deduction_amount = isset($deductions->deduction_amount) ? $deductions->deduction_amount : NULL; ?>
						<?php $deduction_amount = ($deduction_amount === NULL && $employee->deductions_state && $employee->deductions_amount) ? 0 : $deduction_amount; ?>
						<?php if(!isset($pdf) && $deduction_state === true) : ?>
                        <a href="#" title="" class="employeeDeductions" data-html="true" data-placement="top"
                           data-content="<div class='text-center'><input type='text' class='m-n deductionsSum' style='width: 50px;height: 21px;padding: 6px;' value='<?php echo $deduction_amount; ?>'><a class='btn btn-xs btn-info m-l-xs saveDeduction' title='Change' data-payroll_id='<?php echo $payroll_id; ?>' data-employee_id='<?php echo $employee_id; ?>' data-deduction_id='<?php echo $deduction_id; ?>'><i class='fa fa-check'></i></a></div>"
                           data-original-title="Deductions<button type=&quot;button&quot;class=&quot;m-l-sm close pull-right&quot; data-dismiss=&quot;popover&quot;>×</button>">
						<?php endif; ?>
							<?php echo money(isset($deduction_amount) ? $deduction_amount : 0); ?>
						<?php if(!isset($pdf) && $deduction_state === true) : ?>
						</a>
						<?php endif; ?>
						<?php if(!isset($pdf) && $employee->deductions_state && $employee->deductions_amount) : ?>
							<br>Profile: <?php echo money($employee->deductions_amount); ?>
						<?php endif; ?>
					</td>
				</tr>
<?php endif;?>
				<?php if(isset($employee->estmhrs)) : ?>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						AVG Man Hours Return (EST):
					</td>
					<td class="b-a text-center">
                        <?php echo money(element('mhrs_return2', $employee->estmhrs, 0)); ?>
					</td>
				</tr>
				<?php endif; ?>
				<?php if(isset($employee->empmhrs)) : ?>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						AVG Man Hours Return (EMP):
					</td>
					<td class="b-a text-center">
                        <?php //echo money(element('mhrs_return2', $employee->empmhrs, 0)); ?>
                        <?php echo money(element('new_avg', $employee->empmhrs, 0)); ?>
					</td>
				</tr>
				<?php endif; ?>
				<?php if($employee->emp_field_estimator && isset($more)) : ?>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						TOTAL <?php echo money(GOOD_MAN_HOURS_RETURN); ?>+:
					</td>
					<td class="b-a text-center">
                        <?php echo money($more); ?>
					</td>
				</tr>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						TOTAL <?php echo money(GOOD_MAN_HOURS_RETURN); ?>-:
					</td>
					<td class="b-a text-center">
                        <?php echo money($less); ?>
					</td>
				</tr>
				<?php endif; ?>
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						B.L.D / Extra Expenses:
					</td>
					<td class="b-a text-center">
						<?php $bld_total_origin = isset($bld_total) ? $bld_total : 0; ?>
						<?php $extra_total_origin = isset($extra_total) ? $extra_total : 0; ?>
						<?php echo money($bld_total_origin); ?> / <?php echo money($extra_total_origin); ?>
					</td>
				</tr>
				
				<tr>
					<td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
						Total Pay:
					</td>
					<td class="b-a text-center">
						<strong>
							<?php
								$worked_total_pay = isset($payroll_total_data->worked_total_pay) ? $payroll_total_data->worked_total_pay : 0;
							?>
							<?php echo money($worked_total_pay+$bld_total_origin+$extra_total_origin); ?>
						</strong>
					</td>
				</tr>
				</tbody>
			</table>			
		</div>
	</div>
	<?php if(!isset($pdf)) : ?>
	<div class="<?= (bool)$employee->emp_field_estimator ? 'col-md-4' : 'col-md-3'?>  text-right" style="position: initial;">
		<div class="table-responsive hidden-xs">
			<table class="table m-b-none">
				<tbody>
				<tr>
					<td style="border-top: none;">&nbsp;</td>
					<td style="border-top: none;">&nbsp;</td>
				</tr>
				<tr>
					<td style="border-top: none;">&nbsp;</td>
					<td style="border-top: none;">&nbsp;<br>&nbsp;</td>
				</tr>
				<tr>
					<td style="border-top: none;">&nbsp;</td>
					<td style="border-top: none;">&nbsp;</td>
				</tr>
				<tr>
					<td style="border-top: none;">&nbsp;</td>
					<td style="border-top: none;">&nbsp;</td>
				</tr>
				<tr>
					<td style="border-top: none;">&nbsp;</td>
					<td style="border-top: none;">&nbsp;</td>
				</tr>
				<tr>
					<td style="border-top: none;">&nbsp;</td>
					<td style="border-top: none;">&nbsp;</td>
				</tr>

				<tr>
					<td style="border-top: none;">&nbsp;</td>
					<td style="border-top: none;">&nbsp;</td>
				</tr>
                <?php if(isset($employee->empmhrs) || isset($employee->estmhrs)) : ?>
                <tr>
                    <td style="border-top: none;">&nbsp;</td>
                    <td style="border-top: none;">&nbsp;</td>
                </tr>
                <?php endif; ?>
                <?php if($employee->emp_field_estimator && isset($more)) : ?>
				<tr>
					<td style="border-top: none;">&nbsp;</td>
					<td style="border-top: none;">&nbsp;</td>
				</tr>
                <?php endif; ?>

				</tbody>
			</table>
		</div>
		<?php /*if($employee->deductions_state && $employee->deductions_amount) : ?><br><?php endif;*/ ?>

        <div class="m-b" style="margin-top: -20px;">
            <?php if(!isset($pdf) && $employee->deductions_state && $employee->deductions_amount) : ?>
                <br>
            <?php endif; ?>
            <?php if($employee->emp_field_estimator) : ?>
                <a href="<?php echo base_url('employees/payroll_comission/' . $employee_id . '/' . $payroll_id); ?>" class="btn btn-default">Comission PDF</a>
            <?php endif; ?>

            <a href="<?php echo base_url('employees/payroll/' . $employee_id . '/' . $payroll_id . '/1'); ?>" class="btn btn-default">Payroll PDF</a>
            <a href="<?php echo base_url('employees/payroll/' . $employee_id . '/' . $payroll_id . '?payroll_csv=1'); ?>" class="btn btn-success">Payroll CSV</a>
        </div>
	</div>
	<?php endif; ?>
</div>
