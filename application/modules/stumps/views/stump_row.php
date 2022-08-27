	<?php
			$users[] = ['value' => 0, 'text' => 'N/A'];
			foreach ($active_users as $active_user)
				$users[] = ['value' => $active_user->id, 'text' => $active_user->firstname . " " . $active_user->lastname];
			?>
			<?php foreach ($stumps as $key=>$val) : ?>
				<?php $rowFullData = json_decode($val['stump_data']); ?>
				<?php $statusesMenu = $statusesSelect; ?>
				<?php //$statuses = [['value' => 'new', 'text' => 'New'],['value' => 'grinded', 'text' => 'Grinded'],['value' => 'cleaned_up', 'text' => 'Cleaned Up'], ['value' => 'skipped', 'text' => 'Skipped'], ['value' => 'canceled', 'text' => 'Canceled']]; ?>
				<tr data-new="ok" data-id="<?php echo $val['stump_id']?>" style="color:#000;background-color:<?php if($val['stump_status_work'] == 0) : ?>#FFFFFF<?php elseif($val['stump_status_work'] == 1) : ?>#95B3D7<?php elseif($val['stump_status_work'] == 2) : ?>#C3D69B<?php endif; ?>">
					<td class="b-l b-r text-center p-n b-b">
						<?php if($this->session->userdata('STP') != 3) : ?>
							<a href="#" data-name="stump_status_work" data-value="<?php echo $val['stump_status_work']; ?>" data-placement="right" data-type="select" data-source="[{value:'0',text:'No Locates Yet'},{value:'1',text:'Locates Below'},{value:'2',text:'Clean To Dig'}]" data-pk="<?php echo $val['stump_id']?>" class="status_work" title="Status Work" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>"><?php echo $val['stump_unique_id']; ?></a>
						<?php else : ?>
							<?php echo $val['stump_unique_id']; ?>
						<?php endif; ?>
						<?php if($this->session->userdata('user_type') == "admin") : ?>
							<br>
							<a class="deleteStump btn btn-danger btn-xs print-btns" style="padding: 0 5px;margin-top: 5px;">
								<i class="fa fa-trash-o"></i>
							</a>
						<?php endif; ?>
					</td>
					<td class="text-center b-r b-b"><?php echo $val['stump_map_grid']; ?></td>
					
					<td class="b-r b-b">
						<?php if($this->session->userdata('STP') != 3) : ?>
							<a href="#" id="address" data-name="stump_address" data-value="{stump_address:'<?php echo ($val['stump_house_number'] != NULL && $val['stump_house_number'] != '') ? $val['stump_house_number'] . ' ' : ''; echo addslashes($val['stump_address']); ?>',stump_city:'<?php echo addslashes($val['stump_city']); ?>',stump_state:'<?php echo addslashes($val['stump_state']); ?>',stump_lat:'<?php echo $val['stump_lat']; ?>',stump_lon:'<?php echo $val['stump_lon']; ?>'}" data-placement="right" data-type="address" data-pk="<?php echo $val['stump_id']?>" class="stump_address" title="Stump Address" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">
						<?php endif; ?>
							<?php if($val['stump_address'] != '') : ?>
								<?php echo ($val['stump_house_number'] != NULL && $val['stump_house_number'] != '') ? $val['stump_house_number'] . ' ' : ''; echo $val['stump_address']; ?>,
							<?php endif; ?>
							<?php if($val['stump_city'] != '') : ?>
								<?php echo $val['stump_city']; ?>,
							<?php endif; ?>
							<?php if($val['stump_state'] != '') : ?>
								<?php echo $val['stump_state']; ?>
							<?php endif; ?>
						<?php if($this->session->userdata('STP') != 3) : ?>
							</a>
						<?php endif; ?>
					</td>
					<td class="text-center b-r b-b"><?php echo $val['stump_side']; ?></td>
					<td class="b-r b-b text-center">
						<?php echo $val['stump_desc']; ?>
					</td>
					<?php /*<td class="b-r b-b"><?php echo $val['stump_desc']; ?></td>*/ ?>
					<td class="text-center b-r b-b editable-row">
						<?php if($this->session->userdata('STP') != 3) : ?>
							<a href="#" data-name="stump_range" data-value="<?php echo $val['stump_range']; ?>" data-placement="left" data-type="text" data-pk="<?php echo $val['stump_id']?>" class="stump_range" title="Size" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">
						<?php endif; ?>
							<?php echo $val['stump_range']; ?>
						<?php if($this->session->userdata('STP') != 3) : ?>
							</a>
						<?php endif; ?>
					</td>

					<td class="text-center b-r b-b editable-row">
						<?php if($this->session->userdata('STP') != 3) : ?>
							<a href="#" data-name="stump_locates" data-value="<?php echo $val['stump_locates']; ?>" data-placement="left" data-type="text" data-pk="<?php echo $val['stump_id']?>" class="stump_locates" title="Locates" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">
						<?php endif; ?>
							<?php echo $val['stump_locates']; ?>
						<?php if($this->session->userdata('STP') != 3) : ?>
							</a>
						<?php endif; ?>
					</td>
					<td class="b-r b-b">
						<?php if($this->session->userdata('STP') != 3) : ?>
							<a href="#" class="contractor_notes" data-value="<?php echo $val['stump_contractor_notes']; ?>" data-placement="right" data-type="textarea" data-pk="<?php echo $val['stump_id']?>" data-name="stump_contractor_notes" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>" data-original-title="Enter Note">
						<?php endif; ?>
							<?php if($val['stump_contractor_notes']) : ?>
								<?php echo $val['stump_contractor_notes']; ?>
							<?php else : ?>
								-
							<?php endif; ?>
						<?php if($this->session->userdata('STP') != 3) : ?>
							</a>
						<?php endif; ?>
					</td>

					<td class="text-center b-r b-b">
						<?php if(!$val['stump_assigned']) $statusesMenu[1]['disabled'] = true; ?>
						<?php if(!$val['stump_clean_id']) $statusesMenu[2]['disabled'] = true; ?>
						<?php if($val['stump_status'] != 'grinded') $statusesMenu[2]['disabled'] = true; ?>
						<?php if($this->session->userdata('STP') != 3) : ?>
							<a href="#" data-name="stump_status" data-placement="left" data-type="stump_status" data-source='<?php echo json_encode(['statuses'=>$statusesMenu, 'users'=>$users]); ?>' data-pk="<?php echo $val['stump_id']?>" data-value="{'stump_status':'<?php echo $val['stump_status']; ?>', 'stump_assigned':'<?php echo $val['stump_assigned']; ?>', 'stump_removal':'<?php echo $val['stump_removal'] ? getDateTimeWithDate($val['stump_removal'], 'Y-m-d H:i', true) : getDateTimeWithDate(date('Y-m-d H:i'), 'Y-m-d H:i', true); ?>', 'stump_cleaned':'<?php echo $val['stump_clean_id']; ?>', 'stump_cleaned_date':'<?php echo $val['stump_clean'] ? $val['stump_clean'] : date('Y-m-d H:i' ); ?>', 'stump_last_status_changed':'<?php echo date(getDateFormatWithOutYear() . ' ' . getPHPTimeFormatWithOutSeconds(), strtotime($val['stump_last_status_changed'])); ?>'}" class="stump_status" title="Status" data-url="<?php echo base_url('stumps/change_status'); ?>"></a>
						<?php else : ?>
                            <?php echo ucfirst($val['stump_status']); ?><?php if($val['stump_status'] == 'grinded') : ?>/Injected<?php endif; ?><br>
							<?php echo date('m/d ' . getPHPTimeFormatWithOutSeconds() . getPHPTimeFormatWithOutSeconds(), strtotime($val['stump_last_status_changed'])); ?>
						<?php endif; ?>
					</td>
					<td class="text-center b-r b-b">
						<?php if($this->session->userdata('STP') != 3) : ?>
							<a <?php if($this->session->userdata('user_type') != "admin" && $this->session->userdata('STA') == 0) : ?>
							   style="pointer-events: none;"
							   <?php endif; ?>
							href="#" data-name="stump_assigned" data-placement="left" data-type="select" data-source='<?php echo json_encode($users); ?>' data-value="<?php echo $val['stump_assigned']; ?>" class="assigned" title="Grind Crew" data-pk="<?php echo $val['stump_id']?>" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>"></a>
							<?php if($val['stump_removal']) : ?>
								<a href="#" data-name="stump_removal" data-value="<?php echo $val['stump_removal']; ?>" data-placement="left" data-type="datetime" data-pk="<?php echo $val['stump_id']?>" class="stump_removal" title="Grind Date" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">(<?php echo get_count_days($val['stump_removal']); ?>)

									<?php echo $val['stump_removal'] ? '(' . date(getDateFormatWithOutYear() . ' ' . getPHPTimeFormatWithOutSeconds(), strtotime($val['stump_removal'])) . ')' : ''; ?>
								</a>
							<?php endif; ?>
						<?php else : ?>
							<?php 
							echo $val['gfirstname'] ? $val['gfirstname'] . ' ' . $val['glastname'] : '';
							echo $val['stump_removal'] ? ' (' . date('m/d ' . getPHPTimeFormatWithOutSeconds(), strtotime($val['stump_removal'])) . ')' : '';
							?>
						<?php endif; ?>
					</td>
					<td class="text-center b-r b-b editable-row">
						<?php if($this->session->userdata('STP') != 3) : ?>
							<a <?php if($this->session->userdata('user_type') != "admin" && $this->session->userdata('STA') == 0) : ?>
							   style="pointer-events: none;"
							   <?php endif; ?>
							href="#" data-name="stump_clean_id" data-placement="left" data-type="select" data-source='<?php echo json_encode($users); ?>' data-value="<?php echo $val['stump_clean_id']; ?>" class="clean_assigned" title="Clean Crew" data-pk="<?php echo $val['stump_id']?>" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>"></a>
							<?php if($val['stump_clean']) : ?>
								<a href="#" data-name="stump_clean" data-value="<?php echo $val['stump_clean']; ?>" data-placement="left" data-type="date" data-pk="<?php echo $val['stump_id']?>" class="stump_clean" title="Clean Date" data-url="<?php echo base_url('stumps/ajax_update_stump'); ?>">
									(<?php echo get_count_days($val['stump_clean']); ?>)
									<?php echo $val['stump_clean'] ? '(' . date('m/d ' . getPHPTimeFormatWithOutSeconds(), strtotime($val['stump_clean'])) . ')' : ''; ?>
								</a>
							<?php endif; ?>
						<?php else : ?>
							<?php echo $val['stump_clean'] ? $val['gfirstname'] . ' ' . $val['glastname'] . ' (' . date('m/d ' . getPHPTimeFormatWithOutSeconds(), strtotime($val['stump_clean'])) . ')' : '-'; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
