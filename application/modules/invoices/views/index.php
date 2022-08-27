<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/js/jquery.tablesorter.min.js'); ?>"></script>
<!-- Invoices Title -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Invoices</li>
	</ul>
	<!-- Invoices header -->
	<section class="panel panel-default">
		<header class="panel-heading">
            <div class="pull-left m-t-xs">Invoices</div>
			<div class="pull-right">
				<!-- Search Invoices -->
				<form name="search" id="search" method="get" action="<?php echo current_url(); ?>">
					<div class="input-group">
						<input name="q" id="search_tags" type="text" class="input-sm form-control"
							   placeholder="<?php if (!empty($placeholder)) : echo $placeholder;
							   else : ?>Name, Phone number, address...<?php endif; ?>"
							   value="<?php if (isset($search_keyword)) echo $search_keyword; ?>">
						<input type="hidden" name="to" value="<?php echo $to; ?>">
						<input type="hidden" name="from" value="<?php echo $from; ?>">
                        <?php if ($this->input->get() && is_array($this->input->get())) : ?>
                            <?php foreach ($this->input->get() as $key => $val) : ?>
                                <?php if($key != 'q' && $key != 'to' && $key != 'from') : ?>
                                    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
						<span class="input-group-btn">
							<button class="btn btn-sm btn-default" type="submit" id="search">Go!</button>
						</span>
					</div>
				</form>	

			</div>

			<div class="pull-right m-r-xs">
				<input class="datepicker form-control to-invoices input-sm" placeholder="To YYYY-MM-DD" type="text" value="<?php echo $to; ?>">
			</div>
			<div class="pull-right m-r-xs">
				<input class="datepicker form-control from-invoices input-sm" placeholder="From YYYY-MM-DD" type="text" value="<?php echo $from; ?>">
			</div>

            <?php if(isAdmin()) : ?>
            <a href="<?php echo base_url('invoices/' .$type . '/1/' . ($estimator ?? '0') . '/' . $filter . '/1'); echo $queryString ? '?' . $queryString : ''; ?>" id="csvExport" class="btn btn-default btn-sm pull-right m-r-sm" title="Export To CSV" type="button" style=""><i class="fa fa-download"></i></a>
            <?php endif; ?>

			<div class="pull-left m-l-xs">
				<select  style="min-width: 224px;height: 30px;padding: 4px 12px;" class="form-control estimator" name="estimator"
						onchange="location.href = baseUrl + 'invoices/<?php echo (isset($overpaid) && $overpaid) ? 'overpaid' : $type; ?>/1/' + $(this).val() + '/<?php echo $filter ? 1 : 0; ?><?php echo $queryString ? '?' . $queryString : ''; ?>'">

					<option value="0">Select Estimator</option>
					
					<?php foreach($users as $key=>$user) : ?>
						<option <?php if(isset($estimator) && $estimator == $user['employee_id']) : ?>selected="selected"<?php endif; ?> value="<?php echo $user['employee_id']; ?>">
							<?php echo $user['emp_name']; ?>
						</option>
					<?php endforeach; ?>
					
				</select>
			</div>
			<div class="clear"></div>
		</header>
		<div class="p-10"> <!-- Only required for left/right tabs -->
			<label class="checkbox pull-right block <?php if($type != $completed_status) : ?>hide<?php endif; ?>" style="padding-left:0; margin-top:0;">
				Positive Due
				<input type="checkbox" style="margin-left:0" name="filter" <?php if($filter): ?>checked="checked"<?php endif; ?> onclick="location.href = baseUrl + 'invoices/<?php echo $type; ?>/1/<?php echo isset($estimator) ? $estimator : 0; ?>/' + Number($(this).prop('checked')) + '<?php echo $queryString ? '?' . $queryString : ''; ?>'" />
			</label>
			<?php if(isset($invoices_statuses) && !empty($invoices_statuses)) : ?>
			<ul class="nav nav-tabs" data-type="invoices">
				<?php foreach($invoices_statuses as $key => $status) : ?>
					<li <?php if($type == $status->invoice_status_id && (!isset($overpaid) || !$overpaid)) : ?>class="active"<?php endif; ?>>
						<a href="<?php echo base_url('invoices') . '/' . $status->invoice_status_id; ?>/1<?php if(isset($estimator)) : ?>/<?php echo $estimator; ?><?php else : ?>/0<?php endif; ?>/<?php echo $filter ? 1 : 0; ?><?php echo $queryString ? '?' . $queryString : ''; ?>">
						<?php echo $status->invoice_status_name; ?> <span class="badge<?php if (isset($invoices_by_statuses[$status->invoice_status_id])) : ?> bg-info<?php endif; ?>"><?php echo isset($invoices_by_statuses[$status->invoice_status_id]) ? $invoices_by_statuses[$status->invoice_status_id] : 0; ?></span>
						</a>
					</li>
				
				<?php endforeach; ?>
				<li <?php if(isset($overpaid) && $overpaid) : ?>class="active"<?php endif; ?>>
					<a href="<?php echo base_url('invoices') . '/overpaid'; ?>/1<?php if(isset($estimator)) : ?>/<?php echo $estimator; ?><?php else : ?>/0<?php endif; ?>/<?php echo $filter ? 1 : 0; ?><?php echo $queryString ? '?' . $queryString : ''; ?>">Overpaid<span class="badge<?php if ($invoices_by_statuses['overpaid']) : ?> bg-info<?php endif; ?>"><?php echo ($invoices_by_statuses['overpaid']) ? $invoices_by_statuses['overpaid'] : 0; ?></span></a>
				</li>
				
			</ul>
			<?php endif; ?>
			<div class="">
				<div class="table-responsive	">
					<?php $this->load->view('index_issued'); ?>
				</div>
			</div>
		</div>


		</div>
		</div>
		</div>
		<script>
			$(document).ready(function(){
                if (typeof initQbLogPopover !== 'undefined') {
                    initQbLogPopover();
                }
				$('.datepicker').datepicker({format:'yyyy-mm-dd'});

				$('.to-invoices').change(function(){
					$('form#search input[name="to"]').val($('.to-invoices').val().trim());
				});

				$('.from-invoices').change(function(){
					$('form#search input[name="from"]').val($('.from-invoices').val().trim());
				});
			});
		</script>
		<!-- /Invoices Title ends-->
		<?php $this->load->view('includes/footer'); ?>
    </section>
