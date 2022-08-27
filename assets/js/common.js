var base_url = location.protocol + '//' + location.host + '/';
var addressAuto = [];
var refreshIntervalId = false;
var pageTimeout = typeof(activityTimeout) == 'undefined' ? false : activityTimeout;
var logoutTimer = 60 * 1000;//msec
var timerReserve = 30 * 1000;//msec
var vehLabels = [];
//========

(function(){
    if((location.href.indexOf(base_url + 'iframe') + 1) && !inIframe())
    {
        var segments = location.pathname.split('/');
        var url = '';
        $.each(segments, function(key, val){
            if(val && val != 'iframe')
                url += val + '/';
        });
        $(location).attr('href', base_url + url);
    }
})(window, document);

jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

function inIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

function openPopup(id) {
    $('#' + id).show();
}

function hidePopup(id) {
    $('#' + id).hide();
}

function showLoader(f) {
    $("#loader").remove();
    var loading = $("<div></div>");
    $(loading).attr("id", "loader");
    $(loading).addClass("loader");
    $(loading).html("&nbsp;");
    $(loading).insertAfter($("#" + f));
}

function hideLoader() {
    $("#loader").remove();
}

function encrypt(str) {
    return stringToHex(str);
}


////////////////////////////// TEST //////////////////////////////
function stringToHex(s) {
    var r = "0x";
    var hexes = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");
    for (var i = 0; i < s.length; i++) {
        r += hexes [s.charCodeAt(i) >> 4] + hexes [s.charCodeAt(i) & 0xf];
    }
    return r;
}

$(document).ready(function () {

	$(document).on('focusin', function(e) {
		if ($(e.target).closest(".mce-window").length) {
			e.stopImmediatePropagation();
		}
	});

	if($('.numeric').length){
		$('.numeric').inputmask({
			alias: 'numeric',
			allowMinus: false,
			digits: 2,
			rightAlign: false
		});
	}


	$('.modal').on('shown.bs.modal', function() {
		$('html').css({overflow: 'hidden'}); 
	});
	$('.modal').on('hidden.bs.modal', function() {
		$('html').css({overflow: 'auto'}); 
	});
	
    $('[data-toggle="modal"]').not('[data-backdrop]').not('[data-keyboard]').attr({'data-backdrop': 'static', 'data-keyboard': 'false'});//Disable click outside and press esc of bootstrap model area to close modal
    if($('.important-note').length)
        $('.important-note-indicator').fadeIn();
    
    
    if($('[data-autocompleate]').length)
    {
        $.each($('[data-autocompleate]'), function(key, val){

            addressAuto.push(new google.maps.places.Autocomplete(
                ($(val)[0]),
                { types: ['geocode'] , componentRestrictions: {country: AUTOCOMPLETE_RESTRICTION}}
            ));

            var parentSelector = 'form';
            if($(val).data('parent-selectror'))
            	parentSelector = $(val).data('parent-selectror');

            google.maps.event.addListener(addressAuto[key], 'place_changed', function () {
            	
                fillInAutocompleteAddress(addressAuto[key], val, parentSelector);
            	
                Common.callback_checker(val, addressAuto[key]);
            	
            });

        });
    }
    
    
    $('.important-note-indicator').click(function(){
        $('.scrollable.padder:first').animate({
                scrollTop: $(".important-note:first").offset().top - $('.breadcrumb.no-border.no-radius.b-b.b-light.pull-in').offset().top - $('.breadcrumb.no-border.no-radius.b-b.b-light.pull-in').outerHeight() - 150},
            'slow');
        return false;
    });
    
    $(document).on('click', '.save_card_edit', function () {
    	var obj = $(this).parents('.ccBlock:first');
    	var form = $(this).parents('form:first')
		crd_name = $(obj).find('[name="crd_name"]').val(); 
		crd_exp_month = $(obj).find('[name="crd_exp_month"]').val();
		crd_exp_year = $(obj).find('[name="crd_exp_year"]').val();
		crd_number = $(obj).find('[name="crd_number"]').val();
		crd_cvv = $(obj).find('[name="crd_cvv"]').val();
		client_id = $(obj).find('[name="client_id"]').val();
		$.post(baseUrl + 'clients/ajax_save_billing', {client_id: client_id, crd_name: crd_name, crd_exp_month: crd_exp_month, crd_exp_year: crd_exp_year, crd_number: crd_number, crd_cvv: crd_cvv}, function (resp) {
			$('.add_cc_block').slideToggle(); 
			if(ccTpls != undefined && ccTpls['tpl_' + client_id] != undefined) {
				$('.fu_cc_block').slideToggle(10, function() {Popup.setPosition()});
				var tmpDiv = document.createElement('div');
	        	$(tmpDiv).append(ccTpls['tpl_' + client_id]);
	        	$(tmpDiv).find('#crd_name').attr('value', crd_name);
	        	//$(tmpDiv).find('#card_type option[selected]').removeAttr('selected');
	        	//$(tmpDiv).find('#card_type option[value="' + card_type + '"]').attr('selected', 'selected');
	        	$(tmpDiv).find('#crd_exp_month option[selected]').removeAttr('selected');
	        	$(tmpDiv).find('#crd_exp_month option[value="' + crd_exp_month + '"]').attr('selected', 'selected');
	        	$(tmpDiv).find('#crd_exp_year option[selected]').removeAttr('selected');
	        	$(tmpDiv).find('#crd_exp_year option[value="' + crd_exp_year + '"]').attr('selected', 'selected');
	        	$(tmpDiv).find('#crd_number').attr('value', crd_number);
	        	$(tmpDiv).find('#crd_cvv').attr('value', crd_cvv);
	        	ccTpls['tpl_' + client_id] = $(tmpDiv).html();
			}
			//if(resp.status == 'ok' && resp.html != undefined)
		 
			if ($(form).find('#payment_method').children('[value="cc"]').length < 1) {
				if (crd_number) {
					$('#payment_method').append('<option value="cc">Credit Card</option>');
					//$('#add_cr_card').text('Edit card');
				}
			}
			
			if (!crd_number) {
				$('#payment_method').children('option[value="cc"]').remove();
				$('#payment_method').change();
			}
			else if(crd_number && $(form).find('#payment_method').length)
			{
				$('#payment_method').val('cc');
				$('#payment_method').change();
				cc_select = $(form).find('.credit_card');
				$(cc_select).removeClass('hide');
				$(cc_select).find('[name="credit_card"]').append('<option value="'+ resp.card_id +'">'+ resp.card_mask +'</option>');
				$(cc_select).find('[name="credit_card"]').val(resp.card_id);
			}
			if ($(form).find('.payment_method1').children('[value="cc"]').length < 1) {
				if (crd_number) {
					$('.payment_method1').append('<option value="cc">Credit Card</option>');
					//$('#add_cr_card').text('Edit card');
				}
			}
			
			if (!crd_number) {
				$('.payment_method1').children('option[value="cc"]').remove();
				$('.payment_method1').change();
			}
			else if(crd_number && $(form).find('.payment_method1').length)
			{
				$('.payment_method1').val('cc');
				$('.payment_method1').change();
				cc_select = $(form).find('.credit_card');
				$(cc_select).removeClass('hide');
				$(cc_select).find('[name="credit_card"]').append('<option value="'+ resp.card_id +'">'+ resp.card_mask +'</option>');
				$(cc_select).find('[name="credit_card"]').val(resp.card_id);
			}
			if ($(form).find('.edit_payment_method').children('[value="cc"]').length < 1) {
				console.log(7);
				if (crd_number) {
					$(form).find('.edit_payment_method').append('<option value="cc">Credit Card</option>');
					 
					//$('#add_cr_card').text('Edit card');
				}
			}
			if (!crd_number) {
				console.log(8);
				$(form).find('.edit_payment_method').children('option[value="cc"]').remove();
				$(form).find('.edit_payment_method').change();
			}
			else if(crd_number && $(form).find('.edit_payment_method').length)
			{
				console.log(9);
				$(obj).parents('form:first').find('.edit_payment_method').val('cc');
				$(obj).parents('form:first').find('.edit_payment_method').change();
				cc_select = $(form).find('.credit_card');
				console.log(cc_select);
				$(cc_select).removeClass('hide');
				$(cc_select).find('[name="credit_card"]').append('<option value="'+ resp.card_id +'">'+ resp.card_mask +'</option>');
				$(cc_select).find('[name="credit_card"]').val(resp.card_id);
			}
		}, 'json');
	});
    
});

