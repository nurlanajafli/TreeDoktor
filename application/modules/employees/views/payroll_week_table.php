<div class="row p-5"
     <?php if (isset($pdf)): ?>style="margin-bottom:0px; padding-bottom:0; padding-top:0;"<?php endif; ?>>
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table m-b-none b-a">
                <thead>
                <tr>
                    <th class="text-right bg-light b-r b-b"
                        style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf;" width="105px">Week
                        #<?php echo $num; ?></th>
                    <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                        <?php $key = date('Y-m-d', $i + 3600); ?>
                        <th class="text-center bg-light b-r b-b" width="100"
                            style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf;"><?php echo date('D', $i + 3600); ?>
                            <i id="exclamation_change_<?php echo $key; ?>" class="fa fa-exclamation exclamation_hide" aria-hidden="true"></i>
                        </th>
                    <?php endfor; ?>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">PD</td>
                    <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                        <!--					<td class="text-center bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">--><?php //echo date('Y-m-d', $i); ?><!--</td>-->
                        <td class="text-center bg-light b-r b-b"
                            style="background: #f1f1f1;color: #717171;"><?php echo getDateTimeWithTimestamp($i + 3600) ?></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">Clock In</td>
                    <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                        <td class="text-center">
                            <?php $key = date('Y-m-d', $i + 3600); ?>
                            <?php if (!isset($pdf)) :
                            /*echo "<pre>";
                            var_dump($worked_data[$i]);
                            die;*/
                            ?>
                            <a href="#change_<?php echo $key; ?>"
                               class="edit-time"<?php if (isset($worked_data[$key]) && $worked_data[$key]->worked_auto_logout) : ?> style="color: #fb6b5b;"<?php elseif (isset($worked_data[$key]) && isset($worked_data[$key]->not_office)) : ?>style="color: #4cc0c1;"<?php endif; ?>
                               role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                                <?php endif; ?>
                                <?php if (isset($worked_data[$key]) && $worked_data[$key]->worked_start) : ?>
                                    <?php echo date('H:i', strtotime($worked_data[$key]->worked_start)); ?>
                                <?php else : ?>
                                    00:00
                                <?php endif; ?>
                                <?php if (!isset($pdf)) : ?>
                            </a>
                        <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>

                <tr>
                    <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">Clock Out</td>
                    <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                        <?php $key = date('Y-m-d', $i + 3600); ?>
                        <td class="text-center">
                            <?php if (!isset($pdf)) : ?>
                            <a href="#change_<?php echo $key; ?>"
                               class="edit-time"<?php if (isset($worked_data[$key]) && $worked_data[$key]->worked_auto_logout) : ?> style="color: #fb6b5b;"<?php elseif (isset($worked_data[$key]) && isset($worked_data[$key]->not_office)) : ?>style="color: #4cc0c1;" <?php endif; ?>
                               role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                                <?php endif; ?>
                                <?php if (isset($worked_data[$key]) && $worked_data[$key]->worked_end) : ?>
                                    <?php echo date('H:i', strtotime($worked_data[$key]->worked_end)); ?>
                                <?php else : ?>
                                    00:00
                                <?php endif; ?>
                                <?php if (!isset($pdf)) : ?>
                            </a>
                        <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php if ($lunch_state || $hasLunch): ?>
                    <tr>
                        <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">Lunch</td>
                        <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                            <?php $key = date('Y-m-d', $i + 3600); ?>
                            <td class="text-center">
                                <?php if (isset($worked_data[$key]) && $worked_data[$key]->worked_lunch !== NULL) : ?>
                                    <?php if (!isset($pdf)) : ?>
                                        <a
                                            class="editable editable-expense editable-buttons" href="#"
                                            data-name="expense_amount"
                                            data-type="text"
                                            data-clear="false"
                                            data-time="true"
                                            data-pk="<?php echo $worked_data[$key]->worked_id ?? 0; ?>"
                                            data-value="<?php echo $worked_data[$key]->worked_lunch ?? 0; ?>"
                                            data-worked_id="<?php echo $worked_data[$key]->worked_id ?? 0; ?>"
                                            data-lunch="<?php echo $worked_data[$key]->worked_lunch ?? 0; ?>"
                                            data-url="<?php echo base_url('payroll/ajax_change_lunch'); ?>"
                                            data-inputclass="form-control">
                                            <?php echo $worked_data[$key]->worked_lunch ?? 0; ?> hrs.
                                        </a>
                                    <?php else: ?>
                                        <?php echo $worked_data[$key]->worked_lunch ?? 0; ?>  hrs.
                                    <?php endif; ?>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">Worked</td>
                    <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                        <?php $key = date('Y-m-d', $i + 3600); ?>
                        <td class="text-center">
                            <?php echo isset($worked_data[$key]) ? ($worked_data[$key]->worked_hours - ($worked_data[$key]->worked_lunch ?? 0)) : 0; ?>
                            hrs.
                        </td>
                    <?php endfor; ?>
                </tr>

                <?php if (!isset($pdf) && isAdmin()) : ?>
                    <tr>
                        <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">Hourly
                            Rate
                        </td>
                        <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                            <?php $key = date('Y-m-d', $i + 3600); ?>
                            <td class="text-center">
                                <?php if (isset($worked_data[$key])) : ?>
                                    <?php if (!isset($pdf)) : ?>
                                        <a
                                            class="editable editable-expense editable-buttons" href="#"
                                            data-name="expense_amount"
                                            data-type="text"
                                            data-clear="false"
                                            data-pk="<?php echo $worked_data[$key]->worked_id ?? 0; ?>"
                                            data-value="<?php echo $worked_data[$key]->worked_hourly_rate ?? 0; ?>"
                                            data-worked_id="<?php echo $worked_data[$key]->worked_id ?? 0; ?>"
                                            data-rate="<?php echo $worked_data[$key]->worked_hourly_rate ?? 0; ?>"
                                            data-url="<?php echo base_url('payroll/ajax_change_rate'); ?>"
                                            data-inputclass="form-control">
                                            <?php echo $worked_data[$key]->worked_hourly_rate ?? 0; ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo money($worked_data[$key]->worked_hourly_rate); ?>
                                    <?php endif; ?>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endif; ?>
                <?php if (isAdmin()) : ?>
                    <tr>
                        <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">MHR Return
                        </td>
                        <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                            <?php $key = date('Y-m-d', $i + 3600); ?>
                            <td class="text-center">
                                <?php echo isset($worked_data[$key]->mhr['mhrs_return']) ? money($worked_data[$key]->mhr['mhrs_return']) : '-'; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endif; ?>
                <?php if (!isset($pdf)) : ?>
                    <tr>
                        <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">Teams</td>
                        <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                            <?php $key = date('Y-m-d', $i + 3600); ?>
                            <td class="text-center"
                                style="max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php if (isset($worked_data[$key]->team)) : ?>
                                    <?php foreach ($worked_data[$key]->team as $member) : ?>
                                        <a title="<?php echo $member['emp_name']; ?>" target="_blank"
                                           href="<?php echo base_url('employees/payroll/' . $member['employee_id'] . '/' . $payroll_id); ?>">
                                            <?php echo $member['emp_name']; ?>
                                        </a><br>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-right bg-light b-r b-b" style="background: #f1f1f1;color: #717171;">B.L.D</td>
                    <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                        <?php $key = date('Y-m-d', $i + 3600); ?>

                        <td class="text-center"
                            style="max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php if (isset($worked_data) && isset($worked_data[$key])): ?>
                                <a
                                    class="editable editable-expense editable-buttons" href="#"
                                    data-name="expense_amount"
                                    data-type="text"
                                    data-clear="false"
                                    data-pk="<?php echo $expenses_data[$key]['bld']['expense_id'] ?? 0; ?>"
                                <?php if (isset($expenses_data[$key]['bld']) && isset($expenses_data[$key]['bld']['expense_id'])): ?>
                                    data-url="<?php echo base_url('reports/update_expense_amount'); ?>"
                                <?php else: ?>
                                    data-url="<?php echo base_url('reports/add_expense_amount/' . $worked_data[$key]->worked_id . '/employee_benefits'); ?>"
                                <?php endif; ?>
                                    data-value="<?php echo $expenses_data[$key]['bld']['expense_amount'] ?? 0; ?>"
                                    data-inputclass="form-control">
                                <?php if (!isset($pdf)) : ?>
                                    <?php echo $expenses_data[$key]['bld']['expense_amount'] ?? 0; ?>
                                <?php else: ?>
                                    <?php echo money($expenses_data[$key]['bld']['expense_amount'] ?? 0); ?>
                                <?php endif; ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td class="text-right bg-light b-r" style="background: #f1f1f1;color: #717171;">Extra Expenses</td>
                    <?php for ($i = $week_start_time; $i <= $week_end_time + 3600; $i += 86400) : ?>
                        <?php $key = date('Y-m-d', $i + 3600); ?>
                        <td class="text-center"
                            style="max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php if (isset($worked_data) && isset($worked_data[$key])): ?>
                                <a
                                        class="editable editable-expense" href="#"
                                        data-name="expense_amount"
                                        data-type="text"
                                        data-clear="false"
                                        data-pk="<?php echo $expenses_data[$key]['extra']['expense_id'] ?? 0; ?>"
                                    <?php if (isset($expenses_data[$key]['extra']) && isset($expenses_data[$key]['extra']['expense_id'])): ?>
                                        data-url="<?php echo base_url('reports/update_expense_amount'); ?>"
                                    <?php else: ?>
                                        data-url="<?php echo base_url('reports/add_expense_amount/' . $worked_data[$key]->worked_id . '/employee_benefits/1'); ?>"
                                    <?php endif; ?>
                                        data-value="<?php echo $expenses_data[$key]['extra']['expense_amount'] ?? 0; ?>"
                                        data-inputclass="form-control">
                                    <?php if (!isset($pdf)) : ?>
                                        <?php echo $expenses_data[$key]['extra']['expense_amount'] ?? 0; ?>
                                    <?php else: ?>
                                        <?php echo money($expenses_data[$key]['extra']['expense_amount'] ?? 0); ?>
                                    <?php endif; ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td class="text-right bg-light b-r font-bold"
                        style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171; font-weight: bold;">
                        Late
                    </td>
                    <td class="text-right bg-light b-r text-center"
                        style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171;">
                        <?php echo isset($weeks_data[$num - 1]->worked_lates) && $weeks_data[$num - 1]->worked_lates ? $weeks_data[$num - 1]->worked_lates : '-'; ?>
                    </td>
                    <td class="text-right bg-light b-r font-bold"
                        style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171; font-weight: bold;">
                        AVG Rate
                    </td>
                    <?php if (isAdmin()) : ?>
                        <td class="text-right bg-light b-r text-center"
                            style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171;">
                            <?php echo money(isset($weeks_data[$num - 1]->worked_rate) ? $weeks_data[$num - 1]->worked_rate : 0); ?>
                        </td>
                    <?php else : ?>
                        <td class="text-right bg-light b-r text-center"
                            style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171;">
                            -
                        </td>
                    <?php endif; ?>
                    <td class="text-right bg-light b-r font-bold"
                        style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171; font-weight: bold;">
                        To Pay
                    </td>
                    <?php if (isAdmin()) : ?>
                        <td class="text-right bg-light b-r text-center"
                            style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171;">
                            <?php echo money(isset($weeks_data[$num - 1]->worked_total_pay) ? $weeks_data[$num - 1]->worked_total_pay : 0); ?>
                        </td>
                    <?php else : ?>
                        <td class="text-right bg-light b-r text-center"
                            style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171;">
                            -
                        </td>
                    <?php endif; ?>
                    <td class="text-right bg-light b-r font-bold"
                        style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171; font-weight: bold;">
                        Total Hours
                    </td>
                    <td class="text-right bg-light text-center"
                        style="border-top: 1px solid #cfcfcf;background: #f1f1f1;color: #717171;">
                        <?php echo isset($weeks_data[$num - 1]->worked_payed) ? $weeks_data[$num - 1]->worked_payed : 0; ?>
                        hrs.
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
