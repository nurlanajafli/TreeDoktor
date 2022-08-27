var ScheduleCommon = function(){
	var config = {

		ui:{
			week: {
				filter_user: '#week-member-filter option:selected',
				filter_crew: '#week-crew_type-filter option:selected'
			}
		},

		events:{
			refresh_btn: '#refreshList',
			show_team_markers: ".show-team-markers"
		},

		route:{

		},

		templates:{
			timeline_tooltip:'#timeline-tooltip-tmp',
			event_sticker: '#event-sticker-tpl'
		}
	};

	var model = {
		workorder_statuses: [],
	};

	let _templates = {
		init_templates:function () {
			_templates.tooltip_template();
			_templates.sticker();
		},
		//moment(event.end_date).format(dateFormatJS.toUpperCase() + " " + dateTimeFormat)
		tooltip_template: function () {
			scheduler.templates.tooltip_text = function(start,end,ev){
				var renderView = {template_id:config.templates.timeline_tooltip, render_method:'variable', data:[ev] , helpers:[]};
				return Common.renderView(renderView);
			};
		},
		sticker: function () {
			scheduler.getStickerContent = function(ev, disabled) {
				var renderView = {template_id:config.templates.event_sticker, render_method:'variable', data:[ev] , helpers:ScheduleCommon.helpers};
				ev.text = Common.renderView(renderView);
				return ev.text;
			};
		},

		crewsListHeight: function(){
			return $('#crewsList').is(':visible') ? $('.crews-list-container').height():0;
		},
	};
	var _private = {
		init:function(){
			setTimeout(function () {
				$.ajax({
					type: 'POST',
					url: baseUrl + 'schedule/ajax_get_traking_position',
					data: {},
					global: false,
					success: function(resp){
						vehicles = resp;
						if(map !== undefined)
							displayVehicles();
						return false;
					},
					dataType: 'json'
				});
			}, 3000);

			_templates.init_templates();
		},

		refreshSchedule:function () {
			switch ($.cookie('scheduler_mode')) {
				case "timeline":
					ScheduleTimeline.resetTeams();
					break;
				case "week":
					ScheduleWeek.resetTeams();
					break;
				case "month":
					ScheduleMonth.resetTeams();
					break;
				default:
					ScheduleUnit.resetTeams();
					break;
			}
		}
	};

	let public = {
		active_events:{},
		scheduled_events: {},

		init:function(){
			$(document).ready(function(){
				public.setWorkorderStatuses();
				public.events();
				_private.init();
			});
		},

		events:function(){

			$(config.events.refresh_btn).on('click', _private.refreshSchedule);

			scheduler.attachEvent("onMouseMove", function(event_id, e){ // (scheduler event_id, browser event)
				var ev = e||window.event;
				var target = ev.target||ev.srcElement;
				var is_hover_event = (target.closest('.eventSticker') || target.closest('.dhx_cal_event_line') || target.closest('.dhx_cal_event_line_start'));
				if (!event_id || !is_hover_event) {
					dhtmlXTooltip.delay(dhtmlXTooltip.hide, dhtmlXTooltip , []);
				}
			});
		},

		detachEvents: function(current_mode){
			if(!Object.keys(public.active_events).length)
				return true;
			events = public.active_events;
			if(current_mode!=undefined && current_mode)
				events = {current_mode: public.active_events[current_mode]};


			for (const [key, items] of Object.entries(events)){

				if(key == scheduler.getState().mode && (current_mode==undefined || !current_mode))
					return;

				if(items==undefined || !items.length)
					return;

				items.forEach(function (value) {
					scheduler.detachEvent(value);
				});
				public.active_events[key] = [];
			}
		},

		setWorkorderStatuses: function(statuses){
			model.workorder_statuses = (statuses!=undefined)?statuses:scheduleGlobal.workorder_statuses;
			return true;
		},

		scheduleBodyHeight: function(){

			var height_menus = $('.dhx_cal_navline').height() + $('.dhx_cal_header').height();
			height_menus = height_menus + _templates.crewsListHeight();
			var body_height = $('#content').height() - height_menus;
			$('.dhx_cal_data').css('height', body_height + 'px');
		},

		get_current_scheduled_event:function () {
			var event_id = scheduler.getState().lightbox_id;
			var event = scheduler.getEvent(event_id);
			return event;
		},

		get_scheduled_teams:function(){
			var events = scheduler.getEvents();

			if(!events.length)
				return [];

			var result = [];
			var exist_key = {};
			events.forEach(function(item){
				if(item.team!=undefined && exist_key[item.team.team_id]==undefined) {
					result.push(item.team);
					exist_key[item.team.team_id] = item.team.team_id;
				}
			});

			return result;
		},

		get_target_team: function(){

			var event = public.get_current_scheduled_event();
			if(event!=undefined && event.team != undefined)
				return event.team;

			var scheduled_teams = public.get_scheduled_teams();

			var team_id = (event!=undefined)?event.section_id:false;
			if(event!=undefined && event.event_crew_id!=undefined)
				team_id = event.event_crew_id;

			if(team_id && scheduled_teams[team_id] != undefined)
				return scheduled_teams[team_id];

			var mode = scheduler.getState().mode;
			var modeClass = window["Schedule"+jsUcfirst(mode)];
			var team = {};

			if(typeof modeClass !== "undefined" && typeof modeClass.getTeam !== "undefined" && team_id){
				team = modeClass.getTeam(team_id);
			}

			return team;
		},

		set_scheduled_workorders: function(){
			var active_teams = [];

			evs = scheduler.getEvents().sort(function(a,b){ return (a.start_date > b.start_date ? 1 : -1); }).filter(item => item.wo_id != undefined);

			var events_by_teams = Common.groupBy(evs, 'event_crew_id', 'section_id');

			$(config.events.show_team_markers).each(function () {
				if($(this).prop('checked'))
					active_teams.push($(this).val());
			});

			var result = {
				"all_events":{},
				"events":{},
				"current":public.get_current_scheduled_event(),
				"directions": {}
			};

			var current_team_id = false;
			if(result['current'] !=undefined)
				current_team_id = (result['current'].event_crew_id!=undefined)?result['current'].event_crew_id:result['current'].section_id;

			if(!$(config.events.show_team_markers).length && current_team_id){
				active_teams.push(current_team_id);
			}

			scheduler.getEvents().reduce(function(rv, event) {
				if(event.wo_id != undefined){
					if(event.team.team_id!=undefined && events_by_teams[event.team.team_id] != undefined)
					{
						if(result['directions'][event.team.team_id]==undefined){
							result['directions'][event.team.team_id] = {
								'team_id': event.team.team_id,
								'color': event.team.team_color,
								'addresses':[],
								'events':[]
							};
						}
						result['directions'][event.team.team_id].addresses.push(event.lead_address.replaceAll(' ', '+').replaceAll('#', '') + '+' + event.lead_city + '+' + event.lead_state + '+' + event.lead_country);
						result['directions'][event.team.team_id].events.push(event);
						ev_index = events_by_teams[event.team.team_id].findIndex(function (item) {
							return (item.id == event.id);
						});

						event['pointer'] = (ev_index==-1)?null:(ev_index + 1);
					}

					if(result['all_events'][event.wo_id]==undefined){
						result['all_events'][event.wo_id] = [];
					}
					result['all_events'][event.wo_id].push(event);

					if(active_teams.findIndex(function(team, index) { return (team == event.team.team_id) })!=-1){
						if(result['events'][event.wo_id]==undefined)
							result['events'][event.wo_id] = [];

						result['events'][event.wo_id].push(event);
					}
				}
			}, {});

			public.scheduled_events = result;

			return result;
		},

		getWeekRequestConditions: function(){
			var request = {};

			request['from'] = moment(scheduler.getState().min_date).format("YYYY-MM-DD");
			request['to'] = moment(scheduler.getState().max_date).format("YYYY-MM-DD");
			request['mode'] = scheduler.getState().mode;

			if(scheduler.getState().mode=='week'){
				request['user_id'] = $(config.ui.week.filter_user).val();
				request['team_crew_id'] = $(config.ui.week.filter_crew).val();
			}

			return request;
		},

		helpers: {
			getWorkorderStatuses: function(){
				return model.workorder_statuses;
			}
		},
	};

	public.init();
	return public;
}();

var uPressed = false;
var lock = false;
var lastUpdateId = '';
var sections = [];
var bakSections = [];
var processUpdateSections = false;
var infowindow = false;
var dp = {};
var colors = [];
var woStatusesColors = [];
var reasonsAbsence = [];
var trackerItems = [];
var bonuses = {};
var copy = {};
var vehicles = [];
var myInfoWindowOptions = [];
var vehMarkers = [];
var objMarkers = [];
var objects = [];
var infoWindow;
var crewsListVisible = true;
var map; /*, directionsService;*/
var newEventId = 0;
var mapNode = 0;

//var mapRoutes, routes = [], directionsRenderer = [];

function show_minical(){

    if (scheduler.isCalendarVisible()){
        scheduler.destroyCalendar();
    } else {
        scheduler.renderCalendar({
            position:"dhx_minical_icon",
            date:scheduler._date,
            navigation:true,
            handler:function(date,calendar){
            	var mode = scheduler.getState().mode;
                scheduler.setCurrentView(date, mode);
                scheduler.destroyCalendar()
            }
        });
    }
}

