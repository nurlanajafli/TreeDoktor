<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/clients/appointment/schedule_appointment_modal_content.css?v=1.00'); ?>" type="text/css" />

<div class="row pos-rlt">

    <div class="col-md-4 col-xs-12 appointment-estimators-block" style="padding: 0 10px">
        <div class="row">
            <div class="col-md-12">
            <div class="modal-header row">
                <h4 class="modal-title col-lg-5 hidden-md hidden-xs hidden-sm modal-title-schedule">Schedule Appointment</h4>
                <div class="col-lg-4 col-md-8 col-sm-8 col-xs-8 schedule-appointment-estimator">
                    <select class="form-control" id="appointment-estimator">
                        <option value="0">All estimators</option></select>
                    <span class="text-danger" id="appointment-estimator-error" style="position: absolute; left: 17px; bottom: -16px;"></span>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-4 schedule-appointment-datepicker">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <input type="text" style="max-width: 60px" class="datepicker-input form-control datepicker text-center" readonly placeholder="dd:mm:YY"
                           id="scheduled-datepicker"
                           data-date="<?php echo (isset($scheduled_date) && $scheduled_date) ? getDateTimeWithDate($scheduled_date, 'Y-m-d', false, false, true, true) : date(getDateFormatWithOutYear()); ?>"
                           value="<?php echo (isset($scheduled_date) && $scheduled_date) ? getDateTimeWithDate($scheduled_date, 'Y-m-d', false, false, true, true) : date(getDateFormatWithOutYear()); ?>"/>

                </div>
            </div>
            </div>
        </div>
        <div class="row">
            <!--<div class="col-md-12">
                <input type="text" class="datepicker-input form-control" placeholder="dd:mm:YY" id="scheduled-datepicker" data-date="
                <?php /*//echo (isset($scheduled_date) && $scheduled_date)?$scheduled_date:date("d-m-Y"); */?>" value="
                <?php /*//echo (isset($scheduled_date) && $scheduled_date)?$scheduled_date:date("d-m-Y"); */?>" />
            </div>-->
            <div class="col-md-12 p-n">
                <!--<span class="text-success">Recommendations:</span>-->
                <div class="appointment-recomendations">
                    <?php $this->load->view('appointment/recomendations'); ?>
                </div>
                <span class="text-danger appointment-error"></span>
            </div>
        </div>
        <div class="row">
            <div class="modal-footer m-n">
                <div class="row m-bottom-10">
                    <?php if(config_item('messenger')) : ?>
                    <div class="col-md-6 col-lg-6 col-xs-6">
                        <div class="checkbox text-left m-n">
                            <label class="checkbox-custom">
                                <input type="checkbox" class="notification-checkbox" name="notify_client_sms">
                                <i class="fa fa-fw fa-square-o checked"></i>
                                Client SMS
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6 col-lg-6 col-xs-6">
                        <div class="checkbox text-left m-n">
                            <label class="checkbox-custom">
                                <input type="checkbox" class="notification-checkbox" name="notify_client_email">
                                <i class="fa fa-fw fa-square-o checked"></i>
                                Client Email
                            </label>
                        </div>
                    </div>
                    <?php if(config_item('messenger')) : ?>
                    <div class="col-md-6 col-lg-6 col-xs-6">
                        <div class="checkbox text-left m-n">
                            <label class="checkbox-custom">
                                <input type="checkbox" class="notification-checkbox" name="notify_estimator_sms">
                                <i class="fa fa-fw fa-square-o checked"></i>
                                Estimator SMS
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6 col-lg-6 col-xs-6">
                        <div class="checkbox text-left m-n">
                            <label class="checkbox-custom">
                                <input type="checkbox" class="notification-checkbox" name="notify_estimator_email">
                                <i class="fa fa-fw fa-square-o checked"></i>
                                Estimator Email
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-lg-4 col-xs-4">
                        <select class="form-control pull-left" id="appointment-type">
                            <option value="">Choose a lead</option></select>
                        <span class="text-danger" id="appointment-type-error" style="position: absolute; left: 17px; bottom: -16px;"></span>
                    </div>

                    <div class="col-md-8 col-lg-8 col-xs-8 text-right p-left-0">
                        <div class="inline pull-left" style="max-width: 40%;">
                            <select class="form-control pull-left" id="appointment-lead">
                                <option >Choose a lead</option>
                            </select>
                            <span class="text-danger" id="appointment-lead-error" style="position: absolute; left: 17px; bottom: -16px;"></span>
                        </div>
                        <div class="inline">
                            <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
                            <a href="#" id="add-appointment" class="btn btn-primary">Save</a>
                            <a href="#" id="add-appointment-task" class="btn btn-primary" style="display:none;">Save</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-8 hidden-xs hidden-sm appointment-map-block" style="position: unset;">
        <div id="appointments-map" data-lat="<?php echo $appointment_lat; ?>" data-lon="<?php echo $appointment_lon; ?>"
             style="position: absolute; overflow: hidden; right: -5px; top: -2px; bottom: -1px; left: 33.33%;"
             data-origin_lat="<?php echo (isset($appointment_lat)) ? $appointment_lat : $origin_lat; ?>"
             data-origin_lon="<?php echo (isset($appointment_lon)) ? $appointment_lon : $origin_lon; ?>"></div>
        <ul id="appointments-estimators-list" class="list-group no-radius m-b-none pos-abt" style="max-height: 265px; overflow: auto; right: -5px; top: 5px; width: 320px;"></ul>
    </div>


    <input type="hidden" name="task_author_radio">
    <input type="hidden" name="selected_estimator_name">
</div>

<div class="row">
    <div class="col-md-12">
        <div class="free-schedule-times">
            <?php $this->load->view('appointment/schedule_intervals'); ?>
        </div>
    </div>
</div>
<input type="hidden" id="php-variable" value="<?php echo getJSDateFormat() ?>"/>
<input type="hidden" id="date-format-without-year" value="<?php echo getJSDateFormatWithOutYear() ?>"/>

<script type="text/javascript">
    window.office_position = {lat:<?php echo config_item('office_lat'); ?>, lon:<?php echo config_item('office_lon'); ?>};
    window.appointments = <?php echo json_encode($schedule_appointments); ?>;
</script>

<script src="<?php echo base_url('assets/js/modules/clients/appointment/schedule_appointment_modal_content.js?v=1.00'); ?>"></script>
