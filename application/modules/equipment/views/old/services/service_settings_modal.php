<div id="<?php if(isset($new_modal) && $new_modal) : ?>add_service_setting<?php else : ?>edit_service_setting_<?php echo $id; ?><?php endif; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading"><?php if(isset($new_modal) && $new_modal) : ?>New Service<?php else : ?>Edit Service Settings<?php endif; ?></header>
			<form data-type="ajax" method="POST" data-url="<?php echo base_url('equipments/ajax_service_settings'); ?>" data-location="<?php echo current_url(); ?>">
				<div class="modal-body form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Service Type</label>
						<div class="col-sm-9">
							<select name="service_type" type="select" class="form-control" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								<option value="">- Select Service Type -</option>
								<?php foreach($service_types as $type) : ?>
									<option value="<?php echo $type['equipment_service_id']; ?>"<?php if((!isset($new_modal) || !$new_modal) && isset($service_type_id) && $service_type_id == $type['equipment_service_id']) : ?> selected<?php endif; ?>>
										<?php echo $type['equipment_service_type']; ?>
									</option>
								<?php endforeach; ?>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="line line-dashed line-lg"></div>
					<div class="form-group">

						<label class="col-sm-3 control-label">Month Periodicity</label>
						<div class="col-sm-9">							
							<select class="form-control periodicityControl months" name="service_months" style="" data-toggle="tooltip" data-placement="top" title="" data-original-title="">
								<option value=""> - Select Count Months - </option>
								<?php for($i = 1; $i <= 36; $i++) : ?>
									<option value="<?php echo $i; ?>"<?php if((!isset($new_modal) || !$new_modal) && isset($service_period_months) && $service_period_months == $i) : ?> selected<?php endif; ?>><?php echo $i; ?></option>
								<?php endfor; ?>
							</select>
							<span class="help-inline text-danger"></span>

						</div>
						
					</div>
					<div class="line line-dashed line-lg"></div>
					<div class="form-group">

						<label class="col-sm-3 control-label">Counter Periodicity</label>
						<div class="col-sm-9">							
							<input class="form-control" name="report_kilometers" placeholder="Counter Periodicity" value="<?php echo ($service_period_kilometers)?$service_period_kilometers:''; ?>">
							<span class="help-inline text-danger"></span>

						</div>
						
					</div>
				
					<?php if(isset($new_modal) && $new_modal) : ?>
						<?php if(isset($item[0]->group_name)) : ?>
							<div class="line line-dashed line-lg"></div>
							<div class="form-group">
								<label class="col-sm-3 control-label">Add For All <?php echo $item[0]->group_name; ?></label>
								<div class="col-sm-9">
									<div class="checkbox">
										<label>
											<input type="checkbox" name="for_all_in_group" value="1">
											
										</label>
									</div>
								</div>
							</div>
						<?php endif; ?>
					<div class="line line-dashed line-lg"></div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Service Date Start</label>
						<div class="col-sm-9">
							<input class="form-control" name="service_start" placeholder="Service start"  data-date-format="yyyy-mm-dd" value="<?php echo ($service_start)?date('Y-m-d', strtotime($service_start)):date('Y-m-d'); ?>">
						</div>
					</div>
					<?php endif; ?>

					<div class="modal-footer">
						<input type="hidden" name="item_id" value="<?php echo $vehicle_id; ?>">
						<?php if((!isset($new_modal) || !$new_modal) && isset($id) && $id) : ?>
							<input type="hidden" name="id" value="<?php echo $id; ?>">
						<?php endif; ?>
						<button class="btn btn-info" type="submit">
							<span class="btntext">
								<?php if((!isset($new_modal) || !$new_modal) && isset($id) && $id) : ?>
									Save Service
								<?php else : ?>
									Add Service
								<?php endif; ?>
							</span>
							<img src="http://crmold.dev/assets/img/ajax-loader.gif" style="display: none;width: 84px;" class="preloader">
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('[data-date-format="yyyy-mm-dd"]').datepicker({format: 'yyyy-mm-dd'});
	}); 

</script>