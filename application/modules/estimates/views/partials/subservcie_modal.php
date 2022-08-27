<div id="service-<?php echo $subService->service_id; ?>" class="modal fade" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Edit
                Service <?php echo $subService->service_name; ?></header>
            <div class="modal-body">
                <div class="form-horizontal">
                    <?php /*
                    <div class="control-group">
                        <label class="control-label">Service Parent Name</label>

                        <div class="controls">
                            <select name="service_parent" class="service_parent form-control">
                                <option value=""> - </option>
                                <?php foreach ($services as $value) : ?>
                                    <option value="<?php echo $value->service_id?>" <?php if($value->service_id == $subService->service_parent_id) : ?>selected="selected"<?php endif; ?>>
                                        <?php echo $value->service_name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    */ ?>
                    <div class="control-group">
                        <label class="control-label">Service Name</label>

                        <div class="controls">
                            <input class="service_name form-control" type="text"
                                   value="<?php echo $subService->service_name; ?>"
                                   placeholder="Service Name">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Default Description</label>

                        <div class="controls">
																<textarea class="service_description form-control"
                                                                          placeholder="Default Description"
                                                                          rows="5"><?php echo $subService->service_description; ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Markup (%)</label>

                        <div class="controls">
                            <input class="service_markup form-control"
                                   placeholder="Markup (%)"
                                   value="<?php echo $subService->service_markup; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success"
                        data-save-service="<?php echo $subService->service_id; ?>">
                    <span class="btntext">Save</span>
                    <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
                         style="display: none;width: 32px;" class="preloader">
                </button>
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>
