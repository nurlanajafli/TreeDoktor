var ScheduleUnit = function(){
    var config = {

        ui:{
            wizard:'.wizard',
            day_off_btn: '.day-off-btn',
            day_off_container:'.day-off-container',
            edit_team: '#edit-team-dropdown-',
            edit_team_form:'.edit-team-dropdown-body',
            free_member: '.free-member',
            day_edit_team : '.day-edit-team'
        },
        
        events:{
            create_team_btn: '.create-team',
            datepicker: '#teamDate',
            edit_team_crew_type: '.edit-team-dropdown .crew_type_id',
            members_list: "#membersList",
            equipment_list: "#equipmentsList",
            day_off_btn: '.day-off-btn',

            delete_member_from_team:'.deleteFromCrew',
            delete_item_from_team:'.deleteItemFromCrew',

            add_tool: '.dhx_scale_bar .addTool',
            delete_tool_from_team: '.deleteToolFromTeam',

            reason_absence: '.reasonAbsence',
            delete_from_day_off: '.deleteFromDayOff',
            changeTeamLeaderDropdown: '.teamLead',

            optimize_route: '.optimize-route',
        },

        route:{
            
        },
        
        templates:{
            create_team_form: "#day-create-team-form-tpl",
            create_team_free_members: "#day-create-team-free-members-tpl",
            create_team_free_items: "#day-create-team-free-items-tpl",
            create_team_crew_leader:"#day-create-team-crew-leader-tpl",
            day_team_header:"#day-team-header-tpl",
            day_free_members_list: "#day-free-members-list-tpl",
            day_free_equipment_list: "#day-free-equipment-list-tpl",
            day_dayoff: "#day-dayoff-tpl",
            day_dayoff_reasons: "#day-dayoff-reasons-tpl",
            route_optimization_preview: "#route-optimization-preview-tmp"
        },

        views: {
            create_team_form: "#day-create-team-form",
            create_team_form: "#day-create-team-form",
            create_team_free_members: "#day-create-team-free-members",
            create_team_free_items: "#day-create-team-free-items",
            create_team_crew_leader: "#crewLeader",
            day_free_members_list: "#membersList .freeMembers",
            day_free_equipment_list: "#equipmentsList .freeItems",
            day_dayoff: "#day-off-view",
            route_optimization_preview: "#route-optimization-preview"
        },
        const: {
            office_address: OFFICE_ADDRESS + ', ' + OFFICE_CITY + ', ' + OFFICE_STATE + ', ' + OFFICE_COUNTRY
        }
    }

    var _templates = {
        init_templates:function () {
            scheduler.templates.unit_scale_text = function(key, label, section){
                var renderView = {template_id:config.templates.day_team_header,  render_method:'variable', data:[section] , helpers:[]};
                return Common.renderView(renderView);
            };
        },
        renderTeamPupUp: function (team_id, tab) {
            if(tab==undefined)
                tab='members';

            $(config.ui.edit_team_form).each(function(i, val){
                $(this).html('');
            });

            if(Object.keys(model.teams[team_id]).length==0)
                return false;

            Common.renderView({
                template_id:'#team-crews-dropdown-tpl',
                view_container_id: "#edit-team-dropdown-body-"+team_id,
                data:[{
                    tab: tab,
                    crews: scheduleGlobal.crews,
                    team: model.teams[team_id],

                    team_crew_id: model.teams[team_id].crew.crew_id,
                    team_id: team_id,
                    team_color:model.teams[team_id].team_color,
                    members: model.teams[team_id].members,
                    equipment: model.teams[team_id].schedule_teams_equipments,
                    tools:model.teams[team_id].schedule_teams_tools,
                    crew:model.teams[team_id].crew,
                    team_leader: model.teams[team_id].team_leader,

                    free_members: model.free_members,
                    free_items: model.free_items,
                    free_tools: model.free_tools
                }] , helpers:[]
            });

            setMyColorpicker($('#edit-team-dropdown-'+team_id).find('.mycolorpicker'));

            //$('#edit-team-dropdown-'+team_id).parent().parent().find('.addMember[data-emp_id="' + model.teams[team_id].team_leader.id + '"]').remove();
        },
    };

    var _private = {
        init:function(){
        
        },

        team_is_open: function(team_id){
            return $(config.ui.edit_team+team_id).hasClass('open');
        },

        resetTeams:function(team_id) {

            scheduler.config.drag_create = true;
            scheduler.config.readonly = false;

            lastScrollPosition = scheduler._props.unit.position;

            scheduler.templates.event_class = function(start, end, ev){
                return "";
            }

            if($('.popover').length)
                $('.popover').hide();

            lock = true;
            $('#attention').fadeOut();

            $('#crewsList').css({'transform':'translate(0, 0)'});
            $('.flex-stats').css({'transform':'translate(0, 0)'});

            var countDone = 0;
            if(!$('.modal-backdrop.fade.in').is(':visible'))
                $('#processing-modal').modal();

            var showedTeam = team_id ? team_id : null;

            setTimeout(function(){

                var totalSum = 0;
                var totalHrs = 0;

                var date = scheduler.getState().date;
                var dateYMD = date.getFullYear() + '-' + leadZero(date.getMonth() + 1, 2) + '-' + leadZero(date.getDate(), 2);

                var ajax_crews_members_request = ScheduleCommon.getWeekRequestConditions();

                ajax_crews_members_request['date'] = dateYMD;

                $('.schedule-stats-container .one-team-stat-block').remove();
                $('.schedule-stats-container .all-teams-stat-block').remove();

                $.post(baseUrl + 'schedule/ajax_crews_members', ajax_crews_members_request, function(resp){
                    if(resp.status != 'ok')
                        return;

                    model.free_members = resp.free_members;
                    model.free_items = resp.free_items;
                    model.free_tools = resp.free_tools;
                    model.members = resp.members;
                    model.items = resp.items;
                    model.tools = resp.tools;
                    model.teams = resp.teams;
                    model.absences = resp.absences;

                    globalSorted_teams = resp.sorted_teams;

                    setTimeout(function(){
                        $('.crews-list-container').css({'opacity':'1'});
                        if($(window).width() > 992)
                            $('.crews-list-container').css({'padding-left': '50px'});
                    }, 1000);

                    if(resp.update)
                        lastUpdateId = resp.update.update_id;

                    if(resp.weekly_amount != undefined)
                        $('.dhx_cal_date').text($('.dhx_cal_date').text() + ' - Weekly Total: ' + Common.money(resp.weekly_amount) + ' (' + resp.weekly_hours.toFixed(2) + 'hrs.)');

                    if(resp.note != undefined)
                        $('.day-note').val(resp.note.note_text);

                    sections = [];
                    colors = [];

                    $('.schedule-stats-container .one-team-stat-block').remove();
                    $('.schedule-stats-container .all-teams-stat-block').remove();

                    $.each(resp.sections, function(num, val){

                        colors[val.team_id] = val.team_color;

                        sections.push({
                            key:val.team_id,
                            subkey:val.team_leader_user_id,
                            name: (val.crew && val.crew.crew_name) ? val.crew.crew_name : '',
                            color:val.team_color,
                            leader:val.team_leader ? ' (' + val.team_leader.full_name + ')' : '(NoTeamLead)',
                            team_closed:val.team_closed,
                            team_amount:(val.team_amount!=undefined)?val.team_amount:'N/A',
                            team_man_hours:Math.ceil((val.team_man_hours*100)/100),
                            team_estimated_amount:val.team_estimated_amount,
                            team_damage:val.team_damage,
                            team: resp.teams[val.team_id],
                            label: ''
                        });

                        if(num + 1 == resp.sections.length)
                        {
                            updateSections(sections);
                        }

                        /* statistic block */
                        if(val.team_crew_id !== '0')
                        {
                            setTimeout(function(){
                                team_amount = '';
                                crewTotalStr = Common.money(0);
                                if(val.team_amount)
                                {
                                    team_amount = Common.money(val.team_amount - ((val.total_expenses!=undefined)?val.total_expenses:0));

                                    var expStr = '&nbsp;<br>&nbsp;';
                                    if(val.total_expenses > 0) {
                                        expStr = 'expenses<br>' + Common.money(val.total_expenses);
                                    }

                                    crewTotalStr = team_amount + ' <span style="font-size: 9px;color: #85ff00;line-height: 10px;display: inline-block;text-align: center;">' +
                                        expStr + '</span>';

                                    totalSum = totalSum + parseInt(val.team_amount - val.total_expenses);
                                    totalHrs = totalHrs + parseFloat(val.team_man_hours);
                                }
                                var height = $('#crewsList').is(':visible') ? $('#crewsList').height() : 0;

                                var popoverContent = "<div class='form-group m-b-none' style='width: 140px;'><input style='width: 100px;' class='form-control inline teamManHr-" + val.team_id + "' type='text' name='team_m_hr' value='" + val.team_man_hours + "'><button class='btn btn-xs btn-success m-l-sm changeMHr' data-team-id='" + val.team_id + "'><i class='fa fa-check'></i></button></div>";
                                var estimated_per_hour = (val.team_estimated_amount && val.team_estimated_hours) ? (Math.ceil((val.team_estimated_amount / val.team_estimated_hours)*100)/100) : 0;

                                $('.crews-list-container').css({'opacity': '1', 'width': $('#content').width()+'px'});
                                if($(window).width() > 992)
                                    $('.crews-list-container').css({'padding-left': '50px'});
                                $('.schedule-stats').css('width', $('.dhx_cal_header').width());
                                $('.schedule-stats .btn-stats').hide();

                                var sectionWidth = $($('.dhx_scale_bar')[num]).outerWidth() || $('.dhx_scale_bar:last').outerWidth();
                                $('.schedule-stats-container .flex-stats').append('<div class="one-team-stat-block pull-left" data-team-id="'+val.team_id+'" style="flex: 0 0 '+sectionWidth+'px;width:'+sectionWidth+'px;"></div>');
                                $('.schedule-stats-container .flex-stats .one-team-stat-block[data-team-id="'+val.team_id+'"]').append('<div data-team-id="'+val.team_id+'" class="team-amount" placeholder="Team Amount...">'+ crewTotalStr +'</div>');
                                $('.schedule-stats-container .flex-stats .one-team-stat-block[data-team-id="'+val.team_id+'"]').append('<div data-team-id="'+val.team_id+'" class="team-hours th-sortable" data-toggle="popover1" data-html="true" data-placement="top" data-container="body" data-content="' + popoverContent + '"><span class="teamManHoursText">' + val.team_man_hours + '</span> mh (' + val.team_estimated_hours + ')</div>');
                                $('.schedule-stats-container .flex-stats .one-team-stat-block[data-team-id="'+val.team_id+'"]').append('<div class="teams-stat-block" data-stat-team-id="'+val.team_id+'">' + team_stat_tpl.tpl + '</div>');

                                var estimators = [];
                                var actual_team_amount = 0;
                                var actual_per_hour = 0;
                                setTimeout(function(){
                                    $.each($('[data-event_team_id="' + val.team_id + '"][data-event_estimator]'), function(key, val){
                                        if(!(estimators.indexOf($(val).data('event_estimator')) + 1))
                                            estimators.push($(val).data('event_estimator'));
                                    });
                                    actual_team_amount = val.team_amount ? (val.team_amount - ((val.total_expenses!=undefined)?val.total_expenses:0)) : 0;

                                    $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.estimators').text(estimators.length ? estimators.join(', ') : 'N/A');
                                    $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.actual-amount').text(Common.money((actual_team_amount*100)/100));
                                    actual_per_hour = (actual_team_amount && val.team_man_hours != '0') ? (Math.ceil(((actual_team_amount - val.team_damage) / val.team_man_hours)*100)/100) : 0;
                                    $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.actual-per-hour').text(actual_per_hour ? Common.money(actual_per_hour) : 'N/A');
                                    if(actual_per_hour)
                                    {
                                        color = '#8ec165';
                                        if(actual_per_hour < GOOD_MAN_HOURS_RETURN)
                                            color = '#fa5542';
                                        if(actual_per_hour > GOOD_MAN_HOURS_RETURN && actual_per_hour < GREAT_MAN_HOURS_RETURN)
                                            color = '#ffc333';
                                        if(actual_per_hour > VERY_GREAT_MAN_HOURS_RETURN)
                                            color = 'linear-gradient(45deg,#277700 3%, #3bc63b 22%,#52b152 30%,#11e603 54%,#4ace04 72%,#277700 98%)';
                                        $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').css('background', color);
                                        //$('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').prepend('<div class="teams-stat-overlay"><div>Click To Lock</div></div>');
                                    }
                                    if(val.team_estimated_amount)
                                        amountProd = (Math.ceil((actual_team_amount / val.team_estimated_amount * 100) * 100) / 100) + '%';
                                    else
                                        amountProd = 'N/A';

                                    $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.amount-productivity').html(amountProd);

                                    if(estimated_per_hour)
                                        perHourProd = (Math.ceil((actual_per_hour / estimated_per_hour * 100)*100)/100) + '%';
                                    else
                                        perHourProd = 'N/A';

                                    $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.per-hour-productivity').text(perHourProd);

                                }, 500);

                                $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.team-leader').text(val.team_leader ? val.team_leader.full_name : 'N/A');
                                $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.actual-manhours').text(val.team_man_hours ? (Math.ceil(val.team_man_hours*100)/100) + ' mhr.' : 'N/A');
                                $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.estimated-manhours').text(val.team_estimated_hours ? (Math.ceil(val.team_estimated_hours*100)/100) + ' mhr.' : 'N/A');
                                $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.estimated-amount').text(val.team_estimated_amount ? Common.money(val.team_estimated_amount) : 'N/A');
                                $('.teams-stat-block[data-stat-team-id="'+val.team_id+'"]').find('.estimated-per-hour').text(estimated_per_hour ? Common.money(estimated_per_hour) : 'N/A');

                                countDone++;
                            }, 300);
                        }
                        /* statistic block */
                    });
                    _templates.init_templates();
                    setTimeout(function () {
                        scheduler.updateView();
                    }, 350);

                    if(showedTeam){
                        setTimeout(function () {
                            $(config.ui.edit_team+team_id+' '+config.ui.day_edit_team).trigger('click');
                        }, 400);
                    }

                    if(scheduler.getState().mode == 'unit' && sections.length)
                    {
                        setTimeout(function(){
                            if(totalSum==undefined || isNaN(totalSum))
                                totalSum = 0;

                            var height = $('#crewsList').is(':visible') ? $('#crewsList').height() : 0;
                            $('.schedule-stats-container').append('<div class="all-teams-stat-block clear"></div>');
                            $('.schedule-stats-container .all-teams-stat-block').append('<div class="teams-amount">Total: ' + Common.money(Math.ceil((totalSum)*100)/100) + ', Total Hours: ' + (Math.ceil((totalHrs)*100)/100) + ' hrs.</div>');
                            $('.schedule-stats .btn-stats').fadeIn('fast');
                            $('.schedule-stats').show();
                        }, 300);
                    }
                    if(!resp.sections.length)
                    {
                        updateSections(sections);
                    }

                    crewsList(resp.sorted_teams, resp.sections);
                    $.each(resp.bonuses, function(n, v){
                        var bonus_type_id = v.bonus_type_id;
                        var bonus_id = v.bonus_id;
                        var team_id = v.bonus_team_id;
                        if(bonus_type_id != '0')
                        {
                            $('.crew_' + team_id + ' .possibleBonuses [data-bonus_type_id="' + bonus_type_id + '"]').appendTo('.crew_' + team_id + ' .recivedBonuses');
                            $('.crew_' + team_id + ' .recivedBonuses [data-bonus_type_id="' + bonus_type_id + '"]').append('<a href="#" class="rmBonus">x</a>');
                        }
                        else
                        {
                            labelClass = (v.bonus_amount > 0) ? 'success' : 'danger';
                            symbol = (v.bonus_amount > 0) ? '+' : '';
                            $('.crew_' + team_id + ' .recivedBonuses').append('<li class="label p-5 bg-' + labelClass + '" data-bonus_id="' + v.bonus_id + '" data-bonus_type_id="0" style="display: inline-block;margin-left: 2px;">' + symbol + v.bonus_amount + '% - ' + v.bonus_title + ' <a href="#" class="rmBonus">x</a></li>');
                        }
                    });

                    if(!sections)
                        $('#crewsList .clear').before('<br>');

                    $('.mycolorpicker').each(function () {
                        var current_color = $(this).val();
                        var current_color_short = current_color.replace(/^#/, '');
                        $(this).colpickSetColor(current_color_short);
                    });
                    setMyColorpicker($('.mycolorpicker'));

                    for(i=0; i > lastScrollPosition; i++){
                        scheduler.scrollUnit(scheduler._props.unit.position);
                    }

                    $('.day-note').css('width', ($('.dhx_cal_header').width() + 1) + 'px');

                    getScheduleData(function(){ changeFonts(); /*ScheduleUnit.events_delegate();*/ lock = false;});

                    setTimeout(function(){
                        var height = $('#crewsList').is(':visible') ? $('.crews-list-container').height() : 0;

                        $('.teams-amount').animate({bottom: height+'px'});
                        $('.teams-hours').animate({bottom: (height+30)+'px'});
                        $('.teams-stat-btn').animate({bottom: (height+60)+'px'});
                        $('#processing-modal').modal('hide');
                        $('.modal-backdrop.fade.in').remove();
                        $('.modal-backdrop.fade').remove();

                        if(!mapNode) {

                            setTimeout(function() {

                                $('[data-toggle="popover"]').popover();
                                /***DISPLAY STATIC MAP OBJECTS***/
                                $.each(objects, function(key, val){
                                    color = val.object_color;
                                    var latLng = new google.maps.LatLng(val.object_latitude,val.object_longitude);

                                    objMarkers[key] = new google.maps.Marker({
                                        position: latLng,
                                        map: map,
                                        title: val.object_desc,
                                        code: val.object_name,
                                        /*icon: {
                                              url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
                                          }*/
                                        icon: 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="25" height="52" viewBox="0 0 38 38"><path fill="' + color + '" stroke="#000" stroke-width="2" d="M34.305 16.234c0 8.83-15.148 19.158-15.148 19.158S3.507 25.065 3.507 16.1c0-8.505 6.894-14.304 15.4-14.304 8.504 0 15.398 5.933 15.398 14.438z"/><text transform="translate(19 25)" fill="#000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="12" text-anchor="middle">&#9899;</text></svg>')
                                    });

                                    var label = new Label({
                                        map: map
                                    });
                                    label.bindTo('position', objMarkers[key], 'position');
                                    label.bindTo('text', objMarkers[key], 'code');

                                    google.maps.event.addListener(objMarkers[key], 'click', function() {
                                        if (infowindow) infowindow.close();
                                        infowindow = new google.maps.InfoWindow({
                                            content: '<div>' + objMarkers[key].title + '</div>',
                                        });
                                        infowindow.open(map, this);
                                    });
                                });
                                /***DISPLAY STATIC MAP OBJECTS***/
                                displayVehicles();

                            }, 2000);
                        }

                    }, 500);

                    if(resp.weekly_amount != undefined)
                    {
                        setTimeout(function() {
                            $('.dhx_cal_date').text($('.dhx_cal_date').text() + ' - Weekly Total:' + Common.money(resp.weekly_amount) + ' (' + resp.weekly_hours.toFixed(2) + 'hrs.)');
                        }, 1000);
                    }

                    return false;

                }, 'json');

            }, 500);
        },

        render_free_members: function(){
            Common.renderView({
                template_id:config.templates.day_free_members_list,
                view_container_id:config.views.day_free_members_list,
                data:[{free_members:model.free_members}],
                helpers:[]
            });
        },

        render_free_equipment:function(){
            Common.renderView({
                template_id:config.templates.day_free_equipment_list,
                view_container_id:config.views.day_free_equipment_list,
                data:[{free_items:model.free_items}],
                helpers:[]
            });
        },

        render_dayOff: function(){

            reasons_dropdown = Common.renderView({
                template_id:config.templates.day_dayoff_reasons,
                render_method:'variable',
                data:[{reasons:reasonsAbsence}],
                helpers:[]
            });

            Common.renderView({
                template_id:config.templates.day_dayoff,
                view_container_id:config.views.day_dayoff,
                data:[{
                    free_members:model.free_members,
                    reasons:reasons_dropdown,
                    absences: model.absences
                }],
                helpers:[]
            });
        },

        menuElements:function(){
            $('.week-member-filter-view').hide();

            $('.day-off-btn').show();

            $('.free-members-label').show();
            //$(".crewsList").show();
            
            $("#refreshList").attr("style", '');
            $('.crews-list-container').css("margin-top", "165px");
            $(".crews-list-container").show();
        },

        create_team_popup:function () {
            if(!$('#scheduler_here .dropdown').is('.open'))
                return false;

            Common.renderView({
                template_id:config.templates.create_team_form,
                view_container_id:config.views.create_team_form,
                data:[{current_date: moment(scheduler.getState().date).format(MOMENT_DATE_FORMAT)}],
                helpers:[]
            });

            $(config.events.datepicker).datepicker({
                format: DATEPICKER_DATE_FORMAT,
            }).on('changeDate', _private.change_team_date);

            $(config.ui.wizard).wizard();
            setMyColorpicker($('.mycolorpicker'));

            var date = scheduler.getState().date;
            _private.get_free_members(moment(date).format("YYYY-MM-DD"), moment(date).format("YYYY-MM-DD"));
            return false;
        },

        get_free_members: function(from, to){
            Common.request.send('/schedule/scheduleFreeMembers', {
                team_date_start : from,
                team_date_end : to,
            }, _private.team_popup_render);
        },

        team_popup_render: function (response) {
            Common.renderView({
                template_id:config.templates.create_team_crew_leader,
                view_container_id:config.views.create_team_crew_leader,
                data:[response],
                helpers:[]
            });

            Common.renderView({
                template_id:config.templates.create_team_free_members,
                view_container_id:config.views.create_team_free_members,
                data:[response],
                helpers:[]
            });

            Common.renderView({
                template_id:config.templates.create_team_free_items,
                view_container_id:config.views.create_team_free_items,
                data:[response],
                helpers:[]
            });
        },

        change_team_date: function (e) {
            $(this).datepicker('hide');
            var date = $(this).datepicker('getDate');
            _private.get_free_members(moment(date).format("YYYY-MM-DD"), moment(date).format("YYYY-MM-DD"));
        },

        create_new_team: function (evt, data) {
            $('.wizard').parents('.dropdown.open:first').removeClass('open');
            var data = {
                team_date_start : $('#teamDate').val(),
                team_date_end : $('#teamDate').val(),
                team_type : $('#crewType').val(),
                team_leader : $('#crewLeader').val(),
                team_color : $('#crewColor').val(),
                team_members : [],
                team_items : [],
                mode:scheduler.getState().mode
            };
            $.each($('#step2 li[data-employee_id]'), function(key, val){
                data.team_members.push($(val).data('employee_id'));
            });
            $.each($('#step3 li[data-eq_id]'), function(key, val){
                data.team_items.push($(val).data('eq_id'));
            });

            Common.request.send(base_url + "schedule/ajax_new_team", data, function(response){
                if (response.status == 'error'){
                    erors_str = '';
                    for (const [key, value] of Object.entries(response.errors)) {
                        erors_str += value.join('\n')+'\n';
                    }
                    alert(erors_str);
                }
                else
                {
                    if(moment(scheduler.getState().date).format("YYYY MM DD") != moment($('#teamDate').val(), MOMENT_DATE_FORMAT).format("YYYY MM DD")){
                        scheduler.init('scheduler_here', moment($('#teamDate').val(), MOMENT_DATE_FORMAT)._d, "unit");
                    }
                    if(sections.length >= 8)
                        lastScrollPosition = sections.length - scheduler._props.unit.size + 1;

                    ScheduleUnit.resetTeams();
                }
            }, function () {}, false);
        },

        change_team_crew_type:function () {
            var team_crew_id = $(this).val();
            var team_id = $(this).data('team');

            Common.request.send('/schedule/teams/changeTeamCrew', {team_id:team_id,team_crew_id:team_crew_id}, function (response) {
                _private.resetTeams(team_id);
            }, function (response) {
                errorMessage("Data is not valid");
            });
        },

        day_off_toggle: function (e) {
            if($(config.ui.day_off_container).is(':visible')) {
                _private.day_off_hide();
            }
            else {
                _private.day_off_show();
            }
        },

        day_off_show:function () {
            _private.render_dayOff();
            $(config.ui.day_off_btn).find('i').removeClass('fa-angle-left').addClass('fa-angle-right');
            $(config.ui.day_off_container).animate({'right': 0}, 300);
            $(config.ui.day_off_container).show();
            return true;
        },

        day_off_hide: function () {
            if(!$(config.ui.day_off_container).is(':visible'))
                return false;

            $(config.ui.day_off_btn).find('i').removeClass('fa-angle-right').addClass('fa-angle-left');
            $(config.ui.day_off_container).animate({'right': '-50%'}, 300, function () {
                $(config.ui.day_off_container).hide();
            });
            return true;
        },

        add_member:function(id, team_id, old_team_id, extra_member){

            if(old_team_id)
                _private.delete_member(id, old_team_id, true);

            option = model.free_members.findIndex(function(post, index) { return (post.id == id) });

            if(option == -1)
                return;

            if(model.members.findIndex(function (post, index) { return post.id == id }) == -1)
                model.members.push(model.free_members[option]);

            if(team_id > 0 && model.teams[team_id]!=undefined)
                model.teams[team_id].members.push(model.free_members[option]);

            if(team_id == 0)
                model.absences.push(extra_member);

            model.free_members.splice(option, 1);
            model.free_members = model.free_members.filter(x => x != undefined).sortBy('full_name');
            model.members = model.members.filter(x => x != undefined);

            _private.render_free_members();
            _private.render_dayOff();
            if(_private.team_is_open(team_id))
                _templates.renderTeamPupUp(team_id, 'members');

        },

        delete_member:function(id, team_id, ignore_render){

            option = model.members.findIndex(function(post, index) {
                if(post.id == id)
                    return true;
            });

            if(option==-1)
                return false;

            model.free_members.unshift(model.members[option]);
            model.members.splice(option, 1);

            model.free_members = model.free_members.filter(x => x != undefined).sortBy('full_name');
            model.members = model.members.filter(x => x != undefined).sortBy('full_name');

            if(team_id > 0 && model.teams[team_id]!=undefined){
                team_member_option = model.teams[team_id].members.findIndex(function(post, index) { return (post.id == id) });
                model.teams[team_id].members.splice(team_member_option, 1);
                model.teams[team_id].members = model.teams[team_id].members.filter(x => x != undefined).sortBy('full_name');
            }

            if(team_id == 0){
                abs_option = model.absences.findIndex(function(post, index) {
                    if(post.user.id == id)
                        return true;
                });
                model.absences.splice(abs_option, 1);
                model.absences = model.absences.filter(x => x != undefined);
            }


            if(ignore_render!=undefined && ignore_render)
                return;

            _private.render_free_members();
            _private.render_dayOff();
            if(_private.team_is_open(team_id))
                _templates.renderTeamPupUp(team_id, 'members');
        },

        add_item:function(id, team_id, old_team_id){
            if(old_team_id)
                _private.delete_item(id, old_team_id, true);

            option = model.free_items.findIndex(function(post, index) { return (post.eq_id == id) });
            if(option==-1)
                return false;

            if(team_id > 0 && model.teams[team_id]!=undefined)
                model.teams[team_id].schedule_teams_equipments.push(model.free_items[option]);

            /* add item to team */
            model.items.push(model.free_items[option]);
            /* delete item from free items */
            model.free_items.splice(option, 1);

            model.items = model.items.filter(x => x != undefined).sortBy('eq_id').sortBy('group_id');
            model.free_items = model.free_items.filter(x => x != undefined).sortBy('eq_id').sortBy('group_id');

            _private.render_free_equipment();
            if(_private.team_is_open(team_id))
                _templates.renderTeamPupUp(team_id, 'equipment');
        },

        delete_item:function(id, team_id, ignore_render){
            option = model.items.findIndex(function(post, index) { return (post.eq_id == id); });
            model.free_items.unshift(model.items[option]);
            model.items.splice(option, 1);

            model.items = model.items.filter(x => x != undefined).sortBy('eq_id').sortBy('group_id');
            model.free_items = model.free_items.filter(x => x != undefined).sortBy('eq_id').sortBy('group_id');

            if(team_id > 0 && model.teams[team_id]!=undefined){
                team_member_option = model.teams[team_id].schedule_teams_equipments.findIndex(function(post, index) { return (post.eq_id == id) });
                model.teams[team_id].schedule_teams_equipments.splice(team_member_option, 1);
                model.teams[team_id].schedule_teams_equipments = model.teams[team_id].schedule_teams_equipments.filter(x => x != undefined);
            }

            if(ignore_render!=undefined && ignore_render)
                return;

            _private.render_free_equipment();
            if(_private.team_is_open(team_id))
                _templates.renderTeamPupUp(team_id, 'equipment');
        },

        delete_member_from_team: function (e) {

            var user_id = $(e.currentTarget).data('user_id');
            var team_id = $(e.currentTarget).data('team_id');

            removeFromCrewList(user_id);

            var request = {
                team_id:team_id,
                user_id:user_id,
                driver_id:'',
                date:moment(scheduler.getState().date).format("YYYY-MM-DD")
            };

            if($('li.label[data-item-id] a[data-driver_id="' + user_id + '"]').length) {
                Common.request.send("/schedule/ajax_change_driver", request, function (response) {
                    $('li.label[data-item-id] a[data-driver_id="' + user_id + '"]').text('(N/A)');
                    $('li.label[data-item-id] a[data-driver_id="' + user_id + '"]').attr('data-driver_id', '');
                }, function (response) {}, false);
            }

            Common.request.send("/schedule/ajax_delete_member", request, function (response) {
                lastUpdateId = response.update.update_id;
            }, function (response) {}, false);

            _private.delete_member(user_id, team_id);
            return false;
        },

        delete_item_from_team:function (e) {

            var item_id = $(e.currentTarget).data('item_id');
            var team_id = $(e.currentTarget).data('team_id');

            if($('li.label[data-item-id="' + item_id + '"][data-item_group_id="16"]').length) {
                var oldDriverId = $('li.label[data-item-id="' + item_id + '"] a[data-driver_id]').attr('data-driver_id');
                $('li.label.employee_' + oldDriverId).find('.driverFor[data-driver-for-item="' + item_id + '"]').remove();
            }

            removeFromItemList(item_id);
            var request = {
                item_id:item_id,
                crew_id:team_id,
                date:moment(scheduler.getState().date).format("YYYY-MM-DD")
            };
            Common.request.send("/schedule/ajax_delete_equipment", request, function(resp){
                lastUpdateId = resp.update.update_id;
            }, function (response) {
                alert('Ooops! Error');
            }, false);

            _private.delete_item(item_id, team_id);

            return false;
        },

        add_tool: function(tool_id, team_id, pivot){

            option = model.free_tools.findIndex(function(post, index) { return (post.eq_id == tool_id) });

            if(team_id > 0 && model.teams[team_id]!=undefined){
                item = Object.assign({}, model.free_tools[option], {pivot:pivot});
                model.teams[team_id].schedule_teams_tools.push(item);
            }

            if(_private.team_is_open(team_id))
                _templates.renderTeamPupUp(team_id, 'tools');
        },

        add_team_tool:function (e) {
            var team_id = $(e.currentTarget).data('team_id');
            var tool_id = $(e.currentTarget).data('tool-id');

            var request = {
                team_id: team_id,
                tool_id: tool_id,
                date: moment(scheduler.getState().date).format("YYYY-MM-DD")
            };

            Common.request.send("/schedule/ajax_add_tool", request, function (response) {
                lastUpdateId = response.update.update_id;
                _private.add_tool(tool_id, team_id, {
                    stt_id: response.stt_id, stt_item_id: tool_id, stt_team_id: team_id
                });
            }, function (response) {
                alert("Ooops! Error");
            }, false);
        },

        delete_tool: function (tool_id, team_id) {

            if(team_id > 0 && model.teams[team_id]!=undefined){
                team_tool_option = model.teams[team_id].schedule_teams_tools.findIndex(function(post, index) { return (post.pivot.stt_id == tool_id) });
                model.teams[team_id].schedule_teams_tools.splice(team_tool_option, 1);
                model.teams[team_id].schedule_teams_tools = model.teams[team_id].schedule_teams_tools.filter(x => x != undefined);
            }

            if(_private.team_is_open(team_id))
                _templates.renderTeamPupUp(team_id, 'tools');
        },

        delete_tool_from_team: function (e) {
            var stt_id = $(e.currentTarget).data('stt-id');
            var team_id = $(e.currentTarget).data('team_id');
            var tool_id = $(e.currentTarget).data('tool_id');

            Common.request.send("/schedule/ajax_delete_tool", {
                stt_id:stt_id, date:moment(scheduler.getState().date).format("YYYY-MM-DD")
            }, function (response) {
                lastUpdateId = response.update.update_id;
                _private.delete_tool(stt_id, team_id);
            }, function (response) {
                alert('Ooops! Error');
            }, false);
        },

        add_to_absence: function(e){
            var user_id = $(e.currentTarget).closest(config.ui.free_member).data('user_id');
            var reason_id = $(e.currentTarget).val();

            Common.request.send("/schedule/ajax_add_member_absence", {
                employee_id:user_id,
                date:moment(scheduler.getState().date).format("YYYY-MM-DD"),
                reason_id:reason_id
            }, function (response) {
                lastUpdateId = response.update.update_id;
                _private.add_member(user_id, 0, 0, response.user);
            }, function (response) {
                alert('Ooops! Error');
            }, false);
        },

        delete_absence: function(e){
            var user_id = $(e.currentTarget).data('user_id');
            var date = moment(scheduler.getState().date).format("YYYY-MM-DD");
            _private.delete_member(user_id, 0);
            Common.request.send("/schedule/ajax_delete_member_absence", {employee_id:user_id,date:date}, function (response) {
                lastUpdateId = response.update.update_id;
            }, function (response) {
                alert('Ooops! Error');
            }, false);
        },

        optimize_route: function (e) {
            var team_id = $(e.currentTarget).data('team_id');
            ScheduleCommon.set_scheduled_workorders();
            if(ScheduleCommon.scheduled_events['directions'][team_id] == undefined || model.teams[team_id].team_route_optimized == 1){
                events = (ScheduleCommon.scheduled_events['directions'][team_id]!=undefined)?ScheduleCommon.scheduled_events['directions'][team_id].events:[];
                Common.renderView({
                    template_id:config.templates.route_optimization_preview,
                    view_container_id:config.views.route_optimization_preview,
                    data: [{ events: events, team: model.teams[team_id]}],
                    helpers:[]
                });
                return;
            }

            var addresses = ScheduleCommon.scheduled_events['directions'][team_id].addresses;
            var end = config.const.office_address.replaceAll(',', '').replaceAll(' ', '+');
            addresses.push(end);

            ScheduleMapDirections.requestDirectionsArray(end, addresses, _private.optimize_route_render, {team_id: team_id}, function (result, extra) {
                Common.renderView({
                    template_id:config.templates.route_optimization_preview,
                    view_container_id:config.views.route_optimization_preview,
                    data: [{ events: [], team: model.teams[team_id], error:true}],
                    helpers:[]
                });
            });
        },

        optimize_route_render: function (data, extra_data) {
            var events = [];
            data.routes[0].waypoint_order.forEach(function (value, key) {
                if(ScheduleCommon.scheduled_events['directions'][extra_data.team_id].events[value]!=undefined)
                    events.push(ScheduleCommon.scheduled_events['directions'][extra_data.team_id].events[value]);
            });

            events.forEach(function (val, key) {
                diff = moment(val.end_date).diff(moment(val.start_date), 'seconds');

                if(key == 0)
                    start = moment(val.start_date).set({hour:SCHEDULER_STARTS_FROM, minute:0}).format("YYYY-MM-DD HH:mm");
                else
                    start = moment(events[key-1].end_date).format("YYYY-MM-DD HH:mm");

                end = moment(start).add(diff, 'seconds').format("YYYY-MM-DD HH:mm");

                events[key].start_date = moment(start)._d;
                events[key].end_date = moment(end)._d;
            });

            Common.renderView({
                template_id:config.templates.route_optimization_preview,
                view_container_id:config.views.route_optimization_preview,
                data: [{ events: events, team: model.teams[extra_data.team_id]}],
                helpers:[]
            });
        },

        optimizeRouteSuccess: function (response) {
            response.events.forEach(function (event, key) {
                ev = scheduler.getEvent(event.id);
                ev.start_date = moment(event.start_date)._d;
                ev.end_date = moment(event.end_date)._d;
                scheduler.updateEvent(event.id);
            });

            model.teams[response.team.team_id].team_route_optimized = response.team.team_route_optimized;

            Common.renderView({
                template_id:config.templates.route_optimization_preview,
                view_container_id:config.views.route_optimization_preview,
                data: [{ events: response.events, team: response.team }],
                helpers:[]
            });
        }
    };
    
    var selected_date;
    var model = {
        teams:{},
        free_members:{},
        free_items:{},
        members: {},
        items: {},
        free_tools:{},
        tools:{},
        absences:{}
    };
    var public = {
        
        init:function(){
            $(document).ready(function(){
                public.events();
                public.events_delegate();
                if($.cookie('scheduler_mode') && $.cookie('scheduler_mode')=="unit"){
                    _private.menuElements();
                    public.init_unit();
                }

            });
        },

        events:function(){
            public.is_events = 1;

            $(document).on('click', config.events.create_team_btn, _private.create_team_popup);
            $(document).on('finished.fu.wizard', config.ui.wizard, _private.create_new_team);
            $(document).on('change', config.events.edit_team_crew_type, _private.change_team_crew_type);

            $(document).on('show.bs.dropdown', config.events.members_list, _private.render_free_members);
            $(document).on('show.bs.dropdown', config.events.equipment_list, _private.render_free_equipment);

            $(document).on('click', config.events.day_off_btn, _private.day_off_toggle);


            $(document).on('click', '.dhx_scale_bar .addMember', function () {
                var obj = $(this).parents('.emp-dropdown');
                var emp_id = $(this).data('emp_id');
                var crew_id = $(this).parents('.dhx_scale_bar:first').find('a[data-crew_id]:first').data('crew_id');
                var emp_name = $(this).text();
                var emailid = $(this).attr('data-emailid');
                var emp_field_worker = $(this).data('field_worker');
                var team_color = $(this).parents('.dhx_scale_bar:first').find('a[data-crew_id]:first').data('color');
                var date = scheduler.getState().date;
                var dateYMD = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                if(!crew_id)
                {
                    return false;
                }

                addToCrewList({
                    team_id:crew_id,
                    team_color:team_color,
                    emp_name:emp_name,
                    employee_id:emp_id,
                    emailid:emailid,
                    crew_name:$('[data-crew_id="' + crew_id + '"]').text(),
                    field_worker:emp_field_worker
                });

                callback = function(resp){
                    if (resp.status != 'ok')
                        alert('Ooops! Error');
                    else
                    {
                        lastUpdateId = resp.update.update_id;
                    }

                    if(resp.new_team) {
                        _private.add_member(emp_id, crew_id);
                        var obj = $('.team-hours[data-team-id="' + resp.new_team.team_id + '"]');
                        $(obj).find('.teamManHoursText').text(resp.new_team.team_man_hours);
                        var popoverContent = $(obj).attr('data-content');
                        var tempDiv = document.createElement('div');
                        $(tempDiv).append(popoverContent);
                        $(tempDiv).find('input.teamManHr-' + resp.new_team.team_id).attr('value', resp.new_team.team_man_hours);
                        $(obj).attr('data-content', $(tempDiv).html());
                    }
                    if(resp.old_team) {
                        var obj = $('.team-hours[data-team-id="' + resp.old_team.team_id + '"]');
                        $(obj).find('.teamManHoursText').text(resp.old_team.team_man_hours);
                        var popoverContent = $(obj).attr('data-content');
                        var tempDiv = document.createElement('div');
                        $(tempDiv).append(popoverContent);
                        $(tempDiv).find('input.teamManHr-' + resp.old_team.team_id).attr('value', resp.old_team.team_man_hours);
                        $(obj).attr('data-content', $(tempDiv).html());
                    }
                    teamOrder($('ul.crew_' + crew_id + '>li.label'));
                    var height = $('#crewsList').is(':visible') ? $('.crews-list-container').height() : 0;
                    $('.team-amount').animate({bottom: height+'px'});
                    $('.team-hours').animate({bottom: (height+30)+'px'});
                    $('.teams-amount').animate({bottom: height+'px'});
                    $('.teams-hours').animate({bottom: (height+30)+'px'});
                    $('.teams-stat-btn').animate({bottom: (height+60)+'px'});
                }
                Members.addMember({employee_id:emp_id, crew_id:crew_id, date:dateYMD}, callback);


                return false;
            });

            $(document).on('click', config.events.add_tool, _private.add_team_tool);

            $(document).on('click', '.dhx_scale_bar .addItem', function () {
                var obj = $(this).parents('.emp-dropdown');
                var item_id = $(this).data('item_id');
                var group_id = $(this).data('item_group_id');
                var group_color = $(this).data('origin-color');
                var crew_id = $(this).parents('.dhx_scale_bar:first').find('a[data-crew_id]:first').data('crew_id');
                var item_name = $(this).text();
                var date = scheduler.getState().date;
                var updatedSect = false;
                var dateYMD = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                Equipment.setEquipmentToTeam($(this));

                addEquipmentCallback = function(resp){
                    _private.add_item(item_id, crew_id);

                    if (resp.status != 'ok')
                        alert('Ooops! Error');
                    else
                        lastUpdateId = resp.update.update_id;
                    teamOrder($('ul.crew_' + crew_id + '>li.label'));
                    var height = $('#crewsList').is(':visible') ? $('.crews-list-container').height() : 0;
                    $('.team-amount').animate({bottom: height+'px'});
                    $('.team-hours').animate({bottom: (height+30)+'px'});
                    $('.teams-amount').animate({bottom: height+'px'});
                    $('.teams-hours').animate({bottom: (height+30)+'px'});
                    $('.teams-stat-btn').animate({bottom: (height+60)+'px'});
                }

                Equipment.addEquipment({item_id:item_id, crew_id:crew_id, date:dateYMD}, addEquipmentCallback);

                return false;
            });

            $(document).on('click', config.events.delete_tool_from_team, _private.delete_tool_from_team);
            $(document).on('click', config.events.delete_member_from_team, _private.delete_member_from_team);
            $(document).on('click', config.events.delete_item_from_team, _private.delete_item_from_team);
            $(document).on('change', config.events.reason_absence, _private.add_to_absence);
            $(document).on('click', config.events.delete_from_day_off, _private.delete_absence);
        },

        day_off_hide:function () {
            _private.day_off_hide();
        },

        is_events_delegate: false,

        events_delegate: function(){
            if(public.is_events_delegate == true)
                return;

            public.is_events_delegate = true;

            $(document).on('show.bs.dropdown',  ".edit-team-dropdown", function(event){
                var team_id = $(event.relatedTarget).data('crew_id');
                _templates.renderTeamPupUp(team_id);

                width = $("#edit-team-dropdown-"+team_id).width();
                full_width = width*scheduler._cols.length;
                offset = $("#edit-team-dropdown-"+team_id).offset().left;

                if(width+offset < 850){

                    left = (full_width-offset > 850)?0:(full_width-offset+121-870);

                    $("#edit-team-dropdown-"+team_id+'>ul').css('left', left);
                    $("#edit-team-dropdown-"+team_id+'>ul').css('right', 'auto');
                }
                else{

                    right = (offset > 850)?0:(offset-121+width-863);
                    $("#edit-team-dropdown-"+team_id+'>ul').css('left', 'auto');
                    $("#edit-team-dropdown-"+team_id+'>ul').css('right', right);
                }
            });

            $(document).on('click', '.deleteTeam', function(e){
                var $this = $(e.currentTarget);
                if(confirm('Are you sure you want to delete the "' + $this.data('text') + '"'))
                {
                    var team_id = $this.data('team_id');
                    if(teamHasEvents(team_id))
                    {
                        errorMessage('Ooops! Error! Team has events.');
                        $('#processing-modal').modal('hide');
                        return false;
                    }

                    Common.request.send("/schedule/teams/deleteTeam", {team_id:team_id}, function(resp){
                        if(lastScrollPosition && lastScrollPosition > (sections.length - 1 - scheduler._props.unit.size))
                            lastScrollPosition = sections.length - 1 - scheduler._props.unit.size;
                        ScheduleUnit.resetTeams();
                    }, function (response) {
                        errorMessage(response.error);
                    });
                }
                return false;
            });

            $(document).on('click', config.events.optimize_route, _private.optimize_route);
        },

        init_unit:function(){
            setMyColorpicker($('.mycolorpicker'));
            _private.resetTeams();
        },

        onViewChange:function(new_mode , new_date){

            if(!public.is_events)
                public.events();

            _private.menuElements();
            scheduler.xy.scale_height = -60;
            scheduler.config.first_hour = SCHEDULER_STARTS_FROM;
            scheduler.config.last_hour = SCHEDULER_ENDS_AT;

            $('.team-amount').remove();
            $('.team-hours').remove();
            //$('.crewsList').show();
            if(new_mode == 'month')
            {
                $('.teams-amount').remove();
                $('.teams-hours').remove();
                $('.teams-stat-btn').remove();
                $('.teams-stat-block').remove();
                $('.day-note').hide();
                $('.saveNote').hide();
                $('.free-members-label').hide();

                $('#crewsList').parent().hide();
                crewsListVisible = true;
                //$('.crewsList').attr('disabled', 'disabled');
                scheduler.config.drag_create = false;
                min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
                max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);
                scheduler.clearAll();

                return false;
            }
            else
            {
                $('.day-note').css('height', '');
                $('.saveNote').css('height', '');
                $('.day-note').show();
                $('.saveNote').show();
                
                if(new_mode == 'day' || new_mode == 'week')
                {
                    
                    $('.day-note').attr('style', $('.day-note').attr('style') + ';height:' + (parseInt($('.day-note').css('height')) + 1) + 'px!important');
                    $('.saveNote').attr('style', $('.saveNote').attr('style') + ';height:' + (parseInt($('.saveNote').css('height')) + 1) + 'px!important');
                }
            }

            if(new_mode != 'unit' || processUpdateSections)
            {
                $('#processing-modal').modal();
                if(new_mode != 'unit' && new_mode != 'week')
                {
                    $('#crewsList').parent().slideUp();
                    crewsListVisible = true;
                    //$('.crewsList').attr('disabled', 'disabled');
                    scheduler.config.drag_create = false;

                    getScheduleData(function(){ changeFonts(); });
                }
                
                setTimeout(function(){

                    $('.sortable').sortable({
                        start: function(event, ui) {
                            item = ui.item;
                            $(item).css('white-space', 'nowrap');
                            newList = oldList = ui.item.parent();
                            
                            if($(event.currentTarget).hasClass('freeItems')){
                                $('ul.freeItems').addClass('items-less-height');
                            }
                            
                            if($(event.currentTarget).hasClass('freeMembers')){
                                $('ul.freeMembers').addClass('items-less-height');
                            }
                        },
                        stop: function(event, ui) {

                            $('ul.freeItems').removeClass('items-less-height');
                            $('ul.freeMembers').removeClass('items-less-height');
                            
                            var item = ui.item;
                            $(item).css('white-space', 'normal');

                            empId = $(item).data('emp_id');
                            itemId = $(item).data('item-id');
                            if(itemId==undefined || !itemId)
                                itemId = $(item).data('item_id');

                            new_id = $(newList[0]).data('bonus-team-id');
                            old_id = $(oldList[0]).data('bonus-team-id');

                            if(itemId && !new_id){
                                return false;
                            }

                            if(new_id != old_id)
                            {
                                if(empId)
                                {
                                    if($('a[data-crew_leader="' + empId + '"]').length || new_id == -1)
                                        return false;

                                    if($('li.label[data-item-id] a[data-driver_id="' + empId + '"]').length) {
                                        $(item).find('.driverFor').remove();
                                        var dRteamId = old_id;
                                        var dRitemId = $('li.label[data-item-id] a[data-driver_id="' + empId + '"]').parent().attr('data-item-id');
                                        var dRdriverId = '';
                                        $.ajax({
                                            global: false,
                                            method: "POST",
                                            data: {team_id:dRteamId,user_id:empId,driver_id:dRdriverId},
                                            url: base_url + "schedule/ajax_change_driver",
                                            dataType:'json',
                                            success: function(response) {
                                                $('li.label[data-item-id="' + dRitemId + '"] a').popover('hide');
                                                var text = '(N/A)';
                                                if(dRdriverId) {
                                                    text = '(' + $('.changeDriver option[value="' + dRdriverId + '"]').attr('data-emailid') + ')';
                                                }
                                                $('li.label[data-item-id] a[data-driver_id="' + empId + '"]').text(text);
                                                $('li.label[data-item-id] a[data-driver_id="' + empId + '"]').attr('data-driver_id', dRdriverId);
                                            }
                                        });
                                    }

                                    $(item).attr('style', $(newList[0]).data('team-style'));

                                    callback = function(resp){
                                        if(resp.update)
                                            lastUpdateId = resp.update.update_id;
                                        if(resp.status == 'error') {
                                            $(item).attr('style', $('.freeMembers').data('team-style'));
                                            $(item).appendTo('.freeMembers.sortable');
                                            return false;
                                        }

                                        if(Object.keys(resp.new_team).length){
                                            _private.add_member(empId, new_id, old_id);
                                        }
                                        else{
                                            _private.delete_member(empId, old_id);
                                        }

                                        if(resp.new_team) {
                                            var obj = $('.team-hours[data-team-id="' + resp.new_team.team_id + '"]');
                                            $(obj).find('.teamManHoursText').text(resp.new_team.team_man_hours);
                                            var popoverContent = $(obj).attr('data-content');
                                            var tempDiv = document.createElement('div');
                                            $(tempDiv).append(popoverContent);
                                            $(tempDiv).find('input.teamManHr-' + resp.new_team.team_id).attr('value', resp.new_team.team_man_hours);
                                            $(obj).attr('data-content', $(tempDiv).html());
                                        }
                                        if(resp.old_team) {
                                            var obj = $('.team-hours[data-team-id="' + resp.old_team.team_id + '"]');
                                            $(obj).find('.teamManHoursText').text(resp.old_team.team_man_hours);
                                            var popoverContent = $(obj).attr('data-content');
                                            var tempDiv = document.createElement('div');
                                            $(tempDiv).append(popoverContent);
                                            $(tempDiv).find('input.teamManHr-' + resp.old_team.team_id).attr('value', resp.old_team.team_man_hours);
                                            $(obj).attr('data-content', $(tempDiv).html());
                                        }
                                        style = $('.freeMembers').data('team-style');
                                        emp_name = $(item).text();

                                        $('.emp-dropdown').find('[data-employee_id='+empId+']').remove();
                                        $('.teamLead[data-team="' + old_id + '"] option[value="' + empId + '"]').remove();
                                        if(new_id){
                                            teamOrder($(newList[0]).children('li.label'));
                                        }

                                        $('.employee_'+empId).css(style);
                                    }

                                    Members.addMember({ employee_id: empId, crew_id: new_id, old_crew_id: old_id, date: scheduler.getState().date.toDateString() }, callback);

                                    return true;
                                }
                                if(itemId && new_id)
                                {
                                    if($('li.label[data-item-id="' + itemId + '"] a[data-driver_id]')) {
                                        if($('li.label[data-item-id="' + itemId + '"][data-item_group_id="16"]').length) {
                                            var oldDriverId = $('li.label[data-item-id="' + itemId + '"] a[data-driver_id]').attr('data-driver_id');
                                            $('li.label.employee_' + oldDriverId).find('.driverFor[data-driver-for-item="' + itemId + '"]').remove();
                                        }
                                        var teamId = old_id;
                                        var itemId = itemId;
                                        var driverId = '';
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
                                                $('li.label[data-item-id="' + itemId + '"] a').attr('data-content', '');
                                            }
                                        });
                                    }

                                    addEquipmentCallback = function(response){
                                        Equipment.buildEquipment({'item':item, 'itemId':itemId, 'new_team_id':new_id, 'old_team_id':old_id, 'response':response});
                                        if(new_id != -1){
                                            _private.add_item(itemId, new_id, old_id);
                                            teamOrder($(newList[0]).children('li.label'));
                                        }
                                        else{
                                            _private.delete_item(itemId, old_id);
                                        }
                                    }

                                    Equipment.addEquipment({item_id: itemId, crew_id: new_id, old_crew_id: old_id, date: scheduler.getState().date.toDateString() }, addEquipmentCallback);
                                    return true;
                                }
                                else
                                    return false;
                            }
                            else {
                                teamOrder($(newList[0]).children('li.label'));
                            }                        
                            
                        },
                        change: function(event, ui) {
                            if(ui.sender){
                                newList = ui.placeholder.parent();
                            }
                        },
                        connectWith: ".sortable",
                        placeholder: "sortable-placeholder",
                        items: "> li.label",
                        zIndex: 9999999,
                        helper: "clone",
                        appendTo: document.body
                    }).disableSelection();
                    $('#processing-modal').modal('hide');

                }, 500);
                
                return true;
            }

            //$('.crewsList').removeAttr('disabled');
            if(crewsListVisible == true)
                $('#crewsList').parent().show();

            scheduler.config.drag_create = true;
            scheduler.config.readonly = false;

            var obj = $(this);
            var date = scheduler.getState().date;
            var dateYMD = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
            min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
            max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);

            _private.resetTeams();

            return true;
        },

        getTeam: function(id){
            if(model.teams[id]!=undefined)
                return model.teams[id];
            return {};
        },

        resetTeams:function (team_id) {
            _private.resetTeams(team_id);
            if(!public.is_events){
                public.events();
            }
        },

        setField: function(team_id, field, value){
            if(model.teams[team_id] == undefined)
                return;

            model.teams[team_id][field] = value;
        },

        optimizeRouteSuccess:_private.optimizeRouteSuccess
    }

    public.init();
    return public;
}();
