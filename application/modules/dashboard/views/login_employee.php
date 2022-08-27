<?php

use application\modules\equipment\models\EquipmentRepair;

?>
<script>
    var latitude, longitude;
    var repairPriorities = <?php echo json_encode(EquipmentRepair::PRIORITIES); ?>;
</script>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/kartik-v/fileinput/css/fileinput.css'); ?>"
      type="text/css"/>
<style>
    .select2-container {
        width: 100%;
    }
</style>
<input type="hidden" id="empid" value="<?php echo $emp_id ?>"/>
<input type="hidden" id="empname" value="<?php echo $user_name ?>"/>
<div class="login-overlay animated fadeInRight bg-light">
    <div id="loginData" class="row m-l-none m-r-none m-t bg-light" diez-app="DashboardLoginApp"
         diez-src="dashboard/components/login.js">
        <section class="col-lg-3 col-md-5 col-sm-12 col-xs-12">
            <div style="position: absolute;left: 7px;top: -8px;z-index: 1;">
                <a class="badge bg-danger closeLogin" href="#" style="padding: 3px 6px; text-decoration: none;">x</a>
            </div>
            <section class="panel-default">
                <!-- Logged hours -->
                <section class="col-md-12 panel panel-default p-n">
                    <header class="panel-heading">Today:</header>
                    <div class="p-10">
                        <strong>Login:</strong><span id="logintime"><?php echo $login_time; ?></span></br>
                        <strong>Logout:</strong><span id="logouttime"><?php echo $logout_time; ?></span></br>
                        <strong>Total:</strong><span id="timediff"><?php echo $time_diff??'-'; ?></span>
                    </div>
                </section>

                <?php $timer_class = "start"; ?>
                <?php $button_caption = "Start"; ?>
                <?php $timer_running = "none"; ?>
                <?php if ($login == true && $logout == false) { ?>
                    <?php $timer_class = "running"; ?>
                    <?php $timer_running = "display";
                    $class = 'filled_green'; ?>
                    <?php $button_caption = "Stop"; ?>
                <?php } ?>
                <input type="hidden" name="login_rec_id" data-new_table_record="<?php echo $new_record; ?>" id="login_rec_id" value="<?php echo $login_rec_id ?>"/>
                <section>
                    <section class="col-md-6 col-xs-6 panel filled_blue panel-default p-n timer" id="show_timer"
                             style='display:none;'>

                        <!-- entry button -->
                        <div class="start" id="entry-button" href="#" style="<?php if ($timer_class == 'running') {
                            echo 'display:none;';
                        } ?>" <?php if ($timer_class == 'running') {
                            echo 'disabled="disabled"';
                        } ?> onclick="toggletimer('start');"></div>

                    </section>
                    <!--  /Start -->
                    <section class="col-md-6 col-xs-6 panel filled_green panel-default p-n timer" id="show_timer1"
                             style='display:none;'>
                        <!-- <input type="hidden" name="login_rec_id" id="login_rec_id" value="<?php echo $login_rec_id ?>" />-->
                        <!-- entry button -->
                        <div class="running" id="running-button" onclick="toggletimer('stop');" href="#" data-toggle="modal"
                             style=" <?php if ($timer_class == 'start') {
                                 echo 'display:none;';
                             } ?>" <?php if ($timer_class == 'start') {
                            echo 'disabled="disabled"';
                        } ?>></div>
                    </section>
                </section>


            </section>
        </section>


        <section class="col-lg-9 col-md-7 col-sm-12 col-xs-12">
            <section class="panel panel-default">

                <!-- Tracker header header -->
                <header class="panel-heading"><span id="username"></span> - Dashboard</header>

                <div class="m-10">
                    <div class="form-inline">
                        <form name="frmgetmonth" id="frmgetmonth" method="POST" action="">
                            <!--<label for="dpMonths"></label>-->

                            <!-- SELECT Month and Year -->
                            <div class="input-group date col-md-4 col-sm-6 col-xs-6 pull-left" id="dpMonths" data-date="<?php echo $cdate; ?>"
                                 data-date-format="mm/yyyy" data-date-viewmode="years" data-date-minviewmode="months">
                                <input class="picker form-control" size="16" type="text" value="<?php echo $cdate; ?>"
                                       readonly="" name="monthyear" id="monthyear">
                                <span class="input-group-addon add-on"><i class="fa fa-calendar"></i></span>
                            </div>
                            <button type="button" id="btngetreport" class="btn pull-left"
                                    style="margin-top: 0px; margin-left: 10px;">
                                <i class="fa fa-book"></i>&nbsp;Get Report
                            </button>
                            <div class="clear"></div>
                        </form>
                    </div>
                </div>
                <div id="monthreport"></div>

            </section>
        </section>
        <div id="repairCreate" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" class="form-horizontal">
                    <div class="modal-content panel panel-default p-n">
                        <header class="panel-heading">
                            <span>Create Repair Request</span>
                            <button type="button" class="close" data-dismiss="modal">×</button>
                        </header>
                        <div class="modal-footer">
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                            <button class="btn btn-info" type="submit" style="30px"><span
                                        class="btntext">Save</span></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<!------Report-------------->
