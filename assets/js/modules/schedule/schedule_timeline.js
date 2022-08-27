var ScheduleTimeline = function(){
    var model = {};
    var config = {

        ui:{
            blurClassName: 'blur',
            schedule_y_headers: '.schedule-y-headers',
            schedule_y_headers_teams:'.schedule-y-headers-teams',
            free_members_list:'#free-members-list',
            free_equipment_list:'#free-equipment-list',

            timeline_team_id:"#timeline-team-id",
            timeline_team_modal:'#timeline-team-modal',

            timeline_team_date_start: '#timeline-team-date-start',
            timeline_team_date_end: '#timeline-team-date-end',
            timeline_team_color:'.timeline-team-color',
            members:'#timeline-team-members',
            items:'#timeline-team-items',
            team_leader:'.timeline-team-leader',
            select2_input:'.select2-input',
            team_leaders_dropdown: '#team-leaders-dropdown',
            popover: '[data-toggle="popover"]',
            form_error:'.form-error',
            create_timeline_team:'.create-timeline-team'
        },

        events:{
            timeline_team_modal:'#timeline-team-modal',
            free_members_btn:'#free-members-btn',
            free_equipment_btn:'#free-equipment-btn',
            timeline_team_type:'.timeline-team-type',
            timeline_add_member_to_team: '.timeline-add-member-to-team',
            timeline_add_item_to_team: '.timeline-add-item-to-team',
            delete_team:'.delete-timeline-team',
            reset_team_modal:'.reset-team-modal'
        },

        route:{

        },

        templates:{
            schedule_y_header:'#schedule-y-header-tmp',
            timeline_team_modal:'#timeline-team-modal-body-tmp',
            free_members_list: '#free-members-list-content-tmp',
            free_items_list: '#free-equipment-list-content-tmp',
            team_leaders_dropdown: '#team-leaders-dropdown-tmp',
            team_warning: '#team-warning-tmp'
        },

        views: {
            timeline_team_modal:'#timeline-team-modal-body',
            free_members_list: '#free-members-list-content',
            free_items_list: '#free-equipment-list-content',
            team_leaders_dropdown: '#team-leaders-dropdown',
            team_warning: '#team-warning',
            timeline_view_options: function(){
                var step_length = _private.getStep(SCHEDULER_STARTS_FROM, SCHEDULER_ENDS_AT);
                var units_count = (24 * 7)/step_length;

                return {
                    name: "timeline",
                    x_unit: "hour",
                    x_date: (INT_TIME_FROMAT == 12)?"%h:%i %a":"%H:%i",

                    x_start:0,
                    x_size: units_count,
                    x_step: step_length,

                    y_unit:scheduler.serverList("sections", []),
                    y_property: "section_id", // mapped data property
                    render:"bar",
                    scrollable: true,

                    second_scale:{
                        x_unit: "day", // unit which should be used for second scale
                        x_date: "%d, %F %d" // date format which should be used for second scale, "July 01"
                    },
                    round_position: false,
                    preserve_length: false,
                    section_autoheight:false,
                    dy:120, // Line height
                    event_dy:"full", // event height
                    event_min_dy:120 // min event height
                };
            }
        },

        select2: function () {
            return [
                {
                    selector: '#timeline-team-members',
                    options: {
                        placeholder:'Crew Members List',
                        separator: ',',
                        multiple:true,
                        containerCss: 'background-color:#e8f0fe',
                        containerCssClass: function (obj) {
                            return 'timeline-team-members-container pull-right';
                        },
                        dropdownCssClass: "timeline-team-members-dropdown",
                        width: '100%',
                        allowClear: true,
                        data: model.timeline_free_members,
                        initSelection : function (element, callback) {
                            callback(model.timeline_team_members);
                        },

                        formatResult:function format(item) {
                            return null;
                        },
                    },

                    init_selected_data: model.timeline_team_members,
                    onchange: function (obj) {
                        if (obj.removed != undefined) {

                            var deleteCondition = obj.removed;
                            _private.delete_team_member(deleteCondition)
                        }
                    },
                    opening: function (obj) {
                        return false;
                    },
                    focus: function (obj) {
                        setTimeout(function () {
                            $(config.ui.select2_input).trigger("blur");
                        }, 1);
                        return true;
                    }

                },{
                    selector: '#timeline-team-items',
                    options: {
                        placeholder:'Crew Equipment List',
                        separator: ',',
                        multiple:true,
                        containerCss: 'background-color:#e8f0fe',
                        containerCssClass: function (obj) {
                            return 'timeline-team-items-container pull-right';
                        },
                        dropdownCssClass: "timeline-team-members-dropdown",
                        width: '100%',
                        allowClear: true,
                        data:model.timeline_free_items,
                        initSelection : function (element, callback) {
                            callback(model.timeline_team_items);
                        },
                        formatResult:function format(item) {
                            var originalOption = item.element;
                            return item.text
                        }
                    },

                    init_selected_data: model.timeline_team_items,
                    onchange: function (obj) {
                        if (obj.removed != undefined) {
                            var deleteCondition = obj.removed;
                            _private.delete_team_item(deleteCondition)
                        }
                    },
                    opening: function (obj) {
                        return false;
                    },
                    focus: function (obj) {
                        setTimeout(function () {
                            $(config.ui.select2_input).trigger("blur");
                        }, 1);
                        return true;
                    }
                }
            ]
        },
        scheduler: function () {
            scheduler.xy.scale_height = 40;
            scheduler.config.drag_create = true;
            scheduler.config.readonly = false;
            scheduler.config.first_hour = SCHEDULER_STARTS_FROM;
            scheduler.config.last_hour = SCHEDULER_ENDS_AT;
            scheduler.config.multisection = true;
            scheduler.config.mark_now = false;
            scheduler.config.multi_day = true;
            scheduler.config.details_on_dblclick = true;
        }
    };

    var _private = {
        team_bookmark_lines:[],
        init:function(){

        },

        resetTeams: function(team_id){
            public.scheduler_events();
            _templates.main_date();

            _private.team_bookmark_lines = [];

            _private.reset_model();
            _private.menuElements();

            if($('.popover').length)
                $('.popover').hide();

            var ajax_crews_members_request = ScheduleCommon.getWeekRequestConditions();

            ajax_crews_members_request['from'] = moment(scheduler.date.week_start( moment(ajax_crews_members_request['from']).toDate() )).format("YYYY-MM-D");

            $.post(baseUrl + 'schedule/scheduleTimelineCrews', ajax_crews_members_request, function(response){


                    model.teams = response.teams;
                    model.absence = response.absence;
                    model.members = response.members;
                    model.team_leaders = response.team_leaders;

                    scheduler.date['timeline_start'] = scheduler.date.week_start;
                    ScheduleTimelineCore.clear_all_timespan();

                    processUpdateSections = true;
                    _private.init_board();

                    _templates.init_templates();
                    config.scheduler();
                    scheduler.updateView();

                    if(response.update)
                        lastUpdateId = response.update.update_id;

                    scheduler.set_sizes();

                    getScheduleData(function(){
                        changeFonts();
                        _private.init_popover();
                        _templates.team_bookmark_styles();
                    });
                    processUpdateSections = false;
                    return false;
            }, 'json');
        },

        init_board: function(){
            teams = [];
            _private.create_team_button_dates();

            model.team_leaders.forEach(function (team, key) {

                leader_name = (team.team_leader)?team.team_leader.full_name:'No leader';
                teams.push({section_id:parseInt(team.timeline_id), key:parseInt(team.timeline_id), label:leader_name, team_leader_user_id:team.team_leader_user_id, teams:model.teams[team.timeline_id]});

                /*   ------   Mark timelines ------- */
                _private.set_no_team_intervals(team.timeline_id, model.teams[team.timeline_id]);
                _private.team_bookmark(team.timeline_id, model.teams[team.timeline_id]);

                if(model.members[team.timeline_id]!=undefined)
                    _private.set_member_in_other_team(team.team_id, team.timeline_id, model.members[team.timeline_id]);

                if(model.absence[team.timeline_id]!=undefined){
                    _private.set_mamber_absence(team.timeline_id, model.absence[team.timeline_id]);                }

                /*   ------   Mark timelines ------- */
            });

            _templates.hide_days(teams);

            scheduler.updateCollection('sections', teams);
        },

        team_bookmark: function(timeline_id, teams){
            teams.forEach(function (team, key) {
                _private.team_bookmark_lines.push(team.team_id);

                scheduler.addMarkedTimespan({
                    css:   "team-bookmark team-bookmark-"+team.team_id,
                    type:  "says",
                    html: '<span style="background:'+team.team_color+'">&nbsp;</span>',
                    start_date: moment(ScheduleTimelineCore.dates.dayStart(team.team_date_start))._d,
                    end_date : moment(ScheduleTimelineCore.dates.dayEnd(team.team_date_end))._d,
                    zones: "fullweek",
                    sections:{
                        timeline: parseInt(timeline_id),
                    }
                });
            });
        },

        set_no_team_intervals:function(timeline_id, teams){

            if(teams.length==0)
                return true;

            ScheduleTimelineCore.add_time_marker(
                timeline_id,
                moment(scheduler.getState().min_date).format("YYYY-MM-D")+' '+scheduler.config.first_hour+":00",
                moment(scheduler.getState().max_date).subtract('days', 1).format("YYYY-MM-D")+' '+scheduler.config.last_hour+":00",
                "no_team"
                );

            teams.forEach(function (team, key) {
                ScheduleTimelineCore.delete_time_marker(
                    timeline_id,
                    ScheduleTimelineCore.dates.dayEnd(moment(team.team_date_start).subtract('days', 1).format("YYYY-MM-D")),
                    ScheduleTimelineCore.dates.dayStart(moment(team.team_date_end).add('days', 1).format("YYYY-MM-D"))
                );
            });
        },

        set_member_in_other_team: function(team_id, timeline_id, teams){
            if(teams.length==0)
                return true;

            var from = false;
            var to = false;
            teams.forEach(function (team, key) {
                if(timeline_id == team.timeline_id)
                    return;

                ScheduleTimelineCore.delete_time_marker(
                    timeline_id,
                    ScheduleTimelineCore.dates.dayEnd(moment(team.team_date_start).subtract('days', 1).format("YYYY-MM-D")),
                    ScheduleTimelineCore.dates.dayStart(moment(team.team_date_end).add('days', 1).format("YYYY-MM-D")),
                );

                ScheduleTimelineCore.add_time_marker(
                    timeline_id,
                    moment(team.team_date_start).format("YYYY-MM-D")+' '+scheduler.config.first_hour+":00",
                    moment(team.team_date_end).format("YYYY-MM-D")+' '+scheduler.config.last_hour+":00",
                    "busy"
                );

                if(model.blocked_intervals[timeline_id]==undefined)
                    model.blocked_intervals[timeline_id] = [];

                from = moment(team.team_date_start).format("YYYY-MM-D")+' '+scheduler.config.first_hour+":00";
                to = moment(team.team_date_end).format("YYYY-MM-D")+' '+scheduler.config.last_hour+":00";
                index = model.blocked_intervals[timeline_id].findIndex(item => (from == item.from && to == item.to));
                if(index==-1){
                    model.blocked_intervals[timeline_id].push({
                        from: from,
                        to: to
                    });
                }
            });
        },

        set_mamber_absence: function(timeline_id, absence){
            if(absence.length==0)
                return true;

            absence.forEach(function (team, key) {
                ScheduleTimelineCore.delete_time_marker(
                    timeline_id,
                    ScheduleTimelineCore.dates.dayEnd(moment(team.absence_ymd).subtract('days', 1).format("YYYY-MM-D")),
                    ScheduleTimelineCore.dates.dayStart(moment(team.absence_ymd).add('days', 1).format("YYYY-MM-D"))
                );

                ScheduleTimelineCore.add_time_marker(
                    timeline_id,
                    moment(team.absence_ymd).format("YYYY-MM-D")+' '+scheduler.config.first_hour+":00",
                    moment(team.absence_ymd).format("YYYY-MM-D")+' '+scheduler.config.last_hour+":00",
                    "holiday"
                );

                if(model.blocked_intervals[timeline_id]==undefined)
                    model.blocked_intervals[timeline_id] = [];

                model.blocked_intervals[timeline_id].push({
                    from:moment(team.absence_ymd).format("YYYY-MM-D")+' '+scheduler.config.first_hour+":00",
                    to:moment(team.absence_ymd).format("YYYY-MM-D")+' '+scheduler.config.last_hour+":00",
                });

            });
        },

        validate_timelines: function(ev, from, to){

            event_size = ScheduleTimelineCore.dates.interval_size(ev.start_date, ev.end_date);

            if(model.blocked_timeline['timeline_'+ev.section_id]!=undefined && model.blocked_timeline['timeline_'+ev.section_id])
                return;

            var in_team = false;
            model.teams[ev.section_id].forEach(function (team) {
                if(ScheduleTimelineCore.dates.date_in_interval(from, {from:team.team_date_start, to:ScheduleTimelineCore.dates.dayEnd(team.team_date_end)}))
                    in_team = true;
            });

            $.each(model.teams, function (key, value) {

                if(key != model.daraged_timeline){

                    model.teams[key].forEach(function (team) {

                        team_size = ScheduleTimelineCore.dates.interval_size(
                            moment(ScheduleTimelineCore.dates.dayStart(team.team_date_start))._d,
                            moment(ScheduleTimelineCore.dates.dayEnd(team.team_date_end))._d
                        );

                        if(team_size < event_size){
                            ScheduleTimelineCore.add_time_marker(
                                key,
                                moment(team.team_date_start).format("YYYY-MM-D"),
                                moment(team.team_date_end).add('days', 1).format("YYYY-MM-D"),
                                "blocked"
                            );
                        }

                    });
                }
            });

            if(in_team){

                model.teams[model.daraged_timeline].forEach(function (team) {
                    team_size = ScheduleTimelineCore.dates.dayEndUnix(moment(team.team_date_end).format("YYYY-MM-D"))-ScheduleTimelineCore.dates.dayStartUnix(moment(team.team_date_start).format("YYYY-MM-D"));
                    if(!ScheduleTimelineCore.dates.date_in_interval(from, {from:team.team_date_start, to:ScheduleTimelineCore.dates.dayEnd(team.team_date_end)}) && event_size > team_size){

                        ScheduleTimelineCore.add_time_marker(
                            model.daraged_timeline,
                            moment(team.team_date_start).format("YYYY-MM-D"),
                            moment(team.team_date_end).add('days', 1).format("YYYY-MM-D"),
                            "blocked"
                        );
                    }
                });

            }
            else{
                model.teams[model.daraged_timeline].forEach(function (team) {
                    ScheduleTimelineCore.add_time_marker(
                        model.daraged_timeline,
                        moment(team.team_date_start).format("YYYY-MM-D"),
                        moment(team.team_date_end).add('days', 1).format("YYYY-MM-D"),
                        "blocked"
                    );
                });

            }

            scheduler.updateView();
            model.blocked_timeline['timeline_'+ev.section_id] = 1;
        },

        validate_timelines_delete: function(timeline_id){
            _templates.team_bookmark_styles();
            if(model.team_leaders.length==0)
                return true;

            model.team_leaders.forEach(function (team) {
                timeline_id = parseInt(team.timeline_id);

                ScheduleTimelineCore.delete_time_marker(
                    timeline_id,
                    ScheduleTimelineCore.dates.scheduler_start(),
                    ScheduleTimelineCore.dates.scheduler_end(),
                    "dhx_time_block"
                );
                model.blocked_timeline['timeline_'+timeline_id] = 0;

                if(model.absence[timeline_id]!=undefined){
                    _private.set_mamber_absence(timeline_id, model.absence[timeline_id]);
                }

                if(model.members[timeline_id]!=undefined){
                    model.teams[timeline_id].forEach(function (team) {
                        _private.set_member_in_other_team(team.team_id, timeline_id, model.members[timeline_id]);
                    });
                }

            });
            scheduler.updateView();
        },

        get_team_modal:function(e){
            $(config.ui.popover).popover('hide');
            _private.schedule_free_members(e.relatedTarget.dataset, _private.render_team_modal);
        },

        reset_team_modal: function(e){
            _private.schedule_free_members(e.currentTarget.dataset, _private.render_team_modal);
            return false;
        },

        schedule_free_members:function(data, callback){
            if(!$(config.views.timeline_team_modal).hasClass(config.ui.blurClassName))
                $(config.views.timeline_team_modal).addClass(config.ui.blurClassName);

            var callbackFn = callback;
            Common.request.send('/schedule/scheduleFreeMembers', data, function(response){
                callbackFn(response);
                $(config.views.timeline_team_modal).removeClass(config.ui.blurClassName);
            });
        },

        render_team_modal:function(response){
            _private.reset_members_model();

            Common.renderView({
                template_id:config.templates.timeline_team_modal,
                view_container_id:config.views.timeline_team_modal,
                data:[response],
                helpers:[]
            });
            _private.render_views(response);

            public.initDateRangePicker('.team-range', moment(response.team_date_start)._d, moment(response.team_date_end)._d, "#timeline-team-modal");
            setMyColorpicker($('.mycolorpicker'));
            Common.init_select2(config.select2());
        },

        render_views:function(response){

            $(config.ui.timeline_team_date_start).val(response.team_date_start);
            $(config.ui.timeline_team_date_end).val(response.team_date_end);

            model.timeline_free_members = response.free_members;
            model.team_leaders_dropdown = response.free_members;
            model.timeline_free_items = response.free_items;

            if(response.team_members!=undefined && model.timeline_team_members.length==0)
                model.timeline_team_members = response.team_members;

            if(response.team_items!=undefined && model.timeline_team_items.length==0)
                model.timeline_team_items = response.team_items;

            if(response.team!=undefined && !model.team_leader)
                model.team_leader = response.team.team_leader_user_id;

            if(response.busy_members_in_other_teams!=undefined){
                model.busy_members_in_other_teams = response.busy_members_in_other_teams;

                response.busy_members_in_other_teams.forEach((value, index) => {
                    option = model.timeline_team_members.findIndex(function(member, index) { return (member.id == value.id) });
                    if(option!=-1)
                        model.timeline_team_members.splice(option, 1);
                });
            }

            if(response.busy_items_in_other_teams!=undefined){
                model.busy_items_in_other_teams = response.busy_items_in_other_teams;

                response.busy_items_in_other_teams.forEach((value, index) => {
                    option = model.timeline_team_items.findIndex(function(item, index) { return (item.eq_id == value.equipment.eq_id) });
                    if(option!=-1)
                        model.timeline_team_items.splice(option, 1);

                });
            }


            if(response.team!=undefined)
                model.team = response.team;

            _private.render_team_leaders();
            _private.render_free_members();
            _private.render_free_items();
            _private.render_warning();
        },

        render_team_leaders: function(){
            Common.renderView({
                template_id:config.templates.team_leaders_dropdown,
                view_container_id:config.views.team_leaders_dropdown,
                data:[{
                    free_members:model.team_leaders_dropdown,
                    team_leader:model.team_leader,
                    team_members:model.timeline_team_members,
                    team: model.team
                }],
                helpers:[]
            });
        },

        render_free_members: function(){
            Common.renderView({
                template_id:config.templates.free_members_list,
                view_container_id:config.views.free_members_list,
                data:[{free_members:model.timeline_free_members}],
                helpers:[]
            });
        },

        render_free_items: function(){
            var group_items = Common.groupBy(model.timeline_free_items, 'group_id');
            Common.renderView({
                template_id:config.templates.free_items_list,
                view_container_id:config.views.free_items_list,
                data:[{free_items_groups:group_items}],
                helpers:[]
            });
        },

        render_warning: function(){
            //var team_id = $(config.ui.timeline_team_modal+' '+config.ui.timeline_team_id).val();
            Common.renderView({
                template_id:config.templates.team_warning,
                view_container_id:config.views.team_warning,
                data:[{
                    team: model.team,
                    busy_members_in_other_teams: model.busy_members_in_other_teams,
                    busy_items_in_other_teams: model.busy_items_in_other_teams
                }],
                helpers:[]
            });
        },

        timeline_add_member_to_team:function(e, item_id){
            var id = item_id;
            if(e)
                id = e.target.dataset.id;

            leader_id = $(config.ui.team_leader).val();
            if(parseInt(leader_id)==0){
                $(config.ui.team_leader).val(id).trigger('change');
                return true;
            }

            option = model.timeline_free_members.findIndex(function(post, index) { return (post.id == id) });
            if(option==-1)
                return false;

            member = model.timeline_team_members.findIndex(function (member, index) { return member.id == id });
            if(member==-1)
                model.timeline_team_members.push(model.timeline_free_members[option]);

            model.timeline_free_members.splice(option, 1);

            select2vals = model.timeline_team_members.map(function(item) {
                return item['id'].toString();
            });
            $(config.ui.members).select2("val", select2vals);
            _private.render_free_members();
        },

        delete_team_member:function(member){
            var id = member.id;

            option = model.timeline_team_members.findIndex(function(post, index) {
                if(post.id == id)
                    return true;
            });

            model.timeline_free_members.unshift(member);
            model.timeline_team_members.splice(option, 1);

            if(model.timeline_team_members.length==0 || member.id == model.team_leader)
                $(config.ui.team_leaders_dropdown).val(0).trigger('change');

            _private.render_free_members();
        },

        change_team_leader:function(e){
            var id = $(this).val();
            model.team_leader = parseInt(id);
            if(parseInt(id)==0)
                return false;

            _private.timeline_add_member_to_team(false, parseInt(id));
        },

        timeline_add_item_to_team:function(e, item_id){
            var id = item_id;
            if(e)
                id = e.target.dataset.id;

            option = model.timeline_free_items.findIndex(function(post, index) { return (post.id == id) });
            if(option==-1)
                return false;
            /* add item to team */
            model.timeline_team_items.push(model.timeline_free_items[option]);
            /* delete item from free items */
            model.timeline_free_items.splice(option, 1);
            /* insert item to select2 team equipment list */
            select2vals = model.timeline_team_items.map(function(item) {
                return item['eq_id'].toString();
            });
            $(config.ui.items).select2("val", select2vals);

            _private.render_free_items();
        },

        delete_team_item:function(item){
            var id = item.id;
            option = model.timeline_team_items.findIndex(function(post, index) { return (post.id == id); });
            model.timeline_free_items.unshift(item);
            model.timeline_team_items.splice(option, 1);
            _private.render_free_items();
        },

        timeline_delete_team:function(e){
            var id = $(this).data('id');
            var leader = $(this).data('team_leader');
            if(confirm('Are you sure you want to delete the '+((!leader)?'team':('"'+leader+'"')))) {
                Common.request.send("/schedule/teams/deleteTeam", {team_id:id}, _private.resetTeams, function (response) {
                    errorMessage(response.error);
                });
            }
            else{
                console.log("No delete");
            }
        },
        /*
        crewsList:function(members, sections){
            $(".crews-list-container").hide();
            $('#crewsList').html('');
        },*/

        menuElements:function(){
            $('.week-member-filter-view').hide();

            $(".crews-list-container").hide();
            $('.free-members-label').hide();
            //$('.crewsList').hide();
            $('.day-off-btn').hide();
            $(".schedule-stats").hide();
        },

        reset_model: function () {
            model = {
                timeline_free_members:[],
                timeline_team_members:[],
                timeline_free_items:[],
                timeline_team_items:[],
                team_leader:0,
                team_leaders_dropdown:[],
                blocked_timeline:{},
                teams: {},
                absence: {},
                members:{},
                team_leaders:[],
                busy_members_in_other_teams:[],
                busy_items_in_other_teams:[],
                team:{},
                blocked_intervals:{}
            };
        },

        reset_members_model: function(){
            model.timeline_free_members = [];
            model.timeline_team_members = [];
            model.timeline_free_items = [];
            model.timeline_team_items = [];
            model.team_leader = 0;
            model.team_leaders_dropdown = [];
            model.busy_members_in_other_teams = [];
            model.busy_items_in_other_teams = [];
            model.team = {};
            model.blocked_intervals = {};
        },

        getStep: function(from, to){
            return 1;

            if(to==0)
                to = 24;
            interval = to-from;

            if(from%2!=0 || to%2!=0)
                return 1;

            if(interval%3==0)
                return 4;

            if(interval%4==0)
                return 2;

            return 1;
        },

        init_popover: function () {
            _private.close_popover();
            $(config.ui.popover).popover({container:'#scheduler_here'});
        },

        close_popover: function () {
            $('.popover').popover('hide');
        },

        create_team_button_dates:function(){
            document.querySelector(config.ui.create_timeline_team).dataset['team_date_start'] = ScheduleTimelineCore.dates.scheduler_start_date();
            document.querySelector(config.ui.create_timeline_team).dataset['team_date_end'] = ScheduleTimelineCore.dates.scheduler_end_date();
        }
    }
    let _templates = {
        init_templates:function () {
            _templates.teams_template();
            _templates.timeline_hours();
        },

        teams_template: function () {
            scheduler.templates.timeline_scale_label = function(key, label, section){
                var renderView = {template_id:config.templates.schedule_y_header,  render_method:'variable', data:[section] , helpers:[]};
                return Common.renderView(renderView);
            };
        },

        main_date: function () {
            var dinner = Math.trunc((SCHEDULER_ENDS_AT-SCHEDULER_STARTS_FROM)/2)+SCHEDULER_STARTS_FROM;

            scheduler.templates.timeline_scale_date = function(date){
                var className = '';
                if(date.getHours() != dinner && date.getHours() != SCHEDULER_STARTS_FROM && date.getHours() != SCHEDULER_ENDS_AT-1){
                    return '';
                }

                if(date.getHours() == SCHEDULER_STARTS_FROM){
                    className = 'start-day-time p-left-5';
                }

                if(date.getHours() == SCHEDULER_ENDS_AT-1){
                    date = moment(date).add(1, 'hours')._d;
                    className = 'end-day-time p-right-5';
                }

                if(date.getHours() == dinner){
                    className = 'dinner-time';
                }

                return '<span class="'+className+'">'+Common.helpers.dateFormat(date, Common.helpers.getTimeFormat())+'</span>';
            };

            scheduler.templates.timeline_cell_class = function(evs, date, section){
                //if(date.getHours() != dinner && date.getHours() != SCHEDULER_STARTS_FROM && date.getHours() != SCHEDULER_ENDS_AT){
                    //return 'opacity-0';
                //}

                if(date.getHours() == (SCHEDULER_ENDS_AT-1)){
                    return 'rightBorder';
                }
                if(date.getHours() == dinner-1){
                    return 'rightBorder';
                }

                return "";
            };

            scheduler.templates.timeline_scalex_class = function(date){
                if ((date.getDay() == 6 || date.getDay() == 0) && !SHOW_WEEKEND){
                    return 'hidden';
                }

                if(date.getHours() != dinner && date.getHours() != SCHEDULER_STARTS_FROM && date.getHours() != SCHEDULER_ENDS_AT){
                    return 'ignored-time';
                }

                if(date.getHours() == dinner){
                    return 'scale-border-top br-left-none p-right-1';
                }

                return 'scale-border-top';
            }

            scheduler.templates.timeline_second_scalex_class = function(date){
                if ((date.getDay() == 6 || date.getDay() == 0) && !SHOW_WEEKEND)
                    return 'hidden';

                return 'scale-border';
            }

            scheduler.templates.timeline_second_scale_date = function(date){
                if ((date.getDay() == 6 || date.getDay() == 0) && !SHOW_WEEKEND)
                    return '';

                return moment(date).format("dddd, MMM DD");
            }

            scheduler.templates.timeline_date = function(start, end){
                var y_format = '';
                if(moment(start).format("YYYY") != moment(end).format("YYYY")){
                    var y_format = ", YYYY";
                }
                return moment(start).format("MMM DD"+y_format)+ " - "+ moment(end).subtract(1, 'days').format("MMM DD"+y_format);
            };

            scheduler.templates.event_class = function(start, end, ev){
                return "timeline-event-color";
            }
        },

        timeline_hours:function () {
            //var step = _private.getStep(SCHEDULER_STARTS_FROM, SCHEDULER_ENDS_AT);

            scheduler.ignore_timeline = function(date) {

                if ((date.getDay() == 6 || date.getDay() == 0) && !SHOW_WEEKEND)
                    return true;

                if(date.getHours() < SCHEDULER_STARTS_FROM || date.getHours() >= SCHEDULER_ENDS_AT)
                    return true;
                /*
                if(step ==1 && (date.getHours()!=SCHEDULER_STARTS_FROM && date.getHours()!=SCHEDULER_ENDS_AT-1 && date.getHours()!=SCHEDULER_STARTS_FROM+parseInt((SCHEDULER_ENDS_AT-SCHEDULER_STARTS_FROM)/2)))
                    return true;
                */
            };


        },

        hide_days:function (teams) {
            if(SHOW_WEEKEND)
               return false;

            teams.forEach(function (team, key) {
                ScheduleTimelineCore.delete_time_marker(
                    team.key,
                    ScheduleTimelineCore.dates.dayEnd(moment(scheduler.getState().min_date).isoWeekday(6).subtract('days', 1).format("YYYY-MM-D")),
                    ScheduleTimelineCore.dates.dayStart(moment(scheduler.getState().max_date).isoWeekday(7).add('days', 1).format("YYYY-MM-D"))
                );
            });
        },

        team_bookmark_styles:function () {

            _private.team_bookmark_lines.forEach(function (item, key) {
                if($('.team-bookmark-'+item).length>1){
                    $('.team-bookmark-'+item+':first span').css({"right":"10px", "border-radius":"0 7px 7px 0"});
                    $('.team-bookmark-'+item+':last span').css({"left":"10px", "border-radius":"7px 0 0 7px"});
                }
                else{
                    $('.team-bookmark-'+item+' span').css({"left":"10px", "right":"10px", "border-radius":"7px"});
                }
            });
        }

    };
    var selected_date;
    let public = {
        is_events: 0,

        init:function(){

            //$(document).ready(function(){
            window.addEventListener("DOMContentLoaded", function(){
                if($.cookie('scheduler_mode') && $.cookie('scheduler_mode')=="timeline"){
                    _private.menuElements();
                    _private.resetTeams();

                    public.events();

                }
            });
        },

        initDateRangePicker(selector, from, to, parent, callback){
            var start = from != false ? moment(from, MOMENT_DATE_FORMAT) : false;
            var end = to  != false  ? moment(to, MOMENT_DATE_FORMAT) : false;

            if(!start)
                start = moment().startOf('day');
            if(!end)
                end = moment().endOf('day');

            dates = {
                'Today': [moment().format(MOMENT_DATE_FORMAT), moment().format(MOMENT_DATE_FORMAT)],
                'Tomorrow': [moment().add(1, 'days').format(MOMENT_DATE_FORMAT), moment().add(1, 'days').format(MOMENT_DATE_FORMAT)],
                'This Week': [moment().startOf('week').format(MOMENT_DATE_FORMAT), moment().endOf('week').format(MOMENT_DATE_FORMAT)],
            };

            picker_options = {
                startDate: start,
                endDate: end,
                ranges: dates,
                showDropdowns: true,
                linkedCalendars: false,
                maxDate: moment().endOf('year'),
                minDate: moment('2010-01-01', 'YYYY-MM-DD'),
                opens: 'bottom',
                locale: {
                    format: MOMENT_DATE_FORMAT
                }
            };
            if(parent)
                picker_options['parentEl'] = parent;

            $(selector).daterangepicker(picker_options, callback);

            $(selector).on('apply.daterangepicker', function(ev, picker) {

                date_start = picker.startDate.format("YYYY-MM-DD");
                date_end = picker.endDate.format("YYYY-MM-DD");
                $(config.ui.form_error).text('');

                var team_id = $(config.ui.timeline_team_modal+' '+config.ui.timeline_team_id).val();
                _private.schedule_free_members({
                    team_id : (team_id==undefined)?0:team_id,
                    team_date_start : date_start,
                    team_date_end : date_end,
                }, function(response){
                    _private.render_views(response);
                    Common.init_select2(config.select2());

                    var checked_members = $(config.ui.members).select2("val");
                    var checked_items = $(config.ui.items).select2("val");
                    if(response.team==undefined && checked_members.length){
                        checked_members.forEach(function (id) {
                            _private.timeline_add_member_to_team(false, id);
                        });
                    }
                    if(response.team==undefined && checked_items.length){
                        checked_items.forEach(function (id) {
                            _private.timeline_add_item_to_team(false, id);
                        });
                    }
                });
            });
        },

        events:function(){
            public.is_events = 1;

            $(config.events.timeline_team_modal).on('show.bs.modal', _private.get_team_modal);
            $(document).delegate(config.events.free_members_btn+','+config.events.free_equipment_btn, 'click', function (e) {
                $(e.currentTarget.dataset.href).show();
            });
            $(document).delegate(config.ui.free_equipment_list+' .close,'+config.ui.free_members_list+' .close', 'click', function (e) {
                $(e.currentTarget).parent().hide();
            });

            $(document).delegate(config.events.timeline_team_type, 'change', function (e) {
                color = $(e.currentTarget).find('option:selected').data('color');
                if(!color)
                    color = '#5785fa';
                $(config.ui.timeline_team_color).val(color);
                setMyColorpicker($('.mycolorpicker'));
            });

            $(document).delegate(config.events.reset_team_modal, 'click', _private.reset_team_modal);
            $(document).delegate(config.events.timeline_add_member_to_team, 'click', _private.timeline_add_member_to_team);
            $(document).delegate(config.events.timeline_add_item_to_team, 'click', _private.timeline_add_item_to_team);
            $(document).delegate(config.ui.team_leader, 'change', _private.change_team_leader);
            $(document).delegate(config.events.delete_team, 'click', _private.timeline_delete_team);

            $(document).delegate(".dhx_marked_timespan", "mouseup", function (e) {
                $('.popover').popover("hide");
                _templates.team_bookmark_styles();
                _private.init_popover();
            });

            $(document).delegate(config.ui.popover, 'mousedown', _private.init_popover);

        },

        scheduler_events: function(){
            ScheduleCommon.detachEvents('timeline');
            ScheduleCommon.active_events['timeline'] = [];

            var dragged_event;
            ScheduleCommon.active_events['timeline'].push(scheduler.attachEvent("onEventDrag", function(id, drag_mode, e){
                $('.popover').hide();
                var action_data = scheduler.getActionData(e);
                var ev = scheduler.getEvent(id);

                if(model.daraged_timeline==undefined || model.daraged_timeline==0)
                    model.daraged_timeline = ev.section_id;

                if(model.daraged_timeline == ev.section_id)
                    _private.validate_timelines(ev, action_data.date, action_data.date);

                return true;
            }));

            ScheduleCommon.active_events['timeline'].push(scheduler.attachEvent("onBeforeEventChanged", function(ev, e, is_new, original){

                var team_id = public.search_team(ev.start_date, ev.section_id);
                var team_id_confirm = public.search_team(ev.end_date, ev.section_id);
                ev.team_leader_user_id = ev.section_id;

                _templates.team_bookmark_styles();

                if(team_id!=team_id_confirm && team_id && team_id_confirm){
                    return false;
                }

                if(!team_id && team_id_confirm){
                    team_id = team_id_confirm;
                }

                if(ev.crew_id != team_id)
                {
                    ev.crew_id = team_id;
                    ev.event_crew_id = team_id;
                }

                return ev;
            }));

            ScheduleCommon.active_events['timeline'].push(scheduler.attachEvent("onBeforeEventChanged", function (ev, e, is_new) {
                //var action_data = scheduler.getActionData(e);
                model.daraged_timeline = 0;
                _private.validate_timelines_delete();
                return true;
            }));

            ScheduleCommon.active_events['timeline'].push(scheduler.attachEvent("onDragEnd", function (ev, e, is_new) {
                _templates.team_bookmark_styles();
            }));

        },

        onViewChange:function(new_mode , new_date){

            scheduler.xy.scale_height = 20;
            _private.menuElements();

            if(processUpdateSections)
                return true;


            _private.resetTeams();
            if(!public.is_events){
                public.events();
            }

            $('#crewsList').parent().hide();

            ScheduleCommon.scheduleBodyHeight();

        },

        saveTeamCallback:function(response){
            if(response.status=='error')
                return false;

            $(config.events.timeline_team_modal).modal('hide');
            _private.resetTeams();
        },

        createTimelineView: function(){
            config.scheduler();

            scheduler.createTimelineView(config.views.timeline_view_options());

            if($.cookie('scheduler_mode') && $.cookie('scheduler_mode')=='timeline')
                scheduler.date[$.cookie('scheduler_mode') + '_start'] = scheduler.date.week_start;

            _templates.timeline_hours();
        },

        resetTeams:function () {
           _private.resetTeams();
            if(!public.is_events){
                public.events();
            }
        },

        search_team: function(from, timeline_id){

            team_id = 0;
            model.teams[timeline_id].forEach(function (team) {
                if(ScheduleTimelineCore.dates.date_in_interval(from, {from:team.team_date_start, to:ScheduleTimelineCore.dates.dayEnd(team.team_date_end)})){
                    team_id = team.team_id;
                }
            });

            return team_id;
        },

        search_blocked_intervals: function(ev){
            result_timeline = false;
            if(model.blocked_intervals[ev.section_id]==undefined)
                return false;

            if(model.blocked_intervals[ev.section_id].length==0)
                return false;

            model.blocked_intervals[ev.section_id].forEach(function (item) {
                if(ScheduleTimelineCore.dates.date_in_interval(item.from, {from:ev.start_date, to:ev.end_date})){
                    result_timeline = ev.section_id;
                    return false;
                }
                if(ScheduleTimelineCore.dates.date_in_interval(item.to, {from:ev.start_date, to:ev.end_date})){
                    result_timeline = ev.section_id;
                    return false;
                }
            });

            return result_timeline;
        },

        get_blocked_intevals: function(section_id){
            if(model==undefined)
                _private.reset_model();

            if(model.blocked_intervals==undefined)
                model.blocked_intervals = {};

            if(section_id==undefined)
                return model.blocked_intervals;

            if(model.blocked_intervals[section_id] == undefined)
                return [];

            return model.blocked_intervals[section_id];
        },

        onAfterUpdateCallback:function (id,action,tid,tag) {
            if(action=='error'){
                errorMessage(tag.getAttribute("message"));
                setTimeout(function () {
                    public.resetTeams();
                }, 3000);
            }
            _templates.team_bookmark_styles();
        },

        getTeam: function (id) {
            if(Object.keys(model.teams).length==0)
                return {};

            var result = {};
            Object.entries(model.teams).forEach(([timeline, teams]) => {
                if(teams.length==0)
                    return;

                option = teams.findIndex(function(post, index) { return (post.team_id == id) });
                if(option!=-1)
                    result = teams[option];
            });

            return result;
        }
    }

    public.init();
    return public;
}();
