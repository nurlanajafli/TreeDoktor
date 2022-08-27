<?php $this->load->view('includes/header'); ?>

<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/schedule/schedule.css?v='.config_item('schedule.css')); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/codebase/dhtmlxscheduler.css'); ?>"
      type="text/css" media="screen" title="no title" charset="utf-8">

<?php //echo $map1['js']; ?>
<script type="text/javascript">
	var GOOD_MAN_HOURS_RETURN = <?php echo GOOD_MAN_HOURS_RETURN; ?>;
	var GREAT_MAN_HOURS_RETURN = <?php echo GREAT_MAN_HOURS_RETURN; ?>;
	var VERY_GREAT_MAN_HOURS_RETURN = <?php echo VERY_GREAT_MAN_HOURS_RETURN; ?>;
	var ACCOUNT_EMAIL_ADDRESS = '<?php echo $this->config->item('account_email_address'); ?>';
	var MESSENGER = <?php echo intval($this->config->item('messenger')); ?>;
	var SCHEDULER_STARTS_FROM = <?php echo config_item('crew_schedule_start') ?? 7; ?>;
	var SCHEDULER_ENDS_AT = <?php echo config_item('crew_schedule_end') ?? 20; ?>;

	var SHOW_WEEKEND = <?php echo config_item('schedule_show_weekend')??'false'; ?>;
</script>
<script src="<?php echo base_url('assets/js/StyledMarker.js'); ?>"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/dhtmlxscheduler.js'); ?>" type="text/javascript"
        charset="utf-8"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_units.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_minical.js'); ?>"
        type="text/javascript" charset="utf-8"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_collision.js'); ?>"
        type="text/javascript" charset="utf-8"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_tooltip.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_limit.js'); ?>"
        type="text/javascript" charset="utf-8"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_timeline.js'); ?>"
        type="text/javascript" charset="utf-8"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_all_timed.js'); ?>"
        type="text/javascript" charset="utf-8"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_active_links.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/js/jquery-ui.min.js'); ?>"
        type="text/javascript" charset="utf-8"></script>
<script src="<?php echo base_url('assets/js/jquery.ui.touch-punch.min.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/js/bootstrap-select.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/tinymce/tinymce.min.js"></script>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/label.js'); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/schedule/schedule_timeline_core.js?v='.config_item('schedule_timeline_core.js')); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/schedule/schedule.js?v='.config_item('schedule.js')); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/schedule/schedule_month.js?v='.config_item('schedule_month.js')); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/schedule/schedule_unit.js?v='.config_item('schedule_unit.js')); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/schedule/schedule_timeline.js?v='.config_item('schedule_timeline.js')); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/schedule/schedule_week.js?v='.config_item('schedule_week.js')); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/schedule/schedule_functions.js?v='.config_item('schedule_functions.js')); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/schedule/members.js?v='.config_item('members.js')); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/workorders/workorders_damages_modal.js'); ?>"></script>


<style>
    .dhx_cal_event_clear{
        height: 23px;
        line-height: 1.1;
    }
    .dhx_menu_head {
        background-image: none!important;
    }

    .dhx_cal_select_menu .dhx_event_move.dhx_title {
        height: 0px;
    }

</style>

<script charset="utf-8">

var scheduleGlobal = {
    'workorder_statuses': <?php echo json_encode($workorder_statuses); ?>,
	'wostatuses':'<?php echo json_encode($wostatuses); ?>',
	'reasons':'<?php echo json_encode($reasons); ?>',
	'sections':'<?php echo addslashes(json_encode($sections)); ?>',
	'trackerItems':<?php echo json_encode($tracks); ?>,
	'bonuses':<?php echo $bonuses_tpl; ?>,

	'team_stat_tpl':<?php echo $team_stat_tpl; ?>,
	'objects':<?php echo $objects ?>,


    'crews':<?php echo json_encode($crews); ?>,
    'event_workorders_modal_tpl': <?php echo json_encode(['tpl'=>$this->load->view('schedule/modals/event_workorders_modal', [], true)]); ?>,
	'emails_tpl':<?php echo $emails_tpl; ?>,
	'dayOffCrew':{
		'crew_color':'<?php echo ($dayOffCrew && !empty((array)$dayOffCrew) && isset($dayOffCrew->crew_color)) ? $dayOffCrew->crew_color : '#8ec165'; ?>',
		'crew_name':'<?php echo ($dayOffCrew && !empty((array)$dayOffCrew) && isset($dayOffCrew->crew_name)) ? $dayOffCrew->crew_name : ''; ?>'
	},
    'estimates_services_status': <?php echo $estimates_services_status; ?>
};


