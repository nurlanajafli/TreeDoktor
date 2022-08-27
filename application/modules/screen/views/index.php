<?php $this->load->view('includes/header_screen'); ?>

<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/screen/screen.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/schedule/schedule.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<style type="text/css">
	#scheduler_here{
		width: calc(100vw*7/8);
		padding-left: 50px;
	}
	.dhx_cal_header{
		left:0px!important;
	}
</style>
<?php //echo $map1['js']; ?>
<script src="<?php echo base_url('assets/js/StyledMarker.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/dhtmlxscheduler.js'); ?>" type="text/javascript"
        charset="utf-8"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_units.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_collision.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/js/jquery.clock.js?v=1.04'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/js/bootstrap-select.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/codebase/dhtmlxscheduler.css?v.1.01'); ?>"
      type="text/css" media="screen" title="no title" charset="utf-8">

<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/screen/screen.js?v.1.01'); ?>"></script>

<script charset="utf-8">
var currency_symbol = '<?php echo config_item('currency_symbol'); ?>';
var currency_symbol_position = '<?php echo config_item('currency_symbol_position'); ?>';

var sections = [];
var bakSections = [];
var processUpdateSections = false;
var infowindow = false;
var dp = {};
var colors = [];
var woStatusesColors = [];
var wizardClick = 0;
var reasonsAbsence = [];
var counter = false;
function init() {

	var employees = <?php echo $employees_tpl; ?>;
	<?php foreach($reasons as $reason) : ?>
	reasonsAbsence.push({reason_id:'<?php echo $reason->reason_id; ?>', reason_name:'<?php echo $reason->reason_name; ?>'});
	<?php endforeach; ?>
	<?php foreach($wostatuses as $status) : ?>
	woStatusesColors[<?php echo $status['wo_status_id']; ?>] = '<?php echo $status['wo_status_color']; ?>';
	<?php endforeach; ?>

	<?php foreach($sections as $section) : ?>
	colors[<?php echo $section->team_id; ?>] = '<?php echo $section->team_color; ?>';
	<?php endforeach; ?>

	scheduler.renderEvent = function (container, ev) {
		ev.color = getEventColor(ev.id);
		//ev.textColor = getTextColor(colors[ev.section_id]);
	}

	scheduler.locale.labels.unit_tab = "Day"
	scheduler.locale.labels.section_custom = "Assigned to";
	scheduler.config.first_hour = 7;
	scheduler.config.last_hour = 23;
	scheduler.config.time_step  = 60;
	scheduler.textColor = '#000';

	scheduler.config.hour_size_px = 30;
	scheduler.xy.scale_height = -60;
	//scheduler.config.hour_size_px = 60;

	scheduler.config.readonly = true;
	scheduler.config.dblclick_create = false;
	scheduler.config.details_on_create = true;
	scheduler.config.details_on_dblclick = true;
	scheduler.config.icons_select = [
		"icon_delete"
	];
	scheduler.config.xml_date = "%Y-%m-%d %H:%i";
	scheduler.config.collision_limit = 1;


	//scheduler.xy.scale_height = 120;
	scheduler.config.lightbox.sections = [
		{name: "Workorders:", height: "", type: "template", map_to: "my_template"},
		{name: "custom", height: 23, type: "select", options: bakSections, map_to: "section_id" },
		{name: "time", height: 72, type: "time", map_to: "auto", time_format: ["%Y", "%m", "%d", "%H:%i"]}
	]

	scheduler.createUnitsView("unit", "section_id", sections, 80, 1);

	if(new Date().getHours() >= 12)
	{
		scheduler.init('scheduler_here', new Date(new Date().getTime() + 24 * 60 * 60 * 1000), "unit");
	}
	else
	{
		scheduler.init('scheduler_here', new Date(), "unit");
	}
	min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
	max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);

	scheduler.load(baseUrl + 'screen/data?from=' + min_date + "&to=" + max_date, "json");

	dp = new dataProcessor(baseUrl + "screen/data");
	dp.init(scheduler);
	dp.setTransactionMode("POST", true);

	scheduler.templates.hour_scale = function(date){
		var hour = date.getHours();
		var top = '00';
		var bottom = '30';
		if(hour==0)
			top = 'AM';
		if(hour==12)
			top = 'PM';
		hour =  ((date.getHours()+11)%12)+1;
		var html = '';
		var section_width = Math.floor(scheduler.xy.scale_width/2);
		//console.log(section_width);
		var minute_height = Math.floor(scheduler.config.hour_size_px/2);
		html += "<div class='dhx_scale_hour_main' style='width: "+section_width+"px; height:"+(minute_height*2)+"px;'>"+hour+"</div><div class='dhx_scale_hour_minute_cont' style='width: "+section_width+"px;'>";
		html += "<div class='dhx_scale_hour_minute_top' style='height:"+minute_height+"px; line-height:"+minute_height+"px;'>"+top+"</div><div class='dhx_scale_hour_minute_bottom' style='height:"+minute_height+"px; line-height:"+minute_height+"px;'>"+bottom+"</div>";
		html += "<div class='dhx_scale_hour_sep'></div></div>";
		return html;
	};

	scheduler.renderEvent = function(container, ev) {

		var container_width = container.style.width; // e.g. "105px"
		var container_height = parseInt(container.style.height); // e.g. "105px"

		// move section
		var html = "<div class='dhx_event_move dhx_header' style='width: " + container_width + "'></div>";

		// container for event's content
		html+= '<div class="dhx_event_move dhx_title" style="background:' + ev.color + ';">';
		html += ev.estimator + " ";
		//two options here:show only start date for short events or start+end for long
		if ((ev.end_date - ev.start_date)/60000>40){//if event is longer than 40 minutes
			html += scheduler.templates.event_header(ev.start_date, ev.end_date, ev);
			html += "</div>";
		} else {
			html += scheduler.templates.event_date(ev.start_date) + "</div>";
		}
		// displaying event's text
		html += '<div class="dhx_body" style="height: ' + (container_height - 28) + 'px; width:' + (parseInt(container_width) - 10) + 'px;background:' + ev.color + ';">' + ev.text;
		html += '</div>';
		html += '<div class="dhx_event_resize dhx_footer" style=" width:166px;background:' + ev.color + ';"></div>';

		container.innerHTML = html;
		return true; //required, true - display a custom form, false - the default form
	};

	scheduler.attachEvent("onViewChange", function (new_mode , new_date){
		if(new_mode == 'month' || new_mode == 'week')
		{
			//$('.dhx_cal_date').attr('style', '');
			$('.day-note').hide();
			$('.saveNote').hide();
		}
		else
		{
			$('.day-note').css('height', '');
			$('.saveNote').css('height', '');
			//$('.day-note').show();
			$('.saveNote').show();
			if(new_mode == 'day')
			{
				$('.day-note').attr('style', $('.day-note').attr('style') + ';height:' + (parseInt($('.day-note').css('height')) + 1) + 'px!important');
				$('.saveNote').attr('style', $('.saveNote').attr('style') + ';height:' + (parseInt($('.saveNote').css('height')) + 1) + 'px!important');
			}
		}
		if(new_mode != 'unit' || processUpdateSections)
		{
			if(new_mode != 'unit')
			{
				var date = scheduler.getState().min_date;
				var minDate = date.getFullYear() + '-' + leadZero((date.getMonth() + 1), 2) + '-' + leadZero(date.getDate(), 2);
				date = scheduler.getState().max_date;
				var maxDate = date.getFullYear() + '-' + leadZero((date.getMonth() + 1), 2) + '-' + leadZero(date.getDate(), 2);
				$.post(baseUrl + 'screen/ajax_showed_sections', {start_date : minDate, end_date : maxDate}, function(resp){
					if(new_mode == 'day')
					{
						$('.day-note').text(resp.note.note_text);
					}
					$.each(resp.sections, function(key, val){
						colors[val.team_id] = val.team_color;
					});
					$.each($('[event_id]:visible'), function(key, val){
						id = $(val).attr('event_id');
						scheduler.getEvent(id).section_id = scheduler.getEvent(id).crew_id;
						scheduler.getEvent(id).color = getEventColor(id);
						if(key + 1 == $('[event_id]:visible').length)
							scheduler.updateView();
					});
				}, 'json');
				scheduler.config.drag_create = false;
			}
			scheduler.updateView();
			$('#processing-modal').modal('hide');
			min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
			max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);
			scheduler.clearAll();
			scheduler.load(baseUrl + 'screen/data?from=' + min_date + '&to=' + max_date, "json");
			return true;
		}
		$('.crewsList').removeAttr('disabled');
		scheduler.config.drag_create = false;
		var obj = $(this);
		var date = scheduler.getState().date;
		var dateYMD = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
		if(new Date().getHours() >= 12)
		{
			var currentDate = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
			var day = currentDate.getDate();
			var month = currentDate.getMonth() + 1;
			var year = currentDate.getFullYear();
			dateYMD = month + " " + day + " " + year;
		}
		min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
		max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);
		scheduler.clearAll();
		scheduler.load(baseUrl + 'screen/data?from=' + min_date + '&to=' + max_date, "json");
		resetTeams();
		return true;
	});

}

