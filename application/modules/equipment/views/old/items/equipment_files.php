<section class="equipment-files-row">    
        <form action="<?php echo base_url('equipments/save_files/'.$item[0]->item_id); ?>" method="POST"  id="equipment_files_form" name="equipment_files_form" enctype="multipart/form-data">
        
            <?php if(isset($eq_files) && count($eq_files)) : ?>
                <div class="row">
                <?php foreach($eq_files as $k=>$v) : ?>
                    <?php $this->load->view('equipments/items/file_form', ['num' => $k + 1, 'doc' => $v]); ?>
                <?php endforeach; ?>
                <?php if(count($eq_files) < 3) : ?>
                    <?php for($i = 1; $i <= 3 - count($eq_files); $i++) : ?>
                        <?php $this->load->view('equipments/items/file_form', ['num' => count($eq_files) + $i, 'doc' => []]); ?>
                    <?php endfor; ?>
                <?php endif; ?>
                </div>
            <?php else :?>
                <div class="row">
                <?php $this->load->view('equipments/items/file_form', ['num' => 1]); ?>
                <?php $this->load->view('equipments/items/file_form', ['num' => 2]); ?>
                <?php $this->load->view('equipments/items/file_form', ['num' => 3]); ?>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-xs-12">
                    <button class="btn btn-info pull-right m-top-10">Update</button>
                    <a onclick="window.location.href = window.location.href.split('#')[0]+'#tab4'; location.reload();" class="btn btn-info pull-right m-top-10 m-right-10">Cancel</a>
                </div>
            </div>
        </form>    
</section>
    
<script>
var doc_tpl = <?php echo $doc_tpl; ?>;    
$(document).ready(function() {
    $(document).on('click', '.delete-doc', function() {
        if($(this).is('disabled'))
            return false;

        $(this).parents('.files-section:first').remove();

        $.each($('.files-section .doc_title'), function(key, val) {
            if(($(val).text().indexOf('File #') + 1))
                $(val).text('File #' + (key + 1));

        });

        return false;
    });

    $(document).on('click', '.add-doc', function() {
        if($(this).is('disabled'))
            return false;
        
        $('.files-section:last').after(doc_tpl.html);
        $('.files-section:last input').val('');
        $('.files-section:last select.file-notification-user').html($('.files-section:first select.file-notification-user').html()).val('').change();
        $('.datepicker').datepicker();
        $.each($('.files-section .doc_title'), function(key, val) {

            if(($(val).text().indexOf('File #') + 1))
                $(val).text('File #' + (key + 1));
            if(key + 1 == $('.files-section .doc_title').length) {
                $(val).parents('.files-section:first').find('.doc_title').text('File #' + (key + 1));
            }
        });

        return false;
    });
});    
</script>