function processCardForm(token) {
    let additional = {};
    $('.additional-form input, .additional-form select').each(function (index) {
        additional[this.name] = $(this).val();
    });
    const data = {
        "token": token,
        "crd_name": $('#crd_name').val(),
        "additional": additional
    }

    $.post(baseUrl + 'internal_payments/ajax_save_billing', data, function (resp) {
        if (resp.status === 'ok') {
            const select = $('#order_subscription_modal').find('#cc_select');

            if (select) {
                $('#order_subscription_modal').find('#cc_select option').remove();

                resp.cards.map(function (card) {
                    select.append('<option value="' + card.card_id + '">' + card.number + '</option>');
                });
            }

            if ($('#cards_list_json').length) {
                $('#cards_list_json').val(JSON.stringify(resp.cards));
            }

            // reload page if card added in billing payment methods
            if (typeof billingAddCard !== 'undefined' && billingAddCard) {
                location.reload();
            }

            $('#card-form').modal('hide');
        } else {
            setFeedback(resp.error);
        }
        return true;
    }, 'json');
}

function setFeedback(message){
    const xMark = '\u2718';

    if (message) {
        $('#cc_form #feedback').html(xMark + ' ' + message).addClass('error');
        return;
    }

    $('#cc_form #feedback').html("").removeClass('error');

    return;
}

$(document).ready(function () {
    // var paymentDriver = $('#payment_driver').val();
    $('form#cc_form').on("submit", function (event) {
        //event.preventDefault();
    });
    $('#card-form').on('hide.bs.modal', function (event) {
        $('form#cc_form').trigger('reset');
        setFeedback();
    })
    $('#card-form').on('show.bs.modal', function (event) {
        $('form#cc_form').trigger('reset');
        setFeedback();
    })
});