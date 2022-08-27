$(document).ready(function () {
    // Disable all the template row inputs
    $('.query-applet tr.hide input').prop('disabled', true);

    $(document).on('change', '.query-applet input.keypress', function (event) {
        var row = $(this).parents('tr');
        $('input[name^="responses"]', row).attr('name', 'keys[' + $(this).val() + ']');
    });

    $(document).on('click', '.query-applet .action.add', function (event) {
        event.preventDefault();
        var row = $(this).closest('tr');
        var newRow = $('tfoot tr', $(this).parents('.query-applet')).html();
        newRow = $('<tr>' + newRow + '</tr>')
            .show()
            .insertAfter(row);
        $('.flowline-item').droppable(Flows.events.drop.options);
        $('td', newRow).flicker();
        $('textarea.response', newRow).attr('name', 'responses[]');
        $('input.keypress', newRow).attr('name', 'keys[]');
        $('input', newRow).prop('disabled', false).focus();
        $(event.target).parents('.options-table').trigger('change');
        return false;
    });

    $(document).on('click', '.query-applet .action.remove', function () {
        var row = $(this).closest('tr');
        var bgColor = row.css('background-color');
        row.animate({
            backgroundColor: '#FEEEBD'
        }, 'fast')
            .fadeOut('fast', function () {
                row.remove();
            });

        return false;
    });

    $(document).on('change', '.query-applet .options-table', function () {
        var first = $('tbody tr', this).first();
        $('.action.remove', first).hide();
    });

    $('.query-applet .options-table').trigger('change');
});
