<section class="col-sm-6 p-n" style="height: 100%;">
	<div class="affix" style="top: 30px;z-index: 9;right: 5px;position: absolute;">
		<ul class="nav navbar-nav navbar-right m-n hidden-xs nav-user">
			<li class="dropdown">
				<a href="#" class="dropdown-toggle p-10 bg-white b-a" data-toggle="dropdown" style="padding: 5px 10px;">
					<i class="fa fa-gears"></i>
				</a>
				<ul class="dropdown-menu on animated fadeInRight scrollable" id="note-list" style="right: 0px;height: 300px;">
					<?php foreach($wostatuses as $key => $status) : ?>
					<li>
						<a>
							<input type="checkbox" class="showStatus" value="1"<?php if(!$key) : ?> checked<?php endif; ?>
							       data-status_id="<?php echo $status['wo_status_id']; ?>"> -
							<?php echo $status['wo_status_name']; ?>
						</a>
					</li>
					<?php endforeach; ?>
					<li><a><input type="checkbox" class="showStatus" data-status_id="0" value="1"> - Show All Pins</a></li>
				</ul>
			</li>
		</ul>
	</div>
	<?php echo $map1['html']; ?>
</section>
<section class="col-sm-6 p-n" style="height: 100%;">
	<div class="bg-light" style="min-height: 55px;">
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 10px;height: 32px;" id="eventYear" data-select_num="0">
			<button data-toggle="dropdown" disabled class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">

			</ul>
		</div>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 0;height: 32px;" id="eventMonth" data-select_num="1">
			<button data-toggle="dropdown" disabled class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">

			</ul>
		</div>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 0;height: 32px;" id="eventDate" data-select_num="2">
			<button data-toggle="dropdown" disabled class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">

			</ul>
		</div>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 0;height: 32px;" id="eventStartTime" data-select_num="3">
			<button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">
				<?php for($i = 420; $i <= 1380; $i+=15) : ?>
					<li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT); ?></a></li>
				<?php endfor; ?>
			</ul>
		</div>
		<div class="btn-group m-r pull-left" style="margin: 17px 3px 0 0;height: 32px;">
			–
		</div>
		<div class="btn-group m-r pull-left" style="margin: 12px 3px 0 0;height: 32px;" id="eventEndTime" data-select_num="7">
			<button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle no-shadow" style="height: 32px;padding: 5px;">
				<span class="dropdown-label"></span>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu dropdown-select" style="max-height: 200px;min-width: 100px;overflow-y: scroll;">
				<?php for($i = 420; $i <= 1380; $i+=15) : ?>
					<li><a href="#" class="selectDate" data-value="<?php echo $i; ?>"><?php echo str_pad(intval($i / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(($i % 60), 2, '0', STR_PAD_LEFT); ?></a></li>
				<?php endfor; ?>
			</ul>
		</div>
		<div class="btn-group pull-right m-t-sm m-r-xs">
			<?php foreach ($statuses as $status) : ?>
				<button class="btn btn-info dropdown-toggle"
				        data-toggle="dropdown"><?php echo $status['name'] . ' ' . $status['count']; ?><span
						class="caret" style="margin-left:5px;"></span></button>
				<?php break; ?>
			<?php endforeach; ?>
			<ul class="dropdown-menu" id="wo_status" data-type="workorders" style="height: 350px;overflow-y: scroll;">
				<?php $i = 0; ?>
				<?php foreach ($statuses as $status) : ?>
					<li class="small"><a href="#tab<?php echo ++$i; ?>" data-statuid="<?php echo $status['id']; ?>" data-toggle="tab"><?php echo $status['name']; ?>
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
	<div style="padding: 0px;overflow-y: auto;position: absolute;bottom: 55px;top: 55px;width: 100%;">
		<div class="tabbable m-t-xs">
			<?php $i = 0; ?>
			<?php foreach ($workorders as $status => $works) : ?>
				<div class="tab-content">
					<div class="tab-pane panel <?php if (!$i) : ?> active<?php endif; ?>" id="tab<?php echo ++$i; ?>">
						<div data-toggle="buttons">
							<?php if (!empty($works)) : ?>
								<?php foreach ($works as $workorder) : ?>
									<label class="btn btn-dark no-shadow m-b-xs m-l-xs label-wo"
									       data-label-wo-id="<?php echo $workorder['id']; ?>"
									       data-label-wo-no="<?php echo $workorder['workorder_no']; ?>"
									       style="width:48%;overflow: hidden;text-overflow: ellipsis;"
									       data-toggle="popover" data-html="true" data-placement="bottom"
									       data-address="<?php echo $workorder['client_address']; ?>"
									       data-price="<?php echo money($workorder['total']); ?>"
									       data-time="<?php echo $workorder['total_time']; ?>"
									       data-original-title="<button type=&quot;button&quot; class=&quot;close pull-right&quot; data-dismiss=&quot;popover&quot;>×</button>&nbsp;">
										<input type="radio" name="options" id="option1" autocomplete="off">
										<?php $workorder['total_time'] = $workorder['total_time'] ? $workorder['total_time'] : 0; ?>
										<?php echo $workorder['total_time'] . ' hrs. - ' . money($workorder['total']) . ' - ' . $workorder['client_address']; ?>
									</label>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="alert alert-danger" style="font-size: 13px;">
									<i class="fa fa-ban-circle"></i>No record found.
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
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
				<a href="#" class="dropdown-toggle dker" data-toggle="dropdown" id="woSearchShow" style="padding: 5px 20px;margin-top: 12px;margin-left: 10px;background-color: #5bc0de;color: #fff;"><i class="fa fa-fw fa-search"></i></a>
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
</section>
<div class="clear"></div>