</script>
<div style="opacity: 0;padding-left: 50px;display: none;" class="bg-light crews-list-empty-val crews-list-container">
	<div id="crewsList"></div>
</div>

<div class="clear"></div>
<div class="line-items"></div>

<div id="scheduler_here" class="dhx_cal_container" style='width:100%; height:100%;-moz-user-select: none;-khtml-user-select: none;user-select: none;-webkit-user-select: none;'>
	<div class="dhx_cal_navline">
		<div class="dhx_cal_prev_button day-slider">&nbsp;</div>
		<div class="dhx_cal_next_button day-slider">&nbsp;</div>
		<div id="refreshList"><i class="fa fa-refresh"></i></div>

        <div class="dhx_cal_today_button hidden-xs hidden-sm"></div>
		<div class="dhx_cal_date"></div>
        <div class="dhx_minical_icon" id="dhx_minical_icon" onclick="show_minical()">&nbsp;</div>

        <div class="dhx_cal_tab" name="unit_tab"></div>
        <div class="dhx_cal_tab dhx_cal_tab_week" name="week_tab"></div>
        <div class="dhx_cal_tab dhx_cal_tab_month" name="month_tab"></div>
        <div class="dhx_cal_tab dhx_cal_tab_timeline" name="timeline_tab"></div>
        <?php /*
            <div class="btn btn-default no-shadow crewsList" name="crews_tab" style="display: none;">Crews</div>
        */ ?>
        <button class="btn btn-default no-shadow pull-right m-right-10 crewsList bg-white visible-unit" data-toggle="button" name="crews_tab">
            Crews <i class="fa fa-angle-up text"></i><i class="fa fa-angle-down text-active"></i>
        </button>
        <button class="btn btn-default no-shadow pull-right m-right-10 directionsMap bg-white visible-unit" data-backdrop="true" data-toggle="modal" href="#directions-map-modal" data-target="#directions-map-modal" name="crews_tab">
            Map <i class="fa fa-map-marker"></i>
        </button>

        <div class="week-member-filter-view" id="week-member-filter-view"></div>

	</div>
    
	<ul class="nav" style="position: absolute;top: 60px;height: 110px;width: 52px; z-index: 10;">
		<li class="dropdown">
			<a href="#" class="btn btn-success dropdown-toggle create-team" data-toggle="dropdown" style="box-shadow: none;border-radius: 0;height: 110px;width: 52px;text-align: center;padding-top: 50px;border: 1px solid #CECECE;left: -1px;"><i class="fa fa-plus"></i></a>
            <section class="dropdown-menu aside-xxl on animated fadeInLeft no-borders lt bg-light no-shadow" id="day-create-team-form" style="width:500px;">

            </section>
		</li>
	</ul>
    <div class="create-timeline-team-container">
        <a class="btn btn-danger create-timeline-team"
           data-toggle="modal"
           data-team_id = "0"
           data-team_date_start="<?php echo date("Y-m-d"); ?>"
           data-team_date_end="<?php echo date("Y-m-d"); ?>"
           href="#timeline-team-modal"><i class="fa fa-plus"></i>&nbsp;Add Team</a>
    </div>
	<div class="dhx_cal_header">

    </div>
	<div class="dhx_cal_data"></div>
	<!--<button class="btn btn-success fa fa-save no-shadow saveNote"></button>
	<textarea class="form-control no-shadow day-note" style="" placeholder="Note..."></textarea>-->

</div>

<div class="free-members-label pos-abt" style="display: none;">
    <div id="equipmentsList" class="nav navbar-nav navbar-right">
        <a class="dropdown-toggle btn btn-warning bg-white btn-sm" data-toggle="dropdown"><i class="fa fa-truck"></i></a>

        <section class="dropdown-menu aside-xl">
            <section class="panel bg-white">
                <header class="panel-heading b-light bg-light">
                    <strong>Free Equipment</strong>
                </header>
                <div class="scrollable list-group list-group-alt animated fadeInRight equipments-list-body" style="height: 150px">
                    <ul class="text-left freeItems sortable" data-team-style="text-shadow: 1px 1px #626262;" data-bonus-team-id="-1"></ul>
                </div>
                <footer class="panel-footer text-sm">
                    <a data-toggle="class:show animated fadeInRight">All free Equipment</a>
                </footer>
            </section>
        </section>
    </div>

    <div id="membersList" class="nav navbar-nav navbar-right">
        <a class="dropdown-toggle btn btn-primary bg-white btn-sm" data-toggle="dropdown"><i class="fa fa-users"></i></a>

        <section class="dropdown-menu aside-xl">
            <section class="panel bg-white">
                <header class="panel-heading b-light bg-light">
                    <strong>Free Members</strong>
                </header>
                <div class="scrollable list-group list-group-alt animated fadeInRight members-list-body" style="height: 130px;">
                    <ul class="text-left freeMembers sortable" data-team-style="text-shadow: 1px 1px #626262;" data-bonus-team-id="0"></ul>
                </div>
                <footer class="panel-footer text-sm">
                    <a data-toggle="class:show animated fadeInRight">All free Members</a>
                </footer>
            </section>
        </section>
    </div>
