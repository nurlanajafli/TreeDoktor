$(document).ready(function () {
    $('.dd').nestable({
        maxDepth: 4
    }).on('change', function (e) {
        let items = [];
        let categories = [];
        $.each($('.item'), function (key, val) {
            items[$(val).data('id')] = key + 1;
            let parent = $(this).parent().parent();
            if (parent.hasClass('category') === false) {
                errorMessage('Item can only be added to a category');
                location.reload();
            } else if ($(this).data('parent_id') != parent.data('id')) {
                $(this).data('parent_id', parent.data('id'));
                $.ajax({
                    type: 'POST',
                    url: baseUrl + 'categories/ajaxChangeItemCategory',
                    dataType: 'json',
                    global: false,
                    data: {
                        itemId: $(this).data('id'),
                        categoryId: parent.data('id')
                    },
                    success: function (msg) {
                        if (msg.status == 'error') {
                            errorMessage(msg.message);
                            location.reload();
                        }
                    }
                });
            }
        });
        $.each($('.category'), function (key, val) {
            categories[$(val).data('id')] = key + 1;
            let parent = $(this).closest('ol').closest('li');
            let parentId = parent.data('id');
            if (parentId === undefined) {
                parentId = '';
            }
            if (parent.hasClass('item') === true) {
                errorMessage('Category can only be added to a category');
                location.reload();
            } else if ($(this).data('parent_id') != parentId) {
                $(this).data('parent_id', parentId);
                $.ajax({
                    type: 'POST',
                    url: baseUrl + 'categories/ajaxChangeParentCategory',
                    dataType: 'json',
                    global: false,
                    data: {
                        categoryId: $(this).data('id'),
                        categoryParentId: parentId
                    },
                    success: function (msg) {
                        if (msg.status == 'error') {
                            errorMessage(msg.message);
                            location.reload();
                        }
                    }
                });

            }
        });
        if(items.length > 0) {
            $.ajax({
                type: 'POST',
                url: baseUrl + 'estimates/ajax_estimate_priority_service',
                dataType: 'json',
                global: false,
                data: {data: items}
            });
        }
        if(categories.length > 0) {
            $.ajax({
                type: 'POST',
                url: baseUrl + 'estimates/ajax_estimate_priority_category',
                dataType: 'json',
                global: false,
                data: {data: categories}
            });
        }
    });
    $('.showHideDesc').click(function () {
        let obj = $(this).parents('.dd3-content:first').find('.service-desc');
        if ($(obj).is(' :visible'))
            $(this).find('i').removeClass('fa-angle-up').addClass('fa-angle-down');
        else
            $(this).find('i').removeClass('fa-angle-down').addClass('fa-angle-up');
        $(obj).slideToggle();
    });
    if(typeof categoriesWithChildren != 'undefined')
        $('.parentCategorySelect').select2({data: categoriesWithChildren});
    if(typeof classWithChildren != 'undefined')
        $('.parentClassSelect').select2({data: classWithChildren});

    $('.deleteCategory').click(function () {
        $.ajax({
            type: 'POST',
            url: baseUrl + 'categories/deleteCategory',
            dataType: 'json',
            data: {
                categoryId: $(this).data('id')
            },
            success: function (msg) {
                if (msg.status == 'error') {
                    errorMessage(msg.message);

                } else {
                    location.reload();
                }
            }
        });
    });
});

function toggleCategoryCallback(resp) {
    if (resp.status == 'error') {
        errorMessage(resp.message);
    } else {
        location.reload();
    }
}

function checkCategoryError(resp) {
    if (resp.status == 'error') {
        errorMessage(resp.message);
    }
}


