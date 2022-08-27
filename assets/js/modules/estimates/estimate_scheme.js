
/*  ------ EstimateScheme -----  */


var EstimateScheme = {
	
	map_screen_link:null, 

	config:{
		marker_icon: 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="25" height="52" viewBox="0 0 38 38"><path fill="#FD7567" stroke="#000" stroke-width="2" d="M34.305 16.234c0 8.83-15.148 19.158-15.148 19.158S3.507 25.065 3.507 16.1c0-8.505 6.894-14.304 15.4-14.304 8.504 0 15.398 5.933 15.398 14.438z"/><text transform="translate(19 25)" fill="#000" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="20" text-anchor="middle">&#9899;</text></svg>'),
		map_selector:"map-live-canvas",
		lat_selector:"#scheme_lat",
		lng_selector:"#scheme_lon",
		defaultZoom:100
	},

	scheme_map:null, // Global declaration of the map
	markerObj:{},
	myLatlng: {},
	markers: new Array(),
	map_comments: {},

	init:function(){
		this.setEstimateSchemeSource();
		this.initMap();
	},
		
	setEstimateSchemeSource:function(){
		this.estimate_scheme_source = document.getElementById('estimate_scheme_source').value;
	},

	setLatLng:function(){
		lat = $(this.config.lat_selector).val();
		lng = $(this.config.lng_selector).val();
		this.myLatlng = (lat && lng) && (lat != '0' && lng != '0') ? new google.maps.LatLng(lat, lng) : false;
	},

	initMap:function(){
		console.log(this);
		var $this = this;
		this.setLatLng();
		zoom = this.config.defaultZoom;

		var myOptions = {
	  		zoom: zoom,
	  		tilt:0,
	  		mapTypeId: 'satellite',
	  		gestureHandling: 'greedy'
	  	}
	  	
	  	if(this.myLatlng) {
			myOptions.center = this.myLatlng;
		}
		else {
			var address = destination;
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode({'address': address}, function(results, status) {
				if (status === 'OK' && $this.scheme_map) {
					$this.scheme_map.setCenter(results[0].geometry.location);
					var marker = new google.maps.Marker({
						map: $this.scheme_map,
						position: results[0].geometry.location
					});
				}
			});
		}

		this.scheme_map = new google.maps.Map(document.getElementById(this.config.map_selector), myOptions);

		/* ----------- add custom button to map ----------*/
		var centerControlDiv = document.createElement('div');
  		var centerControl = new CenterControl(centerControlDiv, this.scheme_map, 'Create Screen');
  		this.scheme_map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv);
		/* ----------- add custom button to map ----------*/

		this.createMarker();

		if(this.estimate_scheme_source!=undefined && this.estimate_scheme_source/* && isJsonString(this.estimate_scheme_source)*/){

			data = JSON.parse(this.estimate_scheme_source);
			this.map_screen_link = data.link;
			$('#container').append(data.html);

			$('#'+this.config.map_selector).hide();
			$('#map-canvas').show();

			/*$('.kineticjs-content').css({'background-image':"url('"+data.link+"')"});
			$('.kineticjs-content').css({'background-repeat':'no-repeat'});
			$('.kineticjs-content').css({'background-size':'100%'});
			*/
			$('.map_screen').remove();
			$('.kineticjs-content').css('width', '100%');
			$('.kineticjs-content').prepend('<img class="map_screen" src="'+baseUrl+data.link+'"/>');
			this.editMapButton();
			this.screenMapButton();

			this.createMapScreen();
			
			$('#container .imag').draggable({
			    cursor: 'pointer',
			    revert: 'invalid',
			});

			$('#container .imag').on('dblclick', function() {
		        this.remove();
		        layer.draw();
		        $this.createMapScreen();
		    });
		}

	},

	saveMap:function(){
		var obj = this;
		html2canvas($('#map-live-canvas .gm-style div[tabindex="0"]'), {
			useCORS:true,
			windowWidth:20,
			windowHeight: 10,
			onrendered: function(canvas) {
					var uridata = canvas.toDataURL("image/png");
					data = {
						image:uridata,
	                	client_id:$('[name="client_id"]').val(),
	                	lead_id:$('[name="lead_id"]').val(),
	                	source:1
					};
					obj.presaveScreenSource(obj, data);
				}
			});

		
        	
		/* ----------- add custom button to map ----------*/
	},

	presaveScreenSource:function(sel, dat){
		var obj = sel;
		var data = dat;

		$('#'+obj.config.map_selector).hide();
		$('#map-canvas').show();

		$('.map_screen').remove();
		$('.kineticjs-content').css('width', '100%');
		$('.kineticjs-content').parent().find('.imag').remove();
		$('.kineticjs-content').prepend('<img class="map_screen" src="' + data.image + '"/>');

		obj.editMapButton();
		obj.screenMapButton();

		//obj.createMapScreen();

		$.ajax({
			url: baseUrl + 'estimates/ajax_presave_scheme',
			data: data,
			method: "POST",
			global: false,
			success: function(resp){
				/*$('#'+obj.config.map_selector).hide();
				$('#map-canvas').show();

				obj.map_screen_link = resp.path;

				$('.map_screen').remove();
				$('.kineticjs-content').css('width', '100%');
				$('.kineticjs-content').parent().find('.imag').remove();
				$('.kineticjs-content').prepend('<img class="map_screen" src="' + resp.path + '"/>');

				obj.editMapButton();
				obj.screenMapButton();

				obj.createMapScreen();*/
				$('.kineticjs-content').find('.map_screen').attr('src', resp.path);
				successMessage('Success!');
			},
			dataType: 'json'
		});
	},

	screenMapButton: function(){
		$this = this;
		if($(document).find("#create-map-screen").length==0){
			controlUI = document.querySelector('#map-canvas');
			var controlText = document.createElement('div');
			controlText.style.color = '#3c3b3b !important';
			
			controlText.id = "create-map-screen"; 
			controlText.style.paddingTop = '7px';
			controlText.style.paddingLeft = '8px';
			controlText.style.paddingBottom = '7px';
			controlText.style.paddingRight = '8px';      
			controlText.style.border="none";
			controlText.style.width="auto";
			controlText.style.position="absolute";
			controlText.style.top="43px";
			controlText.style.left="80px";
			controlText.className="btn btn-success"; /*glyphicon glyphicon-floppy-disk*/
			controlText.innerHTML = 'Save Scheme';
			controlText.setAttribute('data-original-title', 'Click Here To Save');
			controlUI.appendChild(controlText);
			$('#create-map-screen').tooltip({placement: 'top', trigger: 'manual'}).tooltip('show');
			$('#map-canvas .tooltip').css('z-index', 1);
			$('#create-map-screen').click(function(){
				$this.createMapScreen(true);
			});		
		}
	},

	editMapButton: function(){

		$this = this;
		if($(document).find("#close-map-screen").length==0){
			controlUI = document.querySelector('#map-canvas');
			var controlText = document.createElement('div');
			controlText.style.color = '#3c3b3b !important';
			//controlText.style.backgroundColor = '#fff';
			
			controlText.id = "close-map-screen"; 
			controlText.style.paddingTop = '7px';
			controlText.style.paddingLeft = '8px';
			controlText.style.paddingBottom = '7px';
			controlText.style.paddingRight = '8px';      
			controlText.style.border="none";
			controlText.style.width="auto";
			controlText.style.position="absolute";
			controlText.style.top="43px";
			controlText.style.left="3px";
			controlText.className="btn btn-info"; /*glyphicon glyphicon-floppy-disk*/
			controlText.innerHTML = 'Edit Map';
			controlUI.appendChild(controlText);		
			$this = this;
			
			$('#close-map-screen').click(function(){
				$('.createScreen').parent().removeClass('disabled');
				$('#'+$this.config.map_selector).show();
				$('#map-canvas').hide();
			});		
		}
	},

	createMarker:function(latlng) {
		
		if(latlng==undefined)
			latlng = this.myLatlng;
		
		if(this.markerObj.length)
			latlng = new google.maps.LatLng(this.markerObj.position.lat(), this.markerObj.position.lng());
		
		if(!latlng)
			return false;
		
		var markerOptions = {
			map: this.scheme_map,
			position: latlng,
			icon: this.config.marker_icon,
			animation: google.maps.Animation.DROP,
			draggable: true
		};

		this.markerObj = new google.maps.Marker(markerOptions);
		this.markers.push(this.markerObj);
	},

	createMapScreen:function(click){
		var myImage = '';
		var $this=this;
		var isClick=click;
		if(isClick) {
			html2canvas($("#container .kineticjs-content").parent(), {
				useCORS: true,
				windowWidth:20,
				windowHeight: 10,
				onrendered: function(canvas) { 
					myImage = canvas.toDataURL("image/png"); 
					
					imgHtml = '';
					img = $("#container .imag");

	                $.each(img, function(i, v){  imgHtml += $.trim(v.outerHTML); });
	                
					data = {
	                	image:myImage,
	                	html:imgHtml,
	                	client_id:$('[name="client_id"]').val(),
	                	estimate_id:$('[name="estimate_id"]').val(),
	                	lead_id:$('[name="lead_id"]').val()
	                };
					$this.presaveScreen(data);
				}
			});
		}
	},

	presaveScreen:function(data){
		$.ajax({
			url: baseUrl + 'estimates/ajax_presave_scheme',
			data: data,
			method: "POST",
			global: false,
			success: function(resp){
				successMessage('Success!');
				$('#estimateForm').find('.scheme-block').hide('slow');
				$('#estimateForm').find('.scheme-block').parent().hide('slow');
				$('.showHideScheme').find('i').removeClass('fa-minus');
				$('.showHideScheme').find('i').addClass('fa-plus');
			},
			dataType: 'json'
		});
	},
}
/*   ------ END EstimateScheme ------   */


