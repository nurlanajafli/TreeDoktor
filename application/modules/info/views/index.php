<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Tree Info</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Tree Info
			<div class="col-sm-3  col-xs-8  col-md-8 pull-right" style="margin-top: -6px;">
				<?php $this->load->view('includes/search', array('placeholder' => 'Type search text...')); ?>
			</div>
		</header>
		<div class="table-responsive">
			<table class="table table-striped b-t b-light" id="tbl_search_result">
				<thead>
				<tr>
					<th>Common Name</th>
					<th>Scientific Name</th>
					<th>Family Name</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($trees as $tree) : ?>
					<tr>
						<td><?php echo anchor('info/details/' . $tree->tree_id, $tree->tree_common_name); ?></td>
						<td><?php echo $tree->tree_scientific_name; ?></td>
						<td><?php echo $tree->tree_family_name; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>
<?php $this->load->view('includes/footer'); ?>
