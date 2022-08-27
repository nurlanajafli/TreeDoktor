<?php $this->load->view('includes/header'); ?>
<script>
    <?php $brandId = get_brand_id($estimate_data ?? [], $client_data); ?>
    var OFFICE_LAT = <?php echo brand_office_lat($brandId); ?>;
    var OFFICE_LON = <?php echo brand_office_lon($brandId); ?>;
    <?php $defaultTax = getDefaultTax(); ?>
    var TAX_RATE = <?php echo isset($taxRate) ? $taxRate : ($defaultTax['rate'] ?? 1) ?>;
    var TAX_PERC = <?php echo isset($taxValue) ? $taxValue : ($defaultTax['value'] ?? 0) ?>;
    var EXISTING_FILES = [];
    var DROPZONE_FILES = [];
    $('html').removeClass('app');
    $('header.header').attr('style', 'height:50px;position:fixed;width:100%;z-index:10;');
    $('footer.hidden-xs').css('display', 'none');
    $('#nav').attr('style', 'position: fixed;top: ' + $('header.header').height() + 'px; z-index: 1;');
    $('.vbox:first').css('background-color', '#ebebeb');
    $('#content').attr('style', 'margin: ' + $('header.header').height() + 'px 0 0 ' + ($('#nav').width() + 1) + 'px;display: block;');
    var destination = '';
    var draft = <?php echo isset($draft) && $draft ? $draft : "{}"; ?>;

	<?php if(isset($lead) && !empty((array)$lead)) : ?>
    <?php if($lead->latitude && $lead->longitude) : ?>
    destination = new google.maps.LatLng(<?php echo $lead->latitude; ?>, <?php echo $lead->longitude; ?>);
    <?php else : ?>
    destination = '<?php echo addslashes($lead->lead_address); ?>, <?php echo addslashes($lead->lead_city); ?>, <?php echo addslashes($lead->lead_state); ?>, <?php echo addslashes($lead->lead_country); ?>, <?php echo addslashes($lead->lead_zip); ?>';
    <?php endif; ?>
    <?php endif; ?>
</script>
<script src="<?php echo base_url('assets/js/html2canvas.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo base_url('/assets/css/modules/estimates/estimate.css?v=1.17'); ?>"/>
<?php if (isset($draft) && $draft) : $draft = (array)json_decode($draft); endif; ?>

