<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('equipments'); ?>">Equipments</a></li>
		<li><a href="<?php echo base_url('equipments/details/' . $item[0]->group_id); ?>"><?php echo $item[0]->group_name; ?></a></li>
		<li class="active"><?php echo $item[0]->item_name; ?></li>
	</ul>
	
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">
			<div class="pull-left">
				<p>
				Item Name: "<?php echo $item[0]->item_name; ?>"<br>

				Item Serial: "<?php echo $item[0]->item_serial; ?>"<br>

				Item Description: "<?php if ($item[0]->item_description) :
					echo $item[0]->item_description;
				else :
					echo 'N/A';
				endif; ?>"
				<?php if(isset($kmrs) && $kmrs && $item[0]->item_gps_start_counter != NULL) : ?>
				<br>Item GPS Counter: <?php echo ($item[0]->item_gps_start_counter + $kmrs); ?> km
				<?php endif; ?>
				</p>
			</div>
			<div class="clear"></div>
		</header>
		<?php $href = base_url() .  'equipments/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) . '/'; ?>
		<div class="tabbable p-10"> <!-- Only required for left/right tabs -->
			<ul class="nav nav-tabs" data-type="estimates" data-action="stop">
				<li>
					<a href="<?php echo base_url('equipments/profile') . '/' . $item[0]->item_id; ?>#tab1">Services</a>
				</li>
				<li>
					<a href="<?php echo base_url('equipments/profile') . '/' . $item[0]->item_id; ?>#tab2">Parts</a>
				</li>
				<li>
					<a href="<?php echo base_url('equipments/profile') . '/' . $item[0]->item_id; ?>#tab3">Service Settings</a>
				</li>
				<li>
					<!--<a href="#tab4" data-toggle="tab">Old Services</a>-->
					<a href="<?php echo base_url('equipments/item_repairs') . '/' . $item[0]->item_id ;?>" >Repairs</a>
				</li>
				<?php if(isset($item[0]->item_tracker_name) && $item[0]->item_tracker_name != '' && $item[0]->item_tracker_name != NULL) : ?>
					<li class="active">
						<a href="<?php echo base_url('equipments/item_distance') . '/' . $item[0]->item_id ;?>" >Distance Report</a>
					</li>
				<?php endif; ?>
				<?php /*<li>
					<a href="<?php echo base_url('equipments/profile') . '/' . $item[0]->item_id; ?>#tab4">Files</a>
				</li>*/  ?>
			</ul>
		
			<div class="tab-content">
				<div class="pull-right">
					<form id="dates" method="post" action="<?php echo $href; ?>" class="input-append m-t-xs">
						<label>
							<input name="from" class="datepicker form-control date-input-client from text-center" type="text" readonly
								   value="<?php if ($from) : echo getDateTimeWithDate($from, 'Y-m-d');
								   else : echo date(getDateFormat()); endif; ?>">
						</label>
						— &nbsp;&nbsp;
						<label>
							<input name="to" class="datepicker form-control date-input-client to text-center" type="text" readonly
								   value="<?php if ($to) : echo getDateTimeWithDate($to, 'Y-m-d');
								   else : echo date(getDateFormat()); endif; ?>">
						</label>
						<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
					</form>
				</div>
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Date</th>
								<th>Count(kms)</th>
							</tr>
						</thead>
						<tbody>
							<?php if(!empty($distance) && !empty($distance)) : ?>
								<?php $sum = 0; ?>
								<?php foreach($distance as $key => $val) : ?>
									<tr>
										<td><?php echo ($key + 1); ?></td>
<!--										<td>--><?php //echo $val->egtd_date; ?><!--</td>-->
										<td><?php echo getDateTimeWithDate($val->egtd_date, 'Y-m-d'); ?></td>
										<td><?php echo number_format($val->egtd_counter, 2, '.', ','); ?></td>
									</tr>
								<?php $sum += $val->egtd_counter; ?>
								<?php endforeach; ?>
								<tr>
									<td>&nbsp;</td>
									<td>
										<strong>Total : </strong>
									</td>
									<td>
										<strong><?php echo number_format($sum, 2, '.', ',');?></strong>
									</td>
								</tr>
							<?php else : ?>
								<tr>
									<td colspan="3" style="color:#FF0000;">No record found</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
        <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
	</section>
	<script>
		$(document).ready(function (){
			// $('.datepicker').datepicker({format: 'yyyy-mm-dd'});
            $('.datepicker').datepicker({format: $('#php-variable').val()});
		});
	</script>
</section>
<?php $this->load->view('includes/footer'); ?>
