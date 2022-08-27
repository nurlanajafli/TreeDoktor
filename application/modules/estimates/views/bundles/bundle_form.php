<header class="panel-heading">
    <?php if(isset($bundle) && !empty($bundle) && $bundle !== true): ?>
        Edit Bundle
    <?php else: ?>
        Create Bundle
    <?php endif; ?>
</header>
<form data-type="ajax" id="saveForm" data-location="<?php echo base_url('estimates/bundles'); ?>" data-url="<?php echo base_url('estimates/bundles/save'); ?>" data-callback="ItemSaveCallback">

    <div class="modal-body">
        <div class="form-horizontal">

            <?php if(isset($bundle) && !empty($bundle)): ?>
                <input type="hidden" name="service_id" value="<?php echo isset($bundle)?element('service_id', $bundle, ''):''; ?>">
            <?php endif; ?>
            <div class="control-group">
                <label class="control-label">Bundle Name:</label>

                <div class="controls">
                    <input class="bundle_name form-control" name="bundle_name" type="text" value="<?php echo isset($bundle)?element('service_name', $bundle, ''):''; ?>" placeholder="Bundle Name">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Bundle Description:</label>

                <div class="controls">
                    <textarea class="service_description form-control" name="bundle_description" placeholder="Bundle Description" rows="5"><?php echo isset($bundle)?element('service_description', $bundle, ''):''; ?></textarea>
                </div>
            </div>
            <?php $this->load->view('partials/is_favourite', ['item' => !empty($bundle) ? (object)$bundle : null, 'type' => 'bundle']); ?>
            <div class="control-group">
                <label class="control-label"><strong>Products/services included in the bundle</strong></label>
                <div style="display: flex; align-items: center">
                    <input type="checkbox" id="isView" style="margin-top: 0px !important;" name="is_view_in_pdf" <?php if(isset($bundle) && !empty($bundle['is_view_in_pdf'])) : ?> checked <?php endif; ?>>
                    <label class="form-check-label" style="margin-bottom: 0px !important; margin-left: 5px" for="isView">Display bundle components when printing or sending transactions</label>
                </div>
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Product/service</th>
                        <th scope="col">Qty</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(isset($bundle_services)) :
                        $count = 0;
                        foreach ($bundle_services as $bundle_service) :
                             $count++; ?>
                        <tr>
                            <th scope="row"><?php echo $count ?></th>
                            <td><input type="text" class="select2 w-200" value="<?php echo $bundle_service->service_id?>"></td>
                            <td><input type="number" class="form-control text-center" min="1" onchange="handleChange(this);" style="width: 70px!important;" value="<?php echo $bundle_service->qty?>"></td>
                            <td>
                                <a href="#" class="btn btn-danger remove" >
                                    <i class="fa fa-trash-o"></i>
                                </a>
                                <?php if($count == count($bundle_services)) : ?>
                                    <a href="#" class="btn btn-success addBundleProd">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; else :?>
                    <tr>
                        <th scope="row">1</th>
                        <td><input type="text" class="select2 w-200"></td>
                        <td><input type="number" class="form-control text-center" min="1" onchange="handleChange(this);" style="width: 70px!important;" value="1"></td>
                        <td>
                            <a href="#" class="btn btn-danger remove" >
                                <i class="fa fa-trash-o"></i>
                            </a>
                            <a href="#" class="btn btn-success addBundleProd">
                                <i class="fa fa-plus"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endif;?>
                    </tbody>
                </table>
                <input type="hidden" name="bundle_services">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn btn-success saveBundle">
            <span>Save</span>
            <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
                 class="preloader">
        </a>
        <button class="btn closeFormBundle" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>

</form>
