<?php $num = isset($num) && $num ? $num : 1; ?>

	<div class="col-md-12 col-sm-12 col-lg-4 files-section">
	<div class="panel panel-default p-n m-md-bottom-n m-lg-bottom-15">
		<header class="panel-heading"> 
			<span class="doc_title">
				File #<?php echo $num; ?>
			</span>
			<div class="pull-right"> 
				<?php if($num != 1) : ?>
					<a class="btn btn-xs btn-danger btn-rounded delete-doc" style="line-height: 17px;">
						<i class="fa fa-minus"></i>
					</a>
				<?php endif; ?>
				<a class="btn btn-xs btn-success btn-rounded add-doc" style="line-height: 17px;">
					<i class="fa fa-plus"></i>
				</a>
			</div>
		</header>
		<table class="table m-n profile-table">
			<tr>
				<td>
					<label class="control-label">Name:</label>
					<input type="text" name="file_name[]" class="form-control" value="<?php echo isset($doc->file_name) && $doc->file_name ? $doc->file_name : ''; ?>">
				</td>
				<td style="width: 20%;">
					<label class="control-label">Exp:</label>
					<input type="text" name="file_exp[]" data-date-format ="yyyy-mm-dd"
					class="form-control doc_exp datepicker" value="<?php echo isset($doc->file_exp) && $doc->file_exp ? $doc->file_exp : ''; ?>">
				</td>
				<td style="width: 5%;">
					<label class="control-label">Notification</label>
					<div class="form-group">
						<select name="file_notification[]" class="form-control">
							<option value="0" <?php echo !isset($doc->file_notification) || $doc->file_notification == 0 ? 'selected="selected"' : ''; ?> >No</option>
							<option value="1" <?php echo isset($doc->file_notification) && $doc->file_notification == 1 ? 'selected="selected"' : ''; ?> >Yes</option>
						</select>
					</div>
				</td>
			</tr>
			
			<tr>
				<td colspan="3">
					<select name="file_notification_user[]" class="form-control file-notification-user">
						<option value="" <?php if($doc->file_notification_user == null) {echo 'selected="selected"';} ?>>Notify User</option>
						<?php foreach($users as $user) { ?>
							<?php if($user['system_user'] == 0 && $user['active_status'] == 'yes') { ?>
							<option value="<?= $user['id'] ?>" <?php if($doc->file_notification_user == $user['id']) {echo 'selected="selected"';} ?>>
								<?= $user['firstname'] . ' ' . $user['lastname'] ?>
							</option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			
			<tr> 
				<td colspan="3">
					<label class="control-label">File:</label>
					<?php if(isset($doc->file_name)) : ?>
						<span class="pull-right">
							<a href="<?php echo base_url() . 'uploads/equipments_files/' . $item[0]->item_id . '/' . $doc->file_path; ?>" target="_blank" ><?php echo $doc->file_path; ?></a>
						</span>
						<input type="hidden" name="file_id[]" value="<?php echo $doc->file_id; ?>">
						<div class="clear"></div>
					<?php endif; ?>
					<div class="">
						<div class="fileinput fileinput-new input-group w-100" data-provides="fileinput" style="border: 1px solid #d9d9d9;">
							<span class=" btn btn-secondary btn-file p-n w-100" > 
								<input type="file" class="form-control" name="file_path[]">
							</span>
							<a href="#" class="input-group-addon btn btn-secondary fileinput-exists" data-dismiss="fileinput">Remove</a> </div>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>

