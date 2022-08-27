$(document).on('submit', 'form[name="modalDeleteNotify"]', function(e) {
    e.preventDefault();
    var currentModal = $(this);
    $.ajax({
        url: currentModal.attr('action'),
        data: {},
        success: function (data) {
            if (!data.error) {
                document.location = data.url;
                window.location.reload();
                currentModal.find('.error').addClass('hidden');
                return true;
            } else {
                console.log(data.error);
                currentModal.find('.error').html(data.error);
                currentModal.find('.error').removeClass('hidden');
            }
        },
        type: 'POST',
        dataType: 'json'
    });
});
$('a.delete-activity').click(function (event) {
    event.preventDefault();
    $('form[name="modalDeleteNotify"]').attr('action', $(this).attr('href'));
    $('#activityModal').modal('show')
});