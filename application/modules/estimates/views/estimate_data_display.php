<?php $pdfFiles = isset($estimate_data) && $estimate_data->estimate_pdf_files && $this->router->fetch_class() == 'estimates' ? json_decode($estimate_data->estimate_pdf_files, true) : []; ?>
<?php $pdfFiles = isset($workorder_data) && $workorder_data->wo_pdf_files && $this->router->fetch_class() == 'workorders' ? json_decode($workorder_data->wo_pdf_files, true) : $pdfFiles; ?>
<?php $pdfFiles = isset($invoice_data) && $invoice_data->invoice_pdf_files && $this->router->fetch_class() == 'invoices' ? json_decode($invoice_data->invoice_pdf_files, true) : $pdfFiles; ?>
<?php if (empty($schedule) && !isset($unsortable)) : ?>
	<script src="<?php echo base_url('assets/vendors/notebook/js/sortable/jquery.sortable.js'); ?>"></script>
<?php endif; ?>

<?php
$count_new_services = 0;
foreach ($estimate_data->mdl_services_orm as $service_data){
    if($service_data->service_status==0)
        $count_new_services++;
}
?>

<!-- Estimate Information Display -->
<section class="media-body p-n">
<section class="panel panel-default">
<!-- Estimate Information Header -->
<header class="panel-heading">
    <div class="pull-left">
    Estimate for&nbsp;<?php echo $client_data->client_name; ?>
    </div>
    <div class="pull-right text-right">
        <button id="save-complete-selected" data-estimate_id="<?php echo $estimate_data->estimate_id; ?>" style="margin: -9px 0 -7px 0;display: none;" class="btn btn-success btn-sm animated fadeInRight">Complete Selected <span class="fa fa-check-circle"></span></button>
    </div>
    <div class="clear"></div>
        <!--
    <?php if(isset($estimate_data) && $estimate_data->tree_inventory_pdf): ?>
    <div class="pull-right m-bottom-0">
        <div class="checkbox m-n">
            <label class="checkbox-custom">
                <input type="checkbox" name="tree_inventory_pdf" checked="checked" disabled="disabled">
                <i class="fa fa-fw fa-square-o"></i>
                <strong style="color: #4aa700;text-decoration: underline;">Tree Inventory PDF</strong>&nbsp;&nbsp;
            </label>
            <i class="h4 fa fa-file-text-o"></i>
        </div>
    </div>
    <div class="clear"></div>
    <?php endif;?>
    -->
</header>
<!-- Estimate Items -->
<table class="table table-striped b-t b-light m-n estimate_statuses">
<thead>
<tr>
	<th>
        <?php if($this->router->fetch_class() == 'workorders'): ?>
        <label class="checkbox-inline">
            <input type="checkbox" id="complete-all-services" <?php if(!$count_new_services): ?>disabled="disabled"<?php endif; ?> value="1"><b>Description</b>
        </label>
        <?php else: ?>
            <b>Description</b>
        <?php endif; ?>
    </th>

	<?php if (empty($schedule)) : ?>
	<th width="100" class="text-right">Price</th>
	<?php endif; ?>
