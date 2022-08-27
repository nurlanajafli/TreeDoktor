var woMarkers = {}, globalSorted_teams = [], lastScrollPosition = 0;


var mapClick = false;
var labelClick = false;

function changeFonts(event_id)
{
	$.each($('[event_id]'), function(key, val){
		if($(val).find('.dhx_body').height() < $(val).find('.dhx_body').find('[data-event_wo_id]').height())
		{
			$(val).find('.dhx_body').css('visibility', 'hidden');
			$(val).find('.dhx_body [data-event_wo_id] div').css('display', 'inline-block');
			$(val).find('.dhx_body [data-event_wo_id] div').addClass('m-l-xs');
			var font = 16;
			while($(val).find('.dhx_body').height() < $(val).find('.dhx_body').find('[data-event_wo_id]').height())
			{
				$(val).find('.dhx_body').css('font-size','');
				$(val).find('.dhx_body').attr('style', $(val).find('.dhx_body').attr('style') + 'font-size:' + font + 'px!important');
				font -= 1;
				if(font < 8)
					break;
			}
			$(val).find('.dhx_body').css('visibility', 'visible');
		}
	});
	return false;
}


function teamHasEvents(teamId) {
	var result = false;

	scheduler.getEvents().forEach(function (val,key) {

		if(val.crew_id == teamId){
			result = true;
			return false;
		}
	});

	return result;
}

