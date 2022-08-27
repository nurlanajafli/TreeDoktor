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
		textarea {
		    
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

		
	</style>
</head>
<body>
<?php
if(!isset($safety_form) && isset($started_events['ev_tailgate_safety_form_array'])){
    $safety_form = $started_events['ev_tailgate_safety_form_array'];
}
?>
	<?php if($event) : ?>
	<h4 class="modal-title text-success">TAILGATE SAFETY MEETING FORM | PRE JOB SAFETY ASSESSMENT</h4>
	
	
	<div class="row">
		<div class="col-md-4 col-sm-4 col-xs-4">
			<div class="row">
				<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
					<strong>Date:</strong>
				</div>
				<div class="col-md-7 col-sm-7 col-xs-5"><?php echo getDateTimeWithDate(date('Y-m-d'), 'Y-m-d'); ?></div>
			</div>
			<div class="row">
				<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
					<strong>Client:</strong>
				</div>
				<div class="col-md-7 col-sm-7 col-xs-6"><?php echo $event->workorder->estimate->client->client_name; ?></div>
			</div>
			<div class="row">
				<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
					<strong>Client Phone:</strong>
				</div>
				<div class="col-md-7 col-sm-7 col-xs-5"><i class="fa  fa-phone"></i>&nbsp;
                    <a href="tel:<?php echo $event->workorder->estimate->client->primary_contact->cc_phone_view; ?>">
                        <?php echo $event->workorder->estimate->client->primary_contact->cc_phone_view; ?>
                    </a>
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

		<div class="col-md-3 col-sm-4 col-xs-3">
			<div class="row">
				<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
					<strong>Client Address:</strong>
				</div>
				<div class="col-md-7 col-sm-7 col-xs-6">
					<?php echo $event->workorder->estimate->lead->lead_address . ", " . $event->workorder->estimate->lead->lead_city; ?>&nbsp;
					<?php echo ($event->workorder->estimate->lead->lead_state)?$event->workorder->estimate->lead->lead_state . ", ":''; ?>
					<?php echo $event->workorder->estimate->lead->client_country; ?>,&nbsp;
					<?php echo isset($event->workorder->estimate->lead->client_zip) ? $event->workorder->estimate->lead->client_zip : $event->workorder->estimate->lead->lead_zip; ?>
				</div>
			</div>
		</div>
		<div class="col-md-5 col-sm-4 col-xs-4">
			<div class="col-md-5 col-sm-5 col-lg-5 col-xs-4">
				<div class="hospital-icon"></div>
			</div>
			<div class="col-md-7 col-sm-7 col-xs-6">
				<p class="text-success"><?php echo $hospital_name; ?></p>
				<?php echo $hospital_address; ?>
			</div>
		</div>

	</div>


	<div class="row">
		<div class="col-md-12">
			<h5 class="m-top-0 m-bottom-5"><strong>PROJECT OR WORK ACTIVITY</strong></h5>
                <?php foreach ($event_services as $key => $service_data) : ?>
				<div>
					<?php echo $service_data['service_description']; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="row m-bottom-0">
		<?php if(strlen($service_data['service_description'])>=3014 && strlen($service_data['service_description'])<=5010): ?>
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<?php else: ?>
			<hr class="m-top-5 m-bottom-5">
		<?php endif; ?>
		<div class="col-md-12"><h5 class="text-center m-top-5 m-bottom-0" style="margin-top:3px; padding: 0"><strong>HAZARDS</strong></h5></div>
	</div>

	<?php foreach(config_item('hazards') as $key => $row): ?>
		<div class="row">
		<?php foreach($row as $col_key=>$col_data): ?>
		<div class="col-md-4 col-lg-4 col-sm-4 <?php if($col_key!=1 && $col_key!=4): ?>col-xs-4<?php else: ?>col-xs-3<?php endif; ?>" style="<?php if($col_key==0 || $col_key==3): ?><?php else: ?>margin:0; padding:0;<?php endif; ?><?php if($col_key==1 || $col_key==4): ?>width:31%;<?php endif;?>">
			<h6 class="m-top-0 m-bottom-0" style="margin-top: 3px;"><strong><?php echo $col_data['label']; ?></strong></h6>
			
			<?php foreach($col_data['data'] as $checkbox_key => $checkbox_row): ?>
				<div>
					<input type="checkbox" name="" <?php if(array_search($checkbox_row, (isset($safety_form['hazards']) && is_array($safety_form['hazards']))?$safety_form['hazards']:[])!==FALSE): ?>checked="checked"<?php endif; ?>>
					<?php echo $checkbox_row; ?>
				</div>
			<?php endforeach; ?>
			
			<?php if(!isset($safety_form['hazards_text_'.$key.'_'.$col_key])): ?> 
				<div>Other:_________________________</div>
			<?php else: ?>
				<div><strong>Other:</strong><?php echo $safety_form['hazards_text_'.$key.'_'.$col_key]; ?></div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<div class="row">
		<?php if(strlen($service_data['service_description'])>=1450 && strlen($service_data['service_description'])<=3010): ?>
		<br><br><br><br><br><br><br><br><br><br>
		<?php else: ?>
			<hr class="m-top-5 m-bottom-5">
		<?php endif; ?>
		<div class="col-md-12"><h5 class="text-center" style="margin-bottom: 0; margin-top:0px; padding: 0"><strong>CONTROLS</strong></h5></div>
	</div>
	<?php foreach(config_item('controls') as $key => $row): ?>
		<div class="row">
		<?php foreach($row as $col_key=>$col_data): ?>
		<div class="col-md-4 <?php if($col_key==0 || $col_key==2): ?>col-xs-4<?php else: ?>col-xs-3<?php endif; ?>" style="<?php if($col_key==0 || $col_key==3): ?><?php else: ?>margin:0; padding:0;<?php endif; ?><?php if($col_key==1 || $col_key==4): ?>width:31%;<?php endif;?>">
			<h6 style="margin-bottom: 0; margin-top: 3px;"><strong><?php echo $col_data['label']; ?></strong></h6>
			
			<?php foreach($col_data['data'] as $checkbox_key=>$checkbox_row): ?>
			<div>
					<input type="checkbox" name="" <?php if(array_search($checkbox_row, (isset($safety_form['controls']) && is_array($safety_form['controls']))?$safety_form['controls']:[])!==FALSE): ?>checked="checked"<?php endif; ?>>
					<?php echo $checkbox_row; ?>
				</div>
			<?php endforeach; ?>
			
			<?php if(!isset($safety_form['controls_text_'.$key.'_'.$col_key])): ?> 
				<div>Other:_________________________</div>
			<?php else: ?>
				<div><strong>Other:</strong><?php echo $safety_form['controls_text_'.$key.'_'.$col_key]; ?></div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	
	<?php if(strlen($service_data['service_description'])>=250 && strlen($service_data['service_description'])<=530): ?>
		<br><br><br><br><br>
	<?php else: ?>
		<hr  class="m-top-5" style="margin-bottom: 5px;">
	<?php endif; ?>
	<div class="row" style="height: 100px;">
		<div class="col-md-4 col-xs-6">
			<h5 style="margin-bottom: 0; margin-top: 0; padding: 0;"><strong>Team:</strong></h5>
			<?php
            if($event->team->members && $event->team->members->count()): ?>
				<?php foreach($event->team->members as $key => $member): ?>
                	<span style="margin-bottom: 0"><?php echo $member->full_name; ?></span><?php if($event->team->members->count()-1 > $key): ?>,<?php endif; ?>
            	<?php endforeach; ?>
			<?php endif; ?>
			<br><br><br><br>

			<h5 style="margin-bottom: 0; margin-top: 0;"><strong>Date:&nbsp;</strong><?php echo (isset($started_events['ev_start_time'])) ? getDateTimeWithDate($started_events['ev_start_time'], 'Y-m-d H:i:s') : ' - '; ?></h5>

			<h5 style="margin-bottom: 0; margin-top: 0"><strong>COMPLETED BY:&nbsp;</strong><?php echo ($event->team->team_leader)?$event->team->team_leader->full_name:null; ?></h5>
		
		</div>
		
		<div class="col-md-8 col-xs-5">

            <?php if (isset($signature['teamlead']) && $signature['teamlead']): ?>
                <h5 class="text-left" style="margin-bottom: 0; margin-top: 0">
                    <strong>
                        <?php echo $signature['teamlead']->user->full_name; ?>:
                    </strong>
                </h5>

                <div style="padding-bottom: 10px; border-bottom: 1px solid #000; text-align: right;">
                    <img src="<?php echo $signature['teamlead']->safety_pdf_sign; ?>" height="75" style="height: 75px !important;">
                </div>
                <?php
                if($signature['team'] && count($signature['team'])) : ?>
                    <?php foreach($signature['team'] as $user) : ?>
                        <h5 class="text-left" style="margin-bottom: 0; margin-top: 0">
                            <strong>
                                <?php echo $user->user->full_name; ?>:
                            </strong>
                        </h5>
                        <div style="padding-bottom: 10px; border-bottom: 1px solid #000; text-align: right;">
                            <?php
                            $img = str_replace('data:image/png;base64,', '', $user->safety_pdf_sign);
                            $img = str_replace('[removed]', '', $img);
                            $img = str_replace(' ', '+', $img);
                            $data = base64_decode($img);
                            $im = @imagecreatefromstring($data); ?>
                            <?php if($im!==false): ?>
                                <img src="<?php echo $user->safety_pdf_sign; ?>" height="75" style="height: 75px !important;">
                            <?php else: ?>
                                <h5 style="height: 85px;">&nbsp;</h5>
                                <h5 class="text-right" style="margin: 0; padding: 0"><strong>Signature:&nbsp;</strong>_______________________________</h5>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php if (isset($signature_path) && is_bucket_file($signature_path)): ?>
                <h5 class="text-left" style="margin-bottom: 0; margin-top: 0"><strong>Signature:</strong></h5>
                <div style="padding-bottom: 10px; border-bottom: 1px solid #000; text-align: right;">
                    <img src="<?php echo $signature_path; ?>" height="75" style="height: 75px !important;">
                </div>
                <?php else: ?>
                <h5 style="height: 85px;">&nbsp;</h5>
                <h5 class="text-right" style="margin: 0; padding: 0"><strong>Signature:&nbsp;</strong>_______________________________</h5>
                <?php endif; ?>
            <?php endif; ?>
		</div>
	</div>
	<?php else : ?>
        <h1 class="text-center">No Report Data</h1>
	<?php endif; ?>
</body>
</html>
