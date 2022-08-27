<?php $this->load->view('includes/header'); ?>
    <script>
        var mapCircles = <?php echo is_array(config_item('leads_circles')) ? json_encode(config_item('leads_circles')) : json_encode([]); ?>;
        var mapCirclesArr = [];
    </script>
	<section class="hbox stretch inventory-map-screen">
		<form data-type="ajax" class="hidden" data-url="<?php echo site_url('tree_inventory/upload_map_image'); ?>" data-callback="TreeInventoryHelper.reload_page" id="upload-map-image">
			<input type="hidden" name="ti_lead_id" value="<?php if(isset($ti_lead_id)): echo $ti_lead_id; endif; ?>">
			<input type="hidden" name="ti_client_id" value="<?php echo $client->client_id; ?>">
			<input type="file" name="map" id="map-image">
		</form>
		<form data-type="ajax" class="hidden" data-url="<?php echo site_url('tree_inventory/delete_map_image'); ?>" data-callback="TreeInventoryHelper.reload_page" id="delete-map-image">
			<input type="hidden" name="ti_lead_id" value="<?php if(isset($ti_lead_id)): echo $ti_lead_id; endif; ?>">
			<input type="hidden" name="ti_client_id" value="<?php echo $client->client_id; ?>">
		</form>

		<form data-type="ajax" class="hidden" data-url="<?php echo site_url('tree_inventory/copy_tree_inventory'); ?>" data-callback="TreeInventoryHelper.copy_tree_inventory_success" id="copy-tree-inventory">
			<input type="hidden" name="ti_lead_id_from" value="<?php if(isset($ti_lead_id)): echo $ti_lead_id; endif; ?>">
			<input type="hidden" name="ti_client_id_from" value="<?php echo $client->client_id; ?>">
			
			<input type="hidden" name="ti_lead_id_to" value="">
			<input type="hidden" name="ti_client_id_to" value="">
		</form>
		<aside class="col-lg-12 col-xs-12 col-sm-12 col-md-12 inventory-map-container" style="overflow-x: auto!important;">
			<?php $this->load->view('_partials/map_container'); ?>
		</aside>
		<aside class="col-lg-3 col-xs-9 col-sm-7 col-md-6 inventory-list-container">
			
				<?php $this->load->view('templates/tree_list'); ?>
			
		</aside>
        <input type="hidden" class="client_lat" value="<?= $client->client_lat; ?>">
        <input type="hidden" class="client_lon" value="<?= $client->client_lng; ?>">
        <input type="hidden" class="client_address" value="<?= $client->client_address; ?>">
        <input type="hidden" class="client_city" value="<?= $client->client_city; ?>">
        <input type="hidden" class="client_state" value="<?= $client->client_state; ?>">
        <input type="hidden" class="client_zip" value="<?= $client->client_zip; ?>">
        <input type="hidden" class="client_country" value="<?= $client->client_country; ?>">
        <input type="hidden" class="leads" value="<?= $leads ?? [] ?>">

	</section>


<?php $this->load->view('templates/create_lead_modal'); ?>
<?php $this->load->view('templates/infowindowform'); ?>
<?php $this->load->view('templates/tree_list_modal'); ?>
<?php $this->load->view('templates/project_modal'); ?>

<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">
<script src="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.js'); ?>"></script>

<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.js"></script>
<script src="<?php echo base_url('assets/js/libs/konva/konva.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/libs/touchSwipe/jquery.touchSwipe.min.js'); ?>"></script>

<script src="<?php echo base_url(); ?>assets/js/modules/clients/clients.js?v=<?php echo config_item('js_clients'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/leads/leads.js?v=1.21"></script>

<script src="<?php echo base_url('assets/js/modules/tree_inventory/tree_inventory_helper.js?v=1.2'); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/tree_inventory/tree_inventory_map.js?v=3.21'); ?>"></script>

<script src="<?php echo base_url('assets/js/modules/tree_inventory/tree_inventory_image.js?v=1.61'); ?>"></script>

<script src="<?php echo base_url('/assets/js/html2canvas.js'); ?>"></script>

<link type="text/css" rel="stylesheet" href="<?php echo base_url('assets/css/modules/tree_inventory/tree_inventory.css'); ?>">

<link type="text/css" rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/select2/select2.css'); ?>">
<link type="text/css" rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/select2/theme.css'); ?>">


<?php $this->load->view('includes/footer'); ?>
