var LeadsMapper = function(){
    var config = {

        ui:{
            map:'#leads-map',
            map_id:'leads-map',
            infowindow_view: '#map-infowindow',
            infowindow_container: '.infowindow-container',
            marker_key: 'input[name="marker_key"]',
            sms_modal: '#send-sms-modal',
            priority: 'input[name="lead_priority"]',
            user_input: 'input[name="users"]'
        },

        events:{
            show_all_checkbox: '.showAll',
            show_lead_not_assigned_checkbox: '.showPoint',
            show_task_checkbox: '.showTasks',
            show_lead_checkbox: '.showLeads',
            show_vehicles_checkbox: '.showVehicles',
            show_priority_lead_checkbox: '.showPointPriority',
            show_service_lead_select2: '.showLeadService',
            show_product_lead_select2: '.showLeadProduct',
            show_bundle_lead_select2: '.showLeadBundle',
            show_user_lead_select2: '.showUsers',
            show_user_lead_not_assign: '#showPointPriorityNotAssigned',
            show_priority_lead_select2: '.showPriorities',


            call_checkbox: '.callLead',
            assigned_to: '.assigned_to',
            lead_status: '#lead-status',
            task_status: '#task-status',

            task_category: '#task-category',

            task_assigned_user: '#task-assigned-user',

            task_schedule_date: '#task-schedule-date',
            task_schedule_time_start: '#task-schedule-start',
            task_schedule_time_end: '#task-schedule-end',

            lead_status_form: '#lead-status-form',
            task_status_form: '#task-status-form',
            task_schedule_form: '#task-schedule-form',

            change_lead_priority: '.change-lead-priority',
            gps_tracker_checkbox: '.gps-tracker-checkbox'
        },

        route:{
            tracker: '/schedule/ajax_get_traking_position',
            tasks: '/leads/map/tasks',
            call: '/leads/ajax_call_lead',
        },

        templates:{
            infowindow: {
                lead: '#lead-infowindow-tmp',
                task: '#task-infowindow-tmp'
            },

            lead_status_reasons:'#lead-status-reasons-tpl',
            lead_status_buttons:'#lead-status-buttons-tpl',

            task_status_buttons:'#task-status-buttons-tpl',
            task_schedule_buttons:'#task-schedule-buttons-tpl',
        },

        views:{
            infowindow: '#map-infowindow',

            lead_status_reasons:'#lead-status-reasons',
            lead_status_buttons:'#lead-status-buttons',

            task_status_buttons:'#task-status-buttons',
            task_schedule_buttons:'#task-schedule-buttons',
        },

        images: {
            /* New Markers Style
            tree:'<text transform="translate(16 18.5)" stroke="#000" stroke-width="0.5" fill="#176d3e" x="17" y="35" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="25" text-anchor="middle">&#127876;</text>',
            star:'<text transform="translate(16 18.5)" stroke="#000" stroke-width="1" fill="#fff378" x="15" y="-7" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="30" text-anchor="middle">&#9733;</text>',
            warning:'<path fill="#fc3" d="M19.64 16.36L11.53 2.3A1.85 1.85 0 0 0 10 1.21 1.85 1.85 0 0 0 8.48 2.3L.36 16.36C-.48 17.81.21 19 1.88 19h16.24c1.67 0 2.36-1.19 1.52-2.64zM11 16H9v-2h2zm0-4H9V6h2z" style="transform: scale(1.5);transform-origin:-30px -60px;" stroke="#ff5d00" />',
            fire:'<text transform="translate(22 34)" fill="#fff378" font-size="30" x="8" y="28" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;text-shadow: -1px -1px 1px #fff" stroke="#000"  stroke-width="1" text-anchor="middle">&#x1f525;</text>',

            phone: function (color) {
                return '<text transform="translate(22 34)" fill="' +color+ '" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;text-shadow: 0px 1px 1px rgba(0,0,0,0.35)" font-size="26" text-anchor="middle">&#9743;</text>';
            },
            default: function (content, color) {
                return '<text transform="translate(22.5 30)" fill="'+color+'" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;text-shadow: 0px 1px 1px rgba(0,0,0,0.55)" font-size="20" text-anchor="middle">'+content+'</text>';
            },
            default_task: function (content, color){
                return '<text transform="translate(260 260)" fill="'+color+'" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;text-shadow: 0px 1px 1px rgba(0,0,0,0.55)" font-size="160" text-anchor="middle">'+content+'</text>';
            },
            lead_marker_template: function (content, color){
                var result = '<svg version="1.1" width="25" height="52.5" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 45.137 68.625" xml:space="preserve"><path fill="'+color+'" d="M22.569,0C10.103,0,0,10.104,0,22.568c0,7.149,3.329,13.521,8.518,17.654 c0.154,0.127,0.318,0.258,0.499,0.392c0.028,0.021,0.054,0.042,0.082,0.063c0.006,0.004,0.01,0.007,0.015,0.011 c8.681,6.294,13.453,27.938,13.453,27.938s4.03-20.621,11.407-26.585c6.679-3.921,11.163-11.17,11.163-19.472 C45.137,10.104,35.032,0,22.569,0z M22.569,38.129c-8.382,0-15.176-6.795-15.176-15.176c0-8.382,6.794-15.175,15.176-15.175 c8.381,0,15.174,6.793,15.174,15.175C37.743,31.334,30.95,38.129,22.569,38.129z"/><circle fill="#FFFFFF" cx="22.48" cy="23.043" r="19.27"/>'+content+'</svg>'
                return result;
            },

            task_marker_template: function (content, color) {
                console.log(color);
                console.log(content);
                var result = '<svg version="1.1" height="35px" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 511.999 511.999;" xml:space="preserve">'
                    +'<path style="fill:'+color+'" d="M490.459,139.522c-13.413-24.343-32.425-46.06-56.507-64.546c-47.877-36.752-111.076-56.993-177.951-56.993c-66.876,0-130.073,20.241-177.952,56.993	c-24.082,18.486-43.094,40.203-56.507,64.546C7.248,165.464,0,193.131,0,221.756s7.248,56.293,21.541,82.234 c12.356,22.425,29.475,42.614,50.92,60.109L60.577,477.06c-1.238,11.769,10.762,20.443,21.549,15.576l150.582-67.943l0,0 c7.701,0.548,15.47,0.836,23.291,0.836c66.875,0,130.073-20.241,177.951-56.993c24.082-18.486,43.094-40.203,56.507-64.546 c14.294-25.942,21.541-53.609,21.541-82.234S504.753,165.464,490.459,139.522z"/>'
                    +'<path style="fill:#FFFFFF;transform:translate(-51px,-51px) scale(1.2,1.23)" d="M395.901,318.968C358.877,347.389,309.193,363.04,256,363.04s-102.879-15.652-139.902-44.072 c-34.57-26.538-53.609-61.061-53.609-97.211s19.04-70.674,53.61-97.211c37.024-28.421,86.708-44.072,139.902-44.072 c53.192,0,102.877,15.652,139.901,44.072c34.571,26.537,53.61,61.061,53.61,97.211S430.472,292.43,395.901,318.968z"/>'
                    +'<path style="opacity:0.1;enable-background:new;transform:translate(-51px,-51px) scale(1.2,1.23)" d="M97.709,251.296c0-36.15,19.04-70.674,53.61-97.211 c37.024-28.421,86.708-44.072,139.902-44.072c47.141,0,91.519,12.302,126.78,34.882c-6.448-7.182-13.824-13.996-22.099-20.35 c-37.024-28.421-86.708-44.072-139.901-44.072s-102.879,15.652-139.902,44.072c-34.571,26.537-53.61,61.06-53.61,97.211 s19.04,70.674,53.61,97.211c4.212,3.233,8.595,6.292,13.121,9.19C108.734,305.343,97.709,278.792,97.709,251.296z"/>'
                    +content
                    +'</svg>';

                return result;
            },
            */

            tree:'<text transform="translate(16 18.5)" stroke="#000" stroke-width="0.5" fill="#176d3e" x="17" y="35" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="25" text-anchor="middle">&#127876;</text>',
            star:'<text transform="translate(16 18.5)" stroke="#000" stroke-width="1" fill="#fff378" x="12" y="-12" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="22" text-anchor="middle">&#9733;</text>',
            warning:'<path fill="#ffe70d" stroke="#000" stroke-width="15px" style="transform:scale(0.042, 0.042) translate(440px,500px)" d="M454.106,396.635L247.33,38.496c-3.783-6.555-10.775-10.592-18.344-10.592c-7.566,0-14.561,4.037-18.344,10.592 L2.837,398.414c-3.783,6.555-3.783,14.629,0,21.184c3.783,6.556,10.778,10.593,18.344,10.593h415.613c0.041,0,0.088,0.006,0.118,0 c11.709,0,21.184-9.481,21.184-21.185C458.096,404.384,456.612,400.116,454.106,396.635z M57.872,387.822L228.986,91.456 L400.1,387.828H57.872V387.822z M218.054,163.009h21.982c1.803,0,3.534,0.727,4.8,2.021c1.259,1.3,1.938,3.044,1.892,4.855 l-4.416,138.673c-0.095,3.641-3.073,6.537-6.703,6.537h-13.125c-3.635,0-6.614-2.902-6.7-6.537l-4.418-138.673 c-0.047-1.812,0.636-3.555,1.895-4.855C214.52,163.736,216.251,163.009,218.054,163.009z M246.449,333.502v25.104 c0,3.699-2.997,6.696-6.703,6.696h-21.394c-3.706,0-6.7-2.997-6.7-6.696v-25.104c0-3.7,2.994-6.703,6.7-6.703h21.394 C243.452,326.793,246.449,329.802,246.449,333.502z"/>',
            fire:'<text transform="translate(22 34)" fill="#fff378" font-size="18" x="8" y="6" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;text-shadow: -1px -1px 1px #fff" stroke="#000"  stroke-width="1" text-anchor="middle">&#x1f525;</text>',

            phone: function (color) {
                return '<text stroke-width="1" fill="#000" x="9" y="3" style="font-family: Arial,sans-serif;font-weight:bold;text-align:center;" font-size="17" text-anchor="middle">&#128222;</text>';
            },

            default: function (content, color) {
                return '<text transform="translate(19 22) scale(0.75)" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="20" text-anchor="middle">' + content + '</text>';
            },

            default_task: function (content, color){
                return '<text transform="translate(240 240)" fill="#000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;text-shadow: 0px 1px 1px rgba(0,0,0,0.55)" font-size="160" text-anchor="middle">'+content+'</text>';
            },

            lead_marker_template: function (content, color){
                var result = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="58" viewBox="0 0 38 38"><path fill="' + color + '" stroke="#000" stroke-width="2" d="M34.305 16.234c0 8.83-15.148 19.158-15.148 19.158S3.507 25.065 3.507 16.1c0-8.505 6.894-14.304 15.4-14.304 8.504 0 15.398 5.933 15.398 14.438z"/>'+content+'</svg>';
                return result;
            },

            task_marker_template: function (content, color) {
                var result = '<svg xmlns="http://www.w3.org/2000/svg" height="32px" width="32px" viewBox="0 0 480 480"><path fill="' + color +'" stroke="#000000" stroke-width="5" fill-rule="nonzero" marker-start="" marker-mid="" marker-end="" id="svg_16" d="M11.186155649009088,476.0885041676267 L103.15873182946643,311.96152697217735 L103.15873182946643,311.96152697217735 C5.7211678769939365,262.4517019249397 -22.082518426899092,169.18577638907354 38.9245432416211,96.49309300509664 C99.93046014410076,23.800830456576065 229.78398858465704,-4.52719321136221 338.9146322808233,31.04856760666257 C448.04524461353026,66.62412580024365 500.74136732913627,154.45988688920258 460.65260040526954,233.97186576659885 C420.5659505145268,313.4838134709767 300.8158765142848,358.6497282889444 184.00005749974966,338.315473910676 L11.186155649009088,476.0885041676267 z" style="color: rgb(0, 0, 0);" class=""/>'+content+'</svg>';
                return result;
            },
        }
    };

    //var map = {};
    var infowindow;
    var geocoder;
    var bounds;
    var flightPath = {};
    var markers_object = {
        'lead':{},
        'task':{},
    };
    var allLeadsArray = [];
    var availableLeadsArray = [];
    var filteredServicesLeadsArray = [];
    var filteredByServicesLeadsArray = [];
    var filteredByBundleLeadsArray = [];
    var filteredByProductLeadsArray = [];
    var filteredByPriorityLeadsArray = [];
    var filteredByUsersLeadsArray = [];

    /**
     * Variables For Duplicates markers
     * @type {number}
     */
    var markersDuplicatesArray = [];
    var diffLatForDuplicates = 0.0000250;
    var diffLonForDuplicates = -0.0000302;
    var startForDuplicates = 0;
    var iForDuplicates = 0;
    var jForDuplicates = 0;
    var originLatForDuplicates = 0;
    var originLonForDuplicates = 0;

    geocoder = new google.maps.Geocoder();

    var priorityEmergency = 'Emergency';
    var priorityPriority = 'Priority';
    var priorityRegular = 'Regular';

    var priority_filters = {
        'Emergency':true,
        'Priority':true,
        'Regular': true
    };

    var _private = {
        init:function(){
            _private.init_map();
            _private.init_tracker();
            _private.init_tasks(function () {
                map.fitBounds(bounds);
                _private.update_double_positions();
            });
        },

        init_map:function () {
            var centermap = new google.maps.LatLng($(config.ui.map).data('origin_lat'), $(config.ui.map).data('origin_lon'));

            var myOptions = {
                zoom: 8,
                center: centermap,
                fullscreenControl: true,
                fullscreenControlOptions: {
                    position: google.maps.ControlPosition.LEFT_BOTTOM,
                },
                /*mapTypeId: google.maps.MapTypeId.SATELLITE,*/
                //mapTypeControlOpts: {mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.HYBRID]},
                //labels:false
                //mapTypeId: 'satellite',
                gestureHandling:'greedy',
                streetViewControl: true,
                maxZoom:18
            }

            map = new google.maps.Map(document.getElementById(config.ui.map_id), myOptions);
            infowindow = new google.maps.InfoWindow({maxWidth: 600, minHeight: 900, buttons:{close:{visible: false}} });
            bounds = new google.maps.LatLngBounds();

            bounds.extend(centermap);

            _private.init_polyline();
            _private.init_circles();

            _private.render_leads();
            _private.map_events();
        },

        init_polyline: function(){
            if(window.polylines==undefined || window.polylines.length==0)
                return false;
            var lanLng = [];
            $.each(window.polylines, function (key, flightPlanCoordinates) {

                lanLng = [];
                $.each(flightPlanCoordinates, function (position_key, position_val) {
                    split_arr = position_val.split(',');
                    lanLng.push({lat:parseFloat(split_arr[0].trim()), lng:parseFloat(split_arr[1].trim())});
                });

                flightPath[key] = new google.maps.Polyline({
                    path: lanLng,
                    geodesic: true,
                    strokeColor: "#FF0000",
                    strokeOpacity: 1.0,
                    strokeWeight: 2,
                });

                flightPath[key].setMap(map);
            });

        },

        init_circles: function(){
            if(window.circles==undefined || window.circles.length==0)
                return false;

            var options = {};
            $.each(window.circles, function (circle_key, circle_value) {
                options = {
                    map,
                    center: {lat: circle_value.lat, lng:circle_value.lng},
                    radius: circle_value.radius,
                    strokeWeight: 1,
                    strokeOpacity: 0.3,
                    fillOpacity: 0.1,
                    fillColor: "#FF0000",
                    strokeColor: "#FF0000"
                };

                if(circle_value.fillColor != undefined && circle_value.fillColor)
                    options['fillColor'] = circle_value.fillColor;

                if(circle_value.strokeColor != undefined && circle_value.strokeColor)
                    options['strokeColor'] = circle_value.strokeColor;

                if(circle_value.strokeWeight != undefined && circle_value.strokeWeight)
                    options['strokeWeight'] = circle_value.strokeColor;

                if(circle_value.strokeOpacity != undefined && circle_value.strokeOpacity)
                    options['strokeOpacity'] = circle_value.strokeOpacity;

                if(circle_value.fillOpacity != undefined && circle_value.fillOpacity)
                    options['fillOpacity'] = circle_value.fillOpacity;

                new google.maps.Circle(options);

            });

        },

        render_leads: function(){
            $.each(window.leads, function(key, marker){
                if(marker.latitude == undefined || marker.longitude == undefined || !parseFloat(marker.latitude) || !parseFloat(marker.longitude)){
                    _private.codeAddress(marker.full_address, function(results){
                        marker.latitude = results[0].geometry.location.lat();
                        marker.longitude = results[0].geometry.location.lng();
                        _private.placeMarker(marker);
                    });
                }
                else{
                    _private.placeMarker(marker);
                }
            });
        },
        render_tasks: function(data){
            if(data.tasks.lenght==0)
                return;

            $.each(data.tasks, function(key, marker){
                marker.categories = data.categories;
                console.log(marker)

                if(marker.task_latitude == undefined || marker.task_longitude == undefined || !parseFloat(marker.task_latitude) || !parseFloat(marker.task_longitude)){
                    _private.codeAddress(marker.full_address, function(results){
                        marker.latitude = results[0].geometry.location.lat();
                        marker.longitude = results[0].geometry.location.lng();
                        _private.placeMarker(marker, 'task');
                    });
                }
                else{
                    marker.latitude = marker.task_latitude;
                    marker.longitude = marker.task_longitude;
                    _private.placeMarker(marker, 'task');
                }
            });
        },

        placeMarker: function(marker, type, grabDuplicates = true){
            if(type == undefined || !type)
                type = 'lead';

            if(marker.latitude == undefined || marker.longitude == undefined)
                return false;

            position = new google.maps.LatLng(marker.latitude, marker.longitude);
            key = marker.latitude+'_'+marker.longitude;

            if(markers_object[type][key] !=undefined && markers_object[type][key]!=undefined){
                markers_object[type][key].setMap(null);
            }

            var pin_data = _private.pin_data(marker, type);
            var image = _private.pin_style(type, pin_data.title, pin_data.color, pin_data.icon);

            if(markers_object[type][key] != undefined)
                markers_object[type][key] = {};

            markers_object[type][key] = new google.maps.Marker({
                position: position,
                map: map,
                draggable: false,
                fillColor:'#ccc',
                icon: image
            });

            bounds.extend(position);

            marker['marker_key'] = key;
            markers_object[type][key]['type'] = type;
            markers_object[type][key]['content'] = marker;

            _private.marker_events(markers_object[type][key]);

            if (grabDuplicates === true) {
                _private.set_duplicates_marker(key, type, {marker: marker});
            }
        },

        map_events: function () {

        },

        marker_events: function (marker){
            marker.addListener('click', function() {
                _private.render_infowindow(marker);
            });
        },
        /**
         * Method rerender info window at map if info window has been opened
         */
        reopen_infowindow: function() {
            var marker_key = $(config.ui.infowindow_container).find(config.ui.marker_key).val();
            let marker = (markers_object['lead'][marker_key] === undefined) ? markers_object['task'][marker_key] : markers_object['lead'][marker_key];
            let map = infowindow.getMap();
            let isInfowindowVisible = (map !== null && typeof map !== "undefined");
            if (isInfowindowVisible === true) {
                infowindow.close();
                infowindow.open(map, marker);
            }
        },
        render_infowindow:function(marker, save_open){
            var marker_key = $(config.ui.infowindow_container).find(config.ui.marker_key).val();

            if(marker.content.marker_key!=undefined && marker_key!=undefined && marker.content.marker_key == marker_key && infowindow.getMap() && (save_open==undefined || save_open==false))
                return false;

            if(infowindow.getMap() && (save_open==undefined || save_open==false))
                infowindow.close();

            if(marker.type=='lead'){
                marker.content['statuses'] = public.helpers.getLeadStatuses(marker.content.lead_status_id);
                marker.content['sms'] = public.helpers.getSms();
            }

            var renderView = {template_id:config.templates.infowindow[marker.type], view_container_id:config.views.infowindow, data:[marker.content] , helpers:public.helpers};
            Common.renderView(renderView);
            form = $(config.views.infowindow).html();

            infowindow.setContent(form);

            if(save_open==undefined || save_open==false) {
                infowindow.open(map, marker);
            }
        },

        pin_data: function (marker, type) {

            var pin_data = {
                title:"",
                icon:"",
                color:marker.marker_color,
            };

            if(marker.user != undefined && marker.user && parseInt(marker.user.id)){
                pin_data.color = marker.user.color;
            }

            if(type=='lead'){
                pin_data.title = config.images.default(marker.lead_days, pin_data.color);
                if(parseInt(marker.lead_call)){
                    //pin_data.title = config.images.phone(pin_data.color);
                    pin_data.icon += config.images.phone(pin_data.color);
                }
                if(marker.client && parseInt(marker.client.workorders_count) > 0){
                    pin_data.icon += config.images.star;
                }
                if(marker.marker_priority_icon && config.images[marker.marker_priority_icon]!=undefined){
                    pin_data.icon += config.images[marker.marker_priority_icon];
                }
            }

            if(type=='task'){
                pin_data.title = config.images.default_task((marker.task_schedule_date)?marker.task_schedule_date:'T', pin_data.color)
            }

            return pin_data;
        },

        pin_style: function (template, title, color, icon) {

            text = title+icon;

            if(template=='task'){
                tmp = config.images.task_marker_template(text, color);
            }
            if(template == 'lead'){
                tmp = config.images.lead_marker_template(text, color);
            }

            var image = 'data:image/svg+xml;base64,' + btoa(tmp);
            return image;
        },

        render_lead:function(response){
            var marker_key = $(config.ui.infowindow_container+'[id="'+response.lead.lead_id+'"]').find(config.ui.marker_key).val();
            response.lead['marker_key'] = marker_key;

            markers_object['lead'][marker_key].content = response.lead;
            _private.render_infowindow(markers_object['lead'][marker_key], true);

            var pin_data = _private.pin_data(response.lead, 'lead');
            var image = _private.pin_style('lead', pin_data.title, pin_data.color, pin_data.icon);
            markers_object['lead'][marker_key].setIcon(image);
            return true;
        },

        codeAddress: function(address, callback){
            geocoder.geocode({ 'address': address }, function (results, status) {
                if (status == 'OK')
                    callback(results);
                else
                    console.log('Geocode was not successful for the following reason: ' + status);
            });
        },

        init_tracker: function () {
            Common.request.send(config.route.tracker, {}, function(response){
                vehicles = response;
                if(map !== undefined)
                    displayVehicles();

                return false;
            }, function () {});
        },

        /**
         * @param marker as string
         * @param type
         */
        set_duplicates_marker: function(marker, type, duplicates) {

            var isDuplicate = false;

            markersDuplicatesArray.map( function(value, index) {
                if (value[type] !== undefined && value[type].marker === marker && value[type].type === type) {
                    markersDuplicatesArray[index][type] = {...markersDuplicatesArray[index][type], value: value[type].value + 1};

                    markersDuplicatesArray[index][type] = {
                        ...markersDuplicatesArray[index][type],
                        'duplicates' : [
                            ...markersDuplicatesArray[index][type]['duplicates'], duplicates.marker
                        ]
                    };
                    isDuplicate = true;
                } else if (
                    (value[type] === undefined && type === 'task' && value['lead'].marker === marker) ||
                    (value[type] === undefined && type === 'lead' && value['task'].marker === marker)
                ) {
                    markersDuplicatesArray[index] = {
                        ...markersDuplicatesArray[index], [type] : {
                                marker: marker,
                                type: type,
                                value: 1,
                                duplicates: [duplicates.marker]
                            }
                    };
                    isDuplicate = true;
                }
            });
            if (isDuplicate === false) {
                markersDuplicatesArray.push({[type]: {marker: marker, type: type, value: 1, duplicates: [duplicates.marker]}});
            }


        },

        /**
         * method update marker position if isset in markersDuplicatesArray
         */
        update_double_positions: function() {

            markersDuplicatesArray.map( function(duplicatesArray, index) {

                let leadDuplicateItems = duplicatesArray['lead'] !== undefined ? duplicatesArray['lead'].value : 0;
                let taskDuplicateItems = duplicatesArray['task'] !== undefined ? duplicatesArray['task'].value : 0;
                let countDuplicateItems = leadDuplicateItems + taskDuplicateItems;

                if (countDuplicateItems > 1) {
                    sqrt = Math.ceil(Math.sqrt(countDuplicateItems));
                    startForDuplicates = iForDuplicates = jForDuplicates = Math.ceil(sqrt / 2);

                    if (leadDuplicateItems > 0) {
                        _private.set_duplicate_markers_position('lead', duplicatesArray['lead']);
                    }
                    if (taskDuplicateItems > 0) {
                        _private.set_duplicate_markers_position('task', duplicatesArray['task']);
                    }
                }
            });
        },

        /**
         * @param type
         * @param duplicatesArray
         */
        set_duplicate_markers_position: function(type, duplicatesArray) {
            markers_object[type][duplicatesArray.marker].setMap(null);
            duplicatesArray.duplicates.map( function(lead, index) {
                originLatForDuplicates = parseFloat(markers_object[duplicatesArray.type][duplicatesArray.marker].content.latitude);
                originLonForDuplicates = parseFloat(markers_object[duplicatesArray.type][duplicatesArray.marker].content.longitude);

                type_lat = originLatForDuplicates + (iForDuplicates * diffLatForDuplicates);
                type_lon = originLonForDuplicates + (jForDuplicates * diffLonForDuplicates);

                lead.latitude = type_lat;
                lead.longitude = type_lon;

                _private.placeMarker(lead, type, false);

                iForDuplicates--;
                if (iForDuplicates == -(startForDuplicates - 1)) {
                    iForDuplicates = startForDuplicates;
                    jForDuplicates--;
                }
            });
        },

        init_tasks: function (callback_init) {
            var callback_init_tasks = callback_init;
            var callback = function(response){
                _private.render_tasks(response.data);
                setTimeout(function () {
                    callback_init_tasks();
                }, 100)
            };

            Common.request.get(config.route.tasks, callback);

        },

        /**
         * @param visible
         * @param array
         * @param lead_id
         */
        set_available_leads_array: function(visible, array, lead_id) {
            if (visible === true) {
                array.push(lead_id);
            } else {
                var position = array.indexOf(lead_id);
                if ( ~position ) array.splice(position, 1);
            }
        },

        /**
         * @param serviceListIds
         * @param leadKey
         * @param lead_id
         * @param lead_services
         * @param isBundle
         * @param isProduct
         * @param array
         */
        show_hide_leads_by_service: function(serviceListIds, lead_id, lead_services, isBundle, isProduct, array) {

            if (serviceListIds.length > 0) {
                for (var i = 0; i < lead_services.length; i++) {
                    if(lead_services[i].is_bundle == isBundle && lead_services[i].is_product == isProduct) {
                        if (serviceListIds.includes(lead_services[i].service_id + '') === true || serviceListIds.includes(lead_services[i].service_id) === true) {
                            _private.set_available_leads_array(true, array, lead_id);
                            break;
                        } else {
                            _private.set_available_leads_array(false, array, lead_id);
                        }
                    }
                }
            } else {
                _private.set_available_leads_array(false, array, lead_id);
            }
        },

        nullify_all_service_arrays: function () {
            allLeadsArray = [];
            availableLeadsArray = [];
            filteredByServicesLeadsArray = [];
            filteredByProductLeadsArray = [];
            filteredByBundleLeadsArray = [];
        },
        /**
         * @param show
         */
        search_by_services: function(show) {

            var serviceIds = $('input[name="est_services"]').val();
            var productIds = $('input[name="est_products"]').val();
            var bundleIds = $('input[name="est_bundles"]').val();

            var serviceListIds = serviceIds.length == 0 ? [] : serviceIds.split('|');
            var productListIds = productIds.length == 0 ? [] : productIds.split('|');
            var bundleListIds = bundleIds.length == 0 ? [] : bundleIds.split('|');
            var intersection = [];

            _private.nullify_all_service_arrays();

            $.each(markers_object['lead'], function (key, value) {

                if (value.content.lead_services.length > 0) {
                    //Is Service
                    _private.show_hide_leads_by_service(serviceListIds, value.content.lead_id, value.content.lead_services, 0, 0, filteredByServicesLeadsArray);
                    //END Is Service

                    //Is Product
                    _private.show_hide_leads_by_service(productListIds, value.content.lead_id, value.content.lead_services, 0, 1, filteredByProductLeadsArray);
                    //END Is Product

                    //Is Bundle
                    _private.show_hide_leads_by_service(bundleListIds, value.content.lead_id, value.content.lead_services, 1, 0, filteredByBundleLeadsArray);
                    //END Is Bundle
                } else {
                    _private.set_available_leads_array(false, availableLeadsArray, key);
                }

                _private.set_available_leads_array(true, allLeadsArray, value.content.lead_id);
            });

            if (serviceListIds.length === 0 && productListIds.length === 0 && bundleListIds.length === 0) {
                availableLeadsArray = allLeadsArray;
                return true;
            }
            if (filteredByServicesLeadsArray.length === 0 && serviceListIds.length > 0) {
                _private.nullify_all_service_arrays();
                return true;
            }
            if (filteredByProductLeadsArray.length === 0 && productListIds.length > 0) {
                _private.nullify_all_service_arrays();
                return true;
            }
            if (filteredByBundleLeadsArray.length === 0 && bundleListIds.length > 0) {
                _private.nullify_all_service_arrays();
                return true;
            }

            if (filteredByServicesLeadsArray.length > 0) {
                if (filteredByProductLeadsArray.length > 0) {
                    intersection = filteredByServicesLeadsArray.filter((element) => {
                        return filteredByProductLeadsArray.includes(element)
                    });
                } else {
                    intersection = filteredByServicesLeadsArray;
                }
            } else {
                if (filteredByProductLeadsArray.length > 0) {
                    intersection = filteredByProductLeadsArray;
                }
            }

            if (intersection.length > 0) {
                if (filteredByBundleLeadsArray.length > 0) {
                    availableLeadsArray = intersection.filter((element) => {
                        return filteredByBundleLeadsArray.includes(element)
                    });
                } else {
                    availableLeadsArray = intersection;
                }
            } else {
                if (filteredByBundleLeadsArray.length > 0) {
                    availableLeadsArray = filteredByBundleLeadsArray;
                } else {
                    availableLeadsArray = [];
                }
            }
        },

        show_hide_lead: function() {

            _private.search_by_user();
            _private.search_by_priority();
            _private.search_by_services();
            infowindow.close();

            if (filteredByUsersLeadsArray.length === availableLeadsArray.length && availableLeadsArray.length === filteredByPriorityLeadsArray.length) {
                filteredByUsersLeadsArray = [];
                availableLeadsArray = [];
                filteredByPriorityLeadsArray = [];
            }

            $.each(markers_object['lead'], function (key, value) {
                if (availableLeadsArray.length > 0 || filteredByUsersLeadsArray.length > 0 || filteredByPriorityLeadsArray.length > 0) {
                    if (
                        availableLeadsArray.includes(value.content.lead_id) &&
                        filteredByUsersLeadsArray.includes(value.content.lead_id) &&
                        filteredByPriorityLeadsArray.includes(value.content.lead_id)
                    ) {
                        markers_object['lead'][key].setVisible(true);
                    } else {
                        markers_object['lead'][key].setVisible(false);
                    }
                } else {
                    markers_object['lead'][key].setVisible(true);
                }
            });
        },

        show_hide_task: function () {
            infowindow.close();
            var visible = $('.showTasks').is(':checked');
            $.each(markers_object['task'], function (key, value) {
                markers_object['task'][key].setVisible(visible);
            });
        },

        leads: function () {
            infowindow.close();
            var visible = $('.showLeads').is(':checked');
            if(visible) {
                $('.lead-map-filer-block').fadeIn();
                _private.show_hide_lead();
            } else {
                $('.lead-map-filer-block').fadeOut();
                $.each(markers_object['lead'], function (key, value) {
                    markers_object['lead'][key].setVisible(visible);
                });
            }
        },

        show_hide_vehicles: function () {
            infowindow.close();
            var visible = $('.showVehicles').is(':checked');
            $.each(vehMarkers, function (key, value) {
                vehMarkers[key].setVisible(visible);
                var mapInstance = visible ? map : null;
                vehLabels[key].setMap(mapInstance);
            });
        },

        search_by_user: function () {
            var userIds = $('input[name="users"]').val();
            var userListIds = userIds.length == 0 ? [] : userIds.split('|');
            var userAssignedChecked = $('.showPoint').prop('checked');
            filteredByUsersLeadsArray = [];

            $.each(markers_object['lead'], function (key, value) {

                if (userListIds.length > 0 && userAssignedChecked === true) {

                    if (
                        (value.content.user && value.content.user.id && userListIds.includes(value.content.user.id + '')) ||
                        (!value.content.user || (value.content.user && !parseInt(value.content.user.id)))
                    ) {

                        _private.set_available_leads_array(true, filteredByUsersLeadsArray, value.content.lead_id);
                    } else {
                        _private.set_available_leads_array(false, filteredByUsersLeadsArray, value.content.lead_id);
                    }
                } else if (userListIds.length > 0 && userAssignedChecked === false) {

                    if (value.content.user && value.content.user.id && userListIds.includes(value.content.user.id + '')) {
                        _private.set_available_leads_array(true, filteredByUsersLeadsArray, value.content.lead_id);
                    } else {
                        _private.set_available_leads_array(false, filteredByUsersLeadsArray, value.content.lead_id);
                    }
                } else if (userListIds.length === 0 && userAssignedChecked === true) {

                    if (!value.content.user || (value.content.user && !parseInt(value.content.user.id))) {
                        _private.set_available_leads_array(true, filteredByUsersLeadsArray, value.content.lead_id);
                    } else {
                        _private.set_available_leads_array(false, filteredByUsersLeadsArray, value.content.lead_id);
                    }
                } else if (userListIds.length === 0 && userAssignedChecked === false) {

                    _private.set_available_leads_array(true, filteredByUsersLeadsArray, value.content.lead_id);
                }
            });
        },

        search_by_priority: function () {
            var priorityIds = $('input[name="proprityInput"]').val();
            var priorityListIds = priorityIds.length == 0 ? [priorityEmergency, priorityPriority, priorityRegular] : priorityIds.split('|');
            filteredByPriorityLeadsArray = [];

            $.each(markers_object['lead'], function (key, value) {
                if (priority_filters[value.content.lead_priority] && priorityListIds.includes(value.content.lead_priority)) {
                    _private.set_available_leads_array(true, filteredByPriorityLeadsArray, value.content.lead_id);
                } else {
                    _private.set_available_leads_array(false, filteredByPriorityLeadsArray, value.content.lead_id);
                }
            });
        },

        call_checkbox: function () {
            var lead_id = $(this).data('lead_id');
            var checked = ($(this).prop('checked'))?1:0;
            var marker_key = $(this).closest(config.ui.infowindow_container).find(config.ui.marker_key).val();
            var callback = function(response){
                markers_object['lead'][marker_key].content.lead_call = checked;

                var pin_data = _private.pin_data(markers_object['lead'][marker_key].content, 'lead');
                var image = _private.pin_style('lead', pin_data.title, pin_data.color, pin_data.icon);
                markers_object['lead'][marker_key].setIcon(image);
            };

            Common.request.send(config.route.call, {call:checked,lead_id:lead_id}, callback, function () { errorMessage('Ooops! Error.'); });
        },

        assigned_form_submit: function () {
            $(this).closest('form').trigger('submit');
        },

        change_lead_status: function () {
            var status_id = $(config.events.lead_status+' option:selected').val();
            var current_status = {};
            $.each(window.statuses, function (key, value) {
                if(status_id == value.lead_status_id)
                    current_status = value;
            });

            var reasonsDataView = {template_id:config.templates.lead_status_reasons, view_container_id:config.views.lead_status_reasons, data:[current_status] , helpers:public.helpers};
            var buttonsDataView = {template_id:config.templates.lead_status_buttons, view_container_id:config.views.lead_status_buttons, data:[current_status] , helpers:public.helpers};
            Common.renderView(reasonsDataView);
            Common.renderView(buttonsDataView);
        },

        change_task_status:function(){
            var status = $(config.events.task_status+' option:selected').val();
            var statusDataView = {
                template_id:config.templates.task_status_buttons,
                view_container_id:config.views.task_status_buttons,
                data:[{'status':status}],
                helpers:public.helpers
            };

            Common.renderView(statusDataView);
        },

        change_task_category:function(){
            $(this).closest('form').trigger('submit');
        },

        change_task_assigned_user:function(){
            $(this).closest('form').trigger('submit');
        },

        change_task_schedule:function(){
            let task_date = $(config.events.task_schedule_date).val();
            let task_time_start = $(config.events.task_schedule_time_start).val();
            let task_time_end = $(config.events.task_schedule_time_end).val();

            let statusDataView = {
                template_id:config.templates.task_schedule_buttons,
                view_container_id:config.views.task_schedule_buttons,
                data:[{
                    'task_date':task_date,
                    'task_time_start':task_time_start,
                    'task_time_end':task_time_end,
                }],
                helpers:public.helpers
            };

            Common.renderView(statusDataView);
        },

        lead_status_form_reset: function () {
            setTimeout(function () {
                $(config.events.lead_status).trigger('change');
            }, 200)
        },

        task_status_form_reset: function () {
            setTimeout(function () {
                $(config.events.task_status).trigger('change');
            }, 200)
        },

        task_schedule_form_reset: function () {
            setTimeout(function () {
                $(config.events.task_schedule_date).trigger('change');
                $(config.events.task_schedule_time_start).trigger('change');
                $(config.events.task_schedule_time_end).trigger('change');
            }, 200)
        },

        change_lead_priority:function () {

            var value = $(this).data('value');
            $(this).closest('form').find(config.ui.priority).val(value);
            $(this).closest('form').trigger('submit');
            infowindow.close();
        }

    };

    var public = {
        init:function(){
            $(document).ready(function(){
                public.events();
                _private.init();
            });
        },

        events:function() {

            $(document).on('change', config.events.show_task_checkbox, _private.show_hide_task);
            $(document).on('change', config.events.show_lead_checkbox, _private.leads);
            $(document).on('change', config.events.show_vehicles_checkbox, _private.show_hide_vehicles);

            $(document).on('change', config.events.call_checkbox, _private.call_checkbox);

            $(document).on('change', config.events.assigned_to, _private.assigned_form_submit);

            $(document).on('change', config.events.lead_status, _private.change_lead_status);
            $(document).on('change', config.events.task_status, _private.change_task_status);

            $(document).on('change', config.events.task_category, _private.change_task_category);

            $(document).on('change', config.events.task_assigned_user, _private.change_task_assigned_user);

            $(document).on('change', config.events.task_schedule_date, _private.change_task_schedule);
            $(document).on('change', config.events.task_schedule_time_start, _private.change_task_schedule);
            $(document).on('change', config.events.task_schedule_time_end, _private.change_task_schedule);

            $(document).on('reset', config.events.lead_status_form, _private.lead_status_form_reset);
            $(document).on('reset', config.events.task_status_form, _private.task_status_form_reset);
            $(document).on('reset', config.events.task_schedule_form, _private.task_schedule_form_reset);


            $(document).on('click', config.events.change_lead_priority, _private.change_lead_priority);

            $(document).on('change', config.events.show_service_lead_select2, _private.show_hide_lead);
            $(document).on('change', config.events.show_product_lead_select2, _private.show_hide_lead);
            $(document).on('change', config.events.show_bundle_lead_select2, _private.show_hide_lead);

            $(document).on('change', config.events.show_user_lead_select2, _private.show_hide_lead);
            $(document).on('change', config.events.show_user_lead_not_assign, _private.show_hide_lead);
            $(document).on('change', config.events.show_priority_lead_select2, _private.show_hide_lead);

            $(document).on('click', '.day-off-btn', function() {
                if($('.day-off-container').is(':visible')) {
                    //$(this).find('i').removeClass('fa-angle-left').addClass('fa-angle-filter');
                    $('.day-off-container').animate({'right': '-50%'}, 300, function () {
                        $('.day-off-container').hide();
                    });
                    $('#leads-map').css({'max-width':'100%'});
                }
                else {
                    $('#leads-map').css({'max-width':'70%'});
                    _private.reopen_infowindow();
                    //$(this).find('i').removeClass('fa-angle-filter').addClass('fa-angle-left');
                    $('.day-off-container').show();
                    $('.day-off-container').animate({'right': 0}, 300);
                }
            });
        },

        lead_priority: function(response){
            _private.render_lead(response);
            return false;
        },

        assigned_user:function(response){
            _private.render_lead(response);
            return false;
        },

        change_lead_status_callback: function(response){
            if(response.lead.status.lead_status_default!=0)
                return false;

            var marker_key = $(config.ui.infowindow_container+'[id="'+response.lead.lead_id+'"]').find(config.ui.marker_key).val();
            infowindow.close();
            markers_object['lead'][marker_key].setMap(null);
            delete markers_object['lead'][marker_key];
            successMessage(response.message);
        },

        change_task_status_callback: function(response){
            if(response.status=='error'){
                return true;
            }

            let marker_key = $(config.ui.infowindow_container+'[id="'+response.task.task_id+'"]').find(config.ui.marker_key).val();
            infowindow.close();
        },

        change_task_category_callback: function(response){
            if(response.status=='error'){
                return true;
            }

            let marker_key = $(config.ui.infowindow_container+'[id="'+response.task.task_id+'"]')
                .find(config.ui.marker_key)
                .val();

            infowindow.close();
        },

        change_task_assigned_user_callback: function(response){
            if(response.status=='error'){
                return true;
            }

            let marker_key = $(config.ui.infowindow_container+'[id="'+response.task.task_id+'"]')
                .find(config.ui.marker_key)
                .val();

            infowindow.close();
        },

        change_task_schedule_callback: function(response){
            if(response.status=='error'){
                return true;
            }

            let marker_key = $(config.ui.infowindow_container+'[id="'+response.task.task_id+'"]')
                .find(config.ui.marker_key)
                .val();

            infowindow.close();
        },

        helpers:{
            getPriorities: function(priority){
                result = [];
                $.each(window.priority, function (key, value) {
                    value['selected'] = false;
                    if(key==priority)
                        value['selected'] = true;

                    result.push(value);
                });
                return result;
            },

            getPriorityClass: function(priority){
                if(window.priority[priority]!=undefined)
                    return window.priority[priority]['class'];

                return 'text-success';
            },

            getLeadStatuses:function (status_id) {
                result = [];
                $.each(window.statuses, function (key, value) {
                    value['selected'] = false;
                    if(status_id!=undefined && value.lead_status_id==status_id)
                        value['selected'] = true;

                    result.push(value);
                });
                return result;
            },

            getEstimators:function(estimator_id){
                result = [];
                $.each(window.estimators, function (key, value) {
                    value['selected'] = false;
                    if(estimator_id!=undefined && value.id==estimator_id)
                        value['selected'] = true;

                    result.push(value);
                });
                return result;
            },
            getSms:function(estimator_id){
                return window.sms;
            },
            intval: function (val) {
                return parseInt(val);
            },
            getCategory: (categories, category_id) => (
                 categories.map((el) => {
                    el.selected = el.category_id === +category_id ? true : false;
                    return el
                })
            )

        }
    };

    public.init();
    return public;
}();
