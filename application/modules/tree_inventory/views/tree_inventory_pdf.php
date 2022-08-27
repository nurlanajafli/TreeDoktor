<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">
	

	<link rel="stylesheet" media="print" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>

	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>
	
	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/css/workorder_pdf.css" type="text/css" media="print">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/events/events.css'); ?>">
	
	<style>
		input,
		button,
		select,
		textarea {
		    
		    font-size: 16px;
		    line-height: 1;
		}
		/*
		@page chapter1{
			size: auto;
		}
		*/
		@page{
			size: landscape;
			margin-bottom: 0cm;
		}
		

		div.chapter2 {
			page: chapter2;
		}
		div.chapter1 {
			page: chapter1;
		}
		.col-xs-4, .col-xs-5, .col-xs-6{ margin-right: 0; }
		.col-xs-4, .col-xs-5, .col-xs-6{ padding-right: 0; }
		
		.label-col{
			background: #e8e8e8!important;
		}
		table td, .col-padding{
			padding: 3px 5px 3px;
		}

		.border{ border: 1px solid #ddd; }
		.border-right{ border-right: 1px solid #ddd; }
		.border-left{ border-left: 1px solid #ddd; }
		.border-top{ border-top: 1px solid #ddd; }
		.border-bottom{ border-bottom: 1px solid #ddd; }
	</style>
</head>
<body>
	<?php if(isset($pagebreak) && $pagebreak): ?>
		<pagebreak orientation="landscape" />
		<style type="text/css">.tree-inventory{
        padding-left: 55px;
    }</style>
	<?php endif; ?>
	<div class="tree-inventory">
	<?php echo $html; ?>
	</div>
</body>
</html>
