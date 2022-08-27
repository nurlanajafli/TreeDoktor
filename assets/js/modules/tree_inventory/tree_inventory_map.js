var TreeInventoryMap = function(){
	var config = {
		maptype_driver:'map',
		ui:{
			container: '.inventory-map-container',
			map_id:'inventory_map',
			map:'#inventory_map',
			map_image_screen:'#mapper-image',
			tree_type:'#tree_type',
			lead_select:'#lead-select',
			lead_id:'[name="ti_lead_id"]',
			map_form:'#upload-map-image',
			map_image:'#map-image',
			form_lat:'[name="ti_lat"]',
			form_lng:'[name="ti_lng"]',
			create_estimate:'#create-estimate',
			create_invoice:'#create-invoice',
			create_wo:'#create-wo',
			scheme_select:'#scheme-select',
			copy_project:'.copy-project',
			tree_project:'.tree-project',
			tree_list:'.tree-list',
			edit_project:'.edit-project',
			back_to_projects:'.back-to-projects',
			project_edit: '.project-edit',
			project_create: '.create-project',
			project_delete: '.project-delete',
			client_id: '[name="ti_client_id"]',
			client_lat: '.client_lat',
			client_lon: '.client_lon',
			client_address: '.client_address',
			client_city: '.client_city',
			client_state: '.client_state',
			client_zip: '.client_zip',
			client_country: '.client_country',
			tree_list_project: '#tree-list-project',
			create_select: '.create-select',
			delete_shape: '.delete-shape',
			new_project: '#new_project',
			save_lead: '#save_lead',
			leads_select: '#leads_select',
			create_lead_button: '#createLeadButton',
			create_lead_button_modal: '#createLeadButtonModal',
			ti_tree_number: 'input[name="ti_tree_number"]',
			select2:[
				{
					selector:'#work_types',
					options:{width: '100%'},
					values:false,
					onchange:function(){
			    		$('.work_types .select2-choices').closest('.form-group').height($('.work_types .select2-choices').height());
			    	}
				},
				{
					selector:'#ti_tree_type',
					options:{width: '100%'},
					onchange:false,
					values:false,
				},
				/*{
					selector:'#ti_lead_id',
					options:{width: '100%'},
					onchange:false,
					values:false,
				},*/
				
			],
			create_lead:'#new_lead',
			select_markers_button: '#selectMarkersButton',
			add_marker_button: '#addMarkerButton',
			create_button: '#createButton',
			google_map_background: '#inventory_map [role="region"] div:first>div:last',
			history_tree_block:'#history_tree_block'
		},

		events:{
			save_tree_details:'#save-tree-details',
			close_infowindow:'.close-infowindow',
			edit_tree:'.edit-tree-map',
			tree_inventory_modal:'#inventory-list-modal',
			delete_form:'.delete-map-item',
			delete_formClass:'delete-map-item',
			delete_button:'.delete-map-item button',
			create_estimate:'#create-estimate',
			create_invoice:'#create-invoice',
			create_wo:'#create-wo',
			scheme_select:'#scheme-select',
			copy_project:'.copy-project',
			projects: '#tree-list-project li',
			back_to_projects:'.back-to-projects',
			project_edit: '.project-edit',
			project_create: '.create-project',
			project_delete: '.project-delete',
			create_select: '.create-select',
			new_project: '#new_project',
			save_lead: '#save_lead',
			leads_select: '#leads_select',
			create_lead_button_modal: '#createLeadButtonModal',
			open_tree_history:'#eye_tree_info',
			toggleChevronTree:'a.toggleChevron',
			treeIdChecker:'input.treeIdChecker'
		},
		
		route:{},
		
		templates:{
			infowindowform:'#infowindowform-tmp',
			tree_list:"#tree-list-tmp",
			tree_list_emp:"#tree-list-tmp-emp",
			tree_list_table: "#tree-list-table-tmp",
			tree_list_table_totals:"#tree-list-table-totals-tmp",
			map_screen:"#map-screen-tmp",
			map_screen_form:"#map-screen-form-tmp",
			row_history_template:".row_history_template"
		},
		views:{
			infowindowform:'#infowindowform',
			tree_list:"#tree-list",
			tree_list_table: "#tree-list-table",
			tree_list_table_totals:"#tree-list-table-totals",
			map_screen:"#map-screen",
			map_screen_form:"#map-screen-form",
			map_screen_form2:"#map-screen-form2"
		},
	};
	var map = {};
	var markers_object = {};
	var markers_object_db = {};
	var infowindow;
	var directionDisplay;
  	var directionsService = new google.maps.DirectionsService();
  	var geocoder;
	geocoder = new google.maps.Geocoder();

	let drawingManager;
	let selectedShape;
	let colors = ['#1E90FF', '#FF1493', '#32CD32', '#FF8C00', '#4B0082'];
	let selectedColor;
	let colorButtons = {};
	let shapes = [];
	let checked_Trees = [];
	let unChecked_Trees = [];
	let default_marker = [];
	let overlay = null;
	let latlngbounds;
	let start_bounds;
	let srcImage =
		"https://developers.google.com/maps/documentation/" +
		"javascript/examples/full/images/talkeetna.png";
	srcImage = "/uploads/clients_files/38894/estimates/48315-E/55212/estimate_no_48315-E_5.png";

	
	var work_types_assoc = {};
	
	var _private = {
		init: function () {

			work_types_assoc = TreeInventoryHelper.init_work_types();
			_private.init_map();
		},

		init_map: function () {
			directionsDisplay = new google.maps.DirectionsRenderer({
				suppressMarkers: true
			});

			let centermap = new google.maps.LatLng($(config.ui.map).data('origin_lat'), $(config.ui.map).data('origin_lon'));

			//set center by scheme
			// let scheme_lat = $(config.ui.map).data('scheme_lat');
			// let scheme_lng = $(config.ui.map).data('scheme_lng');
			// if(scheme_lat && scheme_lng) {
			// 	console.log(scheme_lat);
			// 	console.log(scheme_lng);
			// 	centermap = new google.maps.LatLng(scheme_lat, scheme_lng);
			// 	// map.setCenter(position);
			// 	// _private.setClientAddressMarker(position);
			// }

			var myOptions = {
				zoom: 19,
				center: centermap,
				mapTypeId: google.maps.MapTypeId.HYBRID,
				//mapTypeControlOpts: {mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.HYBRID]},
				//labels:false
				//mapTypeId: 'satellite',
				gestureHandling: 'greedy',
				streetViewControl: false,
				rotateControl: false,
				mapTypeControl: false,
				fullscreenControlOptions: {
					position: google.maps.ControlPosition.LEFT_TOP,
				},
				tilt:0
			};

			map = new google.maps.Map(document.getElementById(config.ui.map_id), myOptions);

			map.setOptions({showRoadLabels: false});

			directionsDisplay.setMap(map);

			_private.map_events();

			infowindow = new google.maps.InfoWindow({
				maxWidth: 680,
				maxHeight: 900,
				buttons: {close: {visible: false}}
			});

			google.maps.event.addDomListener(window, 'load', _private.initDrawingManager());
			google.maps.event.addListener(infowindow, 'closeclick', function (event) {
				console.log("---close event----");
				var key = this.position.lat() + '_' + this.position.lng();
				/*
                if(typeof markers_object_db[key] == 'undefined')
                {
                    if(typeof markers_object[key] != 'undefined' && typeof markers_object[key]['marker'] != 'undefined'){
                        markers_object[key]['marker'].setMap(null);
                        delete markers_object[key];
                    }
                }

                if(_private.change_position_key){
                    var latlng = new google.maps.LatLng(markers_object[_private.change_position_key].old_lat, markers_object[_private.change_position_key].old_lng);
                    markers_object[_private.change_position_key]['marker'].setPosition(latlng);
                    markers_object[_private.change_position_key]['form'][0]['ti_lat'] = markers_object[_private.change_position_key].old_lat;
                    markers_object[_private.change_position_key]['form'][0]['ti_lng'] = markers_object[_private.change_position_key].old_lng;

                    delete markers_object[_private.change_position_key].old_lat;
                    delete markers_object[_private.change_position_key].old_lng;

                    markers_object[_private.change_position_key]['marker'].setMap(map);
                    _private.change_position_key = '';
                }*/
				_private.close_infowindow(false, key);
			});

			var centerControlDiv = document.createElement('div');
			var centerControlDiv2 = document.createElement('div');
			var centerControlDiv3 = document.createElement('div');
			var centerControlDiv4 = document.createElement('div');
			var centerControlDiv5 = document.createElement('div');
			var centerControlDiv6 = document.createElement('div');

			// _private.addSearchInput(centerControlDiv5);
			centerControlDiv4.setAttribute('id', 'selectMarkersButton');
			_private.addSelectMarkersButton(centerControlDiv4);
			centerControlDiv.setAttribute('id', 'addMarkerButton');
			_private.addMarkerButton(centerControlDiv);
			// centerControlDiv2.setAttribute('id', 'createButton');
			// _private.addCreateButton(centerControlDiv2);
			//_private.addUploadButton(centerControlDiv3);
			// centerControlDiv5.setAttribute('id', 'createNewLead');
			// _private.addCreateNewLead(centerControlDiv5);
			// centerControlDiv6.setAttribute('id', 'addSelectLead');
			// _private.addSelectLead(centerControlDiv6);

			centerControlDiv6.setAttribute('id', 'createLeadButton');
			_private.addCreateLeadButton(centerControlDiv6);

			centerControlDiv.index = 1;
			map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv);
			map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv4);
			map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv5);

			map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv2);
			map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv6);
			// map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv3);
			_private.selectColor(colors[0]);

			// bounds = map.getBounds();
			// console.log(bounds);
			// const overlay = new USGSOverlay(bounds, srcImage);
			// overlay.setMap(map);
		},

		map_events: function () {
			map.addListener('bounds_changed', function() {
				// if(overlay === null) {
				// 	map.setZoom(10);
				// 	console.log(map.getBounds());
				// 	overlay = new USGSOverlay(map.getBounds(), srcImage);
				// 	overlay.setMap(map);
				// 	start_bounds = map.getBounds();
				// 	map.set('restriction', {latLngBounds: start_bounds});
				// 	console.log(overlay.getProjection());
				// }
				// // $(config.ui.google_map_background).css('visibility', 'hidden');
			});
			map.addListener('dblclick', function (e) {
				if($('.edit-project .data-id').is(':visible') === false) {
					errorMessage('Select a project');
					return false;
				}
				_private.placeMarker(e.latLng, map);
				setTimeout(function () {
					_private.render_infowindow(markers_object[e.latLng.lat() + '_' + e.latLng.lng()]);
				}, 200);
			});

			var tree_list = [];
			if (window.tree_inventory_list != undefined) {
				var tree_list = window.tree_inventory_list;
				// _private.set_tree_inventory_list(tree_list);
			}


			// _private.codeAddress(window.home_address, function (results) {
			// 	position = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
				// if (window.tree_inventory_list == undefined || JSON.parse(window.tree_inventory_list).length == 0) {
				// 	map.setCenter(position);
				// }
			// 	_private.setClientAddressMarker(position);
			// });

			_private.set_default_center();

		},

		update_inventory_list: function (tree_list, marker) {
			_private.calculate_tree_lists(tree_list, _private.render_tree_lists);
			if (marker != undefined) {
				position = new google.maps.LatLng(marker.ti_lat, marker.ti_lng);
				_private.placeMarker(position, map, true);
			}
		},

		/* for delete if all callbacks ok */
		set_tree_inventory_list: function (tree_list) {
			markers_object = {};
			_private.calculate_tree_lists(tree_list, _private.render_tree_lists);
			_private.set_tree_inventory_map(tree_list);
		},

		set_tree_inventory_map: function (tree_list) {
			$.each(tree_list, function (key, marker) {
				position = new google.maps.LatLng(marker.ti_lat, marker.ti_lng);
				_private.placeMarker(position, map);
			});
		},

		calculate_tree_lists: function (tree_list, callback) {
			markers_list = [];
			markers_object_db = {};
			//var totals = {total_cost:0.00, total_stump:0.00};

			$.each(tree_list, function (key, marker) {
				marker['priority_color'] = TreeInventoryHelper.get_color(marker['ti_tree_priority'])

				//totals.total_cost += parseFloat(marker['ti_cost']);
				//totals.total_stump += parseFloat(marker['ti_stump_cost']);

				marker['ti_cost'] = parseFloat(marker['ti_cost']);
				marker['ti_stump_cost'] = parseFloat(marker['ti_stump_cost']);

				markers_object_db[marker.ti_lat + '_' + marker.ti_lng] = marker;
				markers_list.push(marker);

				_private.set_marker_form_object({lat: marker.ti_lat, lng: marker.ti_lng}, marker.ti_tree_number);
			});

			//totals.total_cost = totals.total_cost.toLocaleString('en-US', {style: 'currency', currency: 'USD'});
			//totals.total_stump = totals.total_stump.toLocaleString('en-US', {style: 'currency', currency: 'USD'});
			var totals = TreeInventoryHelper.get_totals(tree_list);

			if (callback != undefined && callback != false)
				callback(markers_list, totals);
		},

		render_tree_lists: function (markers_list, totals) {
			var renderView = {
				template_id: config.templates.tree_list,
				empty_template_id: config.templates.tree_list_emp,
				view_container_id: config.views.tree_list,
				data: markers_list,
				helpers: TreeInventoryHelper.helpers
			};
			Common.renderView(renderView);

			var renderViewTable = {
				template_id: config.templates.tree_list_table,
				view_container_id: config.views.tree_list_table,
				data: markers_list,
				helpers: TreeInventoryHelper.helpers
			};
			Common.renderView(renderViewTable);

			var renderViewTotals = {
				template_id: config.templates.tree_list_table_totals,
				view_container_id: config.views.tree_list_table_totals,
				data: [totals],
				helpers: TreeInventoryHelper.helpers
			};
			Common.renderView(renderViewTotals);
		},

		placeMarker: function (position, map, update) {

			if (infowindow != undefined && infowindow.getMap()){
				/*
				if (marker.form[0] != undefined && marker.form[0].ti_lat != undefined)
					$(config.ui.form_lat).val();
				if (marker.form[0] != undefined && marker.form[0].ti_lng != undefined)
					$(config.ui.form_lng).val();
					*/
				_private.close_infowindow(false, $(config.ui.form_lat).val()+'_'+$(config.ui.form_lng).val());
			}


			var lat = position.lat();
			var lng = position.lng();
			var key = lat + '_' + lng;
			if (markers_object[key] != undefined && markers_object[key]['marker'] != undefined && (update == undefined || update == false)) {
				return false;
			}
			var color = window.priority_color['medium'];
			var pinLabel = (Object.keys(markers_object).length + 1).toString();

			if (markers_object_db[key] != undefined) {
				pinLabel = markers_object_db[key]['ti_tree_number'];
				color = markers_object_db[key]['priority_color'];
			} else {
				let newPinNumber = _private.getNewPinNumber();
				if(newPinNumber > 0)
					pinLabel = Number.parseInt(newPinNumber) + 1;
			}
			if (markers_object[key] != undefined && markers_object[key]['marker'] != undefined) {
				markers_object[key]['marker'].setMap(null);
			}

			/*
		   	if(_private.change_position_key.length > 0 && markers_object[_private.change_position_key] !=undefined && markers_object[_private.change_position_key]['marker']!=undefined){
		   		console.log('change_position', _private.change_position_key);
				console.log(markers_object);
				console.log(Object.keys(markers_object).length);
		   		markers_object[_private.change_position_key]['marker'].setMap(null);
		   		_private.change_position_key = '';
		   	}
		   	*/

			markers_object[key] = {};
			var image = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent('<svg version="1.1" width="25" height="40" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 45.137 68.625" xml:space="preserve"><path fill="' + color + '" d="M22.569,0C10.103,0,0,10.104,0,22.568c0,7.149,3.329,13.521,8.518,17.654 c0.154,0.127,0.318,0.258,0.499,0.392c0.028,0.021,0.054,0.042,0.082,0.063c0.006,0.004,0.01,0.007,0.015,0.011 c8.681,6.294,13.453,27.938,13.453,27.938s4.03-20.621,11.407-26.585c6.679-3.921,11.163-11.17,11.163-19.472 C45.137,10.104,35.032,0,22.569,0z M22.569,38.129c-8.382,0-15.176-6.795-15.176-15.176c0-8.382,6.794-15.175,15.176-15.175 c8.381,0,15.174,6.793,15.174,15.175C37.743,31.334,30.95,38.129,22.569,38.129z"/><circle fill="#FFFFFF" cx="22.48" cy="23.043" r="16.27"/><text transform="translate(22.5 28)" fill="' + color + '" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="18" text-anchor="middle">' +  pinLabel + '</text></svg>')));

			markers_object[key]['marker'] = new google.maps.Marker({
				position: position,
				map: map,
				draggable: true,
				fillColor: '#ccc',
				icon: image
			});

			markers_object[key]['marker'].set("ti_key", key);

			if (markers_object_db[key] == undefined)
				markers_object_db[key] = markers_object[key]['form'];

			if (markers_object[key]['form'] == undefined)
				_private.set_marker_form_object({lat: lat, lng: lng}, pinLabel);


			map.panTo(position);
			_private.marker_events(markers_object[key]);
		},

		getNewPinNumber: function(){
			let pinNumber = 0;
			if(Object.keys(markers_object).length){
				$.each(markers_object, function (key, val) {
					if(val &&  val.form.length === 1){
						if(Number.parseInt(val.form[0].ti_tree_number) !== NaN && Number.parseInt(val.form[0].ti_tree_number) > pinNumber)
							pinNumber = val.form[0].ti_tree_number;
					}
				});
			}
			return pinNumber;
		},

		setClientAddressMarker: function (position) {
			var home = 'data:image/svg+xml;base64,' + btoa('<svg version="1.1" width="35px" height="35px" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512.533 512.533" style="enable-background:new 0 0 512.533 512.533;" xml:space="preserve"><path style="fill:#F3705B;" d="M406.6,62.4c-83.2-83.2-217.6-83.2-299.733,0c-83.2,83.2-83.2,216.533,0,299.733l149.333,150.4L405.533,363.2C488.733,280,488.733,145.6,406.6,62.4z"/><path style="fill:#F3F3F3;" d="M256.2,70.933c-77.867,0-141.867,62.933-141.867,141.867c0,77.867,62.933,141.867,141.867,141.867c77.867,0,141.867-62.933,141.867-141.867S334.066,70.933,256.2,70.933z"/><polygon style="fill:#FFD15D;" points="256.2,112.533 176.2,191.467 176.2,305.6 336.2,305.6 336.2,191.467 "/><g><rect x="229.533" y="241.6" style="fill:#435B6C;" width="54.4" height="64"/><path style="fill:#435B6C;" d="M356.466,195.733L264.733,104c-4.267-4.267-11.733-4.267-17.067,0l-91.733,91.733c-4.267,4.267-4.267,11.733,0,17.067c4.267,4.267,11.733,4.267,17.067,0l83.2-84.267l83.2,83.2c2.133,2.133,5.333,3.2,8.533,3.2c3.2,0,6.4-1.067,8.533-3.2C360.733,207.467,360.733,200,356.466,195.733z"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>');
			let marker = new google.maps.Marker({
				position: position,
				map: map,
				draggable: false,
				fillColor: '#ccc',
				icon: home
			});
			default_marker.push(marker);
		},

		change_position_key: '',

		marker_events: function (marker) {
			marker['marker'].addListener('click', function () {
				if(markers_object[this.ti_key] && markers_object[this.ti_key]['form'] && markers_object[this.ti_key]['form'][0] && markers_object[this.ti_key]['form'][0].ti_id === undefined)
					return;
				_private.render_infowindow(marker);
			});

			marker['marker'].addListener('dragend', function (e) {
				// if (markers_object[this.ti_key]['form'][0].ti_id != undefined && !_private.change_position_key) {

					//_private.change_position_key = this.ti_key;
					var new_key = e.latLng.lat() + '_' + e.latLng.lng();

					console.log(markers_object[this.ti_key]);
					console.log('change position from ' + this.ti_key + ' to:' + new_key);

					markers_object = Common.renameKey(markers_object, this.ti_key, new_key);

					console.log(markers_object[new_key]);
					if(typeof markers_object[new_key].old_lat == 'undefined' && typeof markers_object[new_key].old_lng == 'undefined') {
						markers_object[new_key]['old_lat'] = markers_object[new_key]['form'][0].ti_lat;
						markers_object[new_key]['old_lng'] = markers_object[new_key]['form'][0].ti_lng;
					}
					markers_object[new_key]['form'][0]['ti_lat'] = e.latLng.lat();
					markers_object[new_key]['form'][0]['ti_lng'] = e.latLng.lng();


					markers_object[new_key].marker.ti_key = new_key;

					console.log('++++++++++');
					console.log(markers_object);
					console.log(markers_object[new_key]);
					console.log(new_key);
					console.log('++++++++++');
				// }

				_private.render_infowindow(marker, true);
			});
		},

		render_infowindow: function (marker, dragend = false) {
			console.log(markers_object);
			console.log(marker['marker'].ti_key);
			// if(markers_object[marker['marker'].ti_key]!=undefined)
			// 	return;
			if (infowindow != undefined && infowindow.getMap()){
				_private.close_infowindow(false, $(config.ui.form_lat).val()+'_'+$(config.ui.form_lng).val(), dragend);
			}

			marker.form[0].ti_tis_id = $('.edit-project .data-id').data('id');
			console.log('changed_lat_form:' + marker.form[0].ti_lat + ', ' + marker.form[0].ti_lng);
			infowindow.close();

			if (marker.form[0] != undefined && marker.form[0].ti_lat != undefined)
				$(config.ui.form_lat).val(marker.form[0].ti_lat);
			if (marker.form[0] != undefined && marker.form[0].ti_lng != undefined)
				$(config.ui.form_lng).val(marker.form[0].ti_lng);

			if (infowindow.getMap())
				return false;


			var renderView = {
				template_id: config.templates.infowindowform,
				view_container_id: config.views.infowindowform,
				data: marker['form'],
				helpers: TreeInventoryHelper.helpers
			};

			Common.renderView(renderView);
			form = $(config.views.infowindowform).html();
			infowindow.setContent(form);

			setTimeout(function () {
				config.ui.select2[0].values = marker['form'][0]['work_types'];
				$(config.ui.lead_id).val($(config.ui.lead_select).val());

				Common.mask_currency();
				Common.init_select2(config.ui.select2);
			}, 10);


			var plus_pos=0.35*(Number(map.getBounds().getNorthEast().lat())-Number(map.getBounds().getSouthWest().lat()));
			var lat=Number($(config.ui.form_lat).val())+plus_pos;


			infowindow.open(map, marker['marker']);
			map.panTo(new google.maps.LatLng(lat,$(config.ui.form_lng).val()));

			infowindow.addListener('onclose', function (e) {
				alert("ok");
			});

		},

		close_infowindow: function (e, close_key, dragend = false) {
			key = $(this).data('marker');
			if (close_key != undefined)
				key = close_key;

			if (typeof markers_object[key] != 'undefined' && typeof markers_object[key] != 'undefined' && markers_object[key].old_lat && markers_object[key].old_lng) {
				let oldKey = markers_object[key].old_lat + '_' + markers_object[key].old_lng;
				if(typeof markers_object_db[oldKey] != 'undefined') {
					var latlng = new google.maps.LatLng(markers_object[key].old_lat, markers_object[key].old_lng);
					var new_key = markers_object[key].old_lat + '_' + markers_object[key].old_lng;


					markers_object[key]['marker'].setPosition(latlng);
					markers_object[key]['form'][0]['ti_lat'] = markers_object[key].old_lat;
					markers_object[key]['form'][0]['ti_lng'] = markers_object[key].old_lng;
					markers_object[key].marker.ti_key = new_key;

					markers_object[key]['marker'].setMap(map);
					markers_object = Common.renameKey(markers_object, key, new_key);
					delete markers_object[new_key].old_lat;
					delete markers_object[new_key].old_lng;
				}
			}

			if (typeof markers_object_db[key] == 'undefined') {
				if (typeof markers_object[key] != 'undefined' && typeof markers_object[key]['marker'] != 'undefined') {
					if(dragend === false) {
						markers_object[key]['marker'].setMap(null);
						delete markers_object[key];
					}
				}
			}
			infowindow.close();
		},

		set_marker_form_object: function (position, pinLabel) {
			lat = position.lat;
			lng = position.lng;
			var lead = $(config.ui.lead_select).val();
			let scheme_id = $(config.ui.scheme_select).val();
			if (pinLabel == undefined)
				pinLabel = (Object.keys(markers_object).length + 1).toString();

			if (markers_object_db[lat + '_' + lng] != undefined) {

				var work_types = markers_object_db[lat + '_' + lng]['work_types'].map(function (currentValue, index, array) {
					return parseInt(currentValue['tiwt_work_type_id']);
				});

				if (markers_object[lat + '_' + lng] == undefined)
					markers_object[lat + '_' + lng] = {};

				markers_object[lat + '_' + lng]['form'] = [{
					'ti_id': markers_object_db[lat + '_' + lng]['ti_id'],
					'ti_lat': lat,
					'ti_lng': lng,
					'ti_lead_id': markers_object_db[lat + '_' + lng]['ti_lead_id'],
					'ti_tree_number': markers_object_db[lat + '_' + lng]['ti_tree_number'],
					'ti_tree_type': markers_object_db[lat + '_' + lng]['ti_tree_type'],
					'ti_tree_priority': markers_object_db[lat + '_' + lng]['ti_tree_priority'],
					'ti_prune_type_id': markers_object_db[lat + '_' + lng]['ti_prune_type_id'],
					'work_types': work_types,
					'ti_remark': markers_object_db[lat + '_' + lng]['ti_remark'],
					'ti_title': markers_object_db[lat + '_' + lng]['ti_title'],
					'ti_size': markers_object_db[lat + '_' + lng]['ti_size'],
					'ti_cost': markers_object_db[lat + '_' + lng]['ti_cost'],
					'ti_stump_cost': markers_object_db[lat + '_' + lng]['ti_stump_cost'],
					'ti_map_type': window.ti_map_type,
					'ti_tis_id': scheme_id
				}];

			} else {
				markers_object[lat + '_' + lng]['form'] = [{
					'ti_lat': lat,
					'ti_lng': lng,
					'ti_lead_id': lead,
					'ti_tree_number': pinLabel,
					'ti_tree_type': '',
					'ti_tree_priority': '',
					'ti_prune_type_id': '',
					'work_types': [],
					'ti_remark': '',
					'ti_title': '',
					'ti_size': '',
					'ti_cost': '',
					'ti_stump_cost': '',
					'ti_map_type': window.ti_map_type,
					'ti_tis_id': scheme_id
				}];
			}
		},

		addSearchInput: function (controlDiv) {

			var controlUI = document.createElement('input');
			controlUI.className = 'form-control';
			controlUI.style.padding = "18px 3px";
			controlUI.style.margin = "10px 10px";
			controlUI.setAttribute('type', 'text');
			controlUI.setAttribute('placeholder', 'Search Box');
			controlUI.setAttribute('data-callback', 'TreeInventoryMap.search_address');
			controlUI.setAttribute('data-autocompleate', 'true');
			controlDiv.appendChild(controlUI);
			controlUI.addEventListener('click', function () {
				Common.init_autocompleate();
			});
		},

		addCreateButton: function(controlDiv) {
			let controlUI = document.createElement('select');
			controlUI.className = 'btn m-top-10  m-right-10 create-select';
			controlUI.style.borderRadius = '20px';
			controlUI.style.backgroundColor = 'white';
			controlUI.style.fontSize = '14px';
			controlUI.style.color = '#729C44';
			controlDiv.appendChild(controlUI);

			// Set CSS for the control interior.
			// let controlText = document.createElement('i');
			// controlText.className = 'fa fa-plus';
			//
			// controlText.innerHTML = '&nbsp; Create&nbsp;&nbsp;&nbsp;&nbsp;';
			// controlUI.appendChild(controlText);

			let option = document.createElement('option');
			option.value = 0;
			option.setAttribute('disabled', 'disabled');
			option.setAttribute('selected', 'selected');
			option.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Create';
			controlUI.appendChild(option);

			option = document.createElement('option');
			option.value = 'estimate';
			option.innerHTML = 'Estimate';
			controlUI.appendChild(option);

			option = document.createElement('option');
			option.value = 'wo';
			option.innerHTML = 'Work Order';
			option.id = 'create-wo';
			controlUI.appendChild(option);

			option = document.createElement('option');
			option.value = 'invoice';
			option.innerHTML = 'Invoice';
			controlUI.appendChild(option);

			// Set CSS for the control interior.
			controlText = document.createElement('i');
			controlText.className = 'fa fa-angle-down';
			controlUI.appendChild(controlText);

			// Setup the click event listeners: simply set the map to Chicago.
			controlUI.addEventListener('click', function () {

			});
		},

		addCreateNewLead: function (controlDiv){
			// Set CSS for the control border.
			let controlUI = document.createElement('button');
			controlUI.className = 'btn m-top-10  m-right-10';
			controlUI.style.borderRadius = '4px';
			controlUI.style.backgroundColor = 'white';
			controlUI.style.color = '#729C44';
			controlUI.style.fontSize = '14px';
			controlUI.textContent = 'Create a new lead';
			controlDiv.appendChild(controlUI);

			// Setup the click event listeners: simply set the map to Chicago.
			controlUI.addEventListener('click', function () {
				if($('.edit-project .data-id').is(':visible') === false) {
					errorMessage('Select a project');
					return false;
				}
				// setTimeout(function () {
				// 	// var position = _private.newMarkerPosition(0);
				// 	// _private.placeMarker(new google.maps.LatLng(position.lat, position.lng), map);
				// 	// _private.render_infowindow(markers_object[position.lat + '_' + position.lng]);
				// }, 100);
			});
		},

		addSelectLead: function(controlDiv) {
			if($('#leads_select'))
				$(controlDiv).append($('#leads_select'));
		},

		addCreateLeadButton: function(controlDiv){
			let controlUI = document.createElement('a');
			controlUI.className = 'btn m-top-10  m-right-10';
			controlUI.style.borderRadius = '4px';
			controlUI.style.backgroundColor = 'white';
			controlUI.style.color = '#729C44';
			controlUI.style.fontSize = '14px';
			controlUI.setAttribute('data-toggle', 'modal');
			controlUI.setAttribute('id', 'createLeadButtonModal');
			controlUI.setAttribute('href', '#createLeadModal');
			controlUI.textContent = 'Create';

			controlDiv.appendChild(controlUI);
		},

		addMarkerButton: function (controlDiv) {
			// Set CSS for the control border.
			let controlUI = document.createElement('button');
			controlUI.className = 'btn m-top-10  m-right-10';
			controlUI.style.borderRadius = '4px';
			controlUI.style.backgroundColor = 'white';
			controlUI.style.color = '#729C44';
			controlUI.style.fontSize = '14px';
			controlDiv.appendChild(controlUI);

			// Set CSS for the control interior.
			let controlText = document.createElement('i');
			controlText.className = 'fa fa-plus';

			controlText.innerHTML = '&nbsp;Add marker';
			controlUI.appendChild(controlText);

			// Setup the click event listeners: simply set the map to Chicago.
			controlUI.addEventListener('click', function () {
				if($('.edit-project .data-id').is(':visible') === false) {
					errorMessage('Select a project');
					return false;
				}
				setTimeout(function () {
					var position = _private.newMarkerPosition(0);
					_private.placeMarker(new google.maps.LatLng(position.lat, position.lng), map);
					_private.render_infowindow(markers_object[position.lat + '_' + position.lng]);
				}, 100);
			});
		},
		addSelectMarkersButton: function (controlDiv) {
			// Set CSS for the control border.
			let controlUI = document.createElement('button');
			controlUI.className = 'btn m-top-10 m-right-10';
			controlUI.style.borderRadius = '4px';
			controlUI.style.backgroundColor = 'white';
			controlUI.style.color = '#729C44';
			controlUI.style.fontSize = '14px';
			controlDiv.appendChild(controlUI);

			// Set CSS for the control interior.
			let controlText = document.createElement('i');
			controlText.className = 'fa fa-square-o';

			controlText.innerHTML = '&nbsp; Select markers';
			controlUI.appendChild(controlText);

			// Setup the click event listeners: simply set the map to Chicago.
			controlUI.addEventListener('click', function () {
				// var position = _private.newMarkerPosition(0);
				// _private.placeMarker(new google.maps.LatLng(position.lat, position.lng), map);
				// setTimeout(function () {
				// 	_private.render_infowindow(markers_object[position.lat + '_' + position.lng]);
				// }, 200);
				// To show:
				// console.log(drawingManager.getOption('drawingControl'));
				let visible = drawingManager.get('drawingControl');
				if(visible === false) {
					drawingManager.setOptions({
						drawingControl: true
					});
					$(config.ui.delete_shape).show();
				}
				else if(visible === true) {
					drawingManager.setOptions({
						drawingControl: false
					});
					drawingManager.setDrawingMode(null);
					$(config.ui.delete_shape).hide();
				}

			});
		},
		newMarkerPosition: function (i) {
			if (i == undefined)
				i = 0;
			position = map.getCenter();
			var lat = position.lat() + i;
			var lng = position.lng() + i;

			var key = lat + '_' + lng;

			if (markers_object[key] != undefined) {
				while (true) {
					i += 0.00008;
					lat += i;
					key = lat + '_' + lng;

					if (markers_object[key] == undefined) {
						return {'lat': lat, 'lng': lng};
					}
				}
			}

			return {'lat': lat, 'lng': lng};
		},
		addUploadButton: function (controlDiv) {
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


			controlUI.addEventListener('click', function () {
				$(config.ui.map_image).trigger('click');
			});

		},

		codeAddress: function (address, callback) {
			geocoder.geocode({'address': address}, function (results, status) {
				if (status == 'OK')
					callback(results);
				else
					alert('Geocode was not successful for the following reason: ' + status);
			});
		},

		clearSelection: function () {
			if (selectedShape) {
				if (selectedShape.type !== 'marker') {
					selectedShape.setEditable(false);
				}
				selectedShape = null;
			}
		},

		setSelection: function (shape) {
			if (shape.type !== 'marker') {
				_private.clearSelection();
				shape.setEditable(true);
				_private.selectColor(shape.get('fillColor') || shape.get('strokeColor'));
			}
			selectedShape = shape;

			setTimeout(function () {
				_private.set_checked_after_drawing();
			}, 200);

		},
		eventBoundsChanged: function (){
			_private.set_checked_after_drawing();
		},
		deleteSelectedShape: function () {
			if (selectedShape) {
				selectedShape.setMap(null);
				const index = shapes.indexOf(selectedShape);
				if (index !== -1) shapes.splice(index, 1);
			}
			setTimeout(function () {
				_private.set_checked_after_drawing();
			}, 200);
		},

		selectColor: function(color) {
			selectedColor = color;
			for (let i = 0; i < colors.length; ++i) {
				let currColor = colors[i];
				colorButtons[currColor].style.border = currColor == color ? '2px solid #789' : '2px solid #fff';
			}

			// Retrieves the current options from the drawing manager and replaces the
			// stroke or fill color as appropriate.
			let polylineOptions = drawingManager.get('polylineOptions');
			polylineOptions.strokeColor = color;
			drawingManager.set('polylineOptions', polylineOptions);

			let rectangleOptions = drawingManager.get('rectangleOptions');
			rectangleOptions.fillColor = color;
			drawingManager.set('rectangleOptions', rectangleOptions);

			let circleOptions = drawingManager.get('circleOptions');
			circleOptions.fillColor = color;
			drawingManager.set('circleOptions', circleOptions);

			let polygonOptions = drawingManager.get('polygonOptions');
			polygonOptions.fillColor = color;
			drawingManager.set('polygonOptions', polygonOptions);
		},

		setSelectedShapeColor:  function(color) {
			if (selectedShape) {
				if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
					selectedShape.set('strokeColor', color);
				} else {
					selectedShape.set('fillColor', color);
				}
			}
		},

		makeColorButton: function(color) {
			let button = document.createElement('span');
			button.className = 'color-button';
			button.style.backgroundColor = color;
			button.style.width = "14px";
			button.style.height = "14px";
			button.style.fontSize = "0";
			button.style.margin = "2px";
			button.style.float = "left";
			button.style.cursor = "pointer";
			google.maps.event.addDomListener(button, 'click', function () {
				_private.selectColor(color);
				_private.setSelectedShapeColor(color);
			});

			return button;
		},

		buildColorPalette: function() {
			let colorPalette = $('#color-palette');
			for (let i = 0; i < colors.length; ++i) {
				let currColor = colors[i];
				let colorButton = _private.makeColorButton(currColor);
				colorPalette.appendChild(colorButton);
				colorButtons[currColor] = colorButton;
			}
			_private.selectColor(colors[0]);
		},

		deleteShapeControl: function(controlDiv, map) {
			const controlUI = document.createElement("button");
			controlUI.classList.add("btn", "m-top-5", "btn-xs", 'delete-shape');
			controlUI.style.paddingBottom = '3px';
			controlUI.style.backgroundColor = 'white';
			controlUI.title = 'Delete selected shape';
			controlUI.style.display = 'none';
			controlDiv.appendChild(controlUI);

			// Set CSS for the control interior.
			let controlText = document.createElement('i');
			controlText.className = 'fa fa-trash-o';

			// controlText.innerHTML = '&nbsp; Select markers';
			controlUI.appendChild(controlText);
			controlUI.addEventListener("click", () => {
				_private.deleteSelectedShape();
			});
		},

		colorsControl: function(controlDiv, map) {
			const controlUI = document.createElement("div");
			controlUI.id = "color-palette";
			controlUI.className = "m-top-10";
			controlUI.style.clear = "both";
			for (let i = 0; i < colors.length; ++i) {
				let currColor = colors[i];
				let colorButton = _private.makeColorButton(currColor);
				controlUI.appendChild(colorButton);
				colorButtons[currColor] = colorButton;
			}
			// controlDiv.appendChild(controlUI);
		},

		initDrawingManager: function() {
			let polyOptions = {
				strokeWeight: 0,
				fillOpacity: 0.45,
				editable: true,
				draggable: true
			};

			const deleteControlDiv = document.createElement("div");
			_private.deleteShapeControl(deleteControlDiv, map);
			map.controls[google.maps.ControlPosition.TOP_CENTER].push(deleteControlDiv);


			const colorsControlDiv = document.createElement("div");
			colorsControlDiv.style.paddingLeft = "5px";
			_private.colorsControl(colorsControlDiv, map);
			map.controls[google.maps.ControlPosition.TOP_LEFT].push(colorsControlDiv);

			// Creates a drawing manager attached to the map that allows the user to draw
			// markers, lines, and shapes.
			drawingManager = new google.maps.drawing.DrawingManager({
				// drawingMode: google.maps.drawing.OverlayType.POLYGON,
				markerOptions: {
					draggable: true
				},
				polylineOptions: {
					editable: true,
					draggable: true
				},
				rectangleOptions: polyOptions,
				circleOptions: polyOptions,
				polygonOptions: polyOptions,
				map: map,
				drawingControlOptions: {
					position: google.maps.ControlPosition.TOP_CENTER,
					drawingModes: ['marker','polygon','circle','rectangle']
				},
				drawingControl: false
			});

			google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {
				let newShape = e.overlay;
				newShape.type = e.type;
				if (e.type !== google.maps.drawing.OverlayType.MARKER) {
					// Switch back to non-drawing mode after drawing a shape.
					drawingManager.setDrawingMode(null);

					// Add an event listener that selects the newly-drawn shape when the user
					// mouses down on it.
					google.maps.event.addListener(newShape, 'click', function (e) {
						if (e.vertex !== undefined) {
							if (newShape.type === google.maps.drawing.OverlayType.POLYGON) {
								let path = newShape.getPaths().getAt(e.path);
								path.removeAt(e.vertex);
								if (path.length < 3) {
									newShape.setMap(null);
								}
							}
							if (newShape.type === google.maps.drawing.OverlayType.POLYLINE) {
								let path = newShape.getPath();
								path.removeAt(e.vertex);
								if (path.length < 2) {
									newShape.setMap(null);
								}
							}
						}
						_private.setSelection(newShape);
					});

					google.maps.event.addListener(newShape, 'bounds_changed', function (e) {
						_private.eventBoundsChanged();
					});

					google.maps.event.addListener(newShape, 'drag', function (e) {
						_private.eventBoundsChanged();
					});

					google.maps.event.addListener(newShape, 'mouseup', function (e) {
						_private.eventBoundsChanged();
					});

					_private.setSelection(newShape);
					shapes.push(newShape);
				}
				else {
					if($('.edit-project .data-id').is(':visible') === false) {
						newShape.setMap(null);
						errorMessage('Select a project');
						return false;
					}
					let position = newShape.getPosition();
					_private.placeMarker(position, map);
					setTimeout(function () {
						_private.render_infowindow(markers_object[position.lat() + '_' + position.lng()]);
						newShape.setMap(null);
						}, 200);
				}
			});

			google.maps.Polygon.prototype.Contains = _private.pointInPolygon;
			// Clear the current selection when the drawing mode is changed, or when the
			// map is clicked.
			google.maps.event.addListener(drawingManager, 'drawingmode_changed', _private.clearSelection);
			google.maps.event.addListener(map, 'click', _private.clearSelection);
		},

		pointInCircle: function(point, radius, center){
			return (google.maps.geometry.spherical.computeDistanceBetween(point, center) <= radius)
		},

		pointInPolygon: function(point){
			let crossings = 0,
				path = this.getPath();
			// for each edge
			for (let i = 0; i < path.getLength() ; i++) {
				let a = path.getAt(i),
					j = i + 1;
				if (j >= path.getLength()) {
					j = 0;
				}
				let b = path.getAt(j);
				if (rayCrossesSegment(point, a, b)) {
					crossings++;
				}
			}
			// odd number of crossings?
			return (crossings % 2 == 1);
			function rayCrossesSegment(point, a, b) {
				let px = point.lng(),
					py = point.lat(),
					ax = a.lng(),
					ay = a.lat(),
					bx = b.lng(),
					by = b.lat();
				if (ay > by) {
					ax = b.lng();
					ay = b.lat();
					bx = a.lng();
					by = a.lat();
				}
				if (py == ay || py == by) py += 0.00000001;
				if ((py > by || py < ay) || (px > Math.max(ax, bx))) return false;
				if (px < Math.min(ax, bx)) return true;
				let red = (ax != bx) ? ((by - ay) / (bx - ax)) : Infinity;
				let blue = (ax != px) ? ((py - ay) / (px - ax)) : Infinity;
				return (blue >= red);
			}
		},

		create_estimate: function () {
			let url = baseUrl + 'tree_inventory/create_estimate';
			_private.create_document(url);
		},

		create_wo: function(){
			let url = baseUrl + 'tree_inventory/create_wo';
			_private.create_document(url);
		},

		create_invoice: function(){
			let url = baseUrl + 'tree_inventory/create_invoice';
			_private.create_document(url);
		},

		create_document: async function(url){
			let markers =[];
			let listMarkers=_private.get_all_markers_ids();
			listMarkers.map((item)=>{
				if($("input[name='ti_ids_["+item+"]']").prop('checked')){
					markers.push(item);
				}
			})



			let tis_id = $('.edit-project .data-id').data('id');
			let lead_id = $(config.ui.leads_select).val();

			if(markers.length && $('.edit-project .data-id').is(':visible') === true) {
				let mapImage = await _private.save_screen_map();
				$.ajax({
					url: url,
					data: {'ti_ids': JSON.stringify(markers), 'ti_tis_id': tis_id, 'screen_map' : mapImage, 'lead_id': lead_id},
					method: "POST",
					success: function (resp) {
						console.log(resp);
						if(resp.status == 'ok' && resp.url) {
							//window.open(resp.url, '_blank');
							window.location.href = resp.url;
							if(lead_id == 'new' && resp.new_lead_id){
								$(config.ui.leads_select).append('<option value="' + resp.new_lead_id  + '">' + resp.new_lead_id + '-L</option>');
							}
						}
					},
					dataType: 'json'
				});
			} else if ($('.edit-project .data-id').is(':visible') === false){
				errorMessage('Select a project');
			}
			else if (!markers.length && !shapes.length){
				errorMessage('Add a marker');
			}
			else if (!markers.length && shapes.length){
				errorMessage('There are no markers in the shape');
			}
		},
		set_checked_after_drawing: function(){
			var all_markers=_private.get_all_markers_ids();

			var markers=_private.get_selected_markers_ids();
			var all_selected_markers=[];
			all_markers.map((item)=>{
				var status=false;
				if(markers.includes(item)){
					status=true;
				}
				if(checked_Trees.includes(item)){
					status=true;
				}
				if(unChecked_Trees.includes(item)){
					status=false;
				}
				if(status){
					all_selected_markers.push(item);
				}
				$("input[name='ti_ids_["+item+"]']").prop('checked', status);
			})
			return all_selected_markers;
		},
		get_selected_markers_ids: function(){
			let markers = [];
			if(shapes.length) {
				$.each(shapes, function (key, shape) {
					if ($('.tree-item').length)
						$.each($('.tree-item'), function (key, val) {
							let latLng = new google.maps.LatLng($(val).data("lat"), $(val).data("lng"));
							let id = $(val).find('.edit-tree-map').data("ti_id");

							let inside = false;
							if (shape.type === google.maps.drawing.OverlayType.POLYGON) {
								if (shape.Contains(latLng)) {
									inside = true;
								}
							} else if (shape.type === 'circle') {
								inside = _private.pointInCircle(latLng, shape.getRadius(), shape.center);
							} else if (shape && shape.getBounds && latLng)
								inside = shape.getBounds().contains(latLng);
							if (inside && markers.indexOf(id) === -1) {
								markers.push(id);
							}
						});

				});
			}
			return markers;
		},

		get_all_markers_ids: function(){
			let markers = [];
			if ($('.tree-item').length){
				$.each($('.tree-item'), function (key, val) {
					let id = $(val).find('.edit-tree-map').data("ti_id");
					markers.push(id);
				});
			}
			return markers;
		},

		scheme_select:function (e) {
			let url = $(this).find("option:selected").data('url');
			if(url)
				location.href = url;
		},
		set_edit_project_modal: function(id, copy = false){
			if(id) {
				$.ajax({
					method: "POST",
					url: base_url + 'tree_inventory/edit_project',
					dataType: 'JSON',
					data: {'tis_id': id},
					global: false
				}).done(function (msg) {
					if (msg.project !== undefined) {
						let project = msg.project;
						let project_name = project.tis_name;
						let tis_id = project.tis_id;

						if(copy) {
							project_name += ' ' + date_now;
							$(config.ui.new_project + ' [name="tis_copy"]').val(1);
							$(config.ui.new_project + ' [name="tis_copy_id"]').val(id);
							tis_id = '';
						} else {
							id = '';
						}
						$(config.ui.new_project + ' [name="tis_name"]').val(project_name);
						$(config.ui.new_project + ' [name="tis_address"]').val(project.tis_address);
						$(config.ui.new_project + ' [name="tis_city"]').val(project.tis_city);
						$(config.ui.new_project + ' [name="tis_state"]').val(project.tis_state);
						$(config.ui.new_project + ' [name="tis_zip"]').val(project.tis_zip);
						$(config.ui.new_project + ' [name="tis_country"]').val(project.tis_country);
						$(config.ui.new_project + ' [name="tis_lat"]').val(project.tis_lat);
						$(config.ui.new_project + ' [name="tis_lng"]').val(project.tis_lng);
						$(config.ui.new_project + ' [name="tis_id"]').val(tis_id);
						$(config.ui.new_project + ' [name="tis_copy"]').val(copy);
						$(config.ui.new_project + ' [name="tis_copy_id"]').val(id);
						$(config.ui.new_project).modal('show');
					}
				});
			}
		},

		copy_project: function () {
			let id = $(this).data('id');
			_private.set_edit_project_modal(id, true);
			return;
			if(markers.length) {
				$.ajax({
					url: url,
					data: {'ti_ids': JSON.stringify(markers), 'ti_tis_id': $(config.ui.scheme_select).val()},
					method: "POST",
					global: false,
					success: function (resp) {
						console.log(resp);
					},
					dataType: 'json'
				});
			} else {
				errorMessage('Add a marker first');
			}
		},

		click_project: function () {
			let id = $(this).data('id');
			$(config.ui.tree_project).hide();
			$(config.ui.tree_list).show();
			if(id) {
				$.ajax({
					url: base_url + 'tree_inventory/get_scheme_data',
					data: {'tis_id': id},
					method: "POST",
					global: false,
					success: function (resp) {
						if(resp.status == 'ok') {
							let tree_list = resp.tree_inventory;
							markers_object = {};
							_private.set_tree_inventory_list(tree_list);
							_private.update_inventory_list(tree_list);
							$(config.ui.edit_project).html(resp.tree_project);
							$(config.ui.project_delete).data('id', resp.tis_id);

							if(resp.tis_lat && resp.tis_lon) {
								position = new google.maps.LatLng(resp.tis_lat, resp.tis_lon);
								map.setCenter(position);
								if(resp.tis_overlay_path) {
									map.setZoom(5);
									_private.clear_default_marker();
									latlngbounds = map.getBounds();
									overlay = new USGSOverlay(map.getBounds(), resp.tis_overlay_path);
									overlay.setMap(map);

									setTimeout(()=>{
										// map.set('restriction', {latLngBounds: map.getBounds()});
										$(config.ui.google_map_background).css('visibility', 'hidden');
									}, 100);
								}
								else
									_private.setClientAddressMarker(position);
							}
						}
					},
					dataType: 'json'
				});
			}
		},

		back_to_projects: function(){
			if(overlay) {
				_private.init_map();
				overlay = null;
			}
			_private.clear_map();
			_private.set_default_center();
			$(config.ui.tree_project).show();
			$(config.ui.tree_list).hide();
		},

		set_default_center: function(){
			_private.clear_default_marker();
			let origin_lat = $(config.ui.map).data('origin_lat');
			let origin_lon = $(config.ui.map).data('origin_lon');
			let client_lat = $(config.ui.client_lat).val();
			let client_lng = $(config.ui.client_lon).val();
			if(client_lat && client_lng && client_lat != 0 && client_lng != 0) {
				position = new google.maps.LatLng(client_lat, client_lng);
				map.setCenter(position);
				_private.setClientAddressMarker(position);
			} else {
				position = new google.maps.LatLng(origin_lat, origin_lon);
				map.setCenter(position);
				_private.setClientAddressMarker(position);
			}
		},

		clear_default_marker: function(){
			if(default_marker.length)
				$.each(default_marker, function (key, val) {
					val.setMap(null);
				});
			default_marker = [];
		},

		project_create: function () {
			$(config.ui.new_project + ' [name="tis_name"]').val('');
			$(config.ui.new_project + ' [name="tis_address"]').val($(config.ui.client_address).val());
			$(config.ui.new_project + ' [name="tis_city"]').val($(config.ui.client_city).val());
			$(config.ui.new_project + ' [name="tis_state"]').val($(config.ui.client_state).val());
			$(config.ui.new_project + ' [name="tis_zip"]').val($(config.ui.client_zip).val());
			$(config.ui.new_project + ' [name="tis_country"]').val($(config.ui.client_country).val());
			$(config.ui.new_project + ' [name="tis_lat"]').val($(config.ui.client_lat).val());
			$(config.ui.new_project + ' [name="tis_lng"]').val($(config.ui.client_lon).val());
			$(config.ui.new_project + ' [name="tis_id"]').val('');
			$(config.ui.new_project + ' [name="tis_copy"]').val('');
			$(config.ui.new_project + ' [name="tis_copy_id"]').val('');
		},

		project_edit: function () {
			let id = $(this).data('id');
			_private.set_edit_project_modal(id);
		},

		project_delete: function () {
			let result = confirm("Are you sure?");
			let id = $(this).data('id');
			let client_id = $(config.ui.client_id).val();
			if(id && result) {
				$.ajax({
					method: "POST",
					url: base_url + 'tree_inventory/delete_project',
					dataType: 'JSON',
					data: {'tis_id': id, 'tis_client_id' : client_id}
				}).done(function (msg) {
					if(msg.status == 'ok') {
						successMessage('');
						if(msg.section_tree_projects){
							$(config.ui.tree_project).replaceWith(msg.section_tree_projects);
						}
						$(config.ui.back_to_projects).click();
					}
				});
			}
		},

		change_create_select: function () {
			let val = $(this).val();
			if(val == 'invoice')
				_private.create_invoice();
			else if(val == 'estimate')
				_private.create_estimate();
			else if(val == 'wo')
				_private.create_wo();
			$(this).val(0);
		},

		clear_map: function () {
			if(overlay) {
				overlay.setMap(null);
			}
			$.each(markers_object, function (key, val) {
				val['marker'].setMap(null);
			});
			if(shapes.length){
				$.each(shapes, function (key, shape) {
					shape.setMap(null);
				});
			}
			markers_object = {};
		},

		hide_modal_new_project: function () {
			$("[name='tis_overlay']").val(null);
			// markers_object[key]['marker'].setMap(null);
		},

		hide_map_custom_buttons: function () {
			$(config.ui.select_markers_button).hide();
			$(config.ui.add_marker_button).hide();
			$(config.ui.create_button).hide();
			$(config.ui.delete_shape).hide();
			$(config.ui.create_lead_button).hide();
		},

		hide_elements_for_screen: function () {
			_private.hide_map_custom_buttons();
			map.set('disableDefaultUI', true);
			$('.gmnoprint').hide();
			$('.gm-style-cc').hide();
		},

		show_map_custom_buttons: function () {
			$(config.ui.select_markers_button).show();
			$(config.ui.add_marker_button).show();
			$(config.ui.create_button).show();
			$(config.ui.create_lead_button).show();
		},

		show_elements_after_screen: function () {
			_private.show_map_custom_buttons();
			map.set('disableDefaultUI', false);
			$('.gmnoprint').show();
			if ($(config.ui.delete_shape).closest('div').prev('.gmnoprint').is(':visible'))
				$(config.ui.delete_shape).show();
			$('.gm-style-cc').show();
		},

		save_screen_map: function () {
			selectedMarkers = markers_object;
			let mapImage = null;
			if(!overlay && selectedMarkers) {
				let latlng = [];
				$.each(selectedMarkers, function (key, val) {
					latlng.push(val.marker.position);
				});
				latlngbounds = new google.maps.LatLngBounds();
				for (var i = 0; i < latlng.length; i++) {
					latlngbounds.extend(latlng[i]);
				}
			}
			if(latlngbounds) {
				map.fitBounds(latlngbounds);
				let zoom = map.getZoom();
				if(overlay)
					map.setZoom(zoom + 1);
				else
					map.setZoom(zoom - 1);
			}
			_private.hide_elements_for_screen();

			return new Promise(resolve => {
				let canvas = "#inventory_map .gm-style";
				if(overlay){
					canvas = "#inventory_map";
				}
				setTimeout(function(){
					html2canvas($(canvas), {
						useCORS: true,
						onrendered: function(canvas) {
							mapImage = canvas.toDataURL("image/png");
							_private.show_elements_after_screen();
							resolve(mapImage);
						}
					});
				}, 1000);
			});

		},

		create_lead_from_modal: function() {
			$('#input1 option').length;
		}

		/*
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
					console.log(value.tiwt_work_type_id);
					if(work_types_assoc[value.tiwt_work_type_id] != undefined){
						name = '';
						name = work_types_assoc[value.tiwt_work_type_id]['ip_name_short'];
						if(short==false)
							name=name+':'+work_types_assoc[value.tiwt_work_type_id]['ip_name'];

						result_array.push(name);
					}
				});
				
				return result_array.join(', ');
			}
		}
		*/
	};
	
	var selected_date;
	var public = {
		
		init:function(){
			$(document).ready(function(){
				if(window.ti_map_type!=config.maptype_driver)
					return;

			  	public.events();
			  	_private.init();
			});
		},

		Screenshot:function (){
			//Inspect the element and find .gm-style>div:first>div:first>div:last>div -> May vary from versions
			// var transform=$(".gm-style>div:first>div:first>div:last>div").css("transform");
			var transform=$(".gm-style>div:first").next().find('div').css("transform");
			console.log(transform);
			var comp=transform.split(","); //split up the transform matrix
			var mapleft=parseFloat(comp[4]); //get left value
			var maptop=parseFloat(comp[5]);  //get top value
			$(".gm-style>div:first").next().find('div').css({
				"transform":"none",
				"left":mapleft,
				"top":maptop,
			});
			// html2canvas($(".inventory-map-screen"),
			// 	{
			// 		useCORS: true
			// 	}).
			// then(function(canvas)
			// 	{
			// 		console.log(canvas);
			// 		// $("#img-out").append(canvas);
			// 		// $(".gm-style>div:first>div:first>div:last>div").css({
			// 		// 	left:0,
			// 		// 	top:0,
			// 		// 	"transform":transform
			// 		// })
			// 	}
			// );
			html2canvas($(".inventory-map-screen"), {
				useCORS: true,
				onrendered: function(canvas) {
					var dataUrl= canvas.toDataURL('image/png');
					console.log(dataUrl); //for testing I never get window.open to work
					$(".gm-style>div:first").next().find('div').css({
						left:0,
						top:0,
						"transform":transform
					})
					return;
					myImage = canvas.toDataURL("image/png");
					console.log(myImage);
				}
			});
		},
		
		events:function(){
			$(document).delegate(config.events.create_estimate, 'click', _private.create_estimate);
			$(document).delegate(config.events.create_invoice, 'click', _private.create_invoice);
			$(document).delegate(config.events.create_wo, 'click', _private.create_wo);
			$(document).delegate(config.events.close_infowindow, 'click', _private.close_infowindow);
			$(document).delegate(config.events.scheme_select, 'change', _private.scheme_select);
			$(document).delegate(config.events.copy_project, 'click', _private.copy_project);
			$(document).delegate(config.events.projects, 'click', _private.click_project);
			$(document).delegate(config.events.back_to_projects, 'click', _private.back_to_projects);
			$(document).delegate(config.events.project_create, 'click', _private.project_create);
			$(document).delegate(config.events.project_edit, 'click', _private.project_edit);
			$(document).delegate(config.events.project_delete, 'click', _private.project_delete);
			$(document).delegate(config.events.create_select, 'change', _private.change_create_select);
			$(document).delegate(config.events.save_lead, 'click', _private.create_estimate);


			$(document).delegate(config.events.edit_tree, 'click', public.edit_tree);

			$(document).delegate(config.events.open_tree_history, 'click', public.open_tree_history);
			$(document).delegate(config.events.toggleChevronTree, 'click', public.toggleChevronTree);
			$(document).delegate(config.events.treeIdChecker, 'click', public.treeIdChecker);

			$(config.events.tree_inventory_modal).on('show.bs.modal', public.saveMap);
			$(config.events.new_project).on('hide.bs.modal', _private.hide_modal_new_project);

			$("body").on('keyup', config.ui.ti_tree_number, function(e){
				var realVal=$(this).val();
				$(this).val(realVal.split('"').join(''));
			});

			$(document).delegate(".tree-item", "mouseenter", function(){
				lat = $(this).data('lat');
				lng = $(this).data('lng');
				if(markers_object[lat+'_'+lng]!=undefined)
					markers_object[lat+'_'+lng]['marker'].setAnimation(google.maps.Animation.BOUNCE);
			});

			$(document).delegate(".tree-item", "mouseleave", function(){
				lat = $(this).data('lat');
				lng = $(this).data('lng');
				if(markers_object[lat+'_'+lng]!=undefined)
					markers_object[lat+'_'+lng]['marker'].setAnimation(null);
			});
			/*
			$(document).delegate(config.ui.lead_select, "change", function(){
				id = $(this).val();
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
			*/
			$(config.ui.map_image).change(function(){
				$(config.ui.map_form).trigger("submit");
			});

			if(selectTags)
				window.initSelect2($(config.ui.create_lead).find("input.est_services"), window.selectTags, "Select Services");
			if(selectTagsProducts)
            	window.initSelect2($(config.ui.create_lead).find("input.est_products"), window.selectTagsProducts, 'Select Products');
        	if(selectTagsBundles)
            	window.initSelect2($(config.ui.create_lead).find("input.est_bundles"), window.selectTagsBundles, 'Select Bundles');

			//window.initDropzone($(".dropzone-lead"));
			$(document).delegate('[name="copy_to_lead"]', "change", public.select_copy_lead);
		},
		saveMap:function(callbackCustom){
			var callback = callbackCustom;
			html2canvas($("#inventory_map .gm-style div:first"), {
			    useCORS:true,
			    windowWidth:20,
			    windowHeight: 10,
			    onrendered: function(canvas) {
			        var uridata = canvas.toDataURL("image/png");
			       	
			       	var renderScreen = {template_id:config.templates.map_screen, view_container_id:config.views.map_screen, data:[{map_image:uridata}], helpers:TreeInventoryHelper.helpers};
					Common.renderView(renderScreen);

					var renderScreenForm = {template_id:config.templates.map_screen_form, view_container_id:config.views.map_screen_form, data:[{map_image:uridata}], helpers:TreeInventoryHelper.helpers};
					Common.renderView(renderScreenForm);

					var renderScreenForm2 = {template_id:config.templates.map_screen_form, view_container_id:config.views.map_screen_form2, data:[{map_image:uridata}], helpers:TreeInventoryHelper.helpers};
					Common.renderView(renderScreenForm2);

					if(callback!=undefined)
						callback();
			    }
			});

			$('img[data-toggle="popover"]').popover();
		},
		edit_tree:function(e){
			if (infowindow != undefined && infowindow.getMap()){
				_private.close_infowindow(false, $(config.ui.form_lat).val()+'_'+$(config.ui.form_lng).val());
			}

			if($(e.target).closest('form').length!=0 && $(e.target).closest('form').attr('class')==config.events.delete_formClass)
				return;

			var data = $(this).data();
			if(markers_object[data.ti_lat+'_'+data.ti_lng]==undefined)
				return false;


			_private.close_infowindow(false, data.ti_lat+'_'+data.ti_lng);
			_private.render_infowindow(markers_object[data.ti_lat+'_'+data.ti_lng]);
		},
		open_tree_history: function(e){
			var ti_id=$(this).data('ti_id');
			var btn=$(config.events.open_tree_history);
			var block=$(config.ui.history_tree_block);
			if(btn.hasClass('opened')){
				block.hide(400);
				btn.find('i').removeClass('fa-eye-slash').addClass('fa-eye');
			}else{
				public.loadFullHistory(ti_id);
				block.show(400);
				btn.find('i').removeClass('fa-eye').addClass('fa-eye-slash');
			}
			btn.toggleClass('opened');

		},
		loadFullHistory: function(id){
			$.ajax({
				url: base_url + 'tree_inventory/get_tree_history_data',
				data: {'tis_id': id},
				method: "POST",
				global: false,
				success: function (resp) {
					if(resp.status == 'ok' ) {
						if( resp.list && resp.list.length>0){
							var template=$(config.templates.row_history_template);
							var options = { year: 'numeric', month: 'short', day: 'numeric' };
							resp.list.map(function(item){
								var new_element=template;
								var date_created=Common.helpers.dateFormat(item.estimate[0].date_created*1000);
								new_element.find('.history_item_title').text(date_created);
								new_element.find('a.estimate_id').text(item.estimate[0].estimate_no).prop('href','/estimates/edit/'+item.estimate[0].estimate_id);
								var workTypes='';
								if(item.work_types && item.work_types.length>0){
									item.work_types.map(function(wT,key){
										if(key!=0){
											workTypes+=', ';
										}
										workTypes+=wT.ip_name_short;
									})
								}
								new_element.find('span.notes').text(item.estimates_services.service_description);
								new_element.find('span.cost').text(item.ties_cost);
								new_element.find('span.stump').text(item.ties_stump_cost);
								new_element.find('span.work_types').text(workTypes);
								new_element.find('div.collapse').attr('id','collapse'+item.ties_id);
								new_element.find('a.toggleChevron').attr('href','#collapse'+item.ties_id);
								$(config.ui.history_tree_block).find('.result').append(new_element.html());
							})
							template.remove();
						}else{
							$(config.ui.history_tree_block).find('.result').html('<h5>History is empty</h5>');
						}
					}
				},
				dataType: 'json'
			});
		},
		toggleChevronTree:function(){
			$(this).find('i').toggleClass('fa-angle-down').toggleClass('fa-angle-up');
		},
		treeIdChecker:function(){
			var selectedId=$(this).data('ti_id');
			$(this).removeClass('everSelected').removeClass('everUnSelected')
			if($(this).prop('checked')==true){
				if(!checked_Trees.includes(selectedId)){
					checked_Trees.push(selectedId);
					$(this).addClass('everSelected');
				}
				var myIndex = unChecked_Trees.indexOf(selectedId);
				if (myIndex !== -1) {
					unChecked_Trees.splice(myIndex, 1);
				}
			}else{
				var myIndex = checked_Trees.indexOf(selectedId);
				if (myIndex !== -1) {
					checked_Trees.splice(myIndex, 1);
				}
				if(!unChecked_Trees.includes(selectedId)){
					unChecked_Trees.push(selectedId);
					$(this).addClass('everUnSelected');
				}
			}
		},
		open_pdf:function(response){
			const a = document.createElement('a');
		    a.href = response.link;
		    a.target = '_blank';
		    document.body.appendChild(a);
		    a.click();
		    document.body.removeChild(a);
		},

		delete_callback:function(response){
			
			if(response.status!='ok')
				return;

			var key = response.response.deleted.ti_lat+'_'+response.response.deleted.ti_lng;
			markers_object[key]['marker'].setMap(null);
			delete markers_object[key];

			_private.update_inventory_list(response.response.tree_inventory);
		},

		before_delete:function(form){
			alert("before");
		},

		save_callback:function(response){
			if(response.status!='ok')
				return;
			_private.change_position_key = '';
			_private.update_inventory_list(response.response.tree_inventory, response.marker);
			// _private.update_inventory_list(response.response.tree_inventory, response.marker);
			if(markers_object[response.marker.ti_lat+'_'+response.marker.ti_lng].old_lat!=undefined){
				delete markers_object[response.marker.ti_lat+'_'+response.marker.ti_lng].old_lat;
				delete markers_object[response.marker.ti_lat+'_'+response.marker.ti_lng].old_lng;
			}
			_private.close_infowindow(false,response.marker.ti_lat+'_'+response.marker.ti_lng);
		},

		set_tree_inventory_list:function(response){
			if(response.status!='ok')
				return;
				
			$.each(markers_object, function(key, value){ value['marker'].setMap(null); });
			$(config.events.close_infowindow).trigger('click');
			_private.set_tree_inventory_list(response.response.tree_inventory);
		},

		search_address:function(address){
			var place = address.getPlace();
			if (place.length == 0) {
		      return;
		    }

		    map.setCenter(new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng()));
		},

		map_screen_updated: function(response){

		},

		copy_tree_inventory:function(response){
			$('#copy-tree-inventory').find('[name="ti_lead_id_to"]').val(response.lead_id);
			$('#copy-tree-inventory').find('[name="ti_client_id_to"]').val(response.client_id);
			$('#copy-tree-inventory').trigger('submit');
		},

		select_copy_lead: function(){
			$('#copy-tree-inventory').find('[name="ti_lead_id_to"]').val($(this).data('lead_id'));
			$('#copy-tree-inventory').find('[name="ti_client_id_to"]').val($(this).data('client_id'));
		},

		project_callback: function (callback) {
			if(callback.status === 'ok') {
				_private.clear_map();
				$(config.ui.new_project).modal('hide');
				successMessage('');
				if(callback.section_tree_projects){
					$(config.ui.tree_project).replaceWith(callback.section_tree_projects);
					if(callback.tis_id && callback.update == true) {
						if(overlay)
							overlay.setMap(null);
						$(config.ui.tree_list_project + ' li [data-id="' + callback.tis_id + '"]').click();
					}
				}
			}
		}
	};

	public.init();
	return public;
}();

