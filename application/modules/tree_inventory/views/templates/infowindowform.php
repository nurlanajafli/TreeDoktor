<script type="text/x-jsrender" id="infowindowform-tmp">
<div style="width:auto;display:flex;justify-content:space-between">
<div class="row_history_template hidden">
<div class="row m-left-0"  style="margin-right:0;" >
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-10 history_item_title font-weight-bold p-left-0">- - - - - </div>
                <div class="col-12 history_item_estimate"><b>Estimate:</b> <a href="#" target="_blank" class="estimate_id">- - - - - </a></div>
                <div class="col-12 history_item_work_types"><b>Work Type</b>: <span class="work_types">- - - - - </span></div>
                <div class="col-12 collapse" id="collapse86" aria-expanded="false">
                    <div class="row m-left-0">
                        <div class="col-12">
                            <b>Notes</b>: <span class="notes">- - - - - </span>
                        </div>
                        <div class="col-12 cost">
                            <b>Cost</b>: $ <span class="cost">- - - - - </span>
                        </div>
                        <div class="col-12 stump">
                            <b>Stump</b>: $ <span class="stump">- - - - - </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2" style="display:flex; align-items:center;height:45px;">
            <a class="toggleChevron" style="outline:none;" data-toggle="collapse" href="#collapse86" aria-expanded="false">
                <i class="fa fa-angle-down" aria-hidden="true"></i>
            </a>
        </div>
    </div>
    <hr>
</div>
<div id="history_tree_block" style="width:255px; max-height:450px; overflow-y: auto; display:none;">
    <div class="row m-left-0 text-center" style="margin-bottom:10px;margin-right:0;font-weight:bold;">Serviced in Estimates</div>
    <div class="result"></div>
