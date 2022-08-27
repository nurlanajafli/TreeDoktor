<!-- Issued Invoices -->
<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
	<thead>
	<tr>
		<th width="150px">Client Name</th>
		<th width="180px">Address</th>
		<th width="140px">Phone</th>
		<th width="">Estimator</th>

		<th width="105px">
            <?php $orderRule = $sortBy == 'invoice_no' && $sortRule == 'desc' ? 'asc' : 'desc'; ?>
            <?php $sortQueryStringSegments = $this->input->get() && is_array($this->input->get()) ? $this->input->get() : []; ?>
            <?php if(isset($sortQueryStringSegments['sort_by'])) unset($sortQueryStringSegments['sort_by']); ?>
            <?php if(isset($sortQueryStringSegments['sort_rule'])) unset($sortQueryStringSegments['sort_rule']); ?>
            <?php $url = 'invoices/' . $type . '/1/'; ?>
            <?php $estimator = empty($estimator) ? null : $estimator; ?>
            <?php $estimator = !empty($filter) && empty($estimator) ? 0 : $estimator; ?>
            <?php $url .= !empty($estimator) ? $estimator . '/' : NULL; ?>
            <?php $url .= !empty($filter) ? $filter . '/' : NULL; ?>

            <a href="<?php echo base_url($url) . '?' . http_build_query($sortQueryStringSegments) . '&sort_by=invoice_no&sort_rule=' . $orderRule; ?>">
                No
            </a>
        </th>
		<th width="120px">Total</th>
		<th width="135px">Total With Tax</th>
		<th width="120px">Paid</th>
		<th width="120px">Due</th>
		<th width="110px">
            <?php $orderRule = $sortBy == 'date_created' && $sortRule == 'desc' ? 'asc' : 'desc'; ?>
            <?php $sortQueryStringSegments = $this->input->get() && is_array($this->input->get()) ? $this->input->get() : []; ?>
            <?php if(isset($sortQueryStringSegments['sort_by'])) unset($sortQueryStringSegments['sort_by']); ?>
            <?php if(isset($sortQueryStringSegments['sort_rule'])) unset($sortQueryStringSegments['sort_rule']); ?>

            <a href="<?php echo base_url($url) . '?' . http_build_query($sortQueryStringSegments) . '&sort_by=date_created&sort_rule=' . $orderRule; ?>">
                Date
            </a>
        </th>
		<?php if((int)$status->is_overdue) : ?>
			<?php $totalOverdue = 0; ?>
			<th>Overdue</th>
		<?php endif;?>
		<th width="260px">Notes</th>
		<?php if((int)$status->completed || (int)$status->is_sent) : ?>
			<th>Like</th>
		<?php endif;?>
		
		
		<th width="110px">Act</th>
	</tr>
	</thead>
	<tbody>
	<?php if (isset($invoices) && !empty($invoices)) : ?>
	<?php $sumTotal = $sumDue = $sumTotalWithTax = $sumPaid = 0; ?>
		<?php foreach ($invoices as $rows) :
			$Overdue = get_interest_sum($rows->id);
			?>
			
			<tr>
				<td width=""><?php echo anchor( $rows->client_id, $rows->client_name); ?></td>
				<td><?php echo $rows->client_address . ",&nbsp;" . $rows->client_city; ?></td>
				<td>
					<a href="#" class="<?php if($rows->cc_phone == numberTo($rows->cc_phone)) : ?>text-danger<?php else : ?>createCall<?php endif;?>" data-client-id="<?php echo $rows->client_id; ?>" data-number="<?php echo substr($rows->cc_phone, 0, 10);?>">
						<?php echo numberTo($rows->cc_phone); ?>
					</a>
				</td>
				<td><?php echo $rows->emailid; ?></td>
				<td><?php echo anchor($rows->invoice_no, $rows->invoice_no); ?></td>
                <td><?php echo money(round($rows->total, 2) ? round($rows->total, 2) : 0);
                    $sumTotal += ($rows->total) ? $rows->total : 0; ?></td>
                <td><?php echo money(round($rows->total_with_tax, 2) ? round($rows->total_with_tax, 2) : 0);
                    $sumTotalWithTax += ($rows->total_with_tax) ? $rows->total_with_tax : 0; ?></td>
                <td><?php echo money(round($rows->payments_total, 2) ? round($rows->payments_total, 2) : 0);
                    $sumPaid += ($rows->payments_total) ? $rows->payments_total : 0; ?></td>
                <td><?php echo money(round($rows->due, 2) ? round($rows->due, 2) : 0);
                    $sumDue += ($rows->due) ? $rows->due : 0; ?></td>
