<table class="table table-hover">
	<thead>
	<tr>
		<th width="25%">Part Name</th>
		<th width="30%">Seller</th>
		<th width="25%">Price</th>
		<th width="20%">Date</th>
		<th style="text-align:center;">
			<a href="#" class="btn btn-success btn-xs addPart" data-item_id="<?php echo $item_id; ?>"><i
					class="fa fa-plus"></i></a>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php $this->load->view('equipments/items/equipment_parts_table_record'); ?>
	</tbody>
</table>
