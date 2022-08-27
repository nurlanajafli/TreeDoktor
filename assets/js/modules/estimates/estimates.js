	var directionsService = new google.maps.DirectionsService();
	var servicesCalc = {};
	var totalCalc = {
		discount: 0,
		discountPercents: 0,
		discountString: Common.money(0),
		totalForServices: 0,
		extraExpenses: 0,
		total: 0,
		companyCost: 0,
		crewsCost: 0,
		equipmentsCost: 0,
		overheadsCost: 0,
		tax: 0,
		profit: 0,
		profitPercents: 0
	};
	var profitClass = '';
	var myDropzone = {};
	var date = new Date();
	var direction = {};
	var travelTime = 0;
	var travelHrs = 0;
	var daysToWed = new Date().getDay() <= 2 ? 3 - new Date().getDay() : 7 - new Date().getDay() + 3;

	date.setDate(date.getDate() + daysToWed);
	var request = {
		origin: new google.maps.LatLng(OFFICE_LAT,OFFICE_LON),
		destination: destination,
		travelMode: google.maps.DirectionsTravelMode.DRIVING,
		drivingOptions: {
			departureTime: new Date(date.getFullYear(), date.getMonth(), date.getDate(), 7, 30),
			trafficModel: google.maps.TrafficModel.PESSIMISTIC
		}
	};

	if(destination) {
		directionsService.route(request, function(response, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				direction = response;
				if(direction.routes[0] != undefined && direction.routes[0].legs[0] != undefined && direction.routes[0].legs[0].duration != undefined &&
					direction.routes[0].legs[0].duration.value != undefined && direction.routes[0].legs[0].duration_in_traffic != undefined && direction.routes[0].legs[0].duration_in_traffic.value != undefined)
					travelTime = direction.routes[0].legs[0].duration_in_traffic.value * 2;//(direction.routes[0].legs[0].duration.value + direction.routes[0].legs[0].duration_in_traffic.value);
					travelHrs = parseFloat((parseFloat((travelTime / 3600 / 15).toFixed(3)) * 15).toFixed(1));
			}
		});
	}

	var id = null;

	var tags = [];

	let withSetTax = true;

	String.prototype.stripSlashes = function(){
		return this.replace(/\\(.)/mg, "$1");
	};

	$.each(trees, function(key, val){
		tags.push(val.tree_common_name);
	});

	function get_man_hours(obj)
	{
		var sumExp = 0;
		$(obj).parents('.serviceGroup:first').find(".serviceExtraExpenses .expenseAmount").filter(function() {return sumExp += Common.getAmount(this.value) ? Common.getAmount(this.value) : 0; });

		var serviceId = $(obj).parents('.serviceGroup:first').data('service_id');

		var overhead = 0;
		if($(obj).parents('.serviceGroup:first').find('.overheadRate').val()!=undefined)
			var overhead = $(obj).parents('.serviceGroup:first').find('.overheadRate').val() ? Common.getAmount($(obj).parents('.serviceGroup:first').find('.overheadRate').val()) : 0;
		
		var equipmentsPerHour = 0;
		var extraExpenses = sumExp;
		if($(obj).parents('.serviceGroup:first').find('.serviceMarkup').val()!=undefined)
			var markup = parseFloat($(obj).parents('.serviceGroup:first').find('.serviceMarkup').val().replace('% ', '')) ? parseFloat($(obj).parents('.serviceGroup:first').find('.serviceMarkup').val().replace('% ', '')) : 0;
		
		var serviceTime = parseFloat($(obj).parents('.serviceGroup:first').find('.serviceTime').val()) ? parseFloat($(obj).parents('.serviceGroup:first').find('.serviceTime').val()) : 0;
		var travelTime = parseFloat($(obj).parents('.serviceGroup:first').find('.serviceTravelTime').val()) ? parseFloat($(obj).parents('.serviceGroup:first').find('.serviceTravelTime').val()) : 0;
		var disposalTime = parseFloat($(obj).parents('.serviceGroup:first').find('.serviceDisposalTime').val()) ? parseFloat($(obj).parents('.serviceGroup:first').find('.serviceDisposalTime').val()) : 0;
		var peoples = $(obj).parents('.serviceGroup:first').find('.selectedCrew').find('label').length ? $(obj).parents('.serviceGroup:first').find('.selectedCrew').find('label').length : 1;
		var specialistsPerHour = 0;

		$(obj).parents('.serviceGroup:first').find('.selectedCrew label').each(function() {
			specialistsPerHour += parseFloat($(this).attr('data-rate'));
		});
		$(obj).parents('.serviceGroup:first').find('.service_vehicle').each(function(){
			if($(this).val())
				equipmentsPerHour += parseFloat($(this).find('option[value="' + $(this).val() + '"]').data('rate'));
		});
		$(obj).parents('.serviceGroup:first').find('.service_trailer').each(function(){
			if($(this).val())
				equipmentsPerHour += parseFloat($(this).find('option[value="' + $(this).val() + '"]').data('rate'));
		});
		$(obj).parents('.serviceGroup:first').find('.tools_option:checked').each(function(){
			equipmentsPerHour += parseFloat($(this).data('rate'));
		});

		var hrs = (serviceTime + travelTime + disposalTime);
		var manHrs = hrs * peoples;
		manHrs = Math.round((manHrs)*100)/100;
		$(obj).parents('.serviceGroup:first').find('.manHours').text(manHrs);
		$(obj).parents('.serviceGroup:first').find('.manHours').val(manHrs);
		var companyCost = (overhead * manHrs) + (specialistsPerHour * hrs) + (equipmentsPerHour * hrs) + extraExpenses;
		$(obj).parents('.serviceGroup:first').find('.companyCost').val((companyCost).toFixed(2));
		$(obj).parents('.serviceGroup:first').find('.extraExpenses').val((extraExpenses).toFixed(2));
		var calculatedTotal = ((companyCost) * (1 + (markup / 100))).toFixed(2);
		$(obj).parents('.serviceGroup:first').find('.serviceTotal').val(calculatedTotal);
		$(obj).parents('.serviceGroup:first').find('.manHoursRate').val(((calculatedTotal - sumExp) / manHrs).toFixed(2));

		$(obj).parents('.serviceGroup:first').find('.teamCost').text(Common.money((specialistsPerHour * hrs).toFixed(2)));
		$(obj).parents('.serviceGroup:first').find('.equipCost').text(Common.money((equipmentsPerHour * hrs).toFixed(2)));

		servicesCalc[serviceId] = {
			totalForService: parseFloat(companyCost),
			extraExpenses: parseFloat(extraExpenses),
			total: parseFloat(calculatedTotal),
			companyCost: parseFloat(companyCost),
			crewCost: parseFloat(specialistsPerHour * hrs),
			equipmentCost: parseFloat(equipmentsPerHour * hrs),
			overheadCost: parseFloat(overhead * manHrs),
		};

		totalBlock();

		if(parseFloat(calculatedTotal))
		{
			$(obj).parents('.serviceGroup:first').find('.useTotal').fadeIn();
			//$(obj).parents('.serviceGroup:first').find('.priceSum').removeClass('col-xs-6').addClass('col-xs-4');
		}
		else
		{
			$(obj).parents('.serviceGroup:first').find('.useTotal').fadeOut();
			//$(obj).parents('.serviceGroup:first').find('.priceSum').removeClass('col-xs-4').addClass('col-xs-6');
		}
		return true;
	}

	function totalBlock() {
		totalCalc = {
			discount: 0,
			discountPercents: 0,
			discountString: Common.money(0),
			totalForServices: 0,
			extraExpenses: 0,
			companyCost: 0,
			crewsCost: 0,
			equipmentsCost: 0,
			overheadsCost: 0,
			tax: 0,
			total: 0,
			profitPercents: 0,
			profit: 0,
			totalForServicesForTax: 0
		};

		var taxble_percent_from_total = 0;
		Object.keys(servicesCalc).map(function (serviceId) {
			var obj = $('.serviceGroup[data-service_id="' + serviceId + '"]');
			totalCalc.totalForServices += ($(obj).find('.servicePrice').val()) ? Common.getAmount($(obj).find('.servicePrice').val()) : 0;
		});
		Object.keys(servicesCalc).map(function (serviceId) {
			var obj = $('.serviceGroup[data-service_id="' + serviceId + '"]');
			servicePrice = ($(obj).find('.servicePrice').val()) ? Common.getAmount($(obj).find('.servicePrice').val()) : 0;
			non_taxable_id = $(obj).find('.nonTaxable').data("id");
			is_non_taxble = $(non_taxable_id).val();			
			
			if(is_non_taxble==0) {
				let div = totalCalc.totalForServices ? totalCalc.totalForServices : 1;
				taxble_percent_from_total +=  servicePrice*100/div;
			}
		});
		
		taxble_percent_from_total=taxble_percent_from_total/100;
		totalCalc.discount = parseFloat($('#estimateDiscountValue').val()) ? parseFloat($('#estimateDiscountValue').val()) : 0;
		totalCalc.discountPercents = parseFloat($('#estimateDiscountPercents').val()) ? parseFloat($('#estimateDiscountPercents').val()) : 0;
		if(totalCalc.discountPercents == 1){
			totalCalc.discount = totalCalc.totalForServices * (totalCalc.discount / 100);
		}
		discount = totalCalc.discount*((taxble_percent_from_total==0)?1:taxble_percent_from_total);

		// totalCalc.estimate_hst_disabled = parseInt($('[name="estimate_hst_disabled"]').val());
		if($('#inclTax').is(':checked') || $('[name="estimate_hst_disabled"]').val() == 2)
			totalCalc.estimate_hst_disabled = 2;

		totalCalc.totalForServices = 0;
		Object.keys(servicesCalc).map(function (serviceId) {
			var obj = $('.serviceGroup[data-service_id="' + serviceId + '"]');

			non_taxable_id = $(obj).find('.nonTaxable').data("id");
			is_non_taxble = $(non_taxable_id).val();

			
			//totalCalc.discount = parseFloat($('#estimateDiscountValue').val()) ? parseFloat($('#estimateDiscountValue').val()) : 0;
			if(!isNaN(totalCalc.estimate_hst_disabled) && parseInt(totalCalc.estimate_hst_disabled)==2)
				totalCalc.discount = 0;

			totalCalc.discountString = Common.money(totalCalc.discount);

			totalCalc.totalForServices += ($(obj).find('.servicePrice').val()) ? Common.getAmount($(obj).find('.servicePrice').val()) : 0;
			totalCalc.totalForServicesForTax += is_non_taxble == 0 ? (($(obj).find('.servicePrice').val()) ? Common.getAmount($(obj).find('.servicePrice').val()) : 0) : 0;	

			totalCalc.extraExpenses += servicesCalc[serviceId].extraExpenses ? parseFloat(servicesCalc[serviceId].extraExpenses) : 0;
			totalCalc.companyCost += servicesCalc[serviceId].companyCost ? parseFloat(servicesCalc[serviceId].companyCost) : 0;
			totalCalc.crewsCost += servicesCalc[serviceId].crewCost ? parseFloat(servicesCalc[serviceId].crewCost) : 0;
			totalCalc.equipmentsCost += servicesCalc[serviceId].equipmentCost ? parseFloat(servicesCalc[serviceId].equipmentCost) : 0;
			totalCalc.overheadsCost += servicesCalc[serviceId].overheadCost ? parseFloat(servicesCalc[serviceId].overheadCost) : 0;
			
			totalCalc.tax = totalCalc.tax ? totalCalc.tax : 0;
			totalCalc.total = totalCalc.total ? totalCalc.total : 0;
			totalCalc.profitPercents = totalCalc.profitPercents ? totalCalc.profitPercents : 0;
			totalCalc.profit = totalCalc.profit ? parseFloat(totalCalc.profit) : 0;

			totalCalc.tax = totalCalc.totalForServicesForTax >= discount ? (totalCalc.totalForServicesForTax - discount) * (parseFloat(TAX_PERC) / 100) : 0;

			totalCalc.total = totalCalc.tax + totalCalc.totalForServices - totalCalc.discount; 
			
			totalCalc.profit = totalCalc.totalForServices - totalCalc.discount - totalCalc.companyCost;
			totalCalc.profitPercents = totalCalc.companyCost ? totalCalc.profit * 100 / totalCalc.companyCost : 100;
			totalCalc.profitPercents = !totalCalc.profit ? 0 : totalCalc.profitPercents;
		});

		
		if(!isNaN(totalCalc.estimate_hst_disabled) && parseInt(totalCalc.estimate_hst_disabled)==2){
			// totalCalc.tax = totalCalc.totalForServicesForTax*parseFloat(TAX_PERC)/100;
			let total = totalCalc.totalForServicesForTax / TAX_RATE + (totalCalc.totalForServices - totalCalc.totalForServicesForTax);
			totalCalc.tax = totalCalc.totalForServices-total;
			totalCalc.total = totalCalc.totalForServices;
			totalCalc.profit = totalCalc.totalForServices - totalCalc.tax - totalCalc.discount - totalCalc.companyCost;
			totalCalc.profitPercents = totalCalc.companyCost ? totalCalc.profit * 100 / totalCalc.companyCost : 100;
			totalCalc.profitPercents = !totalCalc.profit ? 0 : totalCalc.profitPercents;
		}

		profitClass = totalCalc.profitPercents > 0 ? 'text-success' : 'text-danger';
		profitClass = totalCalc.profitPercents == 0 ? 'text-warning' : profitClass;
		Object.keys(totalCalc).map(function(objectKey) {
			if(objectKey != 'discountString' && objectKey != 'profitPercents') {
				
				$('.estimateTotalCalculations').find('.' + objectKey).text(Common.money(Common.getAmount(totalCalc[objectKey])));
				$('.estimateTotalCalculations').find('.' + objectKey + 'Input').val(Common.getAmount(totalCalc[objectKey]));
				
				Common.mask_currency($('.estimateTotalCalculations').find('.' + objectKey + 'Input'));
			} 
			else if(objectKey == 'profitPercents'){
				$('.estimateTotalCalculations').find('.' + objectKey).text(Number(totalCalc[objectKey]).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
				$('.estimateTotalCalculations').find('.' + objectKey + 'Input').val(Number(totalCalc[objectKey]).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}).replace(',',''));
			}
			else {

				$('.estimateTotalCalculations').find('.' + objectKey).text(totalCalc[objectKey]);
			}
		});
		$('.estimateTotalCalculations').find('.profitClass').removeClass('text-warning').removeClass('text-success').removeClass('text-danger').addClass(profitClass);
	}

	function draft_multiple(fields_arr) {
		var client_id = $('#client_id').val();
		var lead_id = $('input[name="lead_id"]').val();

		$.ajax({
			url: baseUrl + 'estimates/ajax_estimate_draft_field',
			data: {
				client_id	: client_id,
				lead_id		: lead_id,
				field		: 'array',
				value		: fields_arr,
			},
			method: "POST",
			global: false,
			success: function(resp){
			},
			dataType: 'json'
		});

	}

	function get_travel_time(obj)
	{
		var travel_time = 0;
		if($(obj).is('.serviceTime') && travelHrs) {
			var service_time = parseFloat($(obj).val());
			var full_days = parseInt(service_time / 10);
			var remainder_day = service_time % 10;
			if(full_days) {
				travel_time += travelHrs * full_days;
			}
			if(remainder_day > 5 && remainder_day < 10) {
				travel_time += travelHrs;
			}
			if(remainder_day <= 5 && remainder_day >= 3) {
				travel_time += parseFloat((travelHrs / 1.5).toFixed(2));
			}
			if(remainder_day < 3 && remainder_day >= 2) {
				travel_time += parseFloat((travelHrs / 2).toFixed(2));
			}
			if(remainder_day < 2 && remainder_day > 0) {
				travel_time += parseFloat((travelHrs / 2.5).toFixed(2));
			}
			//if(!full_days && remainder_day < 2 && remainder_day >= 0) {
				//travel_time += parseFloat((travelHrs / 2.5).toFixed(2));
			//}
		}
		travel_time = parseFloat((travel_time).toFixed(2));
		$(obj).parents('.serviceGroup:first').find('.serviceTravelTime').val(travel_time);
	}

	function saveSuccess(resp)
	{
		$('.saveForm').attr('disabled', 'disabled').addClass('disabled');
		$('#processing-modal').modal();
		if(resp.msg) {
			errorMessage(resp.msg);
			$('.saveForm').removeAttr('disabled').removeClass('disabled');
		}
		if(resp.status == 'ok')
			location.href = baseUrl + resp.estimate_no;
	}

	/*var placeSearch, autocomplete1;
	var componentForm = {
		street_number: 'short_name',
		route: 'long_name',
		locality: 'long_name',
		administrative_area_level_1: 'long_name',
		postal_code: 'short_name'
	};

	function initialize1() {
		autocomplete1 = new google.maps.places.Autocomplete(
			/** @type {HTMLInputElement} (document.getElementById('route_1')),
			{ types: ['geocode'] });
		google.maps.event.addListener(autocomplete1, 'place_changed', function () {
			fillInAddress1(autocomplete1, '_1');
			$('#new_add_tab input').trigger('change');
		});
	}
	function fillInAddress1(auto, suffix) {
		var place = auto.getPlace();

		for (var component in componentForm) {
			if ($('#' + component + suffix).length) {
				if (component != 'street_number') {
					component += suffix;
				}
			}
		}
		var num = '';
		for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];
			if (componentForm[addressType]) {
				if (addressType == 'street_number') {
					var num = place.address_components[i][componentForm[addressType]] + ' ';
					continue;
				}
				if (addressType == 'route')
					place.address_components[i][componentForm[addressType]] = num + place.address_components[i][componentForm[addressType]];
				var val = place.address_components[i][componentForm[addressType]];
				document.getElementById(addressType + '_1').value = val;
			}
		}
	}*/
	//setTimeout(function(){initialize1();}, 500);

	function prevalidation(form) {
		
		Common.unmask_currency();

		$('.hours').inputmask('remove');
		$(".serviceMarkup").inputmask("remove");
		var obj = form;
		var error = false;
		var triger = false;
		var inputs = [];
		var fields = ['service_time', 'service_travel_time', 'service_disposal_time'];
		var price = $(obj).find('[name*="service_price"]');
		var crews =$(obj).find('.selectedCrew');
		//var eqs = $(obj).find('.eq.active');
		//console.log(eqs); return false;
		//SETUP
		var eqs = $(obj).find('.service_vehicle');
		//console.log(eqs); return false;
		//SETUP END
		$.each(fields, function(key, val){
			inputs = $(obj).find('[name*="' + val + '"]');
			$.each(inputs, function(k, v){
				$(v).parent().removeClass('has-error');
				if(!$(v).val())
				{
					$(v).parent().addClass('has-error');
					error = true;
				}
			});

		});
		$(price).parent().removeClass('has-error');
		$(price).removeClass('error-estimate-price');
		$(crews).parent().children().filter('div:first').removeClass('has-error');
		$(obj).find('.service_vehicle').parents('.controls:first').removeClass('has-error');
		$('.eq:first').parents('.panel-body:first').removeClass('has-error');
		if($(price).val() === '')
		{
			$(price).val('0');
			/*$(price).parent().addClass('has-error');
			$(price).addClass('error-estimate-price');
			error = true;*/

		}


		//SETUPS
		/*if(!$(eqs).children().length)
		{
			$('.eq:first').parents('.panel-body:first').addClass('has-error');
			error = true;
		}*/
		/*
		if(!$(crews).children().length)
		{
			$(crews).parent().children().filter('div:first').addClass('has-error');
			error = true;
		}
		$.each($(eqs), function (key, val) {
			$(val).parents('.controls:first').removeClass('has-error');
			if($(val).val() == '')
			{
				error = true;
				$(val).parents('.controls:first').addClass('has-error');
			}
		});
		*/
		//SETUPS END
		if(error)
		{
			$("html, body").animate({ scrollTop: $(document).find('.has-error:first').offset().top - 50 });
			
			Common.mask_currency();

			$(".integer").inputmask('integer', {removeMaskOnSubmit: true, rightAlign: false});
			$('.hours').inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})? hrs$", prefix: ' hrs', removeMaskOnSubmit: true, rightAlign: false});
			$(".serviceMarkup").inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})? %$", removeMaskOnSubmit: true, rightAlign: false});
			return false;
		}
		return true;
	}
	function autoComplete()
	{
		$(".species").autocomplete({
			source: tags,
			appendTo: '#div',
			open: function() { $('#div .ui-menu').width(300) }
		});
	}

	function check_equipment()
	{
		if(location.href.indexOf('treedoctors') + 1) {
			var tpls = $('#estimateForm').find('.serviceGroup');

			$.each($(tpls), function (k, v) {
				var vehErrs = true;
				var trErrs = true;
				//console.log($(v).find('#boomErrorMsg')); //return false;
				//var serv = $('[data-service_id="'+serviceId+'"]').parents('.serviceGroup:first');
				var selCrews = $(v).find('.selectedCrew label');
				//console.log($(selCrews)); return false;
				if ($(selCrews).length) {
					//console.log(v);
					$.each($(selCrews), function (key, val) {
						if ($(val).attr('data-emp_type_id') == 9 || $(val).attr('data-emp_type_id') == 10) {
							var veh = $(v).find('.serviceSetupList .service_vehicle');
							$.each($(veh), function (jkey, jval) {
								if ($(jval).val() == 6)
									vehErrs = false;
							});
							if (vehErrs)
								$(v).find('#boomErrorMsg').text('Are you sure you want to send out boom operator without boom truck');
						} else if ($(val).attr('data-emp_type_id') == 12) {
							var trls = $(v).find('.serviceSetupList .service_trailer');
							$.each($(trls), function (jkey, jval) {
								console.log('tra ' + $(jval).val());
								if ($(jval).val() == 4)
									trErrs = false;
							});
							if (trErrs)
								$(v).find('#stumpErrorMsg').text('Are you sure you want to send out stump grinder operator without stump grinder');
						}
					});
				}
			});
		}
	}

	function syncDraft(serviceId) {
		if($('[name="estimate_id"]').val())
			return false;
		var files = [];

		$.each($('input[name="pre_uploaded_files[' + serviceId + '][]"][data-size]'), function (key, val) {
			files.push({
				filepath: $(val).val(),
				size: $(val).data('size'),
				url: $(val).data('url'),
				type: $(val).data('type'),
				name: $(val).data('name'),
				uuid: $(val).data('uuid'),
				show_client: true
			});
		});

		$.ajax({
			url: baseUrl + 'estimates/ajax_estimate_draft_files',
			data: {
				client_id	: $('[name="client_id"]').val(),
				lead_id		: $('[name="lead_id"]').val(),
				service_id  : serviceId,
				files		: files,
			},
			method: "POST",
			global: false,
			success: function(resp){

			},
			dataType: 'json'
		});
	}

	function dropzoneExists(selector) {
		let elements = $(selector).find('.dz-default');
		return elements.length > 0;
	}

	function initDropzone(element = false)
	{
		var id = $(element).parents('section:first').attr('data-service_id');
		if(!element)
			element = '.dropzone';
		else{
			let exists = dropzoneExists(element);
			if(exists) return;
		}
		myDropzone[id] = $(element).dropzone({
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
			url: baseUrl + 'estimates/preupload',
			params: function (files, xhr){
				var files_uuids = [];
				$.each(files, function (key, val) {
					files_uuids.push(val.upload.uuid);
				});
				return {
					service_id: $('[name="id[' + id + ']"]').length ? id : '',
					files_uuids: files_uuids,
					lead_id: $('[name="lead_id"]').val(),
					estimate_id: $('[name="estimate_id"]').val()
				}
			},
			uploadMultiple: true,
			parallelUploads: 5,
			paramName: 'files',
			thumbnailWidth: 94,
			thumbnailHeight: 94,
			init: function() {
				var obj = this;
				if(typeof(EXISTING_FILES.service_files) !== 'undefined')
					{
						$.each(EXISTING_FILES.service_files, function(key, files){
							if(key == id)
							{
								$.each(files, function(jkey, val){
									obj.emit("addedfile", val);
									url = val.url;
									if(val.type == 'application/pdf')
										url = baseUrl + '../../assets/vendors/notebook/images/pdf.png';
									obj.emit("thumbnail", val, url);
									obj.files.push( val );
									obj.emit("success", val);
									obj.emit("accept", val);
									obj.emit("complete", val);

								});
							}
						});
					}
				if($('[name="pre_uploaded_files[' + id + '][]"]').length) {
					$.each($('[name="pre_uploaded_files[' + id + '][]"]'), function(key, input){

						var val = {
							filepath: $(input).val(),
							size: $(input).attr('data-size'),
							url: $(input).attr('data-url'),
							type: $(input).attr('data-type'),
							name: $(input).attr('data-name'),
							uuid: $(input).attr('data-uuid'),
						};

						obj.emit("addedfile", val);
						url = val.url;
						if(val.type == 'application/pdf')
							url = baseUrl + '../../assets/vendors/notebook/images/pdf.png';
						obj.emit("thumbnail", val, url);
						obj.files.push( val );
						obj.emit("success", val);
						obj.emit("accept", val);
					});
				}
			},
			addRemoveLinks: true,
			ignoreHiddenFiles: true,
			autoProcessQueue: true,
			removedfile: function(a, b) {
				var serviceId = $(a.previewElement).parents('.serviceGroup:first').data('service_id');
				if(a.filepath !== undefined) {
					$('input[name="pre_uploaded_files[' + serviceId + '][]"][value="' + a.filepath + '"]').remove();
					$(a.previewElement).remove();
				} else {
					$('input[name="pre_uploaded_files[' + serviceId + '][]"][data-size="' + a.size + '"]:first').remove();
					$(a.previewElement).remove();
				}
				syncDraft(serviceId);
				return true;
			},
			complete: function(a) {
				var serviceId = $(a.previewElement).parents('.serviceGroup:first').data('service_id');
				var response = a.xhr !== undefined ? $.parseJSON(a.xhr.response) : false;
				var needSync = false;
				var c = this;

				if(response) {
					$.each(response.data, function (key, val) {
						if(!$(a.previewElement).parents('.serviceGroup:first').find('input[value="' + val.filepath + '"]').length) {

							if(!$('[name="id[' + id + ']"]').length) {
								needSync = true;
								$(a.previewElement).parents('.serviceGroup:first').append('<input type="hidden" data-service_id="' + serviceId + '" name="pre_uploaded_files[' + serviceId + '][]" value="' + val.filepath + '" data-uuid="' + val.uuid + '" data-size="' + val.size + '" data-url="' + val.url + '" data-type="' + val.type + '" data-name="' + val.name + '">');
							}
							$('a.domfile[data-uuid="' + val.uuid + '"]').attr('onclick', 'remove_remote_file(' + id + ', "' + $.trim(val.name) + '")').attr('data-name', val.name);
						}
					});
					if(needSync)
						syncDraft(serviceId);
				}
			},
			processing: function(a) {
				if (a.previewElement && (a.previewElement.classList.add("dz-processing"),
					a._removeLink))
					return a._removeLink.innerHTML = this.options.dictRemoveFile
			},
			queuecomplete: function(a,b) {
				$('.saveForm, .pdf-preview').removeClass('disabled').removeAttr('disabled');
			},
			addedfile: function(a) {
					$('.saveForm').addClass('disabled').attr('disabled', 'disabled');
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
                        var tpl = '<a class="dz-remove" href="javascript:undefined;" onclick="remove_remote_file('+ id + ", '" + $.trim(a.name) + "')" + '" data-name="'+ a.name +'" data-dz-remove>' + this.options.dictRemoveFile + "</a>";
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
	}

	function remove_remote_file(service, name) {
		if(service === undefined || name === undefined)
			return false;
		var obj = $(this);
		var estimate_no = $('[name="estimate_no"]').val();
		var lead_id = $('[name="lead_id"]').val();
		//var name = $('[onclick="remove_file('+ service +')"]').attr('data-name');
		var client = $('#client_id').val();
		//var service = $(this).parents('section:first').attr('data-service_id');
		$.ajax({
			url: baseUrl + 'estimates/deletePhoto',
			data: {estimate_no: estimate_no, lead_id:lead_id, name: name, service: service, client: client},
			method: "POST",
			global: false,
			success: function(resp){
				if (resp.type == 'ok') {
					return true;
				}
				else {
					alert(resp.message);
				}
				return false;
			},
			dataType: 'json'
		});
		return false;
	}

	$.fn.setCursorPosition = function(pos) {
		this.each(function(index, elem) {
			if (elem.setSelectionRange) {
				elem.setSelectionRange(pos, pos);
			} else if (elem.createTextRange) {
				var range = elem.createTextRange();
				range.collapse(true);
				range.moveEnd('character', pos);
				range.moveStart('character', pos);
				range.select();
			}
		});
		return this;
	};

	$(document).ready(function(){
		$(document).on('keyup change', '.tree-inventory-cost, .tree-inventory-stump', function () {
			let cost = $(this).closest('.serviceGroup').find('.treeInventoryCost').inputmask('unmaskedvalue');
			let stump = $(this).closest('.serviceGroup').find('.treeInventoryStump').inputmask('unmaskedvalue');
			$(this).closest('.serviceGroup').find('.servicePrice').val(parseFloat(cost) + parseFloat(stump));
			totalBlock();
		});

		$('#estimate-preview-modal iframe').load(function(){
			$('#processing-modal').modal('hide');
		});

		$(document).on('click', '.pdf-preview', previewPDF);

		$(window).on('resize', function() {
			let height = $('.floating-line').height();
			if(height > 0)
				$('#content').attr('style', $('#content').attr('style') + ';' + 'padding-bottom:' + height + 'px !important');
		});
		$(window).resize();
		$('.productSelect').select2({
			data:$('.categoriesWithProducts').val() ? JSON.parse($('.categoriesWithProducts').val()) : [],
			placeholder: "Add Product",
			allowClear: true,
			dropdownCssClass: "select-product-dropdown"
		});
		$('.product-select2-toggle').click(function(e){
			e.preventDefault();
			$(this).parent().find('.product-select2').select2('open');
		});
		$('.selectServices').select2({
			data:$('.categoriesWithServices').val() ? JSON.parse($('.categoriesWithServices').val()) : [],
			placeholder: "Add Service",
			allowClear: true,
			dropdownCssClass: "select-service-dropdown"
		});
		$(document).on('mousedown', '.service-select2-toggle', function (evt) {
			evt.preventDefault();
			$(this).parent().find('.service-select2').select2('open');
		});
		// $('.service-select2-toggle').click(function(e){
		// 	e.preventDefault();
		// 	$(this).parent().find('.service-select2').select2('open');
		// });
		$('.bundle-select2').select2({
			placeholder: "Add Bundle",
			allowClear: true,
			dropdownCssClass: "select-service-dropdown"
		}).on("click", function (e) {
			$('.bundle-select2').find(':selected').change();
		}).select2('data', null);
		$('.bundle-select2-toggle').click(function(e){
			e.preventDefault();
			$(this).parent().find('.bundle-select2').select2('open');
		});
		$.each($('.bundleTotal'),function (key, val) {
			$(val).find('.product-select2-bundle').select2({
				data:$('.categoriesWithProducts').val() ? JSON.parse($('.categoriesWithProducts').val()) : [],
				placeholder: "Add Product",
				allowClear: true,
				dropdownCssClass: "select-product-dropdown"
			});
			$(val).find('.service-select2-bundle').select2({
				data:$('.categoriesWithServices').val() ? JSON.parse($('.categoriesWithServices').val()) : [],
				placeholder: "Add Service",
				allowClear: true,
				dropdownCssClass: "select-service-dropdown"
			});
			$(val).find('.product-select2-bundle-toggle').click(function (e) {
				e.preventDefault();
				$(val).find('.product-select2-bundle').select2('open');
			});
			$(val).find('.service-select2-bundle-toggle').click(function (e) {
				e.preventDefault();
				$(val).find('.service-select2-bundle').select2('open');
			});
		});
		$('#readyServices').find('.select2-choice').css('display', 'none');
		if(typeof classWithChildren != 'undefined')
			$('.classSelect').select2({data: classWithChildren});
		setTimeout(function(){
			setTax(true);
			$.each($('.serviceGroup[data-service_id]'), function(key, val){
				get_man_hours($(val).find('a:first'));
				$('.saveForm, .pdf-preview').removeAttr('disabled').removeClass('disabled')
			});
			totalBlock();
			check_equipment();
			/*$(document).on('blur', '[data-name]', function(){
                var lead_id = <?php echo isset($lead) && $lead ? $lead->lead_id : '""'; ?>;
                var str = $(this).parents('form:first').serialize();
                if(lead_id)
                {
                    $.post(baseUrl + 'leads/saveCopyEstimate/' + lead_id, $(this).parents('form:first').serialize() , function (resp) {
                        return false;
                    }, 'json');
                }
            });*/

			autoComplete();
			Common.mask_currency();
			$(".integer").inputmask('integer', {removeMaskOnSubmit: true, rightAlign: false});
			$('.hours').inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})? hrs$", prefix: ' hrs', removeMaskOnSubmit: true, rightAlign: false});
			$(".serviceMarkup").inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})?$", suffix: "%", removeMaskOnSubmit: true, rightAlign: false});

			$('.floating-line').addClass('hidden-sm').addClass('hidden-xs').removeClass('hide').css({visibility: 'visible', bottom: 0, opacity: 1});
			$('.floating-mobile-line').addClass('hidden-md').addClass('hidden-lg').removeClass('hide').css({visibility: 'visible', bottom: 0, opacity: 1});
		}, 500);

		$(document).on('click', '.useTotal>a.btn', function () {
			var obj = $(this).parents('.serviceGroup:first');
			$(obj).find('.servicePrice').val($(obj).find('.serviceTotal').val());
			$(obj).find('.servicePrice').trigger('blur');
			$(obj).find('.servicePrice').trigger('change');
		});

		$(document).on('click', '.deleteService, .deleteBundle', function(){
			let bundleRecords = $(this).closest('.bundleRecords');
			$(this).closest('.panel-heading').find('.taxable').trigger('click');
			var service_id = $(this).attr('data-service_id');
			if(service_id)
				$('#estimateForm').append('<input type="hidden" name="deleted_services[]" value="' + service_id + '">');

			if(!service_id) {
				service_id = $(this).parent().find('input[data-service_id]:first').attr('data-service_id');
			}
			delete servicesCalc[service_id];

			if($(this).hasClass('deleteBundle')){
				$.each($(this).parent().parent().find('.serviceGroup'), function(key, val){
					$(val).find('.deleteService').click();
					sleep(300);
				});
			}
			$(this).parents('section:first').remove();

			/*if($('#estimateForm').find('.serviceGroup').length)
			{
				$.each($('#readyServices').find('.serviceGroup'), function(key, val){
					$(val).css('padding-bottom', '0px');
				});
				$('#readyServices').find('.serviceGroup:last').css('padding-bottom', '70px');
			}*/
			totalBlock();

			if(!$('.deleteService').length)
				$('.saveForm, .pdf-preview').addClass('disabled').attr('disabled', 'disabled');
			totalBundle($(bundleRecords));
			return false;
		});
		$(document).on('click', '.empType', function(){
			var crewId = $(this).data('crew_id');
			var crewName = $(this).text();
			var rate = $(this).data('crew_rate');
			$(this).parents('.panel-body:first').next('.panel-body').append('<label class="btn btn-success inline m-r-xs" data-rate="' + rate + '" data-emp_type_id="' + crewId + '" style="padding: 3px 5px;">' + crewName + ' <a href="#" class="moveFromCrew">x</a><input type="hidden" name="service_crew[' + $(this).data('service_id') + '][]" data-service_id="' + $(this).data('service_id') + '" value="' + crewId + '"></label>');
			$('[name="service_crew[' + $(this).data('service_id') + '][]"]:first').trigger('change');

			if(location.href.indexOf('treedoctors') + 1) {
				if(crewId == 9 || crewId == 10)
				{
					$(this).parents('.row:first').find('#boomErrorMsg').text('');
					var errs = true;
					var veh = $(this).parents('.row:first').find('.service_vehicle');
					$.each($(veh), function (key, val) {
						if($(val).val() == 6)
							errs = false;

					});
					if(errs)
						$(this).parents('.row:first').find('#boomErrorMsg').text('Are you sure you want to send out boom operator without boom truck');
				}
				else if(crewId == 12)
				{
					$(this).parents('.row:first').find('#stumpErrorMsg').text('');
					var errs = true;
					var trls = $(this).parents('.row:first').find('.service_trailer');
					$.each($(trls), function (key, val) {
						if($(val).val() == 4)
							errs = false;
					});
					if(errs)
						$(this).parents('.row:first').find('#stumpErrorMsg').text('Are you sure you want to send out stump grinder operator without stump grinder');
				}
			}

			get_man_hours($(this));
			return false;
		});
		$(document).on('click', '.moveFromCrew', function(){
			var obj = $(this).parent().parent();
			var tmpId = $(this).parent().find('input[data-service_id]:first').attr('data-service_id');
			$(this).parents('label:first').remove();
			$('[name="service_time[' + tmpId + ']"]').trigger('change');
			get_man_hours(obj);
			return false;
		});
		$(document).on('blur', '.serviceTime, .serviceTravelTime, .serviceDisposalTime, .overheadRate, .serviceMarkup, .extraExpenses, .expenseAmount', function(){
			if($(this).is('.serviceTime')) {
				get_travel_time($(this));
				//$(this).parents('.serviceGroup:first').find('.serviceTravelTime').trigger('change');
			}
			get_man_hours($(this));
			return false;
		});
		$(document).on('keyup', '.serviceTime, .serviceTravelTime, .serviceDisposalTime, .overheadRate, .serviceMarkup, .extraExpenses, .expenseAmount', function(){
			if($(this).is('.serviceTime'))
				get_travel_time($(this));
			get_man_hours($(this));
			return false;
		});
		$(document).on('input', '.serviceMarkup', function () {
			get_man_hours($(this));
			return false;
		});
		$(document).on('keyup change input blur', '.servicePrice', function () {
			totalBlock();
			let isBundle = $(this).closest('section').find('input[data-name="bundles_services"]').val();
			if(isBundle !== '0' && $(this).hasClass('bundlePrice') === false)
				totalBundle(this);
			return false;
		});
		
		$(document).on('keyup change', '.productCost, .productQuantity', function (e) {
			var productCost = $(this).closest('.serviceGroup').find('.productCost').inputmask('unmaskedvalue');
			var productQuantity = $(this).closest('.serviceGroup').find('.productQuantity').val();

			if(e.type === 'change' && productQuantity < 1 || productQuantity !== '' && (productQuantity < 1 || isNaN(productQuantity))){
				productQuantity = 1;
				$(this).closest('.serviceGroup').find('.productQuantity').val(productQuantity);
			}
			var servicePrice = productCost*productQuantity;
			$(this).closest('.serviceGroup').find('.servicePrice').val(servicePrice);
			totalBlock();
			let isBundle = $(this).closest('section').find('input[data-name="bundles_services"]').val();
			if(isBundle !== '0')
				totalBundle(this);
			$(this).closest('.serviceGroup').find('.productQuantity').data('old_qty', parseFloat($(this).closest('.serviceGroup').find('.productQuantity').val()));
			return false;
		});

		$(document).on('keyup', '.bundleQty', function () {
			let items = $(this).closest('section').find('.serviceGroup');
			let newQty = $(this).val();
			if(items.length > 0 && newQty > 0){
				let oldQty = $(this).closest('header').find('[data-name="bundle_qty"]');
				$.each(items, function(key, val){
					if($(val).find('[data-name="is_product"]').val() == 1 || $(val).find('[name="is_product['+ $(val).data('service_id') +']"]').val() == 1) {
						let product = $(val).find('.productQuantity');
						if( $(product).data('old_qty') === undefined)
							$(product).data('old_qty', parseFloat($(product).val()));
						let oldServiceQty = $(product).data('old_qty');
						$(product).val(oldServiceQty / $(oldQty).val() * newQty).trigger('change');
						// $(val).closest('section').data('old_value', $(product).val() / $(oldQty).val() * newQty)
						$(product).data('old_qty', oldServiceQty / $(oldQty).val() * newQty);
						// $(productOldQty).val($(product).val() / $(oldQty).val() * newQty)
					} else {
						let service = $(val).find('.servicePrice');
						let price = $(service).val().replaceAll(Common.get_currency(), '');
						price = price.replaceAll(',', '');
						$(service).val( price/ $(oldQty).val() * newQty).trigger('change');
					}
				});
				$(oldQty).val(newQty);
			}
		});

		$(document).on('change', '.service_trailer, .tools_option', function(){
			get_man_hours($(this));
			return false;
		});

		// $(document).on('change', '.service_vehicle', function(e){
		// 	get_man_hours($(this));
		// 	e.stopImmediatePropagation();
		// });

		$(document).on('change', '.btn-upload', function () {
			var obj = $(this).parent().next();
			$(obj).html('');
			$.each($(this)[0].files, function (key, val) {
				if (val.name)
					$(obj).html($(obj).html() + "<div class='label label-info m-r-xs' style='display: inline-block;'>" + val.name + "</div>");
			});
		});
		$(document).on('click change', '.selectBundle', function(){
			let rand = Math.floor(Math.random() * (9999 - 1000 + 1)) + 1000;
			let tpl = $(bundleTpl.tpl);
			let bundleRecords = $(this).data('bundle_records');
			let isView = $(this).data('is_view');
			let serviceId = $(this).data('service_type_id');
			let description = $(this).data('description');
			let price = $(this).data('product_cost');
			$(tpl).attr('data-service_id', rand);
			$(tpl).find('#isView-').attr('id', 'isView-'+rand);
			$(tpl).find('.form-check-label').attr('for', 'isView-'+rand);

			// set priority
			let last_priority = $('#readyServices>section [data-name="service_priority"]').last().val();
			if(typeof last_priority !== 'undefined' && parseInt(last_priority) > 0){
				$(tpl).find('[data-name="service_priority"]').val(parseInt(last_priority) + 1);
			}

			let ids = [];
			let itemsIsNotCollapsed = [];
			let default_crews = [];
			$.ajax({
				url: baseUrl + 'estimates/getBundleRecordsv2',
				data: {id: rand, bundleId: serviceId},
				method: "GET",
				success: function(resp){
					if (resp.type == 'ok') {
						ids = resp.ids;
						itemsIsNotCollapsed = resp.itemsIsNotCollapsed;
						default_crews = resp.servicesDefaultCrews;
						$(tpl).find('.bundlePrice').val(price);
						$(tpl).find('.bundlePrice').attr('name', 'service_price['+ rand +']');
						$(tpl).find('.bundlePrice').attr('data-service_id', rand);
						$(tpl).find('.bundleQty').attr('data-service_id', rand).attr('name', 'quantity['+ rand +']');
						$(tpl).find('.bundleRecords').append(resp.html);
						$(tpl).find('[data-name="service_type_id"]').attr('name', 'service_type_id['+ rand +']').val(serviceId).attr('data-service_id', rand);
						$(tpl).find('[data-name="service_description"]').attr('name', 'service_description['+ rand +']').attr('data-service_id', rand).val(description);
						$(tpl).find('[name="is_view_in_pdf[]"]').attr('name', 'is_view_in_pdf['+ rand +']').attr('data-service_id', rand);
						$(tpl).find('[data-bundle_id=""]').attr('data-bundle_id', rand);
						Common.mask_currency();

						$('.integer').inputmask('integer', {removeMaskOnSubmit: true, rightAlign: false});
						init_taxable_popover();
						let view = true;
						if(isView == 0)
							view = false;
						$(tpl).find('#isView-'+rand).attr('checked', view);
						$.each(ids, function(key, val){
							get_man_hours($('.serviceGroup[data-service_id="' + val + '"]').find('.deleteService:first'));
						});
						$(tpl).find('[data-name="is_bundle"]').attr('data-service_id', rand).attr('name', 'is_bundle[' + rand + ']').change();

						$.each($(tpl).find('.serviceGroup'), function (key, val) {
							initDropzone($(val).find(".dropzone"));
							autoComplete();
							$(val).find('.nonTaxableHidden').attr('name', "non_taxable[" + $(val).data('service_id') +"]").attr('data-service_id', $(val).data('service_id')).val(0);
							$(val).find('.nonTaxable').attr('data-service_id', $(val).data('service_id'));
							$(tpl).find('[name="is_collapsed['+ $(val).data('service_id') +']"]').attr('data-service_id', $(val).data('service_id'));

							if($('#estimateForm').find('.serviceGroup').length > 0)
								$(val).find('.addCopyEquipment').removeClass('hide');

							let serviceTpl = val;
							let serviceSetups = null;
							$.each(bundleRecords, function (k, v) {
								if (v.service_id == $(serviceTpl).find('[name="service_type_id[' + $(val).data('service_id') + ']"]').val()) {
									if (v.service_attachments) {
										serviceSetups = JSON.parse(v.service_attachments);
										$.each(serviceSetups, function (key, val) {
											var setupTpl = $(serviceTpl).find('.serviceSetupTpl:first').clone();
											var tmpDiv = document.createElement('div');
											$(tmpDiv).append(setupTpl);
											$(tmpDiv).children().removeClass('serviceSetupTpl');

											$(tmpDiv).find('.service_vehicle').val(val.vehicle_id).attr('name', 'service_vehicle[' + $(serviceTpl).data('service_id') + '][]');//.attr('selected', 'selected');
											$(tmpDiv).find('.service_vehicle').attr('data-key', key).data('service_id', $(serviceTpl).data('service_id')).data('name', 'service_vehicle');
											$(tmpDiv).find('.service_trailer').attr('data-key', key).data('name', 'service_trailer');
											var opt = $(tmpDiv).find('.service_vehicle option:selected').data('option');

											if (opt !== undefined && opt) {
												var optTpl = '<div data-toggle="buttons" class="text-center btn-group m-t-xs"><i class="fa fa-check text-active"></i>';
												$.each($(opt), function (k, v) {
													var check = '';
													var active = '';
													if (v.trim() == val.vehicle_option.trim()) {
														active = ' active';
														check = 'checked="checked"';
													}
													btnClass = k % 2 ? 'btn-primary' : 'btn-warning';
													optTpl += '<label class="btn btn-xs ' + btnClass + active + '"><i class="fa fa-check text-active"></i>' + v + '<input ' + check + ' type="checkbox" class="vehicle_option" data-service_id="' + $(serviceTpl).data('service_id') + '" value="' + v.replace("\'", '&#39;').replace('\"', '&#34;') + '" name="vehicle_option[' + $(serviceTpl).data('service_id') + '][' + key + '][]"></label>';
												});
												optTpl += '</div>';
												$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"]').remove();
												$(tmpDiv).find('.service_vehicle').parents('.controls:first').append(optTpl);
											}
											$(tmpDiv).find('.service_trailer').val(val.trailer_id).attr('name', 'service_trailer[' + $(serviceTpl).data('service_id') + '][]');//.attr('selected', 'selected');
											var traOpt = $(tmpDiv).find('.service_trailer option:selected').data('traioptions');
											if (traOpt !== undefined) {
												var traOptTpl = '<div data-toggle="buttons" class="text-center btn-group m-t-xs"><i class="fa fa-check text-active"></i>';
												$.each($(traOpt), function (k, v) {
													var check = '';
													var active = '';
													if (v.trim() == val.trailer_option.trim()) {
														active = ' active';
														check = 'checked="checked"';
													}
													btnClass = k % 2 ? 'btn-primary' : 'btn-warning';
													traOptTpl += '<label class="btn btn-xs ' + btnClass + active + '"><i class="fa fa-check text-active"></i>' + v + '<input ' + check + ' type="checkbox" class="trailer_option"  data-service_id="' + $(serviceTpl).data('service_id') + '"  value="' + v.replace("\'", '&#39;').replace('\"', '&#34;') + '" name="trailer_option[' + $(serviceTpl).data('service_id') + '][' + key + '][]"></label>';
												});
												traOptTpl += '</div>';
												$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"]').remove();
												$(tmpDiv).find('.service_trailer').parents('.controls:first').append(traOptTpl);
											}
											if (val.tool_id != undefined && val.tool_id.length) {
												$.each($(val.tool_id), function (k, v) {
													$.each($(val.tools_option[k]), function (jkey, jval) {
														if (jval !== 'any' || jval !== 'Any') {
															$(tmpDiv).find('.tools input[value="' + jval + '"][data-tool_id="' + v + '"]').attr('checked', 'checked');
															$(tmpDiv).find('.tools input[value="' + jval + '"][data-tool_id="' + v + '"]').parent().addClass('active');
														}
													});
												});
											}
											$.each($(tmpDiv).find('.tools input'), function (k, v) {
												$(v).attr('name', 'tools_option[' + $(serviceTpl).data('service_id') + '][' + $(v).attr('data-tool_id') + '][' + key + '][]');
												$(v).attr('data-service_id', $(serviceTpl).data('service_id'));
											});
											$(tmpDiv).addClass('row m-b-sm pos-rlt');
											$(tmpDiv).children().addClass('serviceSetupTpl');
											$(serviceTpl).find('.serviceSetupList').append($(tmpDiv));
											$(serviceTpl).find('.serviceSetupTpl:first').parent().remove();
										});

									}
									if (!serviceSetups || !serviceSetups.length) {
										$.each($(serviceTpl).find('select[data-name="service_vehicle"]'), function (k, v) {
											$(v).attr('name', 'service_vehicle[' + $(serviceTpl).data('service_id') + '][]');
											$(v).attr('data-service_id', $(serviceTpl).data('service_id'));
											$(v).attr('data-key', k);
										});
										$.each($(serviceTpl).find('select[data-name="service_trailer"]'), function (k, v) {
											$(v).attr('name', 'service_trailer[' + $(serviceTpl).data('service_id') + '][]');
											$(v).attr('data-service_id', $(serviceTpl).data('service_id'));
											$(v).attr('data-key', k);
										});
										$.each($(serviceTpl).find('.tools input'), function (k, v) {
											$(v).attr('name', 'tools_option[' + $(serviceTpl).data('service_id') + '][' + $(v).attr('data-tool_id') + '][0][]');
											$(v).attr('data-service_id', $(serviceTpl).data('service_id'));
										});
									}
									if (v.service_class_id !== undefined && v.service_class_id)
										$(serviceTpl).find('.classSelect').attr('value', v.service_class_id);
								}
							});
							$(tpl).find('[name="bundles_services['+ $(val).data('service_id') +']"]').attr('data-service_id', $(val).data('service_id')).change();

						});

                        if(typeof classWithChildren != 'undefined')
                            $('.classSelect').select2({data: classWithChildren});

						// collapsed view services/products in the bundle
						$.each(itemsIsNotCollapsed, function(key, val){
							let section = $('section[data-service_id="' + val + '"]:first');
							section.find('.toggleChevron').click();
							section.find('.use-calc').click();
						});
						// set default crews
						$.each(default_crews, function (key, val) {
							$.each(JSON.parse(val), function (k, v) {
								$('section[data-service_id="' + key + '"]:first').find('label[data-crew_id="' + v +'"]').click();
							});
						});
					}
				},
				complete: function(){
					down();
				},
				dataType: 'json'
			});

			$(tpl).find('.serviceTitle').text($(this).text());
			$(tpl).find('.serviceDescription').val($(this).data('description').toString().stripSlashes());
			$(tpl).find('.product-select2-bundle').select2({
				data:JSON.parse($('.categoriesWithProducts').val()),
				placeholder: "Add Product",
				allowClear: true,
				dropdownCssClass: "select-product-dropdown"
			});
			$(tpl).find('.service-select2-bundle').select2({
				data:JSON.parse($('.categoriesWithServices').val()),
				placeholder: "Add Service",
				allowClear: true,
				dropdownCssClass: "select-product-dropdown"
			});
			$(tpl).find('.product-select2-bundle-toggle').click(function (e) {
				e.preventDefault();
				$(tpl).find('.product-select2-bundle').select2('open');
			});
			$(tpl).find('.service-select2-bundle-toggle').click(function (e) {
				e.preventDefault();
				$(tpl).find('.service-select2-bundle').select2('open');
			});
			$(tpl).find('.product-select2-bundle').click(function (e) {
				$(this).find(':selected').change();
				$(this).closest('footer').find('.open').removeClass('open');
			});
			$(tpl).find('.service-select2-bundle').click(function (e) {
				$(this).find(':selected').change();
				$(this).closest('footer').find('.open').removeClass('open');
			});
			$('#readyServices').append(tpl).find('.select2-choice').css('display', 'none');
			$('.saveForm, .pdf-preview').removeClass('disabled').removeAttr('disabled');
			down();

            if(typeof classWithChildren != 'undefined')
                $('.classSelect').select2({data: classWithChildren});
			$('.bundle-select2').select2('data', null);
			$('.product-select2-bundle').select2('data', null);
			setServicePriority();
		});
		$(document).on('click change', '.selectServiceItem', function () {
			let itemObj = $(this).select2('data');
			if(itemObj == null)
				return;
			var obj = $('.selectService [value="' + $('.selectService').val() + '"]');
			var rand = Math.floor(Math.random() * (9999 - 1000 + 1)) + 1000;
			let qb_access = $('.qbAccess').val();
			var productCost = 0;
			var tpl = $(serviceTpl.tpl);
			if(itemObj.is_product == 1){
				tpl = $(productTpl.tpl);
				productCost = itemObj.cost;
				$(tpl).find('.productCost').val(productCost);
				$(tpl).find('.productCost').attr('value', productCost);
			}
			var service_type_id = itemObj.service_type_id;
			var service_markup = itemObj.service_markup;

			//SETUPS
			var serviceSetups = JSON.parse(itemObj.setups);
			id = rand;
			$(tpl).attr('data-service_id', rand);
			$(tpl).find('.nonTaxable').attr('data-service_id', rand).attr('data-id', '#service-checkbox-' + rand);
			$(tpl).attr('data-service_markup', service_markup);
			$(tpl).attr('data-product_cost', productCost);

			// set priority
			let last_priority = $('#readyServices>section [data-name="service_priority"]').last().val();
			if(typeof last_priority !== 'undefined' && parseInt(last_priority) > 0){
				$(tpl).find('[data-name="service_priority"]').val(parseInt(last_priority) + 1);
			}

			$(tpl).find('[data-name="is_collapsed"]').val(itemObj.service_is_collapsed);
			if(qb_access != 0 && typeof classWithChildren != 'undefined' && classWithChildren.length > 0){
				$(tpl).find('.QBclass').show();
			}
			//SETUPS
			//$(tmpDiv).children().appendTo('.serviceSetupList');
			/*var setupTpl = $(tpl).find('.serviceSetupTpl:first').clone();
			var tmpDiv = document.createElement('div');
			$(tmpDiv).append(setupTpl);
			$(tmpDiv).children().removeClass('serviceSetupTpl');
			*/
			$.each(serviceSetups, function(key, val){
				var setupTpl = $(tpl).find('.serviceSetupTpl:first').clone();
				var tmpDiv = document.createElement('div');
				//console.log(key, setupTpl.html());
				$(tmpDiv).append(setupTpl);
				$(tmpDiv).children().removeClass('serviceSetupTpl');

				$(tmpDiv).find('.service_vehicle').val(val.vehicle_id).attr('name', itemObj.name + '[' + rand + '][]');//.attr('selected', 'selected');
				$(tmpDiv).find('.service_vehicle').attr('data-key', key);
				$(tmpDiv).find('.service_trailer').attr('data-key', key);
				var opt = $(tmpDiv).find('.service_vehicle option:selected').data('option');

				if(opt !== undefined && opt)
				{
					var optTpl = '<div data-toggle="buttons" class="text-center btn-group m-t-xs"><i class="fa fa-check text-active"></i>';
					$.each($(opt), function (k, v) {
						var check = '';
						var active = '';
						if(v.trim() == val.vehicle_option.trim())
						{
							active = ' active';
							check = 'checked="checked"';
						}
						btnClass = k % 2 ? 'btn-primary' : 'btn-warning';
						optTpl += '<label class="btn btn-xs ' + btnClass + active +'"><i class="fa fa-check text-active"></i>' + v + '<input '+ check +' type="checkbox" class="vehicle_option" data-service_id="' + rand + '" value="' + v.replace("\'", '&#39;').replace('\"', '&#34;') + '" name="vehicle_option[' + rand + '][' + key+ '][]"></label>';
					});
					optTpl += '</div>';
					$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"]').remove();
					$(tmpDiv).find('.service_vehicle').parents('.controls:first').append(optTpl);
				}
				$(tmpDiv).find('.service_trailer').val(val.trailer_id).attr('name', itemObj.name + '[' + rand + '][]');//.attr('selected', 'selected');
				var traOpt = $(tmpDiv).find('.service_trailer option:selected').data('traioptions');
				if(traOpt !== undefined)
				{
					var traOptTpl = '<div data-toggle="buttons" class="text-center btn-group m-t-xs"><i class="fa fa-check text-active"></i>';
					$.each($(traOpt), function (k, v) {
						var check = '';
						var active = '';
						if(v.trim() == val.trailer_option.trim())
						{
							active = ' active';
							check = 'checked="checked"';
						}
						btnClass = k % 2 ? 'btn-primary' : 'btn-warning';
						traOptTpl += '<label class="btn btn-xs ' + btnClass + active +'"><i class="fa fa-check text-active"></i>' + v + '<input '+ check +' type="checkbox" class="trailer_option"  data-service_id="' + rand + '"  value="' + v.replace("\'", '&#39;').replace('\"', '&#34;') + '" name="trailer_option[' + rand + '][' + key+ '][]"></label>';
					});
					traOptTpl += '</div>';
					$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"]').remove();
					$(tmpDiv).find('.service_trailer').parents('.controls:first').append(traOptTpl);
				}
				if(val.tool_id != undefined && val.tool_id.length)
				{
					$.each($(val.tool_id), function (k, v) {
						$.each($(val.tools_option[k]), function (jkey, jval) {
							if(jval !== 'any' || jval !== 'Any')
							{
								$(tmpDiv).find('.tools input[value="' + jval + '"][data-tool_id="' + v + '"]').attr('checked', 'checked');
								$(tmpDiv).find('.tools input[value="' + jval + '"][data-tool_id="' + v + '"]').parent().addClass('active');
							}
							/*else
							{
								$(tmpDiv).find('.tools input[value="Any"][data-tool_id="' + v + '"]').attr('checked', 'checked');
								$(tmpDiv).find('.tools input[value="Any"][data-tool_id="' + v + '"]').parent().addClass('active');
							}*/
						});
					});
				}
				$.each($(tmpDiv).find('.tools input'), function (k, v) {
					$(v).attr('name', 'tools_option[' + rand + '][' + $(v).attr('data-tool_id') + '][' + key + '][]');
					$(v).attr('data-service_id', rand);
				});
				$(tmpDiv).addClass('row m-b-sm pos-rlt');
				$(tmpDiv).children().addClass('serviceSetupTpl');
				$(tpl).find('.serviceSetupList').append($(tmpDiv));
			});

			if(serviceSetups && serviceSetups.length)
				$(tpl).find('.serviceSetupTpl:first').parent().remove();
			if(!serviceSetups.length)
			{
				$.each($(tpl).find('.tools input'), function (k, v) {
					//console.log($(v).attr('data-tool_id'), v);
					$(v).attr('name', 'tools_option[' + rand + '][' + $(v).attr('data-tool_id') + '][0][]');
					$(v).attr('data-service_id', rand);
					//console.log($(tmpDiv).find('.tools input[value="' + val.tools_option[k] + '"][data-tool_id="' + v + '"]')); //return false;
				});
			}

			//addCopyEquipm remove hide
			if($('#estimateForm').find('.serviceGroup').length > 0)
				$(tpl).find('.addCopyEquipment').removeClass('hide');
			//SETUPS END

			$(tpl).find('.serviceTitle').text(itemObj.text);
			if(itemObj.description)
				$(tpl).find('.serviceDescription').val(itemObj.description.toString().stripSlashes());
			$(tpl).find('.serviceMarkup').val(itemObj.service_markup.stripSlashes());
			$(tpl).find('.productCost').val(itemObj.cost);
			$(tpl).find('.toggleChevron').attr('href', '#collapse' + id);
			$(tpl).find('.collapse').attr('id', 'collapse' + id);

			if(itemObj.is_product){
				$(tpl).find('.servicePrice').val(itemObj.cost);
				// $(tpl).attr('data-service-type', 'product');
			}

			//servicePrice


			//$(tpl).find('.overheadRate').val();//TODO: EDITABLE VAR
			/*if(service_cost!=0)
				$(tpl).find('.manHoursRate').val(service_cost);*/
			let cnt = 0;
			$.each($(tpl).find('[data-name]'), function(key, val){
				// console.log($(this));
				if($(this).data('name') === 'bundles_services')
					return;
				if(!$(this).data('array'))
				{
					$(this).attr('name', $(this).data('name') + '[' + rand + ']');
					if($(this).data('service_type_id'))
						$(this).val(service_type_id);
					if($(this).data('name') == 'non_taxable'){
						$(this).attr('id', 'service-checkbox-'+ rand);
						$(this).parent().find('.nonTaxable').attr({'data-id': '#service-checkbox-' + rand}, {'data-service_id': rand});
						$(this).parent().find('.non-taxable-title').attr('id', 'service-checkbox-' + rand + '-title');
					}
				}
				else if($(this).data('name') == 'service_vehicle'){
					$(this).attr('name', $(this).data('name') + '[' + rand + '][]');
				}
				else if($(this).data('name') == 'service_trailer'){
					$(this).attr('name', $(this).data('name') + '[' + rand + '][]');
				}
				else
				{
					$(this).attr('name', $(this).data('name') + '[' + rand + '][]');
				}
				$(this).attr('data-service_id', rand);
			});
			 //return false;
			$(tpl).find(".integer").inputmask('integer', {removeMaskOnSubmit: true, rightAlign: false});
			
			Common.mask_currency($(tpl).find(".currency"));

			$(tpl).find(".decimal").inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})?$", groupSeparator: '.', removeMaskOnSubmit: true, rightAlign: false});
			$(tpl).find('.hours').inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})? hrs$", prefix: ' hrs', removeMaskOnSubmit: true, rightAlign: false});
			$(tpl).find(".serviceMarkup").inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})? %$", removeMaskOnSubmit: true, rightAlign: false});
			$(tpl).find('.classSelect').attr('value', itemObj.service_class_id);
			let isBundle = $(this).hasClass("bundle");
			if(isBundle) {
				let bundleId = $(this).data('bundle_id');
				$(tpl).find('[data-name="bundles_services"]').attr('name', 'bundles_services['+ rand +']').attr('data-service_id', rand).val(bundleId);
				$(tpl).find('header').css({
					"background-color": "white",
					"border": "none"
				});
				$(tpl).attr('data-bundle_id', bundleId);
				$(this).closest('section').find('.bundleRecords').append(tpl);
				totalBundle($(this).closest('section').find('.bundleRecords'));
			}else {
				$('#readyServices').append(tpl);
				down();
			}
			let section = $('section[data-service_id="' + rand + '"]:first');
			if(itemObj.service_default_crews){
				$.each(JSON.parse(itemObj.service_default_crews), function (k, v) {
					section.find('label[data-crew_id="' + v +'"]').click();
				})
			}
			// collapsed view a service/product
			if(itemObj.service_is_collapsed == '0') {
				section.find('.toggleChevron').click();
				section.find('.use-calc').click();
			}

			$('input[data-service_id="' + rand + '"]:first').trigger('change');
			$('.species').autocomplete({disabled:true});
			$('.species').autocomplete({disabled:false});
			get_man_hours($('.serviceGroup[data-service_id="' + rand + '"]').find('.deleteService:first'));
			initDropzone($(tpl).find(".dropzone"));
			autoComplete();
			$('.saveForm, .pdf-preview').removeClass('disabled').removeAttr('disabled');
			init_taxable_popover();
			$('.product-select2').select2('data', null);
			$('.service-select2').select2('data', null);
			$('.product-select2-bundle').select2('data', null);
			$('.service-select2-bundle').select2('data', null);
            if(typeof classWithChildren != 'undefined')
                $('.classSelect').select2({data: classWithChildren});
		});

		$(document).on('focus', ".decimal, .hours", function() {
			var obj = $(this);
			if($(this).val() == '0')
				$(this).val('');
			if($(this).val().indexOf('.') + 1) {
				setTimeout(function () {
					$(obj).setCursorPosition($(obj).val().indexOf('.'));
				}, 30);
			}
		});

		$(document).on('blur', ".decimal, .hours", function() {
			if(!$(this).val())
				$(this).val('0');
		});

		$('.createEstimateService').click(function() {
			let serviceId = $(this).data('est-service-id');
			$('.service-select2').select2('val', serviceId).trigger('change');
			return false;
		});
		$('.createEstimateProduct').click(function() {
			let serviceId = $(this).data('est-service-id');
			$('.product-select2').select2('val', serviceId).trigger('change');
			return false;
		});

		$('.createBundleService').click(function() {
			let serviceId = $(this).data('est-service-id');
			$('.floating-line option[value="' + serviceId + '"]').trigger('change');
			return false;
		});

		$(document).on('click', '.permit, .exemption', function(){
			if(!$(this).find('input[type="checkbox"]').is(':checked'))
			{
				$(this).parent().find('label').not(this).removeClass('active');
				$(this).parent().find('label').not(this).find('input[type="checkbox"]').prop('checked', false);//.trigger('change');
			}
		});
		$(document).on('input', '.manHoursRate', function(){
			var rate = parseFloat($(this).val().replace(Common.get_currency(), '').replace(' ', '').replace(',',''));
			var manHrs = $(this).parents('.serviceGroup:first').find('input.manHours').val() ? $(this).parents('.serviceGroup:first').find('input.manHours').val() : 1;
			$(this).parents('.serviceGroup:first').find('.serviceTotal').val(Math.ceil((rate * manHrs)*100)/100);
		});
		$(document).on('input', '.manHours', function(){
			var manHrs = $(this).val();
			var rate = parseFloat($(this).parents('.serviceGroup:first').find('input.manHoursRate').val().replace(Common.get_currency(), '').replace(' ', '').replace(',','')) ? parseFloat($(this).parents('.serviceGroup:first').find('input.manHoursRate').val().replace(Common.get_currency(), '').replace(' ', '').replace(',','')) : 1;
			$(this).parents('.serviceGroup:first').find('.serviceTotal').val(Math.ceil((rate * manHrs)*100)/100);
		});
		$(document).on('input', '.serviceTotal', function(){
			var serviceTotal = parseFloat($(this).val().replace(Common.get_currency(), '').replace(' ', '').replace(',',''));
			var manHrs = $(this).parents('.serviceGroup:first').find('input.manHours').val() ? $(this).parents('.serviceGroup:first').find('input.manHours').val() : 1;
			$(this).parents('.serviceGroup:first').find('.manHoursRate').val(Math.ceil((serviceTotal / manHrs)*100)/100);
		});
		$(document).on('shown.bs.popover', '.discountPopover', function() {
			$(this).parent().find('.popover:first').find('#amountDiscount').val(totalCalc.discount);
			$(this).parent().find('.popover:first').find('#commentDiscount').val($('#estimateDiscountComment').val());
			$(this).parent().find('.popover:first').find('#percentsDiscount').prop('checked', totalCalc.discountPercents ? true : false);
			$(this).parent().find('.popover:first').find('.discountPopoverContent>div').css({visibility: 'visible'});
			if($(this).parent().find('.popover:first').find('#percentsDiscount').prop('checked')) {
				$(this).parent().find('.popover:first').find('#amountDiscount').val((totalCalc.discount*100/totalCalc.totalForServices).toFixed(2));
				$(this).parent().find('.popover:first').find('#amountDiscount').inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})? %$", removeMaskOnSubmit: true, rightAlign: false});
			} else {
				Common.mask_currency($(this).parent().find('.popover:first').find('#amountDiscount'));
			}
		});
		$(document).on('click', '#saveDiscount', function() {
			var estimateDiscountValue = Common.getAmount($(this).parents('.popover-content:first').find('#amountDiscount').val());
			var estimateDiscountComment = $(this).parents('.popover-content:first').find('#commentDiscount').val();
			if(isNaN(estimateDiscountValue))
				estimateDiscountValue = 0;

			$('#estimateDiscountValue').val(estimateDiscountValue);
			$('#estimateDiscountComment').val(estimateDiscountComment);
			$('#estimateDiscountPercents').val($(this).parents('.popover-content:first').find('#percentsDiscount').prop('checked') ? 1 : 0);
			draft_multiple(
			[{'field' : $('#estimateDiscountValue').attr('name'), 'value' : $('#estimateDiscountValue').val()},
			 {'field' : $('#estimateDiscountComment').attr('name'), 'value' : $('#estimateDiscountComment').val()},
				{'field' : $('#estimateDiscountPercents').attr('name'), 'value' : $('#estimateDiscountPercents').val()}]
			);
			$(this).parents('.popover:first').find('.close').click();
			totalBlock();
		});
		$(document).on('change', '#percentsDiscount', function() {
			if($(this).prop('checked')) {
				$(this).parents('.popover:first').find('#amountDiscount').inputmask('remove');
				$(this).parents('.popover:first').find('#amountDiscount').inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})? %$", removeMaskOnSubmit: true, rightAlign: false});
			} else {
				Common.unmask_currency($(this).parents('.popover:first').find('#amountDiscount'));
				Common.mask_currency($(this).parents('.popover:first').find('#amountDiscount'));
			}
		});
		$(document).on('click', '.addAttach', function (){
			var obj = $(this).parent().parent();

			var setupTpl = $(obj).find('.serviceSetupTpl:first').clone();
			let len = $(obj).find('.serviceSetupTpl:last').find('.service_vehicle').data('key') + 1;
			let serviceId = $(obj).find('.serviceSetupTpl:first').find('.service_vehicle').data('service_id');
			// var len = $(obj).find('.serviceSetupTpl').length;
			//console.log(obj); return false;
			var tmpDiv = document.createElement('div');
			$(tmpDiv).append(setupTpl);
			$(tmpDiv).children().removeClass('serviceSetupTpl');
			$(tmpDiv).find('.service_vehicle option[selected]').removeAttr('selected');
			$(tmpDiv).find('.service_trailer option[selected]').removeAttr('selected');
			$(tmpDiv).find('.service_tools option[selected]').removeAttr('selected');
			$(tmpDiv).find('.service_vehicle').attr('data-key', len).attr('name', 'service_vehicle[' + serviceId + '][]');
			$(tmpDiv).find('.service_trailer').attr('data-key', len).attr('name', 'service_trailer[' + serviceId + '][]');
			$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"]').remove();
			$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"]').remove();
			//console.log($(tmpDiv).find('.tools input')); return false;
			$.each($(tmpDiv).find('.tools input'), function(jk, jv){
				$(jv).removeAttr('checked');
				$(jv).parent().removeClass('active');

				$(jv).attr('name', 'tools_option[' + $(jv).attr('data-service_id') + ']['+ $(jv).attr('data-tool_id') +']['+len+'][]');

			});
			$(tmpDiv).addClass('row m-b-sm pos-rlt');
			$(tmpDiv).children().addClass('serviceSetupTpl');
			$(obj).find('.serviceSetupList').append($(tmpDiv));
			//$(tmpDiv).children().appendTo('.serviceSetupList');
		});
		$(document).on('click', '.addExpenseRow', function () {
			var obj = $(this).parents('.serviceGroup:first');
			var serviceId = $(obj).data('service_id');
			$(obj).find(".serviceExtraExpenses .expenseTitle").parent().removeClass('has-error');
			$(obj).find(".serviceExtraExpenses .expenseAmount").parent().removeClass('has-error');
			var emptyTitles = $(obj).find(".serviceExtraExpenses .expenseTitle").filter(function() {return !this.value;});
			var emptyAmounts = $(obj).find(".serviceExtraExpenses .expenseAmount").filter(function() {return !this.value;});
			if((emptyTitles || emptyAmounts) && (emptyTitles.length || emptyAmounts.length)) {
				emptyTitles.each(function () {
					$(this).parent().addClass('has-error');
				});
				emptyAmounts.each(function () {
					$(this).parent().addClass('has-error');
				});
				return false;
			}

			var tpl = $(obj).find('.expenseRow').html();
			var div = document.createElement('div');
			$(div).append(tpl);
			var expenseNumber = $(obj).find('.serviceExtraExpenses .expenseAmount:last').length && $(obj).find('.serviceExtraExpenses .expenseAmount:last').attr('name').split('][')[1] != undefined ? parseInt($(obj).find('.serviceExtraExpenses .expenseAmount:last').attr('name').split('][')[1]) + 1 : 0;
			$(div).find('.expenseTitle').attr('value', '').attr('data-service_id', serviceId).attr('name', 'expenses[' + serviceId + '][' + expenseNumber + '][title]');
			$(div).find('.expenseAmount').attr('value', '').attr('data-service_id', serviceId).attr('name', 'expenses[' + serviceId + '][' + expenseNumber + '][amount]');
			$(obj).find('.serviceExtraExpenses').append($(div).html());
			
			Common.mask_currency($(obj).find(".serviceExtraExpenses .currency:last"));
			get_man_hours($(this));
			return false;
		});
		$(document).on('click', '.removeExpense', function () {
			var obj = $(this).parents('.serviceGroup:first');
			var parent = $(obj).find('.serviceExtraExpenses:first');
			$(this).parents('.row:first').remove();
			get_man_hours(parent);
			return false;
		});
		$(document).on('click', '.removeAttach', function (){
			var parentService = $(this).parents('.serviceGroup:first');
			var parent = $(parentService).find('.serviceExtraExpenses:first');
			var obj = $(this).parents('.row.pos-rlt:first');
			var id = $(obj).find('[data-name="service_vehicle"]').attr('data-service_id');
			if(id == undefined)
				var id = $(this).attr('data-service_id');

			var service = $(this).parents('.serviceGroup:first');
			if($(service).find('.serviceSetupTpl').length > 1)
			{
				$(obj).remove();
				$(service).find('[name="service_price[' + id + ']"]').change();
			}
			get_man_hours(parent);
			return false;
		});
		$(document).on('change', '.service_vehicle', function(){
			get_man_hours($(this));
			var obj = $(this).parents('.controls:first');
			var options = $(this).find('option:selected').data('option');
			var id =  $(this).parents('.serviceGroup:first').find('[data-name="empType"]:first').attr('data-service_id');
			var keyAttr = $(this).attr('data-key');
			var len = $(this).parents('.serviceSetupList:first').find('.serviceSetupTpl').length;
			var val = $(this).val();
			var errText = $(this).parents('.row.m-n:first').find('#boomErrorMsg');
			//console.log(val, $(errText).text());
			if($(errText).text() != '' && val == 6)
				$(errText).text('');
			if(options != undefined && options.length)
			{
				var tpl = '<div data-toggle="buttons" class="text-center btn-group m-t-xs"><i class="fa fa-check text-active"></i>';
				$.each($(options), function (key, val) {
					btnClass = key % 2 ? 'btn-primary' : 'btn-warning';
					tpl += '<label class="btn btn-xs ' + btnClass + '"><i class="fa fa-check text-active"></i>' + val + '<input type="checkbox" value="' + val.replace("\'", '&#39;').replace('\"', '&#34;') + '" class="vehicle_option"  data-name="vehicle_option" data-service_id="'+ id +'" name="vehicle_option[' + id + '][' + keyAttr + '][]"></label>';
				});
				tpl += '</div>';
			}
			check_equipment();
			//console.log($(obj).find('[data-toggle="buttons"]').length);

			if($(obj).find('[data-toggle="buttons"]').length)
				$(obj).find('[data-toggle="buttons"]').replaceWith(tpl);
			else
				$(obj).append(tpl);
		});
		$(document).on('change', '.service_trailer', function(){

			var obj = $(this).parents('.controls:first');
			var options = $(this).find('option:selected').data('traioptions');
			var id =  $(this).parents('.serviceGroup:first').find('[data-name="empType"]:first').attr('data-service_id');
			var len = $(this).parents('.serviceSetupList:first').find('.serviceSetupTpl').length;
			var keyAttr = $(this).attr('data-key');
			var val = $(this).val();
			var errText = $(this).parents('.row.m-n:first').find('#stumpErrorMsg');
			//console.log(val, $(errText).text());
			if($(errText).text() != '' && val == 4)
				$(errText).text('');

			if(options != undefined && options.length)
			{
				var tpl = '<div data-toggle="buttons" class="text-center btn-group m-t-xs"><i class="fa fa-check text-active"></i>';
				$.each($(options), function (key, val) {
					btnClass = key % 2 ? 'btn-primary' : 'btn-warning';
					tpl += '<label class="btn btn-xs ' + btnClass + '"><i class="fa fa-check text-active"></i>' + val + '<input type="checkbox" class="trailer_option" value="' + val.replace("\'", '&#39;').replace('\"', '&#34;') + '" data-name="trailer_option" data-service_id="'+ id +'" name="trailer_option[' + id + '][' + keyAttr+ '][]"></label>';
				});
				tpl += '</div>';
			}
			check_equipment();
			if($(obj).find('[data-toggle="buttons"]').length)
				$(obj).find('[data-toggle="buttons"]').replaceWith(tpl);
			else
				$(obj).append(tpl);
		});
		$(document).on('change', '.service_tools', function(){

			var obj = $(this).parents('.controls:first');
			var options = $(this).find('option:selected').data('tooloptions');
			var id =  $(this).parents('.serviceGroup:first').find('[data-name="empType"]:first').attr('data-service_id');
			var len = $(this).parents('.serviceSetupList:first').find('.serviceSetupTpl').length;

			if(options != undefined && options.length)
			{
				var tpl = '<div data-toggle="buttons" class="text-center btn-group m-t-xs"><i class="fa fa-check text-active"></i>';
				$.each($(options), function (key, val) {
					btnClass = key % 2 ? 'btn-primary' : 'btn-warning';
					tpl += '<label class="btn btn-xs ' + btnClass + '"><i class="fa fa-check text-active"></i>' + val + '<input type="checkbox" value="' + v.replace("\'", '&#39;').replace('\"', '&#34;') + '" data-name="tools_option" data-service_id="'+ id +'" name="tools_option[' + id + '][' + (len - 1)+ '][]"></label>';
				});
				tpl += '</div>';
			}

			if($(obj).find('[data-toggle="buttons"]').length)
				$(obj).find('[data-toggle="buttons"]').replaceWith(tpl);
			else
				$(obj).append(tpl);
		});

		countServices = $('.serviceGroup');
		if(countServices.length > 1)
		{
			$.each($(countServices), function(key, val){
				if(key)
				{
					$(val).find('.addCopyEquipment').removeClass('hide');
				}
			});
		}
		init_taxable_popover();

		$(document).on('click', '.addCopyEquipment', function(){
			let serviceObj = $(this).parents('.serviceGroup:first')[0];
			let objDiv = $(serviceObj).find('.serviceSetupList');
			let  serId = $(serviceObj).attr('data-service_id');

			let allBlock = $('#estimateForm .serviceGroup').not('[data-service-type="product"]');
			let lastBlocks;
			$.each(allBlock, function (k,v) {
				if(serviceObj == v)
					lastBlocks = allBlock[k-1];
			});
			if(!lastBlocks)
				return;

			let crews = $(lastBlocks).find('.selectedCrew input');
			let tpl = $(lastBlocks).find('.serviceSetupList').clone();
			let newCrews = $(serviceObj).find('.empType:first').parent();

			$(serviceObj).find('.selectedCrew label').remove();
			$.each($(crews), function (key, val) {
				$(newCrews).find('[data-crew_id='+ $(val).val() +']').click();
			});
			/*$(tpl).find('.service_vehicle').attr('name', 'service_vehicle[' + serId + '][]').attr('data-service_id', serId).attr('data-name', 'service_vehicle[' + serId + '][]');

			$(tpl).find('.service_trailer').attr('name', 'service_trailer[' + serId + '][]').attr('data-service_id', serId).attr('data-name', 'service_trailer[' + serId + '][]');*/
			//$(tpl).find('.service_trailer').val($(lastBlocks).find('.serviceSetupList '));
			$.each($(tpl).find('.serviceSetupTpl'), function (key, val) {
				$(val).find('.service_vehicle').attr('name', 'service_vehicle[' + serId + '][]').attr('data-service_id', serId).attr('data-name', 'service_vehicle[' + serId + '][]');
				$(val).find('.service_vehicle').val($($(lastBlocks).find('.serviceSetupList').find('.service_vehicle')[key]).val());
				$(val).find('.service_trailer').attr('name', 'service_trailer[' + serId + '][]').attr('data-service_id', serId).attr('data-name', 'service_trailer[' + serId + '][]');
				$(val).find('.service_trailer').val($($(lastBlocks).find('.serviceSetupList').find('.service_trailer')[key]).val());
				$.each($(val).find('.tools_option'), function (k, v) {
					$(v).attr('name', 'tools_option[' + serId + ']['+ $(v).data('tool_id') +']['+key+'][]');
					$(v).attr('data-name', 'tools_option[' + serId + ']['+ $(v).data('tool_id') +']['+key+'][]');
					$(v).attr('data-service_id', serId);
				});

				$.each($(val).find('.vehicle_option'), function (k, v) {
					$(v).attr('name', 'vehicle_option[' + serId + ']['+key+'][]');
					$(v).attr('data-name', 'vehicle_option[' + serId + ']['+key+'][]');
					$(v).attr('data-service_id', serId);
				});
				$.each($(val).find('.trailer_option'), function (k, v) {
					$(v).attr('name', 'trailer_option[' + serId + ']['+key+'][]');
					$(v).attr('data-name', 'trailer_option[' + serId + ']['+key+'][]');
					$(v).attr('data-service_id', serId);
				});
			});
			$(tpl).find('.removeAttach').attr('data-service_id', serId);

			//console.log(objDiv); return false;
			$(objDiv).replaceWith($(tpl));
			$('.serviceGroup[data-service_id="' + serId + '"]').find('[name="service_price[' + serId + ']"]').change();
			// $('.serviceGroup[data-service_id="' + serId + '"]').find('[name="service_vehicle[' + serId + '][]"]').change();

			//$(objDiv).parent().append();
			/*$.each($(blocks), function (key, val) {
				serVeh = $(val).find('.service_vehicle');
				$.each($(serVeh), function (k, v) {
					if($(v).val() != undefined && $(v).val() != '')
					{
						console.log($(serVeh)[k].find('.serviceSetupList')); return false;
						//$(tpl).find('.serviceSetupTpl:first').clone()
					}
				});
			});*/
		});
		/*$(document).on('click', '.controls [data-toggle="buttons"] label', function(){


			$(objDiv).replaceWith($(tpl));

		});
		*/
        $('.popover-markup>.trigger').popover({
			html: true,
			placement : 'top',
			title: function () {
				return $(this).parent().find('.head').html();
				},
			content: function () {
				return $(this).parent().find('.content').html();
			}

        }).click(function(e) {
        	$(this).popover('toggle');
        	e.stopPropagation();
        });

		$('.popover-markup').on('shown.bs.popover', '.tax',  function (e) {
			if ($('[name="estimate_hst_disabled"]').val() == 2) {
				$('#inclTax').prop('checked', true);
			}
			const data_json = $('#allTaxes').val();

			if (typeof(data_json) !== "undefined") {
				const data = JSON.parse(data_json);
				$('.select2Tax').select2({data: data});
				$('.select2Tax').select2( 'val', $('.taxLabelForSelect2').val());
			}

			$('.select2Tax').on('change', function () {
				$('.popover-markup>.trigger').popover('hide');
				$('.taxLabelForSelect2').val($(this).val()).trigger('change');

				if (withSetTax) {
					setTax();
				}
			});
		});

		init_taxable_popover();

		$(document).on('change', '.nonTaxable', function(e) {
			if(this.checked) {
				$($(this).attr("data-id")).val('1');
				$($(this).attr("data-id")+'-title').show();
			}else {
				$($(this).attr("data-id")).val('0');
				$($(this).attr("data-id")+'-title').hide();
			}
			totalBlock();
			$(document).find('input[name="non_taxable['+ $(this).data('service_id') +']"]').trigger('change');
			var popover_id = $(this).closest('.popover').attr("id");
			$('[aria-describedby="'+popover_id+'"]').trigger('click');
		});

		$(document).on('shown.bs.popover', '.taxable', function (e) {
			const tax = $(this).closest('section').find(".nonTaxableHidden").val();
			if (tax == 1) {
				$(".nonTaxable").prop("checked", true);
			}
		});

		$(document).on('click', '.useTax', function(e) {
			withSetTax = false;

			const value = $('.taxRecommendation').val();
			const text = 'Tax (' + value + '%)';

			$('.tax').text(value);
			$('#tax').select2( 'val', text).trigger('change');
			$('.taxLabelTotal').text('Tax');
			TAX_RATE =  value / 100 + 1;
			TAX_PERC = value;
			$('.taxRate').val(TAX_RATE);
			$('.taxValue').val(TAX_PERC);
			$('.taxName').val('Tax');
			$('.taxLabelForSelect2').val(text);
			$('.taxLabel').text('(' + value + '%)');

			totalBlock();

			draft_multiple(
			[{'field' : $('.taxRate').attr('name'), 'value' : $('.taxRate').val()}, {'field' : $('.taxValue').attr('name'), 'value' : $('.taxValue').val()},
			 {'field' : $('.taxName').attr('name'), 'value' : $('.taxName').val()}, {'field' : $('.taxLabelForSelect2').attr('name'), 'value' : $('.taxLabelForSelect2').val()}]
			);

			$('.recommendation').hide();

			withSetTax = true;
		});

		$(document).on('click', '.toggleChevron', function () {
			if($(this).find('i').attr('class') == 'fa fa-chevron-down') {
				$(this).find('i').attr('class', 'fa fa-chevron-up');
			}else{
				$(this).find('i').attr('class', 'fa fa-chevron-down');
			}
		});

		$(document).on('focusout', '.bundleQty', function () {
			if($(this).val() == '' || $(this).val() == 0){
				$(this).val(1).keyup();
			}
		});

		$.each($(('.serviceGroup')), function (key, val) {
			let id = $(this).data('service_id');
			if($(this).find('[name="is_collapsed[' + id + ']"]').val() == 0) {
				$(this).find('.toggleChevron').click();
				$(this).find('.use-calc').click();
			}
		});

		$("[name='clean_up'], [name='disposal_brush'], [name='disposal_wood']").change(function() {
			if(this.checked) {
				$(this).val(1);
			}else{
				$(this).val(0);
			}
		});
		$(document).on('change','#inclTax', function(){
			let value = 0;
			if($('#inclTax').is(':checked'))
				value = 2;
			$('[name="estimate_hst_disabled"]').val(value).change();
			totalBlock();
		});
		$('#estimateForm').submit(function(){
			$('.classSelect, .servicePrice, .serviceDescription').attr('disabled', false);
		});
	});

	function setTax(first= false) {
		$.ajax({
			type: "GET",
			url: baseUrl + "estimates/getTaxValue",
			dataType: 'json',
			data: { text: $('.taxLabelForSelect2').val() }
		}).done(function (msg) {
			let value = 0;
			let name;
			if (typeof msg['value'] !== "undefined") {
				value = msg['value'];
				name = msg['name'];
			} else {
				if ($('.taxLabelForSelect2').val() === 'Tax (' + $('.taxRecommendation').val() + '%)') {
					value = $('.taxRecommendation').val();
					name = 'Tax';
				} else {
					value = $('.taxOldValue').val();
					name = $('.taxOldName').val();

					withSetTax = false;

					$('#tax').val(name + ' (' + value + '%)').trigger('change');
				}
			}

			TAX_RATE = value / 100 + 1;
			TAX_PERC = value;
			$('.taxRate').val(TAX_RATE);
			$('.taxValue').val(TAX_PERC);
			$('.taxName').val(name);
			$('.tax').text(TAX_PERC);
			$('.taxLabel').text('(' + TAX_PERC + '%)').attr('title', name + ' (' + TAX_PERC + '%)');

			if (first && draft.tax_name !== undefined && draft.tax_value !== undefined) {
				$('.taxLabelForSelect2').val(draft.tax_label);
				$('.taxLabel').html(draft.tax_label).attr('title', draft.tax_label);
				$('.taxValue').val(draft.tax_value);
				$('.tax').text(draft.tax_value);
				$('.taxName').val(draft.tax_name);
				TAX_RATE = draft.tax_value / 100 + 1;
				TAX_PERC = draft.tax_value;
				$('.taxRate').val(TAX_RATE);

				$('.select2Tax').select2('val', $('.taxLabelForSelect2').val());
			}

			totalBlock();
			checkRecommendationTax();

			draft_multiple(
			[{'field' : $('.taxRate').attr('name'), 'value' : $('.taxRate').val()}, {'field' : $('.taxValue').attr('name'), 'value' : $('.taxValue').val()},
			 {'field' : $('.taxName').attr('name'), 'value' : $('.taxName').val()}, {'field' : $('.taxLabelForSelect2').attr('name'), 'value' : $('.taxLabelForSelect2').val()}]
			);

			withSetTax = true;
		});
	}

	function checkRecommendationTax() {
		const originalTax = $('.taxValue').val();
		const recommendationTax = $('.taxRecommendation').val();
		if (recommendationTax && originalTax !== recommendationTax) {
			$('.recommendation').show();
		} else {
			$('.recommendation').hide();
		}
	}

	function init_taxable_popover(){
		$('.taxable').popover({
			html: true,
			placement: 'top',
			container: 'body',
			content: function () {
				return $(this).parent().find('.content').html();
			}
		});
	}

	function totalBundle(element) {
		let records = $(element).closest('.bundleRecords').find('.serviceGroup');
		let total = 0;

		$.each(records, function (index, value) {
			let price = $(value).find('.servicePrice').val();
			if(!price)
				return;
			price = price.replaceAll(Common.get_currency() , '');
			total += parseFloat(price.replaceAll(',' , ''));
		});
		$(element).closest('.bundle').find('.bundlePrice').val(total);
	}

	function down() {
		let n = $(document).height();
		$('html, body').animate({ scrollTop: n }, 1500);
	}
	function sleep(milliseconds) {
		const date = Date.now();
		let currentDate = null;
		do {
			currentDate = Date.now();
		} while (currentDate - date < milliseconds);
	}
	document.addEventListener("DOMContentLoaded", calcTotalsInBundles);
	function calcTotalsInBundles(){
		let bundles = $('#readyServices').find('.bundleTotal');
		$.each(bundles, function (index, value) {
			totalBundle($(value).find('.bundleRecords'));
		});

	}

	function previewPDF(){
		$("#estimate-preview-modal iframe").contents().find("body").html('');
		let clientId = $('#client_id').val();
		let leadId = $('[name="lead_id"]').val();
		let brandId = $('[name="estimate_brand_id"]').val();
		let link = baseUrl + 'estimates/preview_estimate_pdf/'+clientId+'/'+leadId+'/'+brandId;
		$('#estimate-preview-modal iframe').attr('src', link);
		$('#estimate-preview-modal').modal();
		$('#processing-modal').modal('show');

	}

	function setServicePriority() {
		$.each($('#readyServices>section'), function(key, val){
			$(val).attr('data-service_priority', key + 1);
		});
	}