function setMyColorpicker (elem) {
	$(elem).colpick({
		submit: 0,
		colorScheme: 'dark',
		onChange: function (hsb, hex, rgb, el, bySetColor) {
			$(el).css('background-color', '#' + hex);
			if (!bySetColor) {
				$(el).val('#' + hex);
			}
		}
	}).keyup(function () {
		$(this).colpickSetColor(this.value);
	});
	$('.mycolorpicker').each(function () {
		var current_color = $(this).val();
		var current_color_short = current_color.replace(/^#/, '');
		$(this).colpickSetColor(current_color_short);
	});
};

$(document).ready(function () {

	$(document).on('click', '.dhx_cal_data .dhx_scale_holder', function(){
		if(uPressed) {
			var sectionNumber = $('.dhx_cal_data .dhx_scale_holder').index($(this));
			var crewId = $('.dhx_cal_header .dhx_scale_bar:eq(' + sectionNumber + ') a[data-crew_id]').data('crew_id');
			$('.teams-stat-block[data-stat-team-id="' + crewId + '"] .teams-stat-overlay').click();
		}
	});

	$(document).on('click', '.dhx_cal_header .dhx_scale_bar', function(){
		if(uPressed) {
			var crewId = $(this).find('a[data-crew_id]').data('crew_id');
			$('.teams-stat-block[data-stat-team-id="' + crewId + '"] .teams-stat-overlay').click();
		}
	});

	$(document).on('click', '.team-amount, .team-hours', function(){
		if(uPressed) {
			var crewId = $(this).data('team-id');
			$('.teams-stat-block[data-stat-team-id="' + crewId + '"] .teams-stat-overlay').click();
		}
	});

	$(document).on('click', 'ul.ui-sortable[data-bonus-team-id]', function(){
		if(uPressed) {
			var crewId = $(this).data('bonus-team-id');
			$('.teams-stat-block[data-stat-team-id="' + crewId + '"] .teams-stat-overlay').click();
		}
	});

	$(document).on('click', '.crew-overlay-list', function(){
		if(uPressed) {
			var crewId = $(this).parents('ul:first').data('bonus-team-id');
			$('.teams-stat-block[data-stat-team-id="' + crewId + '"]').find('.teams-stat-overlay.show:last').click();
		}
	});

	$(document).on('click', '.crew-overlay', function(){
		if(uPressed) {
			var crewId = $(this).next().data('crew_id');
			$('.teams-stat-block[data-stat-team-id="' + crewId + '"]').find('.teams-stat-overlay.show:last').click();
		}
	});

	$(document).on('keypress', 'body', function(event) {
		if(event.key == "u") {
			uPressed = true;
		}
	});

	$(document).on('keyup', 'body', function(event) {
		if(event.key == "u") {
			uPressed = false;
		}
	});

	$(document).on('click', '.dhx_scale_bar .moveToolFromTeam', function () {
		var sttId = $(this).attr('data-stt-id');
		var date = scheduler.getState().date;
		var dateYMD = date.getFullYear() + '-' + leadZero(date.getMonth() + 1, 2) + '-' + leadZero(date.getDate(), 2);
		$(this).parents('li:first').remove();
		$.ajax({
			global: false,
			method: "POST",
			data: {stt_id:sttId,date:dateYMD},
			url: base_url + "schedule/ajax_delete_tool",
			dataType:'json',
			success: function(response){
				if (response.status != 'ok')
					alert('Ooops! Error');
				else {
					lastUpdateId = response.update.update_id;
				}
			}
		});
	});

	$(document).on('click', '.moveFromCrew', function () {
		var obj = $(this).parent();
		var emp_id = $(this).parent().data('employee_id');
		var emp_field_worker = $(this).parent().data('field_worker');
		var field_worker = emp_field_worker ? ' data-field_worker="1"' : '';
		var date = scheduler.getState().date;
		var dateYMD = date.getFullYear() + '-' + leadZero(date.getMonth() + 1, 2) + '-' + leadZero(date.getDate(), 2);
		var crew_id = $(this).parents('.dhx_scale_bar:first').find('a[data-crew_id]:first').data('crew_id');
		var div = document.createElement('div');
		$(div).append($(obj).html());
		$(div).find('.reasonTitle').remove();
		$(div).find('.moveFromCrew').remove();
		var emp_name = $.trim($(div).text().replace(' x', ''));
		var emailid = $(this).parent().data('emailid');
		$(obj).remove();

		var empClass = emp_field_worker ? 'primary' : 'warning';
		if(emp_field_worker)
			$('.emp-dropdown').find('.freeMembersTitle').not('.dayOff').after('<li class="label bg-' + empClass + ' ui-draggable addMember b-a"' + field_worker + ' data-emailid="' + emailid + '" data-emp_id="' + emp_id + '" style="text-shadow: 1px 1px #626262;">' + emp_name + '</li>');
		else
			$('li.addMember[data-field_worker="1"]').last().after(function() {
				let label = '<li class="label bg-' + empClass + ' ui-draggable addMember b-a"' + field_worker + ' data-emailid="' + emailid + '" data-emp_id="' + emp_id + '" style="text-shadow: 1px 1px #626262;">' + emp_name + '</li>';
				$('li.label.bg-warning').each(function(i,v) {
					if(emp_name < $.trim($(v).context.firstChild.textContent)) {
						$(v).before(label);
						return false;
					}
				});
			});

		if(emp_field_worker)
			$('.freeMembers').append('<li class="label bg-primary ui-draggable addMember"' + field_worker + ' data-emailid="' + emailid + '" data-emp_id="' + emp_id + '" style="text-shadow: 1px 1px #626262;">' + emp_name + '</li>');

		order($('li.label.bg-warning'));

		removeFromCrewList(emp_id);

		if($('li.label[data-item-id] a[data-driver_id="' + emp_id + '"]').length) {
			var dRteamId = crew_id;
			var dRitemId = $('li.label[data-item-id] a[data-driver_id="' + emp_id + '"]').parent().attr('data-item-id');
			var dRdriverId = '';
			$.ajax({
				global: false,
				method: "POST",
				data: {team_id:dRteamId,user_id:emp_id,driver_id:dRdriverId},
				url: base_url + "schedule/ajax_change_driver",
				dataType:'json',
				success: function(response) {
					var text = '(N/A)';
					$('li.label[data-item-id] a[data-driver_id="' + emp_id + '"]').text(text);
					$('li.label[data-item-id] a[data-driver_id="' + emp_id + '"]').attr('data-driver_id', dRdriverId);
				}
			});
		}

		if(!crew_id)
		{
			var teamLead = parseInt($('#step1 #crewLeader').val());
			if(teamLead == emp_id)
				$('#step1 #crewLeader').val("");
			$.ajax({
				global: false,
				method: "POST",
				data: {employee_id:emp_id,date:dateYMD},
				url: base_url + "schedule/ajax_delete_member_absence",
				dataType:'json',
				success: function(response){
					if (response.status != 'ok')
						alert('Ooops! Error');
					else
						lastUpdateId = response.update.update_id;
				}
			});
			return false;
		}
		$('.teamLead[data-team="' + crew_id + '"] option[value="' + emp_id + '"]').remove();

		$.ajax({
			global: false,
			method: "POST",
			data: {employee_id:emp_id, crew_id:crew_id, date:dateYMD},
			url: base_url + "schedule/ajax_delete_member",
			dataType:'json',
			success: function(response){
				if (response.status != 'ok')
					alert('Ooops! Error');
				else
				{
					lastUpdateId = response.update.update_id;
				}
				var height = $('#crewsList').is(':visible') ? $('.crews-list-container').height() : 0;
				$('.team-amount').animate({bottom: height+'px'});
				$('.team-hours').animate({bottom: (height+30)+'px'});
				$('.teams-amount').animate({bottom: height+'px'});
				$('.teams-hours').animate({bottom: (height+30)+'px'});
				$('.teams-stat-btn').animate({bottom: (height+60)+'px'});
			}
		});

		return false;
	});

	$(document).on('click', '.dhx_scale_bar .moveItemFromCrew', function () {
		$(this).text('');
		var obj = $(this).parent();
		var item_id = $(obj).data('eq_id');
		var date = scheduler.getState().date;
		var dateYMD = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
		var crew_id = $(this).parents('.dhx_scale_bar:first').find('a[data-crew_id]:first').data('crew_id');
		var item_name = $.trim($(obj).text().replace(' x', ''));
		var updatedSect = false;

		if($('li.label[data-item-id="' + item_id + '"][data-item_group_id="16"]').length) {
			var oldDriverId = $('li.label[data-item-id="' + item_id + '"] a[data-driver_id]').attr('data-driver_id');
			$('li.label.employee_' + oldDriverId).find('.driverFor[data-driver-for-item="' + item_id + '"]').remove();
		}

		deleteEquipmentCallback = function(resp){
			if (resp.status != 'ok')
				alert('Ooops! Error');
			else
			{
				lastUpdateId = resp.update.update_id;
			}
			var height = $('#crewsList').is(':visible') ? $('.crews-list-container').height() : 0;
			$('.team-amount').animate({bottom: height+'px'});
			$('.team-hours').animate({bottom: (height+30)+'px'});
			$('.teams-amount').animate({bottom: height+'px'});
			$('.teams-hours').animate({bottom: (height+30)+'px'});
			$('.teams-stat-btn').animate({bottom: (height+60)+'px'});
		}

		Equipment.deleteEquipment({item_id:item_id, crew_id:crew_id, date:dateYMD}, deleteEquipmentCallback);
		return false;
	});

	/*
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
	*/
	/*
	$(document).on('mouseenter', '.dhx_cal_event', function(e){

		if(scheduler._mode && scheduler._mode == 'unit'){
			return false;
		}

		var eventID = $(this).attr('event_id');
		$('[event_id]').not('[event_id=' + eventID + ']').find('.woStatus').removeAttr('style');

		if($('.dhx_cal_data').scrollTop())
			$('[event_id='+eventID+']').find('.woStatus').css('position', 'absolute');
		return false;
	});

	$(document).on('mouseleave', '.dhx_cal_event', function(e){
		$('[event_id]').find('.woStatus').removeAttr('style');
		$('[event_id]').find('.woStatus').removeClass('open');
		return false;
	});

	$('.dhx_cal_data').on('scroll', function(){
		$('.woStatus:visible').css('position', 'absolute');
	});
	*/

	$(document).on('click', 'html', function(event) {
		$('.bonusesList').parent().removeClass('open');
		//if ($(event.target).closest('.popover').length || $(event.target).closest('.bonusesList').length || $(event.target).closest('.dhx_cal_event').length || $(event.target).closest(".dhx_cal_light").length || $(event.target).closest("#scheduler_here").length) return true;
		//scheduler.cancel_lightbox();
		//scheduler.resetLightbox();
		//event.stopPropagation();
	});

	$('html').on('click', function (e) {
		if ($(e.target).data('toggle') == 'popover') {
			$('.popover.fade.bottom.in').css({'left': '5px', 'max-width': '98%', 'width': '100%'});
			$('.popover.fade.bottom.in .arrow').css({'left': ($(e.target).offset().left - $(e.target).parent().offset().left - 10 + (($(e.target).width() + 24) / 2)) + 'px'});
			$('[data-original-title]').not(e.target).popover('hide');
		}
	});

	$(document).on('click', '#client-notes-tab-content a', function(){
		if($(this).attr('href') && $(this).attr('href').indexOf('#') == -1)
		{
			window.open($(this).attr('href'), '_blank');
			window.focus();
			return false;
		}
	});

	$(document).on('click', '.selectService', function(){
		var woId = $(this).parents('tr:first').data('workorder-id');
		var selectedServices = $(this).closest('.map-workorder-details').data('services');

		if(selectedServices && typeof selectedServices == 'string')
			selectedServices = eval("(" + $(this).closest('.map-workorder-details').data('services') + ")");
		if(typeof selectedServices == 'undefined' || selectedServices == '')
			selectedServices = {};
		if(!selectedServices)
			selectedServices = {};
		var serviceId = $(this).parents('tr:first').data('estimate_service_id');
		if($(this).is('.btn-warning'))
		{
			$(this).find('i').removeClass('fa-circle-o').addClass('fa-check');
			$(this).removeClass('btn-warning').addClass('btn-success').addClass('active');
			selectedServices[serviceId] = serviceId;
		}
		else
		{
			$(this).find('i').removeClass('fa-check').addClass('fa-circle-o');
			$(this).removeClass('btn-success').removeClass('active').addClass('btn-warning');
			delete selectedServices[serviceId];
		}


		$(this).closest('.map-workorder-details').data('services', selectedServices);
		return false;
	});

	$(document).on('click', '.crewsList', function(){
		if(scheduler.getState().mode != 'unit')
		{
			$('#crewsList').parent().slideUp();
			return false;
		}
		if($('#crewsList ul').length != 1)
		{
			if($('#crewsList').is(':visible'))
			{
				$.each($('#crewsList ul'), function(key, val){
					if(key && $(val).offset().left <= $(val).prev().offset().left)
						$(val).addClass('clear');
				});
			}
		}
		crewsListVisible = crewsListVisible ? false : true;
		var height = crewsListVisible ? 'auto' : '0';
		$('#crewsList').parent().css({'height':height});
		recalcSizes();
		ScheduleCommon.scheduleBodyHeight();
		return false;
	});

	$(document).on('click', '.selectCrew', function(){
		var crew_id = $(this).data('value');
		var crew_name = $(this).html();
		var obj = $(this).parents('#crewsSelect');
		$(obj).find('.dropdown-label').html(crew_name);
		$(obj).find('li.active').removeClass('active');
		$(this).parents('li:first').addClass('active');
		$('.dhx_cal_ltext select').val(crew_id);
		$(obj).removeClass('open');
		return false;
	});
	/*
	$(document).on('click', '#woSearchShow', function(){
		$('#searchSchedule').find('[name="search_keyword"]').focus();
		return false;
	});*/

	$(document).on('change', '.wizardStep', function(){
		$('.btn-next').click();
		return false;
	});

	$(document).on('change', '.changeDriver', function(){
		var teamId = $(this).attr('data-equipment_team_id');
		var itemId = $(this).attr('data-equipment_id');
		var driverId = $(this).val();
		if(driverId != '' && $('li.label.it_' + itemId).attr('data-item_group_id') == 16 && !$('.employee_' + driverId).find('.driverFor').length) {
			var tpl = `
				<span class="driverFor" data-driver-for-item="` + itemId + `"> (` + $('li.label.it_' + itemId).attr('data-item_code') + `)</span>
			`;
			$('.employee_' + driverId).append(tpl);
		}
		if(driverId == '' && $('.crew_' + teamId).find('li.label[data-emp_id] .driverFor[data-driver-for-item="' + itemId + '"]').length)
			$('.crew_' + teamId).find('li.label[data-emp_id] .driverFor[data-driver-for-item="' + itemId + '"]').remove();
		$.ajax({
			global: false,
			method: "POST",
			data: {team_id:teamId,item_id:itemId,driver_id:driverId},
			url: base_url + "schedule/ajax_change_driver",
			dataType:'json',
			success: function(response) {
				$('li.label[data-item-id="' + itemId + '"] a').popover('hide');
				var text = '(N/A)';
				if(driverId) {
					text = '(' + $('.changeDriver option[value="' + driverId + '"]').attr('data-emailid') + ')';
				}
				$('li.label[data-item-id="' + itemId + '"] a').text(text);
				$('li.label[data-item-id="' + itemId + '"] a').attr('data-driver_id', driverId);
			}
		});
	});

	$('.btn-stats ').click(function() {
		$('.schedule-stats').toggleClass('opened');
	});

	$(document).on('click', '.btn-next', function(){

		if($('.wizard').wizard('selectedItem').step == 2){
			$('#step2 .addMember[data-emp_id="' + $('#crewLeader').val() + '"]').click();
		}

		return false;
	});

	$(document).on('change', '#crewType', function(){
		$('.mycolorpicker').val($('#crewType option[value="' + $(this).val() + '"]').data('color'));
		var crewInfo = $('#crewType option[value="' + $('#crewType').val() + '"]').text();

		crewInfo += ':';
		$('#step2 .crewInfo').text(crewInfo);
		setMyColorpicker($('.mycolorpicker'));
	});

	$(document).on('click', '.colpick, .teamLead, .deleteTeam, .teamColor, .emp-dropdown, .bonusesList', function(e) {
		e.stopPropagation();
	});

	$(document).on('change', '#crewLeader', function(){
		var crewInfo = $('#crewType option[value="' + $('#crewType').val() + '"]').text();

		crewInfo += ':';
		$('#step2 .crewInfo').text(crewInfo);
	});

	$(document).on('click', '#step2 .addMember', function(){
		var obj = $('#step2 .emp-dropdown');
		var emp_id = $(this).data('emp_id');
		var emp_name = $(this).text();
		if(isNaN(parseInt($('#crewLeader').val()))){
			$('#crewLeader').val(emp_id).trigger("change");
		}

		$(obj).find('.crewInfo').after('<li class="label bg-info" data-employee_id="' + emp_id + '">' + emp_name + ' <a href="#" class="moveFromCrew text-white">x</a></li>');
		$('#step2 .addMember[data-emp_id="' + emp_id + '"]').remove();
		return false;
	});

	$(document).on('click', '#step2 .moveFromCrew', function(){
		var obj = $('#step2 .emp-dropdown');
		var emp_id = $(this).parent().data('employee_id');

		var emp_name = $(this).parent().text().replace(' x',"");
		$(obj).find('.line-members').after('<li class="label bg-primary ui-draggable addMember b-a" style="text-shadow: 1px 1px #626262;" data-emp_id="' + emp_id + '">' + emp_name + '</li>');
		$('#step2 [data-employee_id="' + emp_id + '"] .moveFromCrew').parent().remove();
		return false;
	});

	$(document).on('click', '#step3 .addItem', function(){
		var obj = $('#step3 .emp-dropdown');
		var item_id = $(this).data('item_id');
		var item_name = $(this).text();
		$(obj).find('.eqInfo').after('<li class="label bg-warning" data-original-style="' + $(this).attr('style') + '" data-eq_id="' + item_id + '">' + item_name + ' <a href="#" class="moveItemFromCrew text-white">x</a></li>');
		$('#step3 .addItem[data-item_id="' + item_id + '"]').remove();
		return false;
	});

	$(document).on('click', '#step3 .moveItemFromCrew', function(){
		var obj = $('#step3 .emp-dropdown');
		var item_id = $(this).parent().data('eq_id');
		var item_name = $(this).parent().text().replace(' x',"");
		$(obj).find('.line-items').after('<li class="label bg-danger ui-draggable addItem" style="' + $(this).parent().data('original-style') + '" data-item_id="' + item_id + '">' + item_name + '</li>');
		$('#step3 [data-eq_id="' + item_id + '"] .moveItemFromCrew').parent().remove();
		return false;
	});

	$(document).on('click', '.changeWoStatus', function(){
		var obj = $(this).parents('.woStatus');
		var woId = $(obj).data('woid');
		var status = $(this).data('value');
		var statusName = $.trim($(this).text());
		var pre_status = $(obj).data('status');
		var eventId = $(obj).parents('[event_id]:first').attr('event_id');
		var currDate = scheduler.getState().date;
		var date = currDate.getFullYear() + '-' + leadZero(currDate.getMonth() + 1, 2) + '-' + leadZero(currDate.getDate(), 2);
		$.ajax({
			type: 'POST',
			url: baseUrl + 'workorders/ajax_change_workorder_status',
			data: {date:date,workorder_id:woId,pre_workorder_status:pre_status,workorder_status:status, eventId : eventId},
			global: false,
			success: function(resp){
				if(resp['status']=='error' && resp['message']!=undefined) {
					alert(resp['message']);
					return false;
				}
				ScheduleUnit.resetTeams();
				if(parseInt(status)==0)
					DamagesModal.init(woId, false);

			},
			dataType: 'json'
		});
		return false;
	});

	$(document).on('change', '.teamLead', function(){
		var leader_id = $(this).val();
		var team_id = $(this).data('team');
		var date = moment(scheduler.getState().date).format("YYYY-MM-DD");
		Common.request.send('/schedule/ajax_team_change_leader', {date:date,leader_id:leader_id,team_id:team_id}, function (response) {
			if(response.status == 'ok'){
				ScheduleUnit.resetTeams(team_id);
			}
		}, function (response) {}, false);

		return false;
	});

	$(document).on('click', '.saveNote', function(){
		var currDate = scheduler.getState().date;
		var date = currDate.getFullYear() + '-' + leadZero(currDate.getMonth() + 1, 2) + '-' + leadZero(currDate.getDate(), 2);
		var text = $('.day-note').val();
		$.ajax({
			global: false,
			method: "POST",
			data: {date:date,text:text},
			url: base_url + "schedule/ajax_save_note",
			dataType:'json',
			success: function(response){
				if(response.status != 'ok')
					alert('Ooops! Error!');
				else
					lastUpdateId = response.update.update_id;
			}
		});

		return false;
	});

	$(document).on('blur', '.teamColor', function(){
		if(this.defaultValue != $(this).val())
		{
			this.defaultValue = $(this).val();
			var team_id = $(this).data('team');
			var team_color = $(this).val();
			var currDate = scheduler.getState().date;
			var date = currDate.getFullYear() + '-' + leadZero(currDate.getMonth() + 1, 2) + '-' + leadZero(currDate.getDate(), 2);
			$('.colpick:visible').attr('style', 'position: absolute;');
			$.ajax({
				type: 'POST',
				url: baseUrl + 'schedule/ajax_team_change_color',
				data: {date:date,team_id:team_id,team_color:team_color},
				global: false,
				success:  function(resp){
					if(resp.status == 'ok')
					{
						$(this).parents('.dhx_scale_bar:first').find('a[data-crew_id]:first').data('color', team_color);
						colors[team_id] = team_color;
						ScheduleUnit.resetTeams(team_id);
					}
				},
				dataType: 'json'
			});
		}
		return false;
	});

	$(document).on('focus', '.team-note, .hidden-team-note, .team-amount', function(){
		if(uPressed)
			$(this).blur();
		return false;
	});

	$(document).on('blur', '.team-note', function(){
		var teamId = $(this).parent().find('[data-crew_id]').data('crew_id');
		var teamNote = $(this).val();
		var currDate = scheduler.getState().date;
		var date = currDate.getFullYear() + '-' + leadZero(currDate.getMonth() + 1, 2) + '-' + leadZero(currDate.getDate(), 2);
		$.ajax({
			global: false,
			method: "POST",
			data: {team_id:teamId,team_note:teamNote,team_date:date},
			url: base_url + "schedule/ajax_save_team_note",
			dataType:'json',
			success: function(response){
				if(response.status != 'ok')
					alert('Ooops! Error!');
				else
				lastUpdateId = response.update.update_id;
			}
		});
	});

	$(document).on('blur', '.hidden-team-note', function(){
		var teamId = $(this).parent().find('[data-crew_id]').data('crew_id');
		var hidden_team_note = $(this).val();
		var currDate = scheduler.getState().date;
		var date = currDate.getFullYear() + '-' + leadZero(currDate.getMonth() + 1, 2) + '-' + leadZero(currDate.getDate(), 2);

		$.ajax({
			global: false,
			method: "POST",
			data: {team_id:teamId, hidden_team_note:hidden_team_note,team_date:date},
			url: base_url + "schedule/ajax_save_team_note",
			dataType:'json',
			success: function(response){
				if(response.status != 'ok')
					alert('Ooops! Error!');
				else
					lastUpdateId = response.update.update_id;
			}
		});
	});

	$('.addSMS').on('click', function(){
		var obj = $(this);
		$(obj).attr('disabled', 'disabled');
		var obj = $(this);
		var sms = $(obj).parents('.modal-content:first').find('textarea.sms_text').val();
		var number = $(obj).parents('.modal-content:first').find('input.client_number').val();
		$.post(baseUrl + 'client_calls/send_sms_to_client', {PhoneNumber:number, sms:sms}, function (resp) {
				alert('SMS was sent. Thanks');
				$('#sms-').modal().hide();
				$(obj).removeAttr('disabled');
				//document.location.href = document.location.href;
			return false;
		}, 'json');
		return false;
	});

	$(document).on('click', '.crewBonuses', function(){
		if($(this).is('.open'))
			$(this).removeClass('open');
		else
		{
			$('.bonusesList').parent().removeClass('open');
			$(this).addClass('open');
		}
		return false;
	});

	$(document).on('click', '.possibleBonuses [data-bonus_type_id]', function(){
		var bonus_type_id = $(this).data('bonus_type_id');
		var team_id = $(this).parents('.crewBonuses:first').data('bonus-team-id');
		if(!bonus_type_id)
			return false;
		$(this).appendTo('.crew_' + team_id + ' .recivedBonuses');
		$(this).append('<a href="#" class="rmBonus">x</a>');
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_add_bonus',
			data: {team_id:team_id,bonus_type_id:bonus_type_id},
			global: false,
			success: function(resp){
				if(resp.status == 'error')
				{
					$('.crew_' + team_id + ' .recivedBonuses [data-bonus_type_id="' + bonus_type_id + '"]').appendTo('.crew_' + team_id + ' .possibleBonuses');
					$('.crew_' + team_id + ' .possibleBonuses [data-bonus_type_id="' + bonus_type_id + '"] .rmBonus').remove();
					alert('Error. Please Try Again Later.');
				}
				return false;
			},
			dataType: 'json'
		});
	});

	$(document).on('click', '.rmBonus', function(){
		var bonus_type_id = $(this).parent().data('bonus_type_id');
		var team_id = $(this).parents('.crewBonuses:first').data('bonus-team-id');
		var bonus_id = $(this).parent().data('bonus_id');
		if(bonus_type_id)
		{
			$(this).parent().appendTo('.crew_' + team_id + ' .possibleBonuses');
			$(this).remove();
		}
		else
		{
			$(this).parent().remove();
		}
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_delete_bonus',
			data: {team_id:team_id,bonus_type_id:bonus_type_id,bonus_id:bonus_id},
			global: false,
			success: function(resp){
				if(resp.status == 'error' && bonus_type_id)
				{
					$('.crew_' + team_id + ' .possibleBonuses [data-bonus_type_id="' + bonus_type_id + '"]').appendTo('.crew_' + team_id + ' .recivedBonuses');
					$('.crew_' + team_id + ' .recivedBonuses [data-bonus_type_id="' + bonus_type_id + '"]').append('<a href="#" class="rmBonus">x</a>');
					alert('Error. Please Try Again Later.');
				}
				return false;
			},
			dataType: 'json'
		});
	});

	$(document).on('click', '.customBonus', function(){
		var obj = $(this);
		var title = $(this).parent().find('.bonusTitle').val();
		var percents = $(this).parent().find('.bonusPercents').val();
		if(!title || !percents)
		{
			alert('Ooops! Error.');
			return false;
		}
		var team_id = $(this).parents('.crewBonuses:first').data('bonus-team-id');
		var labelClass = (percents > 0) ? 'success' : 'danger';
		var symbol = (percents > 0) ? '+' : '';
		$(this).parents('.bonusesList').find('.recivedBonuses').append('<li class="tmpBonus label p-5 bg-' + labelClass + '" data-bonus_type_id="0" style="display: inline-block;margin-left: 2px;">' + symbol + percents + '% - ' + title + ' <a href="#" class="rmBonus">x</a></li>');
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_add_bonus',
			data: {team_id:team_id,bonus_title:title,bonus_amount:percents},
			global: false,
			success: function(resp){
				if(resp.status == 'error')
				{
					$('.tmpBonus').remove();
					alert('Error. Please Try Again Later.');
				}
				else
				{
					$('.tmpBonus').attr('data-bonus_id', resp.bonus_id).removeClass('tmpBonus');
					$(obj).parent().find('.bonusTitle').val('');
					$(obj).parent().find('.bonusPercents').val('');
					$(obj).parent().find('.customBonus').attr('disabled', 'disabled');
				}
				return false;
			},
			dataType: 'json'
		});
		return false;
	});

	$(document).on("keypress keyup blur", '.bonusPercents', function (event) {
		if ((event.which < 48 || event.which > 57) && (event.which != 45)) {
			event.preventDefault();
		}
		if(!$(this).val())
			$(this).parent().find('.customBonus').attr('disabled', 'disabled');
		else
		{
			if($(this).parent().find('.bonusTitle').val())
				$(this).parent().find('.customBonus').removeAttr('disabled');
		}
	});

	$(document).on("keypress keyup blur", '.bonusTitle', function (event) {
		if(!$(this).val())
			$(this).parent().find('.customBonus').attr('disabled', 'disabled');
		else
		{
			if($(this).parent().find('.bonusTitle').val())
				$(this).parent().find('.customBonus').removeAttr('disabled');
		}
	});

	$(document).on('click', '.teams-stat-btn', function(){
		if($(this).find('i').is('.fa-caret-up'))
		{
			$(this).find('i').removeClass('fa-caret-up').addClass('fa-caret-down');
			$('.teams-stat-block').animate({bottom:$('.teams-amount').css('bottom')});
		}
		else
		{
			$(this).find('i').removeClass('fa-caret-down').addClass('fa-caret-up');
			$('.teams-stat-block').animate({bottom:'-100px'});
		}
		return false;
	});

	$(document).on('click', '.showFilter', function(){
		$('.filters').slideToggle('fast', function () {
			if($(this).has(':visible').length && $("#search-workorders-block").hasClass("hide")){
				$("#woSearchShow").trigger("click");
			}
		});
		Common.scrollToElement('workorders-filters-block', 'scheduleWorkordersScrollBlock');

		return false;
	});

	$(document).on('change', '.grinderFilter', function(){
		var selector = 'label.label-wo';
		var notSelector = '';
		var addSelector = '';
		var count = 0;
		var notCount = 0;
		$(selector).hide();
		$.each($('.filters .filter'), function(key, val){
			var attr = 'data-' + $(val).data('name');
			if($(val).val()) {
				count++;
				addSelector += '[' + attr + '="' + $(val).val() + '"]';
			}
			selector += addSelector;
		});

		$.each($('.filters .grinderFilter'), function(key, val){
			if(!$(val).is(':checked')) {
				if(notCount)
					notSelector += ',';
				notCount++;
				notSelector += '[data-equipment-' + $(val).val() + '="' + $(val).attr('data-option') + '"]';
			}
			selector += addSelector;
		});

		if(!addSelector)
			addSelector = 'div';
		$(selector).not(notSelector).show();
		if(count)
			$('.clearFilter').show();
		else
			$('.clearFilter').hide();
		$('.clearFilter .badge').text(count);

	});

	$(document).on('click', '.teams-stat-overlay', function() {
		if($('.popover').length)
			$('.popover').hide();
		var obj = $(this);
		var team_id = $(this).parent().data('stat-team-id');
		var check = $(this).is('.show') ? 0 : 1;
		var amount = parseFloat($(this).parent().find('.actual-per-hour').text().replace(Common.get_currency(), ''));

		$.ajax({
			global: false,
			method: "POST",
			data: {team_id:team_id,check:check,amount:amount},
			url: base_url + "schedule/ajax_close_team",
			dataType:'json',
			success: function(){
				if(check)
				{
					$(obj).addClass('show');
					$(obj).find('div:first').text('Click To Unlock');
					$(obj).after($(obj)[0].outerHTML);
					$('a[data-crew_id="' + team_id + '"]').parent().prepend('<div class="crew-overlay"></div>');
					$('ul[data-bonus-team-id="' + team_id + '"]').prepend('<div class="crew-overlay-list"></div>');
					$('ul[data-bonus-team-id="' + team_id + '"]').removeClass('sortable');
					color = '#8ec165';
					if(amount < GOOD_MAN_HOURS_RETURN)
						color = '#fa5542';
					if(amount > GOOD_MAN_HOURS_RETURN && amount < GREAT_MAN_HOURS_RETURN)
						color = '#ffc333';
					if(amount > VERY_GREAT_MAN_HOURS_RETURN)
						color = 'linear-gradient(45deg,#277700 3%, #3bc63b 22%,#52b152 30%,#11e603 54%,#4ace04 72%,#277700 98%)';
					$(obj).parent().parent().find('[data-crew_id="'+team_id+'"]:first').parent().css('background', color);
				}
				else
				{
					$(obj).removeClass('show');
					$(obj).find('div:first').text('Click To Lock');
					$(obj).parent().find('.teams-stat-overlay.show').remove();
					$('a[data-crew_id="' + team_id + '"]').parent().find('.crew-overlay').remove();
					$('ul[data-bonus-team-id="' + team_id + '"]').find('.crew-overlay-list').remove();
					$('ul[data-bonus-team-id="' + team_id + '"]').addClass('sortable');
					$(obj).parent().parent().find('[data-crew_id="'+team_id+'"]:first').parent().css('background', '');
				}
			}
		});
	});

	$(document).on('mouseenter', '.teams-stat-overlay.show', function(){
		$(this).find('div:first').css('display', 'none');
		$(this).prev().find('div:first').css('display', 'none');
	});

	$(document).on('mouseleave', '.teams-stat-overlay.show', function(){
		$(this).find('div:first').css('display', 'block');
		$(this).prev().find('div:first').css('display', 'block');
	});

	$(document).on('click', '.changeEventPrice', function() {
		var eventId = $(this).data('id');
		var event = scheduler.getEvent(eventId);
		var eventPrice = parseFloat($(this).parent().find('.eventPrice-' + eventId).val().replace(',', '.'));
		$(this).parent().find('.eventPrice-' + eventId).val(eventPrice);
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_change_event_price',
			data: {id:eventId, event_price:eventPrice},
			global: false,
			success: function(resp){
				if(resp.status == 'ok') {
					//event.total_services_price = resp.total_services_price;
					event.event_price = resp.event_price;
					event.total_for_services = resp.total_for_services;
					$('.team-amount[data-team-id="' + event.section_id + '"]').text(resp.team_amount);
					scheduler.updateEvent(eventId);
				}
				return false;
			},
			dataType: 'json'
		});
	});

	$(document).on('click', '.changeEventDamage', function() {
		var eventId = $(this).data('id');
		var event = scheduler.getEvent(eventId);
		var eventDamage = parseFloat($(this).parent().find('.eventDamage-' + eventId).val().replace(',', '.'));
		$(this).parent().find('.eventDamage-' + eventId).val(eventDamage);

		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_change_event_damage',
			data: {id:eventId, event_damage:eventDamage},
			global: false,
			success: function(resp){
				if(resp.status == 'ok') {
					$('[data-stat-team-id="'+resp.team_id+'"]').find('.actual-per-hour').text(resp.team_amount);
					event.event_damage = eventDamage;
					scheduler.updateEvent(eventId);
				}
				return false;
			},
			dataType: 'json'
		});
	});

	$(document).on('click', '.changeEventComplain', function() {
		var eventId = $(this).data('id');
		var event = scheduler.getEvent(eventId);
		var eventComplain = parseFloat($(this).parent().find('.eventComplain-' + eventId).val().replace(',', '.'));
		$(this).parent().find('.eventComplain-' + eventId).val(eventComplain);
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_change_event_complain',
			data: {id:eventId, event_complain:eventComplain},
			global: false,
			success: function(resp){
				if(resp.status == 'ok') {
					event.event_complain = eventComplain;
					scheduler.updateEvent(eventId);
				}
				return false;
			},
			dataType: 'json'
		});
	});

	$(document).on('click', '.changeMHr', function() {
		var teamId = $(this).data('team-id');
		var obj = $('.team-hours[data-team-id="' + teamId + '"]');
		var teamMHr = parseFloat($(this).parent().find('.teamManHr-' + teamId).val().replace(',', '.'));
		$(this).parent().find('.teamManHr-' + teamId).val(teamMHr);
		$.ajax({
			type: 'POST',
			url: baseUrl + 'schedule/ajax_change_team_man_hours',
			data: {team_id:teamId, team_man_hours:teamMHr},
			global: false,
			success: function(resp){
				if(resp.status == 'ok') {
					$(obj).find('.teamManHoursText').text(teamMHr);
					$(obj).popover('hide');
					var popoverContent = $(obj).attr('data-content');
					var tempDiv = document.createElement('div');
					tempDiv.innerHTML = popoverContent;
					$(tempDiv).find('.teamManHr-' + teamId).attr('value', teamMHr);
					$(obj).attr('data-content', tempDiv.innerHTML);
				}
				return false;
			},
			dataType: 'json'
		});
	});

	$(document).on('click', '.team-hours', function(){
		var obj = this;
		var teamId = $(this).data('team-id');
		setTimeout(function(){
			if (!$('.teamManHr-' + teamId).length) {
		        $(obj).popover('show');
		    }
		    else {
		    	$(obj).popover('hide');
		    }
		}, 150);

	    return false;
	});

	$('body').on('click', function (e) {
		if ($(e.target).data('toggle') !== 'popover'
	        && $(e.target).parents('.popover.in').length === 0 && $(e.target).closest('[data-toggle="popover"]').length===0) {

	    	$('[data-toggle="popover"]').popover('hide');
	    }
	});

	$('[data-toggle="class:nav-xs"]').click(function(){
		setTimeout(function(){
			scheduler.callEvent("onAfterSchedulerResize");
		}, 100);
	});

	setTimeout(function() {
		loopingFunction();
	}, 15000);
});

