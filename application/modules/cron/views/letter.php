<table class="table tsble-striped" border="1px" cellspacing="0">
	<thead>
		<tr>
			<th align="center">Name</th>
			<th align="center">Date</th>
			<th align="center">Login Time</th>
			<th align="center">Worked</th>
			<th align="center">Late</th>
			<th align="center">Auto Logout</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($users as $key=>$user) : ?>
		<tr>
			<td align="center"><?php echo $user->emp_name; ?></td>
			<td align="center"><?php echo $user->worked_date; ?></td>
			<td align="center">
				<?php foreach($user->mdl_emp_login as $key => $login) : ?>
					<?php if($login->logout_lat !== NULL && $login->logout_lat !== '0' && $login->logout_lat !== 0) : ?>
						<a href="https://www.google.com/maps/search/?api=1&query=<?php echo $login->logout_lat; ?>,<?php echo $login->logout_lon;?>" target="_blank">  
					<?php elseif($login->login_lat !== NULL && $login->login_lat !== '0' && $login->login_lat !== 0) : ?>
						<a href="https://www.google.com/maps/search/?api=1&query=<?php echo $login->login_lat; ?>,<?php echo $login->login_lon;?>" target="_blank">  
					<?php endif; ?>
					
					<?php echo $login->login; ?> - <?php echo $login->logout; ?><br>
					
					<?php if($login->logout_lat !== NULL && $login->logout_lat !== '0' && $login->logout_lat !== 0) : ?>
						</a>
					<?php elseif($login->login_lat !== NULL && $login->login_lat !== '0' && $login->login_lat !== 0) : ?>
						</a>  
					<?php endif; ?>
				<?php endforeach; ?>
			</td>
			<td align="center">
				<?php echo str_pad(intval($user->worked_hours), 2, '0', STR_PAD_LEFT) . ':' . str_pad(intval   (($user->worked_hours - intval($user->worked_hours)) * 60), 2, '0', STR_PAD_LEFT); ?>
			</td>
			<td align="center">
				<?php $late = 0; ?>
				<?php if($user->worked_late) :?>
					<?php $late = str_pad(intval((strtotime($user->worked_start) - strtotime($user->emp_start_time)) / 60 / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad(intval((strtotime($user->worked_start) - strtotime($user->emp_start_time)) / 60 % 60), 2, '0', STR_PAD_LEFT); ?>
				<?php endif; ?>
				<?php echo $late ? $late : '-'; ?>
			</td>
			<td align="center">
				<?php echo $user->worked_auto_logout ? 'X' : ' - '; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
