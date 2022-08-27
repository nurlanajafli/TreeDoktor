<?php if(isset($trees)) : ?>
	<table class="table table-striped b-t b-light" id="tbl_search_result">
		<thead>
		<tr>
			<th>#</th>
			<th>Tree Name</th>
			<th>Tree Name Lat</th>
		</tr>
		</thead>
		<tbody>
		<?php if(!empty($trees)) : ?>
			<?php foreach ($trees as $key=>$tree) : ?>
				<tr>
					<td><?php echo $key + 1;?></td>
					<td><?php echo anchor('info/profile/' . $tree->trees_id, $tree->trees_name_eng); ?></td>
					<td><?php echo $tree->trees_name_lat; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
				<tr style="color:#FF0000;"><td>No records found</td></tr>
		<?php endif;?>
		</tbody>
	</table>

<?php elseif(isset($search_trees)) : ?>
	<table class="table table-striped b-t b-light" id="tbl_search_result">
		<thead>
		<tr>
			<th>#</th>
			<th>Tree Name</th>
			<th>Pest Name</th>
			<th width="200px">Pest Notes</th>
			<th width="400px">Pest Description</th>
			<th>Affecting</th>
			<th>Product Name</th>
			<th>Product Rate</th>
		</tr>
		</thead>
		<tbody>
		<?php if(!empty($search_trees)) : ?>
			<?php foreach ($search_trees as $key=>$tree) : ?>
				<tr>
					<td><?php echo $key + 1;?></td>
					<td><?php echo anchor('info/profile/' . $tree['trees_id'], $tree['trees_name_eng'] . ' / ' . $tree['trees_name_lat']); ?></td>
					
					<td><?php echo $tree['pest_eng_name'] . ' / ' . $tree['pest_lat_name']; ?></td>
					<td><?php echo $tree['tpr_notes']; ?></td>
					<td><small><?php echo $tree['tpr_description']; ?></small></td>
					<td><?php echo $tree['pest_affecting']; ?></td>
					<td><?php echo $tree['tpp_name']; ?></td>
					<td><?php echo $tree['tpp_rate']; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
				<tr style="color:#FF0000;"><td>No records found</td></tr>
		<?php endif;?>
		</tbody>
	</table>
<?php endif;?>
