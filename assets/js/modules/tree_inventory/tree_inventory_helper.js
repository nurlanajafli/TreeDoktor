var TreeInventoryHelper = function(){
	var config = {
		
		ui:{
			lead_id:'[name="ti_lead_id"]',
			map_id:'inventory_map',
			map:'#inventory_map',

			map_image_screen:'#mapper-image',
			map_image_screen_id:'mapper-image',
		},

		events:{
			lead_select:'#lead-select',
		},
		
		route:{},
		
		templates:{
			
		},
		views:{
			
		},
	};
	
	var work_types_assoc = {};
	var _private = {
		
		
		addSearchInput:function(controlDiv){
			
			var controlUI = document.createElement('input');
			controlUI.className = 'form-control';
			controlUI.style.padding = "18px 3px";
			controlUI.style.margin = "10px 10px";
			controlUI.setAttribute('type', 'text');
			controlUI.setAttribute('placeholder', 'Search Box');
			controlUI.setAttribute('data-callback', 'TreeInventoryMap.search_address');
			controlUI.setAttribute('data-autocompleate', 'true');
			controlDiv.appendChild(controlUI);
			controlUI.addEventListener('click', function() {
				Common.init_autocompleate();
			});
		},

		addMarkerButton:function(controlDiv){
			// Set CSS for the control border.
			var controlUI = document.createElement('button');
			controlUI.className = 'btn btn-danger m-top-10';
			controlUI.style.fontSize = '18px';
			controlDiv.appendChild(controlUI);

			// Set CSS for the control interior.
			var controlText = document.createElement('i');
			controlText.className = 'fa fa-plus';
			
			controlText.innerHTML = '&nbsp;Add marker';
			controlUI.appendChild(controlText);
			
			// Setup the click event listeners: simply set the map to Chicago.
			controlUI.addEventListener('click', function() {
				position = map.getCenter();
				var lat = position.lat(); 
				var lng = position.lng();
				
				_private.placeMarker(new google.maps.LatLng(lat, lng), map);
				setTimeout(function(){
					_private.render_infowindow(markers_object[lat+'_'+lng]);
				}, 200);
			});
		},

		addUploadButton:function(controlDiv){
			// Set CSS for the control border.
			var controlUI = document.createElement('button');
			controlUI.className = 'btn btn-danger m-10 ';
			controlUI.style.fontSize = '18px';
			controlDiv.appendChild(controlUI);

			// Set CSS for the control interior.
			var controlText = document.createElement('i');
			controlText.className = 'fa fa-plus';
			
			controlText.innerHTML = '&nbsp;Upload Map';
			controlUI.appendChild(controlText);

			
			controlUI.addEventListener('click', function() {
		    	$(config.ui.map_image).trigger('click');
			});

		},
		init_work_types: function(){

			if(window.work_types === undefined || window.work_types.length==0)
				return;

			window.work_types.forEach(function(value, key){
				work_types_assoc[value.ip_id] = value;
			});
		},

		url_router: function(id){
			url_segments = document.location.pathname.split('/').filter(function (el) {  return el != ''; });
			if(url_segments.length==3)
				url_segments.push(id);
			else
				url_segments[3] = id;

			if(url_segments[3]==0)
				url_segments.splice(3, 1);

			url_segments_str = url_segments.join('/');
			
			window.history.pushState(id, '', '/'+url_segments_str);
		},

		menu_width: function(){
			var window_width = $(window).width();
			var width_array = {
				"xs":[0, window_width/100*90],
				"sm":[768, window_width/2],
				"md":[992, window_width/100*40],
				"lg":[1200, window_width/4],
			};
			grid = 'xs';
			if(window_width>=width_array['sm'][0])
				grid = 'sm';
			if(window_width>=width_array['md'][0])
				grid = 'md';
			if(window_width>=width_array['lg'][0])
				grid = 'lg';

			var width = width_array[grid][1];
			
			return width;
		},

		initMenuEvents: function() {
		    $('.icon-menu').click(function() { 
		    	var width = _private.menu_width();
		    	$('.menu').animate({right: '0px'}, 200, function(){
		    		$('.inventory-list-container').width(width);
					$('.icon-close').css('right', width-10).removeClass('hidden');
		    	}); 
		        // $('body').animate({ right: width+'px'}, 200);
		    });

			$('.icon-close').click(function(){
				var width = _private.menu_width();
		        $('.menu').animate({right: '-'+width+'px'}, 200);
		        $('.inventory-list-container').width(0);
				$(this).addClass('hidden')
		    	// $('body').animate({right: '0px'}, 200);
		    });
		},

		initMenu:function(){
			var width = _private.menu_width();
			$('.menu').width(width);
			if($(window).width()>1170){
				$('.menu').css({right: '0px'});
				$('.inventory-list-container').width(width);
			}
			else{
				$('.menu').css({right:"-"+width+"px"});
				$('.inventory-list-container').width(0);
			}
		}
	}
	var MapController = {};
	var public = {

		init:function(){
			
			$(window).resize(function(){
				_private.initMenu();
			});

			_private.init_work_types();
			_private.initMenuEvents();
			
			public.events();

			url_segments = document.location.pathname.split('/').filter(function (el) {  return el != ''; });
			if(url_segments.length==3){
				$(config.events.lead_select).trigger('change');
			}
			
			$(document).ready(function(){
				_private.initMenu();
				
				/*
				if(window.ti_map_type=='map'){
					MapController = TreeInventoryMap;
				}
				else if(window.ti_map_type=='image'){
					MapController = TreeInventoryImage;
				}
				
				var Controller = MapController();
				console.log(Controller)
				*/
			});
			
		},

		set_tree_inventory_list:function(response){
			window.ti_map_type = response.response.ti_map_type;
			if(response.response.ti_map_type=='map'){

				$(config.ui.map).show();
				$(config.ui.map_image_screen).hide();
				
				window.tree_inventory_list = JSON.stringify(response.response.tree_inventory);
				TreeInventoryMap.init();

				//TreeInventoryMap.set_tree_inventory_list(response.response.tree_inventory);
			}
			else{
				$(config.ui.map).hide();
				$(config.ui.map_image_screen).show();
				console.log(TreeInventoryImage.init_map)
				TreeInventoryImage.init_map(response);
			}
		},

		events: function(){
			$(document).delegate(config.events.lead_select, "change", function(){
				id = $(this).val();
				if(id === null)
					return false;

				$(config.ui.lead_id).val(id);
				var url = $(this).find("option:selected").data('url');

				//Common.request.get(url, public.set_tree_inventory_list);
				
				url_segments = document.location.pathname.split('/').filter(function (el) {  return el != ''; });
				if(url_segments.length==3)
					url_segments.push(id);
				else
					url_segments[3] = id;

				if(url_segments[3]==0)
					url_segments.splice(3, 1);

				url_segments_str = url_segments.join('/');
				
				document.location.href = '/'+url_segments_str;
				//window.history.pushState(id, '', '/'+url_segments_str);
			});

			/*
			$(document).delegate(config.events.lead_select, "change", function(){
				id = $(this).val();
				$(config.ui.lead_id).val(id);
				var url = $(this).find("option:selected").data('url');

				callback = function(response){
					window.ti_map_type = response.response.ti_map_type;
					console.log(window.ti_map_type);
					
					window.ti_map_type = response.response.ti_map_type;
					if(response.response.ti_map_type=='map'){
						console.log(MapController.map_global);
						
						if(MapController.map_global!=undefined && Object.keys(MapController.map_global).length==0){
							console.log(MapController.map_global);
							MapController.set_tree_inventory_list(response);
							return;
						}

						MapController = TreeInventoryMap;
						Controller = MapController();
						console.log(Controller);
						return;
					}
					else if(response.response.ti_map_type=='image'){
						MapController = TreeInventoryImage;
					}
					
					Controller = MapController();
					//console.log(Controller)
					
				}

				Common.request.get(url, callback);
				_private.url_router(id);
			});
			*/
		},

		init_work_types:function(){
			return work_types_assoc;
		},

		get_color:function(ti_tree_priority){
			return window.priority_color[ti_tree_priority];
		},

		helpers:{
			priority_labels:function(priority){
				
				var labels = {'low':'Low', 'medium':'Mid', 'high':'High'};
				if(labels[priority]!=undefined)
					return labels[priority];

				return '';
			},

			work_types_string: function(types, short){
				
				if(types.length==0)
					return '';

				var result_array = [];
				
				$.each(types, function(key, value){
					
					if(work_types_assoc[value.tiwt_work_type_id] != undefined){
						name = '';
						name = work_types_assoc[value.tiwt_work_type_id]['ip_name_short'];
						if(short==false)
							name=name+':'+work_types_assoc[value.tiwt_work_type_id]['ip_name'];

						result_array.push(name);
					}
				});
				
				return result_array.join(', ');
			},
			topFirstChar: function(str){
				return str.charAt(0).toUpperCase() + str.substr(1);
			},

			showString: function(str){
				if(typeof str == "undefined" || str==null)
					return 'none';

				return str;
			}
		},

		get_totals:function(tree_list){
			var totals = {
				total_cost:0.00, total_stump:0.00,
				tax_cost:0.00, tax_stump:0.00,
				grand_total_cost:0.00, grand_total_stump:0.00,
			};

			var totalsArray = {
				total_cost:[], total_stump:[]
			};

			tree_list.forEach(function(value, key){
				totalsArray.total_cost.push(parseFloat(value['ti_cost']));
				totalsArray.total_stump.push(parseFloat(value['ti_stump_cost']));
			});

			totals.total_cost = totalsArray.total_cost.reduce(function(acc, val) { return acc + val; }, 0);
			totals.total_stump = totalsArray.total_stump.reduce(function(acc, val) { return acc + val; }, 0);

			totals.tax_cost = totals.total_cost*window.tax_rate - totals.total_cost;
			totals.tax_stump = totals.total_stump*window.tax_rate - totals.total_stump;

			totals.grand_total_cost = totals.total_cost*window.tax_rate;
			totals.grand_total_stump = totals.total_stump*window.tax_rate;

			$.each(totals, function(key, value){ totals[key] = value.toLocaleString('en-US', {style: 'currency', currency: 'USD'}); });
			
			return totals;
		},
		reload_page:function(response){
			if(response.status!='ok')
				return;

			document.location.reload();
		},

		copy_tree_inventory_success:function(response){
			document.location.href = '/tree_inventory/map/'+response.client_id+'/'+response.lead_id;
		}
	}

	public.init();
	return public;
}();
