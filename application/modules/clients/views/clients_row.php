<?php foreach ($clients as $clients) : ?>
	<tr>
		<td align="center">
			<?php if($clients->client_type == 1) : ?>
				<?php $icon = 'icon_residential.png'?>
			<?php elseif($clients->client_type == 2) : ?>
				<?php $icon = "icon_corp.png"; ?>
			<?php else : ?>
				<?php $icon = "icon_municipal.png";?>
			<?php endif; ?>
			<img height="17px" src="<?php echo base_url('assets/vendors/notebook/images') . '/' . $icon; ?>">
		</td>
		<td><?php echo anchor( $clients->client_id, $clients->client_name); ?></td>
		<td class="email-row-<?php echo $clients->client_id; ?>">
			<?php if($clients->cc_email) : ?>
				<a href="#" data-email="<?php echo $clients->cc_email; ?>" onclick="checkEmail('<?php echo $clients->cc_email; ?>', <?php echo $clients->client_id; ?>)" class="text-<?php if($clients->cc_email_check == 1) : ?>success<?php elseif($clients->cc_email_check === '0') : ?>danger<?php else : ?>warning<?php endif; ?>"><?php echo $clients->cc_email; ?></a>
			<?php endif; ?>
		</td>
		<td><?php echo $clients->client_address;
			echo "&#44;&nbsp;";
			echo $clients->client_city; ?>&nbsp;<?php echo $clients->client_country; ?></td> 
	</tr>
<?php endforeach; ?>
