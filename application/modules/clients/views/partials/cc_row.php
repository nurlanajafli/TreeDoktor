<tr data-id="<?php echo $card_id; ?>">
	<td class="p-n"><div class="<?php echo $card_type; ?>"></div></td>
	<td>
		<div class="cardnumber"><?php echo $number; ?></div>
	</td>
	<td>
		<div class="cardnumber">
			<?php echo $expiry_month; ?>/<?php echo  $expiry_year; ?>
				 
		</div>
	</td>
	<td>
		<div class="cardnumber"><?php echo $name; ?></div>
	</td>
	<td class="options edit"><div class="cardnumber"><a href="#" class="btn btn-default btn-xs btn-danger delete-card"><i class="fa fa-trash-o"></i></a></div></td>
</tr>
