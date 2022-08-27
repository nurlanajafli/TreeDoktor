
<header class="header bg-light bg-gradient">
    <ul class="nav nav-tabs nav-white">
        <li class="active"><a href="#general" class="brands-nav" data-toggle="tab"><i class="fa fa-gear text-success"></i><span class="hidden-sm hidden-xs hidden-md"> General</span></a></li>
        <li class="">
            <a href="#logos" class="brands-nav {{if b_id == undefined}}disabled{{/if}}" data-toggle="tab">
                <i class="fa fa-picture-o text-warning"></i><span class="hidden-sm hidden-xs hidden-md">&nbsp;Logos</span>
            </a>
        </li>

        <li class="">
            <a href="#estimate_terms_tab" class="brands-nav {{if b_id == undefined}}disabled{{/if}}" data-toggle="tab"><i class="fa fa-file text-danger"></i>&nbsp;Estimate PDF<span class="hidden-sm hidden-xs hidden-md"> Terms</span></a>
        </li>

        <li class="">
            <a href="#payment_terms_tab" class="brands-nav {{if b_id == undefined}}disabled{{/if}}" data-toggle="tab"><i class="fa fa-file text-danger"></i>&nbsp;Invoice PDF</a>
        </li>

        <li class="">
            <a href="#settings" class="brands-nav {{if b_id == undefined}}disabled{{/if}}" data-toggle="tab">
                <span class="hidden-lg"><button class="btn btn-default btn-sm btn-rounded" style="margin-bottom: -5px;margin-top: -6px;"><i class="fa fa-wrench text-info"></i></button></span>
                <span class="hidden-sm hidden-xs hidden-md"><i class="fa fa-gear text-info"></i> Settings</span>
            </a>
        </li>
        <li class="">
            <a href="#review" class="brands-nav {{if b_id == undefined}}disabled{{/if}}" data-toggle="tab">
                <span class="hidden-lg"><button class="btn btn-default btn-sm btn-rounded" style="margin-bottom: -5px;margin-top: -6px;"><i class="fa fa-wrench text-info"></i></button></span>
                <span class="hidden-sm hidden-xs hidden-md"><i class="fa fa-comment text-info"></i> Review settings</span>
            </a>
        </li>
        <li class="pull-right p-top-10 m-right-20">
            <div class="actions m-n">
                <button type="submit" class="btn btn-success btn-rounded btn-sm btn-next">
                    <span class="hidden-sm hidden-xs" style="width: 9vw; display: inline-block"><strong>Save&nbsp;<i class="fa fa-save"></i></strong></span>
                    <span class="hidden-lg hidden-md">
                        <strong>Save&nbsp;<i class="fa fa-save"></i></strong>
                    </span>
                </button>
            </div>
        </li>
    </ul>
    
