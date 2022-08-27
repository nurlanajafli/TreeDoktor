<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">


	<link rel="stylesheet" media="print" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>

	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/css/workorder_pdf.css" type="text/css" media="print">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/events/events.css'); ?>">

	<style>
		input,
		button,
		select,
		textarea{

		    font-size: 16px;
		    line-height: 1;
		}
		@page chapter1{
			size: auto;
		}
		@page chapter2{
			size: landscape;
		}
		div.chapter2 {
			page: chapter2;
		}
		div.chapter1 {
			page: chapter1;
		}
		.col-xs-4, .col-xs-5, .col-xs-6{ margin-right: 0; }
		.col-xs-4, .col-xs-5, .col-xs-6{ padding-right: 0; }

		.fake-input {
		    float: left;
		    width: 20px;
		    height: 20px;
		    border: 1px solid #9f9f9f;
		    background: #fff;
		    vertical-align: middle;
		    position: relative;
		    margin-right: 10px;
		    border-radius: 4px;
		}

		.label-col{
			background: #e8e8e8!important;
		}
		table td{
			padding: 10px 5px 10px;
		}
	</style>
</head>
<body>
    <?php if($event) : ?>
	<div class="row">
		<div class="col-xs-5">
			<h4 class="modal-title text-success text-left">REPORT FORM</h4>
		</div>
		<div class="col-xs-6">
			<h4 class="modal-title text-right"><strong>Workorder:</strong>&nbsp;<?php echo $event->workorder->workorder_no; ?> (<?php echo $event->workorder->estimate->lead->lead_address; ?>)</h4>
		</div>
	</div>

	<div class="event col-md-12">

		<br>
		<div class="row">
			<div class="col-md-4 col-sm-4 col-xs-5 p-left-0 p-right-0">
				<div class="row">
					<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
						<strong>Client:</strong>
					</div>
					<div class="col-md-7 col-sm-7 col-xs-5"><?php echo $event->workorder->estimate->client->client_name; ?></div>
				</div>
				<div class="row">
					<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
						<strong>Client Phone:</strong>
					</div>
					<div class="col-md-7 col-sm-7 col-xs-5"><?php echo $event->workorder->estimate->client->primary_contact->cc_phone_view; ?>
					</div>
				</div>
				<div class="row">
					<?php if ($event->workorder->estimate->client->client_contact != $event->workorder->estimate->client->primary_contact->cc_name) : ?>
						<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
							<strong>Contact Persone:</strong>
						</div>
						<div class="col-md-7 col-sm-7 col-xs-5">
							<?php echo $event->workorder->estimate->client->primary_contact->cc_name; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="col-md-3 col-sm-4 col-xs-7 p-left-0 p-right-0">
				<div class="row"><br><br></div>
				<div class="row">
					<div class="col-md-5 col-sm-5 col-lg-5 col-xs-3">
						<strong>Client Address:</strong>
					</div>
					<div class="col-md-7 col-sm-7 col-xs-8 p-left-0 p-right-0">
						<?php echo $event->workorder->estimate->lead->lead_address . ", " . $event->workorder->estimate->lead->lead_city; ?>&nbsp;
						<?php echo ($event->workorder->estimate->lead->lead_state)?$event->workorder->estimate->lead->lead_state . ", ":''; ?>
						<?php echo $event->workorder->estimate->lead->client_country; ?>,
                        <?php echo ($event->workorder->estimate->client->client_zip) ? $event->workorder->estimate->client->client_zip : $event->workorder->estimate->lead->lead_zip; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<hr>
			<div class="col-xs-12 p-left-0 p-right-0 h4" style="margin-bottom: 0">
				<strong class="text-warning">Timing:</strong>
			</div>
			<table class="table table-bordered" style="border-radius: 3px; margin-top:5px;">
				<tbody>
					<tr>
						<td><strong>Plan:</strong></td>
						<td>
							<strong>Start/Finish:</strong>&nbsp;<?php echo ($event->schedule_event->event_start)?date(getDateFormat() . ' ' . getTimeFormat(true), $event->schedule_event->event_start):' - '; ?><?php echo ($event->schedule_event->event_end)?'/'.date(getDateFormat() . ' ' . getTimeFormat(true), $event->schedule_event->event_end):''; ?>
						</td>

						<td>
							<?php $duration = ($event->schedule_event->event_end-$event->schedule_event->event_start)/3600; ?>
							<strong>Duration:</strong>&nbsp;<?php echo round($duration, 2); ?> hr.
						</td>

						<td>
							<strong class="text-success">Total: <?php echo round($duration+$travel_time, 2)*$event->team->members->count(); ?>&nbsp;mhr. (with travel)</strong>
						</td>
					</tr>
					<tr>
						<td><strong>Actual:</strong></td>
						<td>
                            <?php $event_date = date(getDateFormat(), strtotime($event->event_work->ev_date)); ?>
                            <?php $start_time = $end_time = ''; ?>
                            <?php $start_time = ($event->er_event_start_work)?strtotime($event->er_event_date.' '.$event->er_event_start_work):strtotime($event->event_work->ev_start_work); ?>
                            <?php $end_time = ($event->er_event_finish_work)?strtotime($event->er_event_date.' '.$event->er_event_finish_work):strtotime($event->event_work->ev_end_work); ?>

							<strong>Start/Finish:</strong>&nbsp;<?=date(getDateFormat() . ' ' . getTimeFormat(true), $start_time)?> / <?=date(getDateFormat() . ' ' . getTimeFormat(true), $end_time) ?>
						</td>

						<td>
							<?php
								$duration = 0;
								if(isset($event->er_event_start_work) && $event->er_event_finish_work) : ?>
								<?php $duration = (strtotime($event->er_event_date.' '.$event->er_event_finish_work)-strtotime($event->er_event_date.' '.$event->er_event_start_work))/3600; ?>
							<?php endif; ?>
							<strong>Duration:</strong>&nbsp;<?php echo round($duration, 2); ?> hr.
						</td>

						<td>
							<strong class="text-success"><?php
                                if(isset($event->er_on_site_time)) : ?>
								Total: <?php echo round($duration+((int)abs($event->er_travel_time_original)/3600), 2)*$event->team->members->count(); ?>
								<?php else : ?>
								Total: 0
								<?php endif; ?>
								&nbsp;mhr. (with travel)</strong>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="row">
			<div class="col-xs-12 p-left-0 p-right-0 h4" style="margin-bottom: 0">
				<strong class="text-warning">Details:</strong>
			</div>
			<table class="table table-bordered" style="border-radius: 3px; margin-top:5px;">

				<tbody>
					<tr>
						<td style="background: #e8e8e8; width: 20%;"><strong>Finished:</strong></td>
						<td style="text-align: center;">
						<?php if($event->er_event_status_work=="finished"): ?>
							Yes&nbsp;<i class="fa fa-check text-success text-active"></i>
						<?php else: ?>
							No&nbsp;<i class="fa fa-times text-danger"></i>
						<?php endif; ?>
						</td>

						<?php if($event->er_event_status_work=="finished"): ?>
							<td style="background: #e8e8e8; width: 20%;"><strong>Payment:</strong></td>
							<td style="text-align: center;">
								<?php if($event->er_event_payment=="yes"): ?>
									Yes&nbsp;<i class="fa fa-check text-success text-active"></i>
								<?php else: ?>
									No&nbsp;<i class="fa fa-times text-danger"></i>
								<?php endif; ?>
							</td>
						<?php else: ?>
							<td style="background: #e8e8e8; width: 20%;"><strong>Work Remaining:</strong></td>
							<td>
								<?php if($event->er_event_work_remaining && strlen($event->er_event_work_remaining)): ?>
								<?php echo $event->er_event_work_remaining; ?>
								<?php else: ?>
									-
								<?php endif; ?>
							</td>
						<?php endif; ?>
					</tr>

					<?php if($event->er_event_payment && $event->er_event_payment=="yes"): ?>
					<tr>
						<td style="background: #e8e8e8; width: 20%;"><strong>Payment Type:</strong></td>
						<td style="text-align: center;">
							<?php if($event->er_event_payment_type=="Cash"): ?>
								Cash
							<?php elseif($event->er_event_payment_type=="Check"): ?>
								Check
							<?php else: ?>
								No selected
							<?php endif; ?>
						</td>
						<td style="background: #e8e8e8; width: 20%;"><strong>Payment Amount:</strong></td>
						<td style="text-align: center;">
							<?php if($event->er_event_payment_amount): ?>
								<?php echo money(getAmount($event->er_event_payment_amount)); ?>
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>

					<?php if($event->er_expenses=="yes"): ?>
					<tr>
						<td style="background: #e8e8e8; width: 20%;"><strong>Expenses:</strong></td>
						<td style="text-align: center;">Yes</td>
						<td style="background: #e8e8e8; width: 20%;"><strong>Expenses Description:</strong></td>
						<td>
							<?php if($event->er_expenses_description): ?>
								<?php echo $event->er_expenses_description; ?>
							<?php else: ?>
								-
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>

					<?php if($event->er_event_damage=="yes"): ?>
					<tr>
						<td style="background: #e8e8e8; width: 20%;"><strong>Damage:</strong></td>
						<td style="text-align: center;">Yes</td>
						<td style="background: #e8e8e8; width: 20%;"><strong>Damage Description:</strong></td>
						<td>
							<?php if($event->er_event_damage_description && strlen($event->er_event_damage_description)): ?>
									<?php echo $event->er_event_damage_description; ?>
							<?php else: ?>
								-
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>
					<?php if($event->er_malfunctions_equipment=="yes"): ?>
					<tr>
						<td style="background: #e8e8e8; width: 20%;"><strong>Malfunctions Equipment:</strong></td>
						<td style="text-align: center;">Yes</td>
						<td style="background: #e8e8e8; width: 20%;"><strong>Malfunctions Description:</strong></td>
						<td>
							<?php if($event->er_team_fail_equipment): ?>

								<?php echo $event->er_team_fail_equipment; ?>

							<?php else: ?>
								--
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>

					<tr>
						<td style="background: #e8e8e8; width: 20%;"><strong>Note</strong></td>
						<td colspan="3">
							<?php if($event->er_event_description): ?>
								<?php echo $event->er_event_description; ?>
							<?php else: ?>
								No comments
							<?php endif; ?>
						</td>
					</tr>


				</tbody>
			</table>
		</div>



		<?php if(isset($files) && !empty($files)): ?>

		<div class="row">

			<div class="col-xs-12" style="padding-left: 0;margin-left: 0; margin-right: 0;"><strong>Photos:</strong></div>
			<br>
			<?php foreach($files as $file): ?>
                <?php //if (is_bucket_file($file)) : ?>
				    <img src="<?php echo site_url($file); ?>" style="height:320px; max-width: 49%;" />
                <?php //endif; ?>
			<?php endforeach; ?>
			<div class="clear"></div>

		</div>

		<?php endif; ?>
		<?php if(isset($files) && is_countable($files) && count($files)>8 && count($files)<=13): ?>
			<br><br><br><br><br><br><br><br>
		<?php endif; ?>
		<div class="row">
			<hr style="margin: 10px 0 10px">
			<div class="col-xs-6">
				<h5><strong>Team:</strong></h5>
				<?php if($event->team->members && $event->team->members->count()): ?>
                    <?php foreach($event->team->members as $key => $member): ?>

                        <span>
                        	<?php echo $member->full_name; ?><?php if($event->team->members->count() > ($key+1)): ?>,&nbsp;<?php endif; ?>
                        </span>

                	<?php endforeach; ?><br><br>
				<?php endif; ?>
				<div class="row">
					<div class="col-xs-4"><strong>Date:</strong></div>
					<div class="col-md-6 col-xs-6">
                        <?php if($end_time): ?>
                            <?php echo date(getDateFormat() . ' ' . getTimeFormat(true), $end_time); ?>
                        <?php else: ?>
                            <?php echo date(getDateFormat() . ' ' . getTimeFormat(true)); ?>
                        <?php endif; ?>
                    </div>
				</div>
				<div class="row">
					<div class="col-md-6 col-xs-4"><strong>COMPLETED BY:</strong></div>
					<div class="col-md-6 col-xs-6"><?php echo $event->workorder->estimate->client->client_name; ?></div>
				</div>

			</div>
			<div class="col-xs-5">
				<div class="row">
                        <?php if (is_bucket_file($signature_path)): ?>
                            <h5 class="text-left" style="margin-bottom: 0; margin-top: 0"><strong>Signature:</strong></h5>
                            <div style="padding-bottom: 10px; border-bottom: 1px solid #000; text-align: right;">
                                <img src="<?php echo $signature_path; ?>" height="75" style="height: 75px !important;">
                            </div>
                        <?php else: ?>
                            <h5 style="height: 85px;">&nbsp;</h5>
                            <h5 class="text-right" style="margin: 0; padding: 0"><strong>Signature:&nbsp;</strong>_______________________________</h5>
                        <?php endif; ?>
				</div>
			</div>
		</div>
	</div>
    <?php else : ?>
        <h1 class="text-center">No Report Data</h1>
    <?php endif; ?>
</body>
</html>
