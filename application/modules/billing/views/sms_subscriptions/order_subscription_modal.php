<div id="order_subscription_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading modalTitle">Buy package</header>
            <form id="add_order_subscription" class="form-horizontal" autocomplete="off">
                <div class="modal-body">
                    <div class="p-10">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Subscription</label>
                            <div class="col-sm-7 sms-sub-name font-semibold"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Number of SMS</label>
                            <div class="col-sm-7 sms-sub-count font-semibold"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Price</label>
                            <div class="col-sm-7 sms-sub-amount font-semibold"></div>
                        </div>
                        <div class="form-group with-order-update">
                            <label class="col-sm-4 control-label">Active from</label>
                            <div class="col-sm-7 sms-sub-from font-semibold"></div>
                        </div>
                        <div class="form-group with-order-update">
                            <label class="col-sm-4 control-label">Active to</label>
                            <div class="col-sm-7 sms-sub-to font-semibold"></div>
                        </div>
                        <div class="form-group payment-option">
                            <label class="col-sm-4 control-label">
                                Payment Card
                                <span class="card_info hidden"></span>
                            </label>
                            <div class="col-sm-7">
                                <select id="cc_select" name="cc_id" class="form-control"></select>
                                <span class="help-inline"></span>
                                <a href="#card-form" id="add_cc_card" class="pull-right add_credit_card" data-toggle="modal">Add card</a>
                            </div>
                        </div>

                        <div class="form-group description-block hidden">
                            <label class="col-sm-4 control-label">Description</label>
                            <div class="col-sm-7 sms-sub-description font-semibold"></div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <input class="sub_use_period" type="hidden" name="use_period" value="current">
                    <input class="sub_on_period" type="hidden" name="on_period" value="">
                    <input class="sub_on_out_limit" type="hidden" name="on_out_limit" value="">
                    <input class="modal_action_id" type="hidden" name="id" value="">
                    <input class="modal_action" type="hidden" name="action" value="">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-info submitModalBtn" type="submit">
                        <span class="btntext">Buy now</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
