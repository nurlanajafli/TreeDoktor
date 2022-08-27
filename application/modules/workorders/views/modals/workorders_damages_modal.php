
<!-- Change Estimate Status Modal -->
<div id="workorders_damages" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Damages & Complains</header>
			<div class="modal-body">
				
				<form id="workorders_damages_form"></form>


				<script id="theTmpl" type="text/x-jsrender">
					
					<input type="hidden" name="event_id[]" value="{{:id}}"/>
					<div class="alert alert-info">
	                    <div>
		                    <i class="fa fa-info-sign"></i><strong><em>Team:&nbsp;</em></strong>
		                   	{{if crew_name }} {{:crew_name}} ( {{/if}}
		                    	{{:team_members}}
		                    {{if crew_name }} ) {{/if}}
		                    <span class="text-muted m-l-sm pull-right">
	                        	<i class="fa fa-clock-o"></i>
	                        	{{:team_date}}
	                      	</span>
                      	</div>
	                </div>
										
					<div class="col-sm-12">
						<div class="row">
							<div class="col-md-6 input-group m-b pull-left">
								<span class="input-group-addon"><?php echo get_currency(); ?></span>
								<input type="text" class="form-control" placeholder="Damages" name="damage-{{:id}}">
							</div>
							<div class="col-md-6 input-group m-b pull-right">
								<span class="input-group-addon"><?php echo get_currency(); ?></span>
								<input type="text" class="form-control" placeholder="Complains" name="complain-{{:id}}">
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="line line-dashed line-lg pull-in"></div>
				</script>
			</div>

			<div class="modal-footer">
				<div class="pull-right ">
					<button name="save" id="save-damage-complain" class="btn btn-success m-right-5">
						<span class="btntext">Save</span>
						<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
						     style="display: none;width: 32px;" class="preloader">
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /Change Estimate Status Modal End-->
<?php if (empty($schedule) && !isset($unsortable)) : ?>
<script async src="<?php echo base_url('/assets/js/modules/workorders/workorders_damages_modal.js'); ?>"></script>
<?php endif; ?>