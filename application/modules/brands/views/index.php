<?php $this->load->view('includes/header'); ?>

<form data-global="true" data-type="ajax" id="brands-form" data-url="<?php echo site_url('brands/save'); ?>" data-callback="Brands.save_callback" data-method="POST" class="vbox">
    <section class="hbox stretch brands">
        <aside class="aside-lg bg-light lter b-r brands-list-container" id="subNav" style="position: relative;">
            <div class="wrapper b-b header">
                <i class="fa fa-bookmark text-warning"></i>&nbsp;&nbsp;<strong>Brands List</strong>
            </div>
            <ul class="list-group no-radius m-b-none m-t-n-xxs list-group-lg no-border slim-scroll" id="brands-list">

            </ul>
            
            <div style="position: absolute;bottom: 0px;right: 0px;left: 0px; background: #fafafb; border-top: 1px solid #d4d4d4;">
            <ul class="list-group no-radius list-group-lg no-border p-top-15" style="margin-bottom: 15px;">
                <li class="b-light text-center" id="create-new-brand">
                    <a href="#" class="btn btn-success btn-rounded btn-sm"><i class="fa fa-plus m-t-xs text-xs text-prymary"></i>&nbsp;&nbsp;Create new brand</a>
                </li>
            </ul>

            </div>
        </aside>


        <aside class="bg-white">
            <section class="vbox" id="brand-form">
                
            </section>
        </aside>
        <?php /*
        <aside class="col-lg-4 b-l lter">
            <section class="vbox">
                <section class="scrollable">
                    
                        <div class="panel-body p-top-10">
                            <h4 class="p-left-15 m-top-5"><i class="fa fa-picture-o text-warning"></i>&nbsp;&nbsp;System Logos</h4>
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div id="brand-images">
                                
                            </div>
                        </div>
                    
                </section>
            </section>             
        </aside>
        */ ?>
    </section>
    
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    
</form>

<script id="brands-form-tmp" type="text/x-jsrender">
<?php $this->load->view('brands/templates/brands_form'); ?>
</script>

<div class="hidden" id="delete-forms"></div>
<script id="delete-forms-tmp" type="text/x-jsrender">
    <form id="delete-{{:b_id}}-brand" data-global="false" data-type="ajax" data-url="<?php echo site_url('brands/delete'); ?>/{{:b_id}}" data-callback="Brands.save_callback" data-method="POST"></form>
</script>

<div class="hidden" id="restore-forms"></div>
<script id="restore-forms-tmp" type="text/x-jsrender">
    <form id="restore-{{:b_id}}-brand" data-global="false" data-type="ajax" data-url="<?php echo site_url('brands/restore'); ?>/{{:b_id}}" data-callback="Brands.save_callback" data-method="POST"></form>
</script>

<script id="brand-images-tmp" type="text/x-jsrender">
<?php $this->load->view('brands/templates/brand_images'); ?>
</script>

<?php $this->load->view('brands/templates/brands_list'); ?>
<?php $this->load->view('brands/templates/variables'); ?>
<?php $this->load->view('brands/partials/ajax_forms'); ?>
<?php $this->load->view('brands/partials/modals'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">
<script src="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.js'); ?>"></script>

<!--              --- Editor Block---            -->
    <script src="<?php echo base_url('assets/js/libs/quill/dist/quillBase.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/libs/quill/dist/quill.min.js'); ?>"></script>
<!--<script type="module" src="--><?php //echo base_url('assets/js/libs/quill/quillBase.js'); ?><!--"></script>-->
<!--<script type="module" > import quillBase from "--><?php //echo base_url('assets/js/libs/quill/quillBase.js'); ?>//"</script>
<link href="<?php echo base_url('assets/js/libs/quill/dist/quill.snow.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/js/libs/quill/dist/quill.bubble.css'); ?>" rel="stylesheet">
<script src="<?php echo base_url('assets/js/libs/quill/dist/image-resize.min.js'); ?>"></script>
<!--              --- Editor Block ---            -->

<script src="<?php echo base_url(); ?>assets/js/libs/file-input/bootstrap-filestyle.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/libs/file-input/bootstrap-filestyle.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/libs/cropperjs/dist/cropper.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/cropperjs/dist/cropper.min.css'); ?>">

<script src="<?php echo base_url(); ?>assets/js/libs/parsley/parsley.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/brands/brands.js?v=1.34"></script>

<?php if (config_item('default_mail_driver') === 'amazon'): ?>
<script src="<?php echo base_url(); ?>assets/js/modules/mail/mail_check.js?v=0.1"></script>
<?php endif;?>

<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/brands/brands.css'); ?>">

<?php $this->load->view('includes/footer'); ?>
