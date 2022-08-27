<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.css" type="text/css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/theme.css" type="text/css" />
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.min.js"></script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('info/trees'); ?>">Trees & Pests </a></li>
		<li class="active"><?php echo $tree->trees_name_eng . ' / ' . $tree->trees_name_lat; ?></li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading"><?php echo $tree->trees_name_eng . ' / ' . $tree->trees_name_lat; ?>
			
		<!--
				<a href="#add_pest" class="btn btn-xs btn-success btn-mini pull-right" role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
		-->
		</header>
		<div class="media m-b">
			<div class="media-body">
				<h1 style="text-align: center;">
					<strong><?php echo $tree->trees_name_lat; ?>&nbsp;&nbsp;/&nbsp;&nbsp;<?php echo $tree->trees_name_eng; ?></strong>
				</h1>
				<?php if(!empty($tree->pests)) : ?>
					<div class="details m-t-lg">
						<?php foreach($tree->pests as $key=>$pest) : ?>
							<div class="p-15 <?php if($key) : ?>m-t-lg<?php endif; ?>">
								<table class="table m-b-none b-a">
									<thead>
										<tr>
											<th class="bg-light b-r b-b" width="150px">Pest</th>
											<th class="bg-light b-r b-b" width="150px">Product</th>
											<th class="bg-light b-r b-b" width="150px">Rate</th>
											<th class="bg-light b-r b-b">Notes</th>
										</tr>
										<?php if($pest->pest_affecting && $pest->pest_affecting != '') : ?>
											<tr>
												<th colspan="4">
													&nbsp;&nbsp;&nbsp;<?php echo ucfirst($pest->pest_affecting); ?>&nbsp;Affecting&nbsp;<?php echo $tree->trees_name_lat;?>
												</th>
											</tr>
										<?php endif; ?>
									</thead>
									<tbody>
										<?php if(!empty($pest->products) && is_countable($pest->products)) : ?>
											<?php foreach($pest->products as $k=>$product) : ?>
												<tr>
													<?php if($k == 0) : ?>
														<td class="b-r b-b" rowspan="<?php echo count($pest->products)?>"><strong><?php echo $pest->pest_eng_name; ?></strong><br>(<?php echo $pest->pest_lat_name; ?>)</td>
													<?php endif; ?>
													<td class="b-r b-b"><?php echo $product->tpp_name; ?></td>
													<td class="b-r b-b"><?php echo $product->tpp_rate; ?></td>
													<?php if($k == 0) : ?>
														<td class="b-r b-b" rowspan="<?php echo count($pest->products)?>">
													
															<?php echo ($pest->tpr_description) ? $pest->tpr_description : $product->tpp_notes; ?>
													
														</td>
													<?php endif; ?>
												</tr>
											<?php endforeach; ?>
										<?php else : ?>
											<tr style="color:#FF0000;">No records found</tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
							<div class="p-15 m-t">
								<p style="font-style: italic; font-size: initial;">
									<strong>
										<?php echo ($pest->tpr_notes) ? $pest->tpr_notes : $pest->pest_notes; ?>
									</strong>
								</p>
							</div>
							<div class="m-t">
								
									
								
								<?php $files = bucketScanDir('uploads/tree_pests/' . $pest->pest_id); ?>
									<?php if ($files) : ?>
										<?php foreach ($files as $jkey => $file) : ?>
										<?php if($jkey % 2) : ?>
											<div class="row">
										<?php endif; ?>
											<div class="col-md-5" align="center">
												<a href="<?php echo base_url('uploads/tree_pests/' . $pest->pest_id . '/' . $file); ?>"
												   data-lightbox="works">
													<img style="max-height:250px" src="<?php echo base_url('uploads/tree_pests/' . $pest->pest_id . '/' . $file); ?>"
														 data-alt="<?php echo $pest->pest_eng_name; ?>"<?php if ($key) : ?> class="m-t"<?php endif; ?>>
												</a>
											</div>
											<div class="col-md-1">&nbsp;</div>
										<?php if($jkey % 2) : ?>
											<dir class="clear"></dir>
											</div>
										<?php endif; ?>
										<?php endforeach; ?>
									<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
				<div class="details m-t-lg">
					<p style="color:#FF0000;" class="m-l-lg">No records found</p>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
</section>
<?php $this->load->view('add_pest_modal'); ?>
<?php $this->load->view('includes/footer'); ?>