$(document).on('submit', 'form[data-type="ajax"]', function(e, t){
	//console.log(DROPZONE_FILES); return false;
    var valid = true;
    var form = $(this);
    var formData = new FormData($(this)[0]);
    var url = $(this).data('url') ? $(this).data('url') : location.href;
    var data = $(this).serialize();
	
    var countFiles = 0;
    $.each(document.querySelectorAll("input[type=file]"), function(){
    	countFiles += $(this)[0].files.length;
    });
   
    if(typeof(DROPZONE_FILES) != 'undefined') {
		Object.keys(DROPZONE_FILES).map(function(inputName, index) {
			
			Object.keys(DROPZONE_FILES[inputName]).map(function(objectKey, index) {
				$.each(DROPZONE_FILES[inputName][objectKey], function(key, val) {
					formData.append(inputName + '[' + objectKey + '][]', val, val.name);
					countFiles += 1;
				});
			});
		});
	}

	
	if(form.find('input[data-type="file"]').length){
		$.each(form.find('input[data-type="file"]'), function(key, val){
			image = $(this).val();
			name = $(this).attr('data-name');
            if(image){
                var block = image.split(";");
                var contentType = block[0].split(":")[1];// In this case "image/gif"
                var realData = block[1].split(",")[1];// In this case "R0lGODlhPQBEAPeoAJosM...."
                var blob = Common.b64toBlob(realData, contentType);

                formData.append(name, blob, 'image_'+key);    
            }
			
		});	
	}
	

    var successLocation = $(this).data('location') ? $(this).data('location') + location.hash : false;
    var noHideProcessing = $(this).data('processing') ? $(this).data('show_processing') : false;
    var callback = $(this).data('callback') ? $(this).data('callback') : false;
    var before = $(this).data('before') ? $(this).data('before') : false;
    var msgTimeout = ($(this).data('msg-timeout') !== undefined) ? $(this).data('msg-timeout') : false;
    var globalOption = ($(this).data('global') !== undefined) ? $(this).data('global') : true;

    $(form).find('[data-toggle="tooltip"]').tooltip('destroy');
    $(form).find('[data-toggle="tooltip"]').attr('data-original-title', '');

    $.each($(form).find('[name][type]'), function(key, val) {
        $(val).parents('.form-group:first,.control-group:first').removeClass('has-error');
    });

    if(before)
    {
		valid = Common.callback_checker(form, form, 'before');
	}
	$(form).find('.form-error').text('');

    if(valid)
    {
    	if(globalOption==true)
        	$('#processing-modal').modal();

        var params = {
			url: url,
			headers : { "cache-control": "no-cache" },
			data: countFiles ? formData : data,
			method: "POST",
			global: globalOption,
			success: function(resp){
				if(callback)
				{
					Common.callback_checker(form, resp);
				}
				if(resp.status == 'ok')
				{
					if(successLocation)
					{
						if(resp.msg)
						{
							if(globalOption==true)
								$('#processing-modal').modal('hide');
							successMessage(resp.msg);
							var timeout = (msgTimeout !== false) ? (msgTimeout * 1000) : 3000;
							setTimeout(function(){location.href = successLocation;}, timeout);
						}
						else
						{
							if(location.href == successLocation)
								location.reload();
							else
								location.href = successLocation;
						}
					}
					else
					{
						if(resp.url)
							location.href = resp.url;
						if(resp.msg)
							successMessage(resp.msg);
						if(!noHideProcessing && globalOption==true)
							$('#processing-modal').modal('hide');
					}
				}
				else
				{
					if(resp.msg)
					{
						errorMessage(resp.msg);
					}
					if(resp.errors)
					{
						$.each(resp.errors, function(key, val) {
							$(form).find('[name="' + key + '"]').attr('data-original-title', val).parents('.form-group:first').addClass('has-error');
							$(form).find('[name="' + key + '"]').attr('data-original-title', val).parents('.control-group:first').addClass('has-error');

							if($(form).find('[name="' + key + '"]').closest('.form-group').find('.form-error')!=undefined)
								$(form).find('[name="' + key + '"]').closest('.form-group').find('.form-error').text(val);

							if($(form).find('[name="' + key + '"]').closest('.controls').find('.form-error')!=undefined)
								$(form).find('[name="' + key + '"]').closest('.controls').find('.form-error').text(val);

						});
						setTimeout(function(){$(form).find('[data-toggle="tooltip"]').tooltip('show');$('#processing-modal').modal('hide');}, 500);
					}
					if(globalOption==true)
						$('#processing-modal').modal('hide');
				}
				return false;
			},
			beforeSend: function( xhr ) {
				if(globalOption==true)
					$('#processing-modal').modal();
			},
			dataType: 'json'
		}

		if(countFiles) {
			params.processData = false;
			params.contentType = false;
			params.cache = false;
		}

		$.ajax(params).fail(function(e,p,t){if(globalOption==true){$('#processing-modal').modal('hide');} console.log(e, p, t); return false;});
	}
	else
	{
		if(globalOption==true)
			$('#processing-modal').modal('hide');
	}
    return false;
});
$(document).on('click', '[data-toggle="modal"]', function(){
    if($(this).attr('href').indexOf(location.href) === -1 && $(this).attr('href').indexOf(location.protocol + '//') !== -1)
        location.href = $(this).attr('href');
    return false;
});

$(document).on('click', '.deleteEstimatePhotoClass', function(){
	var obj = $(this);
	var estimate_id = $(obj).attr('data-estimate_id');
	var path = $(obj).attr('data-path');
	if(confirm('Are you sure?'))
	{
		$.ajax({
			method: "POST",
			data: {estimate_id:estimate_id,path:path},
			url: base_url + "estimates/deleteFile",
			dataType:'json',
			success: function(response){
				if(response.type != 'ok')
					alert(response.message);
				else
				{
					$("#fileToUpload").val('');
					$(obj).parents('tr:first').remove();
				}
					
			}
		});
	}
	return false;
});

