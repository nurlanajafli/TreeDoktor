var ClientInformationPaymentModal = function() {
    const config = {
        ui: {},

        events: {
            addClientPayment: 'form#add_client_payment',
            paymentMethod: '#payment_method',
            paymentAmount: '#payment_amount',
            deletePayment: '.deletePayment',
            editClientPaymentForm: 'form#edit_client_payment',
            refundClientPaymentForm: 'form#refund_client_payment',
            paymentRefund: '#payment_refund',
            editPayment: '#edit_payment',
            paymentDetails: '#payment_details',
        },

        route: {
            getBillingDetails: '/clients/ajax_get_billing_details',
            deletePayment: '/payments/ajax_delete_payment',
            editPayment: 'payments/ajax_edit_payment',
            refundPayment: 'payments/ajax_refund_payment',
            getPayment: '/payments/ajax_get_payment',
            getTransactionDetails: '/payments/ajax_get_transaction_details',
        },

        templates: {},

        views: {},

        images: {}
    };

    const _private = {
        init: function() {
            $('.datepicker').datepicker({format: $('#php-variable').val()});
            Common.mask_currency($('.currency'));
            $('#add_client_payment select[name="estimate_id"]').change();
        },

        addClientPayment: function (event) {
            event.preventDefault();

            const $form = $(this);
            $form.find('.form_error').hide();
            $form.find('.btntext').hide();
            $form.find('.preloader').show();
            $form.find('.preloader').parent().attr('disabled', 'disabled');

            const formData = new FormData(this);
            $.ajax({
                type: $form.attr('method'),
                url: baseUrl + 'payments/ajax_payment',
                data: formData,
                mimeType: "multipart/form-data",
                contentType: false,
                dataType: 'json',
                cache: false,
                processData: false,
                success: function (data, status) {
                    $form.find('.error .help-inline').html('');
                    $form.find('.error').removeClass('error').removeClass('has-error');

                    if (data.status === 'error') {
                        $form.find('.btntext').show();
                        $form.find('.preloader').hide();
                        $form.find('.preloader').parent().removeAttr('disabled');
                        if (data.errors) {
                            $.each(data.errors, function (key, val) {
                                if (val) {
                                    $form.find('#' + key).parent().parent().addClass('error').addClass('has-error');
                                    $form.find('#' + key).next().html(val);
                                    $form.find('#' + key).next().addClass('text-danger');
                                }
                            });
                        }
                        if (data.error) {
                            $form.find('.form_error').html(data.error);
                            $form.find('.form_error').show();
                        }
                        return false;
                    }

                    if (data.status === 'ok') {
                        $('#add_client_payment').modal('hide');
                        $('#new_payment').modal('hide');

                        if (data.thanks !== undefined && data.thanks != ''){
                            $('#email-template-form').find('input[name="estimate_id"]').val(data.thanks['estimate_id']);
                            ClientsLetters.init_modal(data.thanks['email_template_id'], 'ClientsLetters.invoice_email_modal');
                        } else {
                            location.reload();
                        }

                        if (data.message !== undefined && data.message !== '') {
                            successMessage(data.message);
                        }

                        return false;
                    }
                }
            });
            event.preventDefault();
        },

        changePaymentMethod: function () {
            const form = $(this).parents('form:first');

            if ($(this).val() === default_cc) {
                $(form).find('.file_block').hide();

                const data = {
                    client_id: $('#new_payment').attr('client-id')
                };

                Common.request.send(
                    config.route.getBillingDetails,
                    data,
                    function (response) {
                        if (response.status === 'ok') {
                            const select = $(form).find('#cc_select');
                            $(form).find('#cc_select option').remove();
                            response.cards.map(function (card) {
                                select.append('<option value="' + card.card_id + '">' + card.number + '</option>');
                            });
                        }
                    },
                    function (response) { return false; },
                    false
                );

                $(form).find('.credit_card').removeClass('hide');
                if (cc_extra_fee > 0) {
                    $(form).find('.with-fee').removeClass('hide');
                }
            } else {
                $(form).find('.credit_card').addClass('hide');
                $(form).find('.file_block').show();
                $(form).find('.with-fee').addClass('hide');
            }
        },

        changeExtraFee: function () {
            const amount = Common.getAmount($(this).val()) || 0;
            const with_fee = amount + (amount * (cc_extra_fee / 100));
            $(this).parents('.form-group').find('#with_fee').text(Common.money(with_fee.toFixed(2)));
        },

        deletePayment: function () {
            if (!isAdmin || !confirm('Are you sure to delete payment?')) {
                return false;
            }

            const obj = $(this);
            const data = {
                payment_id: this.dataset.paymentId
            };

            Common.request.send(
                config.route.deletePayment,
                data,
                function (response) {
                    if (response.status === 'ok') {
                        obj.parent().parent().remove();
                    } else {
                        errorMessage(response.error);
                    }
                },
                function (response) { return false; },
                false
            );
        },

        onShowPaymentRefund: function (e) {
            if (e.target.id !== "payment_refund") {
                return false;
            }
            if (e.relatedTarget.length === 0) {
                $(e.currentTarget).modal('hide');
                errorMessage('Unknown error');
                return false;
            }

            const paymentId = e.relatedTarget.dataset.paymentId;
            const amount = new Number(e.relatedTarget.dataset.amount);
            const fee = new Number(e.relatedTarget.dataset.fee);
            const refundForm = $(e.currentTarget).find(config.events.refundClientPaymentForm);

            refundForm.find('input#refund_payment_id').val(paymentId);
            refundForm.find('input#refund_payment_amount')
                .attr('max', amount)
                .val(amount)
                .parent()
                .siblings('label')
                .text(`Amount (max: ${amount.toLocaleString('en-US', {style: 'currency', currency: 'USD'})})`);

            refundForm.find('#refund_fee_amount')
                .text(fee.toLocaleString('en-US', {style: 'currency', currency: 'USD'}));
        },

        editClientPayment: function (event) {
            _private.handleClientPayment(this, config.events.editPayment, config.route.editPayment);
            event.preventDefault();
        },

        refundClientPayment: function (event) {
            _private.handleClientPayment(this, config.events.paymentRefund, config.route.refundPayment);
            event.preventDefault();
        },

        handleClientPayment: function ($this, selector, url) {
            $(selector + ' .btntext').hide();
            $(selector + ' .preloader').show();
            $(selector + ' .preloader').parent().attr('disabled', 'disabled');

            const $form = $($this);
            const formData = new FormData($this);

            $.ajax({
                type: $form.attr('method'),
                url: baseUrl + url,
                data: formData,
                mimeType: "multipart/form-data",
                contentType: false,
                dataType: 'json',
                cache: false,
                processData: false,
                success: function (data, status) {
                    $('.error .controls .help-inline').html('');
                    $('.control-group.error').removeClass('error');
                    $form.find('.btntext').show();
                    $form.find('.preloader').hide();
                    $form.find('.preloader').parent().removeAttr('disabled');
                    if (data.errors) {
                        $.each(data.errors, function (key, val) {
                            if (val) {
                                $form.find('#' + key).parent().parent().addClass('error').addClass('has-error');
                                $form.find('#' + key).next().html(val);
                                $form.find('#' + key).next().addClass('text-danger');
                            }
                        });
                    }
                    if (data.error) {
                        $form.find('.form_error').html(data.error);
                        $form.find('.form_error').show();
                    }
                    if (data.status === 'ok') {
                        $form.closest('.modal').modal('hide');
                        location.reload();
                    }
                }
            });
        },

        onShowEditPayment: function (event) {
            if (event.target.id !== "edit_payment") {
                return false;
            }
            if (event.relatedTarget.length === 0) {
                $(event.currentTarget).modal('hide');
                errorMessage('Unknown error');
                return false;
            }

            const data = {
                payment_id: event.relatedTarget.dataset.paymentId,
                same_estimates: 1
            }
            const editForm = $(event.currentTarget).find(config.events.editClientPaymentForm);

            Common.request.send(
                config.route.getPayment,
                data,
                function (response) {

                    if (response.status === 'ok') {
                        const payment = response.payment;
                        const updatePaymentDatePicker = document.getElementById('edit_payment_date');

                        if (payment.payment_method_int != default_cc) {
                            $(editForm).find('.non-cc-payments').show();
                        }
                        if (typeof response.estimates !== "undefined" && response.estimates !== null) {
                            $(editForm).find('.same-estimates #edit_payment_estimate').html('');
                            $.each(response.estimates, function(key, estimate) {
                                $(editForm).find('.same-estimates #edit_payment_estimate')
                                    .append($("<option></option>")
                                    .attr("selected", (estimate.estimate_id === payment.estimate_id ? 'selected' : false))
                                    .attr("value", estimate.estimate_id)
                                    .text(estimate.estimate_no));
                            });
                            $(editForm).find('.same-estimates').show();
                        }
                        $(editForm).find('#edit_payment_method').val(payment.payment_method_int);
                        $(editForm).find('#edit_payment_type').val(payment.payment_type);
                        $(editForm).find('#edit_payment_amount').val(payment.payment_amount);
                        updatePaymentDatePicker.value = payment.payment_date;
                        $(editForm).find('#edit_payment_id').val(data.payment_id)
                        $(editForm).find('#payment_notes').val(payment.payment_notes)
                    } else {
                        $(event.currentTarget).modal('hide');
                        errorMessage(response.error);
                        return false;
                    }
                },
                function (response) { return false; },
                false
            );
        },

        onHideEditPayment: function (event) {
            if (event.currentTarget.id === 'edit_payment') {
                const editForm = $(event.currentTarget).find(config.events.editClientPaymentForm);
                $(editForm).find('.non-cc-payments').hide();
                $(editForm).find('.same-estimates').hide();
                $(editForm).find('.same-estimates #edit_payment_estimate').empty();
            }
        },

        onHidePaymentDetails: function (event) {
            const detailsBlock = $(event.currentTarget).find('.trans-details');
            detailsBlock.removeClass('error').html('');
        },

        onShowPaymentDetails: function (event) {
            const detailsBlock = $(event.currentTarget).find('.trans-details');
            const data = {
                payment_id: event.relatedTarget.dataset.paymentId
            };

            Common.request.send(
                config.route.getTransactionDetails,
                data,
                function (response) {
                    if (response.status === 'ok') {
                        detailsBlock.html(response.html);
                    } else {
                        detailsBlock.addClass('error').html(response.error);
                    }
                },
                function (response) { return false; },
                false
            );
        },

        changePaymentType: function () {
            let selector = 'option[data-invoice]';

            if ($(this).val() === 'invoice') {
                selector = 'option[data-invoice="1"]';
            }

            $(this).parents('#add_client_payment:first').find('select[name="estimate_id"] ' + selector).removeAttr('disabled').show();
            $(this).parents('#add_client_payment:first').find('select[name="estimate_id"] option').not(selector).attr('disabled', 'disabled').hide();

            let selected = $(this).parents('#add_client_payment:first').find('select[name="estimate_id"] ' + selector + ':first').attr('value');

            if (typeof(estimate_id) !== 'undefined'
                    && $('#add_client_payment').find('select[name="estimate_id"] option[value="' + estimate_id + '"]').css('display') !== 'none'
                    && parseInt(estimate_id) == estimate_id) {
                selected = estimate_id;
            }

            $(this).parents('#add_client_payment:first').find('select[name="estimate_id"]').val(selected).change();
        },

        changeEstimateId: function () {
            $(this).parents('#add_client_payment:first')
                .find('input#payment_amount')
                .val($(this).find('option[value="' + $(this).val() + '"]').data('amount'))
                .keyup();
        },

        typeNumbersOnly: function (event) {
            if ((event.which < 48 || event.which > 57) && event.which !== 46) {
                event.preventDefault();
            }
        },
    };

    const pub = {
        init: function() {
            $(document).ready(function() {
                pub.events();
                _private.init();
            });
        },

        events: function() {
            $(document).on('submit', config.events.addClientPayment, _private.addClientPayment);
            $(document).on('change', config.events.paymentMethod, _private.changePaymentMethod);
            $(document).on('show.bs.modal', config.events.editPayment, _private.onShowEditPayment);
            $(document).on('hidden.bs.modal', config.events.editPayment, _private.onHideEditPayment);
            $(document).on('show.bs.modal', config.events.paymentDetails, _private.onShowPaymentDetails);
            $(document).on('hide.bs.modal', config.events.paymentDetails, _private.onHidePaymentDetails);
            $(document).on('change', '#add_client_payment #payment_type', _private.changePaymentType);
            $(document).on('change', '#add_client_payment select[name="estimate_id"]', _private.changeEstimateId);
            $(document).on('keypress keyup blur', '#payment_amount, #edit_payment_amount', _private.typeNumbersOnly);

            if (cc_extra_fee > 0) {
                $(document).on('keyup', config.events.paymentAmount, _private.changeExtraFee);
            }

            if (isAdmin) {
                $(document).on('click', config.events.deletePayment, _private.deletePayment);
            }

            if (isAdmin || isPME) {
                $(document).on('submit', config.events.editClientPaymentForm, _private.editClientPayment);
                $(document).on('show.bs.modal', config.events.paymentRefund, _private.onShowPaymentRefund);
                $(document).on('submit', config.events.refundClientPaymentForm, _private.refundClientPayment);
            }
        },

        helpers: {},
    };

    pub.init();
    return pub;
}();
