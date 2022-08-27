<div id="edit-<?php echo $repair->repair_id; ?>" class="modal fade" tabindex="-1"
							 role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<header class="panel-heading">
										Edit <?php echo $repair->item_name; ?> Repair<br>
										Reported by 
										<?php echo $repair->author_name; ?><br>
<!--										--><?php //echo $repair->repair_date;?>
										<?php echo getDateTimeWithDate($repair->repair_date, 'Y-m-d H:i:s', true);?>

									</header>
									
									<form method="post" action="<?php echo base_url('equipments/repair_update'); ?>">
									<!-- Client Files Header-->
										<div class="modal-body">
											<div class="p-10">
												<div class="control-group">
													<label class="control-label">Item Group:</label>
													<div class="control-group">
														<select class="form-control group" name="group" disabled>
															<?php foreach($groups as $k=>$v) : ?>
																<option <?php if($v->group_id == $repair->group_id) : ?>selected="selected"<?php endif; ?> value="<?php echo $v->group_id?>"><?php echo $v->group_name; ?></option>
															<?php endforeach; ?>
														</select>
													</div>
												</div>
											</div>
											<div class="p-10">
												<div class="control-group">
													<label class="control-label">Item Name:</label>
													<div class="control-group">
														<select class="form-control item" name="item" disabled>
															<option selected="selected" value="<?php echo $repair->item_id; ?>"><?php echo $repair->item_name; ?></option>
														</select>
													</div>
												</div>
											</div>
											<?php //if(isset($repair->repair_first_comment) && $repair->repair_first_comment) : ?>
											<div class="p-10">
												<div class="control-group">
													<label class="control-label">Comment:</label>
													<div class="control-group">
														
														<textarea data-id="<?php echo $repair->item_id;?>" name="editRepairComment" class="form-control editRepairComment" placeholder="Comment"><?php echo $repair->repair_first_comment ? $repair->repair_first_comment : '';?></textarea>
														
													</div>
												</div>
											</div>
											<?php //endif; ?>
											<div class="p-10">
												<div class="control-group">
													<label class="control-label">Assigned to:</label>
													<div class="control-group">
														<select class="form-control repaired_by" name="repaired_by">
															<?php foreach($solders as $key=>$val) : ?>
																<option <?php if($val['value'] == $repair->repair_solder_id) : ?>selected="selected"<?php endif; ?> value="<?php echo $val['value']; ?>"><?php echo $val['text']; ?></option>
															<?php endforeach; ?>
														</select>
													</div>
												</div>
											</div>
											<div class="p-10">
												<div class="control-group">
													<label class="control-label">Priority:</label>
													<div class="radio inline m-l">
														<label>
															<input name="priority" type="radio" <?php if($repair->repair_priority == 1) : ?>checked="checked"<?php endif; ?> value="1" />1
														</label>
													</div>
													<div class="radio inline m-l">
														<label>
															<input name="priority" type="radio" <?php if($repair->repair_priority == 2) : ?>checked="checked"<?php endif; ?> value="2" />2
														</label>
													</div>
													<div class="radio inline m-l">
														<label>
															<input name="priority" type="radio" <?php if($repair->repair_priority == 3) : ?>checked="checked"<?php endif; ?> value="3" />3
														</label>
													</div>
													<div class="radio inline m-l">
														<label>
															<input name="priority" type="radio" <?php if($repair->repair_priority == 4) : ?>checked="checked"<?php endif; ?> value="4" />4
														</label>
													</div>
												</div>
											</div>
											<div class="p-10">
												<div class="control-group">
													<label class="control-label">Type:</label>
													<div class="radio inline m-l">
														<label>
															<input name="type" type="radio"  <?php if($repair->repair_type == 'damage') : ?>checked="checked"<?php endif; ?> value="damage" />Damage
														</label>
													</div>
													<div class="radio inline m-l">
														<label>
															<input name="type" type="radio" <?php if($repair->repair_type == 'repair') : ?>checked="checked"<?php endif; ?>  value="repair" />Repair
														</label>
													</div>
													<div class="radio inline m-l">
														<label>
															<input name="type" type="radio" <?php if($repair->repair_type == 'maintenance') : ?>checked="checked"<?php endif; ?> value="maintenance" />Maintenance
														</label>
													</div>
												</div>
											</div>
											<div class="p-10">
												<div class="control-group">
													<label class="control-label">Status:</label>
													<div class="control-group">
														<select class="form-control changeNewStatus" name="status">
															<option <?php if($repair->repair_status == 'on_hold') : ?>selected="selected"<?php endif; ?> value="on_hold">On Hold</option>
															<option <?php if($repair->repair_status == 'repaired') : ?>selected="selected"<?php endif; ?> value="repaired">Repaired</option>
															<option <?php if($repair->repair_status == 'not_repaired') : ?>selected="selected"<?php endif; ?> value="not_repaired">Not Repaired</option>
														</select>
													</div>
												</div>
											</div>
											<div class="p-10 soldInputs" style="display:<?php if($repair->repair_status == 'repaired') : ?>block<?php else : ?>none<?php endif; ?>;" >
												<div class="control-group">
													<label class="control-label">Price:</label>
													<div class="control-group">
														<input name="price" class="form-control price" type="number" placeholder="Price"/ value="<?php if($repair->repair_price) : echo $repair->repair_price; endif; ?>">
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Hours:</label>
													<div class="control-group">
														<input name="hours" class="form-control hours" type="number" placeholder="Hours"/ value="<?php if($repair->repair_hours) : echo $repair->repair_hours; endif; ?>">
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Counter Value:</label>
													<div class="control-group">
														<input name="counter" class="form-control counter" type="number" placeholder="Counter Value"/ value="<?php if($repair->repair_counter) : echo $repair->repair_counter; endif; ?>">
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Comment:</label>
													<div class="control-group">
														
														<textarea name="commentSold" class="form-control commentSold" placeholder="Comment"><?php if($repair->repair_finish_comment) : echo $repair->repair_finish_comment; endif; ?></textarea>
													</div>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											
											<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
											<input type="hidden" class="btn btn-info repair_id" name="repair_id" value="<?php echo $repair->repair_id; ?>">
											<input type="hidden" class="btn btn-info item_id" name="item_id" value="<?php echo $repair->repair_item_id; ?>">
											<input type="hidden" class="btn btn-info old" name="old" value="<?php echo $repair->repair_status; ?>">
											<input type="submit" class="btn btn-info edit_status" value="Send">
															
										</div>
									</form>
								</div>
							</div>
						</div>
