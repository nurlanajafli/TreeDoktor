var RoutesMap = function(){
    var config = {

        ui:{
            directions_modal:'#directions-map-modal',
            routes_map: '.routes-map',
            routes_map_id: 'routes-map',
            map_markers_filter_id: 'map-teams-marker-filter-dropdown',
        },

        events:{
            team_lead_filter_checkall: ".team-lead-filter-checkall",
            show_team_markers: ".show-team-markers",
            enable_map_directions: '.enable-map-directions',
            directionsMapBtn: '.directionsMap'
        },

        route:{},

        templates:{
            map_markers_filter: '#map-teams-marker-filter-dropdown-tmp'
        },

        views:{
            map_markers_filter: '#map-teams-marker-filter-dropdown'
        },

        images: {},

        const: {
            office_address: OFFICE_ADDRESS + ', ' + OFFICE_CITY + ', ' + OFFICE_STATE + ', ' + OFFICE_COUNTRY
        }
    };

    var markers_object = {};

    var _private = {

        //directionsService: new google.maps.DirectionsService(),
        //directionsRenderer: [],
        routes: {},
        map: false,

        init_map: function(){

            if(_private.map==false){
                var centermap = new google.maps.LatLng(MAP_CENTER_LAT, MAP_CENTER_LON);
                _private.map = new google.maps.Map(document.getElementById(config.ui.routes_map_id), {
                    zoom: 10,
                    center: centermap,
                    gestureHandling:'greedy',
                    streetViewControl: true,
                    clickableIcons: false,
                    scrollwheel: true,
                    gestureHandling: 'greedy'
                });

                _private.job_routes_filter(_private.map);
            }else{
                //_private.map.getDiv().getAttribute('id') != config.ui.routes_map_id
                //var current_team = ScheduleCommon.get_target_team();
                var teams = ScheduleCommon.get_scheduled_teams();
                _private.render_menu({}, teams);
                _private.render_teams_markers();
            }


        },
        tileListener: {},
        job_routes_filter:function (map) {

            var current_team = ScheduleCommon.get_target_team();
            var teams = ScheduleCommon.get_scheduled_teams();


            _private.tileListener = google.maps.event.addListener(map,'tilesloaded',function () {

                if(map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].length > 0){
                    return;
                }

                if(document.getElementById("team-leads-filter-container")) {
                    map.controls[google.maps.ControlPosition.BOTTOM_RIGHT].clear();
                    document.getElementById("team-leads-filter-container").remove();
                }

                var controlDiv = document.createElement('div');
                var teamLeaderMapFilter = document.createElement('div');
                teamLeaderMapFilter.id = config.ui.map_markers_filter_id;
                teamLeaderMapFilter.className = 'hide';

                controlDiv.style.top = '0px';
                controlDiv.style.bottom = '1px';
                controlDiv.style.zIndex = 3;
                controlDiv.index = 1;
                controlDiv.id = 'team-leads-filter-container';
                controlDiv.appendChild(teamLeaderMapFilter);
                //map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(controlDiv);


                //controlDiv = document.createElement('div');
                //controlDiv.id = 'filter-btn-container';
                var teamLeaderMapFilterButton = document.createElement('button');
                teamLeaderMapFilterButton.className = 'btn btn-default btn-xs';
                teamLeaderMapFilterButton.style.marginRight = '10px';
                teamLeaderMapFilterButton.style.width = '40px';
                teamLeaderMapFilterButton.style.height = '40px';
                teamLeaderMapFilterButton.style.position = 'absolute';
                teamLeaderMapFilterButton.style.right = '0px';
                teamLeaderMapFilterButton.style.bottom = '0px';
                teamLeaderMapFilterButton.style.zIndex = '-1';

                teamLeaderMapFilterButton.id = 'show-team-lead-filter';
                teamLeaderMapFilterButton.setAttribute("data-toggle", "class:hide show_filter");
                teamLeaderMapFilterButton.setAttribute("data-target", config.views.map_markers_filter);

                var teamLeaderMapFilterButtonIcon = document.createElement('i');
                teamLeaderMapFilterButtonIcon.className = 'fa fa-users';
                teamLeaderMapFilterButtonIcon.style.fontSize = '20px';
                teamLeaderMapFilterButtonIcon.style.color = '#4d99f8';
                teamLeaderMapFilterButton.appendChild(teamLeaderMapFilterButtonIcon);

                controlDiv.appendChild(teamLeaderMapFilterButton);
                controlDiv.style.zIndex = 2;
                //controlDiv.index = 1;

                map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(controlDiv);


                is_render_ready = 1;
                window.setTimeout(function() {
                    _private.render_menu(current_team, teams);
                }, 1000);

                google.maps.event.removeListener(_private.tileListener);
            });

            /*
            console.log(_private.tileListener);
            var is_render_ready = 0;
            _private.tileListener = google.maps.event.addListener(map,'tilesloaded',function () {
                is_render_ready = 1;
                window.setTimeout(function() {
                    //console.log($(config.views.map_markers_filter));
                    _private.render_menu(current_team, teams);
                }, 1000);
                google.maps.event.removeListener(_private.tileListener);
            });*/

            //console.log("is_render_ready", is_render_ready);
            //if(_private.tileListener && is_render_ready == 0){
                //window.setTimeout(function() {
                //    _private.render_menu(current_team, teams);
                    /*
                    _private.tileListener = google.maps.event.addListener(map,'tilesloaded',function () {
                        is_render_ready = 1;
                        window.setTimeout(function() {
                            _private.render_menu(current_team, teams);
                        }, 1000);
                        google.maps.event.removeListener(_private.tileListener);
                    });*/
                //}, 1000);
            //}

        },

        render_menu: function(current_team, teams){
            Common.renderView({
                template_id: config.templates.map_markers_filter,
                view_container_id: config.views.map_markers_filter,
                data: [{
                    'current_team':current_team,
                    'teams': teams
                }],
                helpers: public.helpers
            });

            $('.checkbox-custom').checkbox();
        },

        team_lead_filter_checkall: function (e, active) {
            if(active==undefined)
                active = $(e.currentTarget).hasClass('active');

            $(config.events.show_team_markers).prop("checked", active);
            $(config.events.show_team_markers).trigger('change');
        },

        render_teams_markers: function (e) {

            ScheduleCommon.set_scheduled_workorders();

            if(Object.keys(ScheduleCommon.scheduled_events['all_events']).length){

                for (const [key, event] of Object.entries(ScheduleCommon.scheduled_events['all_events'])) {
                    if(markers_object['events']==undefined)
                        markers_object['events'] = {};
                    if(markers_object['events'][key] == undefined)
                        markers_object['events'][key] = {};
                    if(markers_object['workorders'] == undefined)
                        markers_object['workorders'] = {};

                    if(Object.keys(markers_object['events'][key]).length)
                        markers_object['events'][key].setMap(null);

                    if(markers_object['workorders'][key]!=undefined && markers_object['workorders'][key].visible==true){
                        marker = clone(markers_object['workorders'][key]['content']);

                        markers_object['workorders'][key].setMap(null);
                        markers_object['workorders'][key] = ScheduleMapMarkers.createWorkorderMarker(_private.map, marker);
                        markers_object['workorders'][key]['icon1'] = markers_object['workorders'][key].icon;
                        markers_object['workorders'][key]['content'] = marker;
                        ScheduleMapper.marker_events(markers_object['workorders'][marker.id]);
                    }
                    else{
                        workorder = {
                            id:key,
                            estimate: event[0].estimate,
                            wo_id:key
                        };
                        markers_object['events'][key] = ScheduleMapMarkers.createEventMarker(_private.map, workorder);
                    }
                }
            }
        },

        enable_map_directions:function(e){
            var $this = $(e.currentTarget);
            var id = $this.data('id');
            var active = $this.hasClass('active');

            if(active){
                $this.parent().find(config.events.show_team_markers).prop("checked", active).trigger('change');
            }
            ScheduleMapDirections.enable_map_directions(_private.map, id, active);
        },

        checkUncheckTeam: function (e) {
            var active = $(e.currentTarget).prop('checked');
            if(active==false)
            {
                var directions = $(e.currentTarget).closest('.list-group-item').find(config.events.enable_map_directions);
                if(directions.hasClass('active')){
                    directions.trigger('click');
                }
            }
        }
    };

    var public = {

        init: function(){
            document.addEventListener('DOMContentLoaded', public.events);
        },

        events: function(){

            $(config.events.directionsMapBtn).on('mousedown', _private.init_map);
            //$(config.ui.directions_modal).on('shown.bs.modal', _private.init_map);

            $(config.ui.directions_modal).on('hidden.bs.modal', function (e) {
                _private.team_lead_filter_checkall({}, false);
            });


            $(document).on('click', config.events.team_lead_filter_checkall, _private.team_lead_filter_checkall);
            $(document).on('change', config.events.show_team_markers, _private.render_teams_markers);
            $(document).on('click', config.events.enable_map_directions, _private.enable_map_directions);
            $(document).on('change', config.events.show_team_markers, _private.checkUncheckTeam);
        },

        job_routes_filter: _private.job_routes_filter,
        render_teams_markers: _private.render_teams_markers,

        setMarkersObject: function (markers) {
            markers_object = markers;
        },

        setMap: function (map) {
            _private.map = map;
        },

        unsetMap: function () {
            _private.map = false;
        }

    };

    public.init();
    return public;
}();