function fillInAutocompleteAddress(auto, obj, parentSelector) {
    var place = auto.getPlace();
    var form = $(obj).parents(parentSelector + ':first');
    $(form).find('[data-part-address]').val('');

    $.each(place.address_components, function(key, val){
		//console.log(place.address_components); return false;
        if($(form).find('[data-part-address="' + val.types[0] + '"]').length || (val.types.length > 1 && $(form).find('[data-part-address="' + val.types[1] + '"]').length)) {
			$(form).find('[data-part-address="' + val.types[0] + '"]').val(val.long_name);
			if(val.types.length > 1 && $(form).find('[data-part-address="' + val.types[1] + '"]').length)
				$(form).find('[data-part-address="' + val.types[1] + '"]').val(val.long_name);
		}
        if((val.types[0] == 'street_number' || val.types[0] == 'route' || val.types[0] == 'sublocality_level_1' || val.types[0] == 'sublocality') && $(form).find('[data-part-address="address"]').length)
        {
        	if(val.types[0] == 'street_number' || val.types[0] == 'route') {
				$(form).find('[data-part-address="address"]').val($(form).find('[data-part-address="address"]').val() + ' ' + val.long_name);
			} else {
				$(form).find('[data-part-address="address"]').val($(form).find('[data-part-address="address"]').val() + ', ' + val.long_name);
			}
            $(form).find('[data-part-address="address"]').val($.trim($(form).find('[data-part-address="address"]').val()));
        }
		if(val.types[0] == 'administrative_area_level_1')
		{
			$(form).find('[data-part-address="administrative_area_level_1"]').val(val.short_name);
		}

		if(val.types[0] == 'administrative_area_level_1' && val.types[1]!="sublocality")
		{
			if($(form).find('[data-part-address="state"]').length)
				$(form).find('[data-part-address="state"]').val(val.short_name);
		}
		if(val.types[0] == 'country')
		{
			if($(form).find('[data-part-address="country"]').length)
				$(form).find('[data-part-address="country"]').val(val.long_name);
		}

    });
    //place.formatted_address

    if($(form).find('[data-part-address="locality"]').length && !$(form).find('[data-part-address="locality"]').val() && place["vicinity"] != undefined) {
		$(form).find('[data-part-address="locality"]').val(place.vicinity);
	}

    if($(form).find('[data-part-address="lat"]').length)
    {
        $(form).find('[data-part-address="lat"]').val(place.geometry.location.lat);
        $(form).find('[data-part-address="lon"]').val(place.geometry.location.lng);
    }

    if($(form).find('[data-part-address="formatted_address"]').length)
    {
    	$(form).find('[data-part-address="formatted_address"]').val(place.formatted_address);
    }
    

    return true;
}

function autocompleteToInput(input) {

	var parentSelector = 'form';
   	if($(input).data('parent-selectror'))
    	parentSelector = $(input).data('parent-selectror');

	addressAuto.push(new google.maps.places.Autocomplete(
		(input[0]),
		{ types: ['geocode'] , componentRestrictions: {country: AUTOCOMPLETE_RESTRICTION} }
	));
	google.maps.event.addListener(addressAuto[addressAuto.length - 1], 'place_changed', function () {
		fillInAutocompleteAddress(addressAuto[addressAuto.length - 1], input, parentSelector);
	});
}

$(document).on('click', '#autoLogout', function() {
	$('#autologoutModal').modal('hide');
	clearInterval(refreshIntervalId);
	$.ajax({
		method: "POST",
		url: baseUrl + "dashboard/ajax_counters",
		dataType: "json",
		global: false,
		success: function(response){
			autoLogoutTimeout((pageTimeout - logoutTimer - timerReserve) - (new Date().getTime() - parseInt($.cookie('lastActivity'))));//1740000
		}
	});
});

function autoLogoutTimeout(timeout) {
	if(location.pathname != "/screen") {
		setTimeout(function() {
			var currentTime = new Date().getTime();
			if(currentTime - parseInt($.cookie('lastActivity')) >= (pageTimeout - logoutTimer - timerReserve)) {//1800
				var timer = logoutTimer / 1000;
				$('#autologoutModal').find('#logoutTimer').html(logoutTimer / 1000);
				$('#autologoutModal').modal();
				if($('#autologoutModal').is(':visible'))
					return false;
				refreshIntervalId = setInterval(function() {
					if(currentTime - parseInt($.cookie('lastActivity')) < (pageTimeout - logoutTimer - timerReserve)) {
						$('#autologoutModal').modal('hide');
						clearInterval(refreshIntervalId);
						autoLogoutTimeout((pageTimeout - logoutTimer - timerReserve) - (new Date().getTime() - parseInt($.cookie('lastActivity'))));
						return false;
					}

					var empty = timer >= 9 ? '&nbsp;&nbsp;' : '&nbsp;';
					$('#autologoutModal').find('#logoutTimer').html(empty);
					$('#autologoutModal').find('#logoutTimer').html(--timer);
					if(timer < 0) {
						$('#autologoutModal').modal('hide');
						if(autoLogout && currentTime - parseInt($.cookie('lastActivity')) >= (pageTimeout - logoutTimer - timerReserve))
							location.href = baseUrl + 'login/logout';
					}
				}, 1000);
				autoLogout = true;
			}
			else {
				var currentTime = new Date().getTime();
				var newTimeout = (pageTimeout - logoutTimer - timerReserve) - (currentTime - parseInt($.cookie('lastActivity')));
				autoLogoutTimeout(newTimeout);
			}
		}, timeout);
	}
}
function displayVehicles(){
	$.each(vehicles, function(key, val){
		var latLng = new google.maps.LatLng(val.latitude,val.longitude);

		var icon = baseUrl + 'assets/img/truck.png';
		if(val.item_name.indexOf("VHC-32") === 0 || val.item_name.indexOf("VHC-33") === 0 || val.item_name.indexOf("VHC 06") === 0 || val.item_name.indexOf("VHC 12") === 0 || val.item_name.indexOf("VHC 21") === 0)
			icon = baseUrl + 'assets/img/sedan-car.png';
		if(val.item_name.indexOf("VHC 09") === 0 || val.item_name.indexOf("VHC 14") === 0 || val.item_name.indexOf("VHC 15") === 0 || val.item_name.indexOf("VHC 19") === 0 || val.item_name.indexOf("VHC 22") === 0 || val.item_name.indexOf("VHC 24") === 0 || val.item_name.indexOf("VHC 25") === 0 || val.item_name.indexOf("VHC 26") === 0 || val.item_name.indexOf("VHC 29") === 0 || val.item_name.indexOf("VHC 34") === 0)
			icon = baseUrl + 'assets/img/pick_up.png';
		
		vehMarkers[key] = new google.maps.Marker({
			position: latLng,
			map: map,
			title: val.item_name,
			code: val.item_code,
			icon: icon//baseUrl + 'uploads/trackericon/cam_bleue.png'
		});

		var label = new Label({
			map: map
		});
		label.bindTo('position', vehMarkers[key], 'position');
		label.bindTo('text', vehMarkers[key], 'code');

		google.maps.event.addListener(vehMarkers[key], 'click', function() {
			if (typeof infowindow !== 'undefined' && infowindow) infowindow.close();
			infowindow = new google.maps.InfoWindow({
				content: '<div>' + vehMarkers[key].title + '</div>',
			});
			infowindow.open(map, this);
		});
		google.maps.event.addListener(label, 'click', function() {
			if (infowindow) infowindow.close();
			infowindow = new google.maps.InfoWindow({
				content: '<div>' + vehMarkers[key].title + '</div>',
			});
			infowindow.open(map, this);
		});
		vehLabels[key] = label;
	});
	$('#processing-modal').modal('hide');
}

