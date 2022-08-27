<div id="groupUpdateModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">


	<div class="modal-body">
		<h5 class="p-bottom-20">Edit Group Information</h5>

		<form name="update_workorder_priority_form" action="">

			<div class="tabbable">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#tab1" data-toggle="tab">Group Details</a></li>
				</ul>

				<!-- tab content -->
				<div class="tab-content" style="padding-bottom: 9px;">
					<div class="tab-pane active" id="tab1">
						<input type="hidden" id="group_id" value="<?php echo $group_data->group_id; ?>">

						<!-- Tab 1 content -->
						<table>
							<tr>
								<td class="w-200">
									<label class="control-label">Group Name:</label>
								</td>
								<td class="p-left-30">
									<?php  $update_group = array(
										'id' => 'group_name',
										'name' => 'group_name',
										'value' => $group_data->group_name);?>
									<?php echo form_input($update_group) ?></td>
							</tr>
						</table>

					</div>
					<!-- /Tab 1 content -->

				</div>
				<!-- /tab content -->
			</div>
			<!-- /tabbable -->

	</div>

	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		<?php echo form_submit('submit', 'Save Changes', 'class="btn btn-info update__group"'); ?>
		</form>
	</div>

</div>

<script type="text/javascript">

	$(function () {

		$(".update__group").click(function () {
			var group_id = $("input#group_id").val();
			var group_name = $("input#group_name").val();
			var dataString = 'group_id=' + group_id + '&group_name=' + group_name;
			$.ajax({
				type: "POST",
				url: '<?php echo base_url();?>equipments/update_group/',
				data: dataString,
				success: function () {
					$("#groupUpdateModal").modal('hide');
					location.reload();

				}
			});
			return false;

		});
	});

</script>	
