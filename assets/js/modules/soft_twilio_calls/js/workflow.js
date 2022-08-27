$(document).on('click', '.add-step', function(e) {
    e.preventDefault();
    e.stopPropagation();
    let prevBlock = $(this).prev();
    let container = prevBlock.find('.task_routing_wrapper_container').find('.routing_steps');
    let containerClone = container.clone();
    let task_routingFiltersTargetsQueue = $('.task_routingFiltersTargetsQueue').length + 1
    let taskRoutingFilterFriendlyName = container.parent().find('.taskRoutingFilterFriendlyName').data('key');
    containerClone.find('select[name="task_routing[filters][0][targets][0][queue]"]').attr({
        'name':'task_routing[filters]['+taskRoutingFilterFriendlyName+'][targets]['+task_routingFiltersTargetsQueue+'][queue]',
        'data-cloned': 1
    });
    containerClone.find('input[name="task_routing[filters][0][targets][0][order_by]"]').attr(
        'name',
        'task_routing[filters]['+taskRoutingFilterFriendlyName+'][targets]['+task_routingFiltersTargetsQueue+'][order_by]'
    );

    prevBlock.find('.task_routing_wrapper_container').append(containerClone.html());
    prevBlock.find('.task_routing_wrapper_container').find('.routing_steps-remove').last().removeClass('hide');

});
$(document).on('click', '.routing_steps-remove, .task_routing-remove', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).parent().remove();

});
$(document).on('click', '.add-filter', function(e) {
    e.preventDefault();
    e.stopPropagation();
    let prevBlock = $(this).parent().prev();
    let container = prevBlock.find('.tr_w')
    let containerClone = container.clone();

    let task_routingFiltersTargetsQueue = $('.task_routingFiltersTargetsQueue').length + 1;
    let taskRoutingFilterFriendlyName = $('.taskRoutingFilterFriendlyName').length + 1;

    containerClone.find('input[name="task_routing[filters][0][filter_friendly_name]"]').attr({
        'name': 'task_routing[filters]['+taskRoutingFilterFriendlyName+'][filter_friendly_name]',
        'data-key': taskRoutingFilterFriendlyName
    });
    containerClone.find('input[name="task_routing[filters][0][expression]"]').attr(
        'name',
        'task_routing[filters]['+taskRoutingFilterFriendlyName+'][expression]'
    );
    containerClone.find('select[name="task_routing[filters][0][targets][0][queue]"]').attr(
        'name',
        'task_routing[filters]['+taskRoutingFilterFriendlyName+'][targets]['+task_routingFiltersTargetsQueue+'][queue]'
    );
    containerClone.find('input[name="task_routing[filters][0][targets][0][order_by]"]').attr(
        'name',
        'task_routing[filters]['+taskRoutingFilterFriendlyName+'][targets]['+task_routingFiltersTargetsQueue+'][order_by]'
    );

    containerClone.find('select[data-cloned=1]').parent().parent().parent().parent().parent().parent().remove();
    prevBlock.append(containerClone.html());
    prevBlock.find('.task_routing-remove').last().removeClass('hide');
});


$(document).on('submit', 'form[name="modalDeleteNotify"]', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        data: {},
        success: function (data) {
            if (!data.error) {
                document.location = data.url;
                window.location.reload();
                return true;
            }
        },
        type: 'POST',
        dataType: 'json'
    });
});
$('a.delete-workflow').click(function (event) {
    event.preventDefault();
    $('form[name="modalDeleteNotify"]').attr('action', $(this).attr('href'));
    $('#deleteModal').modal('show')
});