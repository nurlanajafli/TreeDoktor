<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/js/jquery.tablesorter.min.js'); ?>"></script>
<!-- Invoices Title -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('qa'); ?>">Quality Assurance</a></li>
		<li class="active">QA Estimates</li>
	</ul>
	
			<section class="panel panel-default p-n">
				<header class="panel-heading">Likes
					<div class="pull-right">
						<form id="dates" method="post" action="<?php echo base_url('qa/invoices'); ?>" class="input-append m-t-xs">
							<label>
								<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
									   value="<?php if ($from) : echo date('Y-m-d', $from);
									   else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?>">
							</label>
							— 
							<label>
								<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
									   value="<?php if ($to) : echo date('Y-m-d', $to);
									   else : echo date('Y-m-d'); endif; ?>">
							</label>
							<input id="status" type="hidden" value="<?php echo isset($status) ? $status : '';  ?>" name="status">
							<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
						</form>
					</div>
					<div class="clear"></div>
				</header>
				<table class="table table-striped table-pulse">
					<tbody>
						<tr>
							<td>All</td>
							<td style="text-align:center;"><a href="#" class="all" onclick="likeDislike('all')"><?php echo $invoices_all; ?></a></td>
						</tr>
						<tr>
							<td>Likes</td>
							<td style="text-align:center;"><a href="#" class="likes" onclick="likeDislike('likes')"><?php echo $invoices_like; ?></a></td>
						</tr>
						<tr>
							<td>Dislikes</td>
							<td style="text-align:center;"><a href="#" class="dislikes" onclick="likeDislike('dislikes')"><?php echo $invoices_dislike; ?></a></td>
						</tr>
						<tr>
							<td>No Response</td>
							<td style="text-align:center;"><a href="#" class="no_response" onclick="likeDislike('no_response')"><?php echo $invoices_response; ?></a></td>
						</tr>
					</tbody>
				</table>
				
			</section>
	
	<!-- Invoices header -->
	<section class="panel panel-default">
		
		<div class="table-responsive">
			<?php $this->load->view('estimates_table'); ?>
		</div>


		
		<!-- /Invoices Title ends-->
		<script>
			$(document).ready(function () {
				$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
			});
			function likeDislike(status)
			{
				//console.log(status); return false;
				var from = $('.from').val();
				var to = $('.to').val();
				$.post(baseUrl + 'qa/ajax_invoices', {from:from, to:to, status:status}, function(resp){
					
					$('#status').val(status);
					$('#tbl_Estimated').replaceWith(resp.table);
					$('.likes').text(resp.invoices_like);
					$('.dislikes').text(resp.invoices_dislike);
					//console.log(resp); return false;
					history.pushState(null, null, '/qa/invoices/' + status);
				}, "json");
			}
		</script>
		<?php $this->load->view('includes/footer'); ?>
