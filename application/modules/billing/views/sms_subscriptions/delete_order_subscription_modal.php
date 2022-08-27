<div id="delete_order_subscription_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading modalTitle">
                Delete '<?php echo $freeSubscription->subscription->name; ?>' subscription or/with order
            </header>
            <div class="modal-body">
                <div class="p-10">
                    <div class="col-sm-6">
                        <button class="btn btn-danger deleteFreeBtn"
                                data-id="<?php echo $freeSubscription->id ;?>"
                                data-type="order">
                            <span class="btntext">Delete order only</span>
                        </button>
                    </div>
                    <div class="col-sm-6">
                        <button class="btn btn-danger deleteFreeBtn"
                                data-id="<?php echo $freeSubscription->subscription->id ;?>"
                                data-type="subscription">
                            <span class="btntext">Delete subscription with order</span>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>
