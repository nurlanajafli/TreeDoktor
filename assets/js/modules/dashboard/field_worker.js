var FieldWorker = function(){
	var config = {
		autoscroll_enabled:false,
		ui:{
			date_active_class_name:'btn-success',
			date_default_class_name: 'btn-default'
		},
		events:{
			change_date: '.change-dashboard-date'
		},
		route:{
			field_worker_dashboard:'/dashboard/ajax_feild_worker_data'
		},
		templates:{
			dashboard:{
				dashboard_date: '#dashboard-date',
				team_equipments_tools:'#team-equipments-tools',
				jobs:'#jobs',
				map:'#map',
				global_js:'#global-js',
				dates_pagination:'#dates-pagination',
			}
		}
	}

	var _private = {
		init:function(){
		
		},
		events:function(){
			$(document).delegate(config.events.change_date, 'click', _private.change_dashboard_date);
		},

		change_dashboard_date:function(){
			if($(this).hasClass(config.ui.date_active_class_name))
				return false;
			var $this = $(this);
			callback = function(response){
				if(response.type=='error')
					alert(response.data);

				$.each(response.data, function(key, tmp){
					
					if(config.templates.dashboard[key]==undefined)
						return;

					if($(config.templates.dashboard[key])==undefined)
						return;

					$(config.templates.dashboard[key]).html(tmp);
				});
				public.init_map();
				//_private.change_date_class($this);
			};

			var new_date = $(this).data('date');
			$.post(baseUrl+config.route.field_worker_dashboard, {date:new_date}, callback, "json");
			
			return;
		},

		change_date_class:function($this){
			$(config.events.change_date).removeClass(config.ui.date_active_class_name);
			$this.removeClass(config.ui.date_default_class_name).addClass(config.ui.date_active_class_name);
			$(config.events.change_date).each(function(){
				if($(this).hasClass(config.ui.date_active_class_name) == false && $(this).hasClass(config.ui.date_default_class_name)==false)
					$(this).addClass(config.ui.date_default_class_name);
			});
		}
	}
	
	var directionDisplay;
  	var directionsService = new google.maps.DirectionsService();
  	var map;

	var public = {

		init:function(){
			$(document).ready(function(){
			  	_private.events();
			  	public.init_map();
			});
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
		    }
		    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		    directionsDisplay.setMap(map);

		    public.calc_route();
		},
	

		calc_route:function() {
			
			var pinIcon = new google.maps.MarkerImage(
			    "https://chart.apis.google.com/chart?chst=d_map_pin_icon&chld=home|FFFFFF",
			    null, /* size is determined at runtime */
			    null, /* origin is 0,0 */
			    null, /* anchor is bottom center of the scaled image */
			    new google.maps.Size(25, 37)
			);
			var pinLabel = '';
			var markers = {};
			$.each(window.map_waypoints_ext, function(key, point){
				pinLabel = (key+1).toString();
				if(markers[point.lat+'_'+point.lng]!=undefined)
				{
					old_text = markers[point.lat+'_'+point.lng].label.text;
					pinLabel = old_text+', '+pinLabel;
					markers[point.lat+'_'+point.lng].visible = false;
				}

				markers[point.lat+'_'+point.lng] = new google.maps.Marker({
					position: new google.maps.LatLng(point.lat, point.lng),
					map: map,
					label: {color: '#000', fontSize: '12px', fontWeight: '600', text: pinLabel}
				});
			});

			new google.maps.Marker({
				position: new google.maps.LatLng(window.map_destination[0],window.map_destination[1]),
				map: map,
				icon: pinIcon
			});

			var request = {
			    origin: new google.maps.LatLng(window.map_origin[0], window.map_origin[1]), 
			    destination: new google.maps.LatLng(window.map_destination[0],window.map_destination[1]), 
			    waypoints: window.map_waypoints,
			    optimizeWaypoints: true,
			    travelMode: google.maps.DirectionsTravelMode.DRIVING
			};
			directionsService.route(request, function(response, status) {
			  if (status == google.maps.DirectionsStatus.OK) {
			    directionsDisplay.setDirections(response);
			    var route = response.routes[0];
			    var summaryPanel = document.getElementById("directions_panel");
			    summaryPanel.innerHTML = "";
			    
			    //computeTotalDistance(response);
			  } else {
			    alert("directions response "+status);
			  }
			});
		}
	}

	

	public.init();
	return public;
}();

/*
     function computeTotalDistance(result) {
      var totalDist = 0;
      var totalTime = 0;
      var myroute = result.routes[0];
      for (i = 0; i < myroute.legs.length; i++) {
        totalDist += myroute.legs[i].distance.value;
        totalTime += myroute.legs[i].duration.value;      
      }
      totalDist = totalDist / 1000.
      document.getElementById("total").innerHTML = "Dis is: "+ totalDist + " km<br>Time is: " + (totalTime / 60).toFixed(2) + " mins";
      }
	*/