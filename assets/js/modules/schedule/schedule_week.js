var ScheduleWeek = function(){
    var model = {};
    var config = {

        ui:{
            team_members_container: '.week-team-members-container',
            week_member_filter: '.week-member-filter',

            filter_user: '#week-member-filter option:selected',
            filter_crew: '#week-crew_type-filter option:selected'
        },
        
        events:{
            team_members_container: '.week-team-members-container',
            week_member_filter: '.week-member-filter',
            week_crew_type_filter: '#week-crew_type-filter'
        },

        route:{
            
        },

        view: {
            week_member_filter : "#week-member-filter-view",
            statistic_total_block: "#statistic-total-block-view",
            schedule_stats_container: "#schedule-stats-container"
        },

        templates:{
            week_section_header : "#week-section-header-tmp",
            week_member_filter : "#week-member-filter-tmp",
            statistic_total_block: "#statistic-total-block-tpl",
            schedule_stats_container: "#schedule-stats-container-tpl"
        },
        scheduler: function () {
            var maxHeight = 0;
            $(config.ui.team_members_container).each(function(){
                if ($(this).height() > maxHeight) {
                    maxHeight = $(this).height();
                }
            });
            scheduler.xy.scale_height = maxHeight;
            scheduler.config.drag_create = false;
            scheduler.config.readonly = true;
        }
    }
    var _templates = {
        init_templates:function () {
            scheduler.templates.week_date_class = function(start, today){
                return "week-section";
            };

            scheduler.templates.week_scale_date = function(date){
                team = {};
                if(model.teams[moment(date).format("YYYY-MM-DD")]!=undefined)
                    team = model.teams[moment(date).format("YYYY-MM-DD")];

                var renderView = {template_id:config.templates.week_section_header,  render_method:'variable', data:[team] , helpers:[]};
                return Common.renderView(renderView);
            };
        },
        main_date: function () {

            scheduler.templates.week_date = function(start, end){
                var y_format = '';
                if(moment(start).format("YYYY") != moment(end).format("YYYY")){
                    var y_format = ", YYYY";
                }
                return moment(start).format("MMM DD"+y_format)+ " - "+ moment(end).subtract(1, 'days').format("MMM DD"+y_format);
            };
        },

        render_filter: function () {
            if(model.members.length==0)
                return false;

            return Common.renderView({
                template_id:config.templates.week_member_filter,
                view_container_id: config.view.week_member_filter,
                data:[{members: model.members, user_id: model.user_id, team_crew_id:model.team_crew_id, crews: model.crews}],
                helpers:[]
            });
        }
    };
    var _private = {
        init:function(){
        
        },

        resetTeams: function(){

            scheduler.config.drag_create = false;
            scheduler.config.readonly = true;

            _private.menuElements();
            _private.reset_model();
            _templates.main_date();

            var ajax_crews_members_request = ScheduleCommon.getWeekRequestConditions();
            processUpdateSections = true;
            Common.request.send('/schedule/scheduleWeekCrews', ajax_crews_members_request, function(response) {

                model.teams = response.data.teams;
                model.members = response.data.members;
                model.user_id = response.data.user_id;
                model.team_crew_id = response.data.team_crew_id;
                model.total = response.data.total;
                model.total_hrs = response.data.total_hrs;
                _templates.init_templates();

                scheduler.updateView();
                config.scheduler();
                scheduler.updateView();

                _private.init_board();
                //_private.render_statistic();

                getScheduleData(function(){
                    changeFonts();
                });

                processUpdateSections = false;

            }, function () {}, true);

            scheduler.set_sizes();
            ScheduleCommon.scheduleBodyHeight();
        },

        init_board: function(){
            _templates.render_filter();
        },


        reset_model: function () {
            model = {
                user_id:0,
                team_crew_id:0,
                team_leaders_dropdown:[],
                blocked_timeline:{},
                teams: {},
                absence: {},
                members:{},
                team_leaders:[],
                team:{},
                total:0,
                total_hrs:0,
                crews: scheduleGlobal.crews
            };
        },

        go_to_date: function (e) {
            var date = $(e.currentTarget).data("date");
            scheduler.setCurrentView(moment(date)._d, "unit");
        },

        render_statistic:function(){

            if($(config.ui.filter_user).val()){
                $('.schedule-stats').show();
            }
            else{
                $('.schedule-stats').hide();
            }

            var sectionWidth = [];
            $('.week-section').each(function () {
                sectionWidth.push($(this).width());
            });

            var statistics_sections = [];
            $.each(model.teams, function (date, team) {
                statistics_sections.push(team.statistic);
            });

            $.each(statistics_sections, function (key, value) {
                statistics_sections[key]['sectionWidth'] = sectionWidth[key]+1;
            });

            Common.renderView({
                template_id:config.templates.schedule_stats_container,
                view_container_id: config.view.schedule_stats_container,
                data:statistics_sections,
                helpers:{}
            });

            Common.renderView({
                template_id:config.templates.statistic_total_block,
                view_container_id:config.view.statistic_total_block,
                data:[{total:model.total, total_hrs:model.total_hrs}],
                helpers:{}
            });
        },

        menuElements:function(){
            $(config.view.week_member_filter).show();

            $(".crews-list-container").hide();
            $('.free-members-label').hide();
            $('.day-off-btn').hide();
            //$('.crewsList').hide();

            $(".btn-stats, .schedule-stats").hide();
        },
    }
    
    var selected_date;

    var public = {
        is_events: 0,

        init:function(){

            window.addEventListener("DOMContentLoaded", function(){
                if($.cookie('scheduler_mode') && $.cookie('scheduler_mode')=="week"){
                    _private.menuElements();
                    _private.resetTeams();

                    if(!public.is_events){
                        public.events();
                        public.scheduler_events();
                    }
                }
            });

        },

        events:function(){
            public.is_events = 1;
            $(document).on("click", config.events.team_members_container, _private.go_to_date);
            $(document).on("change", config.events.week_member_filter, _private.resetTeams);
            $(document).on("change", config.events.week_crew_type_filter, _private.resetTeams);
        },

        scheduler_events: function(){
            ScheduleCommon.active_events['week'] = [];
            /*
            ScheduleCommon.active_events['week'].push(scheduler.attachEvent('onAfterSchedulerResize', function() {
                _private.render_statistic();
            }));
            */
        },

        onViewChange:function(new_mode , new_date){
            config.scheduler();

            _private.resetTeams();
            if(!public.is_events){
                public.events();
                public.scheduler_events();
            }
        },

        resetTeams: _private.resetTeams
    }

    public.init();
    return public;
}();