</div>&nbsp;&nbsp;

<div class="pos-abt schedule-stats">
    <a href="#" class="btn btn-xs btn-danger r-t btn-stats pos-abt" title="Show Analytic">
        <i class="fa fa-angle-up"></i> Statistics
    </a>
    <div class="schedule-stats-container">
        <div class="flex-stats-overflow">
            <div class="flex-stats" id="schedule-stats-container"></div>
        </div>
        <div class="all-teams-stat-block clear" id="statistic-total-block-view"></div>
    </div>
</div>

<a href="#" class="btn btn-xs btn-danger btn-rounded day-off-btn" title="Show Day Off">
    <i class="fa fa-angle-left"></i>
</a>

<aside class="aside-lg b-l pos-abt bg-light b-a day-off-container" style="display: none;">

    <h5 class="text-center font-bold">
        Day Off
    </h5>
    <div class="padder p-sides-10 animated fadeInLeft" style="height: 90%;" id="day-off-view">
    </div>
</aside>

<?php $this->load->view('clients/letters/client_letters_modal'); ?>

<div class="alert alert-danger" style="position: fixed;top: 10px;right: 10px;width: 450px;display:none;" id="attention">
	<strong class="h4">Attention!</strong><br>
	Schedule of this day was changed, please <a href="#" onclick="reloadWorkspace();" class="alert-link">click here</a> to reload your workspace.
</div>

<input type="hidden" id="timeFormat" value="<?php echo getIntTimeFormat()?>" />
<input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
<input type="hidden" id="dhlFormat" value="<?php echo getFormatDhlDefaultDate()?>" />
<script>init();</script>


<div class="hidden">
	<div id="map_canvas"></div>
</div>

<script src="<?php echo base_url(); ?>assets/js/modules/schedule/schedule_map.js?v=<?php echo config_item('schedule_map.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/schedule/schedule_map_markers.js?v=<?php echo config_item('schedule_map.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/schedule/schedule_map_directions.js?v=<?php echo config_item('schedule_map.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/schedule/routes-map.js?v=<?php echo config_item('schedule_map.js'); ?>"></script>
<!--   ------ Statistic templates -------     -->
<script type="text/x-jsrender" id="schedule-stats-container-tpl">
    <?php $this->load->view('statistics/team_stats_block'); ?>
</script>

<script type="text/x-jsrender" id="statistic-total-block-tpl">
    <?php $this->load->view('statistics/statistic_total_block'); ?>
</script>

<script id="schedule-stats-container-empty-tpl" type="text/x-jsrender">
  <div class="one-team-stat-block pull-left" data-team-id="" style="flex: 0 0 {{:sectionWidth}}px;width:{{:sectionWidth}}px;"></div>
</script>

<script type="text/x-jsrender" id="map-teams-marker-filter-dropdown-tmp">
<?php $this->load->view('modals/partials/map_marker_filter'); ?>
</script>

<script type="text/x-jsrender" id="workorder-marker-short-tmp">
<?php $this->load->view('modals/markers/workorder_marker_short'); ?>
</script>

<script type="text/x-jsrender" id="workorder-marker-long-tmp">
<?php $this->load->view('modals/markers/workorder_marker_long'); ?>
</script>

<script type="text/x-jsrender" id="event-marker-short-tmp">
<?php $this->load->view('modals/markers/event_marker_short'); ?>
</script>

<!--   ------ Statistic templates -------     -->

<!--   ------ Common templates -------     -->
<?php $this->load->view('schedule/templates/main'); ?>
<!--   ------ Common templates -------     -->

<!--   ------ Week templates -------     -->
<?php $this->load->view('schedule/templates/week_templates'); ?>
<!--   ------ Week templates -------     -->

<!--   ------ Unit Day templates -------     -->
<?php $this->load->view('schedule/templates/day_templates'); ?>
<!--   ------ Unit Day templates -------     -->

<!--   ------ Timeline templates -------     -->
<?php $this->load->view('schedule/templates/timeline_templates'); ?>
<?php $this->load->view('schedule/modals/timeline/timeline_team_modal'); ?>
<!--   ------ Timeline templates -------     -->

<?php $this->load->view('workorders/modals/workorders_damages_modal'); ?>
<?php $this->load->view('schedule/modals/directions_map_modal'); ?>
<?php $this->load->view('includes/footer'); ?>
