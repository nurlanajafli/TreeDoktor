<?php $this->load->view('includes/header'); ?>
<script async src="<?php echo base_url('assets/js/label.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/wsgps.js?v1.01'); ?>"></script>

<section class="scrollable p-sides-15 p-n mapper" data-origin_lat="<?php echo $center['lat']??''; ?>" data-origin_lon="<?php echo $center['lon']??''; ?>" style="top: 9px;" id="leads-map">
</section>

<div id="map-infowindow" class="hidden"></div>

<script type="text/x-jsrender" id="lead-infowindow-tmp">
<?php $this->load->view('leads_mapper/lead_infowindow'); ?>
</script>

<script type="text/x-jsrender" id="task-infowindow-tmp">
<?php $this->load->view('leads_mapper/task_infowindow'); ?>
</script>

<script type="text/x-jsrender" id="lead-status-reasons-tpl">
    <?php $this->load->view('leads_mapper/form/lead_status_reasons'); ?>
</script>

<script type="text/x-jsrender" id="lead-status-buttons-tpl">
    <?php $this->load->view('leads_mapper/form/lead_status_buttons'); ?>
</script>

<script type="text/x-jsrender" id="task-status-buttons-tpl">
    <?php $this->load->view('leads_mapper/form/task_status_buttons'); ?>
</script>

<script type="text/x-jsrender" id="task-schedule-buttons-tpl">
    <?php $this->load->view('leads_mapper/form/task_schedule_buttons'); ?>
</script>

<?php $this->load->view('leads_mapper/estimators_dropdown'); ?>
<?php //$this->load->view('leads_mapper/gps_tracking_button'); ?>
<?php $this->load->view('clients/client_sms_modal'); ?>

<script>
    window.sms = <?php echo json_encode($sms??[]); ?>;
    window.leads = <?php echo json_encode($leads??[]); ?>;
    window.statuses = <?php echo json_encode($statuses??[]); ?>;
    window.estimators = <?php echo json_encode($users??[]); ?>;
    window.circles  =  <?php echo json_encode($circles??[]); ?>;
    window.polylines = <?php echo json_encode($polylines??[]); ?>;
    window.priority = <?php echo json_encode($priority??[]); ?>;
    var vehMarkers = [];
    var vehLabels = [];

    /* ---- delete after sms refactoring ----- */
    $(document).on("click", ".addLeadSms", function() {
        var obj = $(this);
        var href = $(this).attr('data-href');
        var phone = $(this).attr('data-phone');
        var email = $(this).attr('data-email');
        var name = $(this).attr('data-name');
        var company = $(this).attr('data-company');
        var cphone = $(this).attr('data-company-phone');
        var str = sms.sms_text;
        str = str.replace('[NAME]', name);
        str = str.replace('[EMAIL]', email);
        str = str.replace('[COMPANY_NAME]', company);
        str = str.replace('[COMPANY_PHONE]', cphone);
        $(href + ' .panel-heading').text('SMS to ' + name);
        $(href + ' .client_number').val(phone);
        $(href + ' .client_number').parent().parent().find('.control-label').text('Sms to ' + name);
        $(href + ' .sms_text').val(str);
        $(href).modal().show();
        return false;
    });

</script>

<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/modules/leads/leads_mapper.css">
<script src="<?php echo base_url(); ?>assets/js/modules/leads/leads_mapper.js?v=<?php echo config_item('js_leads_mapper'); ?>">

<?php $this->load->view('includes/footer'); ?>