<!--				<td>--><?php //echo date('Y-m-d', strtotime($rows->date_created)); ?><!--</td>-->
				<td><?php echo getDateTimeWithDate($rows->date_created, 'Y-m-d') ?></td>
				<?php if((int)$status->is_overdue) : ?>
					<td><?php
						//if (isset($rows->interest_status) && $rows->interest_status == 'Yes') 
							echo money($Overdue);
							$totalOverdue += $Overdue;  
						
						?>
					</td>
				<?php endif;?>
				<td>
					<textarea onblur="saveNote($(this));" data-val="<?php echo htmlspecialchars($rows->invoice_notes); ?>" class="form-control schedText-<?php echo $rows->id; ?>" data-in_id="<?php echo $rows->id; ?>" placeholder="Ctrl+Enter"><?php echo htmlspecialchars($rows->invoice_notes); ?></textarea>
					<!--data-gramm_editor="false"-->
				</td>
				
				<?php if((int)$status->completed || (int)$status->is_sent) : ?>
					<td>
						<?php if($rows->invoice_like === '1') : ?>
							<img src="<?php echo base_url('assets/img/up-sm.png'); ?>" height="15" class="m-l-xs">
						<?php elseif($rows->invoice_like === '0') : ?>
							<img src="<?php echo base_url('assets/img/down-sm.png'); ?>" height="15" class="m-l-xs">
						<?php endif; ?>
					</td>
				<?php endif; ?>
				
				<td>
					<?php // echo anchor('invoices/edit/'.$rows->id,'<i class="icon-pencil"></i>', 'class="btn btn-mini"')?>
					<?php echo anchor($rows->invoice_no . '/pdf', '<i class="fa fa-file"></i>', 'class="btn btn-xs btn-default"') ?>
					<?php echo anchor($rows->invoice_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
                    <?php $this->load->view('qb/partials/qb_logs', ['lastQbTimeLog' => $rows->invoice_last_qb_time_log, 'lastQbSyncResult' => $rows->invoice_last_qb_sync_result, 'module' => 'invoice', 'entityId' => $rows->id, 'entityQbId' => $rows->invoice_qb_id]); ?>

				</td>
				<script>
					$(document).ready(function () {
						ctrl = false; // признак нажатой клавиши "Ctrl" 
						$('.schedText-<?php echo $rows->id; ?>').keydown(function(event){
						  switch (event.which) {
							//case 13: return false; // отключаем стандартное поведение
							case 17: ctrl = true; // клавиша Ctrl нажата и удерживается
						  }
						});
						$('.schedText-<?php echo $rows->id; ?>').keyup(function(event){
							var obj = $(this);
							var id = $(obj).attr('data-in_id');
							var text = $(obj).val();
							
							switch (event.which) {
								case 13:
									if (ctrl){ 
										$.post(baseUrl + 'invoices/ajax_edit_notes', {text:text, id:id}, function (resp) {
											if (!resp)
												alert('Error');
											else
												$(obj).blur();
											return false;
										}, 'json'); 
										return false;
									  }
								case 17: ctrl = false;  
							}          
						});
					});
			</script>
			</tr>
			
		<?php endforeach; ?>
		<script>
			function saveNote(obj)
			{
				var defVal = $(obj).attr('data-val');
				var text = $(obj).val();
				var id = $(obj).attr('data-in_id');
				if(defVal !== text)
				{
					$.post(baseUrl + 'invoices/ajax_edit_notes', {text:text, id:id}, function (resp) {
						if (!resp)
							alert('Error');
						return false;
					}, 'json'); 
				}
				return false;
			}
		</script>
			<tfoot>
				<tr>
					<td colspan="5" ></td>
                    <td><strong><?php echo money(round($sumTotal, 2) ? round($sumTotal, 2) : 0); ?></strong></td>
                    <td><strong><?php echo money(round($sumTotalWithTax, 2) ? round($sumTotalWithTax, 2) : 0); ?></strong></td>
                    <td><strong><?php echo money(round($sumPaid, 2) ? round($sumPaid, 2) : 0); ?></strong></td>
                    <td colspan="<?php if ($type == 2) : ?>2<?php elseif ($type != 1) : ?>5 <?php else : ?>4<?php endif; ?>">
                        <strong><?php echo money(round($sumDue, 2) ? round($sumDue, 2) : 0); ?></strong></td>
				<?php if($type == 2) : ?>
					<td colspan="3"><strong><?php
						//if (isset($rows->interest_status) && $rows->interest_status == 'Yes') 
                            echo money(round($totalOverdue, 2));
						
						?></strong>
					</td>
				<?php endif;?>
				</tr>
			</tfoot>
	<?php  else : ?>
		<tr>
			<td colspan="10" style="color:#FF0000;">No record found</td>
		</tr>
	<?php endif;  ?>
	</tbody>
</table>
<footer class="panel-footer">
	<div class="row">
		<div class="col-sm-5 text-right text-center-xs pull-right">
			<?php echo isset($links) ? $links : ''; ?>
		</div>
	</div>
</footer>

<!-- /Issued Invoices end -->
