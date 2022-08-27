<?php $this->load->view('equipments/services/service_settings_modal', array('new_modal' => true, 'item_id' => $item_id)); ?>
	<table class="table table-hover">
		<thead>
			<tr>
				<th>#</th>
				<th>Service Type</th>
				<th>Periodicity(Month)</th>
				<?php //<th>Periodicity(Hours)</th> ?>
				<th>Periodicity(Counter)</th>
				<th width="100px" style="text-align:center;">
					<a href="#add_service_setting" class="btn btn-success btn-xs" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">
						<i class="fa fa-plus"></i>
					</a>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($service_settings) && !empty($service_settings)) : ?>
				<?php foreach($service_settings as $key => $setting) : ?>
					<tr>
						<td><?php echo ($key + 1); ?></td>
						<td><?php echo $setting['equipment_service_type']; ?></td>
						<td>
							<?php echo $setting['service_period_months'] ? $setting['service_period_months'] . ' Months' : ''; ?>
						</td>
						<?php /*<td>
							<?php echo $setting['service_period_hours'] ? $setting['service_period_hours'] . ' Hours' : ''; ?>
						</td>*/ ?>
						<td>
							<?php echo $setting['service_period_kilometers'] ? $setting['service_period_kilometers'] : ''; ?>
						</td>
						<td class="text-center">
							<a href="#edit_service_setting_<?php echo $setting['id']; ?>" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true" class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>
							<form data-type="ajax" method="POST" data-url="<?php echo base_url('equipments/ajax_delete_service_settings'); ?>" data-location="<?php echo current_url(); ?>" style="display: inline-block;">
								<input type="hidden" value="<?php echo $setting['id']; ?>" name="id"/>
								<button type="submit" class="btn btn-xs btn-danger" onclick="if(confirm('Are you sure?')) return true; else return false;">
									<i class="fa fa-trash-o"></i>
								</button>
							</form>
							<?php $setting['new_modal'] = false; ?>
							<?php $this->load->view('equipments/services/service_settings_modal', $setting); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>


	<script>
		$(document).ready(function(){
			$('.periodBy').change(function(){
				$('.periodicityControl').slideUp();
				$('.' + $(this).data('id')).slideDown();
				return false;
			});
		});
	</script>
