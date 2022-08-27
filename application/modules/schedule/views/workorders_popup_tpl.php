<?php  if(!empty($globe) && $globe === TRUE) : ?>
	<?php $this->load->view('includes/header'); ?>
	<?php $this->load->view('schedule_map'); ?>
	<?php $this->load->view('workorders_popup_tpl_map'); ?>
<?php endif; ?>


<div class="col-sm-4 p-n bg-white" style="height: 100%;" id="workordersCoverSection">
	<div class="bg-light" style="min-height: 55px;">
		<?php if(empty($globe)) : ?>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 10px;height: 32px;" id="eventYear" data-select_num="0">
			<button data-toggle="dropdown" disabled class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px; width: 50px;">
				<span class="dropdown-label"></span>
				
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">

			</ul>
		</div>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 0;height: 32px;" id="eventMonth" data-select_num="1">
			<button data-toggle="dropdown" disabled class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px; width: 70px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">

			</ul>
		</div>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 0;height: 32px;" id="eventDate" data-select_num="2">
			<button data-toggle="dropdown" disabled class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px; width: 50px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">

			</ul>
		</div>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 0;height: 32px;" id="eventStartTime" data-select_num="3">
			<button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px; width: 72px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">
            <?php
            if(getIntTimeFormat() == 24):
                for($i = (config_item('crew_schedule_start') * 60); $i <= (config_item('crew_schedule_end') * 60); $i+=15) : ?>
                    <li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT); ?></a></li>
                <?php endfor;
            else:
                for($i = (config_item('crew_schedule_start') * 60); $i <= (config_item('crew_schedule_end') * 60); $i+=15) :
                    if($i / 60 < 12):?>
                        <li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT ) . ' am'; ?></a></li>
                    <?php elseif($i / 60 >= 12 && $i / 60 < 13):?>
                        <li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT ) . ' pm'; ?></a></li>
                    <?php else: ?>
                        <li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60 - 12), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT ) . (($i / 60) !== 24 ? ' pm' : ' am'); ?></a></li>
            <?php endif; endfor; endif;
            ?>
			</ul>
		</div>
		<div class="btn-group m-r pull-left" style="margin: 17px 3px 0 0;height: 32px;">
			–
		</div>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 0;height: 32px;" id="eventEndTime" data-select_num="7">
			<button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px; width: 72px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">
                <?php
                if(getIntTimeFormat() == 24):
                    for($i = (config_item('crew_schedule_start') * 60); $i <= (config_item('crew_schedule_end') * 60); $i+=15) : ?>
                        <li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT); ?></a></li>
                    <?php endfor;
                else:
                    for($i = (config_item('crew_schedule_start') * 60); $i <= (config_item('crew_schedule_end') * 60); $i+=15) :
                        if($i / 60 < 12):?>
                            <li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT ) . ' am'; ?></a></li>
                        <?php elseif($i / 60 >= 12 && $i / 60 < 13):?>
                            <li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT ) . ' pm'; ?></a></li>
                        <?php else: ?>
                            <li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60 - 12), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT ) . (($i / 60) !== 24 ? ' pm' : ' am'); ?></a></li>
                        <?php endif; endfor; endif;
                ?>
			</ul>
		</div>
		<?php endif; ?>
		<a class="btn btn-warning btn-xs showFilter m-t m-l-sm" title="Show Filters">
			<i class="fa fa-filter"></i>
		</a>
		<a class="btn btn-info btn-xs clearFilter m-t m-l-sm" title="Clear Filters" style="display: none;">
			<i class="fa fa-times"></i>
			<span class="badge bg-danger">0</span>
		</a>
		<div class="btn-group pull-right m-t-sm m-r-xs">
			<?php foreach ($statuses as $status) : ?>
				<button class="btn btn-info dropdown-toggle" style="max-width: 140px;overflow: hidden;text-overflow: ellipsis;"
				        data-toggle="dropdown"><?php echo $status['name'] . ' ' . $status['count']; ?><span
						class="caret" style="margin-left:5px;"></span></button>
				<?php break; ?>
			<?php endforeach; ?>
			<ul class="dropdown-menu" id="wo_status" style="height: 350px;overflow-y: scroll;">
				<?php $i = 0; ?>
				<?php foreach ($statuses as $status) : ?>
					<li class="small"><a href="#tab<?php echo ++$i; ?>" <?php if(!empty($status['is_default'])) : ?> data-default_status="1" <?php endif;?> data-statuid="<?php echo $status['id']; ?>" data-toggle="tab"><?php echo $status['name']; ?>
							<span
								class="badge<?php if ($status['count']) : ?> bg-info<?php endif; ?>"><?php echo $status['count']; ?></span></a>
					</li>
				<?php endforeach; ?>
				<li class="small bg-warning" id="woSearch"><a href="#schedulesearch" data-toggle="tab">Search
						<span class="badge">0</span></a>
				</li>
			</ul>
		</div>
		<div class="clear"></div>
	</div>
	<div style="padding: 0px;overflow-y: auto;position: absolute;bottom: 55px;top: 55px;width: 100%;overflow-x: hidden;">
		<div class="m-b-sm filters" style="display: none;">
			<div class="grindersFilter" style="display: none;">
				<?php $grinderOpts = $stump_grinder->vehicle_options ? json_decode($stump_grinder->vehicle_options) : []; ?>
				<?php foreach ($grinderOpts as $key => $value) : ?>
				<div class="col-md-4 m-t-xs" style="padding-right: 0px; padding-left: 2px;">
					<div class="p-5">
						<div class="checkbox m-n bg-light  b-a p-5 p-left-30">
							<label>
								<input type="checkbox" class="grinderFilter" value="<?php echo $stump_grinder->vehicle_id; ?>" data-option="<?php echo $value; ?>" checked="checked">
									<?php echo $stump_grinder->vehicle_name; ?> (<?php echo $value; ?>)
							</label>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

			<?php foreach($crews as $c) : ?>
			<div class="col-md-4" style="padding-right: 0px;padding-left: 2px;">
				<div class="p-5">
					<div class="col-md-4 text-center b-a bg-light p-5"><?php echo $c->crew_name; ?></div>
					<div class="col-md-8 p-n">
						<input class="filter form-control" type="text" data-name="<?php echo str_replace(' ', '-', strtolower($c->crew_name)); ?>">
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php endforeach; ?>
			<div class="clear"></div>

			<div class="estimators-container">
			<?php foreach($estimators as $key => $estimator) : ?>
				<div class="bg-light">
					<div class="checkbox m-n">
						<label style="margin-left: -4px;">
							<input type="checkbox" class="estimatorFilter" value="<?php echo $estimator->id; ?>" checked="checked">
								<?php echo $estimator->emailid; ?>
						</label>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
			<div class="clear"></div>
		</div>
		<div class="tabbable m-t-xs" id="scheduleWorkorders">
			<?php $this->load->view('workorders_popup_labels_tpl'); ?>
			<div class="tab-content">
				<div class="tab-pane panel" id="schedulesearch">
					<div data-toggle="buttons">

					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="bg-light" style="height: 55px;position: absolute;bottom: 0;left: 0;right: 0;">
		<ul class="nav navbar-nav navbar-left m-n hidden-xs nav-user">
			<li class="dropdown hidden-xs">
				<a href="#" class="dropdown-toggle dker pull-left" data-toggle="dropdown" id="woSearchShow" style="padding: 5px 20px;margin-top: 12px;margin-left: 10px;background-color: #5bc0de;color: #fff;"><i class="fa fa-fw fa-search"></i></a>
				<a href="#" class="btn btn-default pull-left m-l" title="Refresh Workorders" style="padding: 4px 10px;margin-top: 12px;" id="reloadWorkorders">
					<i class="fa fa-refresh"></i>
				</a>
				<section class="dropdown-menu aside-xl animated fadeInUp no-shadow" style="top: -65px;">
					<section class="panel bg-light">
						<form role="search" name="search" id="searchSchedule" method="post" class="input-append">
							<div class="form-group wrapper m-b-none">
								<div class="input-group">
									<input type="text" class="form-control no-shadow" placeholder="Search" name="search_keyword" value="">
									<span class="input-group-btn">
										<button type="submit" class="btn btn-info btn-icon no-shadow"><i class="fa fa-search"></i></button>
									</span>
								</div>
							</div>
						</form>
					</section>
				</section>
			</li>
		</ul>
		<div class="btn-group m-r dropup pull-right" style="display: none;margin-right: 180px;margin-top: 12px;height: 32px;" id="crewsSelect">
			<button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;">
				<span class="dropdown-label" style="display: inline-block;"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 250px;min-width: 220px;overflow-y: scroll;">

			</ul>
		</div>
	</div>
</div>
<div class="clear"></div>

<?php  if(!empty($globe) && $globe === TRUE) : ?>
	<script>
		$(document).on('click', '#wo_status li a', function(){
			if($('[data-status_id="0"]').is(':checked'))
				return false;
			if($('[data-status_id]:checked').length == 1)
			{
				var obj = $('[data-status_id]:checked');
				$(obj).prop('checked', false);
				$(obj).change();
			}
			var statusId = $(this).data('statuid');
			$('[data-status_id="' + statusId + '"]').prop('checked', true);
			$('[data-status_id="' + statusId + '"]').change();
			id = $(this).attr('href');
			$('#wo_status').prev().html($(this).text() + '<span class="caret" style="margin-left:5px;"></span>');
			$('#wo_status').find('.active').not($(this).parent()).removeClass('active');
			$('.tabbable').find('.tab-content .tab-pane.panel.active').not($(id)).removeClass('active');
			$('#wo_status').parent().removeClass('open');
		});
	</script>

	<?php $this->load->view('includes/footer'); ?>	
<?php endif; ?>

