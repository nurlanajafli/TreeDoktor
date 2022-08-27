<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/css/workorder_pdf.css" type="text/css" media="print">

</head>
<body>
<div class="workoder_curr_date">
	
</div>
<div class="workorder_number">
	<table width="100%" style="margin-bottom: 10px;">
		<tr>
			<td style="font-size: 16px;">
				<strong>WORKORDERS OVERVIEW</strong>
			</td>
			<td align="right" style="font-size: 20px;">
				<strong>
                    <?php if($team->team_date_start == $team->team_date_end): ?>
					<?php echo date('d M, Y', strtotime($team->team_date_start)); ?>
                    <?php else: ?>
                        <?php echo date('d M, Y', strtotime($team->team_date_start)); ?> - <?php echo date('d M, Y', strtotime($team->team_date_end)); ?>
                    <?php endif; ?>
				</strong>
			</td>
		</tr>
	</table>
</div>



<div class="">
	<div style="width:48%; display:inline-block;float: left;">

        <?php foreach($team->schedule_teams_members_user as $uk=>$member) : ?>
        <div style="background:#36e036; margin-bottom:2px; text-align: center; border-radius: .25em; border:1px solid #000; display: block;text-overflow: ellipsis;overflow: hidden; font-size: 17px;">
            <label>
                <?php if($member->id == $team->team_leader_user_id) echo '* '; ?>
                <?php echo $member->full_name; ?>
            </label>
        </div>
        <?php endforeach; ?>
        <?php foreach($team->schedule_teams_equipments as $ek=>$equipment) : ?>
            <div style="background:#fff; margin-bottom:2px; text-align: center; border-radius: .25em; border:1px solid #000; display: block;text-overflow: ellipsis;overflow: hidden; font-size: 17px;">
                <label>
                    <?php echo $equipment->eq_name; ?>
                    <?php if(isset($drivers[$equipment->eq_id]) && isset($drivers[$equipment->eq_id]->id)) : ?>
                    (<?php echo $drivers[$equipment->eq_id]->emailid; ?>)
                    <?php endif; ?>
                </label>
            </div>
        <?php endforeach; ?>

	</div>
	<div style="width:48%; display:inline-block;float: right;">
	<?php if($team->events->count()) : ?>
		<div style="text-decoration: underline;">JOBS</div>
		<?php $num = 1; ?>
		<div style="display:inline-block;">
		<?php foreach($team->events as $key => $event) : ?>
			
			<div style="">
				<label>
					<strong>
						<?php echo $num++;?>)
						<a href="<?php echo base_url($event->workorder->workorder_no); ?>" style="color: #000;">
							<?php echo $event->workorder->estimate->lead->lead_address; ?>, <?php echo $event->workorder->estimate->lead->lead_zip; ?>
						</a>
					</strong>
				</label>
			</div>
		<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if(isset($tools) && $tools != '') : ?>
		<br>
		<div style="text-decoration: underline;">TOOLS: <?php echo $tools; ?></div>
	<?php endif; ?>
	</div>
</div>



<div style="text-align:center; margin-top:15px; width: 100%;">
	<img src="<?php echo $map_url; ?>" width="739px" height="500px"/>
</div>
