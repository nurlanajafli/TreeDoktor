var ScheduleMapper = function(){
    var config = {

        ui:{
            map:'#schedule-map',
            map_id:'schedule-map',

            filter_preloader: '.wait-loading',
            //event_start_time:'#eventStartTime',
            //event_end_time:'#eventEndTime',
            filter_form: '#map-workorders-filter-form',
            wo_status_input: 'input[name="wo_status_id"]',

            map_workorder_details: '.map-workorder-details',
            map_workorder_details_content: '.map-workorder-details.in',
            map_workorder_details_label: '.map-workorder-details-label',

            map_workorder_details_active: '.btn-success',
            map_workorder_details_default: '.btn-dark',
            map_workorder_details_activeName: 'btn-success',
            map_workorder_details_defaultName: 'btn-dark',
            infowindow_container: '.gm-style-iw',
            googlemap_container: '.gm-style-pbc',
            googlemap_container_start: '.gm-style-moc',
            infowindow_content: '.infowindow-content',
            marker_shadow: ".marker-shadow",
            marker_shadow_class: "marker-shadow",
            search_input: 'input[name="search_keyword"]',
            status_item: '.wo-status-item',

            schedule_lightbox_time: '.dhx_section_time select',

            schedule_start_time: '.date-start-view',
            schedule_end_time: '.date-end-view',
            schedule_date_renge_picker_class:'schedule-date-renge-picker'
        },

        events:{
            wo_status: '.change-wo-status',
            map_workorder_details:'.map-workorder-details',
            map_workorder_details_label: '.map-workorder-details-label',
            reload_workorders:'#reloadWorkorders',

            items_filter:'.items-filter',
            items_filter_clear:'.items-filter-clear',
            delete_filter_item: '.delete-filter-item',
            reset_all_filters:'#reset-all-map-filters',

            btn_save_note: '.btn-save-note',
            workorder_note_text: '.workorder-note-text',
            search_workorders_reset:'#search-workorders-reset',
            search_workorders_reset_filter:'#search-workorders-reset-filter',
            searchSchedule:"#searchSchedule",
            woSearchShow:"#woSearchShow",
            showFilter:"#showFilter",
            check_all_wo_statuses: ".check-all-wo-statuses",
            check_wo_status: ".check-wo-status",
        },

        route:{
            update_notes:"/workorders/update_notes"
        },

        templates:{
            infowindow: {
                workorder: '#map-workorder-infowindow-tmp',
            },
            map_container_scroll: '<div class="gm-style-pbc" style="\n' +
                '    z-index: 2;\n' +
                '    position: absolute;\n' +
                '    opacity: 0;\n' +
                '    left: 0;\n' +
                '    right: 0;\n' +
                '    top: 0;\n' +
                '    bottom: 0;\n' +
                '"></div>',
            workorders_cover: '#workorders-cover-tmp',
            event_date:'#event-date-tmp',
            workorders_statuses_dropdown:'#workorders-statuses-dropdown-tmp',
            workorders_statuses_dropdown_body: '#workorders-statuses-dropdown-body-tmp',
            workorder_details:'#modal-workorder-details-tmp',

            estimate_files:'#modal-estimate-files-tmp',
            status_logs:'#status-logs-tmp',
            client_notes:'#client-notes-tmp',

            filters_block:'#workorders-filters-block-tmp',
            filter_header:'#filters-header-tmp',

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

            workorder_marker_short: '#workorder-marker-short-tmp',
            workorder_marker_long: '#workorder-marker-long-tmp'
        },

        views:{
            infowindow: '#map-workorder-infowindow',
            infowindowId: 'map-workorder-infowindow',
            workorders_cover: '#scheduleWorkorders',
            event_date:'#event-date-view',
            workorders_statuses_dropdown:'#workorders-statuses-dropdown',
            workorders_statuses_dropdown_body: '#workorders-statuses-dropdown-body section',
            estimate_files:'#modal-estimate-files',
            status_logs:'#status-logs',
            client_notes:'#client-notes',
            filters_block:'#workorders-filters-block',

            filter_item_view:'.filter-item-view',
            filter_item_viewName:'filter-item-view',
            filter_header:'#filters-header',
            search_additional_filters: '#search-additional-filters',
        },

        images: {}
    };

    var map = {};
    var infowindows = {};
    var iw;
    var geocoder;
    var bounds;
    var flightPath = {};
    var markers_object = {
        'workorders': {},
        'events': {}
    };
    var estimates_services_status = scheduleGlobal.estimates_services_status;
    geocoder = new google.maps.Geocoder();

    var estimators_dropdown = {};
    var crews_dropdown = {};
    var services_dropdown = {};
    var equipment_dropdown = {};
    var double_positions = {};

    var latestOpenedMarker = false;

    var model = {
        filter_estimator:[],
        //filter_equipment:[],
        filter_crew:[],
        filter_service:[],
        filter_product:[],
        filter_bundle:[],
        filter_estimates_services_status:[],
        active_status:[],
        search_keyword:''
    };
    var model_save = {};

    var _private = {
        //scheduled_events: {},

        init: function(){

        },

        init_map: function () {
            var centermap = new google.maps.LatLng(MAP_CENTER_LAT, MAP_CENTER_LON);
            map = new google.maps.Map(document.getElementById(config.ui.map_id), {
                zoom: 10,
                center: centermap,
                gestureHandling:'greedy',
                streetViewControl: true,
                clickableIcons: false,
                scrollwheel: true,

                gestureHandling: 'greedy'
            });
            bounds = new google.maps.LatLngBounds();
            RoutesMap.job_routes_filter(map);
        },

        reset_map: function(){
            _private.hideAllMarkers();
            RoutesMap.unsetMap();
        },

        hideAllMarkers: function(){
            keys = Object.keys(markers_object['workorders']);
            if(keys.length && Object.keys(map).length!=0) {
                for(var i=keys.length;i--;){
                    if(markers_object['workorders'][keys[i]].map!=undefined)
                        markers_object['workorders'][keys[i]].setMap(null);
                }
                markers_object['workorders'] = {};
            }
        },

        workorders: function (response) {

            if(Object.keys(map).length==0){
                _private.init_map();
            }

            keys = Object.keys(markers_object['workorders']);
            if(keys.length){
                for(var i=keys.length;i--;){
                    if(markers_object['workorders'][keys[i]].map!=undefined){
                        markers_object['workorders'][keys[i]].setVisible(false);
                    }
                }
            }

            model.active_status = response.active_status;
            model.search_keyword = response.search_keyword;
            _private.render_filters(response);
            _private.render_statuses(response.statuses, response.active_status);


            _private.workorders_filter(response, function(workorders, scheduleEvent){
                setTimeout(function () {
                    _private.render_markers(workorders);
                    _private.render_list(workorders, scheduleEvent);
                    _private.render_filter_header(workorders);
                }, 200);
            });

            if(response.event == undefined || (response.event!=undefined && !response.event)){
                setTimeout(function () {
                    map.fitBounds(bounds);
                }, 600);
            }

            delete response;
        },

        reload_workorders: function(){
            public.workorders(0, true);
        },

        render_markers: function(workorders) {

            if (workorders.length) {
                bounds = new google.maps.LatLngBounds();
            } else {
                var home_position = ScheduleMapDirections.setHomeAddressMarker(map).position;
                setTimeout(function () {
                    map.panTo(home_position);
                    map.setZoom(8);
                }, 600);
            }

            double_positions = {};

            for (const [wkey, workorder] of Object.entries(workorders)) {

                if (!workorder.estimate || !workorder.estimate.lead || !workorder.estimate.client)
                    continue;

                if(markers_object['workorders'][workorder.id]==undefined){
                    markers_object['workorders'][workorder.id] = {};
                    markers_object['workorders'][workorder.id]['content'] = workorder;
                }

                if (workorder.estimate.lead.latitude == undefined || workorder.estimate.lead.longitude == undefined || !workorder.estimate.lead.latitude || !workorder.estimate.lead.longitude) {
                    _private.codeAddress(workorder.estimate.lead.full_address, function (results) {
                        workorder.estimate.lead.latitude = results[0].geometry.location.lat();
                        workorder.estimate.lead.longitude = results[0].geometry.location.lng();
                        _private.placeMarker(workorder);
                    }, function(){});
                } else {
                    _private.placeMarker(workorder);
                }
            }

            setTimeout(function () {
                var doubles = Object.values(double_positions).filter(positions => positions.length > 1);
                _private.update_double_positions(doubles);

                RoutesMap.setMap(map);
                RoutesMap.setMarkersObject(markers_object);
                RoutesMap.render_teams_markers();
            }, 500);

        },

        render_list: function(workorders, scheduleEvent){
            Common.renderView({
                template_id:config.templates.workorders_cover,
                empty_template_id:config.templates.workorders_cover+'-empty',
                view_container_id:config.views.workorders_cover,
                data:workorders, helpers:public.helpers
            });

            if(scheduleEvent!=undefined && scheduleEvent)
                _private.select_workorder(scheduleEvent);
        },

        render_statuses: function(statuses, active_status){
            var total = 0;
            var selected_total = 0;
            var selected_array = [];
            for(var i=statuses.length;i--;){
                statuses[i]['selected'] = false;

                option = active_status.findIndex(function(status, index) {
                    return (status == statuses[i].wo_status_id)
                });
                if(active_status.length && option!=-1){
                    statuses[i]['selected'] = true;
                    selected_total+=parseInt(statuses[i].workorders_count);
                    selected_array.push(statuses[i].wo_status_name);
                }
                total+=parseInt(statuses[i].workorders_count);
            }
            var count_filters = 0;
            $.each(model, function (key, value) {
                if(model[key] && model[key].length && key != 'active_status')
                    count_filters++;
            });

            _private.render_statuses_header(count_filters, selected_array, selected_total);

            Common.renderView({
                template_id:config.templates.workorders_statuses_dropdown_body,
                view_container_id:config.views.workorders_statuses_dropdown_body,
                data:[{'statuses':statuses, selectes_string:selected_array.join(', '), 'active_status':active_status, 'selected_total':selected_total, 'total':total}],
                helpers:public.helpers
            });
        },

        render_statuses_header: function(count_filters, selected_array, selected_total){
            Common.renderView({
                template_id:config.templates.workorders_statuses_dropdown,
                view_container_id:config.views.workorders_statuses_dropdown,
                data:[{'count_filters':count_filters, 'selectes_string':selected_array.join(', '), 'selected_total':selected_total}],
                helpers:public.helpers
            });
        },

        render_filters: function(data){

            var render = false;
            if(Object.keys(data.estimators).length){
                estimators_dropdown = data.estimators;
                render = true;
            }

            if(Object.keys(data.crews).length){
                crews_dropdown = data.crews;
                render = true;
            }

            if(Object.keys(data.services_assoc).length){
                services_dropdown = data.services_assoc;
                render = true;
            }

            /*
            if(Object.keys(data.equipment).length){
                equipment_dropdown = data.equipment;
                render = true;
            }*/

            if(render){

                _private.filters_view_data = {
                    'estimators':estimators_dropdown,
                    'crews':crews_dropdown,
                    'active_status':data.active_status,
                    'services':data.services,
                    'estimates_services_status':estimates_services_status,
                    //'equipment':equipment_dropdown
                };

                _private.render_filters_view();
            }
            else{
                var options = document.querySelectorAll(config.events.items_filter);
                for (var i = 0, l = options.length; i < l; i++) {
                    options[i].selectedIndex = 0;
                }
            }

            // Set model values from Backend
            var default_value = [];
            $.each(model, function (key, value) {
                default_value = [];
                if(key=='search_keyword')
                    default_value = '';

                model[key] = (data[key])?data[key]:default_value;

                if(model[key].length && key != 'active_status')
                    model_save[key] = model[key];
            });

            $(config.ui.search_input).val(model.search_keyword);
            _private.render_items_filters();
        },

        filters_view_data:{},

        render_filters_view: function(disabled){
            Common.renderView({
                template_id:config.templates.filters_block,
                view_container_id:config.views.filters_block,
                data:[Object.assign(_private.filters_view_data, {disabled:disabled})],
                helpers:public.helpers
            });
        },

        render_items_filters: function(){

            var renderViewModel = {
                template_id:'',
                view_container_id:'',
                data:[Object.assign({"estimators":estimators_dropdown, "services":services_dropdown, "crews":crews_dropdown}, model)],
                helpers:public.helpers
            };

            const filterItem = document.querySelectorAll(config.views.filter_item_view);
            for(var i=filterItem.length;i--;){
                thisId = '#'+filterItem[i].getAttribute("id");
                renderViewModel.template_id = thisId+'-tmp';
                renderViewModel.view_container_id = thisId;
                Common.renderView(renderViewModel);
            }

            _private.render_additional_filters();

            delete renderViewModel;
        },

        render_additional_filters: function(){
            Common.renderView({
                template_id:config.templates.search_additional_filters,
                view_container_id:config.views.search_additional_filters,
                data:[model],
                helpers:public.helpers
            });
        },

        render_filter_header:function(workorders, disabled){
            Common.renderView({
                template_id:config.templates.filter_header,
                view_container_id:config.views.filter_header,
                data:[{'countWorkorders':workorders.length, 'disabled':disabled}],
                helpers:public.helpers
            });
        },

        placeMarker: function(marker){

            if(marker.estimate.lead.latitude == undefined || marker.estimate.lead.longitude == undefined){
                return false;
            }

            if(double_positions[marker.estimate.lead.latitude+'_'+marker.estimate.lead.longitude]==undefined)
                double_positions[marker.estimate.lead.latitude+'_'+marker.estimate.lead.longitude] = [];

            double_positions[marker.estimate.lead.latitude+'_'+marker.estimate.lead.longitude].push(marker.id);

            if(markers_object['workorders'][marker.id] !=undefined && markers_object['workorders'][marker.id] && markers_object['workorders'][marker.id].position!=undefined){
                bounds.extend(markers_object['workorders'][marker.id].position);
                markers_object['workorders'][marker.id].setVisible(true);
                markers_object['workorders'][marker.id]['content'] = marker;
                return true;
            }

            if(markers_object['workorders'][marker.id] != undefined)
                markers_object['workorders'][marker.id] = {};

            /* Create Marker */
            markers_object['workorders'][marker.id] = ScheduleMapMarkers.createWorkorderMarker(map, marker);
            /* Create Marker */

            bounds.extend(markers_object['workorders'][marker.id].position);

            markers_object['workorders'][marker.id]['icon1'] = markers_object['workorders'][marker.id].icon;
            markers_object['workorders'][marker.id]['content'] = marker;
            _private.marker_events(markers_object['workorders'][marker.id]);
        },

        canvas: false,

        marker_events: function (marker){
            marker.addListener('click', function() {
                if(latestOpenedMarker && latestOpenedMarker != marker) {
                    latestOpenedMarker.setIcon(latestOpenedMarker.icon1);
                }

                if(map.zoom<15) {
                    map.setZoom(15);
                }

                latestOpenedMarker = marker;
                let neededIcon = marker.icon === marker.icon1 ? marker.icon2 : marker.icon1;
                marker.setIcon(neededIcon);

                if(!map.getBounds().contains(marker.position)){
                    map.panTo(marker.position);
                }
                _private.select_workorder({event_wo_id:marker.woId});
            });
        },

        update_double_positions: function(doubles){
            if(doubles.length==0)
                return;

            var lat = 0.0000250;
            var lon = -0.0000302;

            var start = 0, i = 0, j = 0;
            var origin_lat = 0, origin_lon = 0;

            doubles.forEach(function (double_array) {
                sqrt = Math.ceil(Math.sqrt(double_array.length));
                start = i = j = Math.ceil(sqrt / 2);
                double_array.forEach(function (double_point, key) {
                    if(Object.keys(markers_object['workorders']).length==0){
                        return true;
                    }
                    origin_lat = parseFloat(markers_object['workorders'][double_point].content.estimate.lead.latitude);
                    origin_lon = parseFloat(markers_object['workorders'][double_point].content.estimate.lead.longitude);

                    wo_lat = origin_lat + (i * lat);
                    wo_lon = origin_lon + (j * lon);

                    position = new google.maps.LatLng(wo_lat, wo_lon);
                    markers_object['workorders'][double_point].setPosition(position);

                    i--;
                    if (i == -(start - 1)) {
                        i = start;
                        j--;
                    }
                });
            });
        },

        codeAddress: function(address, callback, errorCallback){
            geocoder.geocode({ 'address': address }, function (results, status) {
                if (status == 'OK')
                    callback(results);
                else
                    errorCallback();
            });
        },

        workorder_details_render: function () {

            var id = $(this).data("wo-id");
            var view_id = $(this).attr("href");

            if($(this).hasClass(config.ui.map_workorder_details_activeName)===true)
                return false;

            $(config.ui.map_workorder_details_label+config.ui.map_workorder_details_active).removeClass(config.ui.map_workorder_details_activeName).addClass(config.ui.map_workorder_details_defaultName);
            $(config.ui.map_workorder_details_label+'[href="'+view_id+'"]').removeClass(config.ui.map_workorder_details_defaultName).addClass(config.ui.map_workorder_details_activeName);

            Common.renderView({
                template_id:config.templates.workorder_details,
                view_container_id:view_id,
                data:[{'workorder':markers_object['workorders'][id].content, 'estimates_services_status':estimates_services_status}],
                helpers:public.helpers
            });
            Common.init_checkbox();
        },

        workorder_details: function (e) {
            var id = e.target.dataset.woId;
            var estimate_no = e.target.dataset.estimate_no;
            var estimate_id = e.target.dataset.estimate_id;
            var client_id = e.target.dataset.client_id;

            if(markers_object['workorders'][id]!=undefined && markers_object['workorders'][id].icon === markers_object['workorders'][id].icon1){
                new google.maps.event.trigger(markers_object['workorders'][id], 'click');
            }

            setTimeout(function () {
                Common.request.send('/schedule/workorderProfile', {estimate_no:estimate_no, client_id:client_id, estimate_id:estimate_id, 'wo_id':id}, _private.render_estimate_files, function () {});
                Common.scrollToElement('map-workorder-' + id, 'scheduleWorkordersScrollBlock');
            }, 500);
        },

        render_estimate_files: function(response){

            Common.renderView({
                template_id:config.templates.estimate_files,
                view_container_id:config.views.estimate_files,
                data:[{"pictures" : response.pictures}],
                helpers:public.helpers
            });

            Common.renderView({
                template_id:config.templates.status_logs,
                view_container_id:config.views.status_logs,
                data:[{
                    "status_logs" : response.status_logs,
                    'workorder':(Object.keys(markers_object['workorders']).length)?markers_object['workorders'][response.wo_id].content:{}
                }],
                helpers:public.helpers
            });

            ClientNotes.renderNotes({
                "notes_files":response.notes_files,
                "client" : response.client,
                'limit':response.limit,
                'lead':(Object.keys(markers_object['workorders']).length)?markers_object['workorders'][response.wo_id].content.estimate.lead:{}
            });

        },

        select_workorder: function(event){
            if($(config.events.map_workorder_details_label+'[data-wo-id="'+event.event_wo_id+'"]').hasClass(config.ui.map_workorder_details_activeName))
                return false;

            $(config.events.map_workorder_details_label+'[data-wo-id="'+event.event_wo_id+'"]').trigger('mousedown');
            $(config.events.map_workorder_details_label+'[data-wo-id="'+event.event_wo_id+'"]').trigger('click');

            if(event.id==undefined)
                return true;

            schedule_event = scheduler.getEvent(event.id);

            setTimeout(function () {
                var selected_services = (schedule_event.services)?JSON.parse(schedule_event.services):{};
                if(schedule_event.services && Object.keys(selected_services).length){
                    $.each(selected_services, function (key, value) {
                        $('.estimate_statuses tr[data-estimate_service_id="'+value+'"] .selectService').trigger('click');
                    });
                }
            }, 800);
        },

        clear_workorder_details: function(e){
            $(e.target).html('');
            $('label[href="#'+e.target.id+'"]').removeClass(config.ui.map_workorder_details_activeName);
            $('label[href="#'+e.target.id+'"]').addClass(config.ui.map_workorder_details_defaultName);
        },

        save_event: function(event, data, is_new_event){

            if(event.mode!=undefined && event.mode=='timeline')
            {
                team_id = ScheduleTimeline.search_team(event.start_date, event.section_id);
                if(parseInt(team_id))
                    event.event_crew_id = team_id;

                scheduler.serverList("sections").forEach(function (val) {

                    if(event.section_id == val.section_id){
                        event.team_leader_user_id = val.team_leader_user_id;
                    }
                })
            }
            event.wo_id = $(config.ui.map_workorder_details_label+config.ui.map_workorder_details_active).data('wo-id');

            event.services = $(config.ui.map_workorder_details_content).data('services');
            if(event.services && typeof event.services == 'string')
                event.services = eval("(" + $(config.ui.map_workorder_details_content).data('services') + ")");
            if(typeof event.services == 'undefined' || event.services == '')
                event.services = {};

            delete data.my_template;
            delete event.my_template;
            delete data.color;
            delete event.color;

            event.services = JSON.stringify(event.services);
            event.note = $('#eventNotes').val() ? $('#eventNotes').val() : '';
            event.mode = scheduler.getState().mode;

            if(!event.wo_id || !event.services || event.services == '{}'){
                errorMessage("Please select workorder or service!");
                return false;
            }
            scheduler.updateEvent(event.id);
            return true;
        },

        change_wo_status: function(){
            if(!model.active_status.length) {
                _private.render_markers([]);
                _private.render_list([]);
                _private.hideAllMarkers();
                _private.render_filters_view(true);
                _private.render_statuses_header(0, ['unchecked all'], 0);
                _private.render_filter_header([], true);

                $(config.events.searchSchedule+' input').attr("disabled", "disabled");
                $(config.events.searchSchedule+' button').attr("disabled", "disabled");
                return;
            }

            $(config.events.searchSchedule+' input').removeAttr("disabled");
            $(config.events.searchSchedule+' button').removeAttr("disabled");
            _private.render_items_filters();
            setTimeout(function () {
                $(config.ui.filter_form).trigger('submit');
            }, 100);
        },

        workorders_filter: function(response, callback){
            if(response.workorders.length==0 || model.filter_crew.length==0)
                return callback(response.workorders, response.event);

            var filter_counts = Common.helpers.array_items_counts(model.filter_crew);
            var result = [];
            var estimate_crews_counts = {};
            var estimate_crews_arr = [];
            $.each(response.workorders, function (key, workorder) {
                if(!workorder.estimate)
                    return true;

                estimate_crews_arr = workorder.estimate.estimates_new_services_crews.map(v => v.crew_user_id);
                estimate_crews_counts = Common.helpers.array_items_counts(estimate_crews_arr);

                is_push = true;
                $.each(filter_counts, function (fkey, fvalue) {
                      if(estimate_crews_counts[fkey]==undefined || (estimate_crews_counts[fkey]!=undefined && estimate_crews_counts[fkey] < fvalue))
                          is_push = false;
                });

                if(is_push)
                    result.push(workorder);
            });

            callback(result, response.event);
        },

        items_filter: function(e){

            var item_id = $(this).find('option:selected').val();

            if(parseInt(item_id)==-1 && this.dataset.model=='filter_estimates_services_status')
                return _private.items_filter_clear(this.dataset.model);

            if(parseInt(item_id)==0 && this.dataset.model!='filter_estimates_services_status')
                return _private.items_filter_clear(this.dataset.model);

            if(this.dataset.model!='filter_crew'){
                if(model[this.dataset.model].indexOf(item_id) !== -1){
                    $(e.currentTarget).val('');
                    return false;
                }
            }
            model[this.dataset.model].push(item_id);
            model_save[this.dataset.model] = model[this.dataset.model];

            _private.render_items_filters();

            _private.show_preloader();
            $(config.ui.filter_form).trigger('submit');
        },

        delete_filter_item: function(e){
            model[this.dataset.model].splice(parseInt(this.dataset.index), 1);
            model_save[this.dataset.model] = model[this.dataset.model];

            _private.render_items_filters();
            _private.show_preloader();

            $(config.ui.filter_form).trigger('submit');
            return false;
        },

        items_filter_clear: function(model_name){
            if(model[model_name].length==0)
                return false;

            model[model_name] = [];
            model_save[model_name] = [];
            _private.render_items_filters();
            $(config.ui.filter_form).trigger('submit');
        },

        reset_all_filters: function(){
            $.each(model, function (key, value) {
                model[key] = [];
            });
            model_save = model;
            _private.render_items_filters();
            $(config.ui.filter_form).trigger('submit');
        },

        show_preloader: function () {
            $(config.ui.filter_preloader).show();
            Common.renderPreloader(config.ui.filter_preloader, 'Loading ...', 8000);
        },

        save_note: function(e){
            var data = e.currentTarget.dataset;
            var button = e.currentTarget;
            data[$(e.currentTarget.dataset.href).attr("name")] = $(e.currentTarget.dataset.href).val();
            Common.request.send(config.route.update_notes, data, function (response) {
                markers_object['workorders'][response.workorder.id].content.wo_office_notes = response.workorder.wo_office_notes;
                markers_object['workorders'][response.workorder.id].content.estimate.estimate_crew_notes = response.workorder.estimate.estimate_crew_notes;
                $(button).hide();
            });
        },

        workorder_note_text: function (e) {
            var field = e.currentTarget;
            var wo_id = $(field).data("id");
            var text = $(field).val().toString();

            if(typeof markers_object['workorders'][wo_id].content[$(field).attr("name")] != "undefined"){
                if(markers_object['workorders'][wo_id].content[$(field).attr("name")] == null)
                    markers_object['workorders'][wo_id].content[$(field).attr("name")] = '';

                if(markers_object['workorders'][wo_id].content[$(field).attr("name")]!=text)
                    $(field).parent().find(config.events.btn_save_note).show();
                else
                    $(field).parent().find(config.events.btn_save_note).hide();
            }
            if(typeof markers_object['workorders'][wo_id].content.estimate[$(field).attr("name")] != "undefined"){
                if(markers_object['workorders'][wo_id].content.estimate[$(field).attr("name")] == null)
                    markers_object['workorders'][wo_id].content.estimate[$(field).attr("name")] = '';

                if(markers_object['workorders'][wo_id].content.estimate[$(field).attr("name")]!=text)
                    $(field).parent().find(config.events.btn_save_note).show();
                else
                    $(field).parent().find(config.events.btn_save_note).hide();
            }
        },

        search_workorders_reset: function () {
            var val = $(config.ui.search_input).val();
            $(config.ui.search_input).val('');
            model_save.search_keyword = val;
            model.search_keyword = val;

            if(val)
                $(config.events.searchSchedule).trigger('submit');
        },

        check_all_wo_statuses: function (e) {
            $(config.events.check_wo_status).prop("checked", $(e.currentTarget).prop('checked'));
            $(config.events.check_wo_status).trigger('change');
        },

        check_wo_status: function (e) {
            model.search_keyword = $(config.ui.search_input).val();
            model_save.search_keyword = model.search_keyword;

            var $this = $(e.currentTarget);
            var id = $this.data('wo_status_id');
            var option = model.active_status.findIndex(function(status, index) {
                return (status == id)
            });

            if($this.prop('checked')){
                $this.closest(config.ui.status_item).addClass('active');
                if(option == -1) {
                    model.active_status.push(id);
                }
                model_save.active_status = model.active_status;
                return;
            }

            $this.closest(config.ui.status_item).removeClass('active');
            if(option != -1) {
                model.active_status.splice(option, 1);
            }
            model_save.active_status = model.active_status;
        },

        render_event_date: function (start, end) {
            Common.renderView({
                template_id:config.templates.event_date,
                view_container_id:config.views.event_date,
                data:[{
                    'event_date_start':start,
                    'event_date_end':end,
                }] ,
                helpers:public.helpers
            });
        },

        set_scheduled_time:function (start, end) {

            $(config.ui.schedule_start_time).text(Common.helpers.dateFormat(start, "DD MMM, "+Common.helpers.getTimeFormat()));
            $(config.ui.schedule_end_time).text(Common.helpers.dateFormat(end, "DD MMM, "+Common.helpers.getTimeFormat()));

            var date_start = public.helpers.scheduleEventDate(start);
            var date_end = public.helpers.scheduleEventDate(end);

            var time_selects = document.querySelectorAll(config.ui.schedule_lightbox_time);

            time_selects[0].value = date_start.y;
            time_selects[1].value = parseInt(date_start.m)-1;
            time_selects[2].value = date_start.d;
            time_selects[3].value = (parseInt(date_start.h)*60)+parseInt(date_start.i);

            time_selects[4].value = date_end.y;
            time_selects[5].value = parseInt(date_end.m)-1;
            time_selects[6].value = date_end.d;
            time_selects[7].value = (parseInt(date_end.h)*60)+parseInt(date_end.i);
        }
    };

    var public = {
        init: function(){
            $(document).ready(function(){
                public.events();
                _private.init();
            });
        },

        markers_object: markers_object,

        init_map: _private.init_map,

        save_event: _private.save_event,

        reset_map: _private.reset_map,

        search_result: _private.workorders,

        marker_events: _private.marker_events,

        workorders: function(id, global, status){
            id = (id==undefined)?0:id;
            global = (global==undefined)?false:global;
            var data = {event_id:id};
            if(status==undefined){
                data = Object.assign(data, model_save);
                data['wo_status_id'] = model_save.active_status;
                delete data['active_status'];
            }else{
                data['wo_status_id'] = [status];
            }


            Common.request.send(
                'schedule/workordersByStatuses',
                data,
                _private.workorders,
                function () {},
                global
            );
        },

        event_date: function(current_event){
            bloched_dates = [];
            if(typeof ScheduleTimeline == 'object')
                var bloched_dates = ScheduleTimeline.get_blocked_intevals(current_event.section_id);

            blocked_dates_array = [];
            if(bloched_dates.length){
                bloched_dates.forEach(function (item) {
                    blocked_dates_array.push(moment(item.from).format("YYYY-MM-DD"));
                    if(item.from!=item.to){
                        blocked_dates_array.push(moment(item.to).format("YYYY-MM-DD"));
                    }
                });
            }

            blocked_dates_array = blocked_dates_array.filter((value, index, self) => self.indexOf(value) === index);
            _private.render_event_date(current_event.start_date, current_event.end_date);

            var options = {
                parentEl:".dhx_cal_larea",
                timePicker: true,
                startDate: moment(current_event.start_date),
                endDate: moment(current_event.end_date),
                minTime: SCHEDULER_STARTS_FROM,
                maxTime: SCHEDULER_ENDS_AT,
                locale: {
                    format: "MMM DD "+Common.helpers.getTimeFormat(),
                },

                isInvalidDate: function (arg) {
                    if(blocked_dates_array.length){
                        return (blocked_dates_array.findIndex((item, index) => item==moment(arg).format("YYYY-MM-DD"))!=-1)
                    }
                    return false;
                },
                customStyle: function(picker){
                    if(picker.hasClass(config.ui.schedule_date_renge_picker_class)==false)
                        picker.addClass(config.ui.schedule_date_renge_picker_class);

                    if(scheduler.getState().mode=='unit')
                        $('.calendar-table').addClass('hidden');

                    $('.drp-selected').addClass('hidden');
                }
            };

            Common.dateRangePicker("#event-dates", options, function(start, end, label) {
                _private.set_scheduled_time(start, end);
            });
        },

        update_workorders_list: function(response){
            _private.workorders(response);
            $(config.ui.filter_preloader).fadeOut(200);
        },

        events: function(){
            $(document).on('click', config.events.wo_status, _private.change_wo_status);

            $(document).on('mousedown', config.events.map_workorder_details_label, _private.workorder_details_render);
            $(document).on('shown.bs.collapse', config.events.map_workorder_details, _private.workorder_details);

            $(document).on('hidden.bs.collapse', config.events.map_workorder_details, _private.clear_workorder_details);
            $(document).on('click', config.events.reload_workorders, _private.reload_workorders);

            $(document).on('change', config.events.items_filter, _private.items_filter);
            $(document).on('click', config.events.items_filter_clear, function () {
                _private.items_filter_clear(this.dataset.model)
            });
            $(document).on('click', config.events.delete_filter_item, _private.delete_filter_item);
            $(document).on('click', config.events.reset_all_filters, _private.reset_all_filters);

            $(document).on('click', config.events.btn_save_note, _private.save_note);
            $(document).on('keyup', config.events.workorder_note_text, _private.workorder_note_text);

            $(document).on('click', config.events.search_workorders_reset, _private.search_workorders_reset);
            $(document).on('click', config.events.search_workorders_reset_filter, _private.search_workorders_reset);

            $(document).on('change', config.events.check_all_wo_statuses, _private.check_all_wo_statuses);
            $(document).on('change', config.events.check_wo_status, _private.check_wo_status);
        },

        helpers: {
            scheduleEventDate: function(e_date){
                var result = {};
                result.y = moment(e_date).format("YYYY");
                result.m = moment(e_date).format("MM");
                result.d = moment(e_date).format("D");
                result.h = moment(e_date).format("H");
                result.i = moment(e_date).format("mm");

                return result;
            },

            parseStrJson: function(json_str){
                if(!json_str)
                    return [];

                return JSON.parse(json_str);
            },

            getStatuses: function (status_id) {
                result = [];
                $.each(window.statuses, function (key, value) {
                    value['selected'] = false;
                    if(status_id!=undefined && value.lead_status_id==status_id)
                        value['selected'] = true;

                    result.push(value);
                });
                return result;
            },

            intval: function (val) {
                return parseInt(val);
            },

            getEstimatorById: function (id) {
                var empty = {'full_name':'', 'color':''};
                if(!Common.helpers.object_length(estimators_dropdown))
                    return empty;
                if(estimators_dropdown[id]==undefined)
                    return empty;

                return estimators_dropdown[id];
            },

            getEquipmentById: function(id){
                var empty = {'eq_name':'', group: {'group_color':''}};
                if(!Common.helpers.object_length(equipment_dropdown))
                    return empty;
                if(equipment_dropdown[id]==undefined)
                    return empty;

                return equipment_dropdown[id];
            },

            getCrewById: function (id) {
                if(!Common.helpers.object_length(crews_dropdown))
                    return false;
                if(crews_dropdown[id]==undefined)
                    return false;

                return crews_dropdown[id];
            },

            getServiceById: function (id) {
                if(!Common.helpers.object_length(services_dropdown))
                    return false;
                if(services_dropdown[id]==undefined)
                    return false;

                return services_dropdown[id];
            },

            getStatusById: function(id){

                if(!Common.helpers.object_length(estimates_services_status))
                    return false;

                var option = estimates_services_status.findIndex(function(status, index) {
                    return (status.services_status_id == id)
                });

                if(option==-1)
                    return false;

                return estimates_services_status[option];
            },

            client_payments_sum: function (payments) {
                if(!payments.length)
                    return Common.money(0);

                var result = 0;
                payments.forEach(function (value) {
                    result = result+parseFloat(value.payment_amount)
                });
                return Common.money(result);
            },
        },

        getMap: function () {
            return map;
        }

    };

    public.init();
    return public;
}();