</tr>
</thead>
<tbody <?php if (empty($schedule) && !isset($unsortable)) : ?> class="sortable"<?php endif; ?>>
<?php $sum = 0;
$time = 0;
$sumForTax = 0;
?>
<?php foreach ($estimate_data->mdl_services_orm as $service_data) : ?>
	<?php
		$service_style = '';
		if(isset($service_ids) && !empty($service_ids))
		{
			if(array_search($service_data->id, $service_ids) !== FALSE)
				$service_style = 'background-color: rgba(180, 193, 76, 0.35);';
		}
		?>
		<?php $time += ($service_data->service_time + $service_data->service_travel_time + $service_data->service_disposal_time) * count($service_data->crew); ?>
	<tr data-estimate_sesvice_time="<?php echo ($service_data->service_time + $service_data->service_travel_time + $service_data->service_disposal_time) * count($service_data->crew); ?>" data-estimate_service_id="<?php echo $service_data->id; ?>"<?php if(isset($workorder_data)) : ?> data-workorder-id="<?php echo $workorder_data->id; ?>"<?php endif; ?>>
		<td style="padding-left: 35px;<?php echo $service_style; ?>">
            <?php if (isset($schedule) && $schedule) : ?>
				<?php if(isset($wo_statuses) && $wo_statuses) : ?>
					<div class="control-group m-t-sm m-b-sm">
						<div class="inline pull-left">
							<label>Status: </label>
							<select name="service_status"<?php if(!isset($estimate_data->est_status_confirmed) || (isset($estimate_data->est_status_confirmed) && !$estimate_data->est_status_confirmed)) : ?> disabled<?php endif; ?> class="form-control" id="<?php echo $service_data->id; ?>" data-id="<?php echo $service_data->id; ?>" onchange="changeStatus($(this))" style="display: inline-block; width: 145px;">
								<?php foreach($wo_statuses as $key=>$status) : ?>
									<option name="<?php echo $status['services_status_name']?>" value="<?php echo $status['services_status_id']; ?>" <?php if($service_data->service_status == $status['services_status_id']) : ?>selected="selected"<?php endif; ?>><?php echo $status['services_status_name']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="inline pull-right">
							<?php echo money((float)$service_data->service_price); ?>
							<span class="btn btn-xs btn-warning selectService<?php if($service_data->service_status == 1 ||$service_data->service_status == 2) : ?> disabled<?php endif; ?>" style="margin-top: -3px; width: 24px;"><i class="fa fa-circle-o"></i></span>
						</div>
						<div class="clear"></div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<h5>
                <?php if($this->router->fetch_class() == 'workorders'): ?>
                    <?php if($service_data->service_status==0): ?>
                    <label class="checkbox-inline complete-service-status" style="margin-left: -20px;">
                        <input type="checkbox" id="inlineCheckbox<?php echo $service_data->id; ?>" value="<?php echo $service_data->id; ?>">
                        <strong><?= isset($service_data->profile_estimate_service_ti_title) && !empty($service_data->profile_estimate_service_ti_title) ? $service_data->profile_estimate_service_ti_title : $service_data->service->service_name; ?></strong>
                    </label>
                    <?php else: ?>
                        <?php if($service_data->service_status==1): ?>
                            <span class="fa fa-ban text-danger" style="margin-left: -20px;"></span>
                        <?php else: ?>
                        <span class="fa fa-check text-success" style="margin-left: -20px;"></span>
                        <?php endif; ?>
                        <strong><?= isset($service_data->profile_estimate_service_ti_title) && !empty($service_data->profile_estimate_service_ti_title) ? $service_data->profile_estimate_service_ti_title : $service_data->service->service_name; ?></strong>
                    <?php endif; ?>
                <?php else: ?>
                    <strong><?= isset($service_data->profile_estimate_service_ti_title) && !empty($service_data->profile_estimate_service_ti_title) ? $service_data->profile_estimate_service_ti_title : $service_data->service->service_name; ?></strong>
                <?php endif; ?>
            </h5>
			<?= isset($service_data->profile_service_description) && !empty($service_data->profile_service_description) ? decorate_text($service_data->profile_service_description, true) : decorate_text($service_data->service_description, true); ?>

			<?php if (empty($schedule) || !$schedule) : ?>

				<?php $files = bucketScanDir('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $service_data->id . '/'); ?>
				<?php if ($files && !empty($files)) : ?>
					<div>
						<?php foreach ($files as $img) : ?>
							<?php $filepath = 'uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $service_data->id . '/' . $img; ?>
							<?php $size = bucket_get_file_info($filepath); ?>
							<div class="inline" style="position: relative;">
								<a href="<?php echo base_url(get_image_dir($estimate_data->client_id, $estimate_data->estimate_no, $service_data->id) . $img); ?>" style="text-decoration: none;"
								  <?php if($size['mimetype'] == 'application/pdf') : ?> target="_blank" >
									<img
										src="<?php echo base_url('assets/vendors/notebook/images/pdf.png'); ?>"
										width="32" height="32"/>
                                  <?php elseif(!in_array(strtolower(pathinfo($size['name'], PATHINFO_EXTENSION)), ['gif', 'jpg', 'jpeg', 'png'])) : ?> target="_blank" >
                                        <img
                                                src="<?php echo base_url('assets/vendors/notebook/images/attach.png'); ?>"
                                                width="32" height="32"/>
								  <?php else : ?>
									data-lightbox="works">
									<img
										src="<?php echo base_url($filepath); ?>"
										width="32" height="32"/>
									<?php endif;?>
								</a>
								<input type="checkbox" <?php if(array_search($filepath, $pdfFiles) !== FALSE) : ?>checked="checked"<?php endif; ?> data-file-name="<?php echo $filepath; ?>" style="position: absolute;bottom: -16px;right: 10px;">
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			<?php endif; ?>
            <?php if($service_data->service->is_bundle): ?>
                <div  style="border-left: 1px solid #bebebe; <?php echo $service_style; ?> ">
                    <?php if(isset($service_data->bundle_records)) foreach($service_data->bundle_records as $bundle_record) : ?>
                        <div style="padding-left: 20px; style="<?php echo $service_style; ?>"">
                            <div class="">
                                <div class="pull-left text-left">
                                    <strong ><?php echo $bundle_record->service->service_name; ?></strong>
                                    <?php if($bundle_record->non_taxable): ?>
                                        <span>(Non-taxable)</span>
                                    <?php endif; ?>
                                    <span>
                                                <?php if($bundle_record->service->is_product) : ?>
                                                    (<?php echo $bundle_record->quantity ?> x <?php echo str_replace(' ', '', nl2br(money($bundle_record->cost))); ?>  <?php echo str_replace(' ', '', nl2br(money($bundle_record->service_price)))?>)
                                                <?php else : ?>
                                                    (<?php echo str_replace(' ', '', nl2br(money($bundle_record->service_price))); ?>)
                                                    <?php $time += ($bundle_record->service_time + $bundle_record->service_travel_time + $bundle_record->service_disposal_time) * count($bundle_record->crew);?>
                                                <?php endif ?>
                                        </span>

                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="m-t-b-5 p-bottom-10">
                                <div class="pull-left p-b-5" style="text-align: justify;">
                                    <?php echo decorate_text($bundle_record->service_description); ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <?php if (empty($schedule) || !$schedule) : ?>
                                <?php $files = bucketScanDir('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $bundle_record->id . '/'); ?>
                                <?php if ($files && !empty($files)) : ?>
                                    <div>
                                        <?php foreach ($files as $img) : ?>
                                            <?php $filepath = 'uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $bundle_record->id . '/' . $img; ?>
                                            <?php $size = bucket_get_file_info($filepath); ?>
                                            <div class="inline" style="position: relative;">
                                                <a href="<?php echo base_url(get_image_dir($estimate_data->client_id, $estimate_data->estimate_no, $bundle_record->id) . $img); ?>" style="text-decoration: none;"
                                                    <?php if($size['mimetype'] == 'application/pdf') : ?> target="_blank" >
                                                    <img
                                                            src="<?php echo base_url('assets/vendors/notebook/images/pdf.png'); ?>"
                                                            width="32" height="32"/>
                                                    <?php else : ?>
                                                        data-lightbox="works">
                                                        <img
                                                                src="<?php echo base_url($filepath); ?>"
                                                                width="32" height="32"/>
                                                    <?php endif;?>
                                                </a>
                                                <input type="checkbox" <?php if(array_search($filepath, $pdfFiles) !== FALSE) : ?>checked="checked"<?php endif; ?> data-file-name="<?php echo $filepath; ?>" style="position: absolute;bottom: -16px;right: 10px;">
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="clear" style="margin-bottom: 20px;"></div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
			<?php if(!$service_data->service->is_product && !$service_data->service->is_bundle): ?>
			<div class="row m-t-md">

					<?php if (empty($schedule) || !$schedule) : ?>
						<!-- <div class="col-md-3"></div> -->
					<?php endif; ?>
					<div class="<?php if (empty($schedule) || !$schedule) : ?>
									col-md-3 small
								<?php else : ?>
									col-md-5 small
								<?php endif; ?>">
                        <?php if($service_data->crew && is_array($service_data->crew) && !empty($service_data->crew)) : ?>
						<label>
							<strong>Team Requirements:</strong>
						</label>
                        <div>
                            <i class="fa fa-check"></i>
                            <?php echo implode(', ', array_column($service_data->crew, 'crew_name')); ?>
                        </div>
                            <?php  ?>
							<?php /*foreach($service_data->crew as $jkey=>$crew) : ?>
								<div>
									<i class="fa fa-check"></i>
									<?php echo $crew->crew_name; ?><br>
								</div>
							<?php endforeach;*/ ?>
                        <?php endif; ?>
					</div>

				<div class="<?php if (empty($schedule) || !$schedule) : ?>col-md-6 small<?php else : ?>col-md-5 small<?php endif; ?>">
                    <?php if($service_data->equipments && is_array($service_data->equipments) && !empty($service_data->equipments) && isset($service_data->equipments[0]) && isset($service_data->equipments[0]->vehicle_name)) : ?>
					<label>
						<strong>Equip. Requirements:</strong>
					</label>
					<?php foreach($service_data->equipments as $jkey=>$equipment) : ?>
						<div>
						
								<?php if($equipment->vehicle_name) : ?>
								<i class="fa fa-check"></i>
									<?php echo $equipment->vehicle_name;?> 
									<?php if($equipment->equipment_item_option && !empty((array)json_decode($equipment->equipment_item_option))) : ?>
										<?php $opts = (array)json_decode($equipment->equipment_item_option); ?>
										<?php foreach($opts as $j=>$opt) : ?>
											<?php if(!$j) : ?>(<?php endif?><?php echo trim($opt); ?><?php if(isset($opts[$j +1 ])) : ?> or <?php else : ?>)<?php endif; ?>
										<?php endforeach; ?>
									<?php else : ?>
										(Any)
									<?php endif; ?>
								<?php endif; ?>
								<?php if($equipment->trailer_name) : ?>
								<?php if(!isset($equipment->vehicle_name) || !$equipment->vehicle_name) : ?>
									<i class="fa fa-check"></i>
										<?php echo $equipment->trailer_name; ?>
								<?php else : ?>
									<?php echo ', ' . $equipment->trailer_name ?>
								<?php endif; ?>
									<?php if($equipment->equipment_attach_option && !empty((array)json_decode($equipment->equipment_attach_option))) : ?>
										<?php $opts = (array)json_decode($equipment->equipment_attach_option); ?>
										<?php if(is_array($opts)): ?>
										<?php foreach($opts as $j=>$opt) : ?>
											<?php if(!$j) : ?>(<?php endif?><?php echo trim($opt); ?><?php if(isset($opts[$j +1 ])) : ?> or <?php else : ?>)<?php endif; ?>
										<?php endforeach; ?>
										<?php endif; ?>
									<?php else : ?>
									(Any)
									<?php endif;?>
								<?php endif;?>
								<?php if($equipment->equipment_attach_tool) : ?>
									<?php $eqOpt = json_decode($equipment->equipment_attach_tool); ?>
									<?php foreach($eqOpt as $k=>$v) : ?>
										<?php $opts = json_decode($equipment->equipment_tools_option);?>
										<?php foreach($tools as $tool) : ?>
										<?php if($tool->vehicle_id == $v) : //var_dump($opts->$v); die;?>
											 <?php //echo  $tool->vehicle_name; ?>
											<?php if(isset($opts->$v) && !empty($opts->$v)) : ?>
												<?php //$opts = json_decode($equipment->equipment_tools_option); ?>
												<?php foreach($opts->$v as $j=>$opt) : ?>
													<?php echo ', ' . trim($opt); ?>
													<?php /* if(isset($opts->$v[$j +1]) || isset(json_decode($equipment->equipment_attach_tool)[$k+1])) : ?>, <?php endif; */ ?>
												<?php endforeach; ?>
											<?php else : ?>
											(Any)
											<?php endif;?>
										<?php endif; ?>
										<?php endforeach; ?>
									<?php endforeach; ?>
								<?php endif;?>
							<br>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
				</div>


			</div>
			<?php endif; ?>
			<?php if (isset($schedule) && $schedule) : ?>
			<div class="row m-t-sm">
					
				<div class="col-md-5 m-t-sm small">
                    <?php if($service_data->non_taxable): ?>
                        <div class="small">
                            <i class="fa fa-check"></i>
                            Non-taxable
                        </div>
                    <?php endif; ?>
					<?php if(!$service_data->service->is_product && !$service_data->service->is_bundle && ($service_data->service_time || $service_data->service_travel_time || $service_data->service_disposal_time)): ?>
                        <label>
                            <strong>Time:</strong>
                        </label>
                        <?php if($service_data->service_time) : ?>
                        <div>
                            Service Time :
                            <?php echo isset($service_data->service_time) ? $service_data->service_time : 0; ?> hrs.
                        </div>
                        <?php endif; ?>
                        <?php if($service_data->service_travel_time) : ?>
                        <div>
                            Travel Time:
                            <?php echo isset($service_data->service_travel_time) ? $service_data->service_travel_time : 0; ?> hrs.
                        </div>
                        <?php endif; ?>
                        <?php if($service_data->service_disposal_time) : ?>
                        <div>
                            Disposal Time:
                            <?php echo isset($service_data->service_disposal_time) ? $service_data->service_disposal_time : 0; ?> hrs.
                        </div>
                        <?php endif; ?>
					<?php else: ?>
						<label>
							<strong>Product Details:</strong>
						</label>
						<div>
							Cost:
							<?php echo isset($service_data->cost) ? money($service_data->cost) : money(0); ?>
						</div>
						<div>
							Quantity:
							<?php echo isset($service_data->quantity) ? $service_data->quantity : 0; ?>
						</div>
					<?php endif; ?>
				</div>

			</div>
			<?php endif; ?>
		</td>

		<?php if (empty($schedule)) : ?>
		<td style="<?php echo $service_style; ?> padding-top: 15px" align="right">

            <?php echo money($service_data->service_price); ?>

			<?php if(isset($schedule) && $schedule) : ?>
				<span class="btn btn-xs btn-warning selectService<?php if($service_data->service_status == 1 ||$service_data->service_status == 2) : ?> disabled<?php endif; ?>" style="margin-top: -3px; width: 24px;"><i class="fa fa-circle-o"></i></span>
			<?php endif; ?>
			<?php if(isset($wo_statuses) && $wo_statuses) : ?>
			<div class="control-group m-t-sm m-b-sm">
			
				<select name="service_status"<?php if(!isset($estimate_data->est_status_confirmed) || (isset($estimate_data->est_status_confirmed) && !$estimate_data->est_status_confirmed)) : ?> disabled<?php endif; ?> class="form-control" id="<?php echo $service_data->id; ?>" data-id="<?php echo $service_data->id; ?>" onchange="changeStatus($(this))" style="display: inline-block; width: 145px;">
					<?php foreach($wo_statuses as $key=>$status) : ?>
						<option name="<?php echo $status['services_status_name']?>" value="<?php echo $status['services_status_id']; ?>" <?php if($service_data->service_status == $status['services_status_id']) : ?>selected="selected"<?php endif; ?>><?php echo $status['services_status_name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
            <?php if($service_data->non_taxable): ?>
                <div class="small">
                    <i class="fa fa-check"></i>
                    Non-taxable
                </div>
            <?php endif; ?>
            <?php if(!$service_data->service->is_product && !$service_data->service->is_bundle && ($service_data->service_time || $service_data->service_travel_time || $service_data->service_disposal_time)): ?>
                <label>
                    <strong>Time:</strong>
                </label>
                <?php if($service_data->service_time) : ?>
                    <div>
                        Service Time :
                        <?php echo isset($service_data->service_time) ? $service_data->service_time : 0; ?> hrs.
                    </div>
                <?php endif; ?>
                <?php if($service_data->service_travel_time) : ?>
                    <div>
                        Travel Time:
                        <?php echo isset($service_data->service_travel_time) ? $service_data->service_travel_time : 0; ?> hrs.
                    </div>
                <?php endif; ?>
                <?php if($service_data->service_disposal_time) : ?>
                    <div>
                        Disposal Time:
                        <?php echo isset($service_data->service_disposal_time) ? $service_data->service_disposal_time : 0; ?> hrs.
                    </div>
                <?php endif; ?>
			<?php elseif($service_data->service->is_product): ?>
				<div class="small">
					<label>
						<strong>Product Details:</strong>
					</label>
					<div>
						Cost:
						<?php echo isset($service_data->cost) ? money($service_data->cost) : money(0); ?>
					</div>
					<div>
						Quantity:
						<?php echo isset($service_data->quantity) ? $service_data->quantity : 0; ?>
					</div>
				</div>
			<?php endif; ?>
		</td>
		<?php endif; ?>
		
		
	</tr>
<?php endforeach; ?>
</tbody>

<?php if (empty($schedule) || !$schedule) : ?>
	<tbody>
	<tr>
		<td class="text-right"><strong>Total For Estimate:</strong></td>
		<td class="text-left">
			<?php /*if($estimate_data->estimate_hst_disabled == 2) : */?><!--
				<?php /*echo money($sumForTax / $estimate_data->estimate_tax_rate + ($sum - $sumForTax)); */?>
			<?php /*else : */?>
				<?php /*echo money($sum); */?>total_all_services
			--><?php /*endif;*/ ?>
            <?php echo money($estimate_data->sum_for_services); ?>
		</td>
	</tr>
	<?php
	$term = \application\modules\invoices\models\Invoice::getInvoiceTerm($client_data->client_type ?? null);
	$totalConfirmed = ($estimate_data->sum_taxable + $estimate_data->sum_non_taxable) - $estimate_data->discount_total;//$overdue_amt
	/* Showing overdue only in case of invoices  */
	
	if (!empty($invoice_interest_data)) {
		foreach ($invoice_interest_data as $jk=>$interset_row) {
            $totalToDue = $totalConfirmed;
            $minusPayments = 0;
			if ($payments_data && !empty($payments_data)) {
				foreach ($payments_data as $pay) {
					if ($pay['payment_date'] < (strtotime($interset_row->overdue_date) - $term * 86400))
                        $minusPayments += $pay['payment_amount'];
				}
			}
            $totalToDue -= $minusPayments;
			$interest = abs($interset_row->rate / 100);
			$monthDue = $totalToDue * $interest;
            $monthDue = $monthDue >= 0 ? $monthDue : 0;
            $totalConfirmed += $monthDue;
            $totalToDue += $minusPayments;

			?>
            <?php if ($interest !== 0) : ?>
			<tr>
				<td class="text-right">Interest Of Month
					'<?php echo date_format(date_sub(date_create($interset_row->overdue_date), date_interval_create_from_date_string($term . ' days')), getDateFormat()); ?>
					' is  <?php echo $interset_row->rate; ?>%:
				</td>
				<td class="text-left interes-<?php echo $jk?>">
					<?php if ((isset($invoice_data->interest_status) && $invoice_data->interest_status == 'Yes') || (isset($estimate_data->interest_status) && $estimate_data->interest_status == 'Yes')) {
						$id = 'interest_amt';
                        $totalConfirmed -= $monthDue;
					} else {
						$id = 'interest_amt_none';
					}
					?>
					<span id="<?php echo $id; ?>"><?php echo money($monthDue); ?>   |  (<?php echo money(($estimate_data->total_confirmed) ? $interset_row->interes_cost : 0); ?>)</span>


				</td>

			</tr>
            <?php endif; ?>
		<?php
		}
	}
	?>
	<?php if (floatval($estimate_data->discount_total)) : ?>
		<tr>
			<td class="text-right">Discount</td>
			<td class="text-left">
				<?php if(!$estimate_data->discount_in_percents) : ?>
				- <?php echo money($estimate_data->discount_total); ?>
				<?php else : ?>
				- <?php echo $estimate_data->discount_percents_amount; ?>% (<?php echo money($estimate_data->discount_total); ?>)
			<?php endif; ?>
			</td>
		</tr>
	<?php endif; ?>
	<tr>
		<td class="text-right"><?php echo $estimate_data->estimate_tax_value ? 'Tax (' . round($estimate_data->estimate_tax_value, 3) . '%)' : 'Tax' ?>:</td>
		<td class="text-left">
                <span class="popover-markup recommendation  p-right-5" <?php if(!empty($taxRecommendation) && !empty($taxRate) && $taxRate == $taxRecommendation || empty($taxRecommendation)) : ?> hidden <?php endif; ?>>
                        <i class="fa fa-warning trigger recommendationTax" style="color: red; cursor: pointer"></i>
                        <div class="head hide">Tax advice <button type="button" class="close pull-right" data-dismiss="popover">×</button></div>
                        <div class="content hide">
                            <div style="min-width: 200px;">
                                Recommended tax (<span class="taxRecommendationTitle"><?= isset($taxRecommendation) ? $taxRecommendation : "" ?></span>%)
                                <input type="button" class="btn btn-success btn-xs useTax" value="use">
                            </div>
                        </div>
                    </span>
			<a href="#" class="hst"
               <?php if ($estimate_data->estimate_hst_disabled == 1) : ?>style="text-decoration: line-through;"<?php endif; ?>
               data-toggle="popover" data-html="true" data-placement="top"
               data-content="<div style='' class='text-center'>
					<select class='form-control' id='dsblHst' data-estimate_id='<?php echo $estimate_data->estimate_id; ?>'>
					<?php foreach ($allTaxes as $tax) : ?>
						<option <?php if($tax['text'] == $taxText) : ?> selected <?php endif; ?> ><?php echo $tax['text'] ?></option>
					<?php endforeach; ?>
<!--						<option disabled>_________________________________</option>-->
<!--						<option value='1'--><?php //if ($estimate_data->estimate_hst_disabled == 1) : ?><!-- selected--><?php //endif; ?><!-->Tax (0%)</option>-->
<!--						<option value='1'--><?php //if ($estimate_data->estimate_hst_disabled == 1) : ?><!-- selected--><?php //endif; ?><!-->Zero --><?php //echo $estimate_data->estimate_tax_name ? $estimate_data->estimate_tax_name : 'Tax'; ?><!-- Payment</option>-->
<!--						<option value='2'--><?php //if ($estimate_data->estimate_hst_disabled == 2) : ?><!-- selected--><?php //endif; ?><!-->--><?php //echo $estimate_data->estimate_tax_name ? $estimate_data->estimate_tax_name : 'Tax'; ?><!-- included in the price</option>-->
			        </select>
			        <?php if(config_item('office_country') != 'United States of America') : ?>
                        <div class='form-check'>
                            <label class='form-check-label' for='taxIncluded'>
                                <?php echo $estimate_data->estimate_tax_name ? $estimate_data->estimate_tax_name : 'Tax'; ?> included in the price
                            </label>
                            <input class='form-check-input' type='checkbox' id='inclTax' <?php if ($estimate_data->estimate_hst_disabled == 2) : ?> checked <?php endif; ?> name='tax_included' value='2' id='taxIncluded'>
                        </div>
                    <?php endif; ?>
			   </div>"
               title=""
			   data-original-title="<button type=&quot;button&quot; class=&quot;close pull-right&quot; data-dismiss=&quot;popover&quot;>×</button>Tax selection&nbsp;&nbsp;&nbsp;">
				<?php echo money($estimate_data->total_tax); ?>
			</a>
		</td>
	</tr>
	<?php if ($payments_data && !empty($payments_data)) : ?>
		<?php foreach ($payments_data as $row) : ?>
			<tr>
				<td class="text-right"><?php echo ucfirst($row['payment_type']); ?> amount:</td>
                <td class="text-left"> - <?php echo money($row['payment_amount']); ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if($estimate_data->estimate_hst_disabled != 2) : ?>
	<tr>
		<td class="text-right">Add/Edit Discount</td>
		<td class="text-left">

			<?php
			$data1 = array(
				'name' => 'invoice_id',
				'id' => 'invoice_id',
				'value' => $estimate_data->estimate_id,
				'style' => 'margin:10px');

			echo form_hidden($data1);
			?>
			<a href="#overdue_edit_<?php echo $estimate_data->estimate_id; ?>" role="button"
			   class="btn btn-xs btn-default"
			   data-toggle="modal"><i class="fa fa-pencil"></i></a>

			<div id="overdue_edit_<?php echo $estimate_data->estimate_id; ?>" class="modal fade" tabindex="-1"
			     role="dialog"
			     aria-labelledby="myModalLabel" aria-hidden="true">
				<?php echo form_open(base_url() . "invoices/interset/" . $estimate_data->estimate_id, array('id' => 'intersetForm_' . $estimate_data->estimate_id)) ?>
				<!-- overdue  Files Header-->
				<div class="modal-dialog">
					<div class="modal-content panel panel-default p-n">
						<header class="panel-heading">Edit Interest
							for&nbsp;<?php echo $client_data->client_name; ?></header>
						<div class="modal-body">

							<div class="p-5">Discount:</div>
							<div class="p-5 row">
								<div class="col-md-10">
								<?php
								$edit_item_code_options = array(
									'name' => 'interest_rate',
									'value' => $estimate_data->discount_column,
									'style' => 'text-align: left;',
									'placeholder' => 'Add/Edit invoice discount Amount',
									'class' => 'form-control numeric',
									'id' => 'interest_rate_' . $estimate_data->estimate_id);
								?>
								<?php echo form_input($edit_item_code_options) ?>
								</div>
								<div class="col-md-2">
								<label class="checkbox">%
									<?php $checked = $estimate_data->discount_in_percents ? 'checked' : ''; ?>
									<?php echo form_checkbox(['name' => 'discount_percents', 'value' => 1, 'checked' => $checked]); ?>
								</label>
								</div>
								<div class="col-xs-12">
									<label for="discount_comment">Comment:</label>
									<textarea class="form-control" name="discount_comment" rows="7"><?= $estimate_data->discount_comment ?></textarea>
								</div>
							</div>
							<?php if (isset($estimate_data->interest_status)) : ?>
								<div class="p-5">
									<!-- enable/disable overdue  -->
									<?php

									if (set_value('check_overdue') == 'Yes' || (isset($estimate_data->interest_status) && $estimate_data->interest_status == 'Yes')) {
										$checked = TRUE;
									} else {
										$checked = FALSE;
									}
									$data = array(
										'name' => 'check_overdue',
										'id' => 'check_overdue_' . $estimate_data->estimate_id,
										'value' => 'Yes',
										'checked' => $checked,
										'style' => 'margin:10px');

									echo form_checkbox($data);
									echo 'Check checkbox to disable interest completely';
									?>
								</div>
							<?php endif; ?>
						</div>
						<div class="modal-footer">
							<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
							<button class='btn btn-info' name="submit"
							        onclick="update_interestForm('<?php echo $estimate_data->estimate_id; ?>'); return false;">
								Update
							</button>
						</div>
					</div>
				</div>
				<?php echo form_close() ?>
			</div>
		</td>
	</tr>
	<?php endif; ?>


	<tr>
		<td class="text-right"><strong class="pull-right">Total:</strong></td>
		<td id="totalSum" class="text-left">
			<div class="totalSum"><?php echo money($estimate_data->total_with_tax); ?>
			</div>
		</td>
	</tr>
    <?php if ($estimate_data->total_with_tax != $estimate_data->total_due) : ?>
        <tr>
            <td class="text-right"><strong class="pull-right">Total Due:</strong></td>
            <td id="totalSum" class="text-left">
                <div class="totalSum"><?php echo money($estimate_data->total_due); ?>
                </div>
            </td>
        </tr>
    <?php endif; ?>
	<?php
	if ($this->router->fetch_class() == 'estimates'): ?>
		<tr>
			<td class="text-right"><strong class="pull-right">Time:</strong></td>
			<td class="text-left">
				<?php echo number_format((float)($time), 2, '.', '') . " man hrs."; ?>
			</td>
		</tr>
		<tr>
			<td class="text-right"><strong class="pull-right">Estimated at:</strong></td>
			<td class="text-left">
				<?php
				if ($time):
                    echo money($estimate_data->total_all_services / $time) . ' per hour';
				else:
					echo money(0)." per hour";
				endif;
				?>
			</td>
		</tr>
	<?php endif; ?>

	<?php if (isset($invoice_data) && $invoice_data->in_finished_how) : ?>
	<tr>
		<td colspan="2"><strong>Finished How:</strong><br><?php echo $invoice_data->in_finished_how; ?></td>
	</tr>
		<?php endif; ?>
		<?php if (isset($invoice_data) && $invoice_data->in_extra_note_crew) : ?>
	<tr>
		<td colspan="2"><strong>Extra Note Crew:</strong><br><?php echo $invoice_data->in_extra_note_crew; ?></td>
	</tr>
		<?php endif; ?>
	</tbody>
<?php endif; ?>
</table>

</div>
<?php if(isset($invoice_data) && isset($invoice_data->invoice_notes) && $invoice_data->invoice_notes) : ?>
<div class="  panel panel-default p-n">
	<header class="panel-heading">Invoice Notes</header>
	<table class="table table-striped b-t bg-white m-n">
		<tbody>
			<tr>
				<td style="padding-top: 12px;white-space: pre-wrap; background-color:#fff;"><?php echo trim($invoice_data->invoice_notes); ?></td>
			</tr>
		</tbody>
	</table>
</section>
<?php endif;?>
<div class=" panel panel-default p-n">
<header class="panel-heading">Estimate Files</header>

<table class="table table-striped b-t bg-white m-n">
	<thead>
	<tr>
		<td colspan="2"><strong>File</strong></td>
	</tr>
</thead>
<tbody>
	
	<?php 
    $path = 'clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/';
	$pictures = bucketScanDir('uploads/' . $path); ?>
	
	<?php if(isset($pictures) && $pictures && !empty($pictures)) : ?>
		<?php foreach($pictures as $key=>$file) : ?>
			<?php
				$fileinfo = pathinfo('uploads/' . $path . $file);
			?>
			<tr>
				<?php $filepath = 'uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $file; ?>
				<td colspan="2">
					<a href="#" role="button" data-estimate_id="<?php echo $estimate_data->estimate_id; ?>" data-path="<?php echo $filepath; ?>" class="btn btn-xs btn-mini btn-danger pull-left m-r-sm deleteEstimatePhotoClass">
						<i class="fa fa-trash-o"></i>
					</a>
					<a target="_blank" class="pull-left" href="<?php echo base_url($filepath); ?>"><?php echo $file; ?></a>
					<?php if(!isset($schedule) || !$schedule) : ?>
					 <label class="checkbox pull-right m-l-md m-t-none m-b-none">
						<input type="checkbox" name="pdfFile" <?php if(array_search($filepath, $pdfFiles) !== FALSE) : ?>checked="checked"<?php endif; ?> data-file-name="<?php echo 'uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $file; ?>" class="">Print in PDF</label>
					<?php endif; ?>
				</td>
			</tr>

		<?php endforeach; ?>
	<?php endif; ?>
	<?php if(!isset($schedule) || !$schedule) : ?>
		<tr>
			<td style="padding-top: 10px;">Upload File</td>
			<td style="padding-top: 12px;">
					
					<span class="btn btn-primary est-file pull-left">Choose File
						<input type="file" name="estFile" id="fileToUploadEstimate" class="est-upload">
					</span>
					<?php //<label class="checkbox pull-left m-l-md m-t-xs m-b-none"><input type="checkbox" name="pdfFile" class="pdfFile ">Print in PDF</label>  ?>
				<img id="preloaderEstimate" src="/assets/img/ajax-loader.gif" style="display:none;">
			</td>
		</tr>
	<?php endif; ?>
</tbody>
 
</table>
    <input type="hidden" class="taxRecommendation" value="<?php echo isset($taxRecommendation) ? $taxRecommendation : null ?>">
    <input type="hidden" class="taxEstimate" value="<?php echo isset($taxEstimate) ? $taxEstimate : null ?>">

</section>
<?php $this->load->view('workorders/modals/workorders_damages_modal'); ?>
<!-- /Estimate data -->
<!-- /Estimate Information Display End -->
<?php $segment = NULL; ?>
<script>
	var segment = '<?php echo $this->router->fetch_class(); ?>';
	var estimate_id = '<?php echo isset($estimate_data) ? $estimate_data->estimate_id : ""; ?>';
	$(document).on('change', '#fileToUploadEstimate', function () {
        if ($('#fileToUploadEstimate').parent().is('.disabled') || this.files.length === 0) {
            return false;
        }

        ajaxFileUploadEstimate();
	});
	$(document).on("change", '[data-file-name]', function(){
		if(segment) {
			var name = $(this).data('file-name');
			var obj = $(this);
			$.ajax
			(
				{
					url: baseUrl + segment + '/ajax_pdf_file/',
					secureuri: false,
					type: 'post',
					dataType: 'json',
					data: {name: name, estimate_id: estimate_id},
					success: function (data, status) {
					}
				}
			);
		}
		
		return false;
	});
	function ajaxFileUploadEstimate() {
		$('#preloaderEstimate').show();
		$('#fileToUploadEstimate').parent().addClass('disabled');
		var path = "<?php echo $path; ?>";
		var estimate_no = "<?php echo $estimate_data->estimate_no; ?>";
		var suffix = 0;
		var checked = "";
		if($(".pdfFile").prop("checked"))
		{
			suffix = 1;
			checked = 'checked';
		}
		//starting setting some animation when the ajax starts and completes
		$("#loading")
			.ajaxStart(function () {
				$(this).show();
			})
			.ajaxComplete(function () {
				$(this).hide();
			});
		$.ajaxFileUpload
		(
			{
				url: baseUrl + 'estimates/ajax_save_file/',
				secureuri: false,
				fileElementId: 'fileToUploadEstimate',
				dataType: 'json',
				data: {path: path, estimate_no:estimate_no, suffix:suffix, entity: segment, estimate_id: estimate_id},
				success: function (data, status) {
					$('#preloaderEstimate').hide();
					$('#fileToUploadEstimate').parent().removeClass('disabled');
					if (data.status == 'error')
						alert('Error');
					else {
						$('#fileToUploadEstimate').parent().parent().parent().before('<tr><td colspan="2"><a href="#" role="button" data-estimate_id="' + estimate_id + '" data-path="' + data.filepath + '" class="btn btn-xs btn-mini btn-danger pull-left deleteEstimatePhotoClass m-r-sm"><i class="fa fa-trash-o"></i></a><a target="_blank" class="pull-left" href="' + baseUrl + data.filepath + '">' + data.filename + '</a><label class="checkbox pull-right m-l-md m-t-none m-b-none"><input type="checkbox" name="pdfFile" checked="checked" data-file-name="' + data.filepath + '" class="">Print in PDF</label></td></tr>');
						$('.pdfFile').attr('checked', false);
					}
                    $('#fileToUploadEstimate').removeAttr('disabled');
                    $('#fileToUploadEstimate').val('');
				},
				error: function (data, status, e) {
					alert(e);
					$('#preloaderEstimate').hide();
					$('#fileToUploadEstimate').parent().removeClass('disabled');
				}
			}
		)

		return false;

	}
	function changeStatus(value)
	{
	    var profilesModules = ['estimates', 'workorders', 'invoices'];
		var invoice = <?php echo isset($invoice) ? $invoice : 0; ?>;
		var id = $(value).data('id');
		var status = $(value).val();
		var nameStatus = $('#' + id + ' option:selected').text();
		$.post(baseUrl + 'estimates/ajax_change_status', {status: status, id:id, name:nameStatus}, function (resp) {
			
			var isUpdate = true;

			if (resp.status == 'error')
				alert('Ooops! Error...');
			else
			{
				if($(value).parents('td:first').find('.selectService').length)
				{
					if(status == 1 || status == 2)
						$(value).parents('td:first').find('.selectService').addClass('disabled');
					else
						$(value).parents('td:first').find('.selectService').removeClass('disabled');
				}

				if(resp.finish !== undefined && resp.finish == 0)
				{
					if($(value).parents('tr:first').data('workorder-id'))
					{
					    if(!invoice) {
                            if(confirm('All services for this Estimate was completed. Do you want change workorder status to Finished?'))
                            {
                                var woId = $(value).parents('tr:first').data('workorder-id');
                                $.post(baseUrl + 'workorders/ajax_change_workorder_status/', {workorder_id:$(value).parents('tr:first').data('workorder-id'),workorder_status:0}, function(resp) {

                                    $('#email-template-form').find('input[name="estimate_id"]').val(resp.workorder_data.estimate_id);
                                    callback = function(){
                                        ClientsLetters.init_modal(resp.invoice_email_template, 'ClientsLetters.invoice_email_modal');
                                    }

                                    DamagesModal.init(woId, false, callback);

                                }, 'JSON');
                            } else {
                                if(profilesModules.includes(segment))
                                    location.reload();
                            }
                        } else {
                            if(profilesModules.includes(segment))
                                location.reload();
                        }
					} else {
                        if(profilesModules.includes(segment))
                            location.reload();
                    }
				} else {
                    if(profilesModules.includes(segment))
				        location.reload();
                }
			}
			return false;
		}, 'json');
	}
</script>

<?php if (empty($schedule) || !$schedule) : ?>
<script type="text/javascript">
	function changeServicesPriority() {
		var arr = [];
		$.each($('.sortable tr'), function (key, val) {
			priority = key + 1;
			arr[key] = {id: $(val).data('estimate_service_id'), priority: priority};
		});
		$.post(baseUrl + 'estimates/ajax_priority_service', {data: arr, estimate_id: estimate_id}, function (resp) {
			if (resp.status == 'error')
				alert('Ooops! Error...');
			return false;
		}, 'json');
	}
	$("document").ready(function () {
		var invoice_id = $('#invoice_id').val();
		$('#check_overdue_' + invoice_id).click(function () {
			if ($('input[name="check_overdue"]:checked').length > 0) {
				$('#interest_rate_' + invoice_id).val('');
				$('#interest_rate_' + invoice_id).prop('disabled', true);
			} else {
				$('#interest_rate_' + invoice_id).prop('disabled', false);
			}
		});
		$(document).on('change', '#dsblHst, #inclTax', function () {
			var disabled = 0;
			var inclTax = 0;
			var value = 0;
			var estimate_id = $('#dsblHst').data('estimate_id');
			if ($('#dsblHst').val())
				disabled = $('#dsblHst').val();
            if($('#inclTax').prop('checked'))
                inclTax = $('#inclTax').val();
            if($('.taxRecommendation').val())
                value = $('.taxRecommendation').val();
			$.post(baseUrl + 'estimates/ajax_hst_disable', {disabled: disabled, estimate_id: estimate_id, inclTax: inclTax, recommendationValue: value}, function (resp) {
			    console.log(resp);
				if (resp.status == 'ok'){
                    location.reload();
                }
				else
					alert('Ooops! Error');
				$('[data-dismiss="popover"]').click();

            }, 'json');
		});
        $('.popover-markup>.trigger').popover({
            html: true,
            placement : 'top',
            title: function () {
                return $(this).parent().find('.head').html();
            },
            content: function () {
                return $(this).parent().find('.content').html();
            }

        }).click(function(e) {
            $(this).popover('toggle');
            e.stopPropagation();
        });
        $(document).on('click', '.useTax', function(e) {
            $('.hst').popover('show');
            let text = 'Tax (' + $('.taxRecommendation').val() +'%)';
            $('#dsblHst').val(text).trigger('change');
        });
	});
	function update_interestForm(id) {
		if ($('input[name="check_overdue"]:checked').length > 0) {
			var check_overdue = $('input[name="check_overdue"]:checked').val();
		} else {
			var check_overdue = 'No';
		}
		if (check_overdue == 'Yes') {
			$('#itemForm_' + id).subsmit();
		} else {
			var interest_rate = checkEmpty('interest_rate_' + id);
			if (interest_rate && checkNumber('interest_rate_' + id)) {
				$('#itemForm_' + id).subsmit();
			} else {
				return false;
			}
		}

	}

	function showError(id) {
		$("#" + id).css("background", "none repeat scroll 0 0 #F2DEDE");
		$("#" + id).css("box-shadow", "0 1px 1px rgba(0, 0, 0, 0.075) inset");
		$("#" + id).css("border-color", "#B94A48");
	}
	function hideError(id) {
		$("#" + id).css("background", "none repeat scroll 0 0 #DFF0D8");
		$("#" + id).css("box-shadow", "0 1px 1px rgba(0, 0, 0, 0.075) inset");
		$("#" + id).css("border-color", "#468847");
	}
	function checkEmpty(id) {
		var field = $('#' + id).val();
		if (field == '') {
			showError(id);
			return false;
		}
		hideError(id);
		return true;
	}
    function checkNumber(id) {
        var field = Number($('#' + id).val());
        if (typeof field != 'number') {
            showError(id);
            return false;
        }
        hideError(id);
        return true;
    }
</script>
<?php endif; ?>
</section>
