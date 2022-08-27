<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/payroll_pdf.css">
	<style>
		@page{padding: 10px; }
		body{padding: 200px;}
	</style>
</head>
<body style="border: 0!important;">
	<div class="holder p_top_20 text-center">
		<img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/header2.png" class="p-top-20" style="margin-left: 0px; ">
	</div>
	<div style="margin: 10px 20px 20px 30px">
		<?php $this->load->view('payroll_overview_table');?>
	</div>
	<div class="address" style="position: absolute; bottom: 20px; right: 0; left: 0;">
		<?php echo $this->config->item('footer_pdf_address'); ?>
	</div>
</body>
</html>
