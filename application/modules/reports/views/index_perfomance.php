<?php $this->load->view('includes/header'); ?>

	<section class="scrollable p-sides-15">
		<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
			<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
			<li class="active">Reports</li>
		</ul>
		<section class="panel panel-default">
			<header class="panel-heading">Perfomance Report | MHR(T) 80$/MH
				<div class="pull-right" style="margin-top:-14px;">
					<form id="dates" method="post" action="<?php echo base_url('reports/performance'); ?>" class="input-append m-t-xs">
						<label>
							<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
								   value="<?php if ($from) : echo date('Y-m-d', strtotime($from));
								   else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?>">
						</label>
						— 
						<label>
							<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
								   value="<?php if ($to) : echo date('Y-m-d', strtotime($to));
								   else : echo date('Y-m-d'); endif; ?>">
						</label>
						<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
					</form>
				</div>
			</header>
			<script>
				$(document).ready(function () {
					$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
				});
			</script>
		</section>
	<div class="row">
		<section class="col-md-12">
			<section class="panel panel-default p-n">
				<table class="table table-striped table-pulse">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">TREE WORK</th>
							<th class="text-center">MHR(A) $/MH</th>
							<th class="text-center">%ESTIMATED</th>
							<th class="text-center">COMPLIMENTS</th>
							<th class="text-center">COMPLAINS</th>
							<th class="text-center">DEMAGES</th>
							<th class="text-center">LATE ARRIVALS</th>
						</tr>
					</thead>
					<tbody>
					<?php if(isset($employees) && !empty($employees)) : ?>
						<?php foreach($employees as $key=>$employee) : ?>
							<tr>
								<td class="text-center"><?php echo $key+1;?></td>
								<td class="text-center"><?php echo $employee->emp_name;?></td>
								<td class="text-center"><?php echo money(round($employee->sum / $employee->count, 2));?></td>
								<td class="text-center"><?php echo round(($employee->sum / $employee->count / 80) * 100, 2);?>%</td>
									<td class="text-center">
										<div class="btn-group clear" style="display: block;overflow: visible;">
											<a href="#" class="dropdown-toggle count-<?php echo $employee->worked_employee_id . '-' . 1;?>" data-toggle="dropdown" style="color: #8ec165;">
												<?php echo isset($likes[$employee->worked_user_id][1]) ? count($likes[$employee->worked_user_id][1]) : 0;?>
											</a>
											<?php $data['likes'] = isset($likes[$employee->worked_user_id][1]) ? $likes[$employee->worked_user_id][1] : array();
											  $data['emp_id'] = $employee->worked_user_id;
											  $data['like_type'] = 1;
											  $this->load->view('likes_date', $data);
											?>
										</div>
									</td>
									<td class="text-center">
										<div class="btn-group clear" style="display: block;overflow: visible;">
											<a href="#" class="dropdown-toggle count-<?php echo $employee->worked_user_id . '-' . 2;?>" data-toggle="dropdown" style="color: #8ec165;">
												<?php echo isset($likes[$employee->worked_user_id][2]) ? count($likes[$employee->worked_user_id][2]) : 0;?>
											</a>
											<?php $data['likes'] = isset($likes[$employee->worked_user_id][2]) ? $likes[$employee->worked_user_id][2] : array();
											  $data['emp_id'] = $employee->worked_user_id;
											  $data['like_type'] = 2;
											  $this->load->view('likes_date', $data);
											?>
										</div>
									</td>
									<td class="text-center">
										<div class="btn-group clear" style="display: block;overflow: visible;">
											<a href="#" class="dropdown-toggle count-<?php echo $employee->worked_user_id . '-' . 3;?>" data-toggle="dropdown" style="color: #8ec165;">
												<?php echo isset($likes[$employee->worked_user_id][3]) ? count($likes[$employee->worked_user_id][3]) : 0;?>
											</a>
											<?php $data['likes'] = isset($likes[$employee->worked_user_id][3]) ? $likes[$employee->worked_user_id][3] : array();
												  $data['emp_id'] = $employee->worked_user_id;
												  $data['like_type'] = 3;
												  $this->load->view('likes_date', $data);
											?>
										</div>
									</td>
									<td class="text-center">
										<div class="btn-group clear" style="display: block;overflow: visible;">
											<a href="#" class="dropdown-toggle count-<?php echo $employee->worked_user_id . '-' . 4;?>" data-toggle="dropdown" style="color: #8ec165;">
												<?php echo isset($likes[$employee->worked_user_id][4]) ? count($likes[$employee->worked_user_id][4]) : 0;?>
											</a>
											<?php $data['likes'] = isset($likes[$employee->worked_user_id][4]) ? $likes[$employee->worked_user_id][4] : 0;
											  $data['emp_id'] = $employee->worked_user_id;
											  $data['like_type'] = 4;
											  $this->load->view('likes_date', $data);
											?>
										</div>
									</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>
			</section>
		</section>

	</div>
	</section>
	<script>
	$(document).ready(function(){
		$(document).on( 'click', '.likeLink', function() {
			$($(this).attr('href')).modal();
			return false;
		});
		$(document).on( 'click', '.eventsList', function() {
			$($(this).attr('href')).modal();
			return false;
		});
		$('.saveDate').click(function(){
			var obj = $(this);
			var id = $(this).attr('data-id');
			var type = $(this).attr('data-like');
			var objDates = $(obj).parent().find('.datepicker');
			var dates = [];
			
			$.each(objDates, function(key, val){
				//rand = Math.floor(Math.random() * (1000 - 1 + 1)) + 1;
				dates.push($(val).val()); 
			}); 
			if(dates.length || deleted.length)
			{
				$.post(baseUrl + 'reports/ajax_add_compliment', {id:id, type:type, dates:dates, deleted:deleted}, function (resp) {
					
					if (resp.status === 'ok') {
						$.each(dates, function(key, val){
							
							html = '<li><a href="#commandInfo-'+ val +'-'+ id +'" class="reportInfo" data-toggle="modal" data-backdrop="static" data-keyboard="false" style="display: inline-block;  width: 60%;">'+ val +'</a>     <button class="btn btn-danger btn-xs likeLink deleteLike" type="button" href="#" data-record-id="'+ resp.ids[key] +'"><i class="fa fa-trash-o"></i></button></li>';
							$(obj).parent().parent().append(html);
							
						});
						$(obj).parent().find('.date-block').remove();
						count = $(obj).parent().parent().parent().find('.count-'+id+'-'+type).text();
						$(obj).parent().parent().parent().find('.count-'+id+'-'+type).text(count - deleted.length + dates.length);
						deleted = [];
					}
					
				}, 'json');
				return false; 
			}
			return false;
		});
		$('.newLike').click(function(){
			var obj = $(this);
			var id = $(this).attr('data-id');
			var type = $(this).attr('data-like');
			html = '<div class="date-block" style="text-align: center; padding-top: 5px;"><input name="newCompliment" class="datepicker newPic form-control date-input-client newCompliment likeLink" type="text" readonly value="<?php if ($from) : echo date('Y-m-d', strtotime($from)); else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?>" style="width: 60%; padding: 0px; display: inline-block; margin: 0; text-align: center;"> <a class="btn btn-danger btn-xs likeLink delLike" type="button" href="#" style="display:inline-block"><i class="fa fa-trash-o"></i></a></div>';
			$(obj).parent().append(html);
			$('.newPic').datepicker({format: 'yyyy-mm-dd'});
			return false;
		});
		$(document).on('click', '.delLike', function(){
			$(this).parent().remove();
			return false;
		});
		$(document).on('click', '.deleteLike', function(){
			var obj = $(this);
			var id = $(this).attr('data-record-id');
			deleted.push(id);
			$(obj).parent().remove();
			return false;
		});
		deleted = [];
	});
</script>
<?php $this->load->view('includes/footer'); ?>