<div id="reportModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="eventReport" class="form-horizontal" method="POST">
                <div class="modal-body">
                    <?php if(isset($events_withouth_report) && !empty($events_withouth_report)) : ?>
                        <?php foreach($events_withouth_report as $key => $event) : ?>

                            <div class="event eventDataReport-<?php echo $event['id'];?>">
                                <div class="form-group m-b-none">
                                    <label class="col-sm-4 control-label">Workorder:</label>
                                    <div class="col-sm-7">
                                        <p class="form-control-static">
                                            <strong>
                                                <?php echo $event['workorder_no']; ?> -
                                                <?php echo $event['lead_address']; ?>
                                            </strong>
                                        </p>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                    <input type="hidden" name="workorder" value="<?php echo $event['workorder_no'] . '-' . $event['lead_address']; ?>">
                                </div>
                                <div class="form-group report-field">
                                    <label class="col-sm-4 control-label">Travel</label>
                                    <div class="col-sm-4">
                                        <div>Start:</div>
                                        <input type="time" name="travel_start[<?php echo $event['id'];?>]" class="form-control p-n">
                                    </div>
                                    <div class="col-sm-4">

                                    </div>
                                </div>
                                <div class="form-group m-b-none report-field">
                                    <label class="col-sm-4 control-label">Work:</label>

                                    <div class="col-sm-4">
                                        <div>Start:</div>
                                        <input type="time" name="event_start[<?php echo $event['id'];?>]" class="form-control p-n">
                                    </div>
                                    <div class="col-sm-4">
                                        <div>Finish:</div>
                                        <input type="time" name="event_finish[<?php echo $event['id'];?>]" class="form-control p-n">
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>
                                <div class="form-group m-b-none report-field">
                                    <label class="col-sm-4 control-label">Finished:</label>
                                    <div class="col-sm-7">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" class="status" name="status[<?php echo $event['id'];?>]" value="finished">
                                                Yes
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" class="status" name="status[<?php echo $event['id'];?>]" value="unfinished">
                                                No
                                            </label>
                                        </div>
                                        <input type="hidden" name="wo_id[<?php echo $event['id'];?>]" value="<?php echo $emp_events[$key]['event_wo_id']; ?>">
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>
                                <div class="form-group m-b-none payment report-field" style="display:none;">
                                    <label class="col-sm-4 control-label">Payment:</label>
                                    <div class="col-sm-7">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" class="payment" name="payment[<?php echo $event['id'];?>]" value="yes">
                                                Yes
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" class="payment" name="payment[<?php echo $event['id'];?>]" value="no">
                                                No
                                            </label>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>
                                <div class="form-group m-b-none paymentSum report-field" style="display:none;">
                                    <label class="col-sm-4 control-label">Payment Type:</label>
                                    <div class="col-sm-7">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" class="payment_type" name="payment_type[<?php echo $event['id'];?>]" value="Cash">
                                                Cash
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" class="payment_type" name="payment_type[<?php echo $event['id'];?>]" value="Check">
                                                Check
                                            </label>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>
                                <div class="form-group m-b-none paymentSum report-field" style="display:none;">
                                    <label class="col-sm-4 control-label">Payment Amount:</label>
                                    <div class="col-sm-7">
                                        <input type="text" disabled class="finished form-control" name="payment_amount[<?php echo $event['id'];?>]">
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>

                                <div class="form-group m-b-none finishDescription report-field" style="display:none;">
                                    <label class="col-sm-4 control-label">Work Remaining:</label>
                                    <div class="col-sm-7">
                                        <textarea class="unfinished form-control" disabled name="work_description[<?php echo $event['id'];?>]" ></textarea>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>

                                <div class="form-group m-b-none report-field">
                                    <label class="col-sm-4 control-label">Damage:</label>
                                    <div class="col-sm-7">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" class="damage" name="damage[<?php echo $event['id'];?>]" value="yes">
                                                Yes
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" class="damage" name="damage[<?php echo $event['id'];?>]" value="no">
                                                No
                                            </label>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>
                                <div class="form-group m-b-none dmgDescription report-field" style="display:none;">
                                    <label class="col-sm-4 control-label">Damage Description:</label>
                                    <div class="col-sm-7">
                                        <textarea class="form-control dmgText" disabled name="demage_description[<?php echo $event['id'];?>]" ></textarea>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>
                                <div class="form-group m-b-none report-field" >
                                    <label class="col-sm-4 control-label">Event Description:</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control eventText" name="event_description[<?php echo $event['id'];?>]" ></textarea>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="line line-dashed line-lg"></div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if(isset($team_id)) : ?>
                        <div class="teamDataReport-<?php echo $team_id; ?>">
                            <div class="form-group m-b-none report-field">
                                <label class="col-sm-4 control-label">Expenses:</label>
                                <div class="col-sm-7">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" class="expenses" name="expenses" value="yes">
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" class="expenses" name="expenses" value="no">
                                            No
                                        </label>
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <div class="line line-dashed line-lg"></div>
                            </div>
                            <div class="form-group m-b-none expensesDesc report-field" style="display:none;">
                                <label class="col-sm-4 control-label">Expenses Description:</label>
                                <div class="col-sm-7">
                                    <textarea class="form-control expensesText" disabled name="expenses_description" ></textarea>
                                </div>
                                <div class="clear"></div>
                                <div class="line line-dashed line-lg"></div>
                            </div>
                            <div class="form-group m-b-none report-field">
                                <label class="col-sm-4 control-label">Malfunctions Equipment:</label>
                                <div class="col-sm-7">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" class="fail" name="malfunctions_equipment" value="yes">
                                            Yes
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" class="fail" name="malfunctions_equipment" value="no">
                                            No
                                        </label>
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <div class="line line-dashed line-lg"></div>
                            </div>
                            <div class="form-group m-b-none failDesc report-field" style="display:none;">
                                <label class="col-sm-4 control-label">Malfunctions Description:</label>
                                <div class="col-sm-7">
                                    <textarea class="form-control failText" disabled name="malfunctions_description" ></textarea>
                                </div>
                                <div class="clear"></div>
                                <div class="line line-dashed line-lg"></div>
                            </div>
                            <?php if(isset($team_members) && !empty($team_members)) : ?>
                                <?php foreach($team_members as $key => $member) : ?>
                                    <div class="form-group m-b-none">
                                        <div class="">
                                            <label class="col-sm-4 control-label"><strong><?php echo $member['emp_name']; ?>:</strong></label>
                                            <label class="col-sm-2 control-label" style="text-align: left;">Finished Time:</label>
                                            <div class="col-sm-3">
                                                <input type="time" class="form-control"
                                                       name="logout_time[<?php echo $member['employee_id']; ?>]"
                                                       value="<?php echo $member['employee_logout']; ?>">
                                            </div>
                                            <div class="col-sm-3">
                                                <a class="btn btn-default btn-block" data-toggle="collapse" data-parent="#accordion2" href="#collapse<?php echo $member['employee_id']; ?>">Expenses</a>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                        <div class="panel-group m-b" id="accordion2">
                                            <div id="collapse<?php echo $member['employee_id']; ?>" class="panel-collapse collapse" style="height: auto;">
                                                <div class="panel-body text-sm">
                                                    <div class="row m-bottom-5">
                                                        <div class="col-md-4">&nbsp;</div>
                                                        <div class="col-sm-2">&nbsp;</div>
                                                        <div class="col-md-3">
                                                            <input type='number' class='form-control' placeholder='B.L.D' name='bld[<?php echo $member['employee_id']; ?>]' value="<?php echo floatval($member['ter_bld']); ?>">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type='number' class='form-control' placeholder='Extra' name='extra[<?php echo $member['employee_id']; ?>]' value="<?php echo floatval($member['ter_extra']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">&nbsp;</div>
                                                        <div class="col-sm-2">&nbsp;</div>
                                                        <div class="col-md-6">
                                                            <textarea class='form-control' placeholder='Comment' name='extra_comment[<?php echo $member['employee_id']; ?>]'><?php echo $member['ter_extra_comment']; ?></textarea>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if($key + 1 != count($team_members)) : //countOk ?>
                                            <div class="clear"></div>
                                            <div class="line line-dashed line-lg"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($team_id)) : ?>
                        <input type="hidden" name="team_id" value="<?php echo $team_id; ?>">
                    <?php endif;?>

                </div>
                <div class="modal-footer">
                    <input type="submit" name="submit" value="Preview" class="btn btn-info" id="submit">
                </div>
            </form>
        </div>
    </div>
