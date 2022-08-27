<?php $this->load->view('includes/header'); ?>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
<link href="<?php echo base_url('assets/css/modules/employees/payroll.css'); ?>" rel="stylesheet">
<section class="padder">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
        <li class="active">Payroll<?php echo isset($employee) ? ' - ' . $employee->emp_name : '' ?></li>
    </ul>


    <?php $this->load->view('payroll_employees'); ?>

    <?php if (isset($payroll)) : ?>
        <div style="width: 15px;float: left;height: 10px;"></div>

        <div class="panel panel-default p-n"
             style="max-height: 90%; overflow-y: auto; overflow-x: hidden; padding-left: 5px;">
            <header class="panel-heading"><?php echo isset($employee) ? $employee->emp_name : '' ?>
                <div class="pull-right" style=" ">
                    <a href="" class="btn btn-sm btn-info" disabled="disabled"><< Previous Weeks</a>
                    <a href="<?php echo base_url('employees/payroll/' . $employee_id); ?>"
                       class="btn btn-sm btn-default" id="this-week">Current Weeks</a>
                    <a href="" class="btn btn-sm btn-info" disabled="disabled">Next Weeks >></a>
                </div>
                <div id="payrollrange" class="pull-right"
                     style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;">
                    <i class="fa fa-calendar"></i>&nbsp;
                    <span><?php echo getDateTimeWithDate($payroll['payroll_start_date'], 'Y-m-d'); ?> - <?php echo getDateTimeWithDate($payroll['payroll_end_date'], 'Y-m-d'); ?></span>
                    <i class="fa fa-caret-down"></i>
                </div>

                <div class="clear"></div>
            </header>
            <?php
            $this->load->view('payroll_date_range_workspace');
            ?>
        </div>
    <?php endif; ?>

    <script>
        $(document).ready(function () {

            $(".payroll-model-exclamation").each(function() {
                var id = $(this).attr('id');
                var fa = $(this).find('.fa');

                fa.each(function() {
                    var style = $(this).data('color');
                    var block = $("#exclamation_" + id);

                    if (style == '#ffc333' && !block.hasClass('exclamation-yellow')) {
                        block.addClass('exclamation-yellow');
                        block.removeClass('exclamation-grey');
                        block.removeClass('exclamation_hide');
                        return false;
                    } else if (style == '#bebebe' && !block.hasClass('exclamation-yellow')) {
                        block.addClass('exclamation-grey');
                        block.removeClass('exclamation_hide');
                    }
                });
            })

            init_editable_expense();

            $('.employeeLunch, .employeeRate, .employeeDeductions').popover();

            $('.employeeLunch, .employeeRate, .employeeDeductions').on('click', function (e) {
                $('.employeeLunch, .employeeRate, .employeeDeductions').not(this).popover('hide');
            });

            $(document).on('click', '.saveLunchTime', function () {
                $.post(baseUrl + 'payroll/ajax_change_lunch', {
                    lunch: $(this).parent().find('.lunchTime').val(),
                    worked_id: $(this).data('worked_id')
                }, function (resp) {
                    _reload_workspace();
                }, 'json');
                return false;
            });

            $(document).on('click', '.addEmpRow', function () {
                var time = $(this).data('time');
                var employee_id = $(this).data('emp_id');
                var date = $(this).data('date');
                if ($('#change_' + time).find('.emp_data').find('tr[data-login_id=""]').length)
                    return false;

                var html = '<tr data-login_id="">';
                html += '<td>' +
                    '<input name="login" type="time" value="" onblur="$(this).parent().parent().find(\'.saveForm\').find(\'.loginTime\').val($(this).val())" class="payroll_model_edit_input">' +
                    '<i class="fa fa-pencil-square-o" style="color: #909090;" aria-hidden="true"></i>' +
                    '</td>';
                html += '<td>' +
                    '<input name="logout" type="time" value="" onblur="$(this).parent().parent().find(\'.saveForm\').find(\'.logoutTime\').val($(this).val())" class="payroll_model_edit_input">' +
                    '<i class="fa fa-pencil-square-o" style="color: #909090;" aria-hidden="true"></i>' +
                    '</td>';
                html += '<td class="timeDiff"></td>';

                html += '<td><form data-type="ajax" data-url="' + baseUrl + 'payroll/ajax_save_time" data-callback="_after_insert" style="display: inline-block;" class="saveForm">';
                html += '<input type="hidden" value="" name="login" class="loginTime">';
                html += '<input type="hidden" value="" name="logout" class="logoutTime">';
                html += '<input type="hidden" value="' + employee_id + '" name="employee_id" class="employeeId">';
                html += '<input type="hidden" value="' + date + '" name="date" class="date">';

                html += '<button type="submit" class="btn btn-xs btn-default disabled payroll_model_edit_table_save" disabled="disabled">SAVE</button></form>';
                html += "\n";
                html += '<div style="display:inline-block;" class="cancelInsert"><button class="btn btn-xs bg-danger" onclick="$(this).parents(\'tr:first\').remove();"><i class="fa fa-trash-o"></i></button></div>';
                html += '</td>';

                html += '</tr>';
                $('#change_' + time).find('.emp_data').prepend(html);
            });

            $(document).on('click', '.saveHourlyRate', function () {
                $.post(baseUrl + 'payroll/ajax_change_rate', {
                    rate: $(this).parent().find('.empRate').val(),
                    worked_id: $(this).data('worked_id')
                }, function (resp) {
                    _reload_workspace();
                }, 'json');
                return false;
            });

            $(document).on('input', '.payroll_model_edit_input', function () {
                const btn = $(this).parent().parent().find('.saveForm').find('.payroll_model_edit_table_save');
                if (btn[0].hasAttribute('disabled')) {
                    btn.removeAttr('disabled');
                    btn.removeClass('btn-default, disabled');
                    btn.addClass('btn-success');
                    btn.css({'background-color': '#8ec165', 'border-color': '#8ec165'})
                }
            });

            $(document).on('click', '.saveDeduction', function () {
                $.post(baseUrl + 'payroll/ajax_change_deductions', {
                    deduction_amount: $(this).parent().find('.deductionsSum').val(),
                    employee_id: $(this).data('employee_id'),
                    payroll_id: $(this).data('payroll_id'),
                    deduction_id: $(this).data('deduction_id')
                }, function (resp) {
                    _reload_workspace();
                }, 'json');
            });
        });

        function init_editable_expense() {
            $('.editable-expense').editable({
                format: 'hh:mm a',
                viewformat: 'hh:mm a',
                template: "hh : mm a",
                escape: true,
                combodate: {
                    minYear: 2010,
                    maxYear: 2030,
                },
                ajaxOptions: {
                    dataType: 'json'
                },
                params: function(params) {
                    var data = {};
                    data['pk'] = params.pk;
                    data['value'] = params.value;
                    data['name'] = params.name;
                    if (this.dataset.worked_id) {
                        data['worked_id'] = this.dataset.worked_id;
                    }
                    if (this.dataset.rate) {
                        data['rate'] = params.value;
                    }
                    if (this.dataset.lunch) {
                        data['lunch'] = params.value;
                    }
                    return data;
                },
                success: function (response, newValue) {
                    if (response.status == 'ok') {
                        return {newValue: response.value}
                    }
                },
                error: function (errors, text) {
                    return errors.responseJSON.message;
                },
                display: function (value) {
                    if (this.dataset.time) {
                        $(this).text(value + ' hrs');
                        return;
                    }
                    $(this).text(Common.money(value));
                    return;
                }
            }).on('shown', function() {
                $(document).find('.glyphicon-ok').hide();
                $(document).find('.editable-submit').text('SAVE');
                $(document).find('.editable-input').parent().addClass('payroll_model_edit_flex');
                $(document).find('.editable-cancel').hide();
            });
        }

        function _after_save(resp) {
            $('#change_' + resp.worked_date).find('.totalWorked').text(resp.worked_total);
            $('#change_' + resp.worked_date).find('[data-dismiss="modal"]').attr('onclick', '_reload_workspace();$(this).attr("onclick", "");');
            if (resp.login_diff)
                $('tr[data-login_id=' + resp.login_id + ']').find('.timeDiff').text(resp.login_diff);
            else
                $('tr[data-login_id=' + resp.login_id + ']').find('.timeDiff').text('-');

            if (resp.error)
                errorMessage(resp.error)
            else if (resp.success)
                successMessage(resp.success)

            return true;
        }

        function _after_insert(resp) {
            if (resp.error)
                errorMessage(resp.error)
            else if (resp.success)
                successMessage(resp.success)

            $('#change_' + resp.worked_date).find('.totalWorked').text(resp.worked_total);
            $('#change_' + resp.worked_date).find('[data-dismiss="modal"]').attr('onclick', '_reload_workspace();$(this).attr("onclick", "");');
            $('#change_' + resp.worked_date).find('tr[data-login_id=""]').attr('data-login_id', resp.login_id);

            if (resp.login_diff)
                $('tr[data-login_id=' + resp.login_id + ']').find('.timeDiff').text(resp.login_diff);
            else
                $('tr[data-login_id=' + resp.login_id + ']').find('.timeDiff').text('-');

            var login = $('tr[data-login_id=' + resp.login_id + ']').find('.saveForm').find('[name="login"]').val();
            var logout = $('tr[data-login_id=' + resp.login_id + ']').find('.saveForm').find('[name="logout"]').val();
            $('tr[data-login_id=' + resp.login_id + ']').find('.saveForm').remove();
            $('tr[data-login_id=' + resp.login_id + ']').find('.cancelInsert').remove();
            $('tr[data-login_id=' + resp.login_id + ']').find('td:last').append('<form data-type="ajax" data-url="' + baseUrl + 'payroll/ajax_save_time" data-callback="_after_save" style="display: inline-block;" class="saveForm"><input type="hidden" value="' + resp.login_id + '" name="login_id"><input type="hidden" value="' + login + '" name="login" class="loginTime"><input type="hidden" value="' + logout + '" name="logout" class="logoutTime"><button type="submit" class="btn btn-xs btn-default disabled payroll_model_edit_table_save" disabled="disabled">SAVE</button></button></form>');
            $('tr[data-login_id=' + resp.login_id + ']').find('td:last').append("\n");
            $('tr[data-login_id=' + resp.login_id + ']').find('td:last').append('<form data-type="ajax" data-url="' + baseUrl + 'payroll/ajax_delete_time" data-callback="_after_delete" style="display: inline-block;"><input type="hidden" value="' + resp.login_id + '" name="login_id"><input type="hidden" value="<?php echo (isset($employee) && !empty($employee)) ? $employee->emp_user_id : ''; ?>" name="user_id"><input type="hidden" value="' + resp.login_date + '" name="login_date"><button type="submit" class="btn btn-xs bg-danger"><i class="fa fa-trash-o"></i></button></form>');
            return true;
        }

        function _after_delete(resp) {
            $('tr[data-login_id=' + resp.login_id + ']').parents('.emp_data:first').find('.totalWorked').text(resp.worked_total);
            $('tr[data-login_id=' + resp.login_id + ']').remove();
            $('#change_' + resp.worked_date).find('[data-dismiss="modal"]').attr('onclick', '_reload_workspace();$(this).attr("onclick", "");');
            return true;
        }

        function _reload_workspace() {
            location.reload();
        }

        let start = moment().subtract(6, 'days'),
            end = moment();

        if (!start)
            start = moment().startOf('month');

        if (!end)
            end = moment().endOf('month');

        $(document).on('change', '#payrollrange', function (start, end) {
            cb(start, end);
        });

        function cb(start, end, label) {
            if (typeof start == 'string') {
                start = moment(start);
            }

            if (typeof end == 'string') {
                end = moment(end);
            }

            let employee_id = $('.b-b.b-light.active').children('a').attr('data-emp');

            $('#payrollrange span').html(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));

            location.href = '/employees/payroll/' + employee_id + '/range/' + start.format('YYYY-MM-DD') + '/' + end.format('YYYY-MM-DD');
            return true;

        }

        $('#payrollrange').daterangepicker({
            startDate: moment().startOf('isoWeek'),
            endDate: moment().endOf('isoWeek'),
            ranges: {
                'Current week': [moment().startOf('isoWeek'), moment()],
                'Last Week': [moment().subtract(1, 'week').startOf('isoWeek'), moment().subtract(1, 'week').endOf('isoWeek')],
                'Last 2 Weeks': [moment().subtract(2, 'week').startOf('isoWeek'), moment().subtract(1, 'week').endOf('isoWeek')],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
    </script>


</section>

<?php
/*<!--
<section class="1scrollable" style="top: 45px; bottom: 10px; left: 15px; width: 250px;">
	<section class="hbox stretch">
		<aside class="aside-lg bg-white b-r">
			<section class="vbox">
				<section class="scrollable">
					<div class="wrapper">
						<ul class="nav">
							<?php $status = NULL; ?>
							<?php foreach ($employees as $key => $val) : ?>
								<?php if($status != $val['emp_status']) : ?>
									<?php $status = $val['emp_status']; ?>
									<div class="wrapper b-b header font-bold text-center"><?php echo ucfirst($status); ?></div>
								<?php endif; ?>
								<li class="b-b b-light">
									<a href="<?php echo base_url('employees/payroll/' . $val['employee_id']); ?>">
										<i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>
										<?php echo $val['emp_name']; ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</section>
			</section>
		</aside>

	</section>
</section>*/
?>
<style>
    .bg-info .nav > li > a:hover,
    .bg-info .nav > li > a:focus {
        color: #000;
        background-color: #ededed;
    }

    ul.nav > li.active > a {
        background-color: rgba(0, 0, 0, 0.05) !important;
        text-shadow: none;
    }

    .panel .table td, .panel .table th {
        padding: 3px 10px;
        border-top: 1px solid #f1f1f1;
    }

    .alert-message {
        z-index: 10001
    }
</style>
<?php if (isset($payroll)) : ?>
    <?php foreach ($payroll['weeks'] as $key => $week) : ?>
        <?php for ($i = strtotime($payroll['payroll_start_date']); $i <= strtotime($payroll['payroll_end_date']); $i += 86400) : ?>
            <?php $key = date('Y-m-d', $i + 3600); ?>
            <?php if (!isset($pdf)) : ?>
                <!--			--><?php //$this->load->view('payroll_change_employee_time', array('time' => $key, 'logins' => isset($worked_data[$key]->mdl_emp_login) ? $worked_data[$key]->mdl_emp_login : array())); ?>
            <?php endif; ?>
        <?php endfor; ?>
    <?php endforeach; ?>
<?php endif; ?>
<?php $this->load->view('includes/footer'); ?>
