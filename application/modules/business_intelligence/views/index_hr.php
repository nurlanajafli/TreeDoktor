<?php $this->load->view('includes/header');?>
 <link href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>" rel="stylesheet">
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
		<li class="active">HR</li>
	</ul>
		<div class="panel panel-default" style="">
			<header class="panel-heading">HR
                <a href="#reason" class="btn btn-xs btn-success pull-right addAbsance js-create-absent-days" role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
				<div class="pull-right" >
					<form id="dates" method="post" action="<?php echo base_url('business_intelligence/absent_days'); ?>" class="input-append m-t-xs">
						<label>
							<input name="from" class="datepicker form-control date-input-client from text-center" type="text" readonly
                                    value="<?php if ($from) : echo getDateTimeWithDate($from, 'Y-m-d');
                                    else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
						</label>
						— &nbsp;&nbsp;
						<label>
							<input name="to" class="datepicker form-control date-input-client to text-center" type="text" readonly
                                   value="<?php if ($to) : echo getDateTimeWithDate($to, 'Y-m-d');
                                   else : echo date(getDateFormat()); endif; ?>">
						</label>
						<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
					</form>
				</div>
				<div class="clear"></div>
			</header>
			
			<div class="p-5">
				<div class="table-responsive">
					<table class="table table-striped m-b-none b-a employees-hr">
						<thead>
						<tr>
							<th class="text-center bg-light b-r b-b" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf; border-bottom: 1px solid #cfcfcf;" width="105px"></th>
							<?php $first = 0; ?>
							<?php foreach($reasons as $key=>$val) : ?>
                                <th class="text-center bg-light b-r b-b" width="100" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf; vertical-align: middle;">
                                    <?php echo $val->reason_name; ?>
                                </th>
							<?php endforeach; ?>
                            <th class="text-center bg-light b-r b-b" width="100" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf; vertical-align: middle;">
                                Total
                            </th>
						</tr>
						</thead>
						<tbody>
							<?php foreach($users as $key=>$val) : ?>

								<tr data-user-id="<?php echo $val['id']; ?>">
									<td class="text-center bg-light b-r b-b" width="100" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf;">
										<strong>
											<?php echo $key; ?>
										</strong>
									</td>
									<?php $first = 0; ?>
									
									<?php foreach($reasons as $k=>$v) : ?>
										<?php if(!$v->reason_company) : ?>
											<td class="text-center" data-reason="<?php echo $v->reason_id; ?>">
												<a href="#reason" class="addAbsance" data-user_id="<?php echo $val['id']; ?>" data-reason_id="<?php echo $val[$v->reason_name]['id']; ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false">
													<?php echo $val[$v->reason_name]['count']; ?>
												</a>
											</td>
										<?php elseif(!$first): ?>
											<td class="text-center" data-reason="<?php echo $v->reason_id; ?>">
												<a href="#reason" class="addAbsance" data-user_id="<?php echo $val['id']; ?>" data-company="<?php echo $v->reason_company; ?>" data-reason_id="<?php echo $val[$v->reason_name]['id']; ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false">
													<?php echo $val[$v->reason_name]['count']; ?>
												</a>
											</td>
											<?php $first = 1; ?>
										<?php else : ?>
											<td class="text-center" data-reason="<?php echo $v->reason_id; ?>">
												<a href="#reason" class="addAbsance" data-user_id="<?php echo $val['id']; ?>" data-reason_id="<?php echo $val[$v->reason_name]['id']; ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false">
													<?php echo $val[$v->reason_name]['count']; ?>
												</a>
											</td>
										<?php endif; ?>
									<?php endforeach; ?>
                                    <td class="text-center total">
                                        <?php echo $val['total'];?>
                                    </td>
									
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<div id="reason" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content panel panel-default p-n">
					<form data-type="ajax" data-url="<?php echo base_url('business_intelligence/ajax_save_absence'); ?>" enctype="multipart/form-data" id="reasonForm" method="post" data-callback="saveSuccess">
					<header class="panel-heading">Create Day Off</header>
					<div class="modal-body">
						<div class="form-horizontal">
							<div class="form-group">
								<label class="col-sm-2 control-label">User</label>
								<div class="col-sm-10">
									<select name="employee" class="form-control">
										<?php foreach($employees as $key=>$val) : ?>
											<option value="<?php echo $val['id']; ?>"><?php echo $val['emp_name']; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">Reason</label>
								<div class="col-sm-10">
									<select name="reason" class="form-control">
										<?php foreach($reasons as $key=>$val) : ?>
											<option value="<?php echo $val->reason_id; ?>"><?php echo $val->reason_name; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">Date</label>
								<div class="col-sm-5">
									<input name="dayofffrom" class="datepicker dayofffrom form-control text-center" type="text" readonly value="<?php echo date(getDateFormat()); ?>">
								</div>
								<div class="col-sm-5">
									<input name="dayoffto" class="datepicker dayoffto form-control text-center" type="text" readonly value="<?php echo date(getDateFormat()); ?>">
								</div>
							</div>
                            <?php if(config_item('messenger')) : ?>
							<div class="form-group">
								<div class="col-sm-3 control-group">
									<label class="checkbox">
										<span>Send SMS</span>
										<input type="checkbox" class="pull-right" name="sms" onchange="showHideSms();">
									</label>
								</div>
								<div class="col-sm-9 showHideSms hide">
									<textarea style="resize: none; min-height: 80px;" class="form-control" placeholder="Text sms" name="sms_text">Hi [NAME]!
