<!-- Estimate Project Requirements-->
<section class="row">
	<section class="col-md-4">
		<section class="panel panel-default">
			<!-- Team Requirements Header -->
			<header class="panel-heading">Team Requirements:</header>
			<!-- Team Requirements Data -->
			<table class="table table-striped">
				<?php foreach($estimate_data->mdl_services_orm as $key=>$service_data) : //var_dump($service_data); die;?>
					<?php foreach($service_data->crew as $jkey=>$crew) : ?>
						<?php $crews[$key][$jkey]['name'] = $crew->crew_name; ?>
						<?php $crews[$key][$jkey]['id'] = $crew->crew_id; ?>
					<?php endforeach; ?>
					
					<?php //$equipments = []; ?>
					<?php foreach($service_data->equipments as $jkey=>$equipment) : //var_dump($equipment); die;?>
						
						<?php // $equipments[$jkey]['name'] = $equipment->eq_name; ?>
						<!-- setup -->
							<?php //$equipments[$key][$jkey]['name'] = ''; ?>
							<?php if($equipment->vehicle_name) : ?>
								<?php $equipments[$key][$jkey]['equipment_item_id'] = $equipment->equipment_item_id; ?>
								<?php $equipments[$key][$jkey]['name'] = $equipment->vehicle_name; ?>
								<?php if($equipment->equipment_item_option && !empty((array)json_decode($equipment->equipment_item_option))) : ?>
									<?php $opts = (array)json_decode($equipment->equipment_item_option); ?>
									<?php $equipments[$key][$jkey]['name'] .= ' ('; ?>
									<?php foreach($opts as $j=>$opt) : ?>
										<?php $equipments[$key][$jkey]['name'] .= trim($opt); ?>
										<?php if(isset($opts[$j +1 ])) : $equipments[$key][$jkey]['name'] .= ' or ' ; endif; ?>
									<?php endforeach; ?>
									<?php $equipments[$key][$jkey]['name'] .= ')';?>
									
								<?php else : ?>
									<?php $equipments[$key][$jkey]['name'] .= ' (Any)';?>
								<?php endif; ?>
							<?php endif; ?>
						
								
						<?php if($equipment->trailer_name) : ?>
							<?php if(!isset($equipment->vehicle_name) || !$equipment->vehicle_name) : ?>
									<?php $equipments[$key][$jkey]['name'] = $equipment->trailer_name; ?>
							<?php else : ?>
								<?php $equipments[$key][$jkey]['name'] .= ', ' . $equipment->trailer_name; ?>
							<?php endif; ?>
							<?php //$equipments[$jkey]['equipment_item_id'] = $equipment->trailer_id; ?>
							<?php if($equipment->equipment_attach_option && !empty((array)json_decode($equipment->equipment_attach_option))) : ?>
								<?php $opts = (array)json_decode($equipment->equipment_attach_option); ?>
								<?php $equipments[$key][$jkey]['name'] .= ' ('; ?>
								<?php if(is_array($opts)): ?>
								<?php foreach($opts as $j=>$opt) : ?>
									<?php $equipments[$key][$jkey]['name'] .= trim($opt); ?>
									<?php if(isset($opts[$j +1 ])) : $equipments[$key][$jkey]['name'] .= ' or ';  endif; ?>
								<?php endforeach; ?>
								<?php endif; ?>
								<?php $equipments[$key][$jkey]['name'] .= ')';?>
							<?php else : ?>
								<?php $equipments[$key][$jkey]['name'] .= ' (Any)';?>
							<?php endif;?>
						<?php endif;?>
						<?php  if($equipment->equipment_attach_tool) : //var_dump($equipment); die;?>
							<?php $attTool = json_decode($equipment->equipment_attach_tool); ?>
							<?php foreach($attTool as $k=>$v) : ?>
								<?php if(!isset($equipments[$key][$jkey]['name'])) : ?>
									<?php $equipments[$key][$jkey]['name'] = ''; ?>
								<?php endif?>
								<?php if(!$k) : $equipments[$key][$jkey]['name'] .= ', ';?><?php endif?>
								<?php $opts = json_decode($equipment->equipment_tools_option);?>
								<?php foreach($tools as $tool) : ?>
								<?php if($tool->vehicle_id == $v) : //var_dump($opts->$v); die;?>
									<?php //$equipments[$key][$jkey]['name'] .= '<br><small>' . $tool->vehicle_name; ?>
									<?php if(isset($opts->$v) && !empty($opts->$v)) : ?>
										<?php //$opts = json_decode($equipment->equipment_tools_option); ?>
										<?php foreach($opts->$v as $j=>$opt) : ?>
											
												<?php $equipments[$key][$jkey]['name'] .= trim($opt); ?>
											<?php if(isset($opts->$v[$j +1]) || isset($attTool[$k+1])) : $equipments[$key][$jkey]['name'] .= ', '; ?>
											
											<?php endif; ?>
											<?php $equipments[$key][$jkey]['name'] .= ' ';?>
										<?php endforeach; ?>
									<?php else : ?>
										<?php $equipments[$key][$jkey]['name'] .= ' (Any)'; ?>
									<?php endif;?>
								<?php endif; ?>
								<?php endforeach; ?>
								<?php $equipments[$key][$jkey]['name'] = trim($equipments[$key][$jkey]['name'], ','); ?>
								<?php //$equipments[$key][$jkey]['opts'] .= '</small>'; ?>
							<?php endforeach; ?>
						<?php endif; ?>
							
							<?php /* $equipments[$i]['name'] =  $equipment->tool_name ?>							
							<?php $equipments[$i]['equipment_item_id'] = $equipment->tool_id; ?>
							<?php if($equipment->equipment_tools_option && count(json_decode($equipment->equipment_tools_option))) : ?>
								<?php $opts = json_decode($equipment->equipment_tools_option); ?>
								<?php $equipments[$i]['name'] .= ' ('; ?>
								<?php foreach($opts as $j=>$opt) : ?>
									<?php $equipments[$i]['name'] .= trim($opt); ?>
									<?php if(isset($opts[$j +1 ])) : $equipments[$i]['name'] .= ' or ';  endif; ?>
								<?php endforeach; ?>
								<?php $equipments[$i]['name'] .= ')';?>
							<?php endif; */?>
						
						<!-- setup end -->
					<?php endforeach; ?>
					<?php
						if($service_data->service_disposal_brush)
							$project['service_disposal_brush'] = true; 
						else
							$project['service_disposal_brush'] = false;
						if($service_data->service_disposal_wood)
							$project['service_disposal_wood'] = true; 
						else
							$project['service_disposal_wood'] = false; 
						if($service_data->service_cleanup)
							$project['service_cleanup'] = true;
						else
							$project['service_cleanup'] = false;
						if($service_data->service_permit)
							$project['service_permit'] = true;
						else
							$project['service_permit'] = false;
						if($service_data->service_exemption)
							$project['service_exemption'] = true;
						else
							$project['service_exemption'] = false;
						if($service_data->service_client_home)
							$project['service_client_home'] = true;
						else
							$project['service_client_home'] = false;
					?>
				<?php endforeach; ?>
				<?php $rows = 6; $current = array(); ?>
				<?php if(isset($crews) && !empty($crews)) : //var_dump($crews); die;?>
					<?php //$rows = $rows - count($crews); ?>
					<?php foreach($crews as $jkey=>$jcrew) : ?>
						<?php foreach($jcrew as $jkey=>$crew) : ?>
							<?php if(array_search($crew['name'], $current) === FALSE) : ?>
								<tr>
									<td width="30">
										<i class="fa fa-check"></i>
									</td>
									<td><?php echo $crew['name']; $current[] = $crew['name']; $rows--; ?></td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</table>
		</section>
	</section>

	<!-- Equipment requirements-->
	<section class="col-md-4">
		<section class="panel panel-default">
			<!-- Team Requirements Header -->
			<header class="panel-heading">Equipment Requirements:</header>
			<!-- Team Requirements Data -->
			<table class="table table-striped">
				<?php $rows = 6; $current = $setups = $opts = array(); ?>
				<?php if(isset($equipments) && !empty($equipments)) : //var_dump($equipments); die;?>
				<?php $rows = $rows - !empty($equipments); ?>
					<?php foreach($equipments as $jeq) : ?>
						<?php foreach($jeq as $equipment) : ?>
							<?php if(array_search($equipment['name'], $setups) === FALSE) : ?>
							<tr>
								<td width="30">
									<i class="fa fa-check"></i>
								</td>
								<td><?php echo $equipment['name']; //$current[] = $equipment['equipment_item_id']; ?></td>
								<?php $setups[] = $equipment['name']; ?>
							</tr>
							<?php endif;  ?>
						<?php if(isset($equipment['opts']) && $equipment['opts'] != '' && array_search($equipment['opts'], $opts) === FALSE) : ?>
							<tr>
								<td width="30">
									<i class="fa fa-check"></i>
								</td>
								<td><?php echo $equipment['opts']; //$current[] = $equipment['equipment_item_id']; ?></td>
								<?php $opts[] = $equipment['opts']; ?>
							</tr>
						<?php endif; ?>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if(!isset($equipments) && $rows > 0) : ?>
				<?php foreach($equipment_items as $item) : ?>
					<?php if(isset($item->vehicle_id) && array_search($item->vehicle_id, $current) === FALSE) : ?>
					<?php //if(array_search($item->eq_name, $current) === FALSE) : ?>
							<tr>
								<td width="30">
									<i class="fa fa-times"></i>
								</td>
								<td><?php echo $item->vehicle_name; ?></td>
							</tr>
						<?php $rows--; if($rows == 0) break; ?>
					<?php endif; ?>
				<?php endforeach;?>
				<?php endif; ?>
			</table>
		</section>
	</section>
	<!-- /Equipment Requirements-->

	<!-- Project Requirements-->
	<section class="col-md-4">
		<section class="panel panel-default">
			<!-- Team Requirements Header -->
			<header class="panel-heading">Project Requirements:</header>
			<!-- Team Requirements Data -->
			<table class="table table-striped">
				<tr>
					<td width="30">
					<?php if($estimate_data->full_cleanup == 'yes') : ?>
						<i class="fa fa-check"></i>
					<?php else : ?>
						<i class="fa fa-times"></i>
					<?php endif; ?>
					</td>
					<td>Cleanup</td>
				</tr>
				<tr>
					<td width="30">
					<?php if($estimate_data->brush_disposal == 'yes') : ?>
						<i class="fa fa-check"></i>
					<?php else : ?>
						<i class="fa fa-times"></i>
					<?php endif; ?>
					</td>
					<td>Disposal Brush</td>
				</tr>
				<tr>
					<td width="30">
					<?php if($estimate_data->leave_wood == 'yes') : ?>
						<i class="fa fa-check"></i>
					<?php else : ?>
						<i class="fa fa-times"></i>
					<?php endif; ?>
					</td>
					<td>Disposal Wood</td>
				</tr>				
			</table>
		</section>
	</section>
</section>
<!-- /Project Requirements-->
<!-- /Estimate Project Requirements -->