/*$(document).ready(function() {
	if(pageTimeout) {
		var currentTime = new Date().getTime();
		//console.log(serverLastActivity, currentTime);
		//$.cookie('lastActivity', currentTime, {path:'/'});
		autoLogoutTimeout((pageTimeout - logoutTimer - timerReserve) - (new Date().getTime() - currentTime));
	}
});*/

if (!Object.assign) {
  Object.defineProperty(Object, 'assign', {
    enumerable: false,
    configurable: true,
    writable: true,
    value: function(target, firstSource) {
      'use strict';
      if (target === undefined || target === null) {
        throw new TypeError('Cannot convert first argument to object');
      }

      var to = Object(target);
      for (var i = 1; i < arguments.length; i++) {
        var nextSource = arguments[i];
        if (nextSource === undefined || nextSource === null) {
          continue;
        }

        var keysArray = Object.keys(Object(nextSource));
        for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++) {
          var nextKey = keysArray[nextIndex];
          var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);
          if (desc !== undefined && desc.enumerable) {
            to[nextKey] = nextSource[nextKey];
          }
        }
      }
      return to;
    }
  });
}

var Common = function(){
	var config = {
		ui:{
			'currency': '.currency',
			'checkbox': '.checkbox input[type=checkbox]',
			'ajax_preloader':'#ajax-preloader-tmp',
		},
		templates:{
			
		}
	}
	
	

	var public = {

		init:function(){
			public.events();
		},
		submitFormSelector:'',
		events:function(){
			$('#confirmation-modal-text').html('');
			$('#confirmation-yes').text('Yes');
			
			var yesText = $(this).data('yes-text');

			$(document).on('click', '.confirmDelete', function(){
				var message = $(this).data('confirmation-massage');
				public.submitFormSelector = $(this).data('submit-form');
				var yesText = $(this).data('yes-text');

				if(message==undefined || !message){
					message = 'Are you sure to delete?'
				}

				$('#confirmation-modal-text').html(message);
				$('#confirmation-yes').text(yesText);
				$('#confirmation-modal').modal();
			});
			$(document).on('click', '#confirmation-yes', function(){
				
				if($(public.submitFormSelector)!=undefined){
					$(public.submitFormSelector).submit();

					$('#confirmation-modal').modal('hide');

				}
			});

		},
		init_popover:function(){
			
			$("[data-toggle=popover]").popover();
		    $(document).on('click', '.popover-title .close', function(e){
		    	var $target = $(e.target), $popover = $target.closest('.popover').prev();
		    	$popover && $popover.popover('hide');
		    });
			
		},

		init_checkbox: function(){
			$(config.ui.checkbox).checkbox();
		},


        /****************************************************
        init_select2
        [
            {

                selector:'',
                options:{},
                onchange: function(){
                    //onchange event callback 
                },
                values: [{'id':value, 'text':value}]
            }
        ]
        *****************************************************/
		init_select2:function(selects){
			if(selects.length==0)
				return;

			var selects_obj = {};
			/*console.log(selects);*/
			$.each(selects, function(key, value){
				options = {};
				if(value.options!=undefined)
					options = value.options;

				selects_obj[value['selector']] = $(value['selector']).select2(options);

				if(value.init_selected_data!=undefined && value.init_selected_data.length){

					if(options['data']==undefined)
						options['data'] = [];

					options['data'] = options['data'].concat(value.init_selected_data);
					value.values = $.map(value.init_selected_data, function (item) {
						return item.id;
					});
					$(value['selector']).select2('data', options['data']);
				}

				if(value.onchange!=undefined && value.onchange!=false)
					selects_obj[value['selector']].on('change', value.onchange);

				if(value.values!=false) {
					selects_obj[value['selector']].val(value.values).trigger('change');
				}

				if(value.onblur!=undefined && value.onblur!=false){
					selects_obj[value['selector']].on('select2-blur', value.onblur);
				}
				if(value.opening!=undefined && value.opening!=false){
					selects_obj[value['selector']].on("select2-opening", value.opening);
				}
				if(value.open!=undefined && value.open!=false){
					selects_obj[value['selector']].on("select2-open", value.open);
				}
				if(value.focus!=undefined && value.focus!=false){
					selects_obj[value['selector']].on("select2-focus", value.focus);
				}
			});

		},

		init_scroll: function(){
			$('.no-touch .slim-scroll').each(function () {
	            var $self = $(this), $data = $self.data(), $slimResize;
	            $self.slimScroll($data);
	            $(window).resize(function (e) {
	                clearTimeout($slimResize);
	                $slimResize = setTimeout(function () {
	                    $self.slimScroll($data);
	                }, 500);
	            });

	            $(document).on('updateNav', function () {
	                $self.slimScroll($data);
	            });
	        });
		},
		
		renderView:function(params){
			
			if(params.helpers==undefined)
				params.helpers = {};

			if(params.empty_template_id!=undefined && params.data.length==0){
				var template = $.templates({
					markup: params.empty_template_id,
					allowCode: true
				});
				var htmlOutput = template.render([{message:params.empty_message}], Object.assign(public.helpers, params.helpers));	
			}

			if(params.data.length){
			    var template = $.templates({
					markup: params.template_id,
					allowCode: true
				});
			    var htmlOutput = template.render(params.data, Object.assign(public.helpers, params.helpers));
			}

			delete template;

			if(params.render_method==undefined)
				$(params.view_container_id).html(htmlOutput);
			if(params.render_method=='variable')
				return htmlOutput;
			if(params.render_method=='append')
				$(params.view_container_id).append(htmlOutput);

			delete htmlOutput;
		},
		renderPreloader:function (view, data, out) {

			if($(view).length==0)
				return false;
			if(out==undefined)
				out=500;
			var data_tmp = {
				"title":(data!=undefined && data.title!=undefined)?data.title:'',
				"text" : (data!=undefined && data.text!=undefined)?data.text:'',
			};
			var preloader = {
				template_id:config.ui.ajax_preloader,
				view_container_id:view,
				data:[data_tmp],
				helpers:public.helpers
			};

			public.renderView(preloader);
			$("#global-block-preloader").fadeIn(100);
			$("#global-block-preloader").fadeOut(out);
		},
		init_autocompleate: function(selector){
            if($('[data-autocompleate]').length)
		    {
		        $.each($('[data-autocompleate]'), function(key, val){
		        	

		            addressAuto[key] = new google.maps.places.Autocomplete(
		                ($(val)[0]),
		                { types: ['geocode'] , componentRestrictions: {country: AUTOCOMPLETE_RESTRICTION} }
		            );
		            
		            var parentSelector = 'form';
		            if($(val).data('parent-selectror'))
		            	parentSelector = $(val).data('parent-selectror');

                    
                    google.maps.event.addListener(addressAuto[key], 'place_changed', function () {
		                fillInAutocompleteAddress(addressAuto[key], val, parentSelector);
		                public.callback_checker(val, addressAuto[key]);
		            	
		            });

		        });
		    }
		    
		},

		callback_checker:function(element, param, dataAttr = 'callback'){
			if($(element).data(dataAttr)!==undefined){
            	var parts = $(element).data(dataAttr).split('.');
				var objName;
				var funcName;
				if (parts.length == 1) {
				  objName = 'window';
				  funcName = parts[0];
				}
				else if (parts.length = 2) {
				  objName = parts[0];
				  funcName = parts[1];
				}
				var obj = window[objName];
				if (typeof obj === 'object') {
					var fn = obj[funcName];
				  	if(typeof fn === 'function') {
					    return fn(param);
					}
				}
				return false;
            }
			return false;
		},
		
		myDropzone: [],

        initDropzone:function(elementSelector = false, url_action){

            id = 0;
            if(elementSelector)
                element = $(elementSelector+'.dropzone');
            else
                element = $('.dropzone');

            Dropzone.autoDiscover = false;

            public.myDropzone[id] = new Dropzone(element.get(0), {
                acceptedFiles: 'image/*,application/pdf',
                accept: function(file, done) {
                    var thumbnail = $('.dropzone .dz-preview.dz-file-preview .dz-image:last img');
                    switch (file.type) {
                      case 'application/pdf':
                        thumbnail.attr('src', baseUrl + '../../assets/vendors/notebook/images/pdf.png');
                        break;
                    }

                    done();
                  },
                url: baseUrl + url_action,
                params: function (files, xhr){
                    var files_uuids = [];
                    $.each(files, function (key, val) {
                        files_uuids.push(val.upload.uuid);
                    });
                    return {                    
                        files_uuids: files_uuids,
                        id: id,
                        client_id: client_id
                    }
                },
                uploadMultiple: true,
                parallelUploads: 5,
                paramName: 'files',
                thumbnailWidth: 94,
                thumbnailHeight: 94,
                timeout: 300000,
                init: function() {
                    
                },
                addRemoveLinks: true,
                ignoreHiddenFiles: true,
                autoProcessQueue: true,
                removedfile: function(a, b) {
                    var thingId = $(a.previewElement).closest('form').find('[name="lead_id"]');
                    if(thingId.length > 0){
                        thingId = thingId.val();
                    } else {
                        thingId = 0;
                    }
                                    
                    if(a.filepath !== undefined) {
                        $('input[name="pre_uploaded_files[' + thingId + '][]"][value="' + a.filepath + '"]').remove();
                        $(a.previewElement).remove();
                    } else if(a.size !== undefined) {
                        $('input[name="pre_uploaded_files[' + thingId + '][]"][data-size="' + a.size + '"]:first').remove();
                        $(a.previewElement).remove();
                    } else {
                        $('input[name="pre_uploaded_files[' + thingId + '][]"][data-name="' + a.name + '"]:first').remove();
                        $(a.previewElement).remove();
                    }
                    
                    return true;
                },
                    complete: function(a) {
                    var thingId = $(a.previewElement).closest('form').find('[name="lead_id"]');
                    if(thingId.length > 0){
                        thingId = thingId.val();
                    } else {
                        thingId = 0;
                    }
                    
                    
                    let response = a.xhr !== undefined ? $.parseJSON(a.xhr.response) : false;
                    var needSync = false;
                    var c = this;

                    if(response) {
                        var currentUploaded = response.data.filter(function(el) {
                            return el.uuid ===  a.upload.uuid;
                        })[0];

                        let thumbnailImageContainer = $(a.previewElement).find('.dz-image');
                        let thumbnailImage = $(thumbnailImageContainer).find('img');

                        if (currentUploaded.type.indexOf('image') === -1) {
                            let htmlString = `
                                            <a href="${currentUploaded.url}" target="_blank" data-lead_file="${currentUploaded.name}">
                                                    <img data-dz-thumbnail="" src="${location.origin + '/../../assets/vendors/notebook/images/pdf.png'}">
                                            </a>`;

                            thumbnailImageContainer.html(htmlString);
                            } else {
                                let htmlString = `
                                                <a href="${currentUploaded.url}" data-lightbox="${'leadfile-' + currentUploaded.id}" data-lead_file="${currentUploaded.name}" style="cursor: pointer">
                                                        ${thumbnailImage.get(0).outerHTML}
                                                </a>`;

                                thumbnailImageContainer.html(htmlString);
                        }

                        if(!$(a.previewElement).closest('form').find('input[value="' + currentUploaded.filepath + '"]').length) {
                                if(!$('[name="id[' + id + ']"]').length) {
                                    $(a.previewElement).closest('form').append('<input type="hidden" data-lead_id="' + thingId + '" name="pre_uploaded_files[' + thingId + '][]" value="' + currentUploaded.filepath + '" data-uuid="' + currentUploaded.uuid + '" data-size="' + currentUploaded.size + '" data-url="' + currentUploaded.url + '" data-type="' + currentUploaded.type + '" data-name="' + currentUploaded.name + '">');
                                }

                                $('a.domfile[data-uuid="' + currentUploaded.uuid + '"]').attr('onclick', 'remove_lead_file(' + id + ', "' + $.trim(currentUploaded.name) + '", '+client_id+')').attr('data-name', currentUploaded.name);
                          }
                    }
                },
                processing: function(a) {
                    if (a.previewElement && (a.previewElement.classList.add("dz-processing"),
                        a._removeLink))
                        return a._removeLink.innerHTML = this.options.dictRemoveFile
                },
                queuecomplete: function(a,b) {
                    $('input[type="submit"]').removeClass('disabled').removeAttr('disabled');
                },          
                addedfile: function(a) {
                        $('input[type="submit"]').addClass('disabled').attr('disabled', 'disabled');
                        var c = this;
                        var b = Dropzone; 
                        if (this.element === this.previewsContainer && this.element.classList.add("dz-started"),
                        this.previewsContainer) {
                            a.previewElement = b.createElement(this.options.previewTemplate.trim()),
                            a.previewTemplate = a.previewElement,
                            this.previewsContainer.appendChild(a.previewElement);
                            for (var d = a.previewElement.querySelectorAll("[data-dz-name]"), e = 0, d = d; ; ) {
                                var f;
                                if (e >= d.length)
                                    break;
                                f = d[e++];
                                var g = f;
                                g.textContent = a.name
                            }
                            for (var h = a.previewElement.querySelectorAll("[data-dz-size]"), i = 0, h = h; !(i >= h.length); )
                                g = h[i++],
                                g.innerHTML = this.filesize(a.size);
                            var tpl = '<a class="dz-remove" href="javascript:undefined;" onclick="remove_lead_file('+ id + ", '" + $.trim(a.name) + "', "+client_id+")" + '" data-name="'+ a.name +'" data-dz-remove>' + this.options.dictRemoveFile + "</a>";
                            if(a instanceof File)
                                tpl = '<a class="dz-remove domfile" href="javascript:undefined;" data-uuid="' + $.trim(a.upload.uuid) + '" data-dz-remove>' + this.options.dictRemoveFile + "</a>";
                            this.options.addRemoveLinks && (a._removeLink = b.createElement(tpl),
                            a.previewElement.appendChild(a._removeLink));
                            for (var j = function(d) {
                                return d.preventDefault(),
                                d.stopPropagation(),
                                a.status === b.UPLOADING ? b.confirm(c.options.dictCancelUploadConfirmation, function() {
                                    return c.removeFile(a)
                                }) : c.options.dictRemoveFileConfirmation ? b.confirm(c.options.dictRemoveFileConfirmation, function() {
                                    return c.removeFile(a)
                                }) : c.removeFile(a)
                            }, k = a.previewElement.querySelectorAll("[data-dz-remove]"), l = 0, k = k; ; ) {
                                var m;
                                if (l >= k.length)
                                    break;
                                m = k[l++];
                                m.addEventListener("click", j)
                            }
                        }
                    },
                //autoQueue: false
            });
        },
        b64toBlob:function(b64Data, contentType, sliceSize) {
		        contentType = contentType || '';
		        sliceSize = sliceSize || 512;

		        var byteCharacters = atob(b64Data);
		        var byteArrays = [];

		        for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
		            var slice = byteCharacters.slice(offset, offset + sliceSize);

		            var byteNumbers = new Array(slice.length);
		            for (var i = 0; i < slice.length; i++) {
		                byteNumbers[i] = slice.charCodeAt(i);
		            }

		            var byteArray = new Uint8Array(byteNumbers);

		            byteArrays.push(byteArray);
		        }

		      var blob = new Blob(byteArrays, {type: contentType});
		      return blob;
		},
		renameKey:(object, key, newKey) => {
			const clonedObj = clone(object);
			const targetKey = clonedObj[key];
			delete clonedObj[key];
			clonedObj[newKey] = targetKey;
			return clonedObj;
		},

		text_decorate: function(text){
			let urlRegex = /(\bhttps?:\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
			text = text.replace(urlRegex, (url) => {
				if (url.length >= 80) {
					return `<a class='link text-white text-ul' target='_blank' href="` + url + `">` + url.slice(0, 50) + "..." + url.slice(-20) + `</a>`;
				} else {
					return `<a class="link text-white text-ul" target="_blank" href="` + url + `">` + url + `</a>`;
				}
			});

			text = text
				.replace(/(?:\*)([^*]+)(?:\*)/gm, "<b>$1</b>")
				.replace(/(?:_)([^_]+)(?:_)/gm, "<u>$1</u>")
				.replace(/(?:~)([^~]+)(?:~)/gm, "<i>$1</i>");
			const div = document.createElement("div");
			div.innerHTML = text;
			return div.outerHTML;
		},

		encodeUTF8string: function (str) {
			return encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
				function toSolidBytes (match, p1) {
					return String.fromCharCode('0x' + p1)
				})
		},

		helpers:{
			text_decorate:function (text) {
				return public.text_decorate(text);
			},
			html_entitles:function(str){
				const htmlEntities = {
					"&": "&amp;",
					"<": "&lt;",
					">": "&gt;",
					'"': "&quot;",
					"'": "&apos;"
				};
				return str.replace(/([&<>\"'])/g, match => htmlEntities[match]);
			},
			currency_format:function(n, cents){
				return public.money(n, cents);
			},
            integer:function(str){
                return parseInt(str);
            },
			toUpperCase: function(str, num){
				return str.charAt(num).toUpperCase() + str.slice(num+1);
            },

			floatval: function (val) {
				return parseFloat(val);
			},

			nl2br: function (str, is_xhtml) {
				var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

				return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
			},

			getUserAvatar: function(picture){
				if(picture==undefined || !picture)
					return baseUrl+'assets/pictures/avatar_default.jpg';

				if(!PICTURE_PATH)
					return picture;

				return baseUrl+PICTURE_PATH+picture;
			},

			clientNoteFile:function(client_id, note_id, file){
				return baseUrl+'uploads/notes_files/'+client_id+'/'+note_id+'/'+encodeURI(file);
			},

			object_length:function(obj){
				return Object.keys(obj).length;
			},

			array_items_counts: function(arr){
				var counts = arr.reduce(function(acc, el) {
					acc[el] = (acc[el] || 0) + 1;
					return acc;
				}, {});
				return counts;
			},

			filterServices:function (services) {
				var result = [];
				$.each(services, function (key, value) {
					if(value.is_product==0 && value.is_bundle==0)
						result.push(value);
				});
				return result;
			},

			filterProducts:function (services) {
				var result = [];
				$.each(services, function (key, value) {
					if(value.is_product==1)
						result.push(value);
				});
				return result;
			},

			filterBundles:function (services) {
				var result = [];
				$.each(services, function (key, value) {
					if(value.is_bundle==1)
						result.push(value);
				});
				return result;
			},

			getDateFormat:function(){
				return dateFormatJS.toUpperCase();
			},

			getTimeIntFormat:function(){
				return INT_TIME_FROMAT;
			},

			getTimeFormat:function(){
				return (parseInt(INT_TIME_FROMAT) === 12)?'h:mm a':'H:mm';
			},

			dateFormat:function (fdate, format, from) {
				if(!format || format==undefined){
					format = public.helpers.getDateFormat() + " " + public.helpers.getTimeFormat();
				}

				return moment(fdate).format(format);
			},

			getTextColor: function (color) {
				return getTextColor(color);
			},

			encodeUTF8string: function (str) {
				return public.encodeUTF8string(str);
			},

			defaultBrandId: function () {
				return default_brand_id;
			},

			defaultBrandName: function () {
				return default_brand_name;
			}
		},

		request:{
			get:function(url, callback, errorCallback, data){
				var callbackFunction = callback;
				var errorcallbackFunction = errorCallback;
				var dataVar = {};

				if(data!=undefined)
					dataVar = data;

				$.ajax({
					method: "GET",
					url: url,
					dataType: 'JSON',
					global: false,
					data:dataVar
				}).done(function(msg){  
					callbackFunction(msg);
				})
				.fail(function( jqXHR, textStatus, object) {

		          if (typeof jqXHR.responseText != "undefined") {
		          	response = $.parseJSON(jqXHR.responseText);
		          	errorcallbackFunction(response);
		          }
					return false;
				});
			},
			send:function(url, data, callback, errorCallback, global){
				var callbackFunction = callback;
				var errorcallbackFunction = errorCallback;
				$.ajax({
					method: "POST",
					url: url,
					dataType: 'JSON',
					data: data,
					global: (global!=undefined)?global:false,
					beforeSend: function(request) {
					    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
					},
				})
				.done(function(msg){  
					callbackFunction(msg);
				})
				.fail(function( jqXHR, textStatus, object) {

		          if (typeof jqXHR.responseText != "undefined") {
		          	response = $.parseJSON(jqXHR.responseText);
		          	errorcallbackFunction(response);
		          }
					return false;
				});
			}
		},

        money: function(amount, cents, html){
            pattent = currency_symbol_position.replace('{amount}', '#').replace('{currency}', '!');
            var result = currency(amount, { symbol: public.get_currency(), pattern: pattent}).format();

            if(html!=undefined && html){
				result = result.replace(public.get_currency(), html_encode(public.get_currency()));
			}

            if(cents!=undefined && cents==false)
            	return result.substring(0, result.length-3);

            return result;
        },

        get_currency: function(html){
			return (currency_symbol)?currency_symbol:'$';
        },

        mask_currency: function(selector){
            //pattern = currency_symbol_position.replace('{amount}', '#').replace('{currency}', '!');
            amount_pos = currency_symbol_position.indexOf('{amount}');
            currency_pos = currency_symbol_position.indexOf('{currency}');
            space_pos = currency_symbol_position.indexOf(' ');
            var options = {
                removeMaskOnSubmit: true, 
                rightAlign: false, 
                digits: 2, 
                autoGroup: true, 
                radixPoint: ".",
                suffix:'',
                prefix:''
            };

            if(currency_pos > amount_pos){
                options['suffix'] = public.get_currency();
            }
            else{
                options['prefix'] = public.get_currency();
            }
            
            if(currency_pos < space_pos)
                options['prefix'] = options['prefix'] + ' ';
            if(currency_pos > space_pos)
                options['suffix'] = ' '+options['suffix'];

            if(selector==undefined){
                $(config.ui.currency).inputmask("currency", options);
            }
            else{
                selector.inputmask("currency", options);
            }
        },

        unmask_currency: function(selector){
            // console.log("UnMask"+config.ui.currency);
            // $(config.ui.currency).inputmask('remove');
			if(selector === undefined){
				$(config.ui.currency).inputmask('remove');
			}
			else{
				selector.inputmask("remove");
			}
        },

        getAmount: function(val){
        	if(val==undefined)
        		return 0;
        	return currency(val).value;
        },

		initDateRangePicker: function(selector = '.reportrange', from = false, to = false){
			var start = from != false ? moment(from, MOMENT_DATE_FORMAT) : false;
			var end = to  != false  ? moment(to, MOMENT_DATE_FORMAT) : false;
			var dates = ($(selector).attr('data-dates') === undefined || $(selector).attr('data-dates') === '') ? false : JSON.parse($(selector).attr('data-dates'));
			var url = $(selector).attr('data-url') === undefined ? false : $(selector).attr('data-url');
			var block = $(selector).attr('data-selector') === undefined ? false : $(selector).attr('data-selector');
			var myRanges = Object.create(null);
			if(!start)
				start = moment().startOf('month');
			if(!end)
				end = moment().endOf('month');
			if(dates == false || dates.length == 0) {
				dates = {
					'Today': [moment(), moment()],
					'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
					'Last 7 Days': [moment().subtract(6, 'days'), moment()],
					'Last 30 Days': [moment().subtract(29, 'days'), moment()],
					'This Month': [moment().startOf('month'), moment().endOf('month')],
					'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			}

			function cb(start, end) {
				$(selector + ' span').html(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));
				if($(selector + ' input').length)
					$(selector + ' input').val(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));

				if(url && block != false){
					$.ajax({
						type: "post",
						url: url,
						data: {from: start.format('YYYY-MM-DD'), to: end.format('YYYY-MM-DD')},
						success: function (result) {
							//alert(result);
							//console.log(result.html); return false;
							$(block).html(result.html);
						}
					});
					//location.href = url + start.format('YYYY-MM-DD') + '/' + end.format('YYYY-MM-DD');
				}
				else if(url) {
					location.href = url + start.format('YYYY-MM-DD') + '/' + end.format('YYYY-MM-DD');
				}

			}

			$.each($(dates)[0], function(key, val){
				name = key.replace(/_/g," ");
				fieldname = name.replace(/^(.)|\s(.)/g, function ( $1 ) { return $1.toUpperCase ( )});
				myRanges[fieldname] = [];
				$.each($(val), function(jkey, jval){
					myRanges[fieldname][jkey] = moment(jval, MOMENT_DATE_FORMAT);
				});
			});

			$(selector).daterangepicker({
				startDate: start,
				endDate: end,
				ranges: myRanges,
				showDropdowns: true,
				linkedCalendars: false,
				maxDate: moment().endOf('year'),
				minDate: moment('2010-01-01', 'YYYY-MM-DD'),
				locale: {
					format: MOMENT_DATE_FORMAT
				}
			}, cb);

			$(selector + ' span').html(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));
			$(selector).on('apply.daterangepicker', function(ev, picker) {
				$.each($(selector), function(key, val){
					$(val).find('input').val('');
					$(val).find('span').text('');
				});
				$(this).find('input').val(picker.startDate.format(MOMENT_DATE_FORMAT) + ' - ' + picker.endDate.format(MOMENT_DATE_FORMAT));
				$(this).find('span').text(picker.startDate.format(MOMENT_DATE_FORMAT) + ' - ' + picker.endDate.format(MOMENT_DATE_FORMAT));
			});
		},

		dateRangePicker: function(selector, extra_options, callback){
			// if not show datepicker set parentEl:".className" to extra_options
			var option = {
				timePicker: false,
				timePicker24Hour: (parseInt(INT_TIME_FROMAT)==24),
				timePickerIncrement: 15,
				/*ranges: myRanges,*/
				showDropdowns: true,

				linkedCalendars: false,

				maxDate: moment().endOf('year'),
				minDate: moment('2010-01-01', 'YYYY-MM-DD'),
				locale: {
					format: MOMENT_DATE_FORMAT
				}
			};
			const allOptions = Object.assign(option, extra_options);

			$(selector).daterangepicker(allOptions, callback).on('showCalendar.daterangepicker', function (ev, picker) {
				setTimeout(function () {
					if(Common.helpers.getTimeIntFormat()==12){
						if(allOptions.minTime != undefined)
							allOptions.minTime = parseInt(moment(allOptions.minTime, "H").format('h'));

						if(allOptions.maxTime != undefined)
							allOptions.maxTime = parseInt(moment(allOptions.maxTime, "H").format('h'));
					}

					if(allOptions.minTime != undefined || allOptions.maxTime != undefined){
						var timeIntFormat = Common.helpers.getTimeIntFormat();
						$(picker.container).find('.hourselect option').each(function () {
							value = parseInt($(this).attr('value'));
							optionAmPm = $(this).closest('.calendar-time').find(".ampmselect").val();
							if((optionAmPm=="PM" || optionAmPm=="AM") && value==12){
								value = 0;
							}

							if((optionAmPm=="AM" || timeIntFormat==24) && allOptions.minTime!=undefined && value < allOptions.minTime){
								$(this).remove();
							}
							if((optionAmPm=="PM" || timeIntFormat==24) && allOptions.maxTime!=undefined && value > allOptions.maxTime){
								$(this).remove();
							}
						});
					}
				}, 200);

				if(allOptions.customStyle!=undefined)
					allOptions.customStyle($(picker.container));
			});

			$(document).on('change', ".hourselect", function (e) {
				setTimeout(function () {
					$(e.currentTarget).closest('.daterangepicker').find('.hourselect option').each(function () {
						if(allOptions.minTime!=undefined && $(this).attr('value') < allOptions.minTime)
							$(this).remove();
						if(allOptions.maxTime!=undefined && $(this).attr('value') > allOptions.maxTime)
							$(this).remove();
					});
				}, 1000);

			});
		},

		scrollToElement:function(element_id, parent_id){

			// adjust these two to match your HTML hierarchy
			var child  = document.getElementById(element_id);
			var parent = document.getElementById(parent_id);

			if(!parent)
				return;
			// Where is the parent on page
			var parentRect = parent.getBoundingClientRect();
			// What can you see?
			var parentViewableArea = {
				height: parent.clientHeight,
				width: parent.clientWidth
			};

			// Where is the child
			var childRect = child.getBoundingClientRect();
			// Is the child viewable?
			var isViewable = (childRect.top >= parentRect.top) && (childRect.top <= parentRect.top + parentViewableArea.height);

			// if you can't see the child try to scroll parent
			if (!isViewable) {
				// scroll by offset relative to parent
				//parent.scrollTop = (childRect.top + parent.scrollTop) - parentRect.top
				parent.scroll({
					top: (childRect.top + parent.scrollTop) - parentRect.top,
					behavior: 'smooth'
				});
			}

		},

		initTinyMCE: function (selector, options) {

            if(tinymce.get(selector) && tinymce.EditorManager.editors[selector]!=undefined){
                delete tinymce.EditorManager.editors[selector];
			}

			var additional_plugins = '';
			var additional_tools = '';
			if(options!=undefined && options['variables']!=undefined){
				additional_plugins += ' variables';
				additional_tools += ' | variables';
			}

			tinymce.init({
				language : "en",
				height: 280,
				selector: '#' + selector,
				theme: "modern",
				theme_advanced_buttons2_add : 'spellchecker',
				spellchecker_languages : 'English=en',
				spellchecker_word_separator_chars : '\\s!\"#$%&amp;()*+,-./:;&lt;=&gt;?@[\]^_{|}',
				browser_spellcheck:true,
				menubar : false,
				external_plugins: {
					"nanospell": "../../js/tinymce/plugins/nanospell/plugin.js",
				},

				nanospell_server: "php",
				plugins: [
					"advlist autolink youtube table lists image charmap print preview hr anchor pagebreak code",
					"searchreplace wordcount visualblocks visualchars code fullscreen fullpage",
					"insertdatetime media nonbreaking save table contextmenu directionality",
					"jbimages emoticons template paste textcolor colorpicker textpattern images link"+additional_plugins
				],

				relative_urls : true,
				subfolder:"",
				cleanup_on_startup: false,
				trim_span_elements: false,
				verify_html: false,
				cleanup: false,
				convert_urls: false,
				inline_styles : true,
				valid_elements:"*[*]",
				extended_valid_elements:"*[*]",
				//extended_valid_elements: 'span[class]'
				toolbar1: "undo redo | styleselect | bold italic |  alignleft aligncenter alignright alignjustify | bullist | table | youtube |",
				toolbar2: "outdent indent | forecolor backcolor | emoticons | fullscreen preview | numlist | jbimages | link | code"+additional_tools,
				image_advtab: true,
				setup : function(ed) {
					ed.on("click", function() {
						tinymce.execCommand('mceFocus',false,selector);
					});
				}
			});


		},

		groupBy:function(xs, key, key2) {
			return xs.reduce(function(rv, x) {
				position = 'nokey';
				if(x[key]!=undefined)
					position = x[key];

				if(x[key]==undefined && key2!=undefined && x[key2]!=undefined)
					position = x[key2];

				(rv[position] = rv[position] || []).push(x);
				return rv;
			}, {});
		}
	};

	public.init();
	return public;
}();


function initSelect2(input, items, placeholder, multiply = true, allowClear = true)
{
    var select2Input = $(input);
    // var data = [];
    $(select2Input).select2('destroy');

    // $.each($.parseJSON(items), function(key, val) {
    //     data.push({id:val.key, text:val.name});
    // });

    $(select2Input).select2({
        placeholder: placeholder,
        data: JSON.parse(items),
        allowClear: allowClear,
        multiple: multiply,
        dropdownCssClass: "select-statuses-dropdown",
        separator: "|"
    });
    $(select2Input).val($(select2Input).data('value'));
    $(select2Input).trigger('change');
    //$('input.select2-input').attr('name', 'select2_name_' + parseInt(Math.random() * 100000));
    $(select2Input).attr('type', 'text').addClass('pos-abt').css({'display':'block', 'top':'-1px', 'z-index':'-1'});
}

if (!String.prototype.sprintf) {
	String.prototype.sprintf = function() {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) {
			return typeof args[number] != 'undefined'
				? args[number]
				: match
				;
		});
	};
}

