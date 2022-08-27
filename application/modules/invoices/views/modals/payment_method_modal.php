<div>
	<label class="control-label col-sm-12">Status: </label>
	<div class="col-sm-12">
		<select name="new_invoice_status" style="width: 238px!important;" class="form-control w-100"></select>
	</div>
	<div class="clear"></div>
    <div class="paid_block m-b-md" style="display:none;">
		<label class="control-label col-sm-12">Payment Mode: </label>
		<div class="col-sm-12">
			<select name="payment_method" class="form-control w-100 fu_pm">
				<?php foreach($pay_method as $k=>$v) : ?>
					<option value="<?php echo $k?>"><?php echo ucwords($v); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-sm-12"><a href="#" class="pull-right add_cr_card">Edit card</a></div>
		<div style="display: none; background: rgb(242, 242, 242);" class="fu_cc_block clear"></div> 
		<label class="control-label col-sm-12">Amount: </label>
		<div class="col-sm-6">
			<input type="text" class="form-control w-100 payment_amount" name="payment_amount">
				<span style="display:none; " id="processingCC" data-price_validation="true">
					<a href="#" class="pull-right">Processing</a>
				</span> 
		</div>
		<div class="col-sm-6">
			<span class="btn btn-primary btn-file" id="file1">Choose File<input type="file" name="payment_file" id="paymentFileToUpload" class="btn-upload"></span>
		</div> 
		<input type="hidden" name="pre_invoice_status">
		<input type="hidden" name="estimate_id" class="estimate_id"><input type="hidden" name="payment_type" value="invoice">
		<input type="hidden" name="ccReceipt" id="ccReceipt" value="">
		<input type="hidden" class="fuPk" value="">
		<div class="clear"></div>
    </div>
</div>
