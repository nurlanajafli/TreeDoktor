<div data-origin id="change_<?php echo $time; ?>" class="modal fade payroll-model-exclamation" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="false" style="color: #000;">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Time Login Details - <?php echo date(getDateFormat(), strtotime($time)); ?>
                <span class="btn btn-xs btn-rounded " tabindex="0" data-html="true" data-container="body"
                      data-toggle="popover" title="" role="button" data-trigger="focus" data-content="
                    <div class='row'>
                        <div class='col-md-2'>
                            <span class='badge bg-white' title='clicked' style='cursor: pointer;'>
                                <i class='fa fa-desktop payroll-desktop fa-2x' aria-hidden='true' style='color: #8ec165;'></i>
                            </span>
                        </div>
                        <div class='col-md-2'>
                            <span class='badge bg-white' title='clicked' style='cursor: pointer;'>
                                <i class='fa fa-mobile fa-2x' aria-hidden='true' style='color: #8ec165;'></i>
                            </span>
                        </div>
                        <div class='col-md-7'>
                            location details within the office
                        </div>
                    </div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'>
                        <div class='col-md-2'>
                            <span class='badge bg-white' title='clicked' style='cursor: pointer;'>
                                <i class='fa fa-desktop payroll-desktop fa-2x' aria-hidden='true' style='color: #ffc333;'></i>
                            </span>
                        </div>
                        <div class='col-md-2'>
                            <span class='badge bg-white' title='clicked' style='cursor: pointer;'>
                                <i class='fa fa-mobile fa-2x' aria-hidden='true' style='color: #ffc333;'></i>
                            </span>
                        </div>
                        <div class='col-md-7'>
                             location details out of the office
                        </div>
                    </div>
                    <div class='line line-dashed line-sm pull-in'></div>
                     <div class='row'>
                        <div class='col-md-2'>
                            <span class='badge bg-white' title='clicked' style='cursor: pointer;'>
                                <i class='fa fa-desktop payroll-desktop fa-2x' aria-hidden='true' style='color: #bebebe;'></i>
                            </span>
                        </div>
                        <div class='col-md-2'>
                            <span class='badge bg-white' title='clicked' style='cursor: pointer;'>
                                <i class='fa fa-mobile fa-2x' aria-hidden='true' style='color: #bebebe;'></i>
                            </span>
                        </div>
                        <div class='col-md-7'>
                           no location details
                        </div>
                    </div>
                    <div class='line line-dashed line-sm pull-in'></div>
                     <div class='row'>
                        <div class='col-md-2'>
                            <span  class='badge bg-white' title='clicked' style='cursor: pointer;'>
                                <i class='fa fa-pencil-square-o fa-2x payroll-edit' aria-hidden='true' style='color: #bebebe;'></i>
                            </span>
                        </div>
                        <div class='col-md-8'>
                           time edited manually
                        </div>
                    </div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    " data-original-title="<span style='font-size: 13px;'>Location details</span>">
                    <i class="fa fa-question-circle payroll-question" aria-hidden="true"></i>
                </span>
                <a class="btn btn-success btn-xs pull-right addEmpRow" data-time="<?php echo $time; ?>"
                   data-emp_id="<?php echo $employee_id; ?>" data-date="<?php echo date('Y-m-d', strtotime($time)); ?>">
                    Add Time
                </a>
                <div class="clear"></div>
            </header>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover tablesorter payroll_model_edit_table payroll-table">
                        <thead>
                        <tr>
                            <th class="header payroll_model_edit_thead">Clock In</th>
                            <th class="header payroll_model_edit_thead">Clock Out</th>
                            <?php /*if(isAdmin()) : ?>
								<th class="header">Hourly Rate</th>
							<?php endif;*/ ?>
                            <th class="header payroll_model_edit_thead">Tracked time</th>
                            <th class="header payroll_model_edit_thead"></th>
                        </tr>
                        </thead>
                        <tbody class="emp_data">
                        <?php foreach ($logins as $login) : ?>

                            <?php
                            $dataLogin = getColorAndIcon(
                                $login->login_lat,
                                $login->login_lon,
                                $login->login_office,
                                $login->login_from_app,
                                $login->login_in_office,
                                $login->login_app_in_office
                            );
                            $colorLogin = $dataLogin['color'];
                            $iconLogin = $dataLogin['icon'];
                            $isLinkLogin = $dataLogin['isLink'];

                            $dataLogout = getColorAndIcon(
                                $login->logout_lat,
                                $login->logout_lon,
                                $login->logout_office,
                                $login->logout_from_app,
                                $login->logout_in_office,
                                $login->logout_app_in_office,
                            );
                            $colorLogout = $dataLogout['color'];
                            $iconLogout = $dataLogout['icon'];
                            $isLinkLogout = $dataLogout['isLink'];
                            ?>

                            <tr data-login_id="<?php echo $login->login_id; ?>">
                                <td>
                                    <input name="login" <?php echo $is_allow_edit ? '' : 'disabled="disabled"' ?> type="time"
                                           value="<?php echo $login->login ? date('H:i', strtotime($login->login)) : ''; ?>"
                                           onblur="$(this).parent().parent().find('.saveForm').find('.loginTime').val($(this).val())"
                                           class="payroll_model_edit_input">
                                    <?php if ($isLinkLogin !== false) : ?>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $login->login_lat; ?>,<?php echo $login->login_lon; ?>"
                                       target="_blank">
                                        <?php endif; ?>
                                        <i class="fa fa-<?= $iconLogin ?>" style="color: <?= $colorLogin ?>;"
                                           aria-hidden="true" data-color="<?= $colorLogin ?>"></i>
                                        <?php if ($isLinkLogin !== false) : ?>
                                    </a>
                                <?php endif; ?>
                                </td>
                                <td>
                                    <input name="logout" <?php echo $is_allow_edit ? '' : 'disabled="disabled"' ?> type="time"
                                           value="<?php echo $login->logout ? date('H:i', strtotime($login->logout)) : ''; ?>"
                                           onblur="$(this).parent().parent().find('.saveForm').find('.logoutTime').val($(this).val())"
                                           class="payroll_model_edit_input">
                                    <?php if ($isLinkLogout !== false) : ?>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $login->logout_lat; ?>,<?php echo $login->logout_lon; ?>"
                                       target="_blank">
                                        <?php endif; ?>
                                        <i class="fa fa-<?= $iconLogout ?>" style="color: <?= $colorLogout ?>;"
                                           aria-hidden="true" data-color="<?= $colorLogout ?>"></i>
                                        <?php if ($isLinkLogout !== false) : ?>
                                    </a>
                                <?php endif; ?>
                                </td>
                                <td class="timeDiff">
                                    <?php if ($login->login && $login->logout) : ?>
                                        <?php echo date('H:i', (strtotime($login->logout) - strtotime($login->login)) + strtotime($login->login_date)); ?>
                                    <?php else : ?>
                                        '-'
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form data-type="ajax" data-url="<?php echo base_url('payroll/ajax_save_time'); ?>"
                                          data-callback="_after_save" style="display: inline-block;" class="saveForm">
                                        <input type="hidden" value="<?php echo $login->login_id; ?>" name="login_id">
                                        <input type="hidden" value="<?php echo $is_allow_edit ? 1 : 0; ?>" name="is_allow_edit">
										<input type="hidden"
                                               value="<?php echo $login->login ? date('H:i', strtotime($login->login)) : ''; ?>"
                                               name="login" class="loginTime">
                                        <input type="hidden"
                                               value="<?php echo $login->logout ? date('H:i', strtotime($login->logout)) : ''; ?>"
                                               name="logout" class="logoutTime">
                                        <button type="submit" class="btn btn-xs btn-default disabled payroll_model_edit_table_save"
                                                disabled="disabled">SAVE
                                        </button>
                                    </form>
                                    <form data-type="ajax"
                                          data-url="<?php echo base_url('payroll/ajax_delete_time'); ?>"
                                          data-callback="_after_delete" style="display: inline-block;">
                                        <?php if (isset($employee) && !empty($employee)): ?>
                                            <input type="hidden" value="<?php echo $employee->emp_user_id; ?>"
                                                   name="user_id">
                                            <input type="hidden" value="<?php echo $login->login_date; ?>"
                                                   name="login_date">
                                        <?php endif; ?>
                                        <input type="hidden" value="<?php echo $login->login_id; ?>" name="login_id">
                                        <button type="submit" class="btn btn-xs bg-danger payroll-remove-btn"
                                                onclick="if(confirm('Are you sure?')) $(this).parents('form:first').submit(); return false;">
                                            <i class="fa fa-trash-o"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td class="font-bold text-right">Total</td>
                            <td>
                                <span class="totalWorked"><?php echo isset($worked_data[$time]) ? $worked_data[$time]->worked_hours : '0'; ?></span>
                                hrs.
                            </td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <div class="pull-right">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