const clone = (obj) => Object.assign({}, obj);

function ItemSaveCallback(response){
	if(typeof response.errors != 'undefined'){
		$.each(response.errors, function (key, val) {
			errorMessage(val);
		});
	}
}

function showOrHide(new_add, new_add_tab, inline = false, default_tab = null) {
	new_add = document.getElementById(new_add);
	new_add_tab = document.getElementById(new_add_tab);
	default_tab = document.getElementById(default_tab);
	
	if (new_add.checked) {
		new_add_tab.style.display = inline ? "inline-block" : "block";
		default_tab?.classList.add('hidden');
	}
	else {
		new_add_tab.style.display = "none";
		default_tab?.classList.remove('hidden');
	}
}

Array.prototype.sortBy = function(p) {
	return this.slice(0).sort(function(a,b) {
		return (a[p] > b[p]) ? 1 : (a[p] < b[p]) ? -1 : 0;
	});
}

function getRGB(c) {
	return parseInt(c, 16) || c
}

function getsRGB(c) {
	return getRGB(c) / 255 <= 0.03928
		? getRGB(c) / 255 / 12.92
		: Math.pow((getRGB(c) / 255 + 0.055) / 1.055, 2.4)
}

function getLuminance(hexColor) {
	return (
		0.2126 * getsRGB(hexColor.substr(1, 2)) +
		0.7152 * getsRGB(hexColor.substr(3, 2)) +
		0.0722 * getsRGB(hexColor.substr(-2))
	)
}

