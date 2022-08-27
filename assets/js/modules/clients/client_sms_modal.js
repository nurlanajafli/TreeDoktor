$(document).ready(function(){
    $(document).on('click', '.addSMS', function() {
        const obj = $(this);
        $(obj).attr('disabled', 'disabled');
        const sms = $(obj).parents('.modal-content:first').find('textarea.sms_text').val();
        const number = $(obj).parents('.modal-content:first').find('input.client_number').val();

        $.post(baseUrl + 'client_calls/send_sms_to_client', { PhoneNumber: number, sms: sms }, function (resp) {
            $(obj).removeAttr('disabled');

            if (resp.status !== 'ok') {
                if (resp.messages) {
                    $.each(resp.messages, function(key, message) {
                        errorMessage(message || 'Unexpected error. Please try later.');
                    });
                } else {
                    errorMessage(resp.message || 'Unexpected error. Please try later.');
                }

                return false;
            }

            successMessage('SMS was sent. Thanks');
            $(obj).parents('.modal:first').modal('hide');

            if ($(obj).data('reload') !== false) {
                location.reload();
            }

            return false;
        }, 'json');
        return false;
    });
});