function updateSections(newSections, crew_id) {
	processUpdateSections = true;
	scheduler.config.lightbox.sections[1].options = newSections;

	var countSections = getCountSections();

	scheduler.createUnitsView("unit", "section_id", newSections, countSections, 1);

	if(crew_id)
	{
		setTimeout(function(){ $('[data-crew_id="' + crew_id + '"]').click(); }, 300);
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

function crewsList(members, sections) {
	var crewName = '';
	$('#crewsList').html('');
	$('#crewsList').parent().addClass('crews-list-empty-val');
	$('#crewsList').parent().find('.navbar-right').addClass('navbar-left').removeClass('navbar-right');

	$.each(sections, function(key, val){
		if(val.team_id && val.team_crew_id != '0' && !$('ul.crew_' + val.team_id).length)
		{
			var sectionWidth = $($('.dhx_scale_bar')[$('#crewsList>ul').length]).outerWidth() || $('.dhx_scale_bar:last').outerWidth();
			$('#crewsList').append('<ul class="text-center dropdown-toggle1 crewBonuses1 sortable crew_' + val.team_id + '" data-team-style="background:' + (val.color || val.team_color) + ';display: block;text-overflow: ellipsis;overflow: hidden;" data-bonus-team-id="' + val.team_id + '" data-toggle1="dropdown1" style="margin-top:4px;width:' + sectionWidth + 'px;flex:0 0 ' + sectionWidth + 'px;"><div class="dropdown-menu animated fadeInLeft bonusesList">' + bonuses.tpl + '</div></ul>');

			if(key && $('.crew_' + val.team_id).prev() && $('.crew_' + val.team_id).prev().offset()) {
				if($('.crew_' + val.team_id).offset().left < $('.crew_' + val.team_id).prev().offset().left) {
					$('.crew_' + val.team_id).addClass('clear');
				}
			}
		}
	});

	$.each(members, function(key, val){
		if(val.user != undefined){
			addToSortedCrewList(val, val.team ? false : true);
		}
		if(val.equipment_id != undefined)
			addToSortedItemList(val);
	});

	if(members.length){
		$('#crewsList').parent().removeClass('crews-list-empty-val');
		$('#crewsList').parent().find('.navbar-left').addClass('navbar-right').removeClass('navbar-left');
	}
	$('[data-toggle="class:nav-xs"]').click(function () {
		setTimeout(function () {
			recalcSizes();
		}, 100);
	});
	$(document).on('show.bs.dropdown', '.copyCrewDropdown', function(){

		var date = moment(scheduler.getState().date).add(1, 'days').format(MOMENT_DATE_FORMAT);

		$(this).find('.newTeamDate').val(date);
		$(this).find('[name="crew_id"]').val($(this).parents('.dhx_scale_bar:first').find('a[data-crew_id]').data('crew_id'));
		$('.crew-datepicker').datepicker({format: DATEPICKER_DATE_FORMAT, multidate: true, multidateSeparator:'|', orientation:'bottom'}).on('changeDate', function (e) {

		});//.datepicker('setDates', moment(currDate).format(MOMENT_DATE_FORMAT));
	});

	$('li.label[data-item-id] a[data-toggle="popover"]').popover().on('show.bs.popover', function() {
		var teamId = $(this).parents('ul[data-bonus-team-id]:first').attr('data-bonus-team-id');
		var itemId = $(this).parents('li.label[data-item-id]:first').attr('data-item-id');
		var driverId = $(this).attr('data-driver_id');
		var content = `
			<select class="form-control changeDriver" data-equipment_team_id="` + teamId + `" data-equipment_id="` + itemId + `">
		`;
		content += `<option value="">N/A</option>`;
		$.each($('.crew_' + teamId).find('li.label[data-emp_id]'), function(key, val){
			//if(!$('[data-driver_id="' + $(val).attr('data-emp_id') + '"]').length) {
			var el = document.createElement('div');
			el.innerHTML = $(val).html();
			$(el).find('.teamLeader').remove();
			$(el).find('.driverFor').remove();
			name = $(el).text();
			selected = driverId && driverId==$(val).attr('data-emp_id') ? ' selected="selected"' : '';
			content += `<option data-emailid="` + $(val).attr('data-emailid') + `" ` + selected + ` value="` + $(val).attr('data-emp_id') + `">` + name + `</option>`;
			//}
		});
		content += `
			</select>
		`;
		$(this).attr('data-content', content);
	});
	$('#crewsList').append('<div class="clear"></div>');
}

function copyTeamCallback(data) {
	let message = '';
	let type = 'success';
	let title = 'Success!';

	if(data.status !== 'ok') {
		type = 'danger';
		title = 'Error!';
		message += data.message;
	} else {
		if(data.warnings !== undefined && data.warnings.length) {
			type = 'warning';
			title = 'Warning!';
			data.warnings.forEach(function(warning){
				message += '<p>' + warning + '</p>';
			});
		}
	}
	let uniq = ((Math.random()*1e8)).toString(16).split('.')[0];
	let warningTpl = `
			<div class="alert alert-` + type + ` alert-block pos-abt" id="copyError-` + uniq + `" style="top: -50%; left: 100px; opacity: 0; min-width: 200px;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4><i class="fa fa-bell-alt"></i>` + title + `</h4>` +
		message
		+ `</div>
		`;
	$('body').append(warningTpl);
	$('#copyError-' + uniq).animate({top:40, opacity: 1}, 'slow');
	setTimeout(function() {
		$('#copyError-' + uniq).fadeOut(function () {
			$('#copyError-' + uniq).remove();
		});
	}, 10000);
}


function addToSortedCrewList(member, dayOff) {
	var sectionWidth = $($('.dhx_scale_bar')[$('#crewsList>ul').length]).outerWidth() || $('.dhx_scale_bar:last').outerWidth();
	if(member.team && !$('#crewsList .crew_' + member.team.team_id).length && member.team_id)
		$('#crewsList').find('.clear').before('<ul class="text-center crewBonuses1 sortable crew_' + member.team.team_id + '" data-bonus-team-id="' + member.team.team_id + '" style="margin-top: 4px;width:' + sectionWidth + 'px;flex:0 0 ' + sectionWidth + 'px;"><div class="dropdown-menu1 animated fadeInLeft bonusesList">' + bonuses.tpl + '</div></ul>');

	var empClass = '';
	member.name = member.user.full_name;
	if(member.team && member.user_id == member.team.team_leader_user_id)
		member.name = '<span class="teamLeader">* </span>' + member.user.full_name;

	/* select driver*/
	if(member.driver_id)
		member.name = member.name + '<span class="driverFor" data-driver-for-item="' + member.group_id + '"> (' + member.driver_id + ')</span>';

	var emailid = (member.user)?member.user.emailid:'';
	if(member.team && member.team.team_id){
		$('#crewsList .crew_' + member.team.team_id).append('<li class="label label-info employee_' + member.user_id + empClass + '"' + ' data-emailid="' + emailid +'" data-emp_id="' + member.user_id + '" style="background:' + member.team.team_color + ';text-overflow: ellipsis;overflow: hidden; display: block;">' + member.name + '</li>');
	}
}

function addToSortedItemList(item) {

	if(item.equipment==undefined)
		return;

	var driver = (item.driver && item.driver.full_name) ? item.driver.full_name : 'N/A';
	tpl = `
		<li class="label team-equipment-item it_` + item.equipment_id + `" data-item_code="` + item.equipment.eq_code + `" data-item_group_id="` + item.equipment.group_id + `" data-item-id="` + item.equipment_id + `" data-origin-color="` + ((item.equipment.group)?item.equipment.group.group_color:'#fff') + `" style="color:#000;display: block;">` + item.equipment.eq_name + `
			<a href="#" data-driver_id="` + item.equipment_driver_id + `" data-container="body" data-toggle="popover" data-html="true" data-placement="top" data-content="TEST">
				(` + driver + `)
			</a>
		</li>
	`;

	$('#crewsList .crew_' + item.equipment_team_id).append(tpl);
	return false;
}

function addToCrewList(member, dayOff) {

	var sectionWidth = $($('.dhx_scale_bar')[$('#crewsList>ul').length]).outerWidth() || $('.dhx_scale_bar:last').outerWidth();
	if(!$('#crewsList .crew_' + member.team_id).length && member.team_id)
		$('#crewsList').find('.clear').before('<ul class="text-center crewBonuses1 sortable crew_' + member.team_id + '" data-bonus-team-id="' + member.team_id + '" style="margin-top: 4px;width:' + sectionWidth + 'px;flex:0 0 ' + sectionWidth + 'px;"><div class="dropdown-menu1 animated fadeInLeft bonusesList">' + bonuses.tpl + '</div></ul>');

	if(member.team_id)
		$('#crewsList .crew_' + member.team_id).append('<li class="label label-info employee_' + member.employee_id + '" data-emailid="' + member.emailid + '" data-emp_id="' + member.employee_id + '" style="background:' + member.team_color + ';text-overflow: ellipsis;overflow: hidden; display: block;">' + member.emp_name + '</li>');

}
function removeFromCrewList(employee_id) {
	var obj = $('#crewsList ul li.employee_' + employee_id).parent();
	$('#crewsList ul li.employee_' + employee_id).remove();
	$('.dhx_scale_holder .crew_0 li.employee_' + employee_id).remove();
}

function addToItemList(item) {
	var driver = 'N/A';
	var tpl = `
		<li class="label team-equipment-item it_` + item.eq_id + `" data-item_code="` + item.eq_code + `" data-item-id="` + item.eq_id + `" data-item_group_id="` + item.group_id + `" data-origin-color="` + item.group_color + `" style="color:#000;display: block;">` + item.eq_name + `
			<a href="#" data-driver_id="" data-toggle="popover" data-html="true" data-container="body" data-placement="top" data-content="TEST">
					(` + driver + `)
			</a>
		</li>
	`;
	$('#crewsList .crew_' + item.crew_id).append(tpl);
	$('li.label.it_' + item.item_id + ' a[data-toggle="popover"]').popover().on('show.bs.popover', function() {
		var teamId = item.crew_id;
		var itemId = item.item_id;
		var driverId = $(this).attr('data-driver_id');
		var content = `
			<select class="form-control changeDriver" data-equipment_team_id="` + teamId + `" data-equipment_id="` + itemId + `">
		`;
		content += `<option value="">N/A</option>`;
		$.each($('.crew_' + teamId).find('li.label[data-emp_id]'), function(key, val){
			//if(!$('[data-driver_id="' + $(val).attr('data-emp_id') + '"]').length) {
			var el = document.createElement('div');
			el.innerHTML = $(val).html();
			$(el).find('.teamLeader').remove();
			$(el).find('.driverFor').remove();
			name = $(el).text();
			selected = driverId && driverId==$(val).attr('data-emp_id') ? ' selected="selected"' : '';
			content += `<option data-emailid="` + $(val).attr('data-emailid') + `" ` + selected + ` value="` + $(val).attr('data-emp_id') + `">` + name + `</option>`;
			//}
		});
		content += `
			</select>
		`;
		$(this).attr('data-content', content);
	});
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
function displayVehicles(){
	$.each(vehicles, function(key, val){
		var latLng = new google.maps.LatLng(val.latitude,val.longitude);

		var icon = baseUrl + 'uploads/trackericon/cam_bleue.png';
		if (val.item_name.indexOf("VHC 06") === 0 || val.item_name.indexOf("VHC 12") === 0 || val.item_name.indexOf("VHC 21") === 0)
			icon = baseUrl + 'assets/img/car.png';
		if (val.item_name.indexOf("VHC 09") === 0 || val.item_name.indexOf("VHC 14") === 0 || val.item_name.indexOf("VHC 15") === 0 || val.item_name.indexOf("VHC 19") === 0 || val.item_name.indexOf("VHC 22") === 0 || val.item_name.indexOf("VHC 24") === 0 || val.item_name.indexOf("VHC 25") === 0)
			icon = baseUrl + 'assets/img/pick_up.png';

		vehMarkers[key] = new google.maps.Marker({
			position: latLng,
			map: map,
			title: val.item_name,
			code: val.item_code,
			icon: icon//baseUrl + 'uploads/trackericon/cam_bleue.png'
		});

		var label = new Label({
			map: map
		});
		label.bindTo('position', vehMarkers[key], 'position');
		label.bindTo('text', vehMarkers[key], 'code');

		google.maps.event.addListener(vehMarkers[key], 'click', function() {
			if (infowindow) infowindow.close();
			infowindow = new google.maps.InfoWindow({
				content: '<div>' + vehMarkers[key].title + '</div>',
			});
			infowindow.open(map, this);
		});
		google.maps.event.addListener(label, 'click', function() {
			if (infowindow) infowindow.close();
			infowindow = new google.maps.InfoWindow({
				content: '<div>' + vehMarkers[key].title + '</div>',
			});
			infowindow.open(map, this);
		});
	});
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

function CustomMarker(latlng,  map) {
	this.latlng_ = latlng;

	// Once the LatLng and text are set, add the overlay to the map.  This will
	// trigger a call to panes_changed which should in turn call draw.
	this.setMap(map);
}

CustomMarker.prototype = new google.maps.OverlayView();

CustomMarker.prototype.draw = function() {
	var me = this;

	// Check if the div has been created.
	var div = this.div_;
	if (!div) {
		// Create a overlay text DIV
		div = this.div_ = document.createElement('DIV');
		// Create the DIV representing our CustomMarker
		div.style.border = "none";
		div.style.position = "absolute";
		div.style.paddingLeft = "0px";
		div.style.cursor = 'pointer';

		var img = document.createElement("img");
		img.src = "https://googlemaps.github.io/js-v2-samples/markers/circular/bluecirclemarker.png";
		div.appendChild(img);
		/*google.maps.event.addDomListener(div, "click", function(event) {
			google.maps.event.trigger(me, "click");
		});*/

		// Then add the overlay to the DOM
		var panes = this.getPanes();
		panes.overlayImage.appendChild(div);
	}

	// Position the overlay
	var point = this.getProjection().fromLatLngToDivPixel(this.latlng_);
	if (point) {
		div.style.left = point.x + 'px';
		div.style.top = point.y + 'px';
	}
};


CustomMarker.prototype.getPosition = function() {
	return this.latlng_;
};

changeColor ('FF0000');

function teamOrder(selector) {
	var equipments = [];
	var members = [];
	var new_id = $(selector).parents('ul[data-bonus-team-id]:first').attr('data-bonus-team-id');
	var date = scheduler.getState().date;
	var dateYMD = date.getFullYear() + '-' + leadZero(date.getMonth() + 1, 2) + '-' + leadZero(date.getDate(), 2);
	$.each($(selector), function(key, val) {
		if($(val).attr('data-emp_id'))
			members.push({user_id:$(val).attr('data-emp_id'), weight:key});
		if($(val).attr('data-item-id'))
			equipments.push({equipment_id:$(val).attr('data-item-id'), weight:key});
	});

	$.ajax({
		global: false,
		method: "POST",
		data: {team_id:new_id,date:dateYMD,members:members,equipments:equipments},
		url: base_url + "schedule/ajax_change_team_order",
		dataType:'json',
		success: function(response){
			if (response.status != 'ok')
				alert('Ooops! Error');
			else {
				lastUpdateId = response.update.update_id;
			}
		}
	});
}

function recalcSizes() {

	var sectionsWidth = $('.dhx_cal_header').width();
	var contentWidth = $('#content').width();
	$('.schedule-stats').css({'width': sectionsWidth + 'px'});
	$('.crews-list-container').css({'width': contentWidth + 'px'});
	$('.dhx_cal_navline').css({'width': contentWidth + 'px'});
	$.each($('#crewsList>ul'), function(key, val) {
		var sectionWidth = $($('.dhx_cal_header .dhx_scale_bar')[key]).outerWidth() || $('.dhx_cal_header .dhx_scale_bar:last').outerWidth();
		$(this).css({"width":sectionWidth+'px', 'flex':'0 0 '+sectionWidth+'px'});
		$($('.one-team-stat-block')[key]).css({"width":sectionWidth+'px', 'flex':'0 0 '+sectionWidth+'px'});
	});

	var contentLeft = $('#content').offset().left;
	var createTeamWidth = 52;
	$('.dhx_cal_header').attr('style','left:'+(contentLeft+createTeamWidth-2)+'px!important; width: '+($('#content').width()-50)+'px;');
	scheduler.set_sizes();
}

function addHours(time, h) {
	var newTime = new Date(time.getTime() + (h*60*60*1000));
	return newTime;
}

function timeStringToFloat(time) {
	var hoursMinutes = time.split(/[.:]/);
	var hours = parseInt(hoursMinutes[0], 10);
	var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
	return hours + minutes / 60;
}
function get_bubble_icon(dataCrewType, price){
	var crewType = dataCrewType ? ' (' + dataCrewType + ')' : '';
	var px = 0;
	var division = 2;
	var height = 79.62 / division;
	var marginL = 46 / division;
	var fontSize = 40 / division;

	if(crewType.length)
	{
		px = price.toString().length * 35;
		var crewTypeClear = crewType.replace('(', '').replace(')', '').replace(' ', '');
		px = (px + ((crewTypeClear.length - (crewTypeClear.split(",").length - 1)) * 35 + (crewTypeClear.split(",").length - 1) * 2.5 + 7.5))  / division;
	} else {
		px = price.toString().length * 16;
	}
	return 'data:image/svg+xml;base64,' + btoa('<svg  width="100%" height="' + height + '" viewBox="0 0 '+ px +' ' + height + '" xmlns="http://www.w3.org/2000/svg"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none"  width="100%" height="' + height + '" viewBox="0 0 502 35"><defs><path d="M242.27 29.79L235.41 25.57L249.18 25.57L262.95 25.57L256.04 29.79L249.13 34L242.27 29.79Z" id="e1so6NzTB5"></path><path d="M494.03 0.8C497.58 0.8 500.47 3.69 500.47 7.25C500.47 11.11 500.47 16.27 500.47 20.14C500.47 23.7 497.58 26.58 494.03 26.58C395.57 26.58 106.67 26.58 8.22 26.58C4.66 26.58 1.77 23.7 1.77 20.14C1.77 16.27 1.77 11.11 1.77 7.25C1.77 3.69 4.66 0.8 8.22 0.8C106.67 0.8 395.57 0.8 494.03 0.8Z" id="badwFVnye"></path><path d="" id="f7bXG8h7uV"></path><path d="M256.05 28.71L262.94 24.5L249.17 24.5L235.41 24.5L242.29 28.71L249.17 32.93L256.05 28.71Z" id="g1rJuMZELb"></path></defs><g><g><g><use xlink:href="#e1so6NzTB5" opacity="1" fill="#ffffff" fill-opacity="1"></use><g><use xlink:href="#e1so6NzTB5" opacity="1" fill-opacity="0" stroke="#000000" stroke-width="1" stroke-opacity="1"></use></g></g><g><use xlink:href="#badwFVnye" opacity="1" fill="#ffffff" fill-opacity="1"></use><g><use xlink:href="#badwFVnye" opacity="1" fill-opacity="0" stroke="#000000" stroke-width="1" stroke-opacity="1"></use></g></g><g><g><use xlink:href="#f7bXG8h7uV" opacity="1" fill-opacity="0" stroke="#291111" stroke-width="2" stroke-opacity="1"></use></g></g><g><use xlink:href="#g1rJuMZELb" opacity="1" fill="#ffffff" fill-opacity="1"></use><g><use xlink:href="#g1rJuMZELb" opacity="1" fill-opacity="0" stroke="#ffffff" stroke-width="1" stroke-opacity="1"></use></g></g></g></g></svg><svg id="svg" version="1.1"><text x="50%" y="' + marginL + '" fill="#000000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="' + fontSize + '" text-anchor="middle">' + price + ' ' + crewType + '</text></svg></svg>');
}

function getCountSections()
{
	var countSections = 8;
	if($(window).width() < 500)
		countSections = 2;
	if($(window).width() > 499 && $(window).width() < 768)
		countSections = 3;
	if($(window).width() > 767 && $(window).width() < 992)
		countSections = 5;
	if($(window).width() > 991 && $(window).width() < 1367)
		countSections = 6;

	return countSections;
}

function create_request_string(request_object)
{
	var request_string = '';
	if(Object.keys(request_object).length==0 || typeof request_object !== 'object')
		return '';

	var request_string = jQuery.param(request_object);
	return request_string;
}

function getScheduleData(callbackFn){
	var callbackRun = callbackFn;
	var schedule_data_request = create_request_string(ScheduleCommon.getWeekRequestConditions());
	scheduler.clearAll();
	scheduler.load(baseUrl + 'schedule/data?' + schedule_data_request, "json", function(){

		if(typeof callbackRun != "undefined")
			setTimeout(callbackRun, 100);

		scheduler.set_sizes();
	});
}
