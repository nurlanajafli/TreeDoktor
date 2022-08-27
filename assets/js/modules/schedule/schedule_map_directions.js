var ScheduleMapDirections = function(){
    var config = {

        ui:{},

        events:{
            show_team_markers: ".show-team-markers"
        },

        route:{},

        templates:{},

        views:{},

        images: {},

        const: {
            office_address: OFFICE_ADDRESS + ', ' + OFFICE_CITY + ', ' + OFFICE_STATE + ', ' + OFFICE_COUNTRY
        }
    };

    var _private = {

        directionsService: new google.maps.DirectionsService(),
        directionsRenderer: {},
        routes: {},
        home: false,

        renderDirections: function(map, result, polylineOpts, teamId) {
            teamId = teamId.toString();
            _private.directionsRenderer[teamId] = new google.maps.DirectionsRenderer();
            _private.directionsRenderer[teamId].setMap(map);

            if(polylineOpts) {
                _private.directionsRenderer[teamId].setOptions({
                    polylineOptions: polylineOpts,
                    suppressMarkers: true
                });
            }

            _private.directionsRenderer[teamId].setDirections(result);
            
            _private.routes[teamId.toString()] = result.geocoded_waypoints.length-1;
        },

        requestDirections: function(map, end, polylineOpts, points, teamId) {
            var current_map = map;
            var waypoints = [];
            $.each(points, function(key, val){
                if(key != points.length - 1)
                    waypoints.push({location:val,stopover:true});
            });
            var origin = config.const.office_address.replaceAll(',', '').replaceAll(' ', '+');
            _private.directionsService.route({
                origin: origin,
                destination: end,
                waypoints: waypoints,
                //region: teamId.toString(),
                travelMode: google.maps.DirectionsTravelMode.DRIVING,
                avoidTolls: true,
                optimizeWaypoints: true,
            }, function(result, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    _private.renderDirections(current_map, result, polylineOpts, teamId);
                }
                else{
                    console.log(result, waypoints, "Start:"+origin, "End"+end);
                }
            });
        },

        requestDirectionsArray: function(end, points, callback, extra_data, errorCallback) {

            var extra = extra_data;
            var callbackFunction = callback;
            var errorCallbackFn = errorCallback;
            var waypoints = [];
            $.each(points, function(key, val){
                if(key != points.length - 1)
                    waypoints.push({location:val,stopover:true});
            });
            var origin = config.const.office_address.replaceAll(',', '').replaceAll(' ', '+');
            console.log("Start:"+origin, "End: "+end, "waypoints:", waypoints);
            _private.directionsService.route({
                origin: origin,
                destination: end,
                waypoints: waypoints,
                travelMode: google.maps.DirectionsTravelMode.DRIVING,
                avoidTolls: true,
                optimizeWaypoints: true,
            }, function(result, status) {
                console.log(result, waypoints, "Start:"+origin, "End"+end);
                if (status == google.maps.DirectionsStatus.OK) {
                    callbackFunction(result, extra);
                }
                else{
                    errorCallback(result, extra);
                }
            });
        },

        enable_map_directions: function (map, id, active) {
            id = id.toString();

            if(!active)
            {
                if(_private.directionsRenderer[id] !== undefined)
                    _private.directionsRenderer[id].setMap(null);
            }
            else
            {
                _private.setHomeAddressMarker(map);

                if(_private.directionsRenderer[id] !== undefined && (_private.routes[id]-1) == ScheduleCommon.scheduled_events['directions'][id].addresses.length){
                    _private.directionsRenderer[id].setMap(map);
                }
                else{
                    var addresses = ScheduleCommon.scheduled_events['directions'][id].addresses;
                    var color = ScheduleCommon.scheduled_events['directions'][id].color;

                    var end = config.const.office_address.replaceAll(',', '').replaceAll(' ', '+');
                    addresses.push(end);
                    console.log("addresses:", addresses);

                    _private.requestDirections(map, end,{strokeColor: color}, addresses, id);
                }
            }

            google.maps.event.trigger(map, "resize");
        },

        setHomeAddressMarker: function (map) {
            if(map==undefined)
                map = ScheduleMapper.getMap();

            if(_private.home){
                _private.home.setMap(map);
                return _private.home;
            }
            else{
                var home = 'data:image/svg+xml;base64,' + btoa('<svg version="1.1" width="35px" height="35px" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512.533 512.533" style="enable-background:new 0 0 512.533 512.533;" xml:space="preserve"><path style="fill:#F3705B;" d="M406.6,62.4c-83.2-83.2-217.6-83.2-299.733,0c-83.2,83.2-83.2,216.533,0,299.733l149.333,150.4L405.533,363.2C488.733,280,488.733,145.6,406.6,62.4z"/><path style="fill:#F3F3F3;" d="M256.2,70.933c-77.867,0-141.867,62.933-141.867,141.867c0,77.867,62.933,141.867,141.867,141.867c77.867,0,141.867-62.933,141.867-141.867S334.066,70.933,256.2,70.933z"/><polygon style="fill:#FFD15D;" points="256.2,112.533 176.2,191.467 176.2,305.6 336.2,305.6 336.2,191.467 "/><g><rect x="229.533" y="241.6" style="fill:#435B6C;" width="54.4" height="64"/><path style="fill:#435B6C;" d="M356.466,195.733L264.733,104c-4.267-4.267-11.733-4.267-17.067,0l-91.733,91.733c-4.267,4.267-4.267,11.733,0,17.067c4.267,4.267,11.733,4.267,17.067,0l83.2-84.267l83.2,83.2c2.133,2.133,5.333,3.2,8.533,3.2c3.2,0,6.4-1.067,8.533-3.2C360.733,207.467,360.733,200,356.466,195.733z"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>');
                _private.home = new google.maps.Marker({
                    position: new google.maps.LatLng(OFFICE_LAT, OFFICE_LON),
                    map: map,
                    draggable: false,
                    fillColor: '#ccc',
                    icon: home
                });
            }

            return _private.home;
        },
    };

    var public = {
        init: function(){
            document.addEventListener('DOMContentLoaded', public.events);
        },

        events: function(){

        },
        enable_map_directions: _private.enable_map_directions,
        setHomeAddressMarker:_private.setHomeAddressMarker,
        home:_private.home,
        requestDirectionsArray: _private.requestDirectionsArray,
    };

    public.init();
    return public;
}();
