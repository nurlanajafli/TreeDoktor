<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Online invoice payment</title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.css">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/grid.css">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/datepicker.css">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/css/style.css?v=<?php echo config_item('style.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/jquery.easy-pie-chart.css">

	<script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/libs/currency.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/common.js?v=<?php echo config_item('js_common'); ?>"></script>
	<script src="<?php echo base_url(); ?>assets/js/ccvalidate.js"></script>

	<script type="text/javascript">
        var currency_symbol = '<?php echo config_item('currency_symbol'); ?>';
        var currency_symbol_position = '<?php echo config_item('currency_symbol_position'); ?>';

		$(document).ready(function () {
			//ajax common before processing

		});
	</script>
</head>
<body>


<?php if ($unauthorized == 1) { ?><!-- No access to client's file -->
<div id="horizon">
	<div id="login" class="rounded filled_white shadow overflow">
		<div id="logo" class="wrapper"></div>
		<P>We are unable to process your account at this time.<br>
			Please contact our office for help.</P>

		<address>
			<strong><?php echo $this->config->item('company_name_short'); ?></strong><br>
			1 Towns Rd., Unit 2<br>
			Toronto, ON M8Z 1A1<br>
			<abbr title="Phone">P:</abbr> <?php echo $this->config->item('office_phone_mask'); ?>
		</address>

		<address>
			<a href="mailto:#"><?php echo $this->config->item('account_email_address'); ?></a>
		</address>

	</div>
</div>
<?php exit; ?>
<?php } else if ($unauthorized == 2) { ?>
	<div id="horizon">
		<div id="login" class="rounded filled_white shadow overflow">
			<div id="logo" class="wrapper"></div>
			<p>The Invoice is already paid.</p>
		</div>
	</div>
	<?php exit; ?>

<?php } ?>


