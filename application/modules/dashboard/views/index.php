<?php $this->load->view('includes/header'); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
	@media (max-width: 767px) {
		#content>.vbox>.scrollable {
			position:relative;
		}
	}
    @media (max-width: 1600px) {
        .perfomance-tab{
            padding-left: 5px !important;
            padding-right: 5px !important;
            font-size: 10px;
        }
    }
		</style>
<section class="scrollable">
	
	<section class="hbox stretch">
		<!-- Dashboard data -->

		<!-- Left Column -->

		<!-- Profile Badge -->
		<?php if ($userData->emp_feild_worker == 0 || $userData->emp_field_estimator): ?>
		<?php $this->load->view('profile_badge.php'); ?>
		<?php endif; ?>
		<!-- /Profile Badge -->
		<!-- Profile Counter -->
		<?php if (($this->session->userdata("PP") == 0 && $this->session->userdata('user_type') != "admin") || ($userData->emp_feild_worker == 1 && !$userData->emp_field_estimator)) : ?>
		<?php else : ?>
			<?php $this->load->view('profile_counter.php'); ?>
		<?php endif;  ?>
		
		<!-- /Profile Counter -->
		<?php if ($userData->emp_feild_worker == 1 && !$userData->emp_field_estimator) : ?>
			<?php $this->load->view('field_works.php'); ?>
		<?php endif; ?>
		<!-- Right Column -->

		<!--Task Manager -->
		<?php if (($this->session->userdata("PP") == 0 && $this->session->userdata("TM") == 0 && $this->session->userdata('user_type') != "admin") || ($userData->emp_feild_worker == 1 && !$userData->emp_field_estimator)) : ?>
		<?php else : ?>
			<?php $this->load->view('profile_todo.php'); ?>
		<?php endif; ?>
		<!-- /Task Manager -->
		
		<?php //if(isset($estimator['user_active_employee']) && $estimator['user_active_employee'] != 0) : ?>
			<?php $this->load->view('login_employee.php'); ?>
		<?php //endif; ?>
<script>
	function getCookie(name) {
		var matches = document.cookie.match(new RegExp(
			"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
		))
		return matches ? decodeURIComponent(matches[1]) : undefined
	}
	if(getCookie('autologin'))
		location.href = baseUrl + 'screen';
</script>
		<?php //if(isset($estimator['user_active_employee']) && $estimator['user_active_employee'] != 0) : ?>
			<a href="#" class="showLogin">
				<i class="fa fa-user user-icon"></i>
			</a>
		<?php //endif; ?>
		
		<?php $this->load->view('includes/footer'); ?>

