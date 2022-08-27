<!--Content-->
<section class="panel panel-default p-n client_payments">
	<!-- Client Files Header-->
	<header class="panel-heading">All client payments</header>
    <div class="table-responsive">
	<!-- Client Files Data -->
        <table class="table table-striped b-t b-light">
            <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Method</th>
                <th>Estimate</th>
                <th width="110px">Date</th>
                <th>Amount</th>
                <th>File</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <!-- Payments -->
			<?php $pay_method = $this->config->item('payment_methods');?>
            <?php if ($client_payments) : ?>
                <?php foreach ($client_payments as $row) : ?>
                    <tr class="payments<?php if($row['payment_alarm']) : ?> bg-danger<?php endif; ?>" data-id="<?php echo $row['payment_id']; ?>"<?php if($row['payment_alarm']) : ?> title="Payment Was Declined"<?php endif; ?>>
                        <td<?php if($row['payment_alarm']) : ?> style="background-color: #fb6b5b;"<?php endif; ?>>
                            <?php echo $row['payment_id']; ?>
                        </td>
                        <td<?php if($row['payment_alarm']) : ?> style="background-color: #fb6b5b;"<?php endif; ?>><?php echo ucfirst($row['payment_type']); ?></td>
                        <td<?php if($row['payment_alarm']) : ?> style="background-color: #fb6b5b;"<?php endif; ?>><?php echo $pay_method[$row['payment_method_int']]??'-'; ?></td>
                        <td<?php if($row['payment_alarm']) : ?> style="background-color: #fb6b5b;"<?php endif; ?>><?php echo $row['estimate_no']; ?></td>
<!--                        <td--><?php //if($row['payment_alarm']) : ?><!-- style="background-color: #fb6b5b;"--><?php //endif; ?><?php //echo date('Y-m-d', $row['payment_date']); ?><!--</td>-->
                        <td<?php if($row['payment_alarm']) : ?> style="background-color: #fb6b5b;"<?php endif; ?>><?php echo getDateTimeWithTimestamp($row['payment_date']); ?></td>
                        <td<?php if($row['payment_alarm']) : ?> style="background-color: #fb6b5b;"<?php endif; ?>><?php echo money($row['payment_amount']); ?></td>
                        <td<?php if($row['payment_alarm']) : ?> style="background-color: #fb6b5b;"<?php endif; ?>>
                            <?php if ($row['payment_file']) : ?>
                                <a class="btn btn-success btn-xs pull-left" type="button" target="_blank"
                                   href="<?php echo base_url('uploads/payment_files/' . $row['client_id'] . '/' . $row['estimate_no'] . '/' . $row['payment_file']); ?>">
                                    <i class="fa fa-picture-o"></i>
                                </a>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($this->session->userdata('user_type') == "admin") : ?>
                                <a href="#edit_payment" data-payment-id="<?php echo $row['payment_id']; ?>" role="button" class="btn btn-xs btn-default"
                                   data-toggle="modal" title="Edit payment"><i class="fa fa-pencil"></i></a>
                            <?php endif; ?>
                            <?php if($row['payment_method_int'] == config_item('default_cc')) : ?>
                                <a href="#payment_details" data-payment-id="<?php
                                echo $row['payment_id']; ?>" role="button" class="btn btn-xs btn-default"
                                   data-toggle="modal" title="Transaction details"><i class="fa fa-eye"></i></a>
                                <?php
                                $this->load->view('qb/partials/qb_logs', [
                                    'lastQbTimeLog' => $row['payment_last_qb_time_log'],
                                    'lastQbSyncResult' => $row['payment_last_qb_sync_result'], 'module' => 'payment',
                                    'entityId' => $row['payment_id'], 'entityQbId' => $row['payment_qb_id'],
                                    'class' => ''
                                ]); ?>
                                <?php
                                if ($this->session->userdata('user_type') == "admin" && floatval($row['payment_amount']) > 0) : ?>
                                    <a href="#payment_refund" data-payment-id="<?php
                                    echo $row['payment_id']; ?>" data-amount="<?php
                                    echo $row['payment_amount']; ?>" data-fee="<?php
                                    echo $row['payment_fee']; ?>" role="button" class="btn btn-xs btn-danger"
                                       title="Transaction refund" data-toggle="modal">
                                        <i class="fa fa-level-up"></i>
                                    </a>
                                <?php
                                endif; ?>
                            <?php else : ?>
                                <?php $this->load->view('qb/partials/qb_logs', ['lastQbTimeLog' => $row['payment_last_qb_time_log'], 'lastQbSyncResult' => $row['payment_last_qb_sync_result'], 'module' => 'payment', 'entityId' => $row['payment_id'], 'entityQbId' => $row['payment_qb_id'], 'class' => '']); ?>
                                <?php if(isAdmin()): ?>
                                    <a href="#" data-payment-id="<?php echo $row['payment_id']; ?>" role="button"
                                       class="btn btn-xs btn-danger deletePayment" title="Delete payment">
                                        <i class="fa fa-trash-o"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <tbody>
        </table>
    </div>
</section>
