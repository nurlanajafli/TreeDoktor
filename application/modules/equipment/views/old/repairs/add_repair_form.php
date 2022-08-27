<?php if(isset($row['item_id'])) : ?>
	<?php $item[0] = (object)$row;?>
<?php endif; ?>

<div id="addRepair<?php echo isset($row['item_id']) ? '-'.$row['item_id'] : ''; ?>" class="modal fade" tabindex="-1"
	 role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<header class="panel-heading">
				Repair Request<br><br>
				Reported by 
				<?php echo($this->session->userdata('firstname')); ?>&nbsp;<?php echo($this->session->userdata('lastname')); ?><br>
<!--				--><?php //echo date('Y-m-d H:i:s');?>
				<?php echo date(getDateFormat() . ' ' . getTimeFormat());?>
			</header>
			<form id="new_repair" method="post" action="<?php echo base_url('equipments/new_repair'); ?>">
			<!-- Client Files Header-->
				<div class="modal-body">
					<div class="p-10">
						<div class="control-group">
							<?php if(isset($item[0]->item_name)) : ?>
								<?php $select = 'selected="selected"';
								?>
							<?php endif; ?>
							<label class="control-label">Item Group:</label>
							<div class="control-group">
								<select class="form-control group" name="group">
									<?php foreach($groups as $k=>$v) : ?>
										<option <?php if(isset($select) && $v->group_id == $item[0]->group_id) : ?><?php echo $select; ?><?php endif; ?> value="<?php echo $v->group_id?>"><?php echo $v->group_name; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div class="p-10">
						<div class="control-group">
							<label class="control-label">Item Name:</label>
							<div class="control-group">
								<select class="form-control item" name="item">
									<?php if(isset($item[0]->group_id)) : ?>
										<?php foreach($items[$item[0]->group_id] as $k=>$v) : ?>
											<option <?php if($v['item_id'] == $item[0]->item_id) : ?>selected="selected"<?php endif; ?> value="<?php echo $v['item_id']; ?>"><?php echo $v['item_name']; ?></option>
										<?php endforeach; ?>
									<?php elseif($items && !empty($items) && $groups[0]->group_id && $items[$groups[0]->group_id]) : ?>
										<?php foreach($items[$groups[0]->group_id] as $k=>$v) : ?>
											<option value="<?php echo $v['item_id']; ?>"><?php echo $v['item_name']; ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</div>
						</div>
					</div>
					<div class="p-10">
						<div class="control-group">
							<label class="control-label">Describe the issue in details(Min 5 symbols):</label>
							<div class="control-group">
								<textarea name="comment" class="form-control" onkeyup="countChar(this);" placeholder="Comment"></textarea>
							</div>
						</div>
					</div>
					<div class="p-10">
						<div class="control-group">
							<label class="control-label">Priority:</label>
							<div class="radio inline m-l">
								<label>
									<input name="priority" type="radio" checked="checked" value="1" />1
								</label>
							</div>
							<div class="radio inline m-l">
								<label>
									<input name="priority" type="radio" value="2" />2
								</label>
							</div>
							<div class="radio inline m-l">
								<label>
									<input name="priority" type="radio" value="3" />3
								</label>
							</div>
							<div class="radio inline m-l">
								<label>
									<input name="priority" type="radio" value="4" />4
								</label>
							</div>
						</div>
					</div>
					<div class="p-10">
						<div class="control-group">
							<label class="control-label">Type:</label>
							<div class="radio inline m-l">
								<label>
									<input name="type" type="radio"  checked="checked" value="damage" />Damage
								</label>
							</div>
							<div class="radio inline m-l">
								<label>
									<input name="type" type="radio" value="repair" />Repair
								</label>
							</div>
							<div class="radio inline m-l">
								<label>
									<input name="type" type="radio" value="maintenance" />Maintenance
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn cancelRepair" data-dismiss="modal" aria-hidden="true">Cancel</button>
					<input type="hidden" class="btn btn-info" name="item_id" value="<?php echo isset($item[0]->item_id) ? $item[0]->item_id : ''; ?>">
					<input type="submit" class="btn btn-info repairSubmit" value="Send" disabled="disabled">
									
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	var items = <?php echo json_encode($items); ?>;
	 function countChar(val) {
        var len = val.value.length;
        
        if (len < 5) {
          $(val).parent().addClass('has-error');
          $(val).parents('#new_repair:first').find('.repairSubmit').attr('disabled', 'disabled');
			return false;
        }
        else
        {
			$(val).parent().removeClass('has-error');
			$(val).parents('#new_repair:first').find('.repairSubmit').removeAttr('disabled', 'disabled');
			return false;
		}
      };
	$(document).on('change', '.changeNewStatus', function () {
		var obj = $(this);
		var status = $(obj).val();
		if(status == 'repaired')
			$(this).closest('.modal-body').find('.soldInputs').css('display', 'block');
		else
			$(this).closest('.modal-body').find('.soldInputs').css('display', 'none');
		return false;
	});
	$(document).on('change', '.group', function () {
		
		var obj = $(this);
		var group = $(obj).val();
		var itEl = $(this).closest('.modal-body').find('.item');
		var newIt = '<select class="form-control item" name="item">';
		$.each($(items[group]), function(key, val){
			newIt += '<option value="' + val.item_id + '">' + val.item_name + '</option>';
		})
		newIt += '</select>';
		$(itEl).replaceWith(newIt);
		return false;
	});
	<?php if($this->uri->segment(1) == 'equipments') : ?>
	$(document).on('click', '.submit', function () {
		var obj = $(this);
		var text = $(obj).parent().parent().find('[name="comment"]').val().length;
		
		if(text)
			$(obj).parent().parent().submit();
		else
			$(obj).parent().parent().find('[name="comment"]').parent().addClass('has-error');
		return false;
		
	});
	<?php elseif($this->uri->segment(1) == 'dashboard') : ?>
	$(document).on('click', '.repairSubmit', function () {
		var data = $('#new_repair').serialize();
		$.post('equipments/new_repair', data, function(resp) {
			if(resp.status == 'ok')
			{
				if(confirm('Thank you for feeling in the repair request. Do you have more requests?')) {
					$('#addRepair').modal('show');
					$('#new_repair')[0].reset();
					var itEl = $('#new_repair').find('.item');
					var groupId = $('select.form-control.group option:first').attr('value');
					var newIt = '<select class="form-control item" name="item">';
					$.each($(items[groupId]), function(key, val){
						newIt += '<option value="' + val.item_id + '">' + val.item_name + '</option>';
					})
					newIt += '</select>';
					$(itEl).replaceWith(newIt);
				}
				else
				{
					$('#addRepair').modal('hide');
					//$('#running-button').click();
				}
			}
		}, 'json');
		return false;
		
	});
	/*$(document).on('click', '.cancelRepair', function () {
		$('#running-button').click();
	});*/
	
	<?php endif; ?>
</script>
