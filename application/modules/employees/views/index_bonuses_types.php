<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Bonuses Types</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Bonuses Types
			<a class="btn btn-success btn-xs pull-right" type="button" style="margin-top: -1px;"
			   href="#bonus-" role="button"  data-toggle="modal" data-backdrop="static" data-keyboard="false">
				<i class="fa fa-plus"></i>
			</a>
			<div class="clear"></div>
		</header>
		
		<div id="bonus-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content panel panel-default p-n">
					<header class="panel-heading">Create Bonus</header>
					<div class="modal-body">
						<div class="form-horizontal">
							<div class="control-group">
								<label class="control-label">Name</label>

								<div class="controls">
									<input class="bonus_name form-control" type="text" value="" placeholder="Bonus Name">
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Descrition</label>

								<div class="controls">
									<textarea class="bonus_text form-control" rows="5" type="text"
									          placeholder="Bonus Description" style="background-color: #fff;"></textarea>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Amount</label>

								<div class="controls">
									<input class="bonus_amount form-control" type="text"
									       value=""
									       placeholder="Bonus Amount" style="background-color: #fff;">
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" data-save-bonus="">
							<span class="btntext">Save</span>
							<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
							     class="preloader">
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</div>
		</div>

		<div class="m-bottom-10 p-sides-10 table-responsive">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Description</th>
					<th>Amount</th>
					<th width="85px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php
				if ($bonuses) {
					foreach ($bonuses as $key => $bonus):
						?>
						<tr>
							<td><?php echo $key+1; ?></td>
							<td><?php echo $bonus->bonus_type_name; ?></td>
							<td style="white-space: pre-line;"><?php echo $bonus->bonus_type_description; ?></td>
							<td><?php echo $bonus->bonus_type_amount; ?>%</td>
							<td>
								<a class="btn btn-xs btn-default" href="#bonus-<?php echo $bonus->bonus_type_id; ?>" role="button"
									data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
									&nbsp;
								<a class="btn btn-xs btn-danger deleteBonus"
								   data-delete-id="<?php echo $bonus->bonus_type_id; ?>">
									<i class="fa fa-trash-o"></i>
								</a>
							</td>
						</tr>
					<?php
					endforeach;
				} else {
					?>
					<tr>
						<td colspan="5"><?php echo "No records found"; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</section>
<?php if ($bonuses) : ?>
	<?php foreach ($bonuses as $key => $bonus): ?>
	<div id="bonus-<?php echo $bonus->bonus_type_id; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Edit Bonus <?php echo $bonus->bonus_type_name; ?></header>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="control-group">
							<label class="control-label">Name</label>

							<div class="controls">
								<input class="bonus_name form-control" type="text"
									   value="<?php echo $bonus->bonus_type_name; ?>"
									   placeholder="Bonus Name" style="background-color: #fff;">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Descrition</label>

							<div class="controls">
								<textarea class="bonus_text form-control" rows="5" type="text"
								  placeholder="Bonus Description" style="background-color: #fff;"><?php echo $bonus->bonus_type_description; ?></textarea>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Amount</label>

							<div class="controls">
								<input class="bonus_amount form-control" type="text"
									   value="<?php echo $bonus->bonus_type_amount; ?>"
									   placeholder="Bonus Amount" style="background-color: #fff;">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" data-save-bonus="<?php echo $bonus->bonus_type_id; ?>">
						<span class="btntext">Save</span>
						<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
							 style="display: none;width: 32px;" class="preloader">
					</button>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
<?php endif; ?>
		<script type="text/javascript">
	
	$(document).ready(function(){
		
		$(document).on("click", '[data-save-bonus]', function(){
			console.log(111);
			var id = $(this).data('save-bonus');
			var name = $('#bonus-' + id + ' .bonus_name').val();
			var text = $('#bonus-' + id + ' .bonus_text').val();
			var amount = $('#bonus-' + id + ' .bonus_amount').val();

			$(this).attr('disabled', 'disabled');
			$('#bonus-' + id + ' .modal-footer .btntext').hide();
			$('#bonus-' + id + ' .modal-footer .preloader').show();
			$('#bonus-' + id + ' .bonus_name').parents('.control-group').removeClass('has-error');
			
			if (!name) {
				$('#bonus-' + id + ' .bonus_name').parents('.control-group').addClass('has-error');
				$('#bonus-' + id + ' .modal-footer .btntext').show();
				$('#bonus-' + id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			if (!amount) {
				$('#bonus-' + id + ' .bonus_amount').parents('.control-group').addClass('has-error');
				$('#bonus-' + id + ' .modal-footer .btntext').show();
				$('#bonus-' + id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'employees/ajax_save_bonus', {id : id, name : name, text:text, amount:amount}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteBonus').click(function () {
			var id = $(this).data('delete-id');
			if (confirm('Are you sure?')) {
				$.post(baseUrl + 'employees/ajax_delete_bonus', {id: id}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
	});
</script>
		<?php $this->load->view('includes/footer'); ?>