</div>

<!------End Report-------------->

<!-- /Time tracker -->
<div id="startTimeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h5 class="p-bottom-20">Time Information</h5>
            </div>
            <div class="p-bottom-20 " id="time_str" style=" margin-left: 25px; margin-top: -21px;">
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true" id='empSignout'>Sign Out</button>
            </div>
        </div>
    </div>
</div>

<!--Estimator Report Modal-->
<div id="estReportModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Estimator Report</header>
            <div class="modal-body">
                <form id="estimateReport" class="form-horizontal" method="POST">


                    <input type="hidden" name="log_id" value="<?php echo $login_rec_id; ?>">

                    <div class="form-group m-b-none">
                        <label class="col-sm-4 control-label">Comment: </label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="comment" ><?php echo isset($est_report) ? $est_report : ''; ?></textarea>
                        </div>
                        <div class="clear"></div>
                        <div class="line line-dashed line-lg"></div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true" >Close</button>
                <button class="btn btn-success" data-dismiss="modal" aria-hidden="true" id='saveEstReport'>Confirm</button>
            </div>
        </div>
    </div>
</div>
<!--End Estimator Report Modal-->

<!--Confirm Modal-->
<div id="resultReportModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Report Preview</header>
            <div class="modal-body">
                <div id="resultData"></div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true" id='cancelReport'>Edit</button>
                <button class="btn btn-success" data-dismiss="modal" aria-hidden="true" id='saveReport' onclick="saveReport(id)">Confirm</button>
            </div>
        </div>
    </div>
