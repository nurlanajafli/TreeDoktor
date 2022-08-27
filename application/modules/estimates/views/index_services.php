<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/sortable/jquery.sortable.js'); ?>"></script>
<style>.sortable-placeholder {
		min-height: 54px;
	}</style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Tree Services</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Tree Services
			<a href="#service-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false">New Service <i class="fa fa-plus"></i></a>
            <a style="margin-right: 10px;" href="#category-modal" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
               data-backdrop="static" data-keyboard="false">New Category <i class="fa fa-plus"></i></a>
		</header>
		<div class="table-responsive">
            <?php foreach ($services as $service) : ?>
                <?php $this->load->view('partials/service_modal', ['service' => $service]); ?>
            <?php endforeach; ?>
		 <?php $this->load->view('categories/category'); ?>
		</div>
	</section>
</section>
<div id="service-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Service</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<?php
					/*
					<div class="control-group">
						<label class="control-label">Service Parent Name</label>

						<div class="controls">
							<select name="service_parent" class="service_parent form-control">
								<option value=""> - </option>
								<?php foreach ($services as $value) : ?>
									<option value="<?php echo $value->service_id?>">
										<?php echo $value->service_name; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					*/ ?>
					<div class="control-group">
						<label class="control-label">Service Name</label>

						<div class="controls">
							<input class="service_name form-control" type="text" value="" placeholder="Service Name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Default Description</label>

						<div class="controls">
							<textarea class="service_description form-control" placeholder="Default Description"
							          rows="5"></textarea>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Markup (%)</label>

						<div class="controls">
							<input class="service_markup form-control" placeholder="Markup (%)" value="">
						</div>
					</div>
                    <div class="control-group parentCategory">
                        <label class="control-label">Category</label>
                        <div class="controls">
                            <input type="text" class="parentCategorySelect w-100" value="<?= !empty($product['service_category_id']) ? $product['service_category_id'] : 1 ?>" name="categoryId"/>
                        </div>
                    </div>
                    <?php if (!empty($classes)): ?>
                        <div class="control-group parentClass">
                            <label class="control-label">Class</label>
                            <div class="controls">
                                <input type="text" class="parentClassSelect w-100" value="<?= !empty($product['service_class_id']) ? $product['service_class_id'] : null ?>" name="classId"/>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="control-group ">
                        <label class="control-label">Crew</label>
                        <div class="controls">
                            <select class="crewsSelect w-100" data-service_id="">
                                <option></option>
                                <?php foreach ($crews as $crew) : ?>
                                    <option value="<?= $crew->crew_id ?>" ><?= $crew->crew_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="selectedCrew"></div>
                        <input type="hidden" name="crews" class="crews" value=''>
                    </div>
					<div class="serviceSetupList">
						<div class="row m-b-sm pos-rlt ">
							<div class="serviceSetupTpl">
								<div class="control-group col-md-6">
									<label class="control-label">Service Equipment</label>

									<div class="controls">
										<select name="service_vehicle[]" class="service_vehicle form-control">
											<option value="0"> - </option>
											<?php foreach ($vehicles as $veh) : ?>
												<option value="<?php echo $veh->vehicle_id?>"  data-options='<?php echo htmlentities($veh->vehicle_options, ENT_QUOTES, 'UTF-8'); ?>'>
													<?php echo $veh->vehicle_name; ?>
												</option>
											<?php endforeach; ?>
										</select>
										<select name="vehicle_option[]" class="vehicle_option form-control">
											<option value=""> None of the options </option>
										</select>
									</div>
								</div>
								<div class="control-group col-md-6">
									<label class="control-label">Service Attachment</label>

									<div class="controls">
										<select name="service_trailer[]" class="service_trailer form-control">
											<option value="0"> - </option>
											<?php foreach ($trailers as $trai) : ?>
												<option value="<?php echo $trai->vehicle_id?>" data-traioptions='<?php echo htmlentities($trai->vehicle_options, ENT_QUOTES, 'UTF-8');?>'  >
													<?php echo $trai->vehicle_name; ?>
												</option>
											<?php endforeach; ?>
										</select>
										<select name="trailer_option[]" class="trailer_option form-control">
											<option value=""> None of the options </option>
										</select>
									</div>
								</div>
								<div class="control-group col-md-12">
									<label class="control-label">Service Tools</label>

									<div class="controls">
										<?php foreach ($tools as $k=>$tool) : ?>
												<?php if($k%3==0) : ?><div class="row"><br><?php endif; ?>
												<div class="col-md-4">
													<strong><?php echo $tool->vehicle_name; ?></strong>
													<?php if($tool->vehicle_options && !empty((array)json_decode($tool->vehicle_options))) : ?>
														<?php foreach((array)json_decode($tool->vehicle_options) as $j=>$opt) : ?>
															<div class="radio p-left-0">
																<label>
																	<input type="checkbox" class="tools_option" data-tool_id="<?php echo $tool->vehicle_id; ?>" name="tools_option[<?php echo $tool->vehicle_id; ?>][0][]" value="<?php echo htmlentities($opt, ENT_QUOTES, 'UTF-8'); ?>">
																	<font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php echo $opt; ?></font></font>
																</label>
															</div>
														<?php endforeach; ?>
													<?php else : ?>
														<div class="radio p-left-0">
															<label>
																<input type="checkbox" class="tools_option" data-tool_id="<?php echo $tool->vehicle_id; ?>" name="tools_option[<?php echo $tool->vehicle_id; ?>][0][]" value="Any">
																<font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Any</font></font>
															</label>
														</div>
													<?php endif; ?>
													
												</div>
												<?php if($k%3 == 2 || !isset($tools[$k+1])) : ?></div><?php endif; ?>
										<?php endforeach; ?>
										
									</div>
								</div>
								<div class="control-group pos-abt" style="bottom: 10px;right: 50px;display:none;">
									<a class="btn btn-xs btn-danger removeAttach"><i class="fa fa-trash-o"></i></a>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
                    <div class="control-group">
                        <div class="controls">
                            <label class="control-label">
                                <input type="checkbox" name="service_collapsed_view" class="service-collapsed-view" checked> Collapsed view
                            </label>
                        </div>
                    </div>
                    <?php $this->load->view('partials/is_favourite', ['item' => null, 'type' => 'service']); ?>
				
				<button class="btn btn-xs btn-success pull-right pos-abt addAttach" style="bottom: 0px;right: 55px;" role="button"><i class="fa fa-plus"></i></button>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-service="">
					<span class="btntext">Save</span>
					<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
					     class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>
    <div id="category-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content panel panel-default p-n">
                <?php $this->load->view('categories/partials/category_form', ['category' => []]); ?>
            </div>
        </div>
    </div>
