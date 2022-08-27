<script type="text/javascript">
    var brands = <?php echo (isset($brands) && $brands)?json_encode($brands):json_encode([]); ?>;
    var form = <?php echo (isset($form) && $form)?json_encode($form):json_encode([]); ?>;
    
    var images = <?php echo (isset($form->images) && $form->images)?json_encode($form->images):json_encode([]); ?>;
    
    var active_brand = <?php echo (isset($active))?$active:false; ?>;
    var brand_images_config = <?php echo json_encode(config_item('logos')); ?>;
</script>

