
<span id="global-js"><?php $this->load->view('field_worker/global_js'); ?></span>

<section class="vbox">
	<header class="header bg-white b-b b-light p-left-0 p-right-0">
        <?php /*echo date("d M, Y", strtotime($dashboard_date));*/ ?>
        <section class="hbox stretch">
			<aside class="aside-lg h2 text-center" id="dashboard-date">
				<?php $this->load->view('field_worker/dashboard_date'); ?>
			</aside>
			<aside class="col-lg-8 lter">
				<div class="p-top-5" id="dates-pagination" style="margin-left: 7px">
				<?php /*
					<?php for($i=0; $i<=6; $i++): ?>
<?php $button = (strtotime($week_start.' + '.$i.' day')==strtotime($dashboard_date))?'Today' : date("d M, Y", strtotime($week_start.' + '.$i.' day')); ?>
<?php $button_class = (strtotime($week_start.' + '.$i.' day')==strtotime($dashboard_date))?'btn-success col-md-3 col-xs-12' : 'btn-default col-md-1 col-xs-2'; ?>
<a href="#" data-date="<?php echo strtotime($week_start.' + '.$i.' day'); ?>" class="change-dashboard-date btn-rounded p-10 btn btn-s-md <?php echo $button_class; ?>"><?php echo $button; ?></a>
<?php endfor; ?>
				*/ ?>
				<?php $this->load->view('field_worker/dates_pagination'); ?>
				</div>
			</aside>
		</section>
    </header>
	<section class="scrollable">
		<section class="hbox stretch">
			<aside class="aside-lg bg-light lter b-r">
				<section class="vbox">
					<section class="scrollable">
						
						<div class="wrapper" id="team-equipments-tools">
							<?php $this->load->view('field_worker/team_equipments_tools'); ?>
						</div>

					</section>
				</section>
			</aside>
			<aside class="col-lg-8 lter b-l">
				<section class="vbox">
					<section class="scrollable">
						<div class="wrapper">
							<div class="row">
								<div class="col-lg-5" id="jobs">
								<?php $this->load->view('field_worker/jobs'); ?>
								</div>
								<div class="col-lg-7" id="map" style="height: 500px;">
									<?php $this->load->view('field_worker/map'); ?>
								</div>
							</div>
						</div>
					</section>
				</section>
			</aside>
		</section>
	</section>
</section>

<script src="<?php echo base_url('assets/js/modules/dashboard/field_worker.js'); ?>"></script>