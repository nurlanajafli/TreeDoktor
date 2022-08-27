<ul class="dropdown-menu animated chat-box fadeInLeft">
	<span class="arrow top" style="right: 13px;left: auto;"></span>
	<?php $lamps = array('danger', 'success'); $titles = array('Offline', 'Online'); ?>
	<?php foreach(get_chat_users() as $user) : ?>
	<li class="pos-rlt">
		<a href="#" onclick="javascript:chatWith('<?php echo $user['id']; ?>','<?php echo addslashes($user['name']); ?>')" class="createChat" data-user-id="<?php echo $user['id']; ?>" id="chat-with-<?php echo $user['id']; ?>" title="">
			<i class="fa fa-circle text-xs text-danger userStatus" data-user_id="<?php echo $user['id']; ?>"></i>
			<?php echo $user['name']; ?>
		</a>
	</li>
	<?php endforeach; ?>
</ul>