<script>
	$(document).ready(function () {
	    $('.crewsSelect').select2({
            placeholder: "Please select a crew"
        }).on('select2-selecting', function (e) {
            let service_id = $(this).data('service_id');
            let modal = $('#service-' + service_id);
            let crews = modal.find('.crews');
            let arr = [];
            if(crews.val()){
                if(IsJson(crews.val()) && Array.isArray(JSON.parse(crews.val())))
                    arr = JSON.parse(crews.val());
                else if(crews.val().indexOf(',') + 1)
                    arr = new Array(crews.val().split(','));
                else
                    arr.push(crews.val());
            }
            arr.push(e.choice.id);
            // crews.val(arr.toString());
            crews.val(JSON.stringify(arr));
            modal.find('.selectedCrew').append('<label class="btn btn-success inline m-r-xs" data-service_id="'+ service_id +'" data-id="'+ e.choice.id + '" style="padding: 3px 5px;">' + e.choice.text + ' <a href="#" class="moveFromCrew">x</a><input type="hidden" "></label>')
        }).on('select2-close', function (e) {
            $(this).select2('val', null).trigger('change');
        });
        $(document).on('click', '.moveFromCrew', function(){
            let obj = $(this).closest('label');
            let value = obj.data('id');
            let service_id = obj.data('service_id');
            let crews = $('#service-' + service_id).find('.crews');
            let arr = [];
            if(crews.val()){
                if(IsJson(crews.val()))
                    arr = JSON.parse(crews.val());
                else
                    arr = crews.val().split(',');
            }
            let index = -1;
            if(arr == value)
                arr = [];
            if(Array.isArray(arr))
                index = $.inArray(value.toString(), arr);
            if(index != -1)
                arr.splice(index, 1);
            crews.val(JSON.stringify(arr));
            obj.remove();
        });

	    $(document).on('click','.favourite-checkbox', function () {
	        let item_id = $(this).data('item_id');
            if(!item_id){
                let modal = $('#service-');
                let item_name = modal.find('.service_name').val();
                if(item_name.trim() !== ''){
                    let first_letters = '';
                    let item_name_array = item_name.trim().split(' ');
                    if(item_name_array[0] && item_name_array[1])
                        first_letters = item_name_array[0].substr(0,1).toUpperCase() + item_name_array[1].substr(0,1).toUpperCase();
                    else if(item_name_array[0])
                        first_letters = item_name_array[0].substr(0,2).toUpperCase();
                    modal.find('.first-letters').text(first_letters);
                    modal.find('.first-letters').closest('label').show();
                } else {
                    modal.find("input[value='tree-removal']").prop('checked', true);
                    modal.find('.first-letters').closest('label').hide();
                }
            }
            $('.favourite-icons-' + item_id).toggle();
        });
        initQbLogPopover();
	    $('.percentage').inputmask('Regex', {regex: "^[0-9]{1,6}(\\.\\d{0,2})?$"});
		$('.sortable').sortable().bind('sortupdate', function () {
			var arr = [];
			$.each($('.sortable').children(), function (key, val) {
				priority = key + 1;
				//$(val).find('.priority').text(priority);
				arr[$(val).data('id')] = priority;
			});
			$.post(baseUrl + 'estimates/ajax_estimate_priority_service', {data: arr}, function (resp) {
				if (resp.status == 'error')
					alert('Ooops! Error...');
				return false;
			}, 'json');
			return false;
		});
		$('[data-save-service]').click(function () {
			var service_id = $(this).data('save-service');
			$(this).attr('disabled', 'disabled');
			let button = this;
			$('#service-' + service_id + ' .modal-footer .btntext').hide();
			$('#service-' + service_id + ' .modal-footer .preloader').show();
			$('#service-' + service_id + ' .service_name').parents('.control-group').removeClass('error');
			var tpls = $('#service-' + service_id).find('.serviceSetupTpl');
			var service_name = $('#service-' + service_id).find('.service_name').val();
			var service_parent = $('#service-' + service_id).find('.service_parent').val();
			var service_description = $('#service-' + service_id).find('.service_description').val();
			var service_markup = $('#service-' + service_id).find('.service_markup').val();
			let crews = $('#service-' + service_id).find('.crews').val();
			let crews_to_db;
			if(crews){
                if(IsJson(crews) && Array.isArray(JSON.parse(crews)))
                    crews_to_db = JSON.parse(crews);
                else if(crews.indexOf(',') + 1)
                    crews_to_db = crews.split(',');
                else
                    crews_to_db = [crews];
            }
			let is_collapsed = $('#service-' + service_id).find('.service-collapsed-view').prop('checked');
			let service_is_collapsed = 1;
			if(!is_collapsed)
                service_is_collapsed = 0;
			let is_favourite = $('#service-' + service_id).find('.favourite-checkbox').prop('checked');
			let favourite_icon = '';
			if(is_favourite) {
                favourite_icon = $('#service-' + service_id).find("input[name='favourite_icon_" + service_id + "']:checked").val();
                is_favourite = 1;
            } else {
                is_favourite = 0;
            }
			var service_vehicles = [];
			var service_trailers = [];
			var trailers_options = [];
			var vehicles_options = [];
			var service_tools = [];
			var tools_option = {};
            let category_id = null;
            let class_id = null;
			if($('#service-' + service_id).find('.parentCategorySelect').select2('data'))
                category_id = $('#service-' + service_id).find('.parentCategorySelect').select2('data').id;

            if($('#service-' + service_id).find('.parentClassSelect').select2('data'))
                class_id = $('#service-' + service_id).find('.parentClassSelect').select2('data').id;
			
			$.each($('#service-' + service_id).find('.service_vehicle'), function (key, val) {
				//if($(val).val() != undefined && $(val).val() != '')
					service_vehicles.push($(val).val());
			});
			
			$.each($('#service-' + service_id).find('.service_trailer'), function (key, val) {
				//if($(val).val() != undefined && $(val).val() != '')
					service_trailers.push($(val).val());
			});
			$.each($('#service-' + service_id).find('.trailer_option'), function (key, val) {
				//if($(val).val() != undefined && $(val).val() != '')
					trailers_options.push($(val).val());
			});
			$.each($('#service-' + service_id).find('.vehicle_option'), function (key, val) {
				//if($(val).val() != undefined && $(val).val() != '')
					vehicles_options.push($(val).val());
			});
			/*$.each($('#service-' + service_id).find('.service_tools'), function (key, val) {
				if($(val).val() != '' || $(val).val() != undefined)
					service_tools.push($(val).val());
			});*/
			$.each($(tpls), function (key, val) {
				tools_option[key] = {};
				$.each($(val).find('.tools_option:checked'), function (k, v) {
					var tool_id = $(v).attr('data-tool_id');
					/*if(service_vehicles[key] != undefined && service_vehicles[key] != '')
					{*/
						if(tools_option[key][tool_id] == undefined)
							tools_option[key][tool_id]=[];
							
						tools_option[key][tool_id].push($(v).val());
					/*}*/
				});
			});
			if (!service_name) {
				$('#service-' + service_id + ' .service_name').parents('.control-group').addClass('error');
				$('#service-' + service_id + ' .modal-footer .btntext').show();
				$('#service-' + service_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'estimates/ajax_save_service', {crews : crews_to_db, service_is_collapsed : service_is_collapsed, favourite_icon: favourite_icon, is_favourite: is_favourite, class_id: class_id, category_id: category_id, service_id: service_id, service_name: service_name, service_markup: service_markup, service_description: service_description, service_parent:service_parent, service_vehicles:service_vehicles, service_trailers:service_trailers, vehicles_options:vehicles_options, trailers_options:trailers_options, service_tools:service_tools, tools_option:tools_option}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
                else {
                    errorMessage(resp.error);
                    $('#service-' + service_id + ' .modal-footer .btntext').show();
                    $('#service-' + service_id + ' .modal-footer .preloader').hide();
                    $(button).removeAttr('disabled');
                }

				return false;
			}, 'json');
			return false;
		});
		$('.addAttach').click(function (){
			var obj = $(this).parent();
			
			var tpl = $(obj).find('.serviceSetupTpl:first').clone();
			var len = $(obj).find('.serviceSetupTpl').length;
			var tmpDiv = document.createElement('div');
			
			$(tmpDiv).append(tpl);
			$(tmpDiv).children().removeClass('serviceSetupTpl');
			$(tmpDiv).find('.service_vehicle option[selected]').removeAttr('selected');
			$(tmpDiv).find('.service_trailer option[selected]').removeAttr('selected');
			$(tmpDiv).find('.vehicle_option option[selected]').removeAttr('selected');
			$(tmpDiv).find('.trailer_option option[selected]').removeAttr('selected');
			$(tmpDiv).find('.vehicle_option option').remove();
			$(tmpDiv).find('.trailer_option option').remove();
			$(tmpDiv).find('.vehicle_option').append('<option value="">None of the options </option>');
			$(tmpDiv).find('.trailer_option').append('<option value="">None of the options </option>');
			$(tmpDiv).find('.service_tools option[selected]').removeAttr('selected');
			$.each($(tmpDiv).find('.tools_option'), function (key, val) {
				tool_id = $(val).attr('data-tool_id');
				$(val).attr('name', 'tools_option[' + tool_id +'][' + len + '][]');
			});
			$.each($(tmpDiv).find('.tools_option:checked'), function (key, val) {
				$(val).prop('checked', false);
			});
			if(len != undefined && len)
				$(tmpDiv).find('.removeAttach').parent().css('display', 'block');
			
			$(tmpDiv).addClass('row m-b-sm pos-rlt');
			$(tmpDiv).children().addClass('serviceSetupTpl');
			$(obj).find('.serviceSetupList').append($(tmpDiv));
			
			//$(obj).find('.serviceSetupList').append($(tmpDiv).children().addClass('row'));
			//$(tmpDiv).children().appendTo('.serviceSetupList');
		});
		$('.deleteService').click(function () {
			var service_id = $(this).data('service_id');
			if (confirm('Are you sure?')) {
				var status = 0;
				if ($(this).children().is('.fa-eye'))
					status = 1;
				$.post(baseUrl + 'estimates/ajax_delete_service', {service_id: service_id, status: status}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
		$(document).on('change', '.service_vehicle', function(){
			let obj = $(this).parents('.controls:first');
			let options = $(this).find('option:selected').data('options');
			let tpl = '<select name="vehicle_option[]" class="vehicle_option form-control"><option value=""> None of the options </option>';
			$.each($(options), function (key, val) {
				tpl += '<option value="' + val + '">' + val + '</option>';
			});
			tpl += '</select>';
			$(obj).find('.vehicle_option:first').replaceWith(tpl);
			if(!$(this).find('option:selected').val()) {
                let serviceSetupTpl = obj.closest('.serviceSetupTpl');
                let serviceTrailer = serviceSetupTpl.find('.service_trailer');
                serviceTrailer.val(serviceTrailer.find('option:first').val()).change();
                serviceSetupTpl.find('input:checkbox').removeAttr('checked');
            }
		});
		$(document).on('change', '.service_trailer', function(){
			var obj = $(this).parents('.controls:first');
			var options = $(this).find('option:selected').data('traioptions');
			var tpl = '<select name="trailer_option[]" class="trailer_option form-control"><option  value=""> None of the options </option>';
			$.each($(options), function (key, val) {
				tpl += '<option value="' + val + '">' + val + '</option>';
			});
			tpl += '</select>';
			$(obj).find('.trailer_option:first').replaceWith(tpl);
		});
		$(document).on('change', '.service_tools', function(){
			var obj = $(this).parents('.controls:first');
			var options = $(this).find('option:selected').data('tooloptions');
			var tpl = '<select name="tools_option[]" class="tools_option form-control"><option> None of the options </option>';
			$.each($(options), function (key, val) {
				tpl += '<option value="' + val + '">' + val + '</option>';
			});
			tpl += '</select>';
			//console.log(obj, options, tpl); return false;
			$(obj).find('.tools_option:first').replaceWith(tpl);
		});
		$(document).on('click', '.removeAttach', function (){
			
			var obj = $(this).parents('.row:first');
			
			$(obj).remove();
			return false;
		});
	});
    function IsJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }
</script>
<?php $this->load->view('includes/footer'); ?>