</div>
<form style="width:380px; display:block;" class="form-horizontal" data-type="ajax" data-url="<?php echo site_url('tree_inventory/save_points'); ?>" data-callback="TreeInventoryMap.save_callback">
	{{if ti_id!=undefined}}
	<input type="hidden" name="ti_id" value="{{:ti_id}}">
	{{/if}}
	<input type="hidden" name="ti_lat" value="{{:ti_lat}}">
	<input type="hidden" name="ti_lng" value="{{:ti_lng}}">
	<input type="hidden" name="ti_tis_id" value="{{:ti_tis_id}}">

	<input type="hidden" name="ti_client_id" value="<?php echo $client->client_id; ?>">
	<input type="hidden" name="ti_map_type" value="map">

	<div class="form-group text-right m-n">
	<input type="hidden" name="ti_lead_id" id="ti_lead_id" value="{{:ti_lead_id}}">
	<span class="form-error text-danger"></span>
	</div>

	<div class="form-group">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Tree #:</label>
			<div class="{{if ti_id!=undefined}}col-lg-5 col-md-5 col-sm-5{{else}}col-lg-5 col-md-5 col-sm-5{{/if}} p-right-0">
				<input type="text" class="form-control" name="ti_tree_number" value="{{:ti_tree_number}}">
			</div>
			<div class="col-lg-4 col-md-4 col-sm-5">
				<span class="btn btn-primary btn-file">Choose File
	                <input type="file" name="file" id="fileToUpload" class="btn-upload" accept="image/jpeg, image/pjpeg,image/png,image/x-png">
	            </span>
			</div>
			<label class="col-lg-3 col-md-3 col-sm-3 control-label p-n"></label>
			<div class="col-lg-9 col-md-9"><span class="form-error text-danger"></span></div>
		</div>
	</div>


	<div class="form-group m-bottom-7">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Tree Type:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<select name="ti_tree_type" id="ti_tree_type">
					<option value="">Empty</option>
					<?php if(isset($trees) && !empty($trees)): ?>
						<?php foreach($trees as $tree): ?>
							<option {{if ti_tree_type==<?php echo $tree->trees_id; ?>}}selected="selected"{{/if}} value="<?php echo $tree->trees_id; ?>"><?php echo ucwords($tree->trees_name_eng); ?><?php if(!empty($tree->trees_name_lat)) : ?> (<?php echo $tree->trees_name_lat; ?>)<?php endif; ?></option>
						<?php endforeach; ?>
					<?php endif; ?>

				</select>
				<span class="form-error text-danger"></span>
			</div>
		</div>
	</div>

	<div class="form-group m-bottom-10">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Tree Priority:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<select name="ti_tree_priority" class="form-control">
					<option value="">Select Priority</option>
					<option value="low" {{if ti_tree_priority=='low'}}selected="selected"{{/if}}>Low (Pruning for aesthetic purposes, optional)</option>
					<option value="medium" {{if ti_tree_priority=='medium'}}selected="selected"{{/if}}>Mid (General trimming)</option>
					<option value="high" {{if ti_tree_priority=='high'}}selected="selected"{{/if}}>High (Priority pruning)</option>
				</select>
				<span class="form-error text-danger"></span>
			</div>

		</div>	
	</div>
	<div class="form-group m-bottom-10">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Size:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<input type="text" class="form-control" name="ti_size" value="{{:ti_size}}">
				<span class="form-error text-danger"></span>
			</div>
		</div>
	</div>
	
	<div class="work_types form-group">
		<div class="" style="position:relative;">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Work Type:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<select name="work_types[]" id="work_types" multiple="multiple">
					<option value="">Empty</option>
					<?php if(isset($work_types) && !empty($work_types)): ?>
						<?php foreach($work_types as $type): ?>
							<option value="<?php echo $type->ip_id; ?>"><?php echo $type->ip_name_short; ?>:<?php echo $type->ip_name; ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<span class="form-error text-danger"></span>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Notes:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<textarea rows="3" class="form-control" name="ti_remark">{{:ti_remark}}</textarea>
				<span class="form-error text-danger"></span>
			</div>
		</div>
	</div>
	
	<div class="row m-n">
	<label class="col-lg-3 col-md-3 col-sm-3 control-label">Cost:</label>
	<div class="form-group col-lg-9 col-md-9 col-sm-9">
		<div class="row">
			<div class="col-lg-5 col-md-5 col-sm-5">
				<input type="text" step="0.01" class="form-control currency" name="ti_cost" value="{{:ti_cost}}">
			</div>
			
			<label class="col-lg-2 col-md-2 col-sm-2 control-label">Stump:</label>

			<div class="col-lg-5 col-md-5 col-sm-5">
				<input type="text" step="0.01" class="form-control currency" name="ti_stump_cost" value="{{:ti_stump_cost}}">
			</div>
			<span class="form-error text-danger"></span>
		</div>
	</div>
	

	<div class="form-group text-right m-n">
	    <div class="col-lg-3 col-md-3 col-sm-3 text-left">
	    {{if ti_id!=undefined}}
			<button id="eye_tree_info"  data-ti_id="{{:ti_id}}" type="button" class="btn" style="background:none;outline: none;"><i class="fa fa-eye" style="color:#8ec165"></i></button>
		{{/if}}
	    </div>
		<div class="col-lg-6 col-md-6 col-sm-6 text-center">
		    <button type="button" class="btn btn-default close-infowindow" data-marker="{{:ti_lat}}_{{:ti_lng}}">Close</button>
		    <button type="submit" class="btn btn-success" data-marker="{{:ti_lat}}_{{:ti_lng}}" id="save-tree-details">Save</button>
		</div>
		<div class="col-lg-3 col-md-3 col-sm-3 text-right">
	        {{if ti_id!=undefined}}
				<button type="button" class="btn confirmDelete" data-confirmation-massage="Are you sure to delete <span class='text-danger'># {{:ti_tree_number}}</span> ?" data-yes-text="Yes" data-submit-form="#delete-{{:ti_map_type}}-item-{{:ti_id}}" style="background:none;"><i class="fa fa-trash-o " style="color:#fb6b5b"></i></button>
			{{/if}}
	    </div>
	</div>

</form>
</div>


</script>


<script type="text/x-jsrender" id="infowindowform-modal-tmp">

