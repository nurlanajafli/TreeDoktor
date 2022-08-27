window.edit_modal = function(response){
    $('#product-modal .modal-content').html(response.html);
    $('#product-modal').modal();
    Common.mask_currency();
};

$(document).ready(function () {
    Common.mask_currency();
    $('.dd').nestable({
    }).on('change', function(e) {
        $.each($('.category'), function () {
            let parent = $(this).parent().parent();
            let parentId = parent.data('id');
            if(parentId === undefined){
                parentId = '';
            }
            if($(this).data('parent_id') != parentId){
                $(this).data('parent_id', parent.data('id'));
                $.ajax({
                    type: 'POST',
                    url: baseUrl + 'categories/ajaxChangeParentCategory',
                    dataType: 'json',
                    data: {
                        categoryId : $(this).data('id'),
                        categoryParentId : parentId
                    }
                });
            }
        });
    });
});



