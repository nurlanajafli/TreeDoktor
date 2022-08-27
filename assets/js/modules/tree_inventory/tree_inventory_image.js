var TreeInventoryImage = function(){
	var config = {
		maptype_driver: 'image',
		ui:{
			modal:'#infowindowform-modal',
			container: '.inventory-map-container',
			container_scroll: '.inventory-map-container .scrollable',
			//map_id:'inventory_map',
			/*
			map:'#mapper-image',
			map_id:'mapper-image',
			*/
			map_id:'inventory_map',
			map:'#inventory_map',

			map_image_screen:'#mapper-image',
			map_image_screen_id:'mapper-image',

			map_image_screen_container:'#inventory_map_image',
			map_image_screen_container_id:'inventory_map_image',
			tree_type:'#tree_type',
			lead_select:'#lead-select',
			lead_id:'[name="ti_lead_id"]',
			map_form:'#upload-map-image',
			delete_map_form:'#delete-map-image',

			map_image:'#map-image',

			ti_lat:'[name="ti_lat"]', 
			ti_lng:'[name="ti_lng"]',
			select2:[
				{
					selector:'#work_types',
					options:{
						width: '100%',
						//selectOnClose: true,
						//dropdownParent:$('#infowindowform-modal'),
					},
					values:false,
					onchange:function(){
			    		$('.work_types .select2-choices').closest('.form-group').height($('.work_types .select2-choices').height());
			    	}
				},
				{
					selector:'#ti_tree_type',
					options:{
						width: '100%',
						//selectOnClose: true
						//dropdownParent:$('#infowindowform-modal'),
					},
					onchange:false,
					values:false,
				},
				/*{
					selector:'#ti_lead_id',
					options:{width: '100%'},
					onchange:false,
					values:false,
				},*/
				
			]
		},

		events:{
			save_tree_details:'#save-tree-details',
			edit_tree:'.edit-tree-image',
			tree_inventory_modal:'#inventory-list-modal',
			delete_form:'.delete-map-item',
			delete_formClass:'delete-image-item',
			delete_button:'.delete-image-item button',

			close_modalwindow: '.close-modalwindow',
		},
		
		route:{},
		
		templates:{
			infowindowform:'#infowindowform-modal-tmp',
			tree_list:"#tree-list-tmp",
			tree_list_emp:"#tree-list-tmp-emp",
			tree_list_table: "#tree-list-table-tmp",
			tree_list_table_totals:"#tree-list-table-totals-tmp",
			map_screen:"#map-screen-tmp",
			map_screen_form:"#map-screen-form-tmp"
		},
		views:{
			infowindowform:'#infowindowform-modal-body',
			tree_list:"#tree-list",
			tree_list_table: "#tree-list-table",
			tree_list_table_totals:"#tree-list-table-totals",
			map_screen:"#map-screen",
			map_screen_form:"#map-screen-form"
		},
	};
	
	var markers_object = {};
	var markers_object_db = {};
	
	var work_types_assoc = {};
	var stage,layer,tooltip;
	var backgroundImg;
	var newScale, backgroundGroup;
	var _private = {
		init:function(){
			
			if(window.ti_map_type!=config.maptype_driver)
				return;
			
			$(document).ready(function(){
				work_types_assoc = TreeInventoryHelper.init_work_types();
				_private.init_canvas_map();

				_private.buttons();
				_private.events();

				var tree_list = [];
				if(window.tree_inventory_list!=undefined){
					var tree_list = JSON.parse(window.tree_inventory_list);
					_private.set_tree_inventory_list(tree_list);
				}	
			});
			
		},
		
		init_canvas_map:function(){
			
			width = $(config.ui.map_image_screen_container).data('width');
			height = $(config.ui.map_image_screen_container).data('height');

			stage = new Konva.Stage({
		        container: config.ui.map_image_screen_container_id,
		        width: width,
		        height: height,
		    });

			layer = new Konva.Layer();
      		stage.add(layer);
      		
      		/*------ add map ------*/
      		backgroundImg = new Konva.Image({ width: width, height: height });
      		backgroundGroup = new Konva.Group({x: 0, y: 0, draggable: false});
		    layer.add(backgroundGroup);
		    backgroundGroup.add(backgroundImg);
			var backgroundObj = new Image();
      		backgroundObj.onload = function() {
        		backgroundImg.image(backgroundObj);
        		layer.draw();
      		};
      		backgroundObj.src = $(config.ui.map_image_screen_container).data('background');
      		/*------ add map -----*/

      		$(document).on('wheel', function(e){
      			if((e.target.id==config.ui.map_image_screen_container_id)){
      				stage.fire('wheel', {evt:e.originalEvent});	
      			}
      		});

      		var scaleBy = 1.06;
			stage.on('wheel', e => {
				if(e.evt!=undefined)
				e.evt.preventDefault();

				var oldScale = stage.scaleX();
				newScale = e.evt.deltaY > 0 ? oldScale / scaleBy : oldScale * scaleBy;
				stage.scale({ x: newScale, y: newScale });

				var newPos = {x:0, y:0};
				//-(mousePointTo.y - stage.getPointerPosition().y / newScale) * newScale
				//-(mousePointTo.x - stage.getPointerPosition().x / newScale) * newScale,

				/* change layer position */
				stage.width(width * newScale);
				stage.height(height * newScale);
				stage.scale({ x: newScale, y: newScale });
				/* change layer position */

				stage.position(newPos);
				stage.batchDraw();
			});


		},

		events: function(){
			/* set new  marker */
			mouse_move = false;
			$(config.ui.container_scroll).mouseout(function(){ mouse_move = false; });
			
		    $(config.ui.map_image_screen_container).swipe( {
		      	doubleTap:function(event, phase) {
		      		if(newScale==undefined)
		      			newScale = 1;

		      		position = _private.getRelativePointerPosition(backgroundGroup);
		      		if(position==false)
		      		{
		      			position = {x: stage._changedPointerPositions[0].x, y: stage._changedPointerPositions[0].y};
		      		}

		        	var ti_lat = position.y-40;
					var ti_lng = position.x-15;

					_private.placeMarker(ti_lat, ti_lng);
					setTimeout(function(){
						var key = ti_lat+'_'+ti_lng;
				    	_private.render_infowindow(markers_object[key]['marker']);
				    }, 200);

		        },
		        click:function(event,target){}, hold:function(){}, tap: function(){}, 
		        swipe:function(event, phase, direction, distance, duration, fingers, fingerData, currentDirection){
		        	
	        		difX = (fingers[0].end.x - fingers[0].start.x);
			    	difY = (fingers[0].end.y - fingers[0].start.y);
			    
			    	new_posTop = $(config.ui.container_scroll).scrollTop()-difY;
			    	new_posLeft = $(config.ui.container_scroll).scrollLeft()-difX;
			        
			        $(config.ui.container_scroll).animate({
			        	scrollTop: new_posTop,
			        	scrollLeft: new_posLeft
			        });
				    return true;
					
		        }, pinchIn: function() {}, pinchOut: function() {},
			    swipeStatus: function(event, phase, direction, distance, duration, fingers, fingerData, currentDirection) {
			    	
			    	if(event.type=="pointerdown" || phase=='start' || phase=='move'){
			    		mouse_move = true;
			    	}
			    	else{
			    		mouse_move = false;
			    	}
			    	
			    	if(phase=='move' && mouse_move == true){
			    		difX = (fingerData[0].end.x - fingerData[0].start.x)/20;
				    	difY = (fingerData[0].end.y - fingerData[0].start.y)/20;
				    	
				    	new_posTop = $(config.ui.container_scroll).scrollTop()-difY;
				    	new_posLeft = $(config.ui.container_scroll).scrollLeft()-difX;
				        
				        $(config.ui.container_scroll).scrollTop(new_posTop);
				    	$(config.ui.container_scroll).scrollLeft(new_posLeft);
					    return true;
			    	}
			    },
			    fingers:'all',
		    	threshold:100,
		    	cancelThreshold:10,
		        maxTimeThreshold:500,
		        triggerOnTouchEnd:true,
		    	allowPageScroll: 'auto',
		        fallbackToMouseEvents:true,
		        preventDefaultEvents:false
		    });
		    

		    $(config.ui.modal).on('shown.bs.modal', function(){	
		  		$(config.ui.lead_id).val($(config.ui.lead_select).val());
				Common.mask_currency();
				Common.init_select2(config.ui.select2);	
	      	});
		},

		getRelativePointerPosition: function(node) {
			// the function will return pointer position relative to the passed node
			var transform = node.getAbsoluteTransform().copy();
			// to detect relative position we need to invert transform
			transform.invert();
			// get pointer (say mouse or touch) position
			if(node.getStage()._pointerPositions.length==0)
				return false;	

			var pos = node.getStage().getPointerPosition();
			
			// now we find relative point
			return transform.point(pos);
		},

		buttons:function(){
			var centerControlDiv = document.createElement('div');
			var centerControlDiv2 = document.createElement('div');
			// var centerControlDiv4 = document.createElement('div');
			//var centerControlDiv3 = document.createElement('div');
			// _private.deleteButton(centerControlDiv);
			// _private.addMarkerButton(centerControlDiv2);
			// _private.addSelectMarkersButton(centerControlDiv4);
			//_private.saveLayerButton(centerControlDiv3);

			// $(config.ui.container).append(centerControlDiv);
			// $(config.ui.container).append(centerControlDiv2);
			// $(config.ui.container).append(centerControlDiv4);
			//$(config.ui.map_image_screen).append(centerControlDiv3);
		},
		
		set_tree_inventory_list:function(tree_list){
			markers_object = {};
			_private.calculate_tree_lists(tree_list, _private.render_tree_lists);
			_private.set_tree_inventory_map(tree_list);
		},

		update_inventory_list:function(tree_list, marker){
			_private.calculate_tree_lists(tree_list, _private.render_tree_lists);
			
			if(marker!=undefined){
				_private.placeMarker(marker.ti_lat, marker.ti_lng, marker, true);	
			}
		},

		set_tree_inventory_map:function(tree_list){
			$.each(tree_list, function(key, marker){
				_private.placeMarker(marker['ti_lat'], marker['ti_lng'], marker);
			});
		},

		calculate_tree_lists: function(tree_list, callback){
			markers_list = [];
			markers_object_db = {};
			
			$.each(tree_list, function(key, marker){
				marker['priority_color'] = TreeInventoryHelper.get_color(marker['ti_tree_priority'])
				marker['ti_cost'] = parseFloat(marker['ti_cost']);
				marker['ti_stump_cost'] = parseFloat(marker['ti_stump_cost']);
				marker['change_position'] = false;
				markers_object_db[marker.ti_lat+'_'+marker.ti_lng] = marker;
				markers_list.push(marker);
				
				_private.set_marker_form_object({ti_lat:marker.ti_lat, ti_lng:marker.ti_lng}, marker.ti_tree_number);
			});

			var totals = TreeInventoryHelper.get_totals(tree_list);

			if(callback !=undefined && callback!=false)
				callback(markers_list, totals);
		},

		render_tree_lists:function(markers_list, totals){
			var renderView = {template_id:config.templates.tree_list, empty_template_id:config.templates.tree_list_emp, view_container_id:config.views.tree_list, data:markers_list, helpers:TreeInventoryHelper.helpers};
			Common.renderView(renderView);

			var renderViewTable = {template_id:config.templates.tree_list_table, view_container_id:config.views.tree_list_table, data:markers_list, helpers:TreeInventoryHelper.helpers};
			Common.renderView(renderViewTable);

			var renderViewTotals = {template_id:config.templates.tree_list_table_totals, view_container_id:config.views.tree_list_table_totals, data:[totals], helpers:TreeInventoryHelper.helpers};
			Common.renderView(renderViewTotals);
		},

		placeMarker:function(layerY, layerX, marker) {
			
			var left = parseFloat((layerX!=undefined)?layerX:300);
			var top = parseFloat((layerY!=undefined)?layerY:300);
			var key = top+'_'+left;

			var color = window.priority_color['medium'];
			var pinLabel = (Object.keys(markers_object).length+1).toString();
			
			if(markers_object_db[key]!=undefined){
				pinLabel = markers_object_db[key]['ti_tree_number'];
				color = markers_object_db[key]['priority_color'];
			}

			if(markers_object[key] !=undefined && markers_object[key]['marker']!=undefined){
		   		markers_object[key]['marker'].destroy();
		   	}

		   	//var image = 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="36" height="56" version="1.1" ><circle cx="18" cy="18" r="18" stroke="'+color+'" stroke-width="0" fill="'+color+'"></circle><circle cx="18" cy="18" r="14" stroke="#fff" stroke-width="0" fill="#fff"></circle><text transform="translate(18 24)" fill="'+color+'" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="17" text-anchor="middle">'+pinLabel+'</text></svg>');
			var image = 'data:image/svg+xml;base64,' + btoa('<svg version="1.1" width="25" height="40" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 45.137 68.625" xml:space="preserve"><path fill="'+color+'" d="M22.569,0C10.103,0,0,10.104,0,22.568c0,7.149,3.329,13.521,8.518,17.654 c0.154,0.127,0.318,0.258,0.499,0.392c0.028,0.021,0.054,0.042,0.082,0.063c0.006,0.004,0.01,0.007,0.015,0.011 c8.681,6.294,13.453,27.938,13.453,27.938s4.03-20.621,11.407-26.585c6.679-3.921,11.163-11.17,11.163-19.472 C45.137,10.104,35.032,0,22.569,0z M22.569,38.129c-8.382,0-15.176-6.795-15.176-15.176c0-8.382,6.794-15.175,15.176-15.175 c8.381,0,15.174,6.793,15.174,15.175C37.743,31.334,30.95,38.129,22.569,38.129z"/><circle fill="#FFFFFF" cx="22.48" cy="23.043" r="16.27"/><text transform="translate(22.5 28)" fill="'+color+'" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="18" text-anchor="middle">'+pinLabel+'</text></svg>'); 
			Konva.Image.fromURL(image, imageNode => {
		        layer.add(imageNode);
		        
		        imageNode.setAttrs({
		          	width: 25,
		          	height: 40,
		          	draggable: true,
		          	dragBoundFunc: function(pos, pos2) {
			          	mouse_move = false;
			          	return {
			            	x: pos.x,
			            	y: pos.y,
			            	ti_lng:this.attrs.ti_lng,
			            	ti_lat:this.attrs.ti_lat,
			          	};
			        },
        			x: left,
        			y: top,
        			ti_lng:left,
        			ti_lat:top,
		        });

		        if(markers_object[key]==undefined)
		        	markers_object[key] = {'marker':{}, 'form':{}};

		        markers_object[key]['marker'] = imageNode;
				_private.set_marker_form_object({'ti_lat':top, 'ti_lng':left}, pinLabel);
				_private.marker_events(markers_object[key]);

		        layer.batchDraw();
		    });
		},

		setClientAddressMarker:function(position){
			/*
			var home = 'data:image/svg+xml;base64,' + btoa('<svg version="1.1" width="35px" height="35px" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512.533 512.533" style="enable-background:new 0 0 512.533 512.533;" xml:space="preserve"><path style="fill:#F3705B;" d="M406.6,62.4c-83.2-83.2-217.6-83.2-299.733,0c-83.2,83.2-83.2,216.533,0,299.733l149.333,150.4L405.533,363.2C488.733,280,488.733,145.6,406.6,62.4z"/><path style="fill:#F3F3F3;" d="M256.2,70.933c-77.867,0-141.867,62.933-141.867,141.867c0,77.867,62.933,141.867,141.867,141.867c77.867,0,141.867-62.933,141.867-141.867S334.066,70.933,256.2,70.933z"/><polygon style="fill:#FFD15D;" points="256.2,112.533 176.2,191.467 176.2,305.6 336.2,305.6 336.2,191.467 "/><g><rect x="229.533" y="241.6" style="fill:#435B6C;" width="54.4" height="64"/><path style="fill:#435B6C;" d="M356.466,195.733L264.733,104c-4.267-4.267-11.733-4.267-17.067,0l-91.733,91.733c-4.267,4.267-4.267,11.733,0,17.067c4.267,4.267,11.733,4.267,17.067,0l83.2-84.267l83.2,83.2c2.133,2.133,5.333,3.2,8.533,3.2c3.2,0,6.4-1.067,8.533-3.2C360.733,207.467,360.733,200,356.466,195.733z"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>');
		   	new google.maps.Marker({
		        position: position,
		        map: map,
		        draggable: false,
		        fillColor:'#ccc',
		        icon: home
		    });
		    */
		},

		marker_events:function(marker){
			marker['marker'].on('click', function(e){
	        	_private.render_infowindow(this);
	        });

	        marker['marker'].on('dragend', function(e){
	        	
	        	var key = this.attrs.ti_lat+'_'+this.attrs.ti_lng;
	        	var new_key = this.attrs.y+'_'+this.attrs.x;
	        	if(key==new_key)
	        		return; 

	        	if(markers_object[key]!=undefined){
	        		markers_object[key]['form'][0].ti_lat = this.attrs.y;
	        		markers_object[key]['form'][0].ti_lng = this.attrs.x;

	        		markers_object[key]['form'][0].ti_lat_last = markers_object[key]['form'][0].ti_lat;
	        		markers_object[key]['form'][0].ti_lng_last = markers_object[key]['form'][0].ti_lat;

	        		markers_object[new_key] = Object.assign({}, markers_object[key]); //JSON.parse(JSON.stringify(markers_object[key]));
	        		delete markers_object[key];
	        	}

	        	if(markers_object_db[key]!=undefined){
	        		
	        		markers_object_db[key]['change_position'] = true;
	        		markers_object_db[key].ti_lat = this.attrs.y;
	        		markers_object_db[key].ti_lng = this.attrs.x;
	        		markers_object_db[new_key] = Object.assign({}, markers_object_db[key]); //JSON.parse(JSON.stringify(markers_object_db[key]));
	        		
	        		delete markers_object_db[key];
	        	}

	        	_private.render_infowindow(this);
	        });
		},
		
		render_infowindow:function(marker){
			var key = marker.attrs.y+'_'+marker.attrs.x;
			config.ui.select2[0].values = markers_object[key]['form'][0]['work_types'];
			var renderView = {template_id:config.templates.infowindowform, view_container_id:config.views.infowindowform, data:markers_object[key]['form'], helpers:_private.helpers};
			Common.renderView(renderView);
			$(config.ui.modal).modal();  	
		},

		
		close_infowindow:function(){
			var key = $(this).find(config.ui.ti_lat).val()+'_'+$(this).find(config.ui.ti_lng).val();
			if(typeof markers_object_db[key] == 'undefined')
			{
				if(typeof markers_object[key] != 'undefined' && typeof markers_object[key]['marker'] != 'undefined'){
					markers_object[key]['marker'].destroy();
					layer.batchDraw();
					delete markers_object[key];
				}	
			}
			else{
				if(markers_object_db[key]['change_position'] == true){
					$(config.ui.modal+' form').trigger('submit');
					markers_object_db[key]['change_position']=false;
				}
			}
		},
		

		set_marker_form_object:function(position, pinLabel){
			
			var key = position.ti_lat+'_'+position.ti_lng;
			var lat = position.ti_lat;
			var lng = position.ti_lng;

			var lead = $(config.ui.lead_select).val();
			if(pinLabel == undefined)
				pinLabel = (Object.keys(markers_object).length+1).toString();

			if(markers_object_db[key]!=undefined){
				
				var work_types = markers_object_db[key]['work_types'].map(function(currentValue, index, array){
					return parseInt(currentValue['tiwt_work_type_id']);
				});
				
				if(markers_object[lat+'_'+lng]==undefined)
					markers_object[lat+'_'+lng] = {};

		    	markers_object[key]['form'] = [{
		    		'ti_id':markers_object_db[key]['ti_id'],
			    	'ti_lat':markers_object_db[key]['ti_lat'],
			    	'ti_lng':markers_object_db[key]['ti_lng'],
			    	'ti_lead_id':markers_object_db[key]['ti_lead_id'],
			    	'ti_tree_number':markers_object_db[key]['ti_tree_number'],
			    	'ti_tree_type':markers_object_db[key]['ti_tree_type'],
			    	'ti_tree_priority':markers_object_db[key]['ti_tree_priority'],
			    	'ti_prune_type_id':markers_object_db[key]['ti_prune_type_id'],
			    	'work_types':work_types,
			    	'ti_remark':markers_object_db[key]['ti_remark'],
					'ti_title':markers_object_db[key]['ti_title'],
					'ti_size':markers_object_db[key]['ti_size'],
					'ti_cost':markers_object_db[key]['ti_cost'],
					'ti_stump_cost':markers_object_db[key]['ti_stump_cost'],
					'ti_map_type':window.ti_map_type,
			    }];
		    }
		    else{
		    	markers_object[key]['form'] = [{
			    	'ti_lat':position.ti_lat,
			    	'ti_lng':position.ti_lng,
			    	'ti_lead_id':lead,
			    	'ti_tree_number':pinLabel,
			    	'ti_tree_type':'',
			    	'ti_tree_priority':'',
			    	'ti_prune_type_id':'',
			    	'work_types':[],
			    	'ti_remark':'',
					'ti_title':'',
					'ti_size':'',
					'ti_cost':'',
					'ti_stump_cost':'',
					'ti_map_type':window.ti_map_type,
			    }];
		    }
		},

		addMarkerButton:function(controlDiv){
			// Set CSS for the control border.
			var controlUI = document.createElement('button');
			controlUI.className = 'btn btn-danger absolute';
			controlUI.style.fontSize = '18px';
			controlDiv.appendChild(controlUI);
			controlUI.style.left = '10px';
			controlUI.style.top = '10px';
			// Set CSS for the control interior.
			var controlText = document.createElement('i');
			controlText.className = 'fa fa-plus';
			controlText.innerHTML = '';  //&nbsp;Add marker
			controlUI.appendChild(controlText);
			// Setup the click event listeners: simply set the map to Chicago.
			controlUI.addEventListener('click', function() {

				if(newScale==undefined)
					newScale = 1;

				var ti_lat = $(config.ui.container).height()/newScale/4;
				var ti_lng = $(config.ui.container).width()/newScale/2;

				_private.placeMarker(ti_lat, ti_lng);
				setTimeout(function(){
					var key = ti_lat+'_'+ti_lng;
			    	_private.render_infowindow(markers_object[key]['marker']);
			    }, 200);
			});
			
		},
		
		deleteButton:function(controlDiv){
			// Set CSS for the control border.
			let controlUI = document.createElement('button');
			controlUI.className = 'btn btn-danger absolute';
			controlUI.style.fontSize = '18px';
			controlUI.style.right = '20px';
			controlUI.style.top = '10px';
			controlDiv.appendChild(controlUI);

			// Set CSS for the control interior.
			let controlText = document.createElement('i');
			controlText.className = 'fa fa-trash-o';
			
			controlText.innerHTML = ''; //&nbsp;Delete Map
			controlUI.appendChild(controlText);

			controlUI.addEventListener('click', function() {
		    	$(config.ui.delete_map_form).trigger('submit');
			});
		},

	}
	
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
		
		events:function(){
			
			$(config.ui.modal).on('hide.bs.modal', _private.close_infowindow);

			$(document).delegate(config.events.edit_tree, 'click', public.edit_tree);
			$(config.events.tree_inventory_modal).on('show.bs.modal', public.saveMap);

			$(document).delegate('[name="copy_to_lead"]', "change", public.select_copy_lead);
		},
		saveMap:function(){
			
			html2canvas($(config.ui.map_image_screen_container+">div"), {
			    useCORS:true,
			    windowWidth:20,
			    windowHeight: 10,
			    onrendered: function(canvas) {
			        var uridata = canvas.toDataURL("image/png");
			       	
			       	var renderScreen = {template_id:config.templates.map_screen, view_container_id:config.views.map_screen, data:[{map_image:uridata}], helpers:_private.helpers};
					Common.renderView(renderScreen);

					var renderScreenForm = {template_id:config.templates.map_screen_form, view_container_id:config.views.map_screen_form, data:[{map_image:uridata}], helpers:_private.helpers};
					Common.renderView(renderScreenForm);
			    }
			});

			$('img[data-toggle="popover"]').popover();
			
		},

		edit_tree:function(e){
			
			if($(e.target).closest('form').length!=0 && $(e.target).closest('form').attr('class')==config.events.delete_formClass)
				return;

			var data = $(this).data();
			if(markers_object[data.ti_lat+'_'+data.ti_lng]==undefined)
				return false;

			_private.render_infowindow(markers_object[data.ti_lat+'_'+data.ti_lng]['marker']);
			
		},

		open_pdf:function(response){

			
			const a = document.createElement('a');
		    a.href = base_url+response.link;
		    a.target = '_blank';
		    document.body.appendChild(a);
		    a.click();
		    document.body.removeChild(a);
		    
			
		},

		delete_callback:function(response){
			
			if(response.status!='ok')
				return;

			var key = response.response.deleted.ti_lat+'_'+response.response.deleted.ti_lng;
			
			markers_object[key]['marker'].destroy();
			layer.batchDraw();
			delete markers_object[key];
			$(config.ui.modal).modal("hide");

			_private.update_inventory_list(response.response.tree_inventory);
		},

		save_callback:function(response){
			if(response.status!='ok')
				return;

			$(config.ui.modal).modal("hide");
			_private.update_inventory_list(response.response.tree_inventory, response.marker);
		},

		set_tree_inventory_list:function(response){
			/*
			if(response.status!='ok')
				return;
				
			$.each(markers_object, function(key, value){ value['marker'].setMap(null); });
			
			$(config.events.close_infowindow).trigger('click');
			_private.set_tree_inventory_list(response.response.tree_inventory);
			*/
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

		init_map:function(response){
			
			if(response.status = 'ok'){
				
				$(config.ui.map_image_screen_container).data('background', response.response.map_image);
				$(config.ui.map_image_screen_container).attr('style', 'background: url('+response.response.map_image+') no-repeat; background-size: cover;');
				
				_private.init_canvas_map();
				_private.buttons();
				_private.events();
				
				_private.set_tree_inventory_list(response.response.tree_inventory);
			}

		}


	}

	public.init();
	return public;
}();
