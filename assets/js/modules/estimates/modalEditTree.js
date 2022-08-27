$(document).on('click', '.editService', function () {
    var information = Array();
    var service_id = $(this).data('service_id');
    var nameTree = $('input[name="tree_inventory_title[' + service_id + ']"]').val();
    var item = Object;

    item.service_id = service_id;
    item.ties_priority = $('input[name="ties_priority[' + service_id + ']"]').val();
    item.ties_size = $('input[name="ties_size[' + service_id + ']"]').val();
    item.ties_number = $('input[name="ties_number[' + service_id + ']"]').val();
    item.ties_type = $('input[name="ties_type[' + service_id + ']"]').val();
    information.push(item);

    var renderView = {
        template_id: '#infowindowform-modal-tmp',
        view_container_id: '#infowindowform',
        data: information,
    };
    Common.renderView(renderView);

    $('#infowindowform-modal h4.modal-title').text(nameTree);
    form = $('#infowindowform').html();
    $('#infowindowform-modal-body').html(form);

    var selected_types = $('input[name="ties_work_types[' + service_id + ']"]').val();
    JSON.parse(selected_types).map(function (item) {
        var str = "#work_types_select option[value='" + item + "']";
        $(str).prop('selected', true);
    })

    $('#infowindowform-modal-body #ti_tree_type_select').select2();
    $('#infowindowform-modal-body #work_types_select').select2();
    $('#infowindowform-modal').modal('show');
})

$(document).on('change', '#infowindowform-modal-body select, #infowindowform-modal-body input', function () {
    var serviceId = $("#infowindowform-modal-body input[name='service_id']").val();
    var ties_number = $("#infowindowform-modal-body input[name='ties_number']").val();
    var tree_name = $('#infowindowform-modal-body select[name="ti_tree_type"] option:selected').text();
    var new_priority = $('#infowindowform-modal-body select[name="ties_priority"]').val();
    var new_type = $('#infowindowform-modal-body select[name="ti_tree_type"]').val();
    var new_size = $('#infowindowform-modal-body input[name="ties_size"]').val();
    var new_work_types = $('#infowindowform-modal-body select[name="work_types[]"]').val();
    if (new_work_types == null) {
        new_work_types = [];
    }
    var new_name = 'TREE #' + ties_number;
    if (tree_name != 'Empty') {
        new_name += ' ' + tree_name;
    }

    if (new_size.length > 0) {
        new_name += ' ' + new_size + ' DBH';
    }

    $('#infowindowform-modal h4.modal-title').text(new_name);
    $('input[name="tree_inventory_title[' + serviceId + ']"]').val(new_name);
    $('input[name="ties_number[' + serviceId + ']"]').val(ties_number);
    $('section[data-service_id="' + serviceId + '"] .serviceTitle').text(new_name);
    $('input[name="ties_priority[' + serviceId + ']"]').val(new_priority);
    $('input[name="ties_type[' + serviceId + ']"]').val(new_type);
    $('input[name="ties_work_types[' + serviceId + ']"]').val(JSON.stringify(new_work_types));
    $('input[name="ties_size[' + serviceId + ']"]').val(new_size).trigger('change');

})

$('body').on('keyup', 'input[name="ties_number"]', function () {
    var realVal = $(this).val();
    $(this).val(realVal.split('"').join(''));
})