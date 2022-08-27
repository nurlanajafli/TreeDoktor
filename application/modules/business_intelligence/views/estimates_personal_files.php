<?php if (isset($estimators_files) && $estimators_files != "") { ?>
	<!-- Display New Estimates -->
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Estimator's files:</header>
		<table class="table table-hover" id="tbl_Estimated">
			<thead>
			<tr>
				<th>Client Name</th>
				<th>Address</th>
				<th>Count Contacts</th>
				<th>Phone</th>
				<th width="100px">Date</th>
				<th width="100px">Total</th>
				<th width="165" class="sorter-grades">Status</th>
				<th width="110px">Action</th>
			</tr>
			</thead>
			<tbody id="estimatorFiles">
			<?php foreach ($estimators_files->result() as $rows) { ?>
				<tr>
					<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
					<td><?php echo $rows->client_address . ",&nbsp;" . $rows->client_city; ?></td>
					<td><?php echo $rows->estimate_count_contact; ?></td>
					<td>
						<a href="#" class="<?php if($rows->cc_phone == numberTo($rows->cc_phone)) : ?>text-danger<?php else : ?>createCall<?php endif;?>" data-client-id="<?php echo $rows->client_id; ?>" data-number="<?php echo substr($rows->cc_phone, 0, 10);?>">
							<?php echo numberTo($rows->cc_phone); ?>
						</a>
					</td>
<!--					<td>--><?php //echo date('Y-m-d', $rows->date_created); ?><!--</td>-->
					<td><?php echo getDateTimeWithTimestamp($rows->date_created); ?></td>
					<td><?php echo money($rows->sum_without_tax); ?></td>
					<td data-status="<?php echo $rows->status_priority; ?>"><?php echo $rows->status; ?></td>
					<td>
						<?php echo anchor('estimates/edit/' . $rows->estimate_id, '<i class="fa fa-pencil"></i>', 'class="btn btn-default btn-xs"') ?>
						<?php echo anchor($rows->estimate_no, '<i class="fa fa-eye"></i>', 'class="btn btn-default btn-xs"') ?>
						<?php echo anchor($rows->estimate_no . '/pdf', '<i class="fa fa-file"></i>', 'class="btn btn-default btn-xs" target="_blank"') ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="col-md-12 text-center p-5">
			<a href="#" class="more btn btn-default">Load More</a>
		</div>
	</section>
<?php } ?>
<?php //if(isset($user_id)) : ?>
<script>
	var offset = 1000;
	var load = false;
	$(document).ready(function(){
		$('.more').click(function() {
			if(load)
				return false;
			load = true;
			/*if($('.scrollable.p-sides-15').scrollTop() + 1120 >= $('.scrollable.p-sides-15')[0].scrollHeight) {
				load = true;*/
				var estimator_id = $('[name="user_id"] option[selected]').attr('value');
				if(!estimator_id)
				{
					estimator_id = <?php echo $this->session->userdata('user_id'); ?>;
					/*if(!estimator_id)
					{
						$('.scrollable.p-sides-15').unbind('scroll');
						return false;
					}*/
				}
				$.post(baseUrl + 'estimates/ajax_load_estimates', {offset:offset, estimator_id:estimator_id}, function(resp){
					if(resp.status == 'ok')
					{
						if(!resp.estimators_files.length || resp.estimators_files.length < 1000)
							$('a.more').remove();
						offset += 1000;
						$.each(resp.estimators_files, function(key, val){
							html = '';
							date = new Date(val.date_created * 1000);
							showedDate = date.getFullYear() + '-' + leadZero(date.getMonth() + 1, 2) + '-' + leadZero(date.getDate(), 2);
							html += '<tr>' +
								'<td><a href="' + baseUrl + val.client_id + '">' + val.client_name + '</a></td>' +
								'<td>' + val.client_address + ",&nbsp;" + val.client_city + '</td>' +
								'<td>';
							if(val.estimate_count_contact)
								html += val.estimate_count_contact;
							else
								html += "&nbsp;";
								
							html += '</td>' +
								'<td>' + val.cc_phone + '</td>' +
								'<td>' + showedDate + '</td>' +
								'<td>' + Common.money(val.total) + '</td>' +
								'<td>' + val.status + '</td>' +
								'<td>'
									+ '<a class="btn btn-default btn-xs" href="' + baseUrl + 'estimates/edit/' + val.estimate_id + '">' + '<i class="fa fa-pencil"></i>' + "</a> \n"
									+ '<a class="btn btn-default btn-xs" href="' + baseUrl + '/' + val.estimate_no + '">' + '<i class="fa fa-eye"></i>' + '</a>'
									+ '<a class="btn btn-default btn-xs" href="' + baseUrl + '/' + val.estimate_no + '/pdf' + '"  target="_blank">' + '<i class="fa fa-file"></i>' + '</a>'
								+ '</td>' +
								'</tr>';
							$('#estimatorFiles').append(html);
						});
						load = false;
					}
				}, 'json');
			//}
		});
	});
	
	function leadZero(number, length) {
		while(number.toString().length < length){
			number = '0' + number;
		}
		return number;
	}
</script>
<?php //endif; ?>
