<?php $this->load->view('includes/header'); ?>
<script type="text/javascript">
	$(document).ready(function () {

//        $.ajax({
//            type:"POST",
//            url:"
		<?php echo base_url(); ?>employees/ajax_employee_delete",
//            data:{
//                action:'delete_emp',
//                emp_id: this_val
//                },
//            success:function(result){
//                //alert(result);
//                $("#tbl_search_result").html(result);
//            },
//            error: function(XMLHttpRequest, textStatus, errorThrown) {
//                alert(textStatus);
//                        AJAX_IS_RUNNING = false;
//                        //HideLoaders();
//                        //HideLiveSearch();
//                }
//        });

	});

</script>
<style>.font-bold{font-weight: bold;}</style>
<section class="scrollable p-sides-15 <?php if(isset($pdf)) : ?> bg-white <?php endif; ?>">
	<?php if(!isset($pdf)) : ?>
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Employees</li>
	</ul>
	<?php endif; ?>
	<section class="panel panel-default">
		<header class="panel-heading">Employees
			<?php if(!isset($pdf)) : ?>
				<select name="txtstatus" class="form-control m-l-md"
						onchange="location.href = baseUrl + 'employees/' + $(this).val()"
						style="width: 200px; display: inline-block;">
					<option value="current"<?php if ($status == 'current') : ?> selected<?php endif; ?>>Current</option>
					<option value="temporary"<?php if ($status == 'temporary') : ?> selected<?php endif; ?>>Temporary
					</option>
					<option value="past"<?php if ($status == 'past') : ?> selected<?php endif; ?>>Past</option>
					<option value="on_leave"<?php if ($status == 'on_leave') : ?> selected<?php endif; ?>>On leave</option>
				</select>
			<?php endif; ?>
			<a class="btn btn-success btn-mini pull-right" type="button" style="margin-top: -1px;"
			   href="<?php echo base_url(); ?>employees/employee_add">
				<i class="fa fa-plus"></i>
				<i class="fa fa-user"></i>
			</a>
			<a class="btn btn-info btn-mini pull-right" type="button" style="margin-top: -1px; margin-right: 6px;"
			   href="<?php echo base_url(); ?>employees/employee_pdf/<?php echo isset($status) ? $status : ''; ?>">
				<i class="fa fa-print"></i>
			</a>
		</header>

		<div class="m-bottom-10 p-sides-10">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Phone</th>
					<th>Positon</th>
					<?php if(!isset($pdf)) : ?>
						<th>Action</th>
					<?php endif; ?>
				</tr>
				</thead>
				<tbody>
				<?php
				if ($employee_row) {
					foreach ($employee_row as $key => $row):
						?>
						<tr>
							<td class="font-bold"><?php echo ($key + 1); ?></td>
							<td class="font-bold">
								<?php if ($this->session->userdata('user_type') == "admin" && !isset($pdf)) : ?>
									<a href="<?php echo base_url('/employees/loginAs/' . $row->employee_id); ?>" title="Login As <?php echo $row->emp_name; ?>" onclick="if(confirm('Are you sure you want to login as that person?')) return true; else return false;">
								<?php endif; ?>
								<?php echo $row->emp_name; ?>
								<?php if ($this->session->userdata('user_type') == "admin" && !isset($pdf)) : ?>
									</a>
								<?php endif; ?>
							</td>
							
							<td class="font-bold">
								<?php if(isset($row->emp_phone) && $row->emp_phone != '') : ?>
									<?php echo $row->emp_phone; ?>
								<?php else : ?>
									&mdash;
								<?php endif; ?>
							</td>
							<td class="font-bold"><?php echo $row->emp_position ?></td>
							<?php if(!isset($pdf)) : ?>
								<th>
									<a class="btn btn-xs btn-default"
									   href="<?php echo base_url(); ?>employees/employee_update/<?php echo $row->employee_id; ?>">
										<i class="fa fa-pencil"></i>
									</a> &nbsp;
									<a class="btn btn-xs btn-danger"
									   href="<?php echo base_url(); ?>employees/employee_delete/<?php echo $row->employee_id; ?>">
										<i class="fa fa-trash-o"></i>
									</a>
									<a class="btn-default btn btn-xs"
									   href="<?php echo base_url(); ?>employees/employee_change_pass/<?php echo $row->employee_id; ?>"
									   style="margin-left: 10px;">
										<i class="fa fa-lock" title="Change Password"></i>
									</a>
								</th>
							<?php endif; ?>
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

		</div>
		</div>
		<?php $this->load->view('includes/footer'); ?>