<!-- Active invoice, access granted. -->
<div class="row m-top-10 filled_white rounded shadow overflow">
	<br>
	<br>
	<br>

	<div class="row">
		<!-- Client body -->
		<div class="grid_8 push_1">
			<div class="p-15">

				<br><br>
				<h4>Invoice date:&nbsp;<?php echo $invoice_data["date_created"] ?></h4>
				<h4>Invoice number:&nbsp;<?php echo $invoice_data["invoice_no"] ?></h4>

				<br><br>
				<h4><?php echo $invoice_data["client_name"] ?></h4>
				<?php if ($invoice_data["client_name"] != "") { ?><span class="muted">
					<strong>Attn:&nbsp;<?php echo $invoice_data["client_contact"]; ?></strong></span><?php } ?>

				<table>
					<tr>
						<td colspan="3">

						</td>
					</tr>
					<tr>
						<td>
							<?php echo $invoice_data["client_address"]; ?><br>
							<?php echo $invoice_data["client_city"]; ?>
							&#44;&nbsp; <?php echo $invoice_data["client_state"]; ?><br>
							<?php echo $invoice_data["client_country"]; ?>
							&nbsp;<?php echo $invoice_data["client_zip"]; ?>
						</td>
						<td valign="top" class="p-left-30">
							<abbr title="Phone">Ph:</abbr><?php echo $invoice_data["cc_phone"]; ?>
						</td>
						<td valign="top" class="p-left-30">
							<a href="mailto:<?php echo $invoice_data["cc_email"]; ?>"><?php echo $invoice_data["cc_email"]; ?></a><br>
							<?php if ($invoice_data["client_web"]) { ?>
								<?php $client_web = prep_url($invoice_data["client_web"]); ?>
								<a href="<?php echo $client_web; ?>"><?php echo $client_web; ?></a><br>
							<?php } ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<!-- / Client body -->

		<div class="grid_3 text-right">
			<img alt="" src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/img/logo.png">
		</div>
	</div>

	<!-- Invoice Data -->
	<div class="row">
		<div class="grid_10 push_1">
			<div class="p-15">
				<!-- Estimate Items -->
				<table class="table table-bordered table-striped">
					<thead>
					<tr>
						<th>Description</th>
						<th>Price</th>
					</tr>
					</thead>
					<tbody>

					<?php if (!empty($estimate_data->arborist_report)) { ?>
						<tr>
							<td><?php echo nl2br($estimate_data->arborist_report); ?></td>
							<td><?php echo money($estimate_data->arborist_report_price); ?></td>
						</tr>
					<?php } ?>

					<?php if (!empty($estimate_data->trimming_service)) { ?>
						<tr>
							<td><?php echo nl2br($estimate_data->trimming_service); ?></td>
							<td><?php echo money($estimate_data->trimming_service_price); ?></td>
						</tr>
					<?php } ?>

					<?php if (!empty($estimate_data->tree_removal)) { ?>
						<tr>
							<td><?php echo nl2br($estimate_data->tree_removal); ?></td>
							<td><?php echo money($estimate_data->tree_removal_price); ?></td>
						</tr>
					<?php } ?>

					<?php if (!empty($estimate_data->stump_grinding)) { ?>
						<tr>
							<td><?php echo nl2br($estimate_data->stump_grinding); ?></td>
							<td><?php echo money($estimate_data->stump_grinding_price); ?></td>
						</tr>
					<?php } ?>

					<?php if (!empty($estimate_data->wood_removal)) { ?>
						<tr>
							<td><?php echo $estimate_data->wood_removal; ?></td>
							<td><?php echo nl2br(money(getAmount($estimate_data->wood_removal_price))); ?></td>
						</tr>
					<?php } ?>

					<?php if (!empty($estimate_data->site_cleanup)) { ?>
						<tr>
							<td><?php echo nl2br($estimate_data->site_cleanup); ?></td>
							<td><?php echo money($estimate_data->site_cleanup_price); ?></td>
						</tr>
					<?php } ?>

					<?php if (!empty($estimate_data->extra_option)) { ?>
						<tr>
							<td><?php echo nl2br($estimate_data->extra_option); ?></td>
							<td><?php echo money($estimate_data->extra_option_price); ?></td>
						</tr>
					<?php } ?>

					<?php $sum = $estimate_data->arborist_report_price +
						$estimate_data->trimming_service_price +
						$estimate_data->tree_removal_price +
						$estimate_data->stump_grinding_price +
						$estimate_data->wood_removal_price +
						$estimate_data->site_cleanup_price +
						$estimate_data->extra_option_price ?>
					<tr>
						<td>
							<div class="text-right"><strong>Total:</strong></div>
						</td>
						<td><span data-prefix><?php echo get_currency(); ?></span><span><?php echo $totalEstimate; ?></span></td>
					</tr>
					<tr>
						<td>
							<div class="text-right"><strong>Balance Due</strong></div>
						</td>
						<td><span data-prefix><?php echo get_currency(); ?></span><span><?php echo $totalEstimate; ?></span></td>
					</tr>
					<tr>
						<td>
							<div class="text-right"><strong><?php echo config_item('tax_name') ? config_item('tax_name') : 'TAX' ?> <?php config_item('tax_perc') ? config_item('tax_perc') . '%' : '0'; ?></strong></div>
						</td>
						<td><span data-prefix><?php echo get_currency(); ?></span><span><?php echo $hst; ?></span></td>
					</tr>
					<tr>
						<td>
							<div class="text-right"><strong>Deposit</strong></div>
						</td>
						<td><span data-prefix>-<?php echo get_currency(); ?></span><span><?php echo $deposit; ?></span></td>
					</tr>
					<tr>
						<td>
							<div class="text-right"><strong>Total Payable</strong></div>
						</td>
						<td><span data-prefix><?php echo get_currency(); ?></span><span><?php echo $amount_to_pay; ?></span></td>
					<tbody>
				</table>

			</div>
		</div>
		<!-- / Invoice Data -->
	</div>
</div>
<!-- / Invoice Data Container -->

