$(document).ready(function () {
    $('.datepicker').datepicker({format: $('#schedule-appointment-modal #date-format-without-year').val()});

    $('.checkbox-custom > input[type=checkbox]').each(function () {
        var $this = $(this);
        if ($this.data('checkbox')) return;
        $this.checkbox($this.data());
    });
});

/** recomendations */
$(document).on('click', '.information-table', function (e) {
    e.preventDefault();
    let popoverId = $(this).attr('aria-describedby');

    if ($(`#${popoverId}`).hasClass('in')) {
        // close all except current
        $('[data-toggle="popover"]').not(this).popover('hide');
        $(this).popover('show');
    } else {
        $('[data-toggle="popover"]').popover('hide');
    }
});