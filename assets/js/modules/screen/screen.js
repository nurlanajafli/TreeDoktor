$(document).ready(function(){
	//$('.carousel').carousel({interval:30000});
	//UsersStatistic.init();
});

function show_minical(){
	if (scheduler.isCalendarVisible()){
        scheduler.destroyCalendar();
    } else {
        scheduler.renderCalendar({
            position:"dhx_minical_icon",
            date:scheduler._date,
            navigation:true,
            handler:function(date,calendar){
                scheduler.setCurrentView(date, 'unit');
                scheduler.destroyCalendar()
            }
        });
    }
}


setInterval(function(){
	var currDate = scheduler.getState().date;
	var sentDate = currDate.getFullYear() + '-' + leadZero(currDate.getMonth() + 1, 2) + '-' + leadZero(currDate.getDate(), 2);
	$.post(baseUrl + 'screen/ajax_check_any_updates',{date:sentDate},function(resp){
		if(typeof(resp.result) != 'undefined' && resp.result === 'login')
		{
			location.href = baseUrl + 'login';
			return false;
		}
		if(resp[0].update_id && resp[0].update_id != lastUpdateId)
		{
			lastUpdateId = resp[0].update_id;
			$('#processing-modal').modal();
			setTimeout(function(){
				setTimeout(function(){
					min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
					max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);

					scheduler.clearAll();
					scheduler.init('scheduler_here', new Date(date), "unit");
					scheduler.load(baseUrl + 'screen/data?from=' + min_date + "&to=" + max_date, "json");
					//UsersStatistic.init();
					setTimeout(function(){
						$('#processing-modal').modal('hide');
						if($('.modal-backdrop.fade.in').length)
							$('.modal-backdrop.fade.in').fadeOut(function(){
								$('.modal-backdrop.fade.in').remove();
							});
					}, 300);
				}, 300);
			}, 500);
		}

		var newDate = (new Date().getMonth() + 1) + ' ' + new Date().getDate() + ' ' + (new Date().getYear() + 1900);
		if(new Date().getHours() >= 12)
		{
			var currentDate = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
			var day = currentDate.getDate();
			var month = currentDate.getMonth() + 1;
			var year = currentDate.getFullYear();
			newDate = month + " " + day + " " + year;
		}

		if(date != newDate)
		{
			date = newDate;
			setTimeout(function(){
				setTimeout(function(){
					min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
					max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);

					scheduler.clearAll();
					scheduler.init('scheduler_here', new Date(date), "unit");
					scheduler.load(baseUrl + 'screen/data?from=' + min_date + "&to=" + max_date, "json");
					//UsersStatistic.init();
					setTimeout(function(){
						$('#processing-modal').modal('hide');
						if($('.modal-backdrop.fade.in').length)
							$('.modal-backdrop.fade.in').fadeOut(function(){
								$('.modal-backdrop.fade.in').remove();
							});
					}, 300);
				}, 300);
			}, 500);
		}
	},'json',function(){console.log('error!!!');});
}, 30000);
	
var Screen = function(){
	var config = {
		autoscroll_enabled:false,
		ui:{
			screen:'#scheduler_here',
			timeline:'.dhx_scale_holder:last',
			data_section:'.dhx_scale_holder',
			head_section:'.dhx_scale_bar',
			screen_header: '.screen-top-navbar',
			screen_container:'#screen-container',
			dayoff_body:'#dayoff-body'
		},
		templates:{
			dayoff:'#dayoff-template'
		}
	}

	var private = {
		init:function(){
		
		},

		get_timeline_top:function(){
			return $('.dhx_cal_navline').height()+$('.dhx_cal_header').height();
		},

		sections_count: function(){
			return $(config.ui.data_section).length;
		},

		screen_container_width:function(){
			container_width = $(config.ui.head_section).length*$(config.ui.head_section).first().width()+10;
			if($(config.ui.head_section).length<=7)
				container_width = '100%';
			
			$(config.ui.screen_container).width(container_width);
		}
	}
	
	var public = {

		init:function(){
			setTimeout(function(){
				public.set_timeline();
				public.screen_width();
				public.screen_autoscroll();
			}, 100);
		},

		screen_autoscroll:function(){
			$(config.ui.screen).animate({scrollLeft:0}, 2000);
			if(config.autoscroll_enabled==true)
				return;

			if($(config.ui.head_section).length<7)
				return;

			screen_width = $(config.ui.screen).width();
			screen_step = (screen_width)/7*4;
			screen_scroll = 0;

			setInterval(function(){
				if($(config.ui.head_section).length<7)
					return;
				if(screen_width>screen_scroll){
					screen_scroll+=screen_step;
				}
				else{
					screen_scroll=0;
				}
				$(config.ui.screen).animate({scrollLeft: screen_scroll}, 2000);
			}, 10000);
			config.autoscroll_enabled = true;
		},

		set_timeline:function(){
			top_height = private.get_timeline_top();
			$(config.ui.timeline).css({top: top_height+'!important'});
		},

		screen_width:function(){
			
			$('.dhx_cal_header').width('auto');
			$('.dhx_cal_data').width('auto');

			private.screen_container_width();
		},

		set_day_off:function(members){
			
			render = {template_id:config.templates.dayoff, view_container_id:config.ui.dayoff_body, data:[{emp_name:'No members', team_color:'green', emp_reason:''}]};
			if(!members.length)
				return Common.renderView(render);

			day_off = [];
			$.each(members, function(key, value){
				if(value.team_id!=0)
					return;
				day_off.push(value);
			});

			if(!day_off.length)
				return Common.renderView(render);

			render.data = day_off;
			return Common.renderView(render);
		},
	}


	private.init();
	return public;
}();