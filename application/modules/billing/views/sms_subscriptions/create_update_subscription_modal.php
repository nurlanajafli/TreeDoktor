<?php $modalId = isset($updateSubscription) ? 'update_subscription_modal_' . $updateSubscription->id : 'create_subscription_modal'; ?>
<?php $formId = isset($updateSubscription) ? 'update_subscription_' . $updateSubscription->id : 'create_subscription'; ?>
<?php $isFree = isset($updateSubscription) && $updateSubscription->amount == 0; ?>
<?php $withOrder = $isFree && isset($updateSubscription) && $updateSubscription->orders->count(); ?>
<div id="<?php echo $modalId; ?>" class="modal fade create-update-subscription-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading modalTitle">
                <?php echo (isset($updateSubscription) ? 'Edit' : 'Create') . ($isFree || !isset($updateSubscription) ? ' Gift' : ''); ?> subscription
            </header>
            <form id="<?php echo $formId; ?>" class="form-horizontal create-update-subscription-form" autocomplete="off">
                <div class="modal-body">
                    <div class="p-10">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Subscription name</label>
                            <div class="col-sm-8">
                                <?php if ($isFree || !isset($updateSubscription)): ?>
                                    <input type="hidden" name="name" value="Gift">
                                    <div class="p-top-7">Gift</div>
                                <?php else: ?>
                                    <input class="form-control" type="text" name="name" placeholder="Subscription name"
                                           value="<?php echo $updateSubscription->name; ?>" required>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Period</label>
                            <div class="col-sm-8">
                                <?php $period = isset($updateSubscription) ? $updateSubscription->period : null; ?>
                                <select name="period" class="form-control" required>
                                    <option value="" <?php echo !$period ? 'selected' : ''; ?>>-- Select period --</option>
                                    <option value="month"<?php echo $period === 'month' ? ' selected' : ''; ?>>Month</option>
                                    <option value="year"<?php echo $period === 'year' ? ' selected' : ''; ?>>Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Number of SMS</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="number" name="count" required placeholder="SMS limit"
                                    <?php echo isset($updateSubscription) ? 'value="' . $updateSubscription->count . '"' : ''; ?>>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Price</label>
                            <div class="col-sm-8">
                                <?php if ($isFree || !isset($updateSubscription)): ?>
                                    <input type="hidden" name="amount" value="0">
                                    <div class="p-top-7">$ 0.00</div>
                                <?php else: ?>
                                    <input class="form-control currency" type="text" name="amount"
                                        value="<?php echo $updateSubscription->amount; ?>" required>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Description text</label>
                            <div class="col-sm-8">
                                <textarea class="form-control sub-description-textarea"
                                          placeholder="Description of subscription"
                                          name="description"
                                          rows="5"><?php echo isset($updateSubscription) ? $updateSubscription->description : ''; ?></textarea>
                            </div>
                        </div>
                        <?php if(!isset($updateSubscription) || $isFree): ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label p-top-none">
                                    Automatic renewal at the end of the period
                                </label>
                                <div class="col-sm-8">
                                    <input type="checkbox" data-toggle="toggle" name="on_period" class="sub_auto_renewal"
                                        <?php echo isset($updateSubscription) && $updateSubscription->next_date ? 'checked' : ''; ?>>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label p-top-none">
                                    Automatic renewal when the limit is reached
                                    <span class="badge badge-sm bg-info on-out-limit-info"
                                          data-toggle="tooltip"
                                          data-placement="bottom"
                                          data-html="true" title="Automatic&nbsp;renewal of the subscription will be performed with the remaining 10 SMS"
                                          data-original-title="">i</span>
                                </label>
                                <div class="col-sm-8">
                                    <input type="checkbox" data-toggle="toggle" name="on_out_limit" class="sub_auto_renewal"
                                        <?php echo isset($updateSubscription) && $updateSubscription->on_out_limit ? 'checked' : ''; ?>>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <?php if (!isset($updateSubscription)): ?>
                                <div class="form-group m-b-n">
                                    <label class="col-sm-4 control-label">Use for period</label>
                                    <div class="col-sm-8">
                                        <select name="use_period" class="form-control sub_use_period" required>
                                            <option value="" selected>-- Select used period --</option>
                                            <option value="current">Current period</option>
                                            <option value="next">Next period</option>
                                        </select>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (isset($updateSubscription) && !$isFree): ?>
                            <hr class="m-t-sm m-b-sm">
                            <div class="form-group m-b-n">
                                <label class="col-sm-4 control-label">Active</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" data-toggle="toggle" name="active"
                                        <?php echo $updateSubscription->active ? 'checked' : ''; ?>>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php if (isset($updateSubscription) && $isFree && !$withOrder): ?>
                        <div class="pull-left">
                            <input class="add-free-order" type="hidden" name="add_free_order" value="0">
                            <button class="btn btn-success add-free-order-btn"
                                    data-id="<?php echo $updateSubscription->id ;?>"
                                    data-type="add_order"
                                    title="Add Free order"
                                    type="submit">
                                <span class="btntext">Add free order</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <input class="modal_action_id" type="hidden" name="id"
                        <?php echo isset($updateSubscription) ? 'value="' . $updateSubscription->id . '"' : ''; ?>>
                    <input class="modal_action" type="hidden" name="action"
                           value="<?php echo isset($updateSubscription) ? 'update' : 'create'; ?>">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn btn-info" type="submit">
                        <span class="btntext"><?php echo isset($updateSubscription) ? 'Update' : 'Create'; ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
