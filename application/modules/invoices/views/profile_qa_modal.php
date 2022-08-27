<div id="addInvoiceQA" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Add Quality Assurance</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Type:</label>
						<?php  $qa_types = $this->config->item('qa_types'); ?>
						<div class="controls">
							<select id="assurancesTypes" class="form-control">
								<option value="">Select QA Type</option>
								<?php foreach($qa_types as $k=>$v) : ?>
									<option value="<?php echo $k?>"><?php echo ucfirst($v); ?></option>
								<?php endforeach;?> 
							</select>
						</div>
					</div>
					<div class="control-group" style="display:none;">
						<label class="control-label">Name:</label>
						
						<div class="controls">
							<select id="assurances" class="form-control">
								<option>Select Subject</option>
								<?php foreach ($qa as $assurance) : ?>
									<option value="<?php echo $assurance->qa_id; ?>"
									        data-type="<?php echo $assurance->qa_type_int; ?>"
									        data-description="<?php echo $assurance->qa_description; ?>"><?php echo $assurance->qa_name; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Message:</label>

						<div class="controls" style="position:relative;">
							<textarea class="qa_message form-control" id="assurancesMessage"
							          placeholder="Quality Assurance Message" rows="5"></textarea>
						</div>
						<span class="col-sm-2 btn btn-success m-t-sm m-b-sm pull-right" id="saveQA"
						      data-estimate_id="<?php echo $invoice_data->estimate_id; ?>"
						      style="display: none;">Save</span>
					</div>
				</div>
				<table class="table table-striped m-t-sm">
					<thead>
					<th style="width: 100px;">Date</th>
					<th style="width: 75px;">Type</th>
					<th>Name</th>
					<th>Message</th>
					</thead>
					<tbody>
					<?php $this->load->view('estimate_qa'); ?>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function () {
		$('#assurancesTypes').change(function () {
			if ($(this).val()) 
			{
				type = $(this).val();
				$.post(baseUrl + 'invoices/ajax_qa_select', {type:type}, function (resp) {
					if (resp.status == 'ok') {
						$('option', $("#assurances")).remove();
						$('#assurances').html('<option>Select Subject</option>');
						$.each(resp.qa, function(key, val){
							$('#assurances').append('<option value="'+ val.qa_id +'" data-type="'+ val.qa_type_int +'" data-description="'+ val.qa_description +'">'+ val.qa_name +'</option>');
						});
					}
					else {
						alert('Ooops! Error!');
					}
					return false;
				}, 'json');
				
				$('#assurances option[value]').css('display', 'none');
				$('#assurances option[value][data-type="' + $(this).val() + '"]').css('display', 'block');
				$('#assurances').parent().parent().slideDown();
				$('#assurances').val('Select Subject');
			}
			else {
				$('#assurances').parent().parent().slideUp();
				$('#assurancesMessage').val('');
				$('#saveQA').css('display', 'none');
			}
			return false;
		});
		$('#assurances').change(function () {
			$('#assurancesMessage').val($('#assurances option[value="' + $(this).val() + '"]').data('description'));
			$('#saveQA').css('display', 'block');
			if ($(this).val() == 'Select Subject')
				$('#saveQA').css('display', 'none');
			return false;
		});
		$('#assurancesMessage').on("keypress keyup blur", function (event) {
			if (!$('#assurancesMessage').val())
				$('#saveQA').css('display', 'none');
			else {
				if ($('#assurances').val() != 'Select Subject')
					$('#saveQA').css('display', 'block');
				else
					$('#saveQA').css('display', 'none');
			}
		});
		$('#saveQA').click(function () {
			var qa_id = $('#assurances').val();
			var qa_message = $('#assurancesMessage').val();
			var estimate_id = $(this).data('estimate_id');
			$.post(baseUrl + 'estimates/ajax_add_qa', {qa_id: qa_id, qa_message: qa_message, estimate_id: estimate_id}, function (resp) {
				if (resp.status == 'ok') {
					location.reload();
				}
				else {
					alert('Ooops! Error!');
				}
				return false;
			}, 'json');
		});
	});
</script>
