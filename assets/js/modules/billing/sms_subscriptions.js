var SmsSubscriptions = function () {
    const config = {
        ui: {
            orderModal: '#order_subscription_modal',
            createModal: '#create_subscription_modal',
            deleteModal: '#delete_order_subscription_modal',
            addCcCard: '#add_cc_card',
        },

        events: {
            orderSubBtn: '.order_sub_btn',
            orderForm: '#add_order_subscription',
            createForm: '#create_subscription',
            usePeriod: 'select.sub_use_period',
            createUpdateForm: 'form.create-update-subscription-form',
            createUpdateModal: '.create-update-subscription-modal',
            deleteFreeBtn: 'a.delete-free-order',
            autoRenewalCheckboxes: '.sub_auto_renewal',
            deleteSmsSubscription: 'a.delete-sms-subscription',
            addFreeOrderBtn: '.add-free-order-btn',
        },

        route: {
            orderSubscription: '/billing/ajax_sms_order_subscription',
            updateOrderSubscription: '/billing/ajax_update_sms_order_subscription',
            payOrderSubscription: '/billing/ajax_pay_sms_active_order_subscription',
            createSmsSubscription: '/billing/ajax_create_sms_subscription',
            updateSmsSubscription: '/billing/ajax_update_sms_subscription',
            deleteFreeOrder: '/billing/ajax_delete_free_order',
            deleteFreeSubscription: '/billing/ajax_delete_free_subscription',
            deleteSmsSubscription: '/billing/ajax_delete_sms_subscription',
        },
        templates: {}
    }

    const _private = {
        init: function () {
            _private.initUpdateCheckboxes();
            _private.clearModalFormData();
        },

        initUpdateCheckboxes: function () {
            if (!isSystemUser) {
                return false;
            }

            if ($('input[type="checkbox"]').length) {
                $('input[type="checkbox"]').each(function (i, val) {
                    if ($(val).attr('checked')) {
                        $(val).bootstrapToggle('on');
                    }
                });
            }
        },

        clearModalFormData: function () {
            const modal = $(config.ui.orderModal);
            modal.find('.with-order-create').hide();
            modal.find('.with-order-update').hide();
            modal.find('.modalTitle').text('');
            modal.find('input[type="checkbox"]').removeAttr('checked').prop('checked', false).bootstrapToggle('off');
            modal.find(config.events.usePeriod).val('').closest('.form-group').removeClass('has-error');
            modal.find('input.modal_action_id').val('');
            modal.find('input.modal_action').val('');
            modal.find('input.sub_on_period').val('');
            modal.find('input.sub_on_out_limit').val('');
            modal.find('.submitModalBtn > span').text('');
            modal.find('#cc_select option').remove();
        },

        onShowOrderSub: function (event) {
            const modal = $(this);
            const data = event.relatedTarget.dataset;
            let modalTitle = data.type === 'package' ? 'Buy now' : 'Order new subscription';
            let submitModalBtn = 'Order';
            let forUpdate;

            (modal.find('form'))[0].reset();

            if (data.action === 'create') {
                forUpdate = {
                    name: data.name,
                    count: data.count,
                    amount: data.amount,
                    period: data.period,
                    use_period: data.usePeriod,
                    description: data.description
                };
                modal.find('.with-order-create').show();
            } else {
                modalTitle = 'Edit subscription';
                submitModalBtn = 'Update';

                if (data.action === 'pay') {
                    modalTitle = 'Pay for subscription';
                    submitModalBtn = 'Pay';
                }

                forUpdate = {
                    name: data.name,
                    count: data.count,
                    amount: data.amount,
                    description: data.description,
                };
                modal.find('.with-order-update').show();

                if (data.from) {
                    forUpdate.from = data.from;
                } else {
                    modal.find('.sms-sub-from').parent().hide();
                }

                if (data.to) {
                    forUpdate.to = data.to;
                } else {
                    modal.find('.sms-sub-to').parent().hide();
                }
            }

            if (data.type === 'free') {
                modal.find('.payment-option').hide();
                delete forUpdate.count;
                modal.find('.sms-sub-count').append('<input class="form-control" type="number" name="count" required value="' + data.count + '">');
            } else {
                modal.find('.payment-option').show();
                const cards = $('#cards_list_json').val() ? JSON.parse($('#cards_list_json').val()) : [];

                if (cards.length) {
                    const select = modal.find('#cc_select');
                    modal.find('#cc_select option').remove();
                    cards.map(function (card) {
                        select.append('<option value="' + card.card_id + '"' + (data.defaultCardId === card.card_id ? ' selected' : '') + '>' + card.number + '</option>');
                    });
                }
            }

            $.each(forUpdate, function (key, val) {
                modal.find('.sms-sub-' + key).text(val);
            });

            const descriptionBlock = modal.find('.description-block');

            if (data.description) {
                descriptionBlock.removeClass('hidden');
            } else {
                descriptionBlock.addClass('hidden');
            }

            if (data.action !== 'pay') {
                const usePeriodInput = modal.find('input.sub_use_period');

                if (data.type === 'period') {
                    usePeriodInput.val('next');
                    modal.find('input.sub_on_period').val(1);
                } else {
                    if (data.type === 'limit') {
                        modal.find('input.sub_on_out_limit').val(1);
                    }
                    usePeriodInput.val('current');
                }
            }

            modal.find('.modalTitle').text(modalTitle);
            modal.find('input.modal_action_id').val(data.id);
            modal.find('input.modal_action').val(data.action);
            modal.find('.submitModalBtn > span').text(submitModalBtn);
        },

        onHiddenOrderSubModal: function () {
            // clear modal data
            const modal = $(this);
            const forUpdate = [
                'name',
                'count',
                'amount',
                'period',
                'remain',
                'from',
                'to',
                'description'
            ];

            $.each(forUpdate, function (key, val) {
                modal.find('.sms-sub-' + val).text('');
            });

            _private.clearModalFormData();
        },

        submitOrderForm: function (event) {
            event.preventDefault();

            const form = $(this);
            const data = form.serializeArray().reduce(function(obj, val) {
                obj[val.name] = val.value;
                return obj;
            }, {});

            let route = config.route.updateOrderSubscription;

            if (data.action === 'create') {
                route = config.route.orderSubscription;

                if (data.use_period === '') {
                    form.find(config.events.usePeriod).closest('.form-group').addClass('has-error');
                    return false;
                }
            } else if (data.action === 'pay') {
                route = config.route.payOrderSubscription;
            }

            Common.request.send(
                route,
                data,
                _private.handleResponse,
                _private.handleError,
                true
            );
        },

        onShowCreateUpdateModal: function () {
            const modal = $(this);
            (modal.find('form'))[0].reset();
            modal.find('input.add-free-order').val(0);
        },

        submitCreateUpdateForm: function (event) {
            if (!isSystemUser) {
                return false;
            }

            event.preventDefault();

            const form = $(this);
            const params = form.serializeArray().reduce(function(obj, val) {
                obj[val.name] = val.value;
                return obj;
            }, {});

            const data = {
                params: params
            };

            let route = config.route.updateSmsSubscription;

            if (params.action === 'create') {
                route = config.route.createSmsSubscription;
            }

            Common.request.send(
                route,
                data,
                _private.handleResponse,
                _private.handleError,
                false
            );
        },

        handleResponse: function (response) {
            if (response.status !== 'ok') {
                errorMessage(response.error);

                return false;
            }

            let msg = 'Subscription updated';

            if (response.action !== 'delete') {
                $(config.ui.orderModal).modal('hide');
                $('.create-update-subscription-modal').modal('hide');

                if (response.action === 'create') {
                    msg = 'Subscription created';
                } else if (response.action === 'pay') {
                    msg = 'Subscription paid';
                }
            } else {
                $(config.ui.deleteModal).modal('hide');
                msg = 'Deleted';
            }

            successMessage(msg);

            setTimeout(function () {
                location.reload();
            }, 500);
        },

        deleteFreeOrderSubscription: function () {
            const id = $(this).data('id');
            const type = $(this).data('type');

            if (!id || !isSystemUser || !confirm('Are you sure?')) {
                return false;
            }
            
            let route;
            
            if (type === 'order') {
                route = config.route.deleteFreeOrder;
            }
            else if (type === 'subscription') {
                route = config.route.deleteFreeSubscription;
            }
            else {
                return false;
            }

            const data = {
                id: id
            }

            Common.request.send(
                route,
                data,
                _private.handleResponse,
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

        onChangeCheckboxes: function () {
            let checked = false;

            $(config.events.autoRenewalCheckboxes).each(function (i, val) {
                if ($(val).prop('checked')) {
                    checked = true;
                }
            });

            const cardInfo = $('span.card_info');

            if (checked) {
                cardInfo.removeClass('hidden');
            } else {
                cardInfo.addClass('hidden');
            }
        },

        deleteSmsSubscription: function () {
            if (confirm('Are you sure?')) {
                const data = {
                    order_id: $(this).data('orderId')
                };

                Common.request.send(
                    config.route.deleteSmsSubscription,
                    data,
                    _private.handleResponse,
                    _private.handleError,
                    false
                );
            }

            return false;
        },

        addFreeOrder: function () {
            const btn = $(this);
            btn.siblings('input.add-free-order').val(1);
            // btn.closest(config.events.createUpdateForm).submit();
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
            $(document).on('show.bs.modal', config.ui.orderModal, _private.onShowOrderSub);
            $(document).on('hidden.bs.modal', config.ui.orderModal, _private.onHiddenOrderSubModal);
            $(document).on('submit', config.events.orderForm, _private.submitOrderForm);
            $(document).on('change', config.events.usePeriod, function() {
                $(this).closest('.form-group').removeClass('has-error');
            });
            $(document).on('click', config.ui.addCcCard, Billing.getCardForm);
            $(document).on('click', config.events.deleteSmsSubscription, _private.deleteSmsSubscription);
            $(document).on('change', config.events.autoRenewalCheckboxes, _private.onChangeCheckboxes);

            if (isSystemUser) {
                $(document).on('submit', config.events.createUpdateForm, _private.submitCreateUpdateForm);
                $(document).on('show.bs.modal', config.events.createUpdateModal, _private.onShowCreateUpdateModal);
                $(document).on('click', config.events.deleteFreeBtn, _private.deleteFreeOrderSubscription);
                $(document).on('click', config.events.addFreeOrderBtn, _private.addFreeOrder);
            }
        },

    };

    pub.init();
    return pub;
}();