$(document).ready(function () {

	$(document).on('click', '.dhx_scheduler_week .dhx_scale_bar', function(){
		var date = $(this).text() + ' ' + (scheduler.getState().date.getYear() + 1900);
		scheduler.init('scheduler_here', new Date(date), "unit");
		return false;
	});

	$(document).on('click', '.dhx_scheduler_month .dhx_month_head', function(){
		var date = (scheduler.getState().date.getMonth() + 1) + ' ' + $(this).text() + ' ' + (scheduler.getState().date.getYear() + 1900);
		scheduler.init('scheduler_here', new Date(date), "unit");
		return false;
	});
	$(document).on('click', '.selectDate', function(){
		var obj = $(this).parents('.btn-group:first')[0];
		$(obj).find('.dropdown-label').text($(this).text());
		var num = $(obj).data('select_num');
		$(obj).find('li').removeClass('active');
		$(this).parent().addClass('active');
		$($('.dhx_section_time select')[num]).val($(this).data('value'));
		$(obj).removeClass('open');
		return false;
	});
	resetTeams();
});
function resetTeams(team_id) {
	$('#processing-modal').modal();
	var showedTeam = team_id ? team_id : null;
	setTimeout(function(){
		var dayOffNote = '';
		var employees = <?php echo $employees_tpl; ?>;
		var date = scheduler.getState().date;
		var dateYMD = date.getFullYear() + '-' + leadZero(date.getMonth() + 1, 2) + '-' + leadZero(date.getDate(), 2);
		$.post(baseUrl + 'screen/ajax_crews_members', {date:dateYMD}, function(resp){

			if(resp.status == 'ok')
			{
				Screen.set_day_off(resp.members);

				$('.day-note').text('');
				if(resp.update)
				{
					lastUpdateId = resp.update.update_id;
				}
				if(resp.note != undefined)
					$('.day-note').text(resp.note.note_text);
				sections = [];
				colors = [];
				
				$.each(resp.sections, function(num, val){
					/*if(val.team_crew_id == '0')
						dayOffNote = val.team_note ? val.team_note : '';
					else
					{*/
					if(val.team_crew_id != 0){	
						colors[val.team_id] = val.team_color;
						crew = val.crew_name;
						crew += val.emp_name ? ' (' + val.emp_name + ')' : '';
						note = val.team_note ? val.team_note : '';
						sections.push({
							key:val.team_id,
							subkey:val.team_leader_id,
							name:val.crew_name,
							color:val.team_color,
							leader:val.emp_name,
							label: '<a href="#" class="crew-name">'+crew+'</a><div class="team-note" style="padding: 3px;font-size:16px;margin-top:2px;min-height:36px;border:none;color:#000;position:relative;bottom:0px;left:0px;right:0px;width:99%;resize:none;background-color:' + val.team_color + ';">' + note + '</div>'+'<a href="#" data-color="' + val.team_color + '"' +
								'data-crew_leader="' + val.team_leader_id + '" data-crew_id="' + val.team_id + '" ' +
								'class="dropdown-toggle">' + '</a>' + employees.tpl
						});

					}

					if(num + 1 == resp.sections.length)
					{
						scheduler.config.lightbox.sections[1].options = sections;
						/*
						offTpl = employees.tpl;
						offTpl = offTpl.replace(/<div style="width:60%"/, '<div style="width:100%"');
						offTpl = offTpl.replace(/<div style="width:40%"/, '<div style="display:none"');
						
						sections[sections.length] = {
							key:0,
							subkey:0,
							name:'<?php echo $dayOffCrew->crew_name; ?>',
							color:'<?php echo $dayOffCrew->crew_color; ?>',
							leader:'',
							label: '<div class="team-note" style="padding: 3px;font-size:16px;margin-top:2px;min-height:36px;border:none;color:#000;position:relative;bottom:0px;left:0px;right:0px;width:99%;resize:none;background-color:<?php echo $dayOffCrew->crew_color; ?>;">' + dayOffNote + '</div>'+'<a href="#" data-color="<?php echo $dayOffCrew->crew_color; ?>"' +
								'data-crew_id="0" ' +
								'class="dropdown-toggle"><?php echo $dayOffCrew->crew_name; ?>' +
								'</a>' + offTpl
						};
						*/
						updateSections(sections);
						$.each($('[event_id]:visible'), function(key, val){
							id = $(val).attr('event_id');
							scheduler.getEvent(id).section_id = scheduler.getEvent(id).crew_id;
							scheduler.getEvent(id).color = getEventColor(id);
							if(key + 1 == $('[event_id]:visible').length)
								scheduler.updateView();
						});
					}
				});

				if(!resp.sections.length)
				{
					/*
					offTpl = employees.tpl;
					offTpl = offTpl.replace(/<div style="width:60%"/, '<div style="width:100%"');
					offTpl = offTpl.replace(/<div style="width:40%"/, '<div style="display:none"');
					sections[sections.length] = {
						key:0,
						subkey:0,
						name:'<?php echo $dayOffCrew->crew_name; ?>',
						color:'<?php echo $dayOffCrew->crew_color; ?>',
						leader:'',
						label: '<div class="team-note" style="padding: 3px;font-size:16px;margin-top:2px;min-height:36px;border:none;color:#000;position:relative;bottom:0px;left:0px;right:0px;width:100%;resize:none;background-color:<?php echo $dayOffCrew->crew_color; ?>;"></div>'+'<a href="#" data-color="<?php echo $dayOffCrew->crew_color; ?>"' +
							'data-crew_id="0" ' +
							'class="dropdown-toggle"><?php echo $dayOffCrew->crew_name; ?>' +
							'</a>' + offTpl
							
					};*/
					updateSections(sections);
				}
				//dayOffSetReasons();
				$.each(resp.members, function(key, val){
					$('.addMember[data-emp_id="' + val.employee_id + '"]').remove();
					var empClass = 'bg-info';
					var removeBtn = ' <a href="#" class="moveFromCrew">x</a></li>';
					if(val.employee_id == val.team_leader_id)
					{
						empClass = 'bg-dark';
						removeBtn = '';
					}
					$('[data-crew_id="' + val.team_id + '"]').next().find('.crewInfo').after('<li class="label ' + empClass + '" data-employee_id="' + val.employee_id + '">' + val.emp_name + removeBtn);
				});
				$('.dhx_scale_holder[style]:last').html('<ul class="pull-left text-center crew_0" style="width: 214px;padding: 0;"></ul>');
				crewsList(resp.sorted_teams);

				/*
				var freeMembers = '';
				$.each($('.dhx_cal_header .emp-dropdown:first .addMember'), function(key, val){
					freeMembers += $(val)[0].outerHTML;
					freeMembers = freeMembers.replace(/<select.*>.*<\/select>/, '');
				});
				$('#crewsList .clear').before('<ul class="pull-left text-left freeMembers"><div style="margin-bottom: -3px;">Free Members:</div></ul>');
				$('.freeMembers').css('width', $($('.dhx_scale_bar')[0]).width() + 'px');
				$('.freeMembers').append(freeMembers);
				*/
				changeFonts();

				setTimeout(function(){
					Screen.init();
				}, 500);

				/*$.each(resp.items, function(key, val){
					$('[data-crew_id="' + val.equipment_team_id + '"]').next().find('.eqInfo').after('<li class="label bg-warning" data-eq_id="' + val.equipment_id + '">' + val.item_name + ' <a href="#" class="moveItemFromCrew">x</a></li>');
					addToItemList({crew_id:val.equipment_team_id,item_color:val.group_color,item_name:val.item_name,item_id:val.equipment_id});
					$('.addItem[data-item_id="' + val.equipment_id + '"]').remove();
				});*/
				var max = 0;
				var maxHeight = 0;
				$.each($('.team-note'), function(key, val){
					if($(val).offset().top > max)
						max = $(val).offset().top;
					if($(val).height() > maxHeight)
						maxHeight = $(val).height();
				});
				$.each($('.team-note'), function(key, val){
					var currTop = $(val).offset().top;
					var plus = 2;
					if($(val).height() < maxHeight)
						$(val).css('height', maxHeight + 'px');
						//plus = maxHeight - $(val).height() + 2;
					if(currTop < max)
						$(val).css('margin-top', (max - currTop + plus) + 'px');
					if($('.team-note').length == 1)
						$(val).css('margin-top', '60px');
				});
				$.each($('.dhx_scale_bar .crewInfo'), function(number, val){
					var members = $(this).parent().find('[data-employee_id]');
					var team_id = $(this).parents('.emp-dropdown').prev().data('crew_id');
					var crew_leader = $(val).parent().parent().prev().data('crew_leader');
					var crew_color = $(val).parent().parent().prev().data('color');
					var leader_name = $(val).parent().parent().find('[data-employee_id="' + crew_leader + '"]').text();
					if(teamHasEvents(team_id) || !team_id)
						$('[data-crew_id="' + team_id + '"]').next().find('.deleteTeam').hide();
					else
						$('[data-crew_id="' + team_id + '"]').next().find('.deleteTeam').show();
					var options = '';
					if(team_id)
					{
						options = '<select class="teamLead no-shadow form-control" style="width: 180px;display: inline-block;" data-team="' + team_id + '"><option value="">No Leader</option>';
						$.each(members, function(num, value){
							selected = '';
							if($(value).data('employee_id') == crew_leader)
								selected = ' selected';
							options += '<option value="' + $(value).data('employee_id') + '"' + selected + '>' + $(value).text().replace(' x', '') + '</option>';
						});
						options += '</select>';
						options += '<input type="text" data-team="' + team_id + '" class="teamColor mycolorpicker no-shadow form-control" style="display:inline-block;margin-left:2px;width:32%;background:' + crew_color + '" value="' + crew_color + '">';
					}
					$(val).html($(val).parent().parent().prev().text().replace(/\(.*?\)\u00A0/g, '') + ': ' + options);
					$(val).parent().parent().find('.addMember[data-emp_id="' + crew_leader + '"]').remove();
					if((number < parseInt($("#scheduler_here a.dropdown-toggle").length / 2)))
					{
						$(val).parent().parent().css('left', '0px');
						$(val).parent().parent().find('.arrow').css('left', '48px');
					}
					else
					{
						$(val).parent().parent().css('right', '0px');
						$(val).parent().parent().find('.arrow').css('left', 'auto').css('right', '32px');
					}
				});
				$('.mycolorpicker').each(function () {
					var current_color = $(this).val();
					var current_color_short = current_color.replace(/^#/, '');
					$(this).colpickSetColor(current_color_short);
				});
				if(showedTeam)
					$('[data-crew_id="' + showedTeam + '"]').click();
				$('.dhx_scale_holder[style]:last').css('background-image', 'none');
				$('.day-note').css('width', ($('.dhx_cal_header').width() + 1 - 156) + 'px');
				$('#processing-modal').modal('hide');
				return false;
			}
		}, 'json');
	}, 500);
}

function changeFonts(event_id)
{
	$.each($('[event_id]'), function(key, val){
		if($(val).find('.dhx_body').height() < $(val).find('.dhx_body').children().height())
		{
			$(val).find('.dhx_body').css('visibility', 'hidden');
			var font = 16;
			while($(val).find('.dhx_body').height() < $(val).find('.dhx_body').children().height())
			{
				$(val).find('.dhx_body').css('font-size','');
				$(val).find('.dhx_body').attr('style', $(val).find('.dhx_body').attr('style') + 'font-size:' + font + 'px!important');
				font -= 2;
				if(font < 8)
					break;
			}
			$(val).find('.dhx_body').css('visibility', 'visible');
		}
	});
	return false;
}

function getEventColor(id)
{
	var ev = scheduler.getEvent(id);
	var color = woStatusesColors[ev.wo_status];
	if(!color)
		color = colors[ev.crew_id];
	return color;
}

function dayOffSetReasons()
{
	var absenceObj = $('[data-crew_id="0"]').next();
	reasonsSelect = ' <select class="reasonAbsence" style="color: #000;max-width: 107px;"><option value="">Select Reason</option>';
	$.each(reasonsAbsence, function(key, val){
		reasonsSelect += '<option value="' + val.reason_id + '">' + val.reason_name + '</option>';
	});
	reasonsSelect += '</select>';
	$.each($(absenceObj).find('.addMember:not(:has(*))'), function(key, val){
		$(val).append(reasonsSelect);
	});
}

function teamHasEvents(teamId) {
	var result = false;
	$.each($('.dhx_cal_event'), function(key, val){
		if(scheduler.getEvent($(val).attr('event_id')).crew_id == teamId)
		{
			result = true;
			return false;
		}
	});
	return result;
}

function updateSections(newSections, crew_id) {
	processUpdateSections = true;
	scheduler.config.lightbox.sections[1].options = newSections;
	scheduler.createUnitsView("unit", "section_id", newSections, 80,1);
	if(crew_id)
	{
		setTimeout(function(){
			$('[data-crew_id="' + crew_id + '"]').click();
		}, 300);
	}
	processUpdateSections = false;
	return false;
}
function order(which, dir) {
	var Mdir = (dir == "desc") ? -1 : 1;
	$(which).each(function () {
		var sorted = $(this).find("> li.bg-primary").sort(function (a, b) {
			return $(a).text().toLowerCase() > $(b).text().toLowerCase() ? Mdir : -Mdir;
		});
		$(this).append(sorted);
	});
}
function leadZero(number, length) {
	while(number.toString().length < length){
		number = '0' + number;
	}
	return number;
}
function crewsList(members) {
	var crewName = '';
	$('#crewsList').html('');
	$.each($('a[data-crew_id]'), function(key, val){
		if(!$(val).data('crew_id') || $(val).data('crew_id') == '0')
			return false;
		$('#crewsList').append('<ul class="pull-left text-center crew_' + $(val).data('crew_id') + '">' + $(val).text()+  ':</ul>');
		if(key && $('.crew_' + $(val).data('crew_id')).offset().left < $('.crew_' + $(val).data('crew_id')).prev().offset().left)
			$('.crew_' + $(val).data('crew_id')).addClass('clear');
	});
	$('#crewsList ul').css('width', $($('.dhx_scale_bar')[0]).width() + 'px');
	$('.dhx_scale_holder .crew_0').css('width', $($('.dhx_scale_bar')[0]).width() + 'px');
	$.each(members, function(key, val){
		if(val.type == 'user')
			addToSortedCrewList(val, val.team_id ? false : true);
		if(val.type == 'equipment')
			addToSortedItemList(val);
	});
	//$('.dhx_scale_bar:last').prepend('<div class="timeNow"><ul id="digital-clock" class="digital p-n"><li class="hour"></li><li class="min"></li><li class="sec"></li><li class="meridiem"></li></ul></div>');
	$('#digital-clock').clock({/*offset: '-5', */type: 'digital'});
	//setTimeout(function(){
	//	width = $('.dhx_scale_bar:last').width() + 'px!important;';
	//	left = $('#digital-clock').offset().left + 'px!important;';
	//	if($('.dhx_scale_bar').length == 1)
	//		left = '50px!important;';
		
		//offtop = ($('.hour').offset().top - 30) + 'px!important;';
		//$('.dhx_cal_date').css('cssText', 'left: ' + left + 'top: ' + offtop + 'width: ' + width + 'display: block!important;');
		//console.log('cssText', 'left: ' + left + 'top: ' + offtop + 'width: ' + width + 'display: block!important;');
		//offtop = ($('.dhx_cal_header').offset().top) + 'px!important;';
		//$('.dhx_cal_data').css({top: offtop});
	//}, 1000);
	$('#crewsList').append('<div class="clear"></div>');
}

function addToSortedCrewList(member, dayOff) {
	/*if(!$('#crewsList .crew_' + member.team_id).length && member.team_id)
		$('#crewsList').find('.clear').before('<ul class="text-center crewBonuses1 sortable crew_' + member.team_id + '" data-bonus-team-id="' + member.team_id + '" style="margin-top:4px;"><div class="dropdown-menu1 animated fadeInLeft bonusesList"></div></ul>');
*/	$('#crewsList>ul').css('width', $($('.dhx_scale_bar')[0]).width() + 'px');

	var empClass = '';
	if(member.item_id == member.team_leader_user_id)
		member.name = '<span class="teamLeader">* </span>' + member.name;

	if(member.driver_id)
		member.name = member.name + '<span class="driverFor" data-driver-for-item="' + member.group_id + '"> (' + member.driver_id + ')</span>';

	if(member.team_id)
		$('a[data-crew_id="' + member.team_id + '"]').parent().append('<li class="label label-info employee_' + member.item_id + empClass + '"' + ' data-emailid="' + member.emailid +'" data-emp_id="' + member.item_id + '" style="background:' + member.team_color + ';border:1px solid #000;text-overflow: ellipsis;overflow: hidden; display: block; font-size: 14px; margin-top: 1px;">' + member.name + '</li>');
	if(dayOff) {
		var empName = member.name + ' (' + member.group_color + ')';
		var field_worker = member.field_worker ? ' data-field_worker="1"' : '';
		$('.dhx_scale_holder .crew_0').append('<li class="label m-t-xs label-info employee_' + member.item_id + '" ' +  field_worker + ' data-emailid="' + member.emailid + '" data-emp_id="' + member.item_id + '" style="background:' + member.team_color + ';border:1px solid #000;display: block;font-size: 12px;white-space: normal; margin-top: 1px;">' + empName + '</li>');
	}
}

function addToSortedItemList(item) {
	var tpl = `
		<li class="label bg-warning" data-item_code="` + item.emailid + `" data-eq_id="` + item.item_id + `" data-eq_group_id="` + item.group_id + `" data-origin-color="` + item.group_color + `">` + item.name + ` 
			<a href="#" class="moveItemFromCrew">x</a>
		</li>
	`;
	//$('[data-crew_id="' + item.team_id + '"]').next().find('.eqInfo').after(tpl);
	//$('.addItem[data-item_id="' + item.item_id + '"]').remove();
	var driver = item.field_worker ? item.field_worker : 'N/A';
	//var driverId = item.field_worker ? item.field_worker : 'N/A';
	tpl = `
		<li class="label it_` + item.item_id + `" data-item_code="` + item.emailid + `" data-item_group_id="` + item.group_id + `" data-item-id="` + item.item_id + `" data-origin-color="` + item.group_color + `" style="background:#fff;color:#000;border:1px solid;display: block;margin-top: 1px; font-size: 14px;white-space: normal;">` + item.name + `
			<span href="#" data-driver_id="` + item.driver_id + `" data-toggle="popover" data-html="true" data-placement="bottom" data-content="TEST">
				(` + driver + `)
			</span>
		</li>
	`;

	$('a[data-crew_id="' + item.team_id + '"]').parent().append(tpl);
	return false;
}

function addToCrewList(member, dayOff) {
	if(!$('#crewsList .crew_' + member.team_id).length && member.team_id)
		$('#crewsList').find('.clear').before('<ul class="pull-left text-center crew_' + member.team_id + '">' + member.crew_name + ': </ul>');
	$('#crewsList ul').css('width', $($('.dhx_scale_bar')[0]).width() + 'px');
	if(member.team_id)
		$('#crewsList .crew_' + member.team_id).append('<li class="label label-info employee_' + member.employee_id + '" style="background:' + member.team_color + ';border:1px solid #000;display: block;text-overflow: ellipsis;overflow: hidden; font-size: 14px;white-space: normal;margin-top: 1px;">' + member.emp_name + '</li>');
	
	/*
	if(dayOff)
		$('.dhx_scale_holder .crew_' + member.team_id).append('<li class="label m-t-xs label-info employee_' + member.employee_id + '" style="background:' + member.team_color + ';border:1px solid #000;display: block;font-size: 14px;white-space: normal;margin-top: 1px;">' + member.emp_name + '</li>');
	*/
}
function removeFromCrewList(employee_id) {
	var obj = $('#crewsList ul li.employee_' + employee_id).parent();
	$('#crewsList ul li.employee_' + employee_id).remove();
	$('.dhx_scale_holder .crew_0 li.employee_' + employee_id).remove();
}
function addToItemList(item) {
	$('[data-crew_id="'+ item.crew_id +'"]').parent().find('.team-note').before('<li class="label it_' + item.item_id + '" style="background:#fff;color:#000;border:1px solid;display: block;margin-top: 1px; font-size: 14px;white-space: normal;">' + item.item_name + '</li>');
	return false;
}
function removeFromItemList(item_id) {
	$('.it_' + item_id).remove();
	return false;
}
function getCookie(name) {
	var matches = document.cookie.match(new RegExp(
		"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	))
	return matches ? decodeURIComponent(matches[1]) : undefined
}
function setCookie(name, value, props) {
	props = props || {}
	var exp = props.expires
	if (typeof exp == "number" && exp) {
		var d = new Date()
		d.setTime(d.getTime() + exp*1000)
		exp = props.expires = d
	}
	if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }

	value = encodeURIComponent(value)
	var updatedCookie = name + "=" + value
	for(var propName in props){
		updatedCookie += "; " + propName
		var propValue = props[propName]
		if(propValue !== true){ updatedCookie += "=" + propValue }
	}
	document.cookie = updatedCookie

}
function deleteCookie(name) {
	setCookie(name, null, { expires: -1 })
}
function rgb2hsl(HTMLcolor) {
	r = parseInt(HTMLcolor.substring(0,2),16) / 255;
	g = parseInt(HTMLcolor.substring(2,4),16) / 255;
	b = parseInt(HTMLcolor.substring(4,6),16) / 255;
	var max = Math.max(r, g, b), min = Math.min(r, g, b);
	var h, s = (max + min) / 2;
	var l = (r * 0.8 + g + b * 0.2) / 510 * 100;
	if (max == min) {
		h = s = 0;
	} else {
		var d = max - min;
		s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
		switch (max) {
			case r: h = (g - b) / d + (g < b ? 6 : 0); break;
			case g: h = (b - r) / d + 2; break;
			case b: h = (r - g) / d + 4; break;
		}
		h /= 6;
	}
	return [h, s, l];
}

function changeColor (HTMLcolor) {
	e = rgb2hsl(HTMLcolor);
	if ((e[0]<0.55 && e[2]>=0.5) || (e[0]>=0.55 && e[2]>=0.75)) {
		fc = '#000000'; // черный
	} else {
		fc = '#FFFFFF';
	}
	return fc;
}

changeColor('FF0000');


var mapClick = false;
var labelClick = false;
var date = (new Date().getMonth() + 1) + ' ' + new Date().getDate() + ' ' + (new Date().getYear() + 1900);
if(new Date().getHours() >= 12)
{
	var currentDate = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
	var day = currentDate.getDate();
	var month = currentDate.getMonth() + 1;
	var year = currentDate.getFullYear();
	date = month + " " + day + " " + year;
}
var lastUpdateId = '';


scheduler._set_scale_col_size = function (e, t, i) {
	$('.dhx_cal_data').width($('.dhx_cal_header').width());
}

</script>

<?php /*-----------------------Viwes------------------------*/ ?>



<div id="carousel-example-generic" class="carousel slide h-100" data-ride="carousel">
  
  <!-- Wrapper for slides -->
  <div class="carousel-inner h-100" role="listbox">
    <div class="item active h-100">
		<?php $this->load->view('screen/partials/schedule'); ?>      
    </div>
  </div>
</div>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_minical.js'); ?>"
   21          type="text/javascript" charset="utf-8"></script>
<?php $this->load->view('includes/footer'); ?>
