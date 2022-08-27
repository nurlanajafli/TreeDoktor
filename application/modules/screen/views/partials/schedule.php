
<div style="display:none;padding-left: 50px;" id="crewsList" class="bg-light">

</div>

<div id="scheduler_here" class="dhx_cal_container" style="overflow-x:auto;">
	
	<div id="screen-container">

	<div class="dhx_cal_navline screen-top-navbar" style="display: block;">
		<?php /*
		<div class="dhx_cal_prev_button">&nbsp;</div>
		<div class="dhx_cal_next_button">&nbsp;</div>
		
		<div class="dhx_cal_today_button"></div>
		*/ ?>
		<div class="screen-top-navbar-dates-main top-0">
		<div class="screen-top-navbar-dates top-0">
			<div class="dhx_cal_date left-0"></div>
			<div class="timeNow right-0">
				<ul id="digital-clock" class="digital p-n">
					<li class="hour"></li><li class="min"></li><li class="sec"></li><li class="meridiem"></li>
				</ul>
			</div>
		</div>
		</div>
		<div class="dhx_minical_icon" id="dhx_minical_icon" onclick="show_minical()">&nbsp;</div>
		<?php /*
		/*
		<div class="dhx_cal_tab dhx_cal_tab_first" name="unit_tab" style="left:10px;"></div>
		<div class="dhx_cal_tab" name="week_tab" style="right:140px;"></div>
		<!--<div class="btn btn-default no-shadow crewsList" name="crews_tab">Show Crews</div>-->
		<div class="dhx_cal_tab" name="month_tab" style="right:76px;"></div>
		*/ ?>
	</div>
	
	<div class="dhx_cal_header"></div>
	<div class="dhx_cal_data"></div>
	<span class="form-control no-shadow day-note" style="display: none; top: 8px;left: 205px;overflow-y: scroll;z-index: 10;padding: 0px 3px;"></span>

	</div>
</div>
<div class="datoff-container">
	<div class="dayoff-header">Day Off</div>
	<div id="dayoff-body" class="dayoff-body"></div>

	<script id="dayoff-template" type="text/x-jsrender">
		<div class="alert m-t-xs alert-danger dayoff-item">
		<span>{{:emp_name}}</span>
		
		{{if emp_reason!=''}}<span><br>({{:emp_reason}})</span>{{/if}}
		</div>
	</script>
</div>

<script>init();</script>
