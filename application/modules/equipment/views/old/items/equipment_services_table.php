<table class="table table-hover">
	<thead>
	<tr>
		<th width="22%">Service Name</th>
		<th>Periodicity</th>
		<th width="12%">Date</th>
		<th width="12%">Next</th>
		<th>Status</th>
		<th width="30%">Description</th>
		<th style="text-align:center;" width="9%">
			<a href="#" class="btn btn-success btn-xs addService" data-item_id="<?php echo $item_id; ?>"><i
					class="fa fa-plus"></i></a>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php $this->load->view('equipments/items/equipment_services_table_record'); ?>
	</tbody>
</table>
