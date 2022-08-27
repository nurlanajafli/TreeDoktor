<?php if(isset($emp_events) && !empty($emp_events)): ?>
<section class="block">
	
	<h3>Jobs:</h3>

	<section class="slim-scroll" data-height="550px">
	<?php foreach ($emp_events as $key => $event): ?>
	<article id="comment-id-<?php echo $key; ?>" class="comment-item">
		<section class="panel panel-default">
			<header class="panel-heading bg-white" style="background: #fff;">
				<a href="/events/team_event/<?php echo $event['id']; ?>" class="h4 <?php if($event['event_report']!==NULL): ?>text-muted<?php endif; ?>"><?php echo $key+1; ?>)&nbsp;<?php echo $event['lead_address'].$event['lead_zip']; ?></a>
				
				<?php if($event['event_report']!==NULL): ?>
					<span class="m-l-sm pull-right text-success hidden-xs">Completed <i class="glyphicon glyphicon-ok"></i></span>
					<a class="text-warning pull-right hidden-xs"  target="_blank" href="<?php echo site_url('/events/report_pdf/'.$event['id']); ?>"><i class="fa fa-file"></i>&nbsp;Report FORM <strong>(PDF)</strong></a>

					<span class="m-l-sm pull-right text-success visible-xs p-top-10 h4">Completed <i class="glyphicon glyphicon-ok"></i></span>
					<div class="visible-xs m-left-0 p-top-10 h4">
						<a class="text-warning"  target="_blank" href="<?php echo site_url('/events/report_pdf/'.$event['id']); ?>"><i class="fa fa-file"></i>&nbsp;Report FORM <strong>(PDF)</strong></a>
					</div>
					
					<div class="clear"></div>
				<?php else: ?>
				<span class="text-muted m-l-sm pull-right hidden-xs">
					<i class="fa fa-clock-o"></i>&nbsp;
					<?php echo date("H:i", $event['event_start']); ?> - <?php echo date("H:i", $event['event_end']); ?>
				</span>
				<div class="text-muted m-l-sm visible-xs m-left-0 p-top-10 h4">
					<i class="fa fa-clock-o"></i>&nbsp;
					<?php echo date("H:i", $event['event_start']); ?> - <?php echo date("H:i", $event['event_end']); ?>
				</div>
				<?php endif; ?>
			</header>
			<div class="panel-body <?php if($event['event_report']!==NULL): ?>text-muted<?php endif; ?>">
				<?php if(!isset($event['event_services']) || empty($event['event_services'])): ?>
				<div>No description</div>
				<?php else: ?>
					<?php foreach($event['event_services'] as $service):?>
					<div style="white-space: pre-line;"><strong><?php echo $service['service']['service_name']; ?></strong></div>
					<div style="white-space: pre-line;"><?php echo $service['service_description']; ?></div>
                    <?php if($service['service']['is_bundle']): ?>
                        <?php if(!empty($service['bundle'])) foreach($service['bundle'] as $bundle_record) : ?>
                                <div style="padding-left: 20px; border-left: 1px solid #bebebe;">
                                <div style="white-space: pre-line;"><strong ><?php echo $bundle_record['estimate_service']['service']['service_name']; ?></strong></div>
                                <div style="white-space: pre-line;"> <?php echo $bundle_record['estimate_service']['service_description']; ?></div>

                                </div>
                                <?php endforeach; ?>
                    <?php endif; ?>
					<br>
					<?php endforeach; ?>
				<?php endif; ?>
				<div class="comment-action m-t-sm">
					<a href="/events/team_event/<?php echo $event['id']; ?>" class="btn btn-default btn-xs">
						<i class="fa fa-sign-in text-danger"></i> 
						Details
					</a>
				</div>
			</div>
		</section>
	</article>
    <?php endforeach; ?>    
    </section>


</section>
<?php else: ?>
	<section class="block">
	
	<h3>Jobs:</h3>

	<section class="slim-scroll" data-height="550px">
		<h4 class="text-success text-center">Events list is empty</h4>
	</section>
	</section>
<?php endif; ?>

		

		