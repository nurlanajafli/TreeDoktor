<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="<?php echo base_url('equipment'); ?>">Equipment</a></li>
        <li class="active">Equipment - <?php echo $group_data->group_name; ?></a></li>
	</ul>
    <!-- Edit Equipment Details Modal Loader -->
	<?php $this->load->view('equipments/equipment_information_update_modal'); ?>
	<!-- End of Edit Customer Details Modal-->

    <!-- Equipment information display -->
	<?php $this->load->view('equipments/equipment_information_files'); ?>

	<?php $this->load->view('includes/footer'); ?>