<form class="form-horizontal" data-type="ajax" data-url="<?php echo site_url('tree_inventory/save_points'); ?>" data-callback="TreeInventoryImage.save_callback">
	{{if ti_id!=undefined}}
	<input type="hidden" name="ti_id" value="{{:ti_id}}">
	{{/if}}
	<input type="hidden" name="ti_lat" value="{{:ti_lat}}">
	<input type="hidden" name="ti_lng" value="{{:ti_lng}}">
	<input type="hidden" name="ti_tis_id" value="{{:ti_tis_id}}">

	<input type="hidden" name="ti_client_id" value="<?php echo $client->client_id; ?>">
	<input type="hidden" name="ti_map_type" value="image">

	<div class="form-group text-right">
	<input type="hidden" name="ti_lead_id" id="ti_lead_id" value="{{:ti_lead_id}}">
	<span class="form-error text-danger"></span>
	</div>

	<div class="form-group">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Tree #:</label>
			<div class="{{if ti_id!=undefined}}col-lg-3 col-md-3 col-sm-3{{else}}col-lg-5 col-md-5 col-sm-5{{/if}}">
				<input type="text" class="form-control" name="ti_tree_number" value="{{:ti_tree_number}}">
			</div>
			<div class="col-lg-4 col-md-4 col-sm-4">
				<span class="btn btn-primary btn-file">Choose File
	                <input type="file" name="file" id="fileToUpload" class="btn-upload">
	            </span>
			</div>
			{{if ti_id!=undefined}}
			<div class="col-lg-2 col-md-2 col-sm-2">
				<button type="button" class="btn btn-rounded btn-sm btn-icon btn-default confirmDelete" data-confirmation-massage="Are you sure to delete <span class='text-danger'># {{:ti_tree_number}}</span> ?" data-yes-text="Yes" data-submit-form="#delete-{{:ti_map_type}}-item-{{:ti_id}}"><i class="fa fa-trash-o"></i></button>
			</div>
			{{/if}}
			<label class="col-lg-3 col-md-3 col-sm-3 control-label"></label>
			<div class="col-lg-9 col-md-9"><span class="form-error text-danger"></span></div>
		</div>
	</div>


	<div class="form-group">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Tree Type:</label>
			<div class="col-lg-9 col-md-9 col-sm-9" style="position:relative" id="ti_tree_type_container">
				<select name="ti_tree_type" id="ti_tree_type">
					<option value="">Empty</option>
					<?php if(isset($trees) && !empty($trees)): ?>
						<?php foreach($trees as $tree): ?>
							<option {{if ti_tree_type==<?php echo $tree->trees_id; ?>}}selected="selected"{{/if}} value="<?php echo $tree->trees_id; ?>"><?php echo ucwords($tree->trees_name_eng); ?><?php if(!empty($tree->trees_name_lat)) : ?> (<?php echo $tree->trees_name_lat; ?>)<?php endif; ?></option>
						<?php endforeach; ?>
					<?php endif; ?>

				</select>
				<span class="form-error text-danger"></span>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Tree Priority:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<select name="ti_tree_priority" class="form-control">
					<option value="">Select Priority</option>
					<option value="low" {{if ti_tree_priority=='low'}}selected="selected"{{/if}}>Low (Pruning for aesthetic purposes, optional)</option>
					<option value="medium" {{if ti_tree_priority=='medium'}}selected="selected"{{/if}}>Mid (General trimming)</option>
					<option value="high" {{if ti_tree_priority=='high'}}selected="selected"{{/if}}>High (Priority pruning)</option>
				</select>
				<span class="form-error text-danger"></span>
			</div>

		</div>	
	</div>
	<div class="form-group">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Size:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<input type="text" class="form-control" name="ti_size" value="{{:ti_size}}">
				<span class="form-error text-danger"></span>
			</div>
		</div>
	</div>
	
	<div class="work_types form-group">
		<div class="" style="position:relative;">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Work Type:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<select name="work_types[]" id="work_types" multiple="multiple">
					<option value="">Empty</option>
					<?php if(isset($work_types) && !empty($work_types)): ?>
						<?php foreach($work_types as $type): ?>
							<option value="<?php echo $type->ip_id; ?>"><?php echo $type->ip_name_short; ?>:<?php echo $type->ip_name; ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<span class="form-error text-danger"></span>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="">
			<label class="col-lg-3 col-md-3 col-sm-3 control-label">Notes:</label>
			<div class="col-lg-9 col-md-9 col-sm-9">
				<textarea rows="3" class="form-control" name="ti_remark">{{:ti_remark}}</textarea>
				<span class="form-error text-danger"></span>
			</div>
		</div>
	</div>
	
	<div class="row m-n">
	<label class="col-lg-3 col-md-3 col-sm-3 control-label">Cost:</label>
	<div class="form-group col-lg-9 col-md-9 col-sm-9">
		<div class="row">
			<div class="col-lg-5 col-md-5 col-sm-5">
				<input type="text" step="0.01" class="form-control currency" name="ti_cost" value="{{:ti_cost}}">
			</div>

			<label class="col-lg-2 col-md-2 col-sm-2 control-label hidden">Stump:</label>

			<div class="col-lg-5 col-md-5 col-sm-5 hidden">
				<input type="text" step="0.01" class="form-control currency" name="ti_stump_cost" value="{{:ti_stump_cost}}">
			</div>
			<span class="form-error text-danger"></span>
		</div>
	</div>
	

	<div class="form-group text-right">
		<div class="col-lg-12 col-md-12 col-sm-12">
		<button type="button" class="btn btn-default close-modalwindow" data-marker="{{:ti_lat}}_{{:ti_lng}}" data-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-success" data-marker="{{:ti_lat}}_{{:ti_lng}}" id="save-image-tree-details">Save</button>
		</div>	
	</div>

</form>


</script>

<div class="hidden" id="infowindowform">
</div>

<div class="modal fade" id="infowindowform-modal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 9998;">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title">Tree</h4>
      </div>
      <div class="modal-body p-bottom-0 p-top-2" id="infowindowform-modal-body">
        
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
