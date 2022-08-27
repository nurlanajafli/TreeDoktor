
<script type="text/x-jsrender" id="infowindowform-modal-tmp" >
<form class="form-horizontal pt-3" >
    <input type="hidden" name="service_id" value="{{:service_id}}">

    <div class="form-group mt-2">
        <label class="col-sm-3 control-label">Tree ID:</label>
        <div class="col-sm-9">
            <input class="form-control" name="ties_number" value="{{:ties_number}}">
        </div>
    </div>
	<div class="form-group">
	    <label class="col-sm-3 control-label">Tree Type:</label>
        <div class="col-sm-9">
            <select class="w-100" name="ti_tree_type" id="ti_tree_type_select">
                <option value="">Empty</option>
                <?php if(isset($trees_types) && !empty($trees_types)): ?>
                    <?php foreach($trees_types as $tree): ?>
                        <option {{if ties_type==<?php echo $tree->trees_id; ?>}}selected="selected"{{/if}} value="<?php echo $tree->trees_id; ?>"><?php echo ucwords($tree->trees_name_eng); ?><?php if(!empty($tree->trees_name_lat)) : ?> (<?php echo $tree->trees_name_lat; ?>)<?php endif; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">Tree Priority:</label>
        <div class="col-sm-9">
           <select name="ties_priority" class="form-control">
                <option value="">Select Priority</option>
                <option value="low" {{if ties_priority=='low'}}selected="selected"{{/if}}>Low (Pruning for aesthetic purposes, optional)</option>
                <option value="medium" {{if ties_priority=='medium'}}selected="selected"{{/if}}>Mid (General trimming)</option>
                <option value="high" {{if ties_priority=='high'}}selected="selected"{{/if}}>High (Priority pruning)</option>
           </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Size:</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" name="ties_size" value="{{:ties_size}}">
        </div>
    </div>

    <div class="work_types form-group">
        <label class="col-sm-3 control-label">Work Type:</label>
        <div class="col-sm-9">
            <select name="work_types[]"  class="w-100" id="work_types_select" multiple="multiple">
                <option value="">Empty</option>
                <?php if(isset($work_types) && !empty($work_types)): ?>
                    <?php foreach($work_types as $type): ?>
                        <option value="<?php echo $type->ip_id; ?>" ><?php echo $type->ip_name_short; ?>:<?php echo $type->ip_name; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="form-group text-right mb-3">
		<div class="col-lg-12 col-md-12 col-sm-12">
		<button type="button" class="btn btn-success close-modalwindow" data-dismiss="modal">Save</button>
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
      <div class="modal-body p-bottom-0" id="infowindowform-modal-body">

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>


