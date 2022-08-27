
<table class="table table-striped b-t b-light m-n">
	<thead>
		<tr>
			<th width="10%">Date</th>
			<th width="10%">Type</th>
			<th width="10%">Item</th>
			<th width="10%">Employee</th>
			<th width="10%">Created</th>
			<th width="10%">Amount</th>
			<th width="10%"><?php echo 'Tax'; ?></th>
			<th width="10%">Total</th>
			<th width="15%">Description</th>
			<th width="80px">Action</th>
		</tr>
	</thead>
	<tbody>
        <?php $this->load->view('expenses_table_tbody'); ?>
	</tbody>
</table>

