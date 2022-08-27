<?php $num = isset($num) && $num ? $num : 1; ?>
<?php $data = $this->input->post();  

//echo "<pre>";
if(isset($doc)){
	//var_dump($doc);
	//die;
}
	
?>

	<div class="col-md-12 col-sm-12 col-lg-4 certificate-section">
	<div class="panel panel-default p-n m-md-bottom-n m-lg-bottom-15">
		<header class="panel-heading"> 
			<span class="doc_title">
				Document #<?php echo $num; ?>
			</span>
			<div class="pull-right">
                <a class="btn btn-xs btn-danger btn-rounded delete-doc" style="line-height: 17px; <?= $num == 1 ? 'display:none' : ''?>">
                    <i class="fa fa-minus"></i>
                </a>

                <a class="btn btn-xs btn-success btn-rounded add-doc" style="line-height: 17px;">
					<i class="fa fa-plus"></i>
				</a>
			</div>
		</header>
		<table class="table m-n profile-table">
			<tr>
				<td>
					<label class="control-label">Name:</label>
					<input type="text" name="us_name[]" class="form-control" value="<?php echo isset($doc->us_name) && $doc->us_name ? $doc->us_name : ''; ?>">
				</td>
				<td style="width: 20%;">
					<label class="control-label">Exp:</label>
					<input type="text" name="us_exp[]" data-date-format ="yyyy-mm-dd"
					class="form-control doc_exp datepicker" value="<?php echo isset($doc->us_exp) && $doc->us_exp ? $doc->us_exp : ''; ?>">
				</td>
				<td style="width: 5%;">
					<label class="control-label">Notification</label>
					<div class="form-group">
						<select name="us_notification[]" class="form-control">
							<option value="0" <?php echo !isset($doc->us_notification) || $doc->us_notification == 0 ? 'selected="selected"' : ''; ?> >No</option>
							<option value="1" <?php echo isset($doc->us_notification) && $doc->us_notification == 1 ? 'selected="selected"' : ''; ?> >Yes</option>
						</select>
					</div>
				</td>
			</tr>
			
			<tr> 
				<td colspan="3">
					<label class="control-label">File:</label>
					<?php if(isset($doc->us_photo)) : ?>
						<span class="pull-right">
							<a href="<?php echo base_url() . 'uploads/user_docs/' . $user_row->id . '/' . $doc->us_photo; ?>" target="_blank" ><?php echo $doc->us_photo; ?></a>
						</span>
						<input type="hidden" name="us_id[]" value="<?php echo $doc->us_id; ?>">
						<div class="clear"></div>
					<?php endif; ?>
					<div class="">
						<div class="fileinput fileinput-new input-group w-100" data-provides="fileinput" style="border: 1px solid #d9d9d9;">
							<span class=" btn btn-secondary btn-file p-n w-100" > 
								<input type="file" class="form-control" name="us_photo[]">
							</span>
							<a href="#" class="input-group-addon btn btn-secondary fileinput-exists" data-dismiss="fileinput">Remove</a> </div>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>

