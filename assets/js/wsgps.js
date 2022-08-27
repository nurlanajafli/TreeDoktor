var track_pins = {};
var offline_pins = {};
var saved_data = {};

$(document).ready(function(){

    if ($('#toggle_gps input').prop('checked') == true) {
        ws.send({method: 'getTrackPins', params: {}});
    }
    
    function createTrackingMarker(val) {
        var image_icon = 'data:image/svg+xml;base64,' + btoa('<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" stroke="#000"  stroke-width="1" width="42px" height="42px" viewBox="14 0 96 128"><defs><style>.cls-1{fill:'+val.color+';}.cls-2{fill:#2e79bd;}.blink{animation: blink 2s infinite;}@keyframes blink {from { opacity: 0;} to { opacity: 1;}</style></defs><path class="cls-1" d="M64.00178,3.36652c-25.74943,0-43.04956,14.75866-43.04956,36.7246,0,29.11223,37.01485,81.60069,37.38345,82.01113a7.60318,7.60318,0,0,0,11.3233.00579c.37394-.41623,37.3888-52.90469,37.3888-82.01692C107.04778,18.12518,89.74853,3.36652,64.00178,3.36652ZM64"/><text xmlns="http://www.w3.org/2000/svg" transform="translate(64 62)" fill="#000000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="48" text-anchor="middle">'+val.firstname.charAt(0)+val.lastname.charAt(0)+'</text><text xmlns="http://www.w3.org/2000/svg" transform="translate(64 115)" fill="#28e628" style="font-family: Arial, sans-serif;text-align:center;font-weight:bold;text-shadow: 0 1px #46a74a;" class="blink" font-size="75" text-anchor="middle">&#8226;</text></svg>');
        /*var image = {
            url: baseUrl + "assets/img/human.png",                
            anchor: new google.maps.Point(16, 32)
        };*/
        
        if(typeof val.login_id != 'undefined' && val.login_id == null){ //offline            
            var image_icon = 'data:image/svg+xml;base64,' + btoa('<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" stroke="#000"  stroke-width="1" width="42px" height="42px" viewBox="14 0 96 128"><defs><style>.cls-1{fill:'+val.color+';}.cls-2{fill:#2e79bd;}.blink{animation: blink 3s infinite;}@keyframes blink {from { opacity: 1;} to { opacity: 0.7;}}</style></defs><path class="cls-1" d="M64.00178,3.36652c-25.74943,0-43.04956,14.75866-43.04956,36.7246,0,29.11223,37.01485,81.60069,37.38345,82.01113a7.60318,7.60318,0,0,0,11.3233.00579c.37394-.41623,37.3888-52.90469,37.3888-82.01692C107.04778,18.12518,89.74853,3.36652,64.00178,3.36652ZM64"/><text xmlns="http://www.w3.org/2000/svg" transform="translate(64 62)" fill="#000000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="48" text-anchor="middle">'+val.firstname.charAt(0)+val.lastname.charAt(0)+'</text><text xmlns="http://www.w3.org/2000/svg" transform="translate(64 115)" fill="grey" style="font-family: Arial, sans-serif;text-align:center;font-weight:bold;text-shadow: 0 1px #fff;" class="blink" font-size="75" text-anchor="middle">&#8226;</text></svg>');
            //image.url = baseUrl + "assets/img/humang.png";
        }

        //var info_content = '<div><strong>' + val.firstname + ' ' + val.lastname + '</strong><br> <span>Lat: '+ val.ut_lat +'<br>Long: '+ val.ut_lng +'</span></div>';
        var info_content = '<div><strong>' + val.firstname + ' ' + val.lastname + '</strong><br>Date: ' + val.ut_date + '</span></div>';
        
        var latlng = new google.maps.LatLng(val.ut_lat, val.ut_lng);
        var markerOptions = {
            map: map,
            position: latlng,
            icon: image_icon
        };
        var tr_marker = new google.maps.Marker(markerOptions);
        
        google.maps.event.addListener(tr_marker, 'click', function() {
            if (typeof infowindow != 'undefined' && infowindow) infowindow.close();
            infowindow = new google.maps.InfoWindow({
                content: info_content,
            });
            infowindow.open(map, this);
        });
        track_pins[val.ut_user_id] = tr_marker;
        if(typeof val.login_id != 'undefined' && val.login_id == null){ //offline
            offline_pins[val.ut_user_id] = tr_marker; 
        } else {
            saved_data[val.ut_user_id] = val;
        }           
                 
    }
    
    $('#toggle_gps input').change(function() {

        if ($('#toggle_gps input').prop('checked') == true) {
            ws.send({method:'getTrackPins', params:{}});
            //$('#toggle_gps').attr('data-visible', 1);
            $('#toggle_offline_gps').closest('.show-offline-container').show();
        } else {
            //$('#toggle_gps').attr('data-visible', 0);
            $('#toggle_offline_gps input').prop('checked', false);
            $.each(track_pins, function(key, val){
                track_pins[key].setMap(null);
            });
            $.each(offline_pins, function(key, val){
                offline_pins[key].setMap(null);
            });
            
            $('#toggle_offline_gps').closest('.show-offline-container').hide();
        }
        
    });
    
    $('#toggle_offline_gps input').change(function() {
        if ($('#toggle_offline_gps input').prop('checked') == true) {
            $.each(offline_pins, function(key, val){
                offline_pins[key].setVisible(true);
            });
        } else {
            $.each(offline_pins, function(key, val){
                offline_pins[key].setVisible(false);
            });
        }
    });
    
    callback.getTrackPinsCallback = function(data) {
        if(typeof data.rows != 'undefined'){
                                        
            $.each(data.rows, function(key, val){                
                //if(val.minutes_diff > 30){
                    //val.login_id = null; //to force offline status
                //}
                createTrackingMarker(val);
            });
            $('#toggle_offline_gps input').prop('checked', $('.showAll').prop('checked')).trigger('change');
            
        }
    }
    
    callback.newTrackingDataCallback = function(data) {

        if(typeof data.ut_user_id != 'undefined'){

            var id_to_remove = data.ut_user_id;
            if(typeof track_pins[data.ut_user_id] != 'undefined'){
                track_pins[id_to_remove].setMap(null);
            }
            if(typeof offline_pins[data.ut_user_id] != 'undefined'){
                offline_pins[id_to_remove].setMap(null);
            }
            if ($('#toggle_gps input').prop('checked') == true) {
                createTrackingMarker(data);
            }
        }
    }
    
    callback.getOfflinePinsForAllCallback = function(data) {

        if(typeof data != 'undefined'){
                                                    
            $.each(data, function(key, val){
                if(typeof track_pins[val.ut_user_id] != 'undefined'){                
                    track_pins[val.ut_user_id].setMap(null);
                }
                if(typeof offline_pins[val.ut_user_id] != 'undefined'){                
                    offline_pins[val.ut_user_id].setMap(null);
                }
                
                val.login_id = null; //to force offline status 
                createTrackingMarker(val);
                
                if($('#toggle_offline_gps input').prop('checked') == false) {
                    offline_pins[val.ut_user_id].setVisible(false);
                }
            });            
            
        }
    }
    
    callback.userStoppedCallback = function(data) {
        
        if(typeof data != 'undefined'){
            if(typeof track_pins[data] != 'undefined'){
                var offline_pin = saved_data[data];
                offline_pin.login_id = null; //to force offline status                
                track_pins[data].setMap(null);
                createTrackingMarker(offline_pin);
                
                if($('#toggle_offline_gps input').prop('checked') == false) {
                    offline_pins[data].setVisible(false);
                }
            }
        }
    }
    
});

