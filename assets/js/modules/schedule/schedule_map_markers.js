var ScheduleMapMarkers = function(){
    var config = {

        ui:{

            map_markers_filter_id: 'map-teams-marker-filter-dropdown'
        },

        events:{


            team_lead_filter_checkall: ".team-lead-filter-checkall",
            show_team_markers: ".show-team-markers"
        },

        route:{
            update_notes:"/workorders/update_notes"
        },

        templates:{

            render_infowindow: function (marker) {
                return {
                    price: Common.money(marker.estimate.sum_actual_without_tax, false, true),
                    original_price: Common.money(marker.estimate.sum_actual_without_tax, false),
                    crew: (marker.estimate.estimates_services_crew[0]!=undefined)?marker.estimate.estimates_services_crew[0].crew_name:''
                };
                /*var result = '<div class="infowindow-content" data-id="'+marker.id+'">'
                +'<b class="text-primary">'+Common.money(marker.estimate.sum_actual_without_tax, false)+'</b> '+crewType
                +'<div class="marker-addres-block"><i>'+marker.estimate.client.client_address+'</i></div>'
                +'</div>';
                return result;*/
            },
            search_additional_filters: '#search-additional-filters-tmp',
            map_markers_filter: '#map-teams-marker-filter-dropdown-tmp',

            workorder_marker_short: '#workorder-marker-short-tmp',
            workorder_marker_long: '#workorder-marker-long-tmp',

            event_marker_short: '#event-marker-short-tmp',
        },

        views:{

        },

        images: {}
    };

    var _private = {

        canvas: false,

        getTextWidth: function(text, font) {
            _private.canvas = _private.canvas || (_private.canvas = document.createElement("canvas"));
            var context = _private.canvas.getContext("2d");
            context.font = font;
            var metrics = context.measureText(text);
            return metrics.width;
        },

        pin_data: function (marker) {
            var data = config.templates.render_infowindow(marker);
            return {
                price:data.price,
                original_price:data.original_price,
                crew:data.crew,
                color:"",
            };
        },

        pin_style: function (title, color, icon) {
            return icon;
        },

        createWorkorderMarker: function (map, marker) {
            position = new google.maps.LatLng(marker.estimate.lead.latitude, marker.estimate.lead.longitude);

            var icons = _private.markerWorkorderIcons(marker);

            return new google.maps.Marker({
                position: position,
                map: map,
                icon: 'data:image/svg+xml;base64,' + btoa(icons.icon_short),
                icon2: 'data:image/svg+xml;base64,' + btoa(icons.icon_long),
                visible:true,
                woId: marker.id,
                title: marker.workorder_no + ' ' + marker.estimate.lead.lead_address,
            });
        },

        createEventMarker: function (map, marker){
            if(!marker.estimate || ScheduleCommon.scheduled_events['events'][marker.wo_id]==undefined)
                return null;

            position = new google.maps.LatLng(marker.estimate.lead.latitude, marker.estimate.lead.longitude);
            var icons = _private.markerEventIcons(marker);

            return new google.maps.Marker({
                position: position,
                map: map,
                icon: 'data:image/svg+xml;base64,' + btoa(icons.icon_short),
                visible:true,
                woId: marker.id,
                title: marker.estimate.lead.lead_address,
            });
        },

        markerWorkorderIcons: function (marker) {
            var pin_data = ScheduleMapMarkers.pin_data(marker);
            var width = ScheduleMapMarkers.getTextWidth(pin_data.original_price + (pin_data.crew && pin_data.crew !== '' ? ' ' + pin_data.crew : ''), '14px Arial') + 44;
            var widthAddress = ScheduleMapMarkers.getTextWidth(marker.estimate.lead.lead_address, '14px Arial') + 44;

            var teams = [];
            if(ScheduleCommon.scheduled_events['events']!=undefined && ScheduleCommon.scheduled_events['events'][marker.id]!=undefined)
                teams = ScheduleCommon.scheduled_events['events'][marker.id];

            var current_team_id = null;
            if(ScheduleCommon.scheduled_events['current'] != undefined){
                current_team_id = (ScheduleCommon.scheduled_events['current'].event_crew_id)?ScheduleCommon.scheduled_events['current'].event_crew_id:ScheduleCommon.scheduled_events['current'].section_id;
            }
            var marker_data = [{
                width: width,
                teams: teams,
                current_team_id: current_team_id,
                pin_data: pin_data,
                widthAddress:widthAddress,
                lead_address: ($.trim(marker.estimate.lead.lead_address) != '')?Common.encodeUTF8string(marker.estimate.lead.lead_address):''
            }];

            var icon_short = Common.renderView({
                template_id:config.templates.workorder_marker_short,
                render_method:'variable',
                data:marker_data
            });

            var icon_long = Common.renderView({
                template_id:config.templates.workorder_marker_long,
                render_method:'variable',
                data:marker_data
            });

            return {'icon_long': icon_long, 'icon_short': icon_short};
        },

        markerEventIcons: function (marker) {
            /* Кружочки Время и индекс */
            var width = 44;
            var current_team_id = false;
            if(ScheduleCommon.scheduled_events['current']!=undefined)
                current_team_id = ScheduleCommon.scheduled_events['current'].event_crew_id?ScheduleCommon.scheduled_events['current'].event_crew_id:ScheduleCommon.scheduled_events['current'].section_id;
            var marker_data = [{
                width: width,
                teams: (ScheduleCommon.scheduled_events['events'][marker.wo_id]!=undefined)?ScheduleCommon.scheduled_events['events'][marker.wo_id]:[],
                current_team_id: current_team_id,
            }];

            var icon_short = Common.renderView({
                template_id:config.templates.event_marker_short,
                render_method:'variable',
                data:marker_data
            });

            return {'icon_short': icon_short};
        }
    };

    var public = {
        init: function(){
            $(document).ready(function(){
                public.events();
            });
        },

        events: function(){},


        getTextWidth: _private.getTextWidth,
        pin_data: _private.pin_data,

        createWorkorderMarker: _private.createWorkorderMarker,
        markerWorkorderIcons: _private.markerWorkorderIcons,

        createEventMarker:_private.createEventMarker
    };

    public.init();
    return public;
}();