<!-- Edit Client Details Modal Loader -->
<?php $this->load->view('clients/client_information_update_modal'); ?>
<!-- End of Edit Customer Details Modal-->
<!--<input type="hidden" id="allTaxes" value="--><?php //echo $allTaxes ?><!--">-->
<input type="hidden" value='<?php echo json_encode($allTaxes); ?>' id="allTaxes">
<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<?php if(isset($lead) && !empty((array)$lead) && !isset($estimate_data)) : ?>
            <li><a href="<?php echo base_url('leads'); ?>">Leads</a></li>
            <li class="active pos-rlt">
                New Estimate - <?php echo $lead->lead_id; ?>-E

                <?php if (isset($draft_info) && $draft_info) : ?>
                    <div class="pos-abt" style="top: -1px;left: 110%;">
                        <span class="label bg-danger">Draft <?php echo date('d M Y H:i:s', $draft_info['date']); ?></span>
                    </div>
                <?php endif; ?>

            </li>
        <?php else : ?>
            <li><a href="<?php echo base_url('estimates'); ?>">Estimates</a></li>
            <li><a href="<?php echo base_url($estimate_data->estimate_no); ?>">Profile
                    - <?php echo $estimate_data->estimate_no; ?></a></li>
            <li class="active">Edit</li>
        <?php endif; ?>
    </ul>


    <!-- Edit Client Details Modal Loader -->
    <?php $this->load->view('clients/client_information_display'); ?>
    <!-- End of Edit Customer Details Modal-->

    <!-- Create estimate -->
    <form data-type="ajax" data-before="prevalidation" data-url="<?php echo base_url('estimates/save_estimate'); ?>"
          enctype="multipart/form-data" id="estimateForm" method="post" data-callback="saveSuccess"
          data-show_processing="1">
        <!-- Original lead display -->
	<?php if(isset($lead) && !empty((array)$lead)) : ?>
            <section class="col-md-12 panel panel-default p-n">
                <header class="panel-heading">
                    Original Lead
                    <!--
                    <?php if(isset($tree_inventory) && count($tree_inventory)): ?>
                    <div class="pull-right m-bottom-0">
                        <div class="checkbox m-n">
                            <label class="checkbox-custom">
                                <input type="checkbox" name="tree_inventory_pdf" <?php if(isset($estimate_data) && $estimate_data->tree_inventory_pdf): ?>checked="checked"<?php endif;?> >
                                <i class="fa fa-fw fa-square-o"></i>
                                <strong style="color: #4aa700;text-decoration: underline;">Tree Inventory PDF</strong>&nbsp;&nbsp;
                            </label>
                            <i class="h4 fa fa-file-text-o"></i>
                        </div>
                    </div>
                    <?php endif; ?>
                    -->
                    <div class="pull-right m-bottom-0 m-right-10" style="margin-bottom: -14px; margin-top: -8px;">
                    <?php $this->load->view('brands/partials/estimate_brands_dropdown', ['brand_style'=>'', 'class'=>'input-sm select2 p-n']); ?>
                    </div>
                    <div class="clear"></div>
                </header>
                <div class="p-10">
                    <div class="col-md-6">
                        <!--					Date:&nbsp;--><?php //echo $lead->lead_date_created; ?><!--, by&nbsp;-->
                        <?php //echo $lead->lead_created_by; ?><!--:<br/>-->
                        Date:&nbsp;<?php echo getDateTimeWithDate($lead->lead_date_created, 'Y-m-d H:i:s', true); ?>, by&nbsp;<?php echo $lead->lead_created_by; ?>
                        :<br/>
                        <strong style="font-size: 14px;">
                            <?php echo nl2br($lead->lead_body); ?>
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <div class="  hide">
                            <div class="m-b-xs">Estimate Provided By:</div>
                            <div class="btn-group " data-toggle="buttons">
                                <label class="btn btn-sm btn-danger<?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->estimate_provided_by == 'meeting') || (isset($draft['provided']) && $draft['provided'] == 'meeting')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="meeting"<?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->estimate_provided_by == 'meeting') || (isset($draft['provided']) && $draft['provided'] == 'meeting')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Meeting
                                </label>
                                <label class="btn btn-sm btn-success<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'schedule meeting') || (isset($draft['provided']) && $draft['provided'] == 'schedule meeting')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="schedule meeting"<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'schedule meeting') || (isset($draft['provided']) && $draft['provided'] == 'schedule meeting')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Schedule Meeting
                                </label>
                                <label class="btn btn-sm btn-danger<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'printed') || (isset($draft['provided']) && $draft['provided'] == 'printed')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="printed"<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'printed') || (isset($draft['provided']) && $draft['provided'] == 'printed')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Printed
                                </label>
                                <label class="btn btn-sm btn-success<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'phone') || (isset($draft['provided']) && $draft['provided'] == 'phone')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="phone"<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'phone') || (isset($draft['provided']) && $draft['provided'] == 'phone')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Phone
                                </label>
                                <label class="btn btn-sm btn-danger<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'email') || (isset($draft['provided']) && $draft['provided'] == 'email')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="email"<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'email') || (isset($draft['provided']) && $draft['provided'] == 'email')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Email
                                </label>
                            </div>
                        </div>
                        <div class="m-n">
                            <div class=" m-b pad-0">
                                <div data-toggle="buttons">
                                    <label class="btn btn-success <?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->full_cleanup == 'yes') || (isset($draft['clean_up']) && $draft['clean_up'] == '1') || (isset($draft) && !isset($draft['clean_up']))) : ?> active<?php endif; ?>">
                                        <i class="fa fa-check text-active"></i> Clean Up
                                        <input type="checkbox"
                                               value="1"<?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->full_cleanup == 'yes') || (isset($draft['clean_up']) && $draft['clean_up'] == '1')  || (isset($draft) && !isset($draft['clean_up']))) : ?> checked<?php endif; ?>
                                               name="clean_up">
                                    </label>
                                    <label class="btn btn-success <?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->brush_disposal == 'yes') || (isset($draft['disposal_brush']) && $draft['disposal_brush'] == 1) || (isset($draft) && !isset($draft['disposal_brush']))) : ?> active<?php endif; ?>">
                                        <i class="fa fa-check text-active"></i> Disposal Brush
                                        <input type="checkbox"
                                               value="1" <?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->brush_disposal == 'yes') || (isset($draft['disposal_brush']) && $draft['disposal_brush'] == 1) || (isset($draft) && !isset($draft['disposal_brush']))) : ?> checked<?php endif; ?>
                                               name="disposal_brush">
                                    </label>
                                    <label class="btn btn-success  <?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->leave_wood == 'yes') || (isset($draft['disposal_wood']) && $draft['disposal_wood'] == 1) || (isset($draft) && !isset($draft['disposal_wood'])))  : ?> active<?php endif; ?>">
                                        <i class="fa fa-check text-active"></i> Disposal Wood
                                        <input type="checkbox"
                                               value="1" <?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->leave_wood == 'yes') || (isset($draft['disposal_wood']) && $draft['disposal_wood'] == 1) || (isset($draft) && !isset($draft['disposal_wood']))) : ?> checked<?php endif; ?>
                                               name="disposal_wood">
                                    </label>
                                    <label class="btn btn-success showHideScheme">
                                        <span class="pull-right p-left-5"><i class="fa fa-plus"></i></span> Project
                                        Scheme
                                        <input type="checkbox" class="">
                                    </label>
                                </div>
                            </div>

                            <div class="clear"></div>
                        </div>

                        <div class="p-right-10 p-left-10 p-top-10 hidden-xs hidden-sm hide">
                            <?php $discount = isset($discount_data['discount_amount']) && $discount_data['discount_amount'] ? $discount_data['discount_amount'] : NULL; ?>
                            <?php $discountPercents = isset($discount_data['discount_percents']) && $discount_data['discount_percents'] ? $discount_data['discount_percents'] : 0; ?>
                            <div class="inline">
                                <label>Discount:</label>
                                <?php $options = array('name' => 'discount', 'id' => 'estimateDiscountValue', 'type' => 'hidden', 'class' => 'form-control input-small', 'value' => $discount); ?>
                                <?php echo form_input($options); ?>
                                <input type="hidden" name="discount_comment" id="estimateDiscountComment"
                                       value="<?php if (isset($discount_data['discount_comment'])) {
                                           echo $discount_data['discount_comment'];
                                       } ?>">
                            </div>
                            <div class="m-t-md inline m-l-sm ">
                                <label class="checkbox">
                                    <?php $checked = $discountPercents ? 'checked' : ''; ?>
                                    <?php echo form_input(['name' => 'discount_percents', 'id' => 'estimateDiscountPercents', 'type' => 'hidden', 'value' => $discountPercents, 'checked' => $checked]); ?>
                                    %
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="">
                    <?php foreach ($est_services as $item) : ?>
                        <div class="checkbox p-left-30">
                            <label>
                                <i class="m-r-sm fa fa-check"></i>
                                <a href="#" title="Add <?php echo $item->service_name; ?>" class="<?php if($item->is_bundle) : ?>createBundleService<?php elseif($item->is_product): ?> createEstimateProduct<?php else: ?>createEstimateService<?php endif; ?>"
                                   data-est-service-id="<?php echo $item->services_id; ?>">
                                    <?php echo $item->service_name; ?>
                                </a>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

            </section>
        <?php else : ?>
            <section class="col-md-12 panel panel-default p-n">
                <header class="panel-heading">Estimate Created</header>
                <div class="p-10">
                    <div class="col-md-4">
                        Date:&nbsp;<?php echo date('Y-m-d', $estimate_data->date_created); ?>
                    </div>
                    <div class="  hide">
                        <div class="col-md-6">Estimate Provided By:
                            <div class="btn-group" data-toggle="buttons">
                                <label class="btn btn-sm btn-danger<?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->estimate_provided_by == 'meeting') || (isset($draft['provided']) && $draft['provided'] == 'meeting')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="meeting"<?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->estimate_provided_by == 'meeting') || (isset($draft['provided']) && $draft['provided'] == 'meeting')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Meeting
                                </label>
                                <label class="btn btn-sm btn-success<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'schedule meeting') || (isset($draft['provided']) && $draft['provided'] == 'schedule meeting')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="schedule meeting"<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'schedule meeting') || (isset($draft['provided']) && $draft['provided'] == 'schedule meeting')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Schedule Meeting
                                </label>
                                <label class="btn btn-sm btn-danger<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'printed') || (isset($draft['provided']) && $draft['provided'] == 'printed')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="printed"<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'printed') || (isset($draft['provided']) && $draft['provided'] == 'printed')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Printed
                                </label>
                                <label class="btn btn-sm btn-success<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'phone') || (isset($draft['provided']) && $draft['provided'] == 'phone')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="phone"<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'phone') || (isset($draft['provided']) && $draft['provided'] == 'phone')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Phone
                                </label>
                                <label class="btn btn-sm btn-danger<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'email') || (isset($draft['provided']) && $draft['provided'] == 'email')) : ?> active<?php endif; ?>">
                                    <input type="radio" name="provided"
                                           value="email"<?php if ((isset($estimate_data) && $estimate_data->estimate_provided_by == 'email') || (isset($draft['provided']) && $draft['provided'] == 'email')) : ?> checked<?php endif; ?>><i
                                            class="fa fa-check text-active"></i> Email
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class=" m-n">
                        <div class="  m-b pad-0">
                            <div data-toggle="buttons">
                                <label class="btn btn-success <?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->full_cleanup == 'yes') || (isset($draft['clean_up']) && $draft['clean_up'] == '1')) : ?> active<?php endif; ?>>
									<i class=" fa fa-check text-active"></i> Clean Up
                                <input type="checkbox"
                                       value="1"<?php if ((isset($estimate_data) && $estimate_data->full_cleanup) || (isset($draft['clean_up']) && $draft['clean_up'] == '1')) : ?> checked<?php endif; ?>
                                       name="clean_up">
                                </label>
                                <label class="btn btn-success <?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->brush_disposal == 'yes') || (isset($draft['disposal_brush']) && $draft['disposal_brush'] == 1)) : ?> active<?php endif; ?>">
                                    <i class="fa fa-check text-active"></i> Disposal Brush
                                    <input type="checkbox"
                                           value="1" <?php if ((isset($estimate_data) && $estimate_data->brush_disposal == 'yes') || (isset($draft['disposal_brush']) && $draft['disposal_brush'] == 1)) : ?> checked<?php endif; ?>
                                           name="disposal_brush">
                                </label>
                                <label class="btn btn-success  <?php if ((!isset($estimate_data) && !isset($draft)) || (isset($estimate_data) && $estimate_data->leave_wood == 'yes') || (isset($draft['disposal_wood']) && $draft['disposal_wood'] == 1)) : ?> active<?php endif; ?>">
                                    <i class="fa fa-check text-active"></i> Disposal Wood
                                    <input type="checkbox"
                                           value="1" <?php if ((isset($estimate_data) && $estimate_data->leave_wood == 'yes') || (isset($draft['disposal_wood']) && $draft['disposal_wood'] == 1)) : ?> checked<?php endif; ?>
                                           name="disposal_wood">
                                </label>
                                <label class="btn btn-success showHideScheme">
                                    <span class="pull-right p-left-5"><i class="fa fa-plus"></i></span> Project Scheme
                                    <input type="checkbox" class="">
                                </label>
                            </div>
                        </div>

                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
            </section>
        <?php endif; ?>

        <input type="hidden" name="estimate_hst_disabled"
               value="<?php echo isset($estimate_data) ? $estimate_data->estimate_hst_disabled : 0; ?>">
        <!-- End of Original lead display -->

        <!--Project Scheme-->
        <?php $this->load->view('estimates/partials/estimate_project_scheme'); ?>
        <!--Project Scheme End-->

        <!--<section class="col-md-12 panel panel-default p-n" style="padding-bottom: 75px!important;">
            <header class="panel-heading">Estimate Services</header>-->
        <div class="col-md-12 panel panel-default" style="padding:0;">
            <div id="readyServices">
                <?php if (isset($estimate_data->mdl_services_orm)) : ?>
                    <?php foreach ($estimate_data->mdl_services_orm as $estimate_service) :

                    if (isset($estimate_service->bundle_records)):
                    foreach ($estimate_service->bundle_records as $estimate_service1): ?>
                        <?php $files = bucketScanDir('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $estimate_service1->id . '/'); ?>
                        <?php if ($files && count($files)) : ?>
                        <?php foreach ($files as $img) : ?>
                        <?php $size = bucket_get_file_info('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $estimate_service1->id . '/' . $img); ?>
                        <script>
                            id = '<?php echo $estimate_service1->id; ?>';
                            if (typeof (EXISTING_FILES.service_files) === 'undefined')
                                EXISTING_FILES.service_files = {};
                            if (typeof (EXISTING_FILES.service_files[id]) === 'undefined')
                                EXISTING_FILES.service_files[id] = [];
                            EXISTING_FILES.service_files[id].push({
                                name: '<?php echo $img; ?>',
                                size: '<?php echo $size['size']; ?>',
                                url: '<?php echo base_url('/uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $estimate_service1->id . '/' . $img); ?>',
                                type: '<?php echo $size['mimetype']; ?>'
                            });
                        </script>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>


                    <?php $files = bucketScanDir('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $estimate_service->id . '/'); ?>
								<?php if ($files && !empty($files)) : ?>
                    <?php foreach ($files as $img) : ?>
                    <?php $size = bucket_get_file_info('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $estimate_service->id . '/' . $img); ?>
                        <script>
                            id = '<?php echo $estimate_service->id; ?>';
                            if (typeof (EXISTING_FILES.service_files) === 'undefined')
                                EXISTING_FILES.service_files = {};
                            if (typeof (EXISTING_FILES.service_files[id]) === 'undefined')
                                EXISTING_FILES.service_files[id] = [];
                            EXISTING_FILES.service_files[id].push({
                                name: '<?php echo $img; ?>',
                                size: '<?php echo $size['size']; ?>',
                                url: '<?php echo base_url('/uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $estimate_service->id . '/' . $img); ?>',
                                type: '<?php echo $size['mimetype']; ?>'
                            });
                        </script>
                    <?php endforeach; ?>
                    <?php endif; ?>
                        <p class="is_product_test"
                           style="display: none;"><?php echo $estimate_service->service->is_product; ?></p>
                        <?php if ($estimate_service->service->is_product && (int)$estimate_service->service->is_product == 1): ?>
                        <?php $this->load->view('products/product_tpl', ['service_data' => $estimate_service, 'access_token' => $access_token]); ?>
                    <?php elseif ($estimate_service->service->is_bundle && (int)$estimate_service->service->is_bundle == 1): ?>
                        <?php
                        $this->load->view('bundles/bundle_tpl', ['service_data' => $estimate_service, 'bundle_id' => $estimate_service->id]);
                        ?>
                    <?php else: ?>
                        <?php $this->load->view('service_tpl', array('service_data' => $estimate_service, 'access_token' => $access_token)); ?>
                    <?php endif; ?>

                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

				<?php if(isset($lead) && !empty((array)$lead) && !isset($estimate_data)) : ?>
                <input type="hidden" name="lead_id" value="<?php echo $lead->lead_id; ?>">
                <input type="hidden" name="client_id" value="<?php echo $lead->client_id; ?>">
				<?php elseif(isset($estimate_data) && !empty($estimate_data)) : ?>
                <input type="hidden" name="estimate_id" value="<?php echo $estimate_data->estimate_id; ?>">
                <input type="hidden" name="estimate_no" value="<?php echo $estimate_data->estimate_no; ?>">
                <input type="hidden" name="lead_id" value="<?php echo $estimate_data->lead_id; ?>">
                <input type="hidden" name="client_id" value="<?php echo $estimate_data->client_id; ?>">
            <?php endif; ?>
            <input type="hidden" name="estimate_review_number"
                   value="<?php if (isset($estimate_data)) : echo intval($estimate_data->estimate_review_number) + 1; else : echo 1; endif; ?>">
<!--            <input type="hidden" value='--><?//= json_encode($categoriesWithChildren) ?><!--' class="categoriesWithChildren">-->
<!--            <input type="hidden" value='--><?//= !empty($classes) ? json_encode($classes) : "" ?><!--' class="classWithChildren">-->
            <input type="hidden" value='<?= $access_token ?>' class="qbAccess">
            <div class="clear"></div>

            <?php /* </section> */ ?>
            <!-------END OLD TEXTAREA-------->


        </div>
        <!--</section>-->

        <?php //if($is_mobile) : ?>
        <?php $this->load->view('partials/floating_mobile_line'); ?>
        <?php //else : ?>
        <?php $this->load->view('partials/floating_line'); ?>
        <?php //endif; ?>
    </form>
    <?php $this->load->view('brands/partials/estimate_brands_dropdown_form'); ?>
    <?php $this->load->view('ajax_pdf_preview'); ?>
</section>
<style type="text/css">
    .floating-mobile-line {
        padding-left: 20px;
        padding-right: 20px;
    }
</style>
<script>
    var serviceTpl = <?php echo $service_tpl; ?>;
    var productTpl = <?php echo $product_tpl; ?>;
    var bundleTpl = <?php echo $bundle_tpl; ?>;
    var trees = <?php echo json_encode($trees); ?>;
    var treeInventory=<?php echo json_encode($tree_inventory); ?>;
    let classWithChildren = <?= !empty($classes) ? json_encode($classes) : '[]' ?> ;
    $(document).ready(function () {
        $('.showAllLine').on('click', function () {
            var obj = $(this);
            if ($('.floating-mobile-line').hasClass('closed')) {
                $('.floating-mobile-line').css('height', '350px');
                $('.floating-mobile-line').removeClass('closed').addClass('opened');
                $(obj).find('i').removeClass('fa-arrow-circle-up').addClass('fa-arrow-circle-down');
            } else {
                $('.floating-mobile-line').css('height', '140px');
                $('.floating-mobile-line').removeClass('opened').addClass('closed');
                $(obj).find('i').removeClass('fa-arrow-circle-down').addClass('fa-arrow-circle-up');
            }
            return true;
        });
        $(window).resize(function () {
            $('#content').attr('style', 'margin: ' + $('header.header').height() + 'px 0 0 ' + ($('#nav').width() + 1) + 'px;display: block;');
        });
        $('.floating-line').attr('style', 'left: ' + ($('#nav').width() + 1) + 'px; right: 0; width: calc(100% - 220px);');
        $('.floating-line').fadeIn(10000);
        Dropzone.autoDiscover = false;
        $.each($('.serviceGroup'), function (key, val) {
            initDropzone($(val).find('.dropzone:first'));
        });
        /*Dropzone.autoDiscover = false;
        var myDropzone = $(".dropzone").dropzone({
            acceptedFiles: 'image/*',
            url: 'aaa',
            addRemoveLinks: true,
            autoProcessQueue: false,
            //autoQueue: false
        });*/
        $.each($('[name="estimate_crew_notes"]'), function (key, val) {
            if (!$(val).is(":visible"))
                $(val).parents('section:first').remove();

        });
        //$('#readyServices').find('.serviceGroup:last').css('padding-bottom', '70px');

    });

    function showHideCalc(a) {
        var obj = $(a).parents('.serviceGroup:first');
        var calc = $(obj).find('.calculateBlock');
        var expenses = $(obj).find('.serviceExtraExpenses');
        var crew = $(obj).find('.crewEquipmentBlock');
        if ($(calc).is(":visible")) {
            $(calc).fadeOut('slow');
            $(expenses).fadeOut('slow');
            $(crew).fadeOut('slow');
            //$(obj).css('padding-bottom', '0');
            //$('#readyServices .serviceGroup:last').css('padding-bottom', '75px');
        } else {
            $(calc).fadeIn('slow');
            $(expenses).fadeIn('slow');
            $(crew).fadeIn('slow');
            console.log($('#readyServices').find('.serviceGroup:last .crewEquipmentBlock').is(":visible"));
            /*if($('#readyServices').find('.serviceGroup').length > 1)
                $('#readyServices').find('.serviceGroup:last').css('padding-bottom', '70px');
            else
                $('#readyServices').find('.serviceGroup:last').css('padding-bottom', '0px');*/
        }
        return false;
    }
</script>
<script src="<?php echo base_url('assets/js/modules/estimates/estimates.js?v=1.44'); ?>"></script>
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">
<script src="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.js'); ?>"></script>
<?php if(isset($lead) && !empty((array)$lead) && !isset($estimate_data)) : ?>
    <script src="<?php echo base_url('/assets/js/modules/estimates/estimates_draft.js?v=3.45'); ?>"></script>
<?php endif; ?>
<script src="<?php echo base_url('assets/js/jquery.ui.touch-punch.min.js'); ?>"
        type="text/javascript" charset="utf-8"></script>
<script src="<?php echo base_url('/assets/js/kinetic.js'); ?>"></script>

<script src="<?php echo base_url('/assets/js/canvas.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.min.js"></script>

<?php $this->load->view('templates/infowindowform'); ?>
<script src="<?php echo base_url('/assets/js/modules/estimates/modalEditTree.js?v=1.04'); ?>"></script>

<?php $this->load->view('includes/footer'); ?>
