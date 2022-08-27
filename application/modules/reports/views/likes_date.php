<style>.deleteLike{border: none; outline: 0;}</style>
<ul data-leader_id="<?php echo $emp_id . '-' . $like_type; ?>" class="dropdown-menu on animated fadeInRight eventsList" style="max-height: 300px;overflow-y: scroll;overflow-x: hidden;right: 0px;">
	<?php if(isAdmin()) : ?>
		<div class="dates" style="text-align:center">
			
			<a class="btn btn-info btn-xs saveDate likeLink " data-id="<?php echo $emp_id;?>" data-like="<?php echo $like_type;?>" type="button" data-toggle="collapse" href="#" style="display:inline-block">
				<i class="fa fa-floppy-o"></i>
			</a>
			<a class="btn btn-success btn-xs likeLink newLike" type="button" href="#" data-id="<?php echo $emp_id;?>" data-like="<?php echo $like_type;?>" style="display:inline-block">
				<i class="fa fa-plus"></i>
			</a>
		</div>
	<?php endif;?>
	<?php if($likes && !empty($likes)) : ?>
		<?php foreach($likes as $key => $date) : ?>
				<li>
					<a href="#commandInfo-<?php echo  $date . '-' . $emp_id; ?>" class="reportInfo" data-toggle="modal" data-backdrop="static" data-keyboard="false" style="display: inline-block;  width: 60%;">
						<?php echo $date; ?>
					</a>
					<?php if(isAdmin()) : ?>
						<button class="btn-danger btn-xs likeLink deleteLike" type="button" href="#" data-record-id="<?php echo $key; ?>">
							<i class="fa fa-trash-o"></i>
						</button>
					<?php endif;?>
				</li>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>

