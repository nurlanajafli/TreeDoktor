<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('equipments'); ?>">Equipments</a></li>
		<li class="active">New Group</li>
	</ul>

	<!--Client profile -->
	<section class="col-md-12 panel panel-default p-n">
		<header class="panel-heading">Group</header>
		<?php echo form_open(base_url() . "equipments/add_group") ?>
		<section class="col-md-6">
			<table class="table">
				<tr>
					<td class="w-150"><label class="control-label">Group Name:</label></td>
					<td class="p-left-30">

						<?php  if (form_error('new_group_name')) {
							$new_group_name_error = "inputError";
							$new_group_name_holder = "control-group error";
						} else {
							$new_group_name_error = "inputSuccess";
							$new_group_name_holder = "control-group success";
						}; ?>

						<div class="form-group">
							<?php  $new_group_name_options = array(

								'name' => 'new_group_name',
								'class' => 'form-control',
								'value' => set_value('new_group_name'),
								'id' => $new_group_name_error);
							?>
							<?php echo form_input($new_group_name_options) ?>
						</div>

						<div class="form-group">
							<?php  $new_group_group_options = array(

								'name' => 'new_group_color',
								'class' => 'form-control mycolorpicker',
								'readonly' => 'readonly',
								'value' => set_value('new_group_color') ? set_value('new_group_color') : '#ffffff');
							?>
							<?php echo form_input($new_group_group_options) ?>
						</div>

						<?php echo form_submit('submit', 'Add a new group', "class='btn btn-info pull-right'"); ?>
					</td>
				</tr>

			</table>

		</section>
	</section>
</section><!-- /end of client profile -->

<?php echo form_close() ?>

<script>
	$(function () {

		var setMyColorpicker = function (elem) {
			$(elem).colpick({
				submit: 0,
				colorScheme: 'dark',
				onChange: function (hsb, hex, rgb, el, bySetColor) {
					$(el).css('background-color', '#' + hex);
					if (!bySetColor) {
						$(el).val('#' + hex);
						//for (var i = 0, len = scopes.data.items.length; i < len; i++) {
						//	var curItem = scopes.data.items[i];
						//}
					}
				}
			}).keyup(function () {
				$(this).colpickSetColor(this.value);
			});
			$('.mycolorpicker').each(function () {
				var current_color = $(this).val();
				var current_color_short = current_color.replace(/^#/, '');
				$(this).colpickSetColor(current_color_short);
			});
		};
		window.setMyColorpicker = setMyColorpicker;
		setTimeout(function () {
			setMyColorpicker($('.mycolorpicker'));
		}, 200);
	});
</script>

<!-- / new customer -->
<?php $this->load->view('includes/footer'); ?>
