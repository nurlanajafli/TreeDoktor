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
$('a.delete-task-queue').click(function (event) {
    event.preventDefault();
    $('form[name="modalDeleteNotify"]').attr('action', $(this).attr('href'));
    $('#deleteModal').modal('show')
});