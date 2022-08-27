$(document).ready(function () {
    $('.scheduling tr.hide input').attr('disabled', 'disabled');

    var app = $('.flow-instance.scheduling');
    $(document).on('audio-selected', '.audio-choice', function (event, text) {
        var instance = $(event.target).parents('.flow-instance.scheduling');
        if (text.length > 0) {
            $(instance).trigger('set-name', 'Scheduling: ' + text.substr(0, 6) + '...');
        }
    });

    $(document).on('change', '.scheduling input.keypress', function (event) {
        var row = $(this).parents('tr');
        $('input[name=^choices]', row).attr('name', 'keys[' + $(this).val() + ']');
    });

    $(document).on('click', '.scheduling .action.add', function (event) {
        event.preventDefault();
        var row = $(this).closest('tr');
        var newRow = $('tfoot tr', $(this).parents('.scheduling')).html();
        newRow = $('<tr>' + newRow + '</tr>')
            .show()
            .insertAfter(row);
        $('.flowline-item').droppable(Flows.events.drop.options);
        $('td', newRow).flicker();
        $('.flowline-item input', newRow).attr('name', 'choices[]');
        $('select.timestart', newRow).attr('name', 'timestart[]');
        $('select.timefinish', newRow).attr('name', 'timefinish[]');
        $('select.sunday.day_check', newRow).attr('name', 'sunday[]');
        $('select.monday.day_check', newRow).attr('name', 'monday[]');
        $('select.tuesday.day_check', newRow).attr('name', 'tuesday[]');
        $('select.wednesday.day_check', newRow).attr('name', 'wednesday[]');
        $('select.thursday.day_check', newRow).attr('name', 'thursday[]');
        $('select.friday.day_check', newRow).attr('name', 'friday[]');
        $('select.saturday.day_check', newRow).attr('name', 'saturday[]');


        $('input', newRow).removeAttr('disabled').focus();
        $(event.target).parents('.options-table').trigger('change');
        return false;
    });

    $(document).on('click', '.scheduling .action.remove', function () {
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

    $(document).on('change', '.scheduling .options-table', function () {
        var first = $('tbody tr', this).first();
        $('.action.remove', first).hide();
    });

    $('.scheduling .options-table').trigger('change');

});
