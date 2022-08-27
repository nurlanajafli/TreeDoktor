var InternalPayments = function() {
    const config = {
        ui: {},

        events: {
            paymentDetailsModal: '#payment_details',
            paymentRefundModal: '#payment_refund',
            paymentRefundForm: 'form#refund_payment_form',
        },

        route: {
            getTransactionDetails: '/internal_payments/ajax_get_transaction_details',
            refundPayment: '/internal_payments/ajax_refund_payment',
        },

        templates: {},

        views: {},

        images: {}
    };

    const _private = {
        init: function() {},

        handleError: function (response) {
            errorMessage(response.error);

            return false;
        },

        onHidePaymentDetails: function (event) {
            const detailsBlock = $(event.currentTarget).find('.trans-details');
            detailsBlock.removeClass('error').html('');
        },

        onShowPaymentDetails: function (event) {
            const data = {
                transaction_id: event.relatedTarget.dataset.transactionId
            };
            const detailsBlock = $(event.currentTarget).find('.trans-details');

            Common.request.send(
                config.route.getTransactionDetails,
                data,
                function (response) {
                    if (response.status === 'ok') {
                        detailsBlock.html(response.html);
                    } else {
                        detailsBlock.addClass('error').html(response.error);
                    }

                    return false;
                },
                _private.handleError,
                false
            );
        },

        onShowPaymentRefund: function (event) {
            if (event.target.id !== "payment_refund") {
                return false;
            }

            if (event.relatedTarget.length === 0) {
                $(event.currentTarget).modal('hide');
                errorMessage('Unknown error');

                return false;
            }

            const paymentId = event.relatedTarget.dataset.paymentId;
            const amount = Number(event.relatedTarget.dataset.amount);
            const refundForm = $(event.currentTarget).find(config.events.paymentRefundForm);
            refundForm.find('input#refund_payment_id').val(paymentId);
            refundForm.find('input#refund_payment_amount')
                .attr('max', amount)
                .val(amount)
                .parent()
                .siblings('label')
                .text(`Amount (max: ${amount.toLocaleString('en-US', {style: 'currency', currency: 'USD'})})`);
        },

        submitPaymentRefund: function (event) {
            const form = $(this);
            const params = form.serializeArray().reduce(function(obj, val) {
                obj[val.name] = val.value;
                return obj;
            }, {});

            const data = {
                params: params
            };

            Common.request.send(
                config.route.refundPayment,
                data,
                _private.handleRefundPayment,
                _private.handleError,
                true
            );

            event.preventDefault();
        },

        handleRefundPayment: function (response) {
            if (response.status === 'ok') {
                successMessage('Refund completed');
                $('#payment_refund').modal('hide');
                location.reload();

                return false;
            }

            const $form = $(config.events.paymentRefundForm);

            if (response.errors) {
                $.each(response.errors, function (key, val) {
                    if (val) {
                        $form.find('#' + key).parent().parent().addClass('error').addClass('has-error');
                        $form.find('#' + key).next().html(val);
                        $form.find('#' + key).next().addClass('text-danger');
                    }
                });
            }

            if (response.error) {
                $form.find('.form_error').html(response.error);
                $form.find('.form_error').show();
            }
        },

    };

    const pub = {
        init: function(){
            $(document).ready(function() {
                pub.events();
                _private.init();
            });
        },

        events: function() {
            $(document).on('hide.bs.modal', config.events.paymentDetailsModal, _private.onHidePaymentDetails);
            $(document).on('show.bs.modal', config.events.paymentDetailsModal, _private.onShowPaymentDetails);
            $(document).on('show.bs.modal', config.events.paymentRefundModal, _private.onShowPaymentRefund);
            $(document).on('submit', config.events.paymentRefundForm, _private.submitPaymentRefund);
        },

        helpers: {},
    };

    pub.init();
    return pub;
}();
