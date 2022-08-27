var Events = function(){
	var config = {
		autoscroll_enabled:false,
		ui:{
			create_signature:'[data-action=save-png]',
			modal:"#safety-meeting-form-modal",
			modal_report:'#report-form-modal',
			start_btn:'.start-work-btn',
			stop_btn:'.stop-work-btn',
			buttons:'.event_buttons',
			event_report:'.event'
		},
		events:{
			confirm_start_work: '.confirm-start-work',
			confirm_stop_work: '#confirm-stop-work',
			safety_meeting_form:'#safety-meeting-form',
			report_form:'#report-form'
		},
		route:{
			
		},
		templates:{
		
		}
	}

	var _private = {
		init:function(){
		
		},

		handleLocationError:function(){
			if(typeof(pos) !== 'undefined') {
				infoWindow.setPosition(pos);
			}
			if(typeof(browserHasGeolocation) !== 'undefined') {
				infoWindow.setContent(browserHasGeolocation ?
					'Error: The Geolocation service failed.' :
					'Error: Your browser doesn\'t support geolocation.');
			}
	        infoWindow.open(map);
		},

		routeInGmaps: function(){
			var post_data = $(this).data();
			
			$.ajax({
				method: "POST",
				url: baseUrl + 'events/ride',
				dataType: "json",
				data: post_data,
				global: false,
				success: function(response){
					return false;
				}
			});
			window.open("https://maps.google.be/maps?saddr=" + myPositionAddress + "&daddr="+window.client_address);
		},
		/*
		get_map_id: function(){
			if($(document).width()>1030)
				return "map_canvas";

			return "map_canvas_mobile";
		},
		*/
		codeAddress: function(address, callback){
			geocoder.geocode({ 'address': address }, function (results, status) {
	            if (status == 'OK')
	            	callback(results);
	           	else
	                alert('Geocode was not successful for the following reason: ' + status);
	        });
		}
	}
	
	var map, infoWindow, myPosition, directionsDisplay, myPositionAddress;
	var directionsService = new google.maps.DirectionsService();
	var geocoder;
	geocoder = new google.maps.Geocoder();

	var public = {

		init:function(){
			$(document).ready(function(){
			  	public.events();
			  	public.init_map();
			});
			
		},
		
		events:function(){
			$(document).delegate("#btn-get-geo", 'click', _private.routeInGmaps);
			
			$(config.events.confirm_start_work).click(public.set_image);
			$(config.ui.modal).on('hide.bs.modal', public.set_image);
			$(config.ui.modal_report).on('shown.bs.modal', function(){
				Common.mask_currency();	
			});

			$(config.events.confirm_stop_work).click(public.set_client_image);
			window.init_signature('signature-pad');
			window.init_signature('client-signature-pad');
		},

		init_map:function(){
			directionsDisplay = new google.maps.DirectionsRenderer({
		      	suppressMarkers: true
		    });
		    var centermap = new google.maps.LatLng(window.map_origin[0], window.map_origin[1]);
		    var myOptions = {
		      	zoom: 6,
		      	mapTypeId: google.maps.MapTypeId.ROADMAP,
		      	center: centermap,
		      	dir_action:'navigate',
		      	navigate:true
		    }

		    var map_id = ($(document).width()>1200)?'map_canvas':'map_canvas_mobile';

		    map = new google.maps.Map(document.getElementById(map_id), myOptions);
		    directionsDisplay.setMap(map);

			infoWindow = new google.maps.InfoWindow;
			if (navigator.geolocation) {
			  	navigator.geolocation.getCurrentPosition(function(position) {
					myPosition = {lat: position.coords.latitude, lng: position.coords.longitude};
					//myPosition = {lat: position.coords.latitude, lng: position.coords.longitude};
					//console.log(position);
					
					geocoder.geocode({location: new google.maps.LatLng(position.coords.latitude, position.coords.longitude)}, function (results, status) {
			            myPositionAddress = results[0].formatted_address;
			        });


			    	infoWindow.setPosition(myPosition);
			    	map.setCenter(myPosition);

			    	public.calc_route();
			  	}, function() {
					_private.handleLocationError(true, infoWindow, map.getCenter());
			 	});
			} else {
			  	_private.handleLocationError(false, infoWindow, map.getCenter());
			}
		},
		
		start_work:function(response){
			if(response.status=='error')
				return;
			/*$(config.ui.start_btn).remove();
			$(config.ui.modal).modal('hide');

			$(config.ui.buttons).replaceWith(response.event_buttons);
			$(config.ui.event_report).replaceWith(response.event_report);
			*/
			location.reload();
		},
		
		stop_work:function(response){
			if(response.status=='error')
				return;
			
			//$(config.ui.start_btn).remove();
			$(config.ui.modal_report).modal('hide');
			$(config.ui.buttons).replaceWith(response.event_buttons);
			$(config.ui.event_report).replaceWith(response.event_report);
		},

		set_image:function(){
			$(config.events.safety_meeting_form+' '+config.ui.create_signature).trigger('click');
			$(config.events.safety_meeting_form).trigger('submit');
		},

		set_client_image:function(){
			$(config.events.report_form+' '+config.ui.create_signature).trigger('click');
			$(config.events.report_form).trigger('submit');
		},

		calc_route:function() {
			var pinIcon = new google.maps.MarkerImage(
			    "https://chart.apis.google.com/chart?chst=d_map_pin_icon&chld=home|FFFFFF",
			    null, 
			    null, 
			    null, 
			    new google.maps.Size(25, 37)
			);
			
			new google.maps.Marker({
				position: new google.maps.LatLng(myPosition),
				map: map,
				icon: pinIcon
			});

			_private.codeAddress(window.client_address, function(results){
				var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()),
                    map: map,
                });
			});


			var callback = function(results){
																	
				var request = {
				    origin: new google.maps.LatLng(myPosition), 
				    destination: new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()),
				    waypoints: [{location: new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()), stopover:true}],
				    optimizeWaypoints: true,
				    travelMode: google.maps.DirectionsTravelMode.DRIVING,
				    
				};	
				
				directionsService.route(request, function(response, status) {
					
				  	if (status == google.maps.DirectionsStatus.OK) {
				    	directionsDisplay.setDirections(response);
				    	var route = response.routes[0];
				    	//var summaryPanel = document.getElementById("directions_panel");
				    	//summaryPanel.innerHTML = "";
				    	//computeTotalDistance(response);
				  	} else {
				    	alert("directions response "+status);
				  	}
				});
			}

			_private.codeAddress(window.client_address, callback);
		}
	}

	

	public.init();
	return public;
}();