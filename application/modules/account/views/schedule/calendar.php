<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/dhtmlxscheduler.js'); ?>" type="text/javascript"
        charset="utf-8"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_minical.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<div id="scheduler_here" class="dhx_cal_container client_calendar" style="width:100%; height:100%;-moz-user-select: none;-khtml-user-select: none;user-select: none;-webkit-user-select: none;">
    <div class="dhx_cal_navline">
        <div class="dhx_cal_prev_button day-slider">&nbsp;</div>
        <div class="dhx_cal_next_button day-slider">&nbsp;</div>
        <!--<div id="refreshList"><i class="fa fa-refresh"></i></div>-->

        <div class="dhx_cal_today_button hidden-xs hidden-sm"></div>
        <div class="dhx_cal_date"></div>
        <div class="dhx_minical_icon hidden-xs" id="dhx_minical_icon" onclick="show_minical()">&nbsp;</div>

        <div class="dhx_cal_tab dhx_cal_tab_day" name="day_tab"></div>
        <div class="dhx_cal_tab dhx_cal_tab_week" name="week_tab"></div>
        <div class="dhx_cal_tab dhx_cal_tab_month" name="month_tab"></div>
    </div>
    <div class="dhx_cal_header"></div>
    <div class="dhx_cal_data"></div>
</div>