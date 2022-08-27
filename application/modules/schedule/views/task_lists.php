<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title??'Tasks | ' . SITE_NAME; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" media="print" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>

	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>

	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/css/workorder_pdf.css" type="text/css" media="print">

	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font-awesome.min.css'); ?>" type="text/css">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css">

</head>
<body> 
<div class="workorder_number">
	<div style="margin-right:10px;">
	    <div class="pull-left" style="float: left; width: 86%;">
	        <p style="color: #ca8427; font-size: 20px;"><strong><?php echo $date; ?></strong></p>
			<p style="font-size: 17px;">
				<strong>Task list for:</strong>&nbsp;
				<strong style="color: #ca8427;"><?php echo $name; ?></strong>
			</p>
			<br>
	    </div>
        <?php
        $default_img = 'assets/'.$this->config->item('company_dir').'/print/header2_short.png';
        $brand_id = default_brand();
        $logo = get_brand_logo($brand_id, 'header2_short', $default_img);

        ?>
        <div class="pull-right text-right" style="float: right; width: 13%">
            <img src="<?php echo base_url($logo); ?>">
        </div>
	    <div class="clear"></div>
	</div>	
</div>
<br>
<div>
	<p style="padding: 3px 0px; font-size: 17px; width: 60%; float: left; border: 1px solid #ccc; border-left:0; border-top-right-radius: 20px; border-bottom-right-radius: 20px; background: #f5f5f521;">
		<img src="<?php echo base_url('/assets/print/arrow_small.png'); ?>" style="margin-right: -120px" ><strong>TASKS:</strong>
	</p>
	
	<div class="clear"></div>

	
	<?php  /*
	<div style="width:48%; display:inline-block;float: left;">
		<?php foreach($items as $k=>$v) : ?>
			<div style="background:#<?php if($v['type'] == 'user') : ?>36e036<?php else : ?>FFF<?php endif; ?>; margin-bottom:2px; text-align: center; border-radius: .25em; border:1px solid #000; display: block;text-overflow: ellipsis;overflow: hidden; font-size: 17px;">
				<label>
					<?php if($v['item_id'] == $v['team_leader_user_id']) echo '* '; ?>
					<?php echo $v['name']; ?>
					<?php if($v['type'] == 'user' && $v['driver_id']) : ?>
						(<?php echo $v['driver_id']; ?>)
					<?php elseif($v['type'] == 'equipment' && $v['field_worker']) : ?>
						(<?php echo $v['field_worker']; ?>)
					<?php endif; ?>
				</label>
			</div>
		<?php endforeach; ?>
		
	</div>
	*/ ?>
	<div style="width:100%;  float: left;">
	<?php if(!empty($events)) : ?>
		
		<?php $num = 1; ?>
		<div style="display:inline-block;">
		<?php foreach($events as $k=>$v) : ?>
			
			<div>
				<label>
					<strong style=" font-size:15px">
						<strong style="color: #ca8427;"><?php echo $num++;?>)</strong> 
						<a href="<?php echo base_url($v['task_client_id']); ?>" style="color: #ca8427; text-decoration: underline;" target="_blank">
						<?php if($v['task_lead_id']): ?>
							<?php echo lead_address($v); ?>
						<?php else: ?>
						<?php echo task_address($v); ?>
						<?php endif; ?>
						(<?php echo $v['task_start'];?> - <?php echo $v['task_end'];?>, <?php echo $v['task_date'];?>)
						</a>
					</strong>
				</label><br>
				<div style="padding-left: 17px;">
					<p style="margin-bottom: 2px">
						<strong style="color: #2c6100; display: block; width: 300px">Category:</strong>&nbsp;<?php echo $v['category_name']; ?>
					</p>
					<h5 style="margin: 2px 0px;">
						<strong style="color: #2c6100; font-size:14px; display: block; width: 300px">Details:</strong>
						<?php echo $v['task_desc']; ?>
					</h5>
				</div>
			</div>
			
			<div style="padding-left: 17px;">
				
				<div class="pull-left" style="width: 49.5%">
					<h5 style="float: left; width: 60px;">
						<strong style="color: #2c6100; text-decoration: underline;">Lead:</strong>
					</h5>
						<?php if($v['task_lead_id']): ?>
						<div style="background: #e9f3e2; float:left; padding: 1px 2px 0px; width:70px; text-align: center; border-radius: 8px"><i><?php echo $v['lead_no']; ?></i></div>
						<?php else: ?>
							<div style="background: #e9f3e2; float:left; padding: 1px 2px 0px; width:70px; text-align: center; border-radius: 8px"><i>No</i></div>
						<?php endif; ?>
					<div class="clear"></div>
					
					<?php if($v['task_lead_id']): ?>
					
					<div>
						<strong>Timing:&nbsp;&nbsp;</strong><i><?php echo $v['timing']; ?></i><br>
						<strong>Priority:&nbsp;&nbsp;</strong><i><?php echo $v['lead_priority']; ?></i><br>
						<strong>Description:&nbsp;&nbsp;</strong><i><?php echo $v['lead_body']; ?></i>
					</div>
					
					<?php endif; ?>
				</div>
				

				<div class="pull-right" style="width: 49.5%">
					<h5 style="float: left; width: 70px"><strong style="color: #2c6100; text-decoration: underline;">Client:</strong></h5>
					<?php if($v['task_client_id']): ?>
						<div style="background: #e9f3e2; float:left; padding: 1px 2px 0px; text-align: center; border-radius: 8px"><i><?php echo $v['client_name']; ?></i></div>
					<?php else: ?>
						<div style="background: #e9f3e2; float:left; padding: 1px 2px 0px; text-align: center; border-radius: 8px; width:70px;"><i>No</i></div>
					<?php endif; ?>

					<div class="clear"></div>
					
					<div>
						<?php if(isset($v['cc_phone']) && $v['cc_phone']): ?><strong>Phone:&nbsp;&nbsp;</strong><i><?php echo numberTo($v['cc_phone']); ?></i><?php endif; ?>
						<?php if(isset($v['cc_email']) && $v['cc_email']): ?><br><strong>E-mail:&nbsp;&nbsp;</strong><i><?php echo $v['cc_email']; ?></i><?php endif; ?>
					</div>
					
				</div>
				 <div class="clear"></div>
			</div>
			<br>
		<?php endforeach; ?>
		</div>
	<?php endif; ?>
	 
	</div>
</div>

<div style="text-align:center; margin-top:15px; width: 100%;">
	<img src="<?php echo $map_url; ?>" width="739px" height="500px"/>
</div>
</body>
</html>