This is a system message. You will have a day off(s) from [FROM] to [TO]</textarea>
								</div>
								
							</div>
                            <?php endif; ?>
							
						</div>

                        <div class="js-table-absence" style="display: none">
                            <?php $this->load->view('table_absence');?>
                        </div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" data-save-reason="">
							<span class="btntext">Save</span>
							<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
								 class="preloader">
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
					</form>
				</div>
			</div>
		</div>
		</div>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
<script>
	var from = '<?php echo json_encode($from); ?>';
	var to = '<?php echo json_encode($to); ?>';
	
	function showHideSms()
	{
		if($('#reasonForm').find('[name="sms"]').prop('checked'))
			$('#reasonForm').find('.showHideSms').removeClass('hide');
		else
			$('#reasonForm').find('.showHideSms').addClass('hide');
	}
	
	function saveSuccess(resp)
	{
	    console.log(resp);

        if (resp.status !== 'ok') {
            $('#errorMessage').remove();

            return false;
        }
		$('.saveForm').attr('disabled', 'disabled').addClass('disabled');
		$('#processing-modal').modal();
		
		var obj = $('tr[data-user-id="'+ resp.user_id +'"] td[data-reason="'+ resp.reason +'"] a');
		var company = $(obj).data('company') ? $(obj).data('company') : '';
		
		$(obj).text(parseInt($(obj).text()) + resp.count);
		if(!company)
			$(obj).parents('tr:first').find('td.total').text(parseInt($(obj).parents('tr:first').find('td.total').text()) + resp.count);
		$('#reason').modal('hide');
		return false;
	}
	$(document).ready(function (){
        $('.datepicker').datepicker({format: $('#php-variable').val()});

        let reasonModal = $('#reason');
        let reasonModalDefaultHtml = reasonModal.html();
        reasonModal.on('hidden.bs.modal', function (e) {
            reasonModal.html(reasonModalDefaultHtml);
            $('.datepicker').datepicker({format: $('#php-variable').val()});
        });

		$(document).on('click', '.removeAbs', function(){
			var obj = $(this);
			var user = $(obj).attr('data-id');
			var reason = $(obj).attr('data-reason');
			var date = $(obj).attr('data-date');
			var row = $('tr[data-user-id="'+ user +'"] td[data-reason="'+ reason +'"] a');
			var company = $(row).data('company') ? $(row).data('company') : '';
			if(confirm('Are you sure?'))
			{
				$.ajax({
					global: false,
					method: "POST",
					data: {user:user,date:date,reason:reason},
					url: base_url + "business_intelligence/ajax_delete_absence",
					dataType:'json',
					success: function(response){
						if (response.status == 'ok')
						{
							$(obj).parent().parent().remove();
							$(row).text(parseInt($(row).text()) - 1);
							if(!company)
								$(row).parents('tr:first').find('td.total').text(parseInt($(row).parents('tr:first').find('td.total').text()) - 1);
						}
						return false;
					}
				});
			}
			return false;
		});
	});
	
	$(document).on('change', '.dayofffrom', function() {
		var val = $(this).val();
		$(this).parent().parent().parent().find('.dayoffto').val(val);
		return false;
	});
	$(document).on('click', '.addAbsance', function(e){
		// var now = new Date();
		// var formated_date = now.dateFormat("Y-m-d");
		var userId = $(this).data('user_id') ? $(this).data('user_id') : '';
		var reasonId = $(this).data('reason_id') ? $(this).data('reason_id') : '';
		var sms = $('#reasonForm').find('[name="sms"]');
		var text = $('#reasonForm').find('[name="sms_text"]');
		
		// $('#reasonForm [name="dayofffrom"]').val(formated_date);
		// $('#reasonForm [name="dayoffto"]').val(formated_date);
		$('#reasonForm').find('select[name="employee"]').val(userId);
		$('#reasonForm').find('select[name="reason"]').val(reasonId);

        //skip ajax call
        if ($(e.currentTarget).hasClass('js-create-absent-days')) {
            $('.js-table-absence').hide();
            return false;
        }

        $('.js-table-absence').show()

        $.ajax({
            global: false,
            method: "POST",
            data: {user:userId,from:from,to:to,reason:reasonId},
            url: base_url + "business_intelligence/ajax_get_absence",
            dataType:'json',
            success: function(response){
                if (response.status != 'ok')
                    alert('Ooops! Error');
                else
                    $('#reasonForm .table-absence-rows').html(response.html);
                return false;
            }
        });

		return false;
	});
</script>
<?php $this->load->view('includes/footer'); ?>
