<div id="add_tree" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form name="add_tree_modal" action="<?php echo base_url('info/add_tree'); ?>" method="POST">
				<div class="modal-body">
					<h5 class="p-bottom-20">Add New Tree</h5>
					<table class="table table-striped b-a b-light m-t-n-xxs m-b-none">
						<tr>
							<td class="w-200">
								<label class="control-label">Tree Name(Eng)</label>
							</td>
							<td class="p-left-30">
								<input type="text" name="name_eng" class="form-control">
							</td>
						</tr>
						<tr>
							<td class="w-200">
								<label class="control-label">Tree Name(Lat)</label>
							</td>
							<td class="p-left-30">
								<input type="text" name="name_lat" class="form-control">
							</td>
						</tr>
						<tr>
							<td class="w-200">
								<label class="control-label">Client Status:</label>
							</td>
							<td class="p-left-30">
								<select multiple="" name="pests[]" class="input-sm form-control" >
									<?php foreach($pests as $k=>$v) : ?>
										<option value="<?php echo $v->pest_id;?>"><?php echo $v->pest_eng_name;?> (<?php echo $v->pest_lat_name;?>)</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					<?php echo form_submit('submit', 'Add Tree', 'class="btn btn-info update__client"'); ?>
				</div>
			</form>
		</div>
	</div>
</div>
