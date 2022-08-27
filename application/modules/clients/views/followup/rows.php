<?php
foreach ($fu_list as $key => $item) : ?>
	<tr data-id="<?php echo $item->fu_id; ?>"<?php if($item->fu_date < date('Y-m-d') && $item->fu_status == 'new') : ?> class="bg-warning"<?php endif; ?>>
		<?php $variables = $item->fu_variables ? json_decode($item->fu_variables, TRUE) : []; ?>
		<td class="text-center">
			<?php echo isset($start) ? $start + ($key + 1) : ($key + 1);  ?>
		</td>
		<td>
			<a href="<?php echo base_url('client/' . $item->fu_client_id); ?>" target="_blank">
				<?php echo $item->cc_name; ?>
			</a>
		</td>
		<td class="text-center">
			<?php if($item->cc_phone) :  ?>
				<a href="#" class="createCall" data-client-id="<?php echo $item->fu_client_id; ?>" data-number="<?php echo $item->cc_phone; ?>">
					<?php echo numberTo($item->cc_phone); ?>
				</a>
			<?php else : ?>
				—
			<?php endif; ?>
		</td>
		<td class="text-center">
			<?php echo ($item->estimator_firstname && $item->estimator_lastname != 'system') ? $item->estimator_firstname . ' ' . $item->estimator_lastname : '—'; ?>
		</td>
		<td class="text-center">
			<?php $variables = $item->fu_variables ? json_decode($item->fu_variables, TRUE) : []; ?>
			<a href="<?php echo isset($variables['NO']) ? base_url($variables['NO']) : base_url($item->fu_module_name . '/' . $item->fu_action_name . '/' . $item->fu_item_id); ?>" target="_blank">
				<?php //echo ucfirst($item->fu_module_name); ?>
				
				<?php echo isset($variables['NO']) ? $variables['NO'] : ucfirst($item->fu_module_name); ?>
			</a>
		</td>
		<td class="text-center">
			<?php echo (isset($variables['TOTAL_DUE']) && $variables['TOTAL_DUE']) ? money(getAmount($variables['TOTAL_DUE'])) : '—'; ?>
		</td>
		<td class="text-center">
			<?php $source = []; $source['reasons'] = $config_modules[$item->fu_module_name]['reasons']; ?>
			<?php foreach ($config_modules[$item->fu_module_name]['statuses'] as $key => $value) {
					$source['statuses'][] = ['value' => $key, 'text' => $value];
			}  ?>
			<a href="#"
               data-pk="<?php echo $item->fu_id; ?>"
               data-client_unsubscribed="<?php echo $item->client_unsubsribed; ?>"
               data-client_name="<?php echo $item->cc_name; ?>"
               data-payment_driver="<?php echo $item->client_payment_driver?: config_item('payment_default'); ?>"
               data-source="<?php echo htmlentities(json_encode($source), ENT_QUOTES, 'UTF-8'); ?>"
               data-value="<?php echo $item->status_value; ?>"
               data-name="<?php echo $item->fu_module_name; ?>"
               data-placement="left"
               data-estimate_id="<?php echo $item->estimate_id; ?>"
               data-fu_item_id="<?php echo $item->fu_item_id; ?>"
               data-client_id="<?php echo $item->fu_client_id; ?>"
               data-estimate_balance="<?php echo $item->estimate_balance; ?>"
               data-type="<?php echo $item->fu_module_name; ?>_status"
               class="fu_item_status_<?php echo $item->fu_module_name; ?>"
               title="Change <?php echo ucfirst(rtrim($item->fu_module_name, 's')); ?> Status"
               data-url="<?php echo base_url('clients/ajax_update_followup_item_status'); ?>">
				<?php echo $item->status_name; ?>
			</a>
		</td>
		<td class="text-center">
			<?php echo ucfirst(str_replace('_', ' ', $item->fs_type)); ?>
		</td>
		<td class="text-center">

			<?php $statusData = [
					'fu_comment' => $item->fu_comment,
					'fu_status' => $item->fu_status,
					'fu_date' => $item->fu_date
				]; ?>
			<a href="#" data-pk="<?php echo $item->fu_id; ?>" data-source="[{value: 'new', text: 'New'}, {value: 'completed', text: 'Completed'}, {value: 'postponed', text: 'Postponed'}, {value: 'canceled', text: 'Canceled'}]" data-name="fu_status" data-value="<?php echo htmlentities(json_encode($statusData), ENT_QUOTES, 'UTF-8'); ?>" data-placement="left" data-type="followup_status" class="fu_status" title="Change Status" data-url="<?php echo base_url('clients/ajax_update_followup_status'); ?>"></a>
		</td>
		<td class="text-center">
<!--			--><?php //echo $item->fu_date; ?>
			<?php echo getDateTimeWithDate($item->fu_date, 'Y-m-d')  ?>
		</td>
		<td class="text-center">
			<?php echo $item->fs_every ? 'Every' : 'After'; ?>
			<strong><?php echo $item->fs_periodicity; ?></strong> 
			<?php //echo $item->fs_periodicity > 1 ? 'Days' : 'Day'; ?>
		</td>
		<td class="text-center">
			<?php if($item->fs_template) : ?>

				<?php $keys = array_map(function($value) { return '[' . $value . ']'; }, array_keys($variables)); ?>
				<?php $values = array_values($variables); ?>

				<span class="badge"" data-toggle="tooltip" data-placement="left" title="" data-html="true" data-original-title="<?php echo strip_tags(str_replace($keys, $values, $item->fs_template)); ?>">
					<i class="fa fa-info"></i>
				</span>
			<?php else : ?>
				—
			<?php endif; ?>
		</td>
		<td class="text-center">
			<?php if($item->firstname) echo $item->firstname . ' ' . $item->lastname; elseif ($item->fs_cron) echo 'system'; else echo '—'; ?>
		</td>
		<td class="text-center notes" width="200px">
			<span style="white-space: nowrap;text-overflow: ellipsis;max-width: 200px;display: inline-block;overflow: hidden;" data-toggle="tooltip" data-placement="left" title="" data-html="true" data-original-title='<?php echo htmlentities($item->fu_comment, ENT_QUOTES, 'UTF-8'); ?>'>
				<a href="#" data-pk="<?php echo $item->fu_id; ?>" style="white-space: nowrap;text-overflow: ellipsis;max-width: 200px;display: inline-block;overflow: hidden;"  data-name="fu_comment" data-value='<?php echo htmlentities($item->fu_comment, ENT_QUOTES, 'UTF-8'); ?>' data-placement="left" data-type="textarea" class="fu_comment" title="Notes" data-url="<?php echo base_url('clients/ajax_update_followup_field'); ?>"></a>
				<?php //echo htmlentities($item->fu_comment, ENT_QUOTES, 'UTF-8'); ?>
			</span>
		</td>
		<td class="text-center">
			<?php if($item->fu_status == 'new') : ?>
			<label>
				<input type="checkbox" class="toComplete" data-pk="<?php echo $item->fu_id; ?>"<?php if(!trim($item->fu_comment)) echo " disabled"; ?>>
			</label>
			<?php else : ?>
				<i class="fa fa-times"></i>
			<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
