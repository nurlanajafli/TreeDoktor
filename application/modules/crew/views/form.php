<?php $this->load->view('includes/header'); ?>

<span style="color:red;"><?php echo validation_errors(); ?></span>

<div class="row">
	<div class="grid_12 rounded filled_white shadow overflow">

		<!-- Leads header -->
		<div class="module-header">
			<div class="module-title">Crew Profile</div>
		</div>

		<!-- Data -->
		<div class="row">

			<!--Form -->
			<div class="grid_8" style="width: 90%">
				<div class="p-top-10 m-bottom-10 p-sides-10">
					<?php
					$attri = array(
						'name' => 'crew_form',
						'id' => 'crew_form');

					if (isset($edit)) {
						echo form_open('crew/save/' . $crew_row->crew_id, $attri);
					} else {
						echo form_open('crew/save', $attri);
					}?>

					<table width="100%">
						<tr>
							<td width="30%">Crew name</td>
							<td width="70%">

								<?php
								$data = array(
									'name' => 'crew_name',
									'id' => 'crew_name',
									'value' => set_value('crew_name', isset($crew_row->crew_name) ? $crew_row->crew_name : ''),
									'maxlength' => '100'
								);
								echo form_input($data);
								?>
								<div id="dcrew_name"></div>
							</td>
						</tr>
						<tr>
							<td>Crew Color</td>
							<td>
								<?php
								$data = array(
									'name' => 'crew_color',
									'id' => 'crew_color',
									'value' => set_value('crew_color', isset($crew_row->crew_color) ? $crew_row->crew_color : ''),
									'maxlength' => '50'
								);
								echo form_input($data);
								if (isset($crew_row->crew_color)) {
									$color = $crew_row->crew_color;
								} else {
									$color = '#0000ff';
								}
								?>

								<div id="colorSelector">
									<div style="background-color:<?php echo $color; ?> "></div>
								</div>
								<div id="dcrew_name"></div>
							</td>
						</tr>
						<tr>

							<td colspan="2" align="center">  <?php //print_r($crew_row);
                                if ($emp_row->num_rows() > 0) {
									foreach ($emp_row->result() as $row) {
										$options[$row->employee_id] = $row->emp_name . '(' . $row->emp_position . ')';
									}
								}
								$selected = "";
								if (!empty($crew_emp_mem)) {
									foreach ($crew_emp_mem->result() as $row) {
										$selected[] = $row->employee_id;
									}
								}
								echo form_dropdown('employee_id[]', $options, isset($selected) ? $selected : set_value('employee_id'), 'multiple="multiple" id="employee_id_list"');

								?></td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<?php
								//$attributes = array("onclick" => "return validate()");
								$value = "Add";
								if (isset($edit)) $value = 'Update';
								echo form_submit('submit', $value, 'class="btn btn-info pull-right m-top-10" id="sub_btn"');
								echo anchor('crew/', 'Cancel', 'class="btn btn-info pull-right m-top-10 m-right-10"');?>
							</td>
						</tr>
					</table>

				</div>
			</div>
			<!-- /End Form -->
			<?php echo form_close(); ?>
			<!-- Image -->

			<?php if (isset($user_row->picture) && !empty($user_row->picture)) { ?>
				<div class="grid_3 p-top-10 m-bottom-10 p-right-10 rounded shadow overflow">
					<img src="<?php echo base_url(PICTURE_PATH) . $user_row->picture; ?>">
				</div>
			<?php } ?>


		</div>
		<!-- End Data -->
	</div>
</div>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/assets/css/jquery.comboselect.css">
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/jquery.comboselect.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/colorpicker.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/eye.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/utils.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>/assets/js/layout.js?ver=1.0.2"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>/assets/css/colorpicker.css" type="text/css"/>
<link rel="stylesheet" media="screen" type="text/css" href="<?php echo base_url(); ?>/assets/css/layout.css"/>
<script type="text/javascript">
	$(document).ready(function () {
		// Multi-Combo boxes
		$('#employee_id_list').comboselect({ sort: 'none', addbtn: '>>', rembtn: '<<' });
		$('#colorSelector').ColorPickerSetColor('<?php echo $color;?>');
		$('#colorSelector').ColorPicker();
		$('.colorpicker_submit').click(function () {
			var color = $('.colorpicker_hex input').val();
			$('#crew_color').val('#' + color);
			$('.colorpicker').fadeOut(500);

		});

	});
</script>
<?php $this->load->view('includes/footer'); ?>