</header>
<section>
    <div class="tab-content" style="height: 100%;">

        <div class="tab-pane active p-15" id="general">
            <div class="col-md-12 height-100" style="padding-bottom: 30px; overflow: auto; height: 439px;">
                <div>
                    <aside class="col-lg-6 b-r lter">

                        <h4 class="m-top-5">
                            <i class="fa fa-gear text-info"></i>&nbsp;&nbsp;General
                        </h4>
                        <br><br class="hidden-xs">

                        {{if b_id != undefined}}
                            <input type="hidden" name="b_id" value="{{:b_id}}"/>
                        {{/if}}

                        <div class="form-group row">
                            <div class="col-lg-10 col-sm-12 col-md-12 col-lg-offset-1">
                                <input type="text" class="form-control" data-trigger="change" data-required="true" data-minlength="3" placeholder="Brand Name" name="b_name" value="{{:b_name}}">
                                <span class="form-error text-danger"></span>
                            </div>
                        </div>

                        <div class="line line-dashed line-lg pull-in"></div>

                        <div class="form-group row">
                            <div class="col-lg-10 col-sm-12 col-md-12 col-lg-offset-1">
                                <input type="text" class="form-control phonemask" data-trigger="change" data-required="true" <?php /*data-type="phone"*/ ?> placeholder="Phone" name="bc_phone" value="{{if contact }}{{:contact.bc_phone}}{{/if}}">
                                <span class="form-error text-danger"></span>
                            </div>
                        </div>

                        <div class="line line-dashed line-lg pull-in"></div>

                        <div class="form-group row" id="verify_field">
                            <div class="col-lg-10 col-sm-12 col-md-12 col-lg-offset-1" style="display: flex !important">
                                <input id="email_for_identity" type="text" class="form-control" data-trigger="change" data-required="true" data-type="email" placeholder="Email" name="bc_email" value="{{if contact }}{{:contact.bc_email}}{{/if}}">
                                <input id="identity_id" type="hidden" data-identity-id="{{:identity_id}}">
                                <button id="check_status" class="btn btn-success" style="margin-left: 3px;display:none !important;">Check Status</button>
                                <button id="verify_identity" class="btn btn-success" style="margin-left: 3px;display:none !important;">Verify Email</button>
                            </div>
                            <div class="col-lg-offset-1" style="display: inline-block;padding: 5px 0 0 15px;">
                                <span id="email_identity_status_field" style="display: none !important;">Current Status: <i>{{:current_email_identity_status}}</i></span>
                                <span class="form-error text-danger"></span>
                            </div>
                        </div>

                        <div class="line line-dashed line-lg pull-in"></div>

                        <div class="form-group row">
                            <div class="col-lg-10 col-sm-12 col-md-12 col-lg-offset-1">
                                <input type="text" class="form-control" data-trigger="change" data-type="url" placeholder="https://example.com or http://example.com" name="bc_site" value="{{if contact }}{{:contact.bc_site}}{{/if}}">
                                <span class="form-error text-danger"></span>
                                <span class="text-muted text-sm">https://example.com or http://example.com</span>
                            </div>
                        </div>

                        <div class="line line-dashed line-lg pull-in"></div>


                        <div class="form-group row">
                            <div class="col-lg-10 col-sm-12 col-md-12 col-lg-offset-1">
                                <div class="checkbox m-n">
                                    <label>
                                        <input type="checkbox" name="b_is_default" {{if ~integer(b_is_default)==1}}checked="checked" onclick="return false;"{{/if}} class="parsley-validated" {{if deleted_at }}disabled="disabled"{{/if}}> It's <a href="#" class="text-info">default</a> brand
                                    </label>
                                </div>
                            </div>
                        </div>
                        <br>

                    </aside>
                    <div class="col-lg-6">
                        <section class="vbox">
                            <h4 class="m-top-5">
                                <i class="fa fa-map-marker text-warning"></i>&nbsp;&nbsp;Address
                            </h4>
                            <br><br class="hidden-xs">

                            <div class="form-group row">

                                <div class="col-lg-10 col-sm-12 col-md-12 col-lg-offset-1">
                                    <input type="text" class="form-control" data-trigger="change" data-required="true" placeholder="Address" name="b_company_address" data-autocompleate="true" data-part-address="address" autocomplete="nope" data-parent-selectror="section" value="{{:b_company_address}}">
                                    <span class="form-error text-danger"></span>
                                </div>
                            </div>
                            <input type="hidden" name="b_company_region" data-part-address="region" value="{{:b_company_region}}">

                            <div class="line line-dashed line-lg pull-in"></div>

                            <div class="form-group row">
                                <div class="col-lg-5 col-sm-6 col-md-6 col-xs-6 col-lg-offset-1">
                                    <input type="text" class="form-control" data-trigger="change" data-required="true" placeholder="City" name="b_company_city" data-part-address="locality" autocomplete="nope" value="{{:b_company_city}}">
                                    <span class="form-error text-danger"></span>
                                </div>

                                <div class="col-lg-5 col-sm-6 col-md-6 col-xs-6">
                                    <input type="text" class="form-control" data-trigger="change" data-required="true" placeholder="State" name="b_company_state" data-part-address="state" autocomplete="nope" value="{{:b_company_state}}">
                                    <span class="form-error text-danger"></span>
                                </div>

                            </div>

                            <div class="line line-dashed line-lg pull-in"></div>

                            <div class="form-group row">
                                <div class="col-lg-5 col-sm-6 col-md-6 col-xs-6 col-lg-offset-1">
                                    <input type="text" class="form-control" data-trigger="change" data-required="true" placeholder="Zip" name="b_company_zip" data-part-address="postal_code" autocomplete="nope" value="{{:b_company_zip}}">
                                    <span class="form-error text-danger"></span>
                                </div>

                                <div class="col-lg-5 col-sm-6 col-md-6 col-xs-6">
                                    <input type="text" class="form-control" data-trigger="change" data-required="true" placeholder="Country" name="b_company_country"  data-part-address="country" autocomplete="nope" value="{{:b_company_country}}">
                                    <span class="form-error text-danger"></span>
                                </div>

                            </div>

                            <input type="hidden" class="form-control" data-part-address="lat" name="b_company_lat" value="{{:b_company_lat}}">
                            <input type="hidden" class="form-control" data-part-address="lon" name="b_company_lng" value="{{:b_company_lng}}">

                            <div class="line line-dashed line-lg pull-in"></div>

                        </section>
                    </div>

                </div>
            </div>
        </div>

        <div class="tab-pane" id="logos">
            <div class="form-group main-logo-container">
                <?php $this->load->view('brands/partials/logo'); ?>
            </div>  

        </div>

        <div class="tab-pane" id="estimate_terms_tab">
            <div class="col-md-12 m-top-20 m-bottom-20">
                <div class="row">
                    <div class="col-sm-6 col-lg-6 col-md-6 h4">Terms and Conditions:</div>
                    <div class="col-sm-6 col-lg-6 col-md-6 text-right p-right-15">
                        <a class="btn btn-sm btn-rounded btn-danger m-right-20 pdf-preview" data-editor="estimate-terms" data-template="estimate_terms" data-id="#estimate-terms-text">Preview <i class="fa fa-file"></i></a>
                    </div>
                </div>
            </div>
            <div class="crear"></div>
            <div class="col-md-12 height-100" style="padding-bottom:30px;overflow: auto;">
                <div>

                    <div id="toolbar-container"></div>
                    <div id="estimate-terms" style="height: auto; min-height: 500px"></div>
                    <div style="height: 80px"></div>
                    <textarea name="b_estimate_terms" id="estimate-terms-text"  style="display: none">{{:b_estimate_terms}}</textarea>
                </div>

            </div>

            <div class="clearfix"></div>
        </div>

        <div class="tab-pane" id="payment_terms_tab">
            <div class="col-md-12 m-top-20 m-bottom-20">
                <div class="row">
                    <div class="col-sm-6 col-lg-6 col-md-6 h4">Payment Terms:</div>
                    <div class="col-sm-6 col-lg-6 col-md-6 text-right p-right-15">
                        <a class="btn btn-sm btn-rounded btn-danger m-right-20 pdf-preview" data-id="#payment-terms-text" data-editor="payment-terms" data-template="payment_terms">Preview <i class="fa fa-file"></i></a>
                    </div>
                </div>
            </div>
            <div class="crear"></div>
            <div class="col-md-12 height-100" style="padding-bottom:30px;overflow: auto;">

                <div>

                    <div id="toolbar-container"></div>
                    <div id="payment-terms" style="height: auto; min-height: 500px"></div>
                    <div style="height: 80px"></div>
                    <textarea name="b_payment_terms" id="payment-terms-text" style="display: none">{{:b_payment_terms}}</textarea>

                </div>

            </div>

            <div class="clearfix"></div>

        </div>

        <div class="tab-pane" id="settings" style="position: relative;">
            <div class="col-md-12 m-top-20 m-bottom-20">
                <div class="row">
                    <div class="col-sm-6 col-lg-6 col-md-6 h4">PDF Settings:</div>
                    <div class="col-sm-6 col-lg-6 col-md-6 text-right p-right-15">
                        <a class="btn btn-sm btn-rounded btn-danger m-right-20 pdf-preview" data-editor="pdf-footer" data-template="pdf_footer" data-id="#pdf-footer-text">Preview <i class="fa fa-file"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-md-12 height-100" style="padding-bottom:30px;overflow: auto;">
                <p class="m-top-20">PDF Footer:</p>

                <div id="toolbar-container"></div>
                <div id="pdf-footer" style="height: auto; min-height: 200px"></div>
                <div style="height: 80px"></div>
                <textarea name="b_pdf_footer" id="pdf-footer-text" style="display: none">{{:b_pdf_footer}}</textarea>

            </div>

            <div class="clearfix"></div>
            
        </div>

        <div class="tab-pane" id="review" style="position: relative; height: 100%;">
            <div class="pos-abt" style="left: 0; right: 0; top: 0; bottom: 0; overflow: auto;">
            <div class="col-md-12 m-top-20 m-bottom-20">
                <div class="row">
                    <div class="col-sm-6 col-lg-6 col-md-6 h4">Review Settings:</div>
                </div>
            </div>

            <div class="col-md-12 " style="padding-bottom:30px;overflow: auto;">
                <p class="m-top-20">Review Header:</p>
                <div id="toolbar-container"></div>
                <div id="review-header" style="height: auto; min-height: 200px"></div>
                <textarea name="b_review_header" id="review-header-text" style="display: none" data-placeholder="Write your header...">{{:br_header}}</textarea>
            </div>

            <div class="col-md-12 " style="padding-bottom:30px;overflow: auto;">
                <p class="m-top-20">Dislike message:</p>
                <div id="toolbar-container"></div>
                <div id="dislike-message" style="height: auto; min-height: 200px"></div>
                <textarea name="b_dislike_message" id="dislike-message-text" style="display: none" data-placeholder="Write your message...">{{:br_dislike_message}}</textarea>
            </div>


            <div class="col-md-12 " style="padding-bottom:30px;overflow: auto;">
                <p class="m-top-20">Like message:</p>
                <div id="toolbar-container"></div>
                <div id="like-message" style="height: auto; min-height: 200px"></div>
                <textarea name="b_like_message" id="like-message-text" style="display: none" data-placeholder="Write your message...">{{:br_like_message}}</textarea>
            </div>

            <div class="col-md-12 " style="padding-bottom:30px;overflow: auto;">
                <p class="m-top-20">Links:</p>
                <select class="links-select2 p-top-2">
                    <?php if(!empty($form) && !empty(json_decode($form->br_links)))
                            foreach (json_decode($form->br_links) as $link):?>
                                <option value='<?= $link->id ?>' data-id='<?= random_int(1, 1000)?>'> <?= $link->text ?> </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="b_review_links" value='{{:br_links}}'>
                <span class="popover-markup">
                    <a href="#" class="btn btn-success trigger">
                        <i class="fa fa-plus"></i>
                    </a>
                    <div class="head hide">Create Link</div>
                </span>

                <span class="popover-markup edit">
                    <a href="#" class="btn btn-default trigger" >
                        <i class="fa fa-pencil"></i>
                    </a>
                    <div class="head hide">Edit Link</div>
                </span>

                <a href="#" class="btn btn-danger trigger delete-link" >
                    <i class="fa fa-trash-o"></i>
                </a>

                <div class="content hide">
                    <div class="form-group w-200">
                        <label><span>Link Name: </span><input type="text" class="form-control linkName" /></label>
                        <label><span>Link: </span><input type="text" class="form-control w-200 link" placeholder="https://example.com"/></label>
                        <button type="button" class="btn btn-primary btn-sm create-link"><i class="glyphicon glyphicon-ok"></i></button>
                        <button type="button" class="btn btn-default btn-sm close-link-popover"><i class="glyphicon glyphicon-remove"></i></button>
                        <input type="hidden" class="linkId">
                        <input type="hidden" class="tmpLinkId">
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>

            </div>

        </div>

    </div>

</section>
