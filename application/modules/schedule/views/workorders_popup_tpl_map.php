<div class="col-sm-8 p-n" id="modalMap" style="height: 100%;">
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
</div>