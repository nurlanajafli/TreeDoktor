        {{if default.thumbnail_template=='site_template'}}
        <div class="site-thumbnail image-row-container {{if image!=false }}s3{{else}}default{{/if}}">
            <label class="col-lg-12 col-sm-12 col-md-12 control-label image-row-label text-right p-n">
                <strong>{{:default.label}}: ({{:default.width}}x{{:default.height}})</strong>
            </label>
            <header class="bg-success dk header navbar navbar-fixed-top-sm navbar-fixed-top-xs site-thumbnail-header">

                <div class="navbar-header aside-md">

                    <a href="#" class="navbar-brand" style="white-space: nowrap; height: auto; display: inline-block; line-height: 39px;">

                        {{if default.logo_file=='main_logo_file'}}


                        <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
                        <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">
                        <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="max-height: 33px;margin-top: -3px;">

                        {{/if}}

                    </a>

                </div>

            </header>
            <section>
                <section class="hbox stretch">
                    <aside class="bg-light lter b-r aside-md" style="width: 110px; height: 228px;">
                        <nav class="nav-primary" style="zoom: 46%;">
                            <ul class="nav">
                                <li>
                                    <a>
                                        <i class="fa fa-users icon"><b class="bg-warning"></b></i>
                                        <span class="pull-right">
                                            <i class="fa fa-angle-down text"></i>
                                            <i class="fa fa-angle-up text-active"></i>
                                        </span>
                                        <span>Clients</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="fa fa-dot-circle-o icon"><b class="bg-info"></b></i>
                                        <span class="pull-right">
                                            <i class="fa fa-angle-down text"></i>
                                            <i class="fa fa-angle-up text-active"></i>
                                        </span>
                                        <span>Contracts</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="fa fa-bars icon"><b class="bg-success"></b></i>
                                        <span class="pull-right">
										<i class="fa fa-angle-down text"></i>
										<i class="fa fa-angle-up text-active"></i>
									    </span>
                                        <span>Leads</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="fa fa-tasks icon"><b class="bg-danger"></b></i>
                                        <span class="pull-right">
                                            <i class="fa fa-angle-down text"></i>
                                            <i class="fa fa-angle-up text-active"></i>
                                        </span>
                                        <span>Tasks</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="fa fa-columns icon"><b class="bg-warning"></b></i>
                                        <span class="pull-right">
                                            <i class="fa fa-angle-down text"></i>
                                            <i class="fa fa-angle-up text-active"></i>
                                        </span>
                                        <span>Estimates</span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="fa fa-gears icon"><b class="bg-info"></b></i>
                                        <span class="pull-right">
                                            <i class="fa fa-angle-down text"></i>
                                            <i class="fa fa-angle-up text-active"></i>
                                        </span>

                                        <span>Workorders</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </aside>
                    <section class="bg-white bg-light dk" style="width: 100%;">
                        <section class="vbox">
                            <section class="hbox stretch"></section>
                        </section>
                    </section>
                </section>
            </section>

            <button class="btn btn-default btn-rounded btn-icon edit-main-logo"  type="button"><i class="fa fa-pencil"></i></button>
        </div>
        {{/if}}
        {{if default.thumbnail_template=='estimate_template'}}
        <div class="estimate-pdf-thumbnail image-row-container {{if image!=false }}s3{{else}}default{{/if}}">

            {{if default.logo_file=='estimate_left_side_file'}}
            <?php /*
            <div class="arrow-hover arrow-right">
                <div></div>
            </div>
            */ ?>
            <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
            <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">
            <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="position: absolute;left: 0;top: 0;bottom: 0;width: 15px;max-height: 100%;height: 100%;">

            {{/if}}

            <div style="position: relative">
                <label class="col-lg-12 col-sm-12 col-md-12 control-label image-row-label text-right p-n" style="top: -34px;">
                    <strong>{{:default.label}}: ({{:default.width}}x{{:default.height}})</strong>
                </label>


                <div class="holder p_top_20">
                    {{if default.logo_file=='estimate_logo_file'}}
                    <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
                    <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">
                    <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="<?php echo config_item('company_header_pdf_logo_styles'); ?> max-height: 50px;">
                    {{/if}}
                </div>

                <div class="estimate_number m-top-10 p-top-15">
                    <div class="estimate_number_1 estimate-number-title">Estimate #</div>
                    <div class="estimate_number_2 estimate-number">00000-E</div>
                    <div class="clearBoth"></div>
                </div>


                <?php /*
                <div class="col-md-7 col-sm-7 col-xs-7 text-left">
                    <div class="text-right p-top-20" style="display: inline-block;">
                        <div class="pull-left estimate-number-title">Estimate #</div>
                        <div class="pull-left estimate-number">00000-E</div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="col-md-5 col-sm-5 col-xs-5 text-center">
                    {{if default.logo_file=='estimate_logo_file'}}
                    <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
                    <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">
                    <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="<?php echo config_item('company_header_pdf_logo_styles'); ?>max-height: 50px;">
                    {{/if}}
                </div>
                */ ?>


            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="pdf-client-info">Client Information</div>
                </div>
                <div class="col-lg-12 m-bottom-20"><div class="pdf-client-info-body"></div></div>
                <div class="col-lg-12"><div class="pdf-client-info">Proposed Work</div></div>
                <div class="col-lg-12"><div class="services-info"></div></div>
            </div>
            <button class="btn btn-default btn-rounded btn-icon edit-main-logo" style="position: absolute;top: -15px;right: -20px;" type="button"><i class="fa fa-pencil"></i></button>
        </div>
        {{/if}}
        {{if default.thumbnail_template=='invoice_template'}}
        <div class="estimate-pdf-thumbnail image-row-container {{if image!=false }}s3{{else}}default{{/if}}">

            {{if default.logo_file=='invoice_left_side_file'}}


            <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
            <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">
            <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="position: absolute;left: 0;top: 0;bottom: 0;width: 15px;max-height: 100%; height: 100%;">

            {{/if}}



            <div class="row">
                <label class="col-lg-12 col-sm-12 col-md-12 control-label image-row-label text-right p-n">
                    <strong>{{:default.label}}: ({{:default.width}}x{{:default.height}})</strong>
                </label>
                <div class="col-md-7 col-sm-7 col-xs-7 text-left">
                    <div class="text-right p-top-20" style="display: inline-block;">
                        <div class="pull-left estimate-number-title">Invoice #</div>
                        <div class="pull-left estimate-number">00000-I</div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="col-md-5 col-sm-5 col-xs-5 text-center">
                    {{if default.logo_file=='invoice_logo_file'}}


                    <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
                    <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">

                    <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="max-height: 50px">
                    {{/if}}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="pdf-client-info">Client Information</div>
                </div>
                <div class="col-lg-12 m-bottom-20"><div class="pdf-client-info-body"></div></div>
                <div class="col-lg-12"><div class="pdf-client-info">Invoice Details</div></div>
                <div class="col-lg-12"><div class="services-info"></div></div>
            </div>
            <button class="btn btn-default btn-rounded btn-icon edit-main-logo" style="position: absolute;top: -15px;right: -20px;" type="button"><i class="fa fa-pencil"></i></button>
        </div>

        {{/if}}
        {{if default.thumbnail_template=='payment_template'}}
        <div class="estimate-pdf-thumbnail image-row-container {{if image!=false }}s3{{else}}default{{/if}}">

            {{if default.logo_file=='invoice_left_side_file'}}


            <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
            <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">
            <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="position: absolute;left: 0;top: 0;bottom: 0;width: 15px;height: 100%;">
            {{/if}}
            <div class="row">
                <label class="col-lg-12 col-sm-12 col-md-12 control-label image-row-label text-right p-n">
                    <strong>{{:default.label}}: ({{:default.width}}x{{:default.height}})</strong>
                </label>
                <div class="col-md-7 col-sm-7 col-xs-7 text-left">
                    <div class="text-right p-top-20" style="display: inline-block;">
                        <div class="pull-left estimate-number-title">Invoice #</div>
                        <div class="pull-left estimate-number">00000-I</div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="col-md-5 col-sm-5 col-xs-5 text-center">
                    {{if default.logo_file=='payment_logo_file'}}


                    <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
                    <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">

                    <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="max-height: 50px">
                    {{/if}}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="pdf-client-info">Client Information</div>
                </div>
                <div class="col-lg-8 m-bottom-20"><div class="pdf-client-info-body"></div></div>
                <div class="col-lg-4 m-bottom-20"></div>
                <div class="col-lg-12">
                    <p class="text-center" style="font-size: 10px;font-weight: 600;line-height: 1.5;">Thank you for choosing {{if form.b_name}}{{:form.b_name}}{{/if}}</p>
                    <p class="text-center" style="font-size: 10px;font-weight: 600;line-height: 1.5;"><a>Press here for a copy of the invoice</a></p>
                    <p class="text-center h5"><b>Invoice Balance 0.00</b></p>
                    <p class="text-center" style="font-size: 10px;font-weight: 600;line-height: 1.5;">Thank you for your business. We will contact you shortly</p>
                </div>

            </div>
            <button class="btn btn-default btn-rounded btn-icon edit-main-logo" style="position: absolute;top: -15px;right: -20px;" type="button"><i class="fa fa-pencil"></i></button>
        </div>

        {{/if}}

        {{if default.thumbnail_template=='whatermark_template'}}
        <div class="estimate-pdf-thumbnail image-row-container {{if image!=false }}s3{{else}}default{{/if}}">

            {{if default.logo_file=='estimate_left_side_file'}}

            <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
            <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">
            <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="position: absolute;left: 0;top: 0;bottom: 0;width: 15px;max-height: 100%;height: 100%;">

            {{/if}}

            <div class="row">
                <label class="col-lg-12 col-sm-12 col-md-12 control-label image-row-label text-right p-n">
                    <strong>{{:default.label}}: ({{:default.width}}x{{:default.height}})</strong>
                </label>
                <div class="col-md-7 col-sm-7 col-xs-7 text-left">
                    <div class="text-right p-top-20" style="display: inline-block;">
                        <div class="pull-left estimate-number-title">Estimate #</div>
                        <div class="pull-left estimate-number">00000-E</div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="col-md-5 col-sm-5 col-xs-5 text-center">
                    {{if default.logo_file=='estimate_logo_file'}}


                    <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
                    <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">

                    <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="max-height: 50px">
                    {{/if}}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="pdf-client-info">Client Information</div>
                </div>
                <div class="col-lg-12 m-bottom-20"><div class="pdf-client-info-body"></div></div>
                <div class="col-lg-12"><div class="pdf-client-info">Proposed Work</div></div>
                <div class="col-lg-12">
                    <div class="services-info text-center" style="padding: 0">
                        {{if default.logo_file=='watermark_logo_file'}}


                        <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
                        <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">

                        <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="max-height: 130px; max-width: 98%;">
                        {{/if}}
                     </div>
                </div>
            </div>
            <button class="btn btn-default btn-rounded btn-icon edit-main-logo" style="position: absolute;top: -15px;right: -20px;" type="button"><i class="fa fa-pencil"></i></button>
        </div>
        {{/if}}

        {{if !default.thumbnail_template}}
        <div class="row image-row-container m-n p-5 {{if image!=false }}s3{{else}}default{{/if}}">
            
            <div class="text-center">
                <a href="#" class="thumbnail">
                    <label class="col-lg-12 col-sm-12 col-md-12 control-label image-row-label text-right p-n">
                        <strong>{{:default.label}}: ({{:default.width}}x{{:default.height}})</strong>
                    </label>
                    
                    <input type="hidden" data-name="{{:default.logo_file}}" data-type="file" data-width="{{:default.width}}" data-height="{{:default.height}}">
                    <input type="hidden" name="{{:default.logo_filename}}" data-type="filename">
                    
                    <img src="{{if image!=false }}{{:image.file_url}}{{else}}{{:default.default_image}}{{/if}}" style="max-height: 597px">


                </a>
                <div class="clearfix"></div>
            </div>

            <button class="btn btn-default btn-rounded btn-icon edit-main-logo" style="position: absolute;top: 0px;right: 10px;" type="button"><i class="fa fa-pencil"></i></button>
        </div>
        {{/if}}
        <div class="line line-dashed line-lg pull-in m-15"></div>
    