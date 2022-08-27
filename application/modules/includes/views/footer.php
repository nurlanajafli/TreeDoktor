<?php $this->load->view('includes/confirm'); ?>
<style>
    .file-list-name-block{
        max-width: 90%;
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-list-name{
        max-width: 59%;
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
<script type="text/x-jsrender" id="ajax-preloader-tmp">
<div id="global-block-preloader" class="text-center clearfix wrapper-lg" style="display:none">
    {{if title!=undefined && title}}
    <h1 class="m-t-lg">{{:title}}</h1>
    {{/if}}
    {{if text!=undefined && text}}
        <h3 class="text-muted">{{:text}}</h3>
    {{/if}}
    <p class="m-t-lg"><i class="fa fa-spinner fa fa-spin fa fa-3x"></i></p>
</div>
</script>

<?php $disabledFor = [6, 15, 31, 32, 33, 44, 47, 49, 53, 146]; ?>
<!-- Content End -->
<!-- Footer -->
<script>
	$(document).ready(function () {
        /***FIX DATATABLE WIDTH (scroll)***/
        $('.dataTable').on('init.dt', function(){ $(this).css('width', ($(this).width() - 10) + 'px'); });
	});
</script>
<link type="text/css" rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>"></link>

<link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.css" type="text/css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/theme.css?v=1.0" type="text/css" />

<style>
    .mce-panel { background: #fff; border-color: #00426140}
    .mce-btn button, .mce-btn{
        background: #fbfeff;
        border-color: #d2ebf3;
    }

    .mce-btn button, .mce-btn button span, .mce-btn button i{ color: #286090; }

    .mce-btn:hover{ background: #e8f5ff;
        border-color: #c0d8e6; }

    .mce-btn:hover button, .mce-btn:hover button i, .mce-btn:hover button span, .mce-active button span, .mce-active button i, .mce-active button{
        color: #0071fd;
    }

    .tox-dialog {
        z-index: 99999999 !important;
    }
</style>



<style>
    .pac-container{ min-width: 380px!important; }
</style>

<!-- Placed at the end of the document so the pages load faster -->
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/bootstrap.js?v=1.03"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/datepicker/bootstrap-datepicker.js"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/datetimepicker/bootstrap-datetimepicker.js"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/js/bootstrap-editable.js?v.1'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/js/address.js?v=1.02'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/js/contact.js?v=1.02'); ?>" type="text/javascript"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/js/status.js?v.1.02'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/js/followup_status.js?v.1'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/js/leads_status.js?v.1'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/js/estimates_status.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/js/invoices_status.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/app.js"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/app.plugin.js?v=1"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/fuelux/fuelux.js'); ?>"></script>
<script type="module" src="<?php echo base_url('assets/vendors/diez/js/app.js'); ?>"></script>

<script src="<?php echo base_url('assets/js/libs/jsrender.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/jquery.inputmask.bundle.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js/soundmanager/script/berniecode-animator.js'); ?>"></script>

<script type="text/javascript" src="<?php echo base_url('assets/js/soundmanager/script/soundmanager2.js'); ?>"></script>

<script type="text/javascript" src="<?php echo base_url('assets/js/soundmanager/script/360player.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js/libs/bootstrap-notify/bootstrap-notify.min.js'); ?>"></script>

<script src="<?php echo base_url(); ?>assets/js/modules/brands/brands-ui.js?v=0.4"></script>

<script src="<?php echo base_url(); ?>assets/js/modules/clients/clients_letters.js?v=1.21"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/clients/client_notes.js?v=1.12"></script>


<style>
    .mce-email-variables-item{
        position: relative;
        display: block;
        min-width: 250px;
    }
    .submenu-tooltip{
        position: absolute;
        top: 0;
        bottom: 0;
        padding: 5px 0;
    }
    /*
    span.variable-info:before {
        content: "\f301";
        font-family: FontAwesome;
    }*/


</style>
<script type="text/javascript">
    var PICTURE_PATH = '<?php echo defined('PICTURE_PATH')?PICTURE_PATH:''; ?>';
    var EMAIL_TEMPATE_VARIABLES = <?php echo json_encode(application\modules\clients\models\ClientLetter::CLIENT_LETTER_KEYWORDS); ?>;
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
</script>

<script>
  //fix popover version bug
  if ($.fn.popover.Constructor.VERSION == "3.3.7") {
    $(document).on("hidden.bs.popover", "[data-toggle='popover']",  function() {
      $(this).data("bs.popover").inState.click = false
    })
  }
</script>

</section>
</section>

</section>
<?php echo isset($renderer) ? $renderer->render() : "" ?>
</body>
</html>
