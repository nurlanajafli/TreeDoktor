<ul>
	Declined Payments:
	<?php foreach ($payments as $key => $value) : ?>
		<li>
            AMT: <?php echo money($value->payment_amount) . ' Date: ' . date('Y-m-d', $value->payment_date); ?> -
			<a href="<?php echo base_url('client/' . $value->client_id); ?>"><?php echo $value->client_name; ?></a>
		</li>
	<?php endforeach; ?>
</ul>