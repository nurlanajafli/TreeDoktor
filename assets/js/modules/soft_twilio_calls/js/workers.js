$(document).on('click', '.createNewWorker', function(e) {
   e.preventDefault();
   if (!$(this).data('available_users')) {
       errorMessage('You do not have twilio available users');
       return false;
   }
   location.href = $(this).attr('href');
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
$('a.delete-worker').click(function (event) {
    event.preventDefault();
    $('form[name="modalDeleteNotify"]').attr('action', $(this).attr('href'));
    $('#deleteModal').modal('show')
});

$(document).on('click', '.open_attributes_textarea_block', function() {
    $(this).removeClass('open_attributes_textarea_block fa fa-plus-square');
    $(this).addClass('close_attributes_textarea_block fa fa-minus-square');
    $('.attributes_textarea_block').removeClass('hidden');
});

$(document).on('click', '.close_attributes_textarea_block', function() {
    $(this).removeClass('close_attributes_textarea_block fa fa-minus-square');
    $(this).addClass('open_attributes_textarea_block fa fa-plus-square');
    $('.attributes_textarea_block').addClass('hidden');
});