function loopingFunction() {
	if(!lock) {
		checkAnyUpdates();
	}
	setTimeout(loopingFunction, 15000);
}

function checkAnyUpdates() {
	if(scheduler.getState().mode != 'unit')
		return false;
	let currDate = scheduler.getState().date;
	let sentDate = currDate.getFullYear() + '-' + leadZero(currDate.getMonth() + 1, 2) + '-' + leadZero(currDate.getDate(), 2);
	$.ajax({
		type: 'POST',
		url: baseUrl + 'schedule/ajax_check_any_updates',
		data: {date:sentDate},
		global: false,
		success: function(resp){
			if (resp[0] !== null && resp[0].update_id && resp[0].update_id != lastUpdateId)
				$('#attention').fadeIn();
			return false;
		},
		dataType: 'json'
	});
}

function reloadWorkspace() {
	ScheduleUnit.resetTeams();
}

function init() {
	if($.cookie('scheduler_mode')==undefined || $.cookie('scheduler_mode')==null)
		$.cookie('scheduler_mode', 'unit');

	trackerItems = scheduleGlobal.trackerItems;
	bonuses = scheduleGlobal.bonuses;
	copy = scheduleGlobal.copy;
	team_stat_tpl = scheduleGlobal.team_stat_tpl;
	objects = scheduleGlobal.objects;
	var employees = scheduleGlobal.employees;

	$.each(JSON.parse(scheduleGlobal.reasons), function(key, value){
		reasonsAbsence.push({reason_id:value.reason_id, reason_name:value.reason_name});
	});

	$.each(JSON.parse(scheduleGlobal.wostatuses), function(key, value){
		woStatusesColors[value.wo_status_id] = value.wo_status_color;
	});

	$.each(JSON.parse(scheduleGlobal.sections), function(key, value){
		colors[value.team_id] = value.team_color;
	});

	scheduler.locale.labels.unit_tab = "Day"
	scheduler.locale.labels.section_custom = "Assigned to";
	scheduler.locale.labels.timeline_tab ="Timeline";
	scheduler.config.first_hour = SCHEDULER_STARTS_FROM;
	scheduler.config.last_hour = SCHEDULER_ENDS_AT;
	scheduler.config.time_step  = 15;
	scheduler.textColor = '#000';
	scheduler.config.dblclick_create = false;
	scheduler.config.details_on_create = true;
	scheduler.config.details_on_dblclick = true;

	scheduler.config.multi_day = true;
	//scheduler.config.multi_day_height_limit = 30;
	scheduler.config.all_timed = true;

	scheduler.config.icons_select = [
		"icon_delete", "icon_price", "icon_profile", "icon_email",
	];
	if(typeof(MESSENGER) != 'undefined' && MESSENGER) {
		scheduler.config.icons_select.push("icon_sms");
	}

	scheduler.config.icons_select.push("icon_complain");
	scheduler.config.icons_select.push("icon_damage");
	scheduler.config.icons_select_orig = scheduler.config.icons_select;

	let timeFormat = "%H:%i";
	if(INT_TIME_FROMAT == 12){
		timeFormat = "%h:%i %a";
	}

	scheduler.config.hour_date = timeFormat;
	scheduler.config.xml_date = "%Y-%m-%d %H:%i";
	scheduler.config.default_date = "%l, %j %F %Y";
	scheduler.config.collision_limit = 1;

	if(scheduler.getState().mode=='unit' || $.cookie('scheduler_mode')=='unit'){
		scheduler.xy.scale_height = -60;
	}
	else{
		scheduler.xy.scale_height = 20;
	}

	scheduler.config.hour_size_px = 60;
	scheduler.config.lightbox.sections = [
		{name: "Workorders:", height: "", type: "template", map_to: "my_template"},
		{name: "custom", height: 23, type: "select", options: bakSections, map_to: "section_id"},
		{name: "time", height: 72, type: "time", map_to: "auto", time_format: ["%Y", "%m", "%d", "%H:%i"]}
	];

	scheduler.templates.hour_scale = function(date){
		var top = '00';
		var bottom = '30';
		var hour = date.getHours();
		if(INT_TIME_FROMAT == 12)
		{
			if(hour==0)
				top = 'AM';
			if(hour==12)
				top = 'PM';
			hour =  ((date.getHours()+11)%12)+1;
		}
		var html = '';
		var section_width = Math.floor(scheduler.xy.scale_width/2);
		var minute_height = Math.floor(scheduler.config.hour_size_px/2);
		html += "<div class='dhx_scale_hour_main' style='width: "+section_width+"px; height:"+(minute_height*2)+"px;'>"+hour+"</div><div class='dhx_scale_hour_minute_cont' style='width: "+section_width+"px;'>";
		html += "<div class='dhx_scale_hour_minute_top' style='height:"+minute_height+"px; line-height:"+minute_height+"px;'>"+top+"</div><div class='dhx_scale_hour_minute_bottom' style='height:"+minute_height+"px; line-height:"+minute_height+"px;'>"+bottom+"</div>";
		html += "<div class='dhx_scale_hour_sep'></div></div>";
		return html;
	};

	scheduler._click.buttons.price = function(id) {
		var ev = scheduler.getEvent(id);
		var input = '<div class="form-group m-b-none" style="width: 140px;">';
		input += '<input style="width: 100px;" class="form-control inline eventPrice-' + id + '" type="text" name="event_price" value="' + ev.event_price + '" + >';
		input += '<button class="btn btn-xs btn-success m-l-sm changeEventPrice" data-id="' + id + '"><i class="fa fa-check"></i></button>';
		input += '</div>';
		$('[event_id="' + id + '"]').find('.icon_price').attr('data-html', 1);
		$('[event_id="' + id + '"]').find('.icon_price').attr('data-content', input);
		if(!$('[event_id="' + id + '"]').find('.popover').length) {
			$('[event_id="' + id + '"]').find('.icon_price').popover('show');
		}
		return true;
	};
	scheduler._click.buttons.damage = function(id) {

		var ev = scheduler.getEvent(id);
		var input = '<div class="form-group m-b-none" style="width: 140px;">';
		input += '<input style="width: 100px;" class="form-control inline eventDamage-' + id + '" type="text" name="event_damage" value="' + ev.event_damage + '">';
		input += '<button class="btn btn-xs btn-success m-l-sm changeEventDamage" data-id="' + id + '"><i class="fa fa-check"></i></button>';
		input += '</div>';
		$('[event_id="' + id + '"]').find('.icon_damage').attr('data-html', 1);
		$('[event_id="' + id + '"]').find('.icon_damage').attr('data-content', input);
		if(!$('[event_id="' + id + '"]').find('.popover').length) {
			$('[event_id="' + id + '"]').find('.icon_damage').popover('show');
		}
		return true;
	};
	scheduler._click.buttons.complain = function(id) {

		var ev = scheduler.getEvent(id);
		var input = '<div class="form-group m-b-none" style="width: 140px;">';
		input += '<input style="width: 100px;" class="form-control inline eventComplain-' + id + '" type="text" name="event_complain" value="' + ev.event_complain + '">';
		input += '<button class="btn btn-xs btn-success m-l-sm changeEventComplain" data-id="' + id + '"><i class="fa fa-check"></i></button>';
		input += '</div>';
		$('[event_id="' + id + '"]').find('.icon_complain').attr('data-html', 1);
		$('[event_id="' + id + '"]').find('.icon_complain').attr('data-content', input);
		if(!$('[event_id="' + id + '"]').find('.popover').length) {
			$('[event_id="' + id + '"]').find('.icon_complain').popover('show');
		}
		return true;
	};

	scheduler._click.buttons.profile = function(id) {
		var ev = scheduler.getEvent(id);
		var win = window.open(baseUrl + ev.wo_no + '/pdf/' + id, '_blank');
  		win.focus();
		return true;
	};

	scheduler._click.buttons.email = function(id) {
		var ev = scheduler.getEvent(id);
		var dropdownContent = scheduleGlobal.emails_tpl.tpl.replace(/\[ADDRESS\]/g, ev.lead_address);
		var dropdownContent = dropdownContent.replace(/\[JOB_ADDRESS\]/g, ev.lead_address);

		$('[event_id="'+id+'"]').find('.icon_email').html(dropdownContent);

		$('[event_id="'+id+'"]').find('.icon_email').find("a[data-toggle=\"modal\"]").each(function(i, v){
			v.dataset.event_id = id;
			v.dataset.workorder_id = ev.wo_id;
		});

		if($('[event_id="'+id+'"]').find('.icon_email').is('.open'))
			$('[event_id="'+id+'"]').find('.icon_email').removeClass('open');
		else
			$('[event_id="'+id+'"]').find('.icon_email').addClass('open');
		return true;
	};

	scheduler._click.buttons.sms = function(id) {
		$('#sms-9' + id).modal('show');
		return true;
	};

	scheduler.locale.labels.icon_price = "Event Price";
	scheduler.locale.labels.icon_profile = "Workorder Profile";
	scheduler.locale.labels.icon_email = "Send Email";
	scheduler.locale.labels.icon_sms = "Send SMS";
	scheduler.locale.labels.icon_damage = "Damage";
	scheduler.locale.labels.icon_complain = "Complain";
	scheduler.config.touch = true;

	scheduler.update_view = function () {

		$.cookie('scheduler_mode', scheduler.getState().mode);
		window.updatingView = true;

		var currDate = scheduler.getState().date;
		var view = this[this._mode + "_view"];
	
		if (view) {
			view(true);
		} else {
			this._reset_scale();
		}

		if (this._trigger_dyn_loading()) {
			return true;
		}

		if(this._mode != 'timeline'){
			scheduler.render_view_data();
		}

		var actual_team_amount = 0;
		var actual_per_hour = 0;

		if(this._mode == 'unit') {
			setTimeout(function () {
            $.each(sections, function(num, val) {

                actual_team_amount = val.team_amount ? val.team_amount : 'N/A';
                actual_per_hour = (actual_team_amount && val.team_man_hours != '0') ? (Math.ceil(((actual_team_amount - val.team_damage) / val.team_man_hours)*100)/100) : 0;
                if(actual_per_hour && actual_per_hour != '0')
                {
                    color = '#8ec165';
                    if(actual_per_hour < GOOD_MAN_HOURS_RETURN)
                        color = '#fa5542';
                    if(actual_per_hour > GOOD_MAN_HOURS_RETURN && actual_per_hour < GREAT_MAN_HOURS_RETURN)
                        color = '#ffc333';
                    if(actual_per_hour > VERY_GREAT_MAN_HOURS_RETURN)
                        color = 'linear-gradient(45deg,#277700 3%, #3bc63b 22%,#52b152 30%,#11e603 54%,#4ace04 72%,#277700 98%)';
					$('.teams-stat-block[data-stat-team-id="'+val.key+'"]').css('background', color);
                    var div = document.createElement('div');
                    $(div).append('<div class="teams-stat-overlay"><div>Click To Lock</div></div>');
                    $(div).css('background', color);
					if(val.team_closed == '1') {
						$(div).find('.teams-stat-overlay').find('div:first').text('Click To Unlock');
						$(div).find('.teams-stat-overlay').addClass('show');
						$('.dhx_scale_bar a[data-crew_id="' + val.key + '"]').parent().find('.crew-overlay').remove();
						$('.dhx_scale_bar a[data-crew_id="' + val.key + '"]').parent().prepend('<div class="crew-overlay"></div>');
						$('.crew-overlay-list').remove();
						$('ul[data-bonus-team-id="' + val.key + '"]').prepend('<div class="crew-overlay-list"></div>');
						$('ul[data-bonus-team-id="' + val.key + '"]').removeClass('sortable');

					}
					$('.teams-stat-block[data-stat-team-id="'+val.key+'"]').find('.teams-stat-overlay').remove();
					$('.teams-stat-block[data-stat-team-id="'+val.key+'"]').prepend($(div).html());
                }
				ScheduleCommon.scheduleBodyHeight();
            });
			}, 500);

			if(typeof scheduler._props.unit!="undefined" &&  !scheduler._props.unit.position && lastScrollPosition)
				scheduler.scrollUnit(lastScrollPosition);
            changeFonts();
            var newDate = scheduler.getState().date;

			window.updatingView = false;

        }
	};

    scheduler.scrollUnit = function (step) {
    	var pr = scheduler._props[this._mode];
        if (pr) {
            pr.position = Math.min(Math.max(0, pr.position + step), pr.options.length - pr.size);
            lastScrollPosition = scheduler._props.unit.position;
            var sectionWidth = $('.dhx_scale_bar:last').outerWidth();
            var translate = parseInt($('#crewsList').css('transform').split(',')[4]) || 0;
            var newTranslate = translate - sectionWidth * step;

			if(lastScrollPosition && sections.length < pr.size) {
				pr.position = 0;
				lastScrollPosition = 0;
				translate = newTranslate = 0;
			}
            $('#crewsList').css({'transform':'translate('+newTranslate+'px, 0)'});
            $('.flex-stats').css({'transform':'translate('+newTranslate+'px, 0)'});
            this.update_view();
        }
    };


	scheduler.attachEvent("onEventCreated", function (id, e) {
		var ev = scheduler.getEvent(id);
		var tpl = scheduleGlobal.event_workorders_modal_tpl.tpl;
		ev.my_template = tpl;
	});

	scheduler.attachEvent('onAfterSchedulerResize', function() {
		recalcSizes();
		scheduler.scrollUnit(lastScrollPosition);
	});

	scheduler.attachEvent("onEventCancel", function(id, flag){
		return true;
	});

	scheduler.attachEvent("onBeforeLightbox", function (id) {
		var ev = scheduler.getEvent(id);

		ScheduleMapper.workorders(id, false, ev.wo_status);
		ev.my_template = scheduleGlobal.event_workorders_modal_tpl.tpl;

		return true;
	});

	scheduler.attachEvent("onAfterLightbox", function (){
		setTimeout(function () {
			ScheduleMapper.reset_map();
		}, 200);

		$('[data-toggle="popover"]').popover({container:'#scheduler_here'});
		newEventId = 0;
	});

	scheduler.config.active_link_view = "unit";
	scheduler.attachEvent("onEmptyClick", function(date, e){
		newEventId = 0;

		if(!$(e.target).closest('.popover').length)
		{
			$('.icon_price').popover('destroy');
			$('.icon_complain').popover('destroy');
			$('.icon_damage').popover('destroy');
		}
		setTimeout(function(){changeFonts();},100);

		return true;
	});

	scheduler.attachEvent("onLightbox", function (id){

		scheduler.config.lightbox.sections[1].options = bakSections;
		$('.dhx_cal_larea').data('id', id);
		var event = scheduler.getEvent(id);

		$('.dhx_cal_ltext select').html('');
		/*-------------------REFACTORING---------------??----*/

		$.each(sections, function(key,val){
			$('.dhx_cal_ltext select').append('<option value="' + val.key + '">' + val.name + '</option>');
		});

		/* select sections */
		$('.dhx_cal_ltext select').val(event.section_id);
		$.each($('.dhx_cal_ltext select option'), function(key, val){
			$(val).text($(val).text().split('\n')[0]);
		});
		/*-------------------REFACTORING-------------------*/
		if(typeof(event.wo_id) != 'undefined')
		{
			var services = event.services ? event.services : '';
		}
		else
		{
			newEventId = id;
		}
		/*-------------------REFACTORING-------------------*/
		ScheduleMapper.init_map();
		ScheduleMapper.event_date(event);
		return true;
	});


	scheduler.showCover = function (box) {
		var left = (($(window).width() - $(box).outerWidth()) / 2) + $(window).scrollLeft() + "px";
		this.show_cover();
		if (box) {
			box.style.display = 'block';
			box.style.left = left;
		}
	}

	var countSections = getCountSections();	
	var scheduler_mode = ($.cookie('scheduler_mode'))?$.cookie('scheduler_mode'):'unit';

	scheduler.createUnitsView("unit", "section_id", sections, countSections, 1);
	ScheduleTimeline.createTimelineView();
	scheduler.init('scheduler_here', new Date(), scheduler_mode);

	min_date = moment(scheduler.getState().min_date).format("YYYY-MM-DD");
	max_date = moment(scheduler.getState().max_date).format("YYYY-MM-DD");

	dp = new dataProcessor(baseUrl + "schedule/data");
	dp.init(scheduler);
	dp.setTransactionMode("POST", true);

	scheduler.renderEvent = function(container, ev) {
		var container_width = container.style.width; // e.g. "105px"
		var container_height = parseInt(container.style.height); // e.g. "105px"

		var disabled = ((ev.wo_no == null || ev.wo_no==0 || Object.keys((ev.client!=undefined)?ev.client:{}).length==0) && ev.my_template==undefined && ev.brand_name!=undefined);
		// move section
		var html = "<div class='dhx_event_move dhx_header' style='width: " + container_width + "'></div>";
		ev.color = (disabled && ev.wo_id !== undefined) ? '#ff0000' : ev.color;

		// container for event's content
		html+= '<div class="dhx_event_move dhx_title" style="background:' + ev.color + ';">';
		if(disabled){
			if(ev.wo_id !== undefined) {
				html += '<span class="text-warning">Workorder was deleted!</span> ';
			}
		}
		else {
			html += ev.estimator + " ";
		}
		//two options here:show only start date for short events or start+end for long
		if ((ev.end_date - ev.start_date)/60000>40){//if event is longer than 40 minutes
			html += scheduler.templates.event_header(ev.start_date, ev.end_date, ev);
		} else {
			html += scheduler.templates.event_date(ev.start_date);
		}

		html += "</div>";
		// displaying event's text
		html += '<div class="dhx_body" style="height: ' + (container_height - 28) + 'px; width:' + (parseInt(container_width) - 10) + 'px;background:' + ev.color + ';">';
		html += this.getStickerContent(ev, disabled);
		html += '</div>';
		html += '<div class="dhx_event_resize dhx_footer" style="background:' + ev.color + ';"></div>';
		container.innerHTML = html;

		setTimeout(function(){
			$('body>.eventModalSms[id="sms-9' + ev.id + '"]').remove();
			$('[event_id="' + ev.id + '"] .eventModalSms').appendTo('body');
			$('[event_id="' + ev.id + '"] .eventModalSms').remove();
		}, 100);
		return true; //required, true - display a custom form, false - the default form
	};

	scheduler.templates.event_bar_date = function(start, end, ev) {
		return '';//"• <b>" + scheduler.templates.event_date(start) + '-' + scheduler.templates.event_date(end) + "</b> ";
	},

	scheduler.templates.event_bar_text = function(start, end, ev) {
		var address = '';
		var total_s = '';
		if(ev.lead_address!=undefined)
			address = address + ' ' + ev.lead_address;
		if(ev.lead_city!=undefined)
			address = address + ', ' + ev.lead_city;
		if(ev.total_for_services!=undefined)
			total_s = ' - <b><u>' + ev.total_for_services + '</u></b>';

		return address  + total_s + " <p>" + scheduler.templates.event_date(start) + '-' + scheduler.templates.event_date(end) + "</p> ";
	},

	scheduler.attachEvent("onClick", function(id, e){

		if(uPressed)
			return false;

		var ev = scheduler.getEvent(id);
		var disabled = (ev.wo_no == null);

		if(disabled) {
			scheduler.config.icons_select = ["icon_delete"];
		} else {
			scheduler.config.icons_select = scheduler.config.icons_select_orig;
		}

		if(!$(e.target).closest('.popover').length)
		{
			$('.icon_price').popover('destroy');
			$('.icon_complain').popover('destroy');
			$('.icon_damage').popover('destroy');
		}
		if(!$(e.target).closest('.dropdown-menu').length && !$(e.target).is('.icon_email'))
			$('.icon_email.open').removeClass('open');
		setTimeout(function(){changeFonts(id)},100);
		if($(e.target).closest('.statusesList').length)
			return false;
		return true;


	});

	scheduler.attachEvent("onContextMenu", function (id, e){
		if(uPressed)
			return false;
		changeFonts(id);
		return true;
	});

	scheduler.attachEvent("onBeforeDrag", function (id, drag_mode) {

		/*-------------------REFACTORING-------------------*/
		if($('.team-amount').is(':focus') || $('.team-note').is(':focus') || $('.hidden-team-note').is(':focus'))
			return false;
		/*-------------------REFACTORING-------------------*/

		var ev = scheduler.getEvent(id);
		if(ev && ev.start_date.getHours() < SCHEDULER_STARTS_FROM && scheduler.getState().mode != 'timeline')
			return false;

		if(ev && !ev.section_id)
			return false;

		if (this._mode != 'unit')
			scheduler.config.collision_limit = 100;
		else
			scheduler.config.collision_limit = 1;

		setTimeout(function(){changeFonts(id)},300);
		return !(drag_mode == "create" && this._mode == "month");
	});

	scheduler.attachEvent("onEventSave", function (id, ev, is_new) {

		var event = scheduler.getEvent(id);
		if(!ev.section_id && event.section_id)
			ev.section_id = event.section_id;

		if(!ev.section_id)
			return false;

		var result = true;
		if(ScheduleTimeline.search_blocked_intervals(ev)) {
			result = false;
		}

		$.each(scheduler.getEvents(ev.start_date, ev.end_date), function (key, val) {
			if (val.section_id == ev.section_id && id != val.id) {
				result = false;
				return false;
			}
		});

		if(!result){
			errorMessage("Ooops! The time interval is scheduled for other event.");
			return false;
		}

		result = ScheduleMapper.save_event(event, ev, is_new);

		setTimeout(function(){ changeFonts(id)},500);
		return result;
	});

	scheduler.attachEvent("onBeforeViewChange", function (old_mode, old_date, mode, date) {
		if(old_mode != mode || old_date != date){
			scheduler._props.unit.position = lastScrollPosition = 0;
		}

		return true;
	});

	scheduler.attachEvent("onBeforeEventChanged", function (ev, e, is_new) {

		if(scheduler.getState().mode == 'timeline' && ScheduleTimeline.search_blocked_intervals(ev)){
			return false;
		}

		if(ev && !ev.section_id)
			return false;

		if(ev.start_date.getHours() < SCHEDULER_STARTS_FROM && scheduler.getState().mode != 'timeline'){
			return false;
		}

		var result = true;
		var events = scheduler.getEvents(ev.start_date, ev.end_date);
		ev.mode = scheduler.getState().mode;
		scheduler.getEvent(ev.id).my_template = '';

		$.each(events, function (key, val) {
			if (val.section_id == ev.section_id && ev.id != val.id) {
				result = false;
				return false;
			}
		});

		setTimeout(function(){ changeFonts(ev.id) }, 100);
		return result;
	});

	scheduler.attachEvent("onEventChanged", function(id, ev){
		newEventId = 0;
		$('.dhx_cal_data').trigger('click');
	});

	scheduler.attachEvent("onBeforeViewChange", function (old_mode, old_date, mode, date) {
		if(old_mode != mode || old_date != date){
			scheduler._props.unit.position = lastScrollPosition = 0;
		}

		return true;
	});

	dp.attachEvent("onAfterUpdate", function (id,action,tid,tag){

		if(scheduler.getState().mode=='timeline' && action=='error'){
			ScheduleTimeline.onAfterUpdateCallback(id,action,tid,tag);
		}
		if(parseInt(tag.getAttribute("reload"))==1 && scheduler.getState().mode=='timeline'){
			ScheduleTimeline.resetTeams();
			return;
		}

		if(action=='error'){
			errorMessage(tag.getAttribute("message"));
			return false;
		}


		lastUpdateId = tag.getAttribute("uid");
		teamAmount = tag.getAttribute("team_amount");
		teamId = tag.getAttribute("team_id");
		oldTeamAmount = tag.getAttribute("old_team_amount");
		oldTeamId = tag.getAttribute("old_team_id");

		$('.team-amount[data-team-id="' + teamId + '"]').text(teamAmount);
		if(oldTeamAmount != undefined && oldTeamId != undefined)
			$('.team-amount[data-team-id="' + oldTeamId + '"]').text(oldTeamAmount);

		$('.team-amount[data-team-id="' + teamId + '"]').text(teamAmount);

		var team = JSON.parse(tag.getAttribute("team"));
		ScheduleUnit.setField(team.team_id, 'team_route_optimized', team.team_route_optimized);

		var ev = scheduler.getEvent(id);
		if(!ev)
			return false;

		ev.total_for_services = tag.getAttribute("total_for_services");
		ev.color = tag.getAttribute("color");
		ev.wo_status_name = tag.getAttribute("wo_status_name");
		ev.wo_status = tag.getAttribute("wo_status");
		ev.wo_id = tag.getAttribute("wo_id");
		ev.team_color = tag.getAttribute("team_color");

		ev.section_id = (scheduler.getState().mode=='timeline')?scheduler.getEvent(id).section_id:tag.getAttribute("section_id");

		ev.estimator = tag.getAttribute("estimator");
		/*
		ev.count_events = tag.getAttribute("count_events"); delete if all ok
		ev.total_services_price = tag.getAttribute("total_services_price");
		*/

		ev.event_price = tag.getAttribute("event_price");
		ev.event_complain = tag.getAttribute("event_complain");
		ev.event_damage = tag.getAttribute("event_damage");
		ev.tags = tag.getAttribute("tags");
		ev.state = tag.getAttribute("state");
		ev.city = tag.getAttribute("city");
		ev.address = tag.getAttribute("address");

		ev.total_for_services = tag.getAttribute("total_for_services");
		ev.total_service_time = tag.getAttribute("total_service_time");
		ev.total_hours = tag.getAttribute("total_hours");
		ev.event_crew = tag.getAttribute("event_crew");
		ev.event_equipment = tag.getAttribute("event_equipment");
		ev.mode = scheduler.getState().mode;

		ev.crew_id = tag.getAttribute("team_id");
		ev.event_team_id = ev.team_id = tag.getAttribute("team_id");

		scheduler.updateEvent(id);
		setTimeout(function () {
			scheduler.updateView();
		}, 300);
	});

	scheduler.attachEvent("onEventAdded", function(id, ev) {
		if(scheduler.getState().mode=='timeline')
			return true;

		ScheduleUnit.resetTeams();
	});

	scheduler.attachEvent("onEventDelete", function (id, data, is_new_event) {
		scheduler.getEvent(id).my_template = '';
		scheduler.updateEvent(id);
		event.text = '';
		data.text = '';
		if(!event.id)
			return false;
		return true;
	});

	scheduler.attachEvent("onViewChange", function (new_mode , new_date){

		ScheduleUnit.day_off_hide();
		var modeClass = window["Schedule"+jsUcfirst(new_mode)];

		if(typeof modeClass === "undefined"){
			ScheduleUnit.resetTeams();
		}

		if (typeof modeClass === 'object') {
			ScheduleCommon.detachEvents();
			var fn = modeClass['onViewChange'];
			if(typeof fn === 'function') {
				    fn(new_mode , new_date);
			}
		}
	});
}
function jsUcfirst(string) 
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}