// The custom USGSOverlay object contains the USGS image,
// the bounds of the image, and a reference to the map.
class USGSOverlay extends google.maps.OverlayView {
	bounds_;
	image_;
	div_;
	constructor(bounds, image) {
		super();
		// Initialize all properties.
		this.bounds_ = bounds;
		this.image_ = image;
		// Define a property to hold the image's div. We'll
		// actually create this div upon receipt of the onAdd()
		// method so we'll leave it null for now.
		this.div_ = null;
	}
	/**
	 * onAdd is called when the map's panes are ready and the overlay has been
	 * added to the map.
	 */
	onAdd() {
		this.div_ = document.createElement("div");
		this.div_.style.borderStyle = "none";
		this.div_.style.borderWidth = "0px";
		this.div_.style.position = "absolute";
		this.div_.style.textAlign = "center";
		// Create the img element and attach it to the div.
		const img = document.createElement("img");
		img.src = this.image_;
		// img.style.width = "100%";
		img.style.height = "100%";
		// img.style.position = "absolute";
		img.style.objectFit = "contain";
		// img.style.backgroundSize = "cover";
		this.div_.appendChild(img);
		// Add the element to the "overlayLayer" pane.
		const panes = this.getPanes();
		panes.overlayLayer.appendChild(this.div_);
	}
	draw() {
		// We use the south-west and north-east
		// coordinates of the overlay to peg it to the correct position and size.
		// To do this, we need to retrieve the projection from the overlay.
		const overlayProjection = this.getProjection();
		// Retrieve the south-west and north-east coordinates of this overlay
		// in LatLngs and convert them to pixel coordinates.
		// We'll use these coordinates to resize the div.
		const sw = overlayProjection.fromLatLngToDivPixel(
			this.bounds_.getSouthWest()
		);
		const ne = overlayProjection.fromLatLngToDivPixel(
			this.bounds_.getNorthEast()
		);

		// Resize the image's div to fit the indicated dimensions.
		if (this.div_) {
			this.div_.style.left = sw.x + "px";
			this.div_.style.top = ne.y + "px";
			this.div_.style.width = ne.x - sw.x + "px";
			this.div_.style.height = sw.y - ne.y + "px";
		}
	}
	/**
	 * The onRemove() method will be called automatically from the API if
	 * we ever set the overlay's map property to 'null'.
	 */
	onRemove() {
		if (this.div_) {
			this.div_.parentNode.removeChild(this.div_);
			this.div_ = null;
		}
	}
}
