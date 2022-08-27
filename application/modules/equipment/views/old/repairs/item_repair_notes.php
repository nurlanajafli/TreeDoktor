<?php if(isset($repair->repair_notes) && !empty($repair->repair_notes)) : ?>
	<?php foreach($repair->repair_notes as $k=>$note) : ?>
		<div class="media m-t-sm">
			<div class="pull-left m-l">
				<span class="thumb-md">
					<?php if (isset($note->picture) && is_file(PICTURE_PATH . $note->picture)) : ?>
						<img src="<?php echo base_url(PICTURE_PATH) . $note->picture; ?>" class="img-circle">
					<?php else : ?>
						<img src="<?php echo base_url("assets/pictures/avatar_default.jpg"); ?>" class="img-circle">
					<?php endif; ?>
				</span>
				
			</div>
			<div class="h6 media-body p-10">
				<?php if($note->equipment_note_text) : ?>
					<div class="m-b-sm">
						<?php echo $note->equipment_note_text; ?>
					</div>
				<?php endif; ?>
				
			</div>
			
			<div class="clear"></div>
			<div class="note-author border-top">
				
<!--				Created on:&nbsp;--><?php //echo $note->equipment_note_date; ?>
				Created on:&nbsp;<?php echo getDateTimeWithDate($note->equipment_note_date, 'Y-m-d H:i:s', true); ?>
				<?php if(isset($note->note_name) && $note->note_name) : ?>
					&nbsp;by&nbsp;<?php echo $note->note_name; ?>
				<?php else: ?>
					"N/A"
				<?php endif;?>
				
				<?php if ($this->session->userdata('user_type') == "admin") : ?>
					 <a href="#" data-id="<?php echo $note->equipment_note_id;?>" class="btn btn-xs btn-danger deleteNote"><i class="fa fa-trash-o"></i></a>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach;  ?>
<?php else : ?>
	No record's found
<?php endif;  ?>
