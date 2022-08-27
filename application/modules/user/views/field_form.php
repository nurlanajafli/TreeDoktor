<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/jasny-bootstrap.js'); ?>"></script>
<style>.table.table-hover tr td {
		line-height: 30px;
	}</style>
<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('user'); ?>">Users</a></li>
	<li class="active"><?php if (isset($user_row->user_id)) : ?>User - <?php echo $user_row->firstname; ?><?php else : ?>New User<?php endif; ?></li>
</ul>
<section class="col-md-12 panel panel-default p-n">
	<?php
$attri = array(
	'name' => 'user_form',
	'id' => 'user_form');


if (isset($edit)) {
	$url = base_url() . 'user/save/' . $user_row->id;
	//echo form_open_multipart('user/save/' . $user_row->id, $attri);
} else {
	$url = base_url() . 'user/save/';
	//echo form_open_multipart('user/save', $attri);
}?>
<header class="panel-heading">User Profile</header>
<form action="<?php echo $url; ?>" method="POST"  id="user_form" name="user_form" enctype="multipart/form-data">
<input type="hidden" name="worker_type" value="1">

<div class="">
	<!--Form -->
	<div class="col-md-8">
		<?php $this->load->view('form/main_form');?>
	</div>
	<!-- Image -->
	<div class="col-md-4 p-top-10 m-bottom-10 p-right-10 p-left-30 pull-right">
	<?php if (isset($user_row->picture) && !empty($user_row->picture)) { ?>
			<div class=" ">
				<img src="<?php echo base_url() . PICTURE_PATH . $user_row->picture; ?>">

			</div>
	<?php } ?>
	</div>

	<?php $this->load->view('form/employee_form');?>

</div>
	
<?php $this->load->view('form/docs_form');?>
	
<div>

	<?php
	$value = "Add";
	if (isset($edit)) 
		$value = 'Update';

	echo form_submit('submit', $value, 'class="btn btn-info pull-right m-top-10" id="sub_btn"');
	echo anchor('user/', 'Cancel', 'class="btn btn-info pull-right m-top-10 m-right-10"');?>
</div>
</form>
</section>
</section>

</section>
</section>


<!-- /End Form -->
<!-- End Data -->
<?php $this->load->view('includes/footer'); ?>

<script type="text/javascript">
	var doc_tpl = <?php echo $doc_tpl; ?>;
	$(document).ready(function () {
		editor.preview();
		$('.support').click(function(){
			var val = $(this).prop('checked');
			if(val)
				$('#twilio_level').removeAttr('disabled');
			else
				$('#twilio_level').attr('disabled', 'disabled');
		});
		$('#txtyearlyrate').keyup(function(){
			var obj = $(this); 
			var last = <?php echo isset($employee_row->emp_hourly_rate) ? json_encode($employee_row->emp_hourly_rate) : 0 ; ?>;
			var val = $('#txtyearlyrate').val();
			if(val != '' && val != '0' && val)
			{
				rate = val/26/80;
				$('#content').find('#txthourlyrate').val(parseFloat(rate.toFixed(4)));
				return false;
			}
			else
				$('#content').find('#txthourlyrate').val(last);
			console.log(last); 
			return false;
		});

		/*$('.select_active_status').on('change', function(){
			console.log($(this).val()); 
			$('.employee_settings').slideToggle("slow");
			
			
		});*/
		$('.RPS').click(function () {
			if ($('input[name="RPS_GEN"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_EST"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_WO"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
				$('#RPS_0').prop('checked', false);

			}
			if ($('input[name="RPS_IN"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_PR"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
			if ($('input[name="RPS_PRO"]:checked').val() == 1) {
				$('#RPS_1').prop('checked', true);
			}
		});
		$('.RPS_GEN').click(function () {
			if ($('input[name="RPS_GEN"]:checked').length > 0) {
				if ($('input[name="RPS_GEN"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_EST').click(function () {
			if ($('input[name="RPS_EST"]:checked').length > 0) {
				if ($('input[name="RPS_EST"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_WO').click(function () {
			if ($('input[name="RPS_WO"]:checked').length > 0) {
				if ($('input[name="RPS_WO"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_IN').click(function () {
			if ($('input[name="RPS_IN"]:checked').length > 0) {
				if ($('input[name="RPS_IN"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_PR').click(function () {
			if ($('input[name="RPS_PR"]:checked').length > 0) {
				if ($('input[name="RPS_PR"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});
		$('.RPS_PRO').click(function () {
			if ($('input[name="RPS_PRO"]:checked').length > 0) {
				if ($('input[name="RPS_PRO"]:checked').val() == 1) {
					$('#RPS_1').prop('checked', true);
				}
			}
		});

		 

		$(document).on('click', '.delete-doc', function() {
			if($(this).is('disabled'))
				return false;

			$(this).parents('.certificate-section:first').remove();

			$.each($('.certificate-section .doc_title'), function(key, val) {
				if(($(val).text().indexOf('Document #') + 1))
					$(val).text('Document #' + (key + 1));

			});

			return false;
		});

		$(document).on('click', '.add-doc', function() {
			if($(this).is('disabled'))
				return false;
			
			$('.certificate-section:last').after(doc_tpl.html);
			$('.certificate-section:last input').val('');
            $('.certificate-section:last .delete-doc').show();
			$('.datepicker').datepicker();
			$.each($('.certificate-section .doc_title'), function(key, val) {

				if(($(val).text().indexOf('Document #') + 1))
					$(val).text('Document #' + (key + 1));
				if(key + 1 == $('.certificate-section .doc_title').length) {
					$(val).parents('.certificate-section:first').find('.doc_title').val('Document #' + (key + 1));
				}
			});

			return false;
		});

	});
	$(function () {

		var setMyColorpicker = function (elem) {
			$(elem).colpick({
				submit: 0,
				colorScheme: 'dark',
				onChange: function (hsb, hex, rgb, el, bySetColor) {
					$(el).css('background-color', '#' + hex);
					if (!bySetColor) {
						$(el).val('#' + hex);
						for (var i = 0, len = scopes.data.items.length; i < len; i++) {
							var curItem = scopes.data.items[i];
						}
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
	
	/*function checkActive(obj)
	{
		console.log($(obj).parent().find('.active_employee').prop('checked', true), $(obj).val());
		if($(obj).val() == 'yes')
			$(obj).parent().find('.active_employee').prop('checked', true);
		else if($(obj).val() == 'no')
			$(obj).parent().find('.active_employee').prop('checked', false);
	}*/
</script>
<script src="<?php echo base_url('assets/vendors/notebook/js/markdown/epiceditor.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/markdown/demo.js'); ?>"></script>