<!--Credit Card Container -->
<div class="row m-top-10 filled_white rounded shadow overflow">
	<div class="p-15">

		<!-- Online Payment Title-->
		<div class="row">
			<div class="grid_8 push_1">
				<h3>Pay online - Please enter your billing information:</h3>
			</div>
			<div class="grid_3 push_1">
				<img src="<?php echo base_url(); ?>assets/img/creditcards.png"/>
			</div>
		</div>
		<!-- / Online Payment Title-->


		<!-- Card Information -->
		<div class="row">
			<div class="grid_10 push_1">
				<div class="cc-container">
					<?php echo form_open(base_url() . 'payments/process_payment', array('name' => 'frmcfc', 'id' => 'ccfrm', 'method' => 'post')); ?>
					<table cellspacing="0" cellpadding="0" border="0">

						<tr>
							<td style="width: 97px">
								<label>Card Type</label>
							</td>
							<td style="width: 270px">
								<select class="cc-ddl-type" id="cc-type">
									<option value="mcd">Master Card</option>
									<option value="vis">Visa Card</option>
								</select>
							</td>
						</tr>

						<tr>
							<td><label>Card Number</label></td>
							<td><input type="text" class="large cc-card-number" id="cc-number" name="cc_number"
							           maxlength="20" value=""></td>
						</tr>

						<tr>
							<td><label>Card Holder Name</label></td>
							<td><input type="text" class="large cc-card-name" id="cc-name" name="cc_name" maxlength="30"
							           value=""></td>
						</tr>

						<tr>
							<td><label>Expires on</label></td>
							<td>
								<select id="cc-month" name="cc_month">
									<?php for ($m = 1; $m <= 12; $m++) { ?>
										<?php if ($m < 10) {
											$lm = "0" . $m;
										} else {
											$lm = $m;
										} ?>
										<option value="<?php echo $lm ?>"><?php echo $lm ?></option>
									<?php } ?>
								</select>

								<select id="cc-year" name="cc_year">
									<?php for ($y = date("Y"); $y <= date("Y", strtotime("+20 year")); $y++) { ?>
										<option value="<?php echo $y ?>"><?php echo $y ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>

						<tr>
							<td><label>CVV</label></td>
							<td><input type="text" class="small" id="cc-ccv" name="cc_ccv" maxlength="4" size="3"
							           value=""></td>
						</tr>

						<tr>
							<td>&nbsp;</td>
							<td><input type="button" id="check-out" class="btn btn-success cc-checkout"
							           value="Checkout"></td>
						</tr>

					</table>
					<?php echo form_close(); ?>
				</div>
			</div>
		</div>
		<!-- / Card Information -->

	</div>
</div>
<!--Credit Card Container -->


<!-- /Active invoice, access granted. -->


<!-- Client dialog modal -->
<div class="trans-result-blck">
	<div id="trans-result-close" align="right"><img src='<?php echo base_url(); ?>assets/img/close.png'/></div>
	<div id="trans-result"></div>
</div>

<!-- / Client dialog modal -->

<script>
	$(document).ready(function () {
		var refresh_link = '<?php echo $refresh_link ?>';
		$('.cc-container').ccvalidate({ onvalidate: function (isValid) {
			if (!isValid) {
				alert("Incorrect Credit Card details");
				return false;
			}
			try {
				$("#trans-result-close").show();
				var msg = '';
				loading("Processing you transaction, please wait. ");
				$.ajax({
					url: '<?php echo base_url(); ?>payments/process_payment',
					data: $("#ccfrm").serialize(),
					type: "post",
					dataType: "json",
					success: function (r) {
						$("#ccfrm")[0].reset();
						try {
							if (r["ERROR"] != undefined) {
								hideloading();
								$("#trans-result-close").hide();
								showmessage("<span style='color: red'>Error in processing payments: " + r["ERROR"] + " <a href='#' onclick='return process_again();' class='btn btn-success'>Try again</a></span>");
							} else {
								if (r.RESULT != undefined) {
									var trans_approve = r.RESULT["trnApproved"];
									var trans_id = parseInt(r.RESULT["trnId"]);
									var trans_msg = r.RESULT["messageText"];
									var trans_amount = r.RESULT["trnAmount"];
									var trans_date = r.RESULT["trnDate"];
									trans_date = decodeURIComponent(trans_date)
									trans_msg = decodeURIComponent(trans_msg)
									if (trans_msg.indexOf("+") >= 0) {
										trans_msg = trans_msg.replace(/\+/gi, " ");
									}

									if (trans_date.indexOf("+") >= 0) {
										trans_date = trans_date.replace(/\+/gi, " ");
									}
									var order_no = r.RESULT["trnOrderNumber"];
									//alert("Transaction ID: "+trans_id+", Transaction Message: "+trans_msg);

									//loading("Saving Transaction Details...");
									$.ajax({
										url: '<?php echo base_url(); ?>payments/save_payment',
										data: "trans_id=" + trans_id + "&trans_msg=" + trans_msg + "&trans_order=" + order_no + "&trans_approve=" + trans_approve + "&trans_amount=" + trans_amount + "&trans_date=" + trans_date,
										type: "post",
										dataType: "json",
										success: function (r1) {
											try {
												hideloading();
												if (trans_approve == 1) {
													hideloading();
													showmessage("<span style='color: green'>Your transaction has been approved. Thank you for the payment.<br>Transaction Id: " + trans_id + "<br>Transaction Order: " + order_no + "<br>Transaction Amount: $" + decodeURIComponent(trans_amount) + "<br>Transaction Date: " + trans_date + "</span><br>Please save this page for your records.");
												} else {
													//Declined transaction;
													hideloading();
													$("#trans-result-close").hide();
													showmessage("<span style='color: red'>Your transaction has been declined due to error:<br> <b>" + trans_msg + "</b><br><a href='#' onclick='return process_again();' class='btn btn-success'>Try again</a></span>");
												}
											} catch (err1) {
												hideloading();
												showmessage("<span style='color: red'>Error in processing payment" + err1.message + "</span>");
											}
										},
										error: function (e1) {
											hideloading();
											showmessage("<span style='color: red'>Error in processing payment" + e1.responseText + "</span>");
										}
									});
								} else {
									hideloading();
									showmessage("<span style='color: red'>Error in processing payment. Payment not done.</span>");
								}
							} // end if
						}
						catch (err) {
							hideloading();
							showmessage("<span style='color: red'>Error in processing payment" + err.message + "</span>");
						}
					},
					error: function (e) {
						hideloading();
						showmessage("<span style='color: red'>Error in processing payment" + e.responseText + "</span>");
					},
				});
			}
			catch (err) {
				hideloading();
				showmessage("<span style='color: red'>Error in processing payment" + err.message + "</span>");
			}
		}
		});

		$("#trans-result-close").click(function () {
			//var conf = window.confirm("Do you really want to exit without paying?");
			//if(conf==true) {
			$(this).parent().fadeOut(1000);
			window.location.href = refresh_link;
			//} else {
			//	return false;
			//	}
		});
	});

	function process_again() {
		hideloading();
		$(".trans-result-blck").fadeOut(1000);
	}

	function showmessage(msg) {
		$("#trans-result").html('');
		$("#trans-result").html(msg);
		$("#trans-result").parent().centerToWindow();
		$("#trans-result").parent().fadeIn(1000);
	}

	function loading(msg) {
		hideloading();
		var ldo = $("<div></div>");
		$(ldo).addClass("loader-o");
		$(ldo).appendTo($('body'));
		var ld = $("<div></div>");
		$(ld).attr("id", "loader");
		$(ld).attr("class", "loader");
		$(ld).html("<div class='loader-in'>" + msg + "</div>");
		$(ld).appendTo($(ldo));
		$(ld).show();
		$(ldo).centerToWindow();
	}

	function hideloading() {
		$("#loader").parent().fadeOut(1000, function () {
			$(".loader-o").remove();
		});
	}

	$.fn.centerToWindow = function () {
		var obj = $(this);
		var obj_width = $(this).outerWidth(true);
		var obj_height = $(this).outerHeight(true);
		var window_width = window.innerWidth ? window.innerWidth : $(window).width();
		var window_height = window.innerHeight ? window.innerHeight : $(window).height();

		obj.css({
			"position": "fixed",
			"top": ((window_height / 2) - (obj_height / 2)) + "px",
			"left": ((window_width / 2) - (obj_width / 2)) + "px"
		});
	}
</script>
</body>
</html>
