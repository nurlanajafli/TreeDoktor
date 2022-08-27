var tags = [];

$.each(trees, function(key, val){
	tags.push(val.tree_common_name);
});
$(document).ready(function(){
	console.log(draft);
	// return;

	if(draft.service_type_id != undefined) {
		$('.pdf-preview').hide();
		let qb_access = $('.qbAccess').val();
		let bundles_services = [];
		let bundles = [];
		let checkService = 0;
		$.each(draft.service_type_id, function(key, val){
			var rand = key;
			var service_markup = null;
			var service_type_id = draft.service_type_id[key];
			var tempDiv = document.createElement('div');
			id = rand;
			if(draft.is_product!=undefined && draft.is_product[key]!=undefined && draft.is_product[key]==1)
				tempDiv.innerHTML = productTpl.tpl;
			else if(draft.is_bundle!=undefined && draft.is_bundle[key]==1){
				tempDiv.innerHTML = bundleTpl.tpl;
			}
			else {
				tempDiv.innerHTML = serviceTpl.tpl;
				if(draft.bundles_services == undefined || draft.bundles_services[key] == undefined)
					checkService++;
				if(checkService > 1)
					$(tempDiv).find('.addCopyEquipment').removeClass('hide');

			}

			if(draft.is_product!=undefined && draft.is_product[key]!=undefined && draft.is_product[key]==1){
				var tpl = $(productTpl.tpl);
				if($(".product-select2").select2("val", service_type_id).select2('data')) {
					service_markup = $(".product-select2").select2("val", service_type_id).select2('data').service_markup;
					$(tempDiv).find('.serviceTitle').text($(".product-select2").select2("val", service_type_id).select2('data').text);
				}
			}
			else if(draft.is_bundle!=undefined && draft.is_bundle[key]==1){
				var tpl = $(bundleTpl.tpl);
				service_markup = $(document).find('.selectService').find('select option[data-service_type_id="' + service_type_id + '"]').attr('data-service_markup');
				$(tempDiv).find('.serviceTitle').text($(document).find('.selectService:first').find('select option[data-service_type_id="' + service_type_id + '"]').text());
			}
			else{
				var tpl = $(serviceTpl.tpl);
				console.log(service_type_id);
				let serviceData = $(".service-select2").select2("val", service_type_id).select2('data');
				if(serviceData){
					service_markup = serviceData.service_markup;
					$(tempDiv).find('.serviceTitle').text(serviceData.text);
				}
			}

			if(qb_access && typeof classWithChildren != 'undefined' && classWithChildren.length > 0){
				$(tempDiv).find('.QBclass').show();
			}
			// console.log($('.qbAccess').val());
			// var service_markup = $(document).find('.selectService').find('ul li a[data-service_type_id="' + service_type_id + '"]').attr('data-service_markup');
			// console.log(service_type_id);
			// var service_markup = $(".selectServiceItem").select2("val", service_type_id).select2('data').service_markup;

			$(tempDiv).find('.serviceGroup').attr('data-service_id', rand);
			$(tempDiv).find('.serviceGroup').attr('data-service_markup', service_markup);
			//$(tempDiv).find('.overheadRate').val(15);//TODO: EDITABLE VAR
			$(tempDiv).find('.serviceMarkup').val(service_markup);

			// $(tempDiv).find('.serviceTitle').text($(document).find('.selectService:first').find('ul li a[data-service_type_id="' + service_type_id + '"]').text());

			if(typeof draft.is_collapsed !== 'undefined' && typeof draft.is_collapsed[key] !== 'undefined')
				$(tempDiv).find('[data-name="is_collapsed"]').attr('value', draft.is_collapsed[key]).attr('name', 'is_collapsed[' + rand + ']').attr('data-service_id', rand);

			$(tempDiv).find('[data-name="service_type_id"]').attr('value', draft.service_type_id[key]).attr('name', 'service_type_id[' + rand + ']').attr('data-service_id', rand);
			$(tempDiv).find('[data-name="non_taxable"]').attr('id', 'service-checkbox-'+ rand).attr('name', 'non_taxable[' + rand + ']').attr('data-service_id', rand);
			$(tempDiv).find('.nonTaxable').attr('data-id', '#service-checkbox-' + rand).attr('data-service_id', rand);
			$(tempDiv).find('.non-taxable-title').attr('id', 'service-checkbox-' + rand + '-title');
			$(tempDiv).find('[name="is_view_in_pdf[]"]').attr('name', 'is_view_in_pdf[' + rand + ']').attr('data-service_id', rand);
			if(typeof draft.non_taxable !== "undefined" && typeof draft.non_taxable[key] !== "undefined" && draft.non_taxable[key] == 1){
				$(tempDiv).find('.nonTaxableHidden').val(1);
				$(tempDiv).find('.non-taxable-title').show();
			}
			if(typeof draft.is_view_in_pdf !== "undefined" && typeof draft.is_view_in_pdf[key] !== "undefined" && draft.is_view_in_pdf[key] == 'on'){
				$(tempDiv).find('[name="is_view_in_pdf[' + key + ']"]').attr('checked', 'checked');
			}
			//$(tempDiv).find('[data-name="service_size"]').attr('value', draft.service_size[key]).attr('name', 'service_size[' + rand + ']').attr('data-service_id', rand);
			//$(tempDiv).find('[data-name="service_reason"]').attr('value', draft.service_reason[key]).attr('name', 'service_reason[' + rand + ']').attr('data-service_id', rand);
			//$(tempDiv).find('[data-name="service_species"]').attr('value', draft.service_species[key]).attr('name', 'service_species[' + rand + ']').attr('data-service_id', rand);

			//$(tempDiv).find('[data-name="service_permit"]').attr('name', 'service_permit[' + rand + ']').attr('data-service_id', rand);
			//$(tempDiv).find('[data-name="service_exemption"]').attr('name', 'service_exemption[' + rand + ']').attr('data-service_id', rand);

			$(tempDiv).find('[data-name="service_description"]').text(draft.service_description[key]).attr('name', 'service_description[' + rand + ']').attr('data-service_id', rand);
			if(draft.service_time!=undefined && draft.service_time[key]!=undefined)
				$(tempDiv).find('[data-name="service_time"]').attr('value', draft.service_time[key]).attr('name', 'service_time[' + rand + ']').attr('data-service_id', rand);
			
			if(draft.service_travel_time!=undefined && draft.service_travel_time[key]!=undefined)
					$(tempDiv).find('[data-name="service_travel_time"]').attr('value', draft.service_travel_time[key]).attr('name', 'service_travel_time[' + rand + ']').attr('data-service_id', rand);
			
			if(draft.service_disposal_time!=undefined && draft.service_disposal_time[key]!=undefined)
				$(tempDiv).find('[data-name="service_disposal_time"]').attr('value', draft.service_disposal_time[key]).attr('name', 'service_disposal_time[' + rand + ']').attr('data-service_id', rand);

			if(draft.service_price!=undefined && draft.service_price[key]!=undefined)
				$(tempDiv).find('[data-name="service_price"]').attr('value', draft.service_price[key]).attr('name', 'service_price[' + rand + ']').attr('data-service_id', rand);

			if(draft.product_cost!=undefined && draft.product_cost[key]!=undefined)
				$(tempDiv).find('[data-name="product_cost"]').attr('value', draft.product_cost[key]).attr('name', 'product_cost[' + rand + ']').attr('data-service_id', rand);

			if(draft.quantity!=undefined && draft.quantity[key]!=undefined)
				$(tempDiv).find('[data-name="quantity"]').attr('value', draft.quantity[key]).attr('name', 'quantity[' + rand + ']').attr('data-service_id', rand);

			if(draft.class != undefined && draft.class[key] != undefined)
				$(tempDiv).find('[data-name="class"]').attr('value', draft.class[key]).attr('name', 'class[' + rand + ']').attr('data-service_id', rand);

			$(tempDiv).find('[data-name="service_overhead_rate"]').attr('value', draft.service_overhead_rate ? draft.service_overhead_rate[key] : '').attr('name', 'service_overhead_rate[' + rand + ']').attr('data-service_id', rand);
			$(tempDiv).find('[data-name="service_markup_rate"]').attr('value', draft.service_markup_rate ? draft.service_markup_rate[key] : '').attr('name', 'service_markup_rate[' + rand + ']').attr('data-service_id', rand);

			//SETUPS
			if(draft.service_vehicle != undefined && draft.service_vehicle[key] != undefined)
			{
				$.each(draft.service_vehicle[key], function(k, v){
					var setupTpl = $(tpl).find('.serviceSetupTpl:first').clone();
					var expensesBlock = '';
					var tmpDiv = document.createElement('div');
					$(tmpDiv).append(setupTpl);
					$(tmpDiv).children().removeClass('serviceSetupTpl');
					$(tmpDiv).find('[data-name="service_trailer"] option[value="' +  draft.service_trailer[key][k] + '"]').attr('selected', 'selected');
					$(tmpDiv).find('[data-name="service_trailer"]').attr('name', 'service_trailer[' + rand + '][]').attr('data-service_id', rand).attr('data-key', k);
					$(tmpDiv).find('[data-name="service_vehicle"]  option[value="' + v + '"]').attr('selected', 'selected');
					$(tmpDiv).find('[data-name="service_vehicle"]').attr('name', 'service_vehicle[' + rand + '][]').attr('data-service_id', rand).attr('data-key', k);
					$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"] input').attr('name', 'vehicle_option[' + rand + '][]').attr('data-service_id', rand);
					$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"] input').attr('name', 'trailer_option[' + rand + '][]').attr('data-service_id', rand);
					$.each($(tmpDiv).find('.tools input'), function(jk, jv){
						$(jv).attr('name', 'tools_option[' + rand + ']['+ $(jv).attr('data-tool_id') +']['+k+'][]').attr('data-service_id', rand).attr('data-name', 'tools_option[' + rand + ']['+ $(jv).attr('data-tool_id') +']['+k+'][]');
					});
					if(draft.expenses != undefined && draft.expenses[key] != undefined) {
						$.each(draft.expenses[key], function(ek, ev) {
							var tpl = $(tempDiv).find('.expenseRow').html();
							var div = document.createElement('div');
							$(div).append(tpl);
							$(div).find('.expenseTitle').attr('value', ev.title).attr('data-service_id', key).attr('name', 'expenses[' + key + '][' + ek + '][title]');
							$(div).find('.expenseAmount').attr('value', ev.amount).attr('data-service_id', key).attr('name', 'expenses[' + key + '][' + ek + '][amount]');
							expensesBlock += $(div).html();
						});
					}
					$(tempDiv).find(".serviceExtraExpenses").html(expensesBlock);
					if(draft.tools_option != undefined && draft.tools_option[key] != undefined)
					{
						$.each(draft.tools_option[key], function(jk, jv){
							if(jv[k] != undefined)
							{
								$.each(jv[k], function(jkey, jval){
									$(tmpDiv).find('.tools input[data-name="tools_option['+rand+']['+jk+']['+k+'][]"][value="'+jval+'"]').attr('checked', 'checked');
									$(tmpDiv).find('.tools input[data-name="tools_option['+rand+']['+jk+']['+k+'][]"][value="'+jval+'"]').parent().addClass('active');
								});
								
							}
							/*check = '';
							active = '';
							btnClass = jk % 2 ? 'btn-primary' : 'btn-warning';
							
							if(draft.vehicle_option != undefined && draft.vehicle_option[key][k] != undefined)
							{
								if(draft.vehicle_option[key][k].indexOf(jv) >= 0)
								{
									active = ' active';
									check = 'checked="checked"';
								}
							}
							tplOpts = '<label class="btn btn-xs ' + btnClass + active + '"><i class="fa fa-check text-active"></i><input type="checkbox" ' + check + ' value="'+ jv +'" data-array="1" data-name="vehicle_option[]" name="vehicle_option['+rand+']['+k+'][]" data-service_id="'+rand+'">' + jv + '</label>';
							$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"]').append(tplOpts);
							*/
						});
						
						
						$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"] input:first').parent().remove();
						$(tmpDiv).find('[data-name="service_tools"]  option[value="' + draft.service_tools[key][k] + '"]').attr('selected', 'selected').attr('name', 'service_tools[' + rand + '][]').attr('data-service_id', rand);
						$(tmpDiv).find('[data-name="service_tools"]').attr('name', 'service_tools[' + rand + '][]').attr('data-service_id', rand);
					}
					else
					{
						$(tmpDiv).find('[data-name="service_tools"]').attr('name', 'service_tools[' + rand + '][]').attr('data-service_id', rand);
					}
					
					var vehOpt = $(tmpDiv).find('[data-name="service_vehicle"] option[value="' +  v + '"]').attr('data-option');
					var traOpt = $(tmpDiv).find('[data-name="service_trailer"] option[value="' +   draft.service_trailer[key][k] + '"]').attr('data-traioptions');
					if(vehOpt != undefined && vehOpt != '')
					{
						$.each($.parseJSON(vehOpt), function(jk, jv){
							check = '';
							active = '';
							
							btnClass = jk % 2 ? 'btn-primary' : 'btn-warning';
							if(draft.vehicle_option != undefined && draft.vehicle_option[key][k] != undefined)
							{
								if(draft.vehicle_option[key][k].indexOf(jv) !== -1)
								{
									active = ' active';
									check = 'checked="checked"';
								}
								tplOpts = '<label class="btn btn-xs ' + btnClass + active + '"><i class="fa fa-check text-active"></i><input  class="vehicle_option" type="checkbox" ' + check + ' value="' + jv.replace("\'", '&#39;').replace('\"', '&#34;') +'" data-array="1" data-name="vehicle_option[]" name="vehicle_option['+rand+']['+k+'][]" data-service_id="'+rand+'">' + jv + '</label>';
								$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"]').append(tplOpts);
							}
							
						});
						// $(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"] input:first').parent().remove();
					}
					if(traOpt != undefined && traOpt != '')
					{
						$.each($.parseJSON(traOpt), function(jk, jv){
							check = '';
							active = '';
							btnClass = jk % 2 ? 'btn-primary' : 'btn-warning';
							if(draft.trailer_option != undefined && draft.trailer_option[key][k] != undefined)
							{
								if(draft.trailer_option[key][k].indexOf(jv) !== -1)
								{
									active = ' active';
									check = 'checked="checked"';
								}
								tplOpts = '<label class="btn btn-xs ' + btnClass + active + '"><i class="fa fa-check text-active"></i><input class="trailer_option" type="checkbox" ' + check + ' value="' + jv.replace("\'", '&#39;').replace('\"', '&#34;') +'" data-array="1" data-name="trailer_option[]" name="trailer_option['+rand+']['+k+'][]" data-service_id="'+rand+'">' + jv + '</label>';
								$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"]').append(tplOpts);
							}
							
						});
						$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"] input:first').parent().remove();
					}
					$(tmpDiv).addClass('row m-b-sm pos-rlt p-left-5');
					$(tmpDiv).children().addClass('serviceSetupTpl');
					$(tempDiv).find('.serviceSetupList').append($(tmpDiv));
					
				});
				if(draft.service_trailer[key].length)
					$(tempDiv).find('.serviceSetupTpl:first').parent().remove();
			}
			else
			{
				var setupTpl = $(tpl).find('.serviceSetupTpl:first').clone();
				var tmpDiv = document.createElement('div');
				$(tmpDiv).append(setupTpl);
				$(tmpDiv).children().removeClass('serviceSetupTpl');
				$(tmpDiv).find('.service_vehicle option[selected]').removeAttr('selected');
				$(tmpDiv).find('.service_vehicle').attr('name', 'service_vehicle[' + rand + '][]').attr('data-service_id', rand);
				$(tmpDiv).find('.service_trailer option[selected]').removeAttr('selected');
				$(tmpDiv).find('.service_trailer').attr('name', 'service_trailer[' + rand + '][]').attr('data-service_id', rand);
				$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"]').remove();
				$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"]').remove();
				$(tmpDiv).addClass('row m-b-sm pos-rlt p-left-5');
				$(tmpDiv).children().addClass('serviceSetupTpl');
				$(tempDiv).find('.serviceSetupList').append($(tmpDiv));
				$(tempDiv).find('.serviceSetupTpl:first').parent().remove();
			}

			//return false;
			//addCopyEquipm
			if($('#estimateForm').find('.serviceGroup').length > 0)
			{
				$(tpl).find('.addCopyEquipment').removeClass('hide');
			}
			
			//SETUPS END
			$(tempDiv).find('[data-name="empType"]').attr('name', 'empType[' + rand + ']').attr('data-service_id', rand);
			var crewObj = $(tempDiv).find('[data-name="empType"]');
			$.each(draft.service_crew[key], function(k, v){
				//if(v == 9 || v == 10 || v == 12)
					
				var crewName = $(tempDiv).find('label.empType[data-crew_id="' + v + '"]:first').text();
				var rate = $(tempDiv).find('label.empType[data-crew_id="' + v + '"]:first').data('crew_rate');
				$(crewObj).parents('.panel-body:first').next('.panel-body').append('<label class="btn btn-success inline m-r-xs" data-rate="' + rate + '" data-emp_type_id="' + v + '" style="padding: 3px 5px;">' + crewName + ' <a href="#" class="moveFromCrew">x</a><input type="hidden" name="service_crew[' + rand + '][]" data-service_id="' + rand + '" value="' + v + '"></label>');
			});

			if(draft.pre_uploaded_files !== undefined && draft.pre_uploaded_files[key] !== undefined) {
				$.each(draft.pre_uploaded_files[key], function (k,v) {
					$(tempDiv).find('.serviceGroup[data-service_id="' + rand + '"]').append('<input type="hidden" data-service_id="' + rand + '" name="pre_uploaded_files[' + rand + '][]" value="' + v.filepath + '" data-uuid="' + v.uuid + '" data-size="' + v.size + '" data-url="' + v.url + '" data-type="' + v.type + '" data-name="' + v.name + '">');
				});
			}
			
			$(tempDiv).find('[data-name="service_equipment"]').attr('name', 'service_equipment[' + rand + '][]').attr('data-service_id', rand);
			
			//$(tempDiv).find('[data-name="service_wood_chips"]').attr('value', draft.service_wood_chips[key]).attr('name', 'service_wood_chips[' + rand + ']').attr('data-service_id', rand);
			//$(tempDiv).find('[data-name="service_wood_trailers"]').attr('value', draft.service_wood_trailers[key]).attr('name', 'service_wood_trailers[' + rand + ']').attr('data-service_id', rand);

			//$(tempDiv).find('[data-name="service_front_space"]').attr('name', 'service_front_space[' + rand + ']').attr('data-service_id', rand);
			$(tempDiv).find('[data-name="service_cleanup"]').attr('name', 'service_cleanup[' + rand + ']').attr('data-service_id', rand);
			$(tempDiv).find('[data-name="service_disposal_brush"]').attr('name', 'service_disposal_brush[' + rand + ']').attr('data-service_id', rand);
			$(tempDiv).find('[data-name="service_disposal_wood"]').attr('name', 'service_disposal_wood[' + rand + ']').attr('data-service_id', rand);
			//$(tempDiv).find('[data-name="service_access"]').attr('name', 'service_access[' + rand + ']').attr('data-service_id', rand);
			//$(tempDiv).find('[data-name="service_client_home"]').attr('name', 'service_client_home[' + rand + ']').attr('data-service_id', rand);
			// console.log($(tempDiv).find('.dropzone'));
			$(tempDiv).find('.toggleChevron').attr('href', '#collapse' + rand);
			$(tempDiv).find('.collapse').attr('id', 'collapse' + rand);
			if(draft.bundles_services != undefined && draft.bundles_services[key]){
				$(tempDiv).find('header').css({
					"background-color": "white",
					"border": "none"
				});
				$(tempDiv).find('input[data-name="bundles_services"]').attr('data-name', 'bundles_services[' + rand + ']').attr('name', 'bundles_services[' + rand + ']').val(draft.bundles_services[key]);
				bundles_services[bundles_services.length] = { bundleId : draft.bundles_services[key], bundles_services : tempDiv };
				return;
			} else if(draft.is_bundle != undefined && draft.is_bundle[key]){
				// $(tempDiv).find('[data-bundle_id=""]').attr();
				$(tempDiv).find('[data-name="bundle_qty"]').val(draft.quantity[key]);
				// $(tempDiv).find('ul[data-bundle_id=""]').attr('data-bundle_id', rand);
				$(tempDiv).find('[data-bundle_id=""]').attr('data-bundle_id', rand);
				bundles[bundles.length] = { bundleId : key, bundle : tempDiv };
				return;
			}
			$('#readyServices').append($(tempDiv).html());
			// totalBundle($('section[data-service_id= '+ rand +']').closest('section').find('.bundleRecords'));
			totalBundle(tempDiv);
			initDropzone($('#readyServices').find('section[data-service_id="' + rand+ '"]').find('.dropzone'));
			if(draft.service_permit[key] != undefined && parseInt(draft.service_permit[key]))
				$(document).find('[name="service_permit[' + rand + ']"]').click();
			if(draft.service_exemption[key] != undefined && parseInt(draft.service_exemption[key]))
				$(document).find('[name="service_exemption[' + rand + ']"]').click();

			$.each(draft.service_equipment[key], function(k, v){
				$(document).find('[name="service_equipment[' + rand + '][]"][value="' + v + '"]').parents('label.btn:first').click();
			});

			if(parseInt(draft.service_front_space[key]))
				$(document).find('[name="service_front_space[' + rand + ']"]').parents('label.btn:first').click();
			if(parseInt(draft.service_cleanup[key]))
				$(document).find('[name="service_cleanup[' + rand + ']"]').parents('label.btn:first').click();
			if(parseInt(draft.service_disposal_brush[key]))
				$(document).find('[name="service_disposal_brush[' + rand + ']"]').parents('label.btn:first').click();
			if(parseInt(draft.service_disposal_wood[key]))
				$(document).find('[name="service_disposal_wood[' + rand + ']"]').parents('label.btn:first').click();
			if(draft.service_access[key])
				$(document).find('[name="service_access[' + rand + ']"][value="' + draft.service_access[key] + '"]').parents('label.btn:first').click();
			if(parseInt(draft.service_client_home[key]))
				$(document).find('[name="service_client_home[' + rand + ']"]').parents('label.btn:first').click();

			$(tempDiv).find('.serviceTravelTime').trigger('keyup');
			$('#readyServices').find('.serviceGroup[data-service_id="' + rand + '"]').find('input[name="service_travel_time[' + rand + ']"]').trigger('keyup');
			$('#readyServices').find('.serviceGroup[data-service_id="' + rand + '"]').find('.service_vehicle').parent().find('[data-toggle="buttons"]:not(:has(>label))').parent().find('.service_vehicle').trigger('change');
			$('#readyServices').find('.serviceGroup[data-service_id="' + rand + '"]').find('.service_trailer').parent().find('[data-toggle="buttons"]:not(:has(>label))').parent().find('.service_trailer').trigger('change');

			Common.mask_currency($('#readyServices').find('.serviceGroup[data-service_id="' + rand + '"]').find(".currency"));
			
			$('#readyServices').find('.serviceGroup[data-service_id="' + rand + '"]').find(".decimal").inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})?$", removeMaskOnSubmit: true, rightAlign: false});
			$('#readyServices').find('.serviceGroup[data-service_id="' + rand + '"]').find('.hours').inputmask('decimal', {suffix: " hrs", removeMaskOnSubmit: true, rightAlign: false});
			$('#readyServices').find('.serviceGroup[data-service_id="' + rand + '"]').find(".serviceMarkup").inputmask("decimal", {suffix: "%", removeMaskOnSubmit: true, rightAlign: false});
			$('.species').autocomplete({disabled:true});
			$('.species').autocomplete({disabled:false});
			autoComplete();
			check_equipment();
			$('.saveForm, .pdf-preview').removeClass('disabled').removeAttr('disabled');

			//collapsed view
			if(typeof draft.is_collapsed !== 'undefined' && typeof draft.is_collapsed[key] !== 'undefined')
				if(draft.is_collapsed[key] == 0) {
					$('section[data-service_id="' + rand + '"]:first').find('.toggleChevron').click();
					$('section[data-service_id="' + rand + '"]:first').find('.use-calc').click();
				}

			/*$.each($('#readyServices').find('.serviceGroup'), function(key, val){
				$(val).css('padding-bottom', '0px');
			});
			$('#readyServices').find('.serviceGroup:last').css('padding-bottom', '70px');
			*/
		});
		$.each(bundles, function (key, val) {
			let recordsId = [];
			$.each(bundles_services, function (servicesKey, servicesVal) {
				if(servicesVal['bundleId'] == val['bundleId']) {
					$(val['bundle']).find('.bundleRecords').append(servicesVal['bundles_services']);
					recordsId.push($(servicesVal['bundles_services']).find('[data-name="service_type_id"]').data('service_id'));
				}
			});
			$('#readyServices').append($(val['bundle']).html());
			console.log(recordsId);
			$.each(recordsId, function (key, val) {
				let section = $('#readyServices').find('section.serviceGroup[data-service_id="' + val + '"]');
				initDropzone(section.find('.dropzone'));
				let is_collapsed = section.find('[name="is_collapsed['+val+']"]').val();
				if(is_collapsed == 0) {
					section.find('.toggleChevron').click();
					section.find('.use-calc').click();
				}
				if(checkService > 1)
					section.find('.addCopyEquipment').removeClass('hide');
				$('#readyServices').find('.serviceGroup[data-service_id="' + val + '"]').find('.service_vehicle').parent().find('[data-toggle="buttons"]:not(:has(>label))').parent().find('.service_vehicle').trigger('change');
				$('#readyServices').find('.serviceGroup[data-service_id="' + val + '"]').find('.service_trailer').parent().find('[data-toggle="buttons"]:not(:has(>label))').parent().find('.service_trailer').trigger('change');
			});
		});
	}
	if(draft.estimate_brand_id !== undefined && draft.estimate_brand_id > 0){
		$('#estimate-brand-select').val(draft.estimate_brand_id);
	}
	else{
		$('#estimate-brand-select').val($('#estimate-brand-select [selected="selected"]').val()).change();
	}
	if(draft.bundles !== undefined){
		$.each(draft.bundles, function (key, val) {
			let tempDiv = document.createElement('div');
			tempDiv.innerHTML = bundleTpl.tpl;
			tempDiv = setDefaultTempDiv(tempDiv, key, val);
			let result = addBundleItems(tempDiv, val, key);
			tempDiv = result[0];
			$(tempDiv).find('section.bundle').attr('data-service_id', key);
			$(tempDiv).find('.product-select2-bundle').attr('data-bundle_id', key);
			$(tempDiv).find('.service-select2-bundle').attr('data-bundle_id', key);
			$('#readyServices').append($(tempDiv).html());
			if(result[1].length) {
				$.each(result[1], function (key, id) {
					initDropzone($('#readyServices').find('section.serviceGroup[data-service_id="' + id+ '"]').find('.dropzone'));
					setSettings(id);
				});
			}
		});
	}
	if(draft.products !== undefined){
		$.each(draft.products, function (key, val) {
			let tempDiv = document.createElement('div');
			tempDiv.innerHTML = productTpl.tpl;
			tempDiv = setDefaultTempDiv(tempDiv, key, val);
			$('#readyServices').append($(tempDiv).html());
		});
	}
	if(draft.services !== undefined){
		$.each(draft.services, function (key, val) {
			let tpl = $(serviceTpl.tpl);
			let tempDiv = document.createElement('div');
			tempDiv.innerHTML = serviceTpl.tpl;
			tempDiv = setDefaultTempDiv(tempDiv, key, val);
			tempDiv = setSetupsTempDiv(tempDiv, key, val, tpl);
			if(val['ties_number'] !== undefined)
				tempDiv = setTreeInventorySetups(tempDiv, key, val);
			$('#readyServices').append($(tempDiv).html());
			initDropzone($('#readyServices').find('section.serviceGroup[data-service_id="' + key+ '"]').find('.dropzone'));
			setSettings(key);
		});
	}
	if(draft.items !== undefined && draft.order_items !== undefined && draft.order_items){
		$.each(draft.order_items, function (key, val) {
			let item = draft.items[val];
			if(item !== undefined && item && item.constructor === Object)
				setDraftItem(val, item);
		})
	}

	function setDraftItem(key, item) {


		let tempDiv = document.createElement('div');
		if(item.is_bundle !== undefined && item.is_bundle == 1){
			tempDiv.innerHTML = bundleTpl.tpl;
			tempDiv = setDefaultTempDiv(tempDiv, key, item);
			let result = addBundleItems(tempDiv, item, key);
			tempDiv = result[0];
			$(tempDiv).find('section.bundle').attr('data-service_id', key);
			$(tempDiv).find('.product-select2-bundle').attr('data-bundle_id', key);
			$(tempDiv).find('.service-select2-bundle').attr('data-bundle_id', key);

			$('#readyServices').append($(tempDiv).html());
			if(result[1].length) {
				$.each(result[1], function (key, id) {
					initDropzone($('#readyServices').find('section.serviceGroup[data-service_id="' + id+ '"]').find('.dropzone'));
					setSettings(id);
				});
			}
		} else if(item.is_product !== undefined && item.is_product == 1){
			tempDiv.innerHTML = productTpl.tpl;
			tempDiv = setDefaultTempDiv(tempDiv, key, item);

			if(draft['pre_uploaded_files'] !== undefined && draft['pre_uploaded_files'][key]) {
				$.each(draft['pre_uploaded_files'][key], function (k,v) {
					$(tempDiv).find('.serviceGroup[data-service_id="' + key + '"]').append('<input type="hidden" data-service_id="' + key + '" name="pre_uploaded_files[' + key + '][]" value="' + v.filepath + '" data-uuid="' + v.uuid + '" data-size="' + v.size + '" data-url="' + v.url + '" data-type="' + v.type + '" data-name="' + v.name + '">');
				});
			}
			$('#readyServices').append($(tempDiv).html());

			initDropzone($('#readyServices').find('section.serviceGroup[data-service_id="' + key+ '"]').find('.dropzone'));


		} else {
			let tpl = $(serviceTpl.tpl);
			tempDiv.innerHTML = serviceTpl.tpl;
			if(item.ties_number!=null){
				$(tempDiv).find('.editService').removeClass('hidden').attr('data-service_id', key);
			}else{
				$(tempDiv).find('.editService').remove();
			}

			tempDiv = setDefaultTempDiv(tempDiv, key, item);
			tempDiv = setSetupsTempDiv(tempDiv, key, item, tpl);
			if(item['ties_number'] !== undefined && item['tree_inventory_title'] !== undefined && item['tree_inventory_title'])
				tempDiv = setTreeInventorySetups(tempDiv, key, item);
			$('#readyServices').append($(tempDiv).html());
			initDropzone($('#readyServices').find('section.serviceGroup[data-service_id="' + key+ '"]').find('.dropzone'));
			setSettings(key);
		}
	}

	init_taxable_popover();
	if(draft.provided != undefined) {
		$('input[name="provided"][value="' + draft.provided + '"]').parents('label.btn:first').click();
	}
	else {
		$('input[name="provided"][value="meeting"]').parents('label.btn:first').click();
	}

	if(draft.new_add != undefined) {
		$('#new_add').prop('checked', true).trigger('change');
	}

	if(draft.new_address != undefined) {
		$('#route_1').val(draft.new_address);
	}

	if(draft.new_city != undefined) {
		$('#locality_1').val(draft.new_city);
	}

	if(draft.new_state != undefined) {
		$('#administrative_area_level_1_1').val(draft.new_state);
	}

	if(draft.new_zip != undefined) {
		$('#postal_code_1').val(draft.new_zip);
	}

	if(draft.save_item != undefined) {
		$('form#estimateForm').submit();
	}

	if(draft.estimate_item_estimated_time != undefined) {
		$('#estimate_item_estimated_time').val(draft.estimate_item_estimated_time);
	}

	if(draft.estimate_item_equipment_setup != undefined) {
		$('#estimate_item_equipment_setup').val(draft.estimate_item_equipment_setup);
	}

	if(draft.estimate_item_note_crew != undefined) {
		$('.estimate_item_note_crew').val(draft.estimate_item_note_crew);
	}

	if(draft.estimate_item_note_payment != undefined) {
		$('#estimate_item_note_payment').val(draft.estimate_item_note_payment);
	}

	if(draft.discount != undefined) {
		$('[name="discount"]').val(draft.discount);
		$('#estimateDiscountValue').val(draft.discount);
	}
	
	if(draft.discount_comment != undefined) {
		$('[name="discount_comment"]').val(draft.discount_comment);
		$('#estimateDiscountComment').val(draft.discount_comment);
	}

	if(draft.estimate_crew_notes != undefined) {
		$('[name="estimate_crew_notes"]').val(draft.estimate_crew_notes);
	}
	
	if(draft.discount_percents != undefined && parseInt(draft.discount_percents)) {
		// $('[name="discount_percents"]').prop('checked', true);
		$('#estimateDiscountPercents').val(1);
	}

	if(draft.tax_include !== undefined && draft.tax_include == 1){
		$('[name="estimate_hst_disabled"]').val(2);
		$('#inclTax').click();
	}

	if(draft.createInvoice == true) {
		$('#readyServices').parent().append('<input type="hidden" name="create_invoice" value="true" />');
	}

	if(draft.createWo == true) {
		$('#readyServices').parent().append('<input type="hidden" name="create_wo" value="true" />');
	}

	if(draft.copyEst !==undefined && draft.copyEst !== false) {
		$('#readyServices').parent().append('<input type="hidden" name="copy_estimate" value="'+draft.copyEst+'" />');
	}
	if(draft.copyInvoice !==undefined && draft.copyInvoice !== false) {
		$('#readyServices').parent().append('<input type="hidden" name="copy_invoice" value="'+draft.copyInvoice+'" />');
	}

	if(draft.copyWo !==undefined && draft.copyWo !== false) {
		$('#readyServices').parent().append('<input type="hidden" name="copy_wo" value="'+draft.copyWo+'" />');
	}

	if(draft.old_estimate_id !== undefined) {
		$('#readyServices').parent().append('<input type="hidden" name="old_estimate_id" value="'+draft.old_estimate_id+'" />');
	}

	if(draft.new_client_id !== undefined) {
		$('#readyServices').parent().append('<input type="hidden" name="new_client_id" value="'+draft.new_client_id+'" />');
	}



	if(draft.tree_inventory_pdf !== undefined && draft.tree_inventory_pdf == 1){
		$('[name="tree_inventory_pdf"]').prop('checked', true);
	}
	/*if(draft.tax_name != undefined && draft.tax_value != undefined) { console.log(draft.tax_name); console.log(draft.tax_value);
		$('.taxLabelForSelect2').val(draft.tax_name + ' (' + draft.tax_value + '%)');
		$('.taxLabel').html(draft.tax_name + ' (' + draft.tax_value + '%)');
		$('.taxValue').val(draft.tax_value);		
		$('.taxName').val(draft.tax_name);
		console.log($('.taxName').val()); console.log($('.taxValue').val()); console.log($('.taxLabel').html());
		
		$('#tax').select2( 'val', $('.taxLabelForSelect2').val());
	}*/

		
	$(document).on('change', '#estimateForm textarea[name], #estimateForm input[name], #estimateForm select[name]', function() {
		var serviceId = $(this).data('service_id');
		var client_id = $('#client_id').val();
		var lead_id = $('input[name="lead_id"]').val();
		if(serviceId) {
			let id = $(this).attr('id');
			var data = $('[data-service_id="' + serviceId + '"]').serialize();
			data += '&client_id=' + client_id + '&lead_id=' + lead_id + '&service_id=' + serviceId;
			$.ajax({
				url: baseUrl + 'estimates/ajax_estimate_draft_service',
				data: data,
				method: "POST",
				global: false,
				success: function(resp){
					console.log(resp);
				},
				dataType: 'json'
			});
		}
		else {
			var field = $(this).attr('name');
			var value = $(this).val();
			if($(this).attr('type') == 'checkbox'){
				value = $(this).is(':checked') ? 1 : 0;
			}
			if(field == 'estimate_hst_disabled') {
				field = 'tax_include';
				value = $(this).val() > 0 ? 1 : 0;
			}

			$.ajax({
				url: baseUrl + 'estimates/ajax_estimate_draft_field',
				data: {
					client_id	: client_id,
					lead_id		: lead_id,
					field		: field,
					value		: value,
				},
				method: "POST",
				global: false,
				success: function(resp){
				},
				dataType: 'json'
			});
		}
	});

	$(document).on('click', '.deleteService, .deleteBundle', function() {
		let serviceGroup = $(this).parents('.serviceGroup:first');
		let bundleId = $(serviceGroup).data('bundle_id');
		$(this).parents('section:first').hide();
		var serviceId = serviceGroup.data('service_id');
		if(typeof serviceId === "undefined"){
			serviceId =$(this).parents().find('[data-name="service_type_id"]').data('service_id');
		}
		var client_id = $('#client_id').val();
		var lead_id = $('input[name="lead_id"]').val();
		$.ajax({
			url: baseUrl + 'estimates/ajax_estimate_delete_draft_service',
			data: {
				client_id	: client_id,
				lead_id		: lead_id,
				service_id	: serviceId,
				bundle_id : bundleId
			},
			method: "POST",
			global: false,
			success: function(resp){

			},
			dataType: 'json'
		});
	});



	function autoComplete()
	{
		$(".species").autocomplete({
			source: tags,
			appendTo: '#div',  
			open: function() { $('#div .ui-menu').width(300) }
		});
	}
	$('.taxable').popover({
		html: true,
		placement : 'top',
		container : 'body',
		content: function () {
			return $(this).parent().find('.content').html();
		}
	});

	$.each($('.bundleTotal'),function (key, val) {
		$(val).find('.product-select2-bundle').select2({
			data:JSON.parse($('.categoriesWithProducts').val()),
			placeholder: "Add Product",
			allowClear: true,
			dropdownCssClass: "select-product-dropdown"
		});
		$(val).find('.service-select2-bundle').select2({
			data:JSON.parse($('.categoriesWithServices').val()),
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
});

function mapToJson(map) {
	return JSON.stringify([...map]);
}

function setDefaultTempDiv(tempDiv, id, item){
	let serviceTypeId = item['service_type_id'];
	let productSelect2 = $(".product-select2");
	let serviceSelect2 = $(".service-select2");
	let bundleSelect2 = $(".bundle-select2");
	let serviceMarkup = 0;
	let qbAccess = $('.qbAccess').val();

	if(productSelect2.length && productSelect2.select2("val", serviceTypeId).select2('data')) {
		serviceMarkup = productSelect2.select2("val", serviceTypeId).select2('data').service_markup;
		$(tempDiv).find('.serviceTitle').text(productSelect2.select2("val", serviceTypeId).select2('data').text);
	} else if(serviceSelect2.select2("val", serviceTypeId).select2('data')){
		serviceMarkup =  serviceSelect2.select2("val", serviceTypeId).select2('data').service_markup;
		if(typeof(item['tree_inventory_title']) != 'undefined' && item['tree_inventory_title']) {
			$(tempDiv).find('.serviceTitle').text(item['tree_inventory_title']);
			$(tempDiv).find('[data-name="tree_inventory_title"]').val(item['tree_inventory_title']).attr('data-service_id', id);
		}else
			$(tempDiv).find('.serviceTitle').text(serviceSelect2.select2("val", serviceTypeId).select2('data').text);
	} else if(bundleSelect2.length && bundleSelect2.select2("val", serviceTypeId).select2('data')){
		serviceMarkup =  bundleSelect2.select2("val", serviceTypeId).select2('data').service_markup;
		$(tempDiv).find('.serviceTitle').text(bundleSelect2.select2("val", serviceTypeId).select2('data').text);
	}

	if(qbAccess && typeof classWithChildren != 'undefined' && classWithChildren.length > 0){
		$(tempDiv).find('.QBclass').show();
	}
	$(tempDiv).find('.serviceGroup').attr('data-service_id', id);
	$(tempDiv).find('.serviceGroup').attr('data-service_markup', serviceMarkup);
	$(tempDiv).find('.serviceMarkup').val(serviceMarkup);
	$(tempDiv).find('.nonTaxable').attr('data-id', '#service-checkbox-' + id).attr('data-service_id', id);
	$(tempDiv).find('.non-taxable-title').attr('id', 'service-checkbox-' + id + '-title');
	$(tempDiv).find('[name="is_view_in_pdf[]"]').attr('name', 'is_view_in_pdf[' + id + ']').attr('data-service_id', id);
	if(typeof item['non_taxable'] !== "undefined" && item['non_taxable'] == 1){
		$(tempDiv).find('.nonTaxableHidden').val(1);
		$(tempDiv).find('.non-taxable-title').show();
	}
	if(typeof item['is_view_in_pdf'] !== "undefined" && item['is_view_in_pdf'] == 1){
		$(tempDiv).find('[name="is_view_in_pdf[' + id + ']"]').attr('checked', 'checked');
	}
	// if(typeof item['tree_inventory_service'] !== "undefined" && item['tree_inventory_service']){
	// 	$(tempDiv).find('.info').removeClass('hidden');
	// 	$(tempDiv).find('.taxable').removeClass('taxable');
	// 	$(tempDiv).find('.classSelect, .servicePrice, .deleteService, .serviceDescription, .use-calc').attr('disabled', true);
	// }
	$(tempDiv).find('.toggleChevron').attr('href', '#collapse' + id);
	$(tempDiv).find('.collapse').attr('id', 'collapse' + id);

	$.each($(tempDiv).find('[data-name]'), function (selectorKey, selectorVal) {
		let data_name = $(selectorVal).data('name');
		$(selectorVal).attr('value', item[data_name]).attr('name', data_name + '[' + id + ']').attr('data-service_id', id);
		if(data_name === 'non_taxable')
			$(selectorVal).attr('id', 'service-checkbox-'+ id);
		if(data_name === 'service_description')
			$(selectorVal).html(item['service_description']);
	});
	return tempDiv;
}

function setTreeInventorySetups(tempDiv, id, item){
	let service = $(tempDiv).find('.serviceGroup');
	$(tempDiv).find('.tree-inventory-cost, .tree-inventory-stump').show();
	$(tempDiv).find('.servicePrice').attr('disabled', 'disabled');
	$(tempDiv).find('.use-calc').attr('disabled', 'disabled');
	if(item['ties_number'] !== undefined)
		$('<input>').attr({
			type: 'hidden',
			name: 'ties_number[' + id + ']',
			'data-service_id': id,
			'data-name': 'ties_number',
			value: item['ties_number']
		}).appendTo(service);
	if(item['ties_type'] !== undefined)
		$('<input>').attr({
			type: 'hidden',
			name: 'ties_type[' + id + ']',
			'data-service_id': id,
			'data-name': 'ties_type',
			value: item['ties_type']
		}).appendTo(service);
	if(item['ties_size'] !== undefined)
		$('<input>').attr({
			type: 'hidden',
			name: 'ties_size[' + id + ']',
			'data-service_id': id,
			'data-name': 'ties_size',
			value: item['ties_size']
		}).appendTo(service);
	if(item['ties_priority'] !== undefined)
		$('<input>').attr({
			type: 'hidden',
			name: 'ties_priority[' + id + ']',
			'data-service_id': id,
			'data-name': 'ties_priority',
			value: item['ties_priority']
		}).appendTo(service);
	if(item['ties_notes'] !== undefined)
		$('<input>').attr({
			type: 'hidden',
			name: 'ties_notes[' + id + ']',
			'data-service_id': id,
			'data-name': 'ties_notes',
			value: item['ties_notes']
		}).appendTo(service);
	if(item['ties_work_types'] !== undefined)
		$('<input>').attr({
			type: 'hidden',
			name: 'ties_work_types[' + id + ']',
			'data-service_id': id,
			'data-name': 'ties_work_types',
			value: item['ties_work_types']
		}).appendTo(service);
	if(item['tree_inventory_title'] !== undefined)
		$('<input>').attr({
			type: 'hidden',
			name: 'tree_inventory_title[' + id + ']',
			'data-service_id': id,
			'data-name': 'tree_inventory_title',
			value: item['tree_inventory_title']
		}).appendTo(service);
	let cost = 0;
	let stump = 0;
	if(item['ties_cost'] !== undefined) {
		$(tempDiv).find('.treeInventoryCost').attr('value', item['ties_cost']).attr('name', 'ties_cost[' + id + ']').attr('data-service_id', id);
		cost = parseFloat(item['ties_cost']);
	}
	if(item['ties_stump'] !== undefined) {
		$(tempDiv).find('.treeInventoryStump').attr('value', item['ties_stump']).attr('name', 'ties_stump[' + id + ']').attr('data-service_id', id);
		stump = parseFloat(item['ties_stump']);
	}
	// $(tempDiv).find('.servicePrice').attr('value', cost + stump);
	return tempDiv;
}

function setSetupsTempDiv(tempDiv, id, item, tpl){
	if(item['equipments'] !== undefined && item['equipments']['transport'] !== undefined && item['equipments']['transport']['vehicles'] !== undefined && item['equipments']['transport']['vehicles']['vehicle'] !== undefined)
	{
		$.each(item['equipments']['transport']['vehicles']['vehicle'], function(k, v){
			let setupTpl = $(tpl).find('.serviceSetupTpl:first').clone();
			let tmpDiv = document.createElement('div');
			$(tmpDiv).append(setupTpl);
			$(tmpDiv).children().removeClass('serviceSetupTpl');
			if(item['equipments']['transport']['trailers'] !== undefined && item['equipments']['transport']['trailers']['vehicle'] !== undefined)
				$(tmpDiv).find('[data-name="service_trailer"] option[value="' +  item['equipments']['transport']['trailers']['vehicle'][k] + '"]').attr('selected', 'selected');
			$(tmpDiv).find('[data-name="service_trailer"]').attr('name', 'service_trailer[' + id + ']['+ k +']').attr('data-service_id', id).attr('data-key', k);
			$(tmpDiv).find('[data-name="service_vehicle"]  option[value="' + v + '"]').attr('selected', 'selected');
			$(tmpDiv).find('[data-name="service_vehicle"]').attr('name', 'service_vehicle[' + id + ']['+ k +']').attr('data-service_id', id).attr('data-key', k);
			$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"] input').attr('name', 'vehicle_option[' + id + '][]').attr('data-service_id', id);
			$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"] input').attr('name', 'trailer_option[' + id + '][]').attr('data-service_id', id);
			$.each($(tmpDiv).find('.tools input'), function(jk, jv){
				$(jv).attr('name', 'tools_option[' + id + ']['+ $(jv).attr('data-tool_id') +']['+k+'][]').attr('data-service_id', id).attr('data-name', 'tools_option[' + id + ']['+ $(jv).attr('data-tool_id') +']['+k+'][]');
			});

			if(item['equipments']['tools'] !== undefined)
			{
				$.each(item['equipments']['tools'], function(jk, jv){
					if(jv[k] !== undefined) {
						$.each(jv[k], function (jkey, jval) {
							$(tmpDiv).find('.tools input[data-name="tools_option[' + id + '][' + jk + '][' + k + '][]"][value="' + jval + '"]').attr('checked', 'checked').parent().addClass('active');
						});
					}
				});

				$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"] input:first').parent().remove();
				// $(tmpDiv).find('[data-name="service_tools"]  option[value="' + item['service_tools'][k] + '"]').attr('selected', 'selected').attr('name', 'service_tools[' + id + '][]').attr('data-service_id', id);
				// $(tmpDiv).find('[data-name="service_tools"]').attr('name', 'service_tools[' + id + '][]').attr('data-service_id', id);
			}
			else
			{
				// $(tmpDiv).find('[data-name="service_tools"]').attr('name', 'service_tools[' + id + '][]').attr('data-service_id', id);
			}

			let vehOpt = $(tmpDiv).find('[data-name="service_vehicle"] option[value="' +  v + '"]').attr('data-option');
			let traOpt = '';
			if(item['equipments']['transport']['trailers'] !== undefined && item['equipments']['transport']['trailers']['vehicle'] !== undefined)
				traOpt = $(tmpDiv).find('[data-name="service_trailer"] option[value="' +   item['equipments']['transport']['trailers']['vehicle'][k] + '"]').attr('data-traioptions');
			if(vehOpt !== undefined && vehOpt !== '')
			{
				let remove = false;
				$.each($.parseJSON(vehOpt), function(jk, jv){
					let check = '';
					let active = '';

					let btnClass = jk % 2 ? 'btn-primary' : 'btn-warning';
					if(item['equipments']['transport']['vehicles']['option'] !== undefined && item['equipments']['transport']['vehicles']['option'][k] !== undefined)
					{
						remove = true;
						if(item['equipments']['transport']['vehicles']['option'][k].indexOf(jv) !== -1 || parseInt(jv) !== NaN && item['equipments']['transport']['vehicles']['option'][k].indexOf( parseInt(jv)) !== -1)
						{
							active = ' active';
							check = 'checked="checked"';
						}
						let tplOpts = '<label class="btn btn-xs ' + btnClass + active + '"><i class="fa fa-check text-active"></i><input  class="vehicle_option" type="checkbox" ' + check + ' value="' + jv.replace("\'", '&#39;').replace('\"', '&#34;') +'" data-array="1" data-name="vehicle_option[]" name="vehicle_option['+id+']['+k+'][]" data-service_id="'+id+'">' + jv + '</label>';
						$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"]').append(tplOpts);
					}

				});
				if(remove === false)
				 	$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"] input:first').parent().remove();
			}
			if(traOpt !== undefined && traOpt !== '')
			{
				$.each($.parseJSON(traOpt), function(jk, jv){
					let check = '';
					let active = '';
					let btnClass = jk % 2 ? 'btn-primary' : 'btn-warning';
					if(item['equipments']['transport']['trailers']['option'] !== undefined && item['equipments']['transport']['trailers']['option'][k] !== undefined)
					{
						if(item['equipments']['transport']['trailers']['option'][k].indexOf(jv) !== -1)
						{
							active = ' active';
							check = 'checked="checked"';
						}
						let tplOpts = '<label class="btn btn-xs ' + btnClass + active + '"><i class="fa fa-check text-active"></i><input class="trailer_option" type="checkbox" ' + check + ' value="' + jv.replace("\'", '&#39;').replace('\"', '&#34;') +'" data-array="1" data-name="trailer_option[]" name="trailer_option['+id+']['+k+'][]" data-service_id="'+id+'">' + jv + '</label>';
						$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"]').append(tplOpts);
					}

				});
				$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"] input:first').parent().remove();
			}
			$(tmpDiv).addClass('row m-b-sm pos-rlt p-left-5');
			$(tmpDiv).children().addClass('serviceSetupTpl');
			$(tempDiv).find('.serviceSetupList').append($(tmpDiv));

		});
		if(item['equipments']['transport']['trailers'] !== undefined && item['equipments']['transport']['trailers']['vehicle'] !== undefined && Object.keys(item['equipments']['transport']['trailers']['vehicle']).length > 0 ||
			Object.keys(item['equipments']['transport']['vehicles']['vehicle']).length > 0 )
			$(tempDiv).find('.serviceSetupTpl:first').parent().remove();
	}
	else
	{
		let setupTpl = $(tpl).find('.serviceSetupTpl:first').clone();
		let tmpDiv = document.createElement('div');
		$(tmpDiv).append(setupTpl);
		$(tmpDiv).children().removeClass('serviceSetupTpl');
		$(tmpDiv).find('.service_vehicle option[selected]').removeAttr('selected');
		$(tmpDiv).find('.service_vehicle').attr('name', 'service_vehicle[' + id + '][]').attr('data-service_id', id);
		$(tmpDiv).find('.service_trailer option[selected]').removeAttr('selected');
		$(tmpDiv).find('.service_trailer').attr('name', 'service_trailer[' + id + '][]').attr('data-service_id', id);
		$(tmpDiv).find('.service_vehicle').parents('.controls:first').find('[data-toggle="buttons"]').remove();
		$(tmpDiv).find('.service_trailer').parents('.controls:first').find('[data-toggle="buttons"]').remove();
		$(tmpDiv).addClass('row m-b-sm pos-rlt p-left-5');
		$(tmpDiv).children().addClass('serviceSetupTpl');
		$(tempDiv).find('.serviceSetupList').append($(tmpDiv));
		$(tempDiv).find('.serviceSetupTpl:first').parent().remove();
	}

	if(item['crews'] !== undefined && item['crews']) {
		$.each(item['crews'], function (k, v) {
			let crewName = $(tempDiv).find('label.empType[data-crew_id="' + v + '"]:first').text();
			let rate = $(tempDiv).find('label.empType[data-crew_id="' + v + '"]:first').data('crew_rate');
			$(tempDiv).find('[data-name="empType"]').parents('.panel-body:first').next('.panel-body').append('<label class="btn btn-success inline m-r-xs" data-rate="' + rate + '" data-emp_type_id="' + v + '" style="padding: 3px 5px;">' + crewName + ' <a href="#" class="moveFromCrew">x</a><input type="hidden" name="service_crew[' + id + '][]" data-service_id="' + id + '" value="' + v + '"></label>');
		});
	}
	if(draft['pre_uploaded_files'] !== undefined && draft['pre_uploaded_files'][id]) {
		$.each(draft['pre_uploaded_files'][id], function (k,v) {
			$(tempDiv).find('.serviceGroup[data-service_id="' + id + '"]').append('<input type="hidden" data-service_id="' + id + '" name="pre_uploaded_files[' + id + '][]" value="' + v.filepath + '" data-uuid="' + v.uuid + '" data-size="' + v.size + '" data-url="' + v.url + '" data-type="' + v.type + '" data-name="' + v.name + '">');
		});
	}

	if(item['expenses'] !== undefined) {
		let expensesBlock = '';
		$.each(item['expenses'], function(ek, ev) {
			let tpl = $(tempDiv).find('.expenseRow').html();
			let div = document.createElement('div');
			$(div).append(tpl);
			$(div).find('.expenseTitle').attr('value', ev.title).attr('data-service_id', id).attr('name', 'expenses[' + id + '][' + ek + '][title]');
			$(div).find('.expenseAmount').attr('value', ev.amount).attr('data-service_id', id).attr('name', 'expenses[' + id + '][' + ek + '][amount]');
			expensesBlock += $(div).html();
		});
		$(tempDiv).find(".serviceExtraExpenses").html(expensesBlock);
	}

	return tempDiv;
}

function addBundleItems(bundleDiv, bundle, bundleId){
	let idsServices=[];
	if(bundle.products !== undefined && bundle.products !== null){
		$.each(bundle.products, function (key, val) {
			let tempDiv = getItemForBundle(productTpl.tpl, bundleId, key, val);
			$(bundleDiv).find('.bundleRecords').append($(tempDiv).html());
		});
	}
	if(bundle.services !== undefined && bundle.services !== null){
		$.each(bundle.services, function (key, val) {
			let tempDiv = getItemForBundle(serviceTpl.tpl, bundleId, key, val);
			tempDiv = setSetupsTempDiv(tempDiv, key, val, serviceTpl.tpl);
			$(bundleDiv).find('.bundleRecords').append($(tempDiv).html());
			idsServices.push(key);
		});
	}
	if(bundle.items !== undefined && bundle.items !== null && bundle.order_items !== undefined ){
		$.each(bundle.order_items, function(key, val){
			if(bundle.items[val].is_product !== undefined && bundle.items[val].is_product == 1){
				let tempDiv = getItemForBundle(productTpl.tpl, bundleId, val, bundle.items[val]);
				$(bundleDiv).find('.bundleRecords').append($(tempDiv).html());
			} else {
				let tempDiv = getItemForBundle(serviceTpl.tpl, bundleId, val, bundle.items[val]);
				tempDiv = setSetupsTempDiv(tempDiv, val, bundle.items[val], serviceTpl.tpl);
				$(bundleDiv).find('.bundleRecords').append($(tempDiv).html());
				idsServices.push(val);
			}
		});
	}

	return [bundleDiv, idsServices];
}

function getItemForBundle(tpl, bundleId, itemId, item){
	let tempDiv = document.createElement('div');
	tempDiv.innerHTML = tpl;
	tempDiv = setDefaultTempDiv(tempDiv, itemId, item);
	$(tempDiv).find('header').css({
		"background-color": "white",
		"border": "none"
	});
	$(tempDiv).find('[name="bundles_services['+itemId+']"]').val(bundleId);
	$(tempDiv).find('.serviceGroup').attr('data-bundle_id', bundleId);
	return tempDiv;
}

function setSettings(id){
	$('#readyServices').find('.serviceGroup[data-service_id="' + id + '"]').find('input[name="service_travel_time[' + id + ']"]').trigger('keyup');
	$('#readyServices').find('.serviceGroup[data-service_id="' + id + '"]').find('.service_vehicle').parent().find('[data-toggle="buttons"]:not(:has(>label))').parent().find('.service_vehicle').trigger('change');
	$('#readyServices').find('.serviceGroup[data-service_id="' + id + '"]').find('.service_trailer').parent().find('[data-toggle="buttons"]:not(:has(>label))').parent().find('.service_trailer').trigger('change');
}

function setFiles(draft) {
	if(draft['pre_uploaded_files'] !== undefined) {
		$.each(draft['pre_uploaded_files'], function (k,v) {
			// $(tempDiv).find('.serviceGroup[data-service_id="' + id + '"]').append('<input type="hidden" data-service_id="' + id + '" name="pre_uploaded_files[' + id + '][]" value="' + v.filepath + '" data-uuid="' + v.uuid + '" data-size="' + v.size + '" data-url="' + v.url + '" data-type="' + v.type + '" data-name="' + v.name + '">');
			// $('.serviceGroup[data-service_id="' + k + '"]').append('<input type="hidden" data-service_id="' + k + '" name="pre_uploaded_files[' + k + '][]" value="' + v.filepath + '" data-uuid="' + v.uuid + '" data-size="' + v.size + '" data-url="' + v.url + '" data-type="' + v.type + '" data-name="' + v.name + '">');
		});
	}
}
