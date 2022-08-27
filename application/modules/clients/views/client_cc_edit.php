<div class="ccBlock">
<div class="divider-bottom p-top-5 m-bottom-10" style="background: #fff;"></div>
 <div class="row" >
	<div class="col-md-6">
		<div class="input-group m-b">
			<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
			<input name="crd_number" class="form-control" value="" placeholder="1234 1234 1234 1234" type="number">
		</div>
	</div>
	<div class="col-md-6">
		<div class="input-group m-b">
			<span class="input-group-addon"><i class="fa fa-user"></i></span>
			<input name="crd_name" class="form-control" value="" placeholder="Cardholder Name">
		</div> 
	</div> 
	<div class="col-md-3">
		<div class="input-group m-b">
			<span class="input-group-addon"><i class="fa fa-key"></i></span>
			<input class="form-control" name="crd_cvv" type="text" id="crd_cvv"
			   placeholder="CVV"
			   value="">
		</div>
	</div>
	<div class="col-md-5">
		<div class="input-group m-b">
			<span class="input-group-addon">MM</span>
			<select name="crd_exp_month" class="form-control" id="crd_exp_month">
				<?php for ($i = 1; $i <= 12; $i++) : ?>
					<option value="<?php echo $i; ?>"><?php echo $i . ') ' . date("F", mktime(0, 0, 0, $i, 10)); ?></option>
				<?php endfor; ?>
			</select>
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group m-b">
			<span class="input-group-addon">YYYY</span>
			<select name="crd_exp_year" class="m-l-md form-control pull-right"  placeholder="YYYY">
				<?php $year = date('Y'); ?>
				<?php for ($i = $year; $i <= $year + 10; $i++) : ?>
					<option
						value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
		</div>
	</div>
	<div class="m-b">

		<div class="col-sm-12">
			
			<a id="" class="p-left-5 pull-right save_card_edit" href="#">Save card</a>
		</div>
		<div class="clear"></div>
	</div>
</div>

<input name="client_id" type="hidden" value="<?php echo $client_data->client_id; ?>">
<div class="divider-bottom p-top-5 m-bottom-10"></div>
</div>
