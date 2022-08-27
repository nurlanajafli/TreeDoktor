<div id="payment_refund" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">
                Refund payment
                <?php if(isset($internal_payment) && $internal_payment): ?>
                    <span style="float: right; font-size: 11px; color: red;text-align: right;line-height: 1.1;margin-top: -3px;">
                        This action will only return the money<br>without affecting the entity for which they paid!
                    </span>
                <?php endif; ?>
            </header>
            <form id="refund_payment_form" method="POST" class="form-horizontal" enctype="multipart/form-data">
                <div class="modal-body p-10">
                    <div class="p-10">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Amount</label>
                            <div class="col-sm-7">
                                <input name="refund_payment_amount" id="refund_payment_amount" class="form-control"
                                       type="number" step="0.01" max="0"
                                       placeholder="Payment Amount" value="" required>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <?php if(config_item('cc_extra_fee') > 0
                                  && !config_item('cc_extra_fee_service_id')
                                  && !isset($internal_payment)): ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"></label>
                                <div class="col-sm-7">
                                    <div class="checkbox">
                                        <label>
                                            <input name="refund_payment_fee" id="refund_payment_fee" type="checkbox">
                                            Include fee <span id="refund_fee_amount"></span>
                                        </label>
                                    </div>
                                    <div>(Note: Fee can be returned when full amount refund processed)</div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Password</label>
                            <div class="col-sm-7">
                                <input name="refund_password" id="refund_password" class="form-control"
                                       type="password"
                                       placeholder="Your password" value="" required>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <input name="refund_payment_id" id="refund_payment_id" class="form-control" type="hidden"
                               value="">
                        <div class="text-danger form_error"
                             style="display: none; text-align: left; font-size: 20px; padding-top: 20px;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-info" type="submit">
                        <span class="btntext">Refund</span>
                        <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
                             style="display: none;width: 91px;" class="preloader">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