function getContrast(f, b) {
	const L1 = getLuminance(f)
	const L2 = getLuminance(b)
	return (Math.max(L1, L2) + 0.05) / (Math.min(L1, L2) + 0.05)
}

function getTextColor(bgColor, wh, bl) {
	if(wh == undefined)
		wh = "#ffffff";
	if(bl = undefined)
		bl = "#000000";

	const whiteContrast = getContrast(bgColor, '#ffffff')
	const blackContrast = getContrast(bgColor, '#000000')

	return whiteContrast > blackContrast ? '#ffffff' : '#000000'
}


/*
$.notify({
	// options
	icon: 'glyphicon glyphicon-warning-sign',
	title: 'Bootstrap notify',
	message: 'Turning standard Bootstrap alerts into "notify" like notifications',
	url: 'https://github.com/mouse0270/bootstrap-notify',
	target: '_blank'
},{
	// settings
	element: 'body',
	position: null,
	type: "info",
	allow_dismiss: true,
	newest_on_top: false,
	showProgressbar: false,
	placement: {
		from: "top",
		align: "right"
	},
	offset: 20,
	spacing: 10,
	z_index: 1031,
	delay: 5000,
	timer: 1000,
	url_target: '_blank',
	mouse_over: null,
	animate: {
		enter: 'animated fadeInDown',
		exit: 'animated fadeOutUp'
	},
	onShow: null,
	onShown: null,
	onClose: null,
	onClosed: null,
	icon_type: 'class',
	template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert">' +
		'<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
		'<span data-notify="icon"></span> ' +
		'<span data-notify="title">{1}</span> ' +
		'<span data-notify="message">{2}</span>' +
		'<div class="progress" data-notify="progressbar">' +
		'<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
		'</div>' +
		'<a href="{3}" target="{4}" data-notify="url"></a>' +
		'</div>'
});
*/
function html_encode(string) {
	var ret_val = '';
	for (var i = 0; i < string.length; i++) {
		ret_val += '&#' + string.codePointAt(i) + ';';
	}
	return ret_val;
}
