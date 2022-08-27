<!--Modal-->
<div id="new_project" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="col-md-2"></div>
        <div class="modal-content panel panel-xs panel-default p-n col-md-6"  style="border-radius: 8px">
<!--            <header class="panel-heading project-title">Create new project</header>-->
            <form class="form-horizontal" method="post" data-type="ajax" data-url="<?php echo base_url('tree_inventory/save_project'); ?>" data-callback="TreeInventoryMap.project_callback">
                <div class="modal-body p-left-0 p-right-0 p-top-10">
                    <div class="">
                        <div class="form-group m-bottom-5">
                            <label for="project_name" class="col-sm-12"><strong>Name of tree inventory</strong></label>
                            <div class="col-sm-12">
                                <input class="form-control" style="border-radius: 8px"  id="project_name" name="tis_name" type="text" required>
                            </div>
                        </div>
                        <div class="form-group m-bottom-5">
                            <label for="tis_address" class="col-sm-12"><strong>Address</strong></label>
                            <div class="col-sm-12">
                                <input name="tis_address" id="tis_address" style="border-radius: 8px" class="form-control" data-autocompleate="true" data-part-address="address" autocomplete="nope">
                            </div>
                        </div>
                        <div class="form-group m-bottom-5">
                            <label for="tis_city" class="col-sm-12"><strong>City</strong></label>
                            <div class="col-sm-12">
                                <input name="tis_city" id="tis_city"  class="form-control" style="border-radius: 8px"  data-part-address="locality" placeholder="City" autocomplete="nope">
                            </div>
                        </div>
                        <div class="form-group m-bottom-5">
                            <label for="tis_state" class="col-sm-12"><strong>Country</strong></label>
                            <div class="col-sm-12">
                                <input name="tis_state" id="tis_state" class="form-control" style="border-radius: 8px" data-part-address="state" placeholder="State" autocomplete="nope">
                            </div>
                        </div>
                        <div class="form-group m-bottom-5">
                            <div class="col-sm-7 p-right-0">
                                <label for="tis_country" class="col-sm-12 p-left-0"><strong>State</strong></label>
                                <input name="tis_country" id="tis_country" class="form-control" style="border-radius: 8px" data-part-address="country" placeholder="Country" autocomplete="nope">

                            </div>
                            <div class="col-sm-5">
                                <label for="tis_zip" class="col-sm-12 p-left-0"><strong>Zip Code</strong></label>
                                <input name="tis_zip" id="tis_zip"  class="form-control" style="border-radius: 8px"  data-part-address="postal_code" placeholder="Zip/Postal Code" autocomplete="nope">
                            </div>
                        </div>
                        <div class="form-group m-bottom-5">
                            <label for="tis_overlay_path" class="col-sm-12"><strong>Upload overlay</strong></label>
                            <div class="col-sm-12">
                                <label class="btn btn-default btn-file w-100" id="tis_overlay_path" style="border-radius: 8px">
                                    Browse <input type="file" name="tis_overlay" style="display: none">
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer p-bottom-0" style="margin-top: 0px;border-top: none;text-align: center;">
                            <button class="btn btn-success m-right-10" style="border-radius: 8px; width: 100px" type="submit"><span class="btntext">Save</span>
                                <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
                                     style="display: none;width: 84px;" class="preloader">
                            </button>
                            <button class="btn text-danger m-left-10" data-dismiss="modal" style="border-radius: 8px; background-color: white; border: 1px solid #CC806A; width: 100px" aria-hidden="true">Cancel</button>
                        </div>
                        <input type="hidden" data-part-address="lat" name="tis_lat">
                        <input type="hidden" data-part-address="lon" name="tis_lng">
                        <input type="hidden" name="tis_id">
                        <input type="hidden" name="tis_copy">
                        <input type="hidden" name="tis_copy_id">
                        <input type="hidden" name="tis_client_id" value="<?= (isset($client_data) && !empty($client_data->client_id)) ? $client_data->client_id : '' ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>