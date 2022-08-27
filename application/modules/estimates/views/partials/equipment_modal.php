<div id="<?php if (isset($val) && isset($val->vehicle_id)) : ?>edit<?php echo $val->vehicle_id; ?><?php else : ?>addEquipment<?php endif; ?>" class="modal fade" tabindex="-1"
     role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading"><?php if (isset($val) && isset($val->vehicle_id)) : ?>Edit Equipment<?php else : ?>Add Equipment<?php endif; ?></header>
            <form data-type="ajax" data-before="prevalidation"
                  data-url="<?php echo base_url('estimates/save_vehicle'); ?>"
                  method="POST" class="p-10" action=""
                  data-location="<?php echo current_url(); ?>">
                <div class="control-group m-b-xs">
                    <label class="control-label">Vehicle
                        Name</label>
                    <div class="controls">
                        <input class="form-control"
                               value="<?php if (isset($val) && isset($val->vehicle_id)) : ?><?php echo $val->vehicle_name; ?><?php endif; ?>" type="text"
                               name="vehicle_name" data-toggle="tooltip"
                               data-placement="top" title="" data-original-title="">
                    </div>
                </div>
                <div class="control-group m-b-xs">
                    <label class="control-label" for="estimate_id">Price Per Hour</label>
                    <div class="controls">
                        <input class="form-control currency"
                               value="<?php if (isset($val) && isset($val->vehicle_id)) : ?><?php echo $val->vehicle_per_hour_price; ?><?php endif; ?>" type="text"
                               name="vehicle_per_hour_price" data-toggle="tooltip"
                               data-placement="top" title="" data-original-title="">
                    </div>
                </div>
                <div class="control-group m-b-xs">
                    <div class="radio">
                        <label>
                            <input type="radio" name="vehicle_trailer" value="0"<?php if ((!empty($val) && isset($val->vehicle_id) && !$val->vehicle_trailer) || (empty($val) || !isset($val->vehicle_id))) : ?> checked="checked"<?php endif; ?>>
                            Vehicle
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="vehicle_trailer" value="1"<?php if (!empty($val) && isset($val->vehicle_id) && $val->vehicle_trailer == 1) : ?> checked="checked"<?php endif; ?>>
                            Attachment
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="vehicle_trailer" value="2"<?php if (!empty($val) && isset($val->vehicle_id) && $val->vehicle_trailer == 2) : ?> checked="checked"<?php endif; ?>>
                            Tool
                        </label>
                    </div>
                </div>

                <div class="control-group m-b-xs">
                    <label class="control-label">Vehicle Options (Type and Press
                        Enter)</label>
                    <div class="controls">
                        <?php $options = ''; ?>
                        <?php if (isset($val) && isset($val->vehicle_id) && $val->vehicle_options && !empty(json_decode($val->vehicle_options)) && json_decode($val->vehicle_options) != '') : ?>
                            <?php //foreach(json_decode($val->vehicle_options) as $k=>$v) : ?>
                            <?php $options = implode('|', json_decode($val->vehicle_options)); ?>


                            <?php //endforeach; ?>
                        <?php endif; ?>
                        <input type="text" placeholder="Add Option"
                               class="vehicleOptions w-100" name="vehicle_options"
                               value="<?php echo trim(htmlspecialchars($options), ' | '); ?>">
                        <?php if (isset($val) && isset($val->vehicle_id)) : ?>
                            <input type="hidden" class="form-control" name="id"
                                value="<?php echo $val->vehicle_id; ?>">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="pull-right ">
                        <button class="btn btn-success m-right-5" id="addStump">
                            Save
                        </button>
                        <button class="btn" data-dismiss="modal" aria-hidden="true">
                            Close
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
