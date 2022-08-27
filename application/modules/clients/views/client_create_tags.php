<link type="text/css" rel="stylesheet" href="<?php echo base_url('assets/css/modules/clients/client_create_tag.css?v=1.00'); ?>" />

<script>
    window.client_tags = <?php echo json_encode(isset($client_tags) ? $client_tags : []); ?>;
    const isClientData = <?php echo isset($client_data) ? 'true' : 'false'; ?>;
</script>
<?php if(isset($client_data)): ?>
    <script src="<?php echo base_url(); ?>assets/js/libs/disableAutoFill/jquery.disableAutoFill.min.js"></script>
<?php endif; ?>
<script src="<?php echo base_url(); ?>assets/js/modules/clients/client_tags.js?v=1.12"></script>

    <div class="client-tags-container m-bottom-5">
        <span class="pull-left h5" style="padding-top: 7px"><i class="fa fa-bookmark icon-muted fa-fw text-warning"></i>&nbsp;Tag:</span>
        <?php if(isset($client_data)): ?>
            <form id="client-tags-form" autocomplete="off">
        <?php endif; ?>
            <input class="" type="text" id="client-tags" multiple="multiple" autocomplete="nope"/>
            <input type="hidden" name="client_tags" value="" autocomplete="off" autocorrect="off"
                   autocapitalize="off" spellcheck="false">
            <div class="clear"></div>
        <?php if(isset($client_data)): ?>
            </form>
        <?php endif; ?>
    </div>
    <div class="clear"></div>

<?php if(isset($client_data)): ?>
    <form id="cteate-client-tag" data-type="ajax" method="POST" data-url="<?php echo base_url('/clients/ajax_add_tag'); ?>" data-global="false" data-callback="">
        <input type="hidden" name="tag_name" id="tag_name" value="">
        <input type="hidden" name="client_id" value="<?php echo $client_data->client_id; ?>">
    </form>
    <form id="delete-client-tag" data-type="ajax" method="POST" data-url="<?php echo base_url('/clients/ajax_delete_tag'); ?>" data-global="false" data-callback="">
        <input type="hidden" name="tag_name" id="tag_name" value="">
        <input type="hidden" name="client_id" value="<?php echo $client_data->client_id; ?>">
    </form>
<?php endif; ?>
