<?php if(!isset($sorted)) : ?>
<style>
    .popover {
        max-width: 360px;
    }
</style>
<div class="table-responsive">
	<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
		<thead>
		<tr>
			<th width="200px"><a href="#" class="sort" data-status="<?php echo $current_status->est_status_id; //mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)); ?>" data-field="client_name" data-type="ASC">
				Client Name<i class="fa fa-filter desc"></i></a></th>
            <th width="50px">No</th>
			<th width="300px"><a href="#" class="sort" data-status="<?php echo $current_status->est_status_id; //mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)); ?>" data-field="lead_address" data-type="ASC">
				Address<i class="fa fa-filter desc"></i></a></th>
			<!--<th width="90px"><a href="#" class="sort" data-status="<?php /*echo $current_status->est_status_id; //mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)); */?>" data-field="estimate_count_contact" data-type="ASC">
				Count&nbsp;<i class="fa fa-filter desc"></i><br>Contacts</a></th>
			<th width="110px"><a href="#" class="sort" data-status="<?php /*echo $current_status->est_status_id; //mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)); */?>" data-field="estimate_last_contact" data-type="ASC">
				Last&nbsp;&nbsp;&nbsp;<i class="fa fa-filter desc"></i><br>Contact</a></th>-->
            <?php if(config_item('default_mail_driver') === 'amazon') : ?>
            <th width="75">
                Last Email
                <span class="btn btn-xs btn-rounded" tabindex="0" data-html="true" data-container="body" data-toggle="popover" title="" role="button" data-trigger="focus" data-content="
                    <div class='row'><div class='col-md-1'><span class='badge bg-info' title='clicked' style='cursor: pointer;'>A</span></div><div class='col-md-10 p-right-0'>Accepted - The email request to send is accepted and the message is queued.</div></div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'><div class='col-md-1'><span class='badge bg-info' title='clicked' style='cursor: pointer;'>D</span></div><div class='col-md-10 p-right-0'>Delivered - The email was successfully delivered to the client.</div></div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'><div class='col-md-1'><span class='badge bg-success' title='clicked' style='cursor: pointer;'>O</span></div><div class='col-md-10 p-right-0'>Opened - The client received and opened the email.</div></div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'><div class='col-md-1'><span class='badge bg-success' title='clicked' style='cursor: pointer;'>C</span></div><div class='col-md-10 p-right-0'>Clicked - The client clicked one or more links in the email.</div></div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'><div class='col-md-1'><span class='badge bg-danger' title='clicked' style='cursor: pointer;'>R</span></div><div class='col-md-10 p-right-0'>Rejected - ArboStar email provider rejected the request to send/forward the email.</div></div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'><div class='col-md-1'><span class='badge bg-danger' title='clicked' style='cursor: pointer;'>B</span></div><div class='col-md-10 p-right-0'>Bounce - The client’s mail server rejected the email. Client’s email address may be incorrect.</div></div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'><div class='col-md-1'><span class='badge bg-danger' title='clicked' style='cursor: pointer;'>C</span></div><div class='col-md-10 p-right-0'>Complained - The email was marked as a spam by the client.</div></div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'><div class='col-md-1'><span class='badge bg-danger' title='clicked' style='cursor: pointer;'>U</span></div><div class='col-md-10 p-right-0'>Unsubscribed - The client clicked on the unsubscribe link in the email header.</div></div>
                    <div class='line line-dashed line-sm pull-in'></div>
                    <div class='row'><div class='col-md-1'><span class='badge bg-danger' title='clicked' style='cursor: pointer;'>E</span></div><div class='col-md-10 p-right-0'>Error - The email wasn’t sent because of a template rendering issue. Some email template data may be missing.</div></div>
                    " data-original-title="Date and state of the last sent email with Estimate">
                    <i class="fa fa-question-circle" aria-hidden="true"></i>
                </span>
            </th>
            <?php endif; ?>
			<th width="110px"><a href="#" class="sort" data-status="<?php echo $current_status->est_status_id; //mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)); ?>" data-field="date_created" data-type="ASC">
				Date&nbsp;<i class="fa fa-filter desc"></i></a></th>
			<th width="130px">Estimator</th>
			<th width="100px">Status</th>
			<th width="85px">Action</th>
		</tr>
		</thead>
		<tbody>
	<?php endif; ?>
		<?php if (isset($estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate']) && $estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate'] && $estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate']->num_rows() != 0) { ?>
			<?php foreach ($estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate']->result() as $rows) { ?>
				<tr>
					<td width="200"><?php echo anchor($rows->client_id, $rows->client_name); ?></td>
					<td><?php echo $rows->estimate_no ?></td>
					<td><?php echo $rows->lead_address . ",&nbsp;" . $rows->lead_city . ",&nbsp;" . $rows->lead_state . ",&nbsp;" . $rows->lead_zip; ?></td>
                    <?php if(config_item('default_mail_driver') === 'amazon') : ?>
                    <td width="75">
                        <?php if($rows->email_status) : ?>
                            <?php echo getDateTimeWithTimestamp(strtotime($rows->email_created_at)); ?>
                            <span class="badge bg-<?php if(in_array($rows->email_status, ['accepted', 'delivered'])) echo 'info'; elseif (in_array($rows->email_status, ['opened', 'clicked'])) echo 'success'; else echo 'danger'; ?>" title="<?php echo $rows->email_status; ?>" style="cursor: pointer;"><?php echo strtoupper(substr($rows->email_status, 0, 1)); ?></span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
					<td width="75"><?php echo getDateTimeWithTimestamp($rows->date_created); ?></td>
					<td width="100"><?php echo $rows->firstname . ' ' . $rows->lastname; ?></td>
					<td width="100"><?php echo $rows->status; ?></td>
					<td width="70">
						<?php echo anchor('estimates/edit/' . $rows->estimate_id, '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default"') ?>
						<?php echo anchor($rows->estimate_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
					</td>
				</tr>
			<?php } ?>
			<?php if ($estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate_links']) : ?>
				<tr>
					<td colspan="8" style="color:#FF0000;">
						<?php echo $estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate_links']; ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php } else { ?>
			<tr>
				<td colspan="8" style="color:#FF0000;">No record found</td>
			</tr>
		<?php } ?>
	<?php if(!isset($sorted)) : ?>
		</tbody>
	</table>
</div>
<?php endif; ?>
