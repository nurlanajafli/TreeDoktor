function processCardForm(token) {
    var additional = {};
    $('.additional-form input, .additional-form select').each(function (index) {
        additional[this.name] = $(this).val();
    });
    var data = {
        "token": token,
        "crd_name": document.getElementById('crd_name').value,
        "client_id": document.getElementById('card-form').getAttribute('data-customer-id'),
        "additional": additional
    }

    console.log('data: ', data);

    $.post(baseUrl + 'clients/ajax_save_billing', data, function (resp) {
        if(resp.status == 'ok')
        {
            if($('#billing_details')){
                $('#billing_details').find('.modal-body .cards-info').html('').append(resp.html);
            }
            if($('#add_client_payment')) {
                var select = $('#add_client_payment').find('#cc_select');
                $('#add_client_payment').find('#cc_select option').remove();
                resp.cards.map(function (card) {
                    select.append('<option value="' + card.card_id + '">' + card.number + '</option>');
                });
            }
            if($('#change_estimate_status')){
                var select = $('#change_estimate_status').find('#cc_select');
                $('#change_estimate_status').find('#cc_select option').remove();
                resp.cards.map(function (card) {
                    select.append('<option value="' + card.card_id + '">' + card.number + '</option>');
                });
            }
            if($('#change_invoice_status')){
                var select = $('#change_invoice_status').find('#cc_select');
                $('#change_invoice_status').find('#cc_select option').remove();
                resp.cards.map(function (card) {
                    select.append('<option value="' + card.card_id + '">' + card.number + '</option>');
                });
            }
            if($('form.editableform')) {
                var select = $('form.editableform').find('#cc_select');
                $('form.editableform').find('#cc_select option').remove();
                resp.cards.map(function (card) {
                    select.append('<option value="' + card.card_id + '">' + card.number + '</option>');
                });
            }
            $('#card-form').modal('hide');
        } else {
            setFeedback(resp.error);
        }
        return true;
    }, 'json');
}

function setFeedback(message){
    var xMark = '\u2718';
    if(message) {
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