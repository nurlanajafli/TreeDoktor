<div class="arrow top"></div>
<ul class="recivedBonuses">
	<li><strong>Received Bonuses:</strong></li>
</ul>
<div class="line line-dashed line-lg"></div>
<ul class="possibleBonuses">
	<li><strong>Possible Bonuses:</strong></li>
	<?php foreach($bonuses as $bonus) : ?>
	<li class="label p-5 bg-<?php echo $bonus->bonus_type_amount > 0 ? 'success' : 'danger'; ?>" data-bonus_type_id="<?php echo $bonus->bonus_type_id; ?>"
	    title="<?php echo $bonus->bonus_type_description; ?>" style="display: inline-block;margin-left: 2px;">
		<?php echo $bonus->bonus_type_amount > 0 ? '+' : ''; ?><?php echo $bonus->bonus_type_amount; ?>% -
		<?php echo $bonus->bonus_type_name; ?>
	</li>
	<?php endforeach; ?>
</ul>
	<li class="label bg-warning" style="display: inline-block;padding: 2px; max-width: 100%;">
			<input type="text" class="text-center bonusPercents" style="outline: 0; color: #000; width: 25px; line-height: 13px; border: 0; height: 16px;" placeholder="%">
			 -
			<input type="text" class="bonusTitle" style="outline: 0; color: #000; line-height: 13px; border: 0; padding: 0 3px; height: 16px; max-width: 75%;" placeholder="Bonus Title">
		<button class="btn btn-success btn-xs customBonus" style="outline:0;box-shadow: none;font-size: 11px;line-height: 1px; height: 16px; margin-top: 2px;display: block; width: 100%;" disabled="disabled"><i class="fa fa-check"></i></button>
	</li>
