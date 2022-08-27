<?php $this->load->view('includes/header'); ?>

<script>
	$(document).ready(function () {
		$('.header').click(function () {
			var sort_opt = $('#sort_opt').val();
			var sort_order = $('#sort_order').val();
			if (sort_order == 'ASC') {
				$(this).addClass('.headerSortUp');
				$(this).removeClass("headerSortDown");
			} else {
				$(this).removeClass('.headerSortUp');
				$(this).addClass("headerSortDown");
			}
		});
	});
	function sortpt(opt) {
		//alert(opt);
		$('#sort_opt').val(opt);
		var sort_order = $('#sort_order').val();
		//alert(sort_order);
		if (sort_order == 'ASC') {
			$('#sort_order').val('DESC');

		} else {
			$('#sort_order').val('ASC');
		}
		//  alert($('#sort_order').val());
		$('#search').submit();
	}
</script>

<!-- Search result data -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Global Search</li>
	</ul>
	<section class="col-md-12 panel panel-default p-n">
		<header class="panel-heading">Search Result (<?php echo $search_count; ?>)</header>
		<!-- Data display -->
		<div class="m-bottom-10 p-sides-10">
			<table class="table table-hover" id='search_table'>
				<thead>
				<tr>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'clients.client_name') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("clients.client_name");'>Client Name
					</th>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'clients_contacts.cc_phone') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("clients_contacts.cc_phone");'> Phone Number & <br>Address
					</th>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'clients.client_contact') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("clients.client_contact");'>Client Contact
					</th>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'estimates.estimate_no') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("estimates.estimate_no");'>Estimate No
					</th>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'estimates.status') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("estimates.status");'>Estimate Status
					</th>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'workorders.workorder_no') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("workorders.workorder_no");'>Workorder No
					</th>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'workorders.wo_status') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("workorders.wo_status");'>Workorder Status
					</th>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'invoices.invoice_no') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("invoices.invoice_no");'>Invoice No
					</th>
					<th class="header <?php if (isset($sort_opt, $sort_order) && $sort_opt == 'invoices.in_status') {
						if ($sort_order == 'ASC') {
							echo 'headerSortDown';
						} else {
							echo 'headerSortUp';
						}
					} ?>" onclick='sortpt("invoices.in_status");'>Invoice Status
					</th>
				</tr>
				</thead>
				<tbody id="tbl_search_result">
				<?php
				if (!empty($search_data)) {
					foreach ($search_data as $row) {
						?>
						<tr>
							<td><?php echo anchor('client/' . $row->client_id, ucfirst($row->client_name)); ?></td>
							<td>
								<a href="#" class="<?php if($row->cc_phone == numberTo($row->cc_phone)) : ?>text-danger<?php else : ?>createCall<?php endif;?>" data-client-id="<?php echo $row->client_id; ?>" data-number="<?php echo substr($row->cc_phone, 0, 10);?>">
									<?php echo numberTo($row->cc_phone); ?>
								</a>
								<br>
								
								<?php echo ucfirst($row->client_address) . ",&nbsp;" . ucfirst($row->client_city); ?></td>
							<td><?php echo ucfirst($row->cc_name); ?></td>
							<td><?php if ($row->estimate_id) {
									echo anchor($row->estimate_no, $row->estimate_no);
								} else {
									echo "N/A";
								} ?></td>
							<td><?php if ($row->status) {
									echo ucfirst($row->status);
								} else {
									echo "N/A";
								} ?></td>
							<td><?php if ($row->workorder_id) {
									echo anchor($row->workorder_no, $row->workorder_no);
								} else {
									echo "N/A";
								} ?></td>
							<td><?php if ($row->wo_status != NULL) {
									echo ucfirst($row->wo_status_name);
								} else {
									echo "N/A";
								} ?></td>
							<td><?php if ($row->invoice_id) {
									echo anchor($row->invoice_no, $row->invoice_no);
								} else {
									echo "N/A";
								} ?></td>
							<td><?php if ($row->in_status) {
									echo ucwords($row->in_status);
								} else {
									echo "N/A";
								} ?></td>
						</tr>
					<?php } ?>

					<?php if ($pagination_link) : ?>
						<tr>
						<td colspan="9"><?php echo $pagination_link; ?></td>
					<?php endif; ?>

				<?php } else { ?>
					<tr>
						<td colspan="5"><p style="color:#FF0000;"> No records found</p></td>
					</tr>
				<?php } ?>

				</tbody>
			</table>
		</div>

		<!-- Pagination Control -->

	</section>
</section>

<?php $this->load->view('includes/footer'); ?>
