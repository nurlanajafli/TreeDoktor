var Billing = function () {
    const config = {
        ui: {
            addCcCard: '#payment_add_cc_card',
            cardContentBlock: '#card-block',
            cardForm: '#card-form'
        },

        events: {},

        route: {
            getCardForm: '/internal_payments/ajax_get_card_form',
            deleteCard: '/billing/ajax_delete_card',
            setDefaultCard: '/billing/ajax_set_default_card',
        },
        templates: {}
    }

    const _private = {
        init: function () {
            Common.mask_currency($('.currency'));
        },

        getCardForm: function (event) {
            console.log('getCardForm event: ', event);
            if (event.currentTarget.id === 'payment_add_cc_card') {
                billingAddCard = true;
            }

            $(config.ui.addCcCard).addClass('disabled');

            const data = {
                type: 'int_payment'
            };

            Common.request.send(
                config.route.getCardForm,
                data,
                _private.handleGetCardForm,
                _private.handleError,
                false
            );
        },

        handleError: function (response) {
            errorMessage(response.error);

            if ($(config.ui.addCcCard)) {
                $(config.ui.addCcCard).removeClass('disabled');
            }

            return false;
        },

        handleGetCardForm: function (response) {
            if (response.status === 'error') {
                errorMessage(response.error + ' Please try again later');
                $(config.ui.addCcCard).removeClass('disabled');
            } else {
                $(config.ui.cardContentBlock).append(response.html);
                $(config.ui.cardForm).modal('show');
                $(config.ui.cardForm).on('hidden.bs.modal', function () {
                    $(config.ui.cardContentBlock).html('');
                    billingAddCard = false;
                });

                setTimeout(function () {
                    $(config.ui.addCcCard).removeClass('disabled');
                }, 100);
            }
        },

        deleteCard: function () {
            const parentBlock = $(this).closest('.payment-content');
            if (parentBlock.hasClass('is-default-card') && parentBlock.siblings('.payment-content').length) {
                errorMessage('You can\'t delete default card! Set as default another card first.');

                return false;
            }

            if (!confirm('Delete card. Are you sure?')) {
                return false;
            }

            const data = {
                card_id: $(this).data('cardId'),
            };

            Common.request.send(
                config.route.deleteCard,
                data,
                function (response) {
                    if (response.status === 'error') {
                        errorMessage(response.error || 'Delete card error');
                        return false;
                    }

                    if (!parentBlock.siblings('.payment-content').length) {
                        parentBlock.parent().append('<p>You have no payment methods.</p>');
                    }

                    $('#cardId-' + data.card_id).remove();
                },
                _private.handleError,
                false
            );
        },

        setDefaultCard: function () {
            const data = {
                card_id: $(this).data('cardId'),
            };

            Common.request.send(
                config.route.setDefaultCard,
                data,
                function (response) {
                    if (response.status === 'error') {
                        errorMessage(response.error || 'Set default card error');
                        return false;
                    }

                    const cardBlock = $('#cardId-' + data.card_id);

                    $('.payment-content').removeClass('is-default-card');
                    cardBlock.addClass('is-default-card');
                    cardBlock.prependTo(cardBlock.parent());

                    $('[data-toggle="tooltip"]').tooltip('hide');
                },
                _private.handleError,
                false
            );
        },

    };

    const pub = {
        init: function () {
            $(document).ready(function () {
                pub.events();
                _private.init();
            });
        },

        events: function () {
            $(document).on('click', config.ui.addCcCard, _private.getCardForm);
            $(document).on('click', '.delete-card', _private.deleteCard);
            $(document).on('click', 'a.set-default-card', _private.setDefaultCard);
        },

        getCardForm: function(event) {
            return _private.getCardForm(event);
        },

    };

    pub.init();
    return pub;
}();
