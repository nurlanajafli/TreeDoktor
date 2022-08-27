<div class="col-lg-7 col-sm-12 col-md-12 lter">

    <div class="row p-15">
        <label class="col-lg-6 col-sm-6 col-md-6 col-xs-6 h4"><i class="fa fa-picture-o text-info"></i>&nbsp;&nbsp;Logo:</label>
        <div class="col-lg-6 col-sm-6 col-md-6 col-xs-6 text-right">
            <a class="btn btn-sm btn-rounded btn-danger m-right-20 cropper-save disabled" data-id="main-logo-image" data-method="destroy" title="Apply">Apply this Image <i class="fa fa-check"></i></a>
        </div>
    </div>
    <br>
    <input type="file" class="main-logo hidden" data-icon="false" data-classButton="btn btn-default" data-classInput="form-control inline input-s">
<?php /*
    <style type="text/css">.cropper-container{ margin:0 auto; max-width: 80%; }</style>
 */ ?>
    <div class="row">
        <div class="col-lg-12 text-center position-relative p-15">

            <div style="overflow: hidden; width: 80%; margin: 0 auto;">
                <img id="main-logo-image" src="<?php echo base_url('assets/img/nopic.jpg'); ?>">
            </div>

        </div>

    </div>
    <dic class="clearfix"></dic>
</div>

<div class="col-lg-5 col-sm-12 col-md-12 b-l height-100">

    <div class="hbox">
        <div class="vbox">
            <div class="scrollable">
                <div class="row p-15">
                    <label class="col-lg-12 col-sm-12 col-md-12 h4"><i class="fa fa-picture-o text-warning"></i>&nbsp;&nbsp;All Logos:</label>
                </div>
                <div id="brand-images" style="padding-bottom: 60px;padding-top: 20px;" class="slim-scroll"></div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<div class="text-right p-15" style="position: absolute;bottom: 0px;right: 0px;left: 0px; background: #fafafb; border-top: 1px solid #d4d4d4; padding: 13px; z-index: 99999">
    <div class="row">
        <div class="col-lg-7 col-sm-12 col-md-12">
            <div class="btn-group">
                <button type="button" data-id="main-logo-image" class="btn btn-primary cropper-move-left disabled" data-method="move" data-option="-10" data-second-option="0" title="Move Left">
                    <span class="fa fa-arrow-left"></span>
                </button>
                <button type="button" data-id="main-logo-image" class="btn btn-primary cropper-move-right disabled" data-method="move" data-option="10" data-second-option="0" title="Move Right">
                    <span class="fa fa-arrow-right"></span>
                </button>
                <button type="button" data-id="main-logo-image" class="btn btn-primary cropper-move-up disabled" data-method="move" data-option="0" data-second-option="-10" title="Move Up">
                    <span class="fa fa-arrow-up"></span>
                </button>
                <button type="button" data-id="main-logo-image"   class="btn btn-primary cropper-move-down disabled" data-method="move" data-option="0" data-second-option="10" title="Move Down">
                    <span class="fa fa-arrow-down"></span>
                </button>
            </div>
            <div class="btn-group">
                <a type="button" data-id="main-logo-image" class="btn btn-default cropper-rotate disabled" data-method="rotate" title="Rotate">
                    <span class="fa fa-repeat"></span>
                </a>
            </div>

            <div class="btn-group">
                <a type="button" data-id=".brands .main-logo" class="btn btn-default cropper-init disabled" data-method="crop" title="Crop">
                    <span class="fa fa-crop"></span>
                </a>
                <a type="button" data-id="main-logo-image" class="btn btn-default cropper-reset disabled" data-method="reset" title="Reset">
                    <span class="fa fa-refresh"></span>
                </a>
                <?php /*
                <a type="button" data-id="main-logo-image" class="btn btn-primary cropper-save disabled" data-method="destroy" title="Destroy">
                    <span class="fa fa-check"></span>
                </a>
                */ ?>
            </div>
            <div class="btn-group">
                <a type="button" data-id="main-logo-image" class="btn btn-primary cropper-zoom-in disabled" data-method="zoom" data-option="0.1" title="Zoom In">
                    <span class="fa fa-search-plus"></span>
                </a>
                <button type="button" data-id="main-logo-image" class="btn btn-primary cropper-zoom-out disabled" data-method="zoom" data-option="-0.1" title="Zoom Out">
                    <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.zoom(-0.1)">
                        <span class="fa fa-search-minus"></span>
                    </span>
                </button>
            </div>
        </div>
        <div class="col-lg-5 col-sm-12 col-md-12 visible-lg">

        </div>
    </div>
</div>