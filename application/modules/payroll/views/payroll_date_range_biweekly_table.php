<div class="row p-5">
    <div class="<?= (bool)$employee['emp_field_estimator'] ? 'col-md-8' : 'col-md-9'?> m-b-xs text-center">
        <div class="table-responsive">
            <table class="table m-b-none"<?php if(isset($pdf)) : ?> style="width: 50%;"<?php endif; ?>>
                <tbody>
                <tr>
                    <td class="bg-light font-bold b-a text-right" style="font-weight: bold; border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171;">
                        Total:
                    </td>
                    <td class="b-a text-center" style="border-top: 1px solid #cfcfcf;">
                        <?php echo isset($payroll_total_data['total_hours']) ? round($payroll_total_data['total_hours'], 2) : 0; ?> hrs.
                    </td>
                </tr>
                <tr>
                    <td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
                        Hourly Rate:
                    </td>
                    <td class="b-a text-center">
                        <?php echo money((float) $payroll_total_data['rate'] ?? 0); ?>
                    </td>
                </tr>
                <tr>
                    <td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
                        Late:
                    </td>
                    <td class="b-a text-center">
                        <?php echo isset($payroll_total_data['late']) ? $payroll_total_data['late'] : 0; ?>
                    </td>
                </tr>
                <?php if(isset($employee['estmhrs'])) : ?>
                    <tr>
                        <td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
                            AVG Man Hours Return (EST):
                        </td>
                        <td class="b-a text-center">
                            <?php echo money(element('mhrs_return2', $employee['estmhrs'], 0)); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if(isset($employee['empmhrs'])) : ?>
                    <tr>
                        <td class="bg-light font-bold b-a text-right" style="font-weight: bold; background: #f1f1f1;color: #717171;">
                            AVG Man Hours Return (EMP):
                        </td>
                        <td class="b-a text-center">
                            <?php //echo money(element('mhrs_return2', $employee->empmhrs, 0)); ?>
                            <?php echo money(element('new_avg', $employee['empmhrs'], 0)); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if($employee['emp_field_estimator'] && isset($more)) : ?>
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
                            $worked_total_pay = isset($payroll_total_data['total_pay']) ? $payroll_total_data['total_pay'] : 0;
                            ?>
                            <?php echo money($worked_total_pay); ?>
                        </strong>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php if(!isset($pdf)) : ?>
        <div class="<?= (bool)$employee['emp_field_estimator'] ? 'col-md-4' : 'col-md-3'?>  text-right" style="position: initial;">
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
                    <?php if(isset($employee['empmhrs']) || isset($employee['estmhrs'])) : ?>
                        <tr>
                            <td style="border-top: none;">&nbsp;</td>
                            <td style="border-top: none;">&nbsp;</td>
                        </tr>
                    <?php endif; ?>
                    <?php if($employee['emp_field_estimator'] && isset($more)) : ?>
                        <tr>
                            <td style="border-top: none;">&nbsp;</td>
                            <td style="border-top: none;">&nbsp;</td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
            <div class="m-b" style="margin-top: -20px;">
                <a href="<?php echo base_url('payroll/range_report_pdf/' . $employee_id . '/' . $range_start .'/' . $range_end); ?>" class="btn btn-default">Payroll PDF</a>
                <?php /*<a href="<?php echo base_url('payroll/range_report_pdf/' . $employee_id . '/?payroll_csv=1'); ?>" class="btn btn-success">Payroll CSV</a>*/ ?>
            </div>
        </div>
    <?php endif; ?>
</div>
