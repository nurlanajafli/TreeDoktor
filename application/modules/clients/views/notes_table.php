<?php
    /**
    *  Deprecated!
     */
?>
<?php $notesFiles = get_client_notes_files('uploads/notes_files/' . $client_data->client_id . '/'); ?>
<?php foreach ($client_notes[$type] as $row) : ?>
	<div class="media m-t-sm">
		<div class="pull-left m-l">
			<span class="thumb-md">
				<?php if (isset($row['picture']) && $row['picture']/* && is_file(PICTURE_PATH . $row['picture'])*/) : ?>
					<img src="<?php echo base_url(PICTURE_PATH) . $row['picture']; ?>" class="img-circle">
				<?php else : ?>
					<img src="<?php echo base_url("assets/pictures/avatar_default.jpg"); ?>" class="img-circle">
				<?php endif; ?>
			</span>
		</div>

		<?php  $robot_status = ($row['robot'] != "yes")?"alert-info":"filled_dark_grey"; ?>
		
		<div class="h6 media-body p-10 client-note">
			<?php if($row['client_note']): ?>
				<div class="m-b-sm">
					<?php echo $row['client_note']; ?>
				</div>
			<?php endif; ?>
			<?php
			//if ($type == 'email' ||$type == 'attachment' || $type == 'all' || $type == 'all_client_notes'):

				$files = (isset($notesFiles[$row['client_note_id']]) && $notesFiles[$row['client_note_id']]) ? $notesFiles[$row['client_note_id']] : array();
				foreach ($files as $file): ?>
					<a target="_blank"
					   href="<?php echo base_url('uploads/notes_files/' . $row['client_id'] . '/' . $row['client_note_id'] . '/' . urlencode($file)); ?>">
						<span class="label label-success"><?php echo $file; ?></span>
					</a>
				<?php endforeach;
			//endif;
			?>
            <?php if (isset($row['email']) && is_array($row['email']) && sizeof($row['email'])): ?>
                <div class="note-email-log">
                    <?php echo generateAdditionalInfo($row['email']); ?>
                </div>
            <?php endif; ?>
		</div>
		
		<div class="clear"></div>
		<div class="note-author border-top <?php echo $robot_status; ?>">
			<form data-type="ajax" data-location="<?php echo current_url(); ?>" data-url="<?php echo base_url('clients/ajax_top_note'); ?>" class="inline pull-left">
				<div class="checkbox m-t-xs m-b-none m-l p-n" style="width: 65px;">
					<label class="checkbox-custom">
						<input type="checkbox"<?php if($row['client_note_top']) : ?> checked="checked"<?php endif; ?> name="note_top" value="1" onchange="$(this).parents('form:first').submit();">
						<input type="hidden" name="note_id" value="<?php echo $row['client_note_id']; ?>">
						<i class="fa fa-fw text-danger fa-square-o<?php if($row['client_note_top']) : ?> checked important-note<?php endif; ?>"></i>
						<span<?php if($row['client_note_top']) : ?> class="text-danger"<?php endif; ?>>Important</span>
					</label>
				</div>
			</form>

<!--			Created on:&nbsp;--><?php //echo $row['client_note_date']; ?>
			Created on:&nbsp;<?php echo getDateTimeWithDate($row['client_note_date'],'Y-m-d H:i:s', true) ?>
			&nbsp;by&nbsp;<?php echo $row['firstname'] . " " . $row['lastname']; ?>
			<?php if ($this->session->userdata('user_type') == "admin")
				echo anchor('clients/delete_note/' . $row['client_note_id'], '<i class="fa fa-trash-o"></i>', 'class="btn btn-xs btn-danger"');
			?>
		</div>
	</div>
<?php endforeach; ?>