</div>
<!--End Confirm Modal-->
<?php $this->load->view_hb('equipment/repair_create'); ?>
<?php //$this->load->view('equipments/repairs/add_repair_form'); ?>
<script>
    window.glat = false;
    window.glng = false;
    navigator.geolocation.getCurrentPosition(function(position) {
        // Get the coordinates of the current possition.
        window.glat = position.coords.latitude;
        window.glng = position.coords.longitude;

    }, function (error) {
        console.info("Position can't be checked");
    });
    $('#show_timer1').hide();
    $('#show_timer').hide();
    //var show_web_cam = '<?php echo @$show_web_cam ?>';
    var estimator = <?php echo isset($estimator) && !empty($estimator) && $estimator['emp_field_estimator'] == true ? json_encode($estimator) : '[]'; ?>;
    var teams = <?php echo isset($teams) && !empty($teams) ? json_encode($teams) : '[]'; ?>;
    var events_withouth_report = <?php echo isset($events_withouth_report) && count($events_withouth_report) ? json_encode($events_withouth_report) : '[]'; ?>;
    var exceptionIds = <?php echo json_encode([31, 44, 146, 163, 192, 193]); ?>;
    var myIp = '<?php echo $this->session->userdata('ip_address'); ?>';
    var officeIp = '64.40.241.43';
    //var _BASE_URL = '<?php echo base_url(); ?>';
    //var _DISABLE_TIMER_BUTTON = '<?php echo _DISABLE_TIMER_BUTTON; ?>';
    function show_employee_dashboard() {
        sloading();
        $.post(baseUrl + "dashboard/getdatabymonth", function (resp) {
            $("#username").html($("#empname").val());
            if(resp.status == 'ok') {
                $('#monthreport').html(resp.html);
                $("#toploading").remove();
            }
        }, 'json');
        return false;
    }

    function logout() {
        sloading();
        $.ajax({
            url: baseUrl + 'login/logout',
            data: '',
            type: 'post',
            dataType: 'text',
            success: function (res) {
                alert("Logged out successfully");
                location.reload();
                //$("#container").html('');
                $(".datepicker").hide();
                location.href = baseUrl + 'login';//call_login(); CHANGED 30.01.2015 BY GLEBA RUSLAN
                $("#toploading").remove();
            },
            error: function (err) {
                alert("Error in logout: " + err.responseText);
                $("#toploading").remove();
            }
        });
    }

    function get_monthly_report() {
        sloading();
        $.post(baseUrl + "dashboard/getdatabymonth", {monthyear : $("#monthyear").val()},  function (resp) {
            //console.log(resp); return false;
            if(resp.status == 'ok') {
                $("#monthreport").html(resp.html);
                $("#toploading").remove();
                $('#show_timer').show();
                $('#show_timer1').show();
            } else {
                alert("Error in logout: " + err.responseText);
                $("#toploading").remove();
            }
        }, 'json');
        return false;
    }

    function sloading() {
        var loading = $("<div></div>");
        $(loading).attr("id", "toploading");
        $(loading).css("position", "absolute");
        $(loading).css("border", "4px ridge #DADADA");
        $(loading).css("border-radius", "5px");
        $(loading).css("background-color", "#D1F0FF");
        $(loading).css("width", "200px");
        $(loading).css("padding", "15px");
        $(loading).css("top", "30%");
        $(loading).css("left", "40%");
        $(loading).css("text-align", "center");
        $(loading).css("color", "brown");
        $(loading).css("font-weight", "bold");
        $(loading).html("<img src='" + baseUrl + "assets/img/loading4.gif'/>");
        $("body").append($(loading));
    }

    function toggletimer(action, send_report) {

        var a = action;
        if(a == 'start') {
            if($('#entry-button').attr('disabled') != 'disabled') {
                var that = $('#entry-button');
                $('#entry-button').attr("disabled", "disabled");
                $('#entry-button').hide();
                $('#running-button').show();
                timer(a);
            }
        }

        if ($('#running-button').attr('disabled') != 'disabled')
            $("#entry-button").hide();
        else
            $("#running-button").hide();
        if(a == 'stop') {
            if($(teams).length && send_report == undefined) {
                $('#reportModal').modal().show();
                if($(events_withouth_report).length == 0) {
                    $('#reportModal .report-field').remove();
                    return false;
                }
                return false;
            }

            if($(estimator).length) {
                $('#estReportModal').modal().show();
                return false;
            }
            if ($('#running-button').attr('disabled') != 'disabled') {
                timer(a);
                var that = $('#running-button');
            }
        }
    }

    function timer(status) {

        $("#btnsubmit").hide();
        sloading();

        if(exceptionIds.indexOf(user_id) == -1 && officeIp != myIp)
            var loc = check_location(status);
        else
            sendStartStop(status);


        return false;

    }

    function sendStartStop(status, loc) {

        if(window.glat && window.glng) {
            lat = window.glat;
            lng = window.glng;
            office = true;
        } else {
            lat = lng = office = false;
        }
        office = true;
        $.post(baseUrl + "dashboard/timer", {timer:status, login_rec_id:$("#login_rec_id").val(), ltt:lat, lng:lng, office:office, lat:latitude, lon:longitude, new_rec_id:$("#login_rec_id").attr('data-new_table_record')},  function (resp) {
            $("#username").html($("#empname").val());
            if(resp.status == 'ok') {
                if(status == "start") {
                    if (resp.rec_id)
                        $("#login_rec_id").val(resp.rec_id);
                    if (resp.new_rec_id)
                        $("#login_rec_id").attr('data-new_table_record', resp.new_rec_id);

                    $("#logouttime").val('00:00');
                    $("#timediff").val('00:00');

                    $("#entry-button").find("i").show();
                    $("#logintime").html(resp.login_time);
                    $('#running-button').removeAttr("disabled");
                    $('#running-button').show();
                    $('#entry-button').hide();
                    $('#entry-button').attr("disabled", true);
                    $('#entry-button').hide();
                    $('#running-button').show();
                    /* to photo need uncomment
                     * var dataUrl = takepicture();
                    uploadImage(dataUrl, response.rec_id, 'login', response.new_rec_id);
                    */
                } else {
                    var login_rec_id = $("#login_rec_id").val();
                    var new_rec_id = $("#login_rec_id").attr('data-new_table_record');
                    $("#login_rec_id").val('');

                    $("#entry-button").find("i").hide();
                    $("#logouttime").html(resp.logout_time);
                    $("#timediff").html(resp.time_diff);
                    $('#time_str').html(resp.time_str);
                    $('#running-button').attr("disabled", true);
                    $('#entry-button').show();
                    $('#running-button').hide();

                    $('#entry-button').removeAttr("disabled");
                    $('#entry-button').show();
                    $('#running-button').hide();
                    $('#running-button').attr("disabled", true);
                    $('#entry-button').show();
                    $('#running-button').hide();

                    /* to photo need uncomment
                    var dataUrl = takepicture();
                    uploadImage(dataUrl, login_rec_id, 'logout', new_rec_id);
                    */
                }

                $("#toploading").remove();
                get_monthly_report();
            } else {
                addError("Error in "+ status +" timer. Please try again.");
                $("#btnsubmit").show();
            }

            $("#toploading").remove();
        }, 'json');
        return false;
    }

    function check_location(status) {
        var loc = [];
        loc['not_office'] = true;
        sendStartStop(status, loc);
    }

    $("document").ready(function () {
        $("#username").html($("#empname").val());
        $('#dpMonths').datepicker();
        $('#drop1').on("click", function () {
            if ($(this).next().attr("aria-labelledby") == "drop1") {
                $(this).next().show();
            }
        });

        $('.status').change(function(){
            if($(this).val() == 'finished') {
                $(this).parents('.event:first').find('.payment').slideDown();
                $(this).parents('.event:first').find('.timeToFinish').slideUp();
                $(this).parents('.event:first').find('.finishDescription').slideUp();
                $(this).parents('.event:first').find('.timeToFinish').find('.unfinished').val('').attr('disabled', 'disabled');
                $(this).parents('.event:first').find('.finishDescription').find('.unfinished').val('').val('').attr('disabled', 'disabled');
            } else {
                $(this).parents('.event:first').find('.payment').slideUp();
                $(this).parents('.event:first').find('.paymentSum').slideUp();
                $(this).parents('.event:first').find('.paymentSum').find('.finished').val('').attr('disabled', 'disabled');
                $(this).parents('.event:first').find('.payment').find('.payment').prop('checked', false);
                $(this).parents('.event:first').find('.timeToFinish').find('.unfinished').removeAttr('disabled');
                $(this).parents('.event:first').find('.timeToFinish').slideDown();
                $(this).parents('.event:first').find('.finishDescription').find('.unfinished').removeAttr('disabled');
                $(this).parents('.event:first').find('.finishDescription').slideDown();
            }
            return false;
        });
        $('.payment').change(function(){
            if($(this).val() == 'yes') {
                $(this).parents('.event:first').find('.paymentSum').slideDown();
                $(this).parents('.event:first').find('.paymentSum').find('.finished').removeAttr('disabled');
            } else {
                $(this).parents('.event:first').find('.paymentSum').slideUp();
                $(this).parents('.event:first').find('.paymentSum').find('.finished').val('').attr('disabled', 'disabled');
            }
            return false;
        });
        $('.damage').change(function(){
            if($(this).val() == 'yes') {
                $(this).parents('.event:first').find('.dmgDescription').slideDown();
                $(this).parents('.event:first').find('.dmgDescription').find('.dmgText').removeAttr('disabled');
            } else {
                $(this).parents('.event:first').find('.dmgDescription').slideUp();
                $(this).parents('.event:first').find('.dmgDescription').find('.dmgText').val('').attr('disabled', 'disabled');
            }
        });
        $('.fail').change(function(){

            if($(this).val() == 'yes') {
                $(this).parents('form').find('.failDesc').slideDown();
                $(this).parents('form').find('.failDesc').find('.failText').removeAttr('disabled');
            } else {
                $(this).parents('form').find('.failDesc').slideUp();
                $(this).parents('form').find('.failDesc').find('.failText').val('').attr('disabled', 'disabled');
            }
        });
        $('.expenses').change(function(){

            if($(this).val() == 'yes') {
                $(this).parents('form').find('.expensesDesc').slideDown();
                $(this).parents('form').find('.expensesDesc').find('.expensesText').removeAttr('disabled');
            } else {
                $(this).parents('form').find('.expensesDesc').slideUp();
                $(this).parents('form').find('.expensesDesc').find('.expensesText').val('').attr('disabled', 'disabled');
            }
        });

        $(document).mouseup(function (e) {
            var container = $(".dropdown");

            if (!container.is(e.target) // if the target of the click isn't the container...
                && container.has(e.target).length === 0) // ... nor a descendant of the container
            {
                //$("#emp_logout").parent().parent().hide();
            }
        });

        //$("#drop1").next().remove();


        $("#btngetreport").click(function () {
            get_monthly_report();
        });
        /*
        $("#emp_logout").on("click", function () {
            logout();
        });
        */
        $("#empSignout").on("click", function () {
            if ($('#running-button').attr('disabled') == 'disabled') {
                logout();
            }

        });

        $("#eventReport").on("submit", function(){
            var formValid = true;
            var startTime = null;
            var finishTime = null;
            $('#eventReport').find('div.form-group').removeClass('has-error');
            var inputs = $('#eventReport').find('textarea:visible, input:visible, select:visible').not('[type="submit"]');
            $.each(inputs, function(key, val){
                var inputName = $(val).attr('name');
                var inputType = $(val).attr('type');
                if(!inputType && !$('#eventReport').find('[name="' + inputName + '"]').val()) {
                    $(val).parents('div.form-group:first').addClass('has-error');
                    formValid = false;
                }
                if(inputType == 'radio' && !$('#eventReport').find('[name="' + inputName + '"]').is(':checked')) {
                    $(val).parents('div.form-group:first').addClass('has-error');
                    formValid = false;
                }
                if(inputType && inputType != 'radio' && !$('#eventReport').find('[name="' + inputName + '"]').val()) {
                    $(val).parents('div.form-group:first').addClass('has-error');
                    formValid = false;
                }
                /*
                if($(val).hasClass('hrs'))
                    var seconds = 3600;
                if($(val).hasClass('min'))
                    var seconds = 60;
                if($(val).hasClass('begin'))
                    startTime += $(val).val() * seconds;
                if($(val).hasClass('finish'))
                    finishTime += $(val).val() * seconds;
                if($(val).hasClass('status'))
                {
                    console.log(startTime, finishTime);
                    if(startTime > finishTime)
                    {
                        $(val).parents('div.form-group:first').prev().addClass('has-error');
                        formValid = false;
                    }
                    finishTime = null;
                    startTime = null;
                }*/


            });
            if(formValid) {
                $('#reportModal').modal().hide();
                var data = $('#eventReport').serializeArray();
                var html = '';
                $.each(data, function(key, value){
                    name = $.trim(value['name'].replace(/[^A-Za-zА-Яа-яЁё]/g, " "));
                    if(name == 'logout time')
                        name = $('#reportModal').find('input[name="' + value['name'] + '"]').parents('.form-group:first').find('label.control-label').html().replace(":", "");
                    if((name == 'workorder' && key) || name == 'malfunctions equipment')
                        html= html + '<br><br>';
                    if(name == 'team id')
                        return false;
                    if(name == 'workorder')
                        value['value'] = '<strong>' + value['value'] + '</strong>';

                    /*
                    if(name == 'event start hours' || name == 'event finish hours' || name == 'travel start hours')
                    {
                        name = name.replace(' hours', '');
                        value['value'] += ':' + data[key + 1]['value'];
                    }*/
                    divider = '';
                    if(data[key + 1] != undefined && data[key + 1]['name'] != 'team_id' && data[key + 1]['name'] != 'workorder' && data[key + 1]['name'] != 'malfunctions_equipment')
                        divider = '<div class="line line-dashed line-lg"></div>';

                    if(/*name != 'event start min' && name != 'event finish min' &&*/ name != 'wo id' /*&& name != 'travel start min'*/) {
                        html = html + '<div class="form-group m-b-none"><label class="col-sm-4 control-label first-letter">' + name + ':</label><div class="col-sm-8 first-letter">' + value['value'] + '</div><div class="clear"></div>' + divider + '</div>';
                    }
                });
                $("#resultData").html(html);
                $('#resultReportModal').find('#saveReport').attr('onclick', 'saveReport("eventReport")');
                $('#resultReportModal').modal().show();
                return false;
            } else {
                $('#reportModal').animate({
                    scrollTop: $('#reportModal').scrollTop() + $('#reportModal').find('.has-error:first').offset().top
                },'slow');
            }
            return false;
        });

        $('#saveEstReport').click(function(){
            var fields = [];
            var fields = $("input[data-required='required']");
            $("#estimateReport input[data-required='required']").removeClass("error");
            var trigger = true;
            $.each(fields, function(key,val){
                if($(val).val() == '' || $(val).val() == '00:00') {
                    $(fields[key]).addClass('error');
                    trigger = false;
                }
            });
            if(trigger) {
                $.post(baseUrl + 'report/ajax_save_report', $('#estimateReport').serialize(), function (resp) {
                    if(resp.status == 'ok') {
                        estimator = [];
                        $('#estReportModal').modal('hide');
                        //$('#running-button').click();
                        toggletimer('stop', true);

                        $('#startTimeModal').addClass('in');
                        $('#startTimeModal').css('display', 'block');

                    }
                    return false;
                }, 'json');
            }
            return false;
        });
        $('#cancelReport').click(function(){
            $('#resultReportModal').modal('hide');
            var id = $('#saveReport').attr('onclick').match(/\("([^\}]*)"\)/);
            if(id[1])
                $('#' + id[1]).parents('.modal').modal().show();
            else
                $('#reportModal').modal().show();
            return false;
        });
        $(document).on('click', '.showLogin', function(){
            show_employee_dashboard();
            $('.login-overlay').show();
            $('#show_timer1').show();
            $('#show_timer').show();
            $('aside.bg-white').css('display', 'none');
            return false;
        });
        $(document).on('click', '.closeLogin', function(){
            $("#toploading").remove();
            $('.login-overlay').hide();
            $('aside.bg-white').css('display', 'block');
            return false;
        });
    });

    function saveReport(form) {
        //var day = $('#' + form).attr('data-close_day');

        $.post(baseUrl + 'dashboard/ajax_save_report', $('#' + form).serialize(), function (resp) {
            if(resp.status == 'ok') {
                events_withouth_report = [];
                if(form != 'eventReport') {
                    $('.' + form).remove();
                }
                //	teams = [];
                $('#resultReportModal').modal('hide');
                if(confirm('Do you have any repair requests?')) {
                    //$('#addRepair').modal('show');
                    DashboardLoginApp.eventRepairCreate();

                } else {

                    if(form == 'eventReport') {
                        teams = [];
                        //$('#running-button').click();
                        toggletimer('stop', true);
                        console.log(form);
                    }
                }
                $('#' + form).parents('.modal').hide();
            }
            return false;
        }, 'json');
        return false;
    }
</script>
