<?php
    /**
     *  Deprecated!
     */
?>
<!--timeline -->
<section class="panel panel-default p-n">
	<div id="nodesTabs" style="padding: 15px 15px 0 15px;">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#all">All Notes</a></li>
			<li><a href="#info">Info</a></li>
			<!--<li><a href="#contact">Contact</a></li>-->
			<li><a href="#attachment">Attachment</a></li>
			<li><a href="#system">System</a></li>
            <?php if(config_item('phone')) : ?>
			<li><a href="#calls">Calls</a></li>
            <?php endif; ?>
            <?php if(config_item('messenger')) : ?>
			<li><a href="#sms">Sms</a></li>
            <?php endif; ?>
			<li><a href="#email">Email</a></li>
			<?php if ($this->router->fetch_class() != 'clients') : ?>
				<li><a href="#all_client_notes">All Client Notes</a></li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="tab-content" id="">
		<?php foreach ($types as $key => $type) : ?>
			<div class="tab-pane<?php if (!$key) : ?> active<?php endif; ?>" id="<?php echo $type ?>">
				<?php if($client_notes[$type] && $type == 'calls') : ?>
				<?php $client_info = isset($client_info) ? $client_info : $client_data; ?>
					<?php $this->load->view('call_sms_notes', ['client_notes' => $client_notes[$type], 'client_info' => (array) $client_info]);?>
					<?php if($client_notes[$type . '_more']) : ?>
						<div class="text-center">
							<a href="#" class="getMore getMore<?php echo $type; ?>"  data-num="<?php echo $client_notes[$type . '_count']?>" data-type="<?php echo $type;?>" data-id="<?php echo $client_data->client_id?>">Show More</a>
						</div>
					<?php endif; ?>
				<?php elseif($client_notes[$type] && $type == 'sms') : ?>
					<?php $client_info = isset($client_info) ? $client_info : $client_data; ?>
					<div class="sms_notes">
						<div class="messages-wrapper">
							<?php $this->load->view('sms_notes', ['client_notes' => $client_notes[$type], 'client_info' => (array) $client_info]);?>
						</div>
					</div>
						<?php if($client_notes[$type . '_more']) : ?>
							<div class="text-center">
								<a href="#" class="getMore getMore<?php echo $type; ?>"  data-num="<?php echo $client_notes[$type . '_count']?>" data-type="<?php echo $type;?>" data-id="<?php echo $client_data->client_id?>">Show More</a>
							</div>
						<?php endif; ?>
				<?php elseif($client_notes[$type]) : ?>
					<?php $this->load->view('notes_table', array('client_notes' => $client_notes, 'type' => $type)); ?>
					<?php if($client_notes[$type . '_more']) : ?>
					<div class="text-center">
						<a href="#" class="getMore getMore<?php echo $type; ?>" data-lead_id="<?php echo isset($estimate_data->lead_id) ? $estimate_data->lead_id : 0; ?>" data-num="<?php echo $client_notes[$type . '_count']?>" data-type="<?php echo $type;?>" data-id="<?php echo $client_data->client_id?>">Show More</a>
					</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="client_note filled_white rounded shadow overflow">
						<div class="corner"></div>
						<div class="p-20">
							No record found
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
		<?php /* if ($this->uri->segment(1) != 'clients') : ?>
			<div class="tab-pane" id="allClientNotes">
				<div class="client_note filled_white rounded shadow overflow">
					<div class="corner"></div>
					<div class="p-20">
						No record found
					</div>
				</div>
			</div>
		<?php endif; */ ?>
	</div>
</section>
<script>
	$(document).ready(function () {
		soundManager.reboot();

		$('#nodesTabs .nav.nav-tabs a').click(function() {

			if ($(this).parent().is('.active'))
				return false;

			var selector = $('#nodesTabs .nav.nav-tabs .active a').attr('href');
			$('#nodesTabs .nav.nav-tabs a').parent('.active').removeClass('active');
			$(selector + '.tab-pane.active').removeClass('active');
			selector = $(this).attr('href');
			$(selector + '.tab-pane').addClass('active');
			$(this).parent().addClass('active');
			history.pushState(null, null, selector);

			return false;
		});
		$(".tab-pane").on("click", ".getMore", function() {
			var num = $(this).attr('data-num');
			var type = $(this).data('type');
			var id = $(this).data('id');
			var lead_id = $(this).data('lead_id');
			$.ajax({
				global: false,
				method: "POST",
				data: {num:num, type:type, id:id, lead_id:lead_id},
				url: base_url + "clients/ajax_more_notes",
				dataType:'json',
				success: function(response){
					
					if (response.status != 'ok')
					{
						errorMessage('Sorry. Client has not any notes more');
						$('#' + type).find('.getMore' + type).remove(); 
					}
					else
					{
						if(type == 'sms')
							$('#' + type + ' .sms_notes .message-row:last').after(response.table);
						else
							$('#' + type + ' .media.m-t-sm:last').after(response.table);
						if(response.more)
							$('#' + type).find('.getMore' + type).attr('data-num', response.offset);
						else
							$('#' + type).find('.getMore' + type).remove();
					}
				}
			});
			return false;
		});
	});
	
	
</script>
