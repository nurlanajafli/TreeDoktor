<?php

if(!$row['stump_assigned'])
	$statuses[1]['disabled'] = true;
if(!$row['stump_clean_id'])
	$statuses[2]['disabled'] = true;
if($row['stump_status'] != 'grinded')
	$statuses[2]['disabled'] = true;

$marker_style = NULL;
$marker_date['id'] = $row['stump_id'];
$estimator_id = $row['stump_assigned'];
if(!$this->input->post('map'))
	$note = str_replace(["\r","\n","\r\n"], '\r\n', $row['stump_contractor_notes']);
else
	$note = $row['stump_contractor_notes'];
?>
<div class="infowindow">
	<strong data-user="<?php echo $estimator_id; ?>"></strong>
	<div class="form-inline">
		<label>#: </label>
		<strong>
			<?php if($this->session->userdata('STP') != 3) : ?>
				<a href="#" data-name="stump_status_work" data-value="<?php echo $row['stump_status_work']; ?>" data-placement="right" data-type="select" data-source="[{value:'0',text:'No Locates Yet'},{value:'1',text:'Locates Below'},{value:'2',text:'Clean To Dig'}]" data-pk="<?php echo $row['stump_id']; ?>" class="status_work" title="Status Work" data-url="<?php echo base_url("stumps/ajax_update_stump"); ?>">
			<?php endif; ?>
				<?php echo $row['stump_unique_id']; ?>
			<?php if($this->session->userdata('STP') != 3) : ?>
				</a>
			<?php endif; ?>

		</strong>
	</div>

	<div class="form-inline">
		<label>Address: </label>
		<strong>
			<?php if($this->session->userdata('STP') != 3) : ?>
				<a href="#" id="address" style="font-size: 26px;" data-name="stump_address" data-value="{stump_address:'<?php echo ($row['stump_house_number'] != NULL && $row['stump_house_number'] != '') ? $row['stump_house_number'] . ' ' : ''; echo addslashes($row['stump_address']); ?>',stump_city:'<?php echo addslashes($row['stump_city']); ?>',stump_state:'<?php echo addslashes($row['stump_state']); ?>',stump_lat:'<?php echo $row['stump_lat']; ?>',stump_lon:'<?php echo $row['stump_lon']; ?>'}" data-placement="right" data-type="address" data-pk="<?php echo $row['stump_id']?>" class="stump_address" title="Stump Address" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">
			<?php endif; ?>
				<?php echo ($row['stump_house_number'] != NULL && $row['stump_house_number'] != '') ? $row['stump_house_number']  . ' ' : ''; echo $row['stump_address']; ?>
			<?php if($this->session->userdata('STP') != 3) : ?>
				</a>
			<?php endif; ?>
		</strong>
	</div>

	<div class="form-inline">
		<label>Size: </label>
			<?php echo $row['stump_range']; ?>
	</div>

	<div class="form-inline">
		<label>Locates: </label>
		<?php if($this->session->userdata('STP') != 3) : ?>
			<a href="#" data-name="stump_locates" data-value="<?php echo $row['stump_locates']; ?>" data-placement="left" data-type="text" data-pk="<?php echo $row['stump_id']; ?>" class="stump_locates" title="Locates" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">
		<?php endif; ?>
			<?php echo $row['stump_locates']; ?>
		<?php if($this->session->userdata('STP') != 3) : ?>			
			</a>
		<?php endif; ?>
	</div>

	<div class="form-inline">
		<label class="pull-left">Location: </label> 
		
		<span class="block pull-left">&nbsp;<?php echo str_replace(array("\n", "\r\n"), "<br>", $row['stump_side'] . ' ' . $row['stump_desc']);?></span>
	</div>
	<div class="clear"></div>
	<div class="form-inline">
		<label class="pull-left">Notes: </label> 
		<?php if($this->session->userdata('STP') != 3) : ?>
			<a href="#" class="contractor_notes block pull-left" data-name="stump_contractor_notes" data-value="<?php echo addslashes($note); ?>" data-placement="right" data-type="textarea" data-pk="<?php echo $row['stump_id']; ?>" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>" data-original-title="Enter Note">
		<?php endif; ?>
			<?php if($row['stump_contractor_notes'])
				    echo str_replace(array("\n", "\r\n"), "<br>", $row['stump_contractor_notes']);
			?>
		<?php if($this->session->userdata('STP') != 3) : ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="form-inline clear">
		<label>Status: </label>
		<?php if($this->session->userdata('STP') != 3) : ?>
			<a href="#" data-name="stump_status" data-placement="left" data-type="stump_status" data-source='<?php echo json_encode(['statuses'=>$statuses, 'users'=>$users]); ?>' data-pk="<?php echo $row['stump_id']; ?>" data-value="{'stump_status':'<?php echo $row['stump_status']; ?>', 'stump_assigned':'<?php echo $row['stump_assigned']; ?>', 'stump_removal':'<?php echo $row['stump_removal'] ? $row['stump_removal'] : date('Y-m-d H:i'); ?>', 'stump_cleaned':'<?php echo $row['stump_clean_id']; ?>', 'stump_cleaned_date':'<?php echo $row['stump_clean'] ? $row['stump_clean'] : date('Y-m-d H:i'); ?>', 'stump_last_status_changed':'<?php echo date('m/d '. getPHPTimeFormatWithOutSeconds(), strtotime($row['stump_last_status_changed'])); ?>', 'stump_map':'1'}" class="status" title="Status" data-url="<?php echo base_url('stumps/change_status'); ?>"></a>
		<?php else : ?>
			<?php echo ucfirst($row['stump_status']); ?><?php echo date('m/d '. getPHPTimeFormatWithOutSeconds(), strtotime($row['stump_last_status_changed'])); ?>
		<?php endif; ?>
	</div>

	<div class="form-inline">
		<label>Assigned to grind: </label>
		<?php if($this->session->userdata('STP') != 3) : ?>
		<a <?php if($this->session->userdata('user_type') != "admin" && $this->session->userdata('STA') == 0) echo 'style="pointer-events: none;"'; ?> href='#' data-name='stump_assigned' data-placement='left' data-type='select' data-source='<?php echo json_encode($users); ?>' data-value="<?php echo $row['stump_assigned']; ?>" class="assigned" title="Grind Crew" data-pk="<?php echo $row['stump_id']; ?>" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>"></a>
		<?php //<div class="inline stump_removal">?>
			<?php if($row['stump_removal']) : ?>
				<a href="#" data-name="stump_removal" data-value="<?php echo $row['stump_removal']; ?>" data-placement="left" data-type="date" data-pk="<?php echo $row['stump_id']?>" class="stump_clean" title="Grind Date" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">
					<?php echo $row['stump_removal'] ? ' (' . date('m/d ' . getPHPTimeFormatWithOutSeconds(), strtotime($row['stump_removal'])) . ')' : ''; ?>
				</a>
			<?php endif; ?>
		<?php //</div>?>
		<?php else : ?>
			<?php 
			echo $row['gfirstname'] ? $row['gfirstname'] . ' ' . $row['glastname'] : '';
			echo $row['stump_removal'] ? ' (' . date('m/d '. getPHPTimeFormatWithOutSeconds(), strtotime($row['stump_removal'])) . ')' : ''; ?>
		<?php endif; ?>
	</div>


	<div class="form-inline">
		<label>Assigned to clean: </label>
		<?php if($this->session->userdata('STP') != 3) : ?>
		<a <?php if($this->session->userdata('user_type') != "admin" && $this->session->userdata('STA') == 0) echo 'style="pointer-events: none;"'; ?> href='#' data-name='stump_clean_id' data-placement='left' data-type='select' data-source='<?php echo json_encode($users); ?>' data-value="<?php echo $row['stump_clean_id']; ?>" class="clean_assigned" title="Clean Crew" data-pk="<?php echo $row['stump_id']; ?>" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>"></a>
		<?php //<div class="inline stump_clean">?>
			<?php if($row['stump_clean']) : ?>
				<a href="#" data-name="stump_clean" data-value="<?php echo $row['stump_clean']; ?>" data-placement="left" data-type="date" data-pk="<?php echo $row['stump_id']?>" class="stump_clean" title="Clean Date" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">
					<?php echo $row['stump_clean'] ? ' (' . date('m/d ' . getPHPTimeFormatWithOutSeconds(), strtotime($row['stump_clean'])) . ')' : ''; ?>
				</a>
			<?php endif; ?>
		<?php //</div>?>
		<?php else : ?>
			<?php echo $row['stump_clean'] ? $row['cfirstname'] . ' ' . $row['clastname'] . ' (' . date('m/d ' . getPHPTimeFormatWithOutSeconds(), strtotime($row['stump_clean'])) . ')' : '-'; ?>
		<?php endif; ?>
	</div>
</div>