/*   ------ field in MAP ------   */
function isJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function CenterControl(controlDiv, map, textContent=null) {
	controlDiv.style.left='30px';
  	// Set CSS for the control border.
  	var controlUI = document.createElement('div');

  	controlUI.disabled = '';
  	controlUI.style.borderRadius = '3px';
	controlUI.style.position="absolute";
	controlUI.style.top="10px";
	controlUI.style.right="100px";
  	controlUI.style.cursor = 'pointer';
  	controlUI.title = 'Click to add marker';
  
  	controlDiv.appendChild(controlUI);

	// Set CSS for the control interior.
	var controlText = document.createElement('div');
	controlText.style.color = '#3c3b3b !important';
	controlText.style.backgroundColor = '#fff';
	 
	controlText.style.paddingTop = '7px';
	controlText.style.paddingLeft = '8px';
	controlText.style.paddingBottom = '7px';
	controlText.style.paddingRight = '8px';      
	controlText.style.border="none";
	controlText.style.width="auto";
	controlText.className="btn btn-default createScreen"; /*glyphicon glyphicon-floppy-disk*/
	controlText.innerHTML = textContent;

	controlUI.appendChild(controlText);
	  // Setup the click event listeners: simply set the map to Chicago.
	controlUI.addEventListener('click', function() {
		if(!$(this).is('.disabled')) {
			$(this).addClass('disabled');
			EstimateScheme.saveMap();
		}
	});

}


/*-----------------------Events------------------------*/
$(document).on('click', '.showHideScheme', function(){
	$('.createScreen').removeClass('disabled');
	
	$('#estimateForm').find('.scheme-block').toggle('slow', function(){ 
		EstimateScheme.init();
	});

	if($(this).find('i').hasClass('fa-plus')){
		$('#estimateForm').find('.scheme-block').parent().toggle('slow');
		$(this).find('i').removeClass('fa-plus');
		$(this).find('i').addClass('fa-minus');
	}
	else{
		$(this).find('i').removeClass('fa-minus');
		$(this).find('i').addClass('fa-plus')
		$('#estimateForm').find('.scheme-block').parent().toggle('slow');
	}
	
	return false;
});
/*-----------------------Events------------------------*/

