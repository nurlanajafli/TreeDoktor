(function ($) {
    "use strict";

    var EstimatesStatus = function (options) {
        this.init('estimates_status', options, EstimatesStatus.defaults);
    };

    $.fn.editableutils.inherit(EstimatesStatus, $.fn.editabletypes.list);

    $.extend(EstimatesStatus.prototype, {

        value2htmlFinal: function (value, element) {

            var text = '',
                items = $.fn.editableutils.itemsByValue(value, this.sourceData);

            if (items.length) {
                text = items[0].text;
            }

            $.fn.editabletypes.abstractinput.prototype.value2html.call(this, text, element);
        },

        autosubmit: function () {
            this.$input.off('keydown.editable').on('change.editable', function () {
                $(this).closest('form').submit();
            });
        },

        estimateStatuses: [],
        estimateDeclineStatus: null,
        estimateConfirmedStatus: null,
        getEstimateStatuses: function () {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                global: false,
                type: 'POST',
                url: baseUrl + 'estimates/ajax_get_estimate_statuses',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        estimateReasons: [],
        getEstimateReasons: function () {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                global: false,
                type: 'POST',
                url: baseUrl + 'estimates/ajax_get_estimate_reasons',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        paymentOpts: [],
        getPaymentOpts: function () {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                global: false,
                type: 'POST',
                url: baseUrl + 'invoices/ajax_get_payment_methods',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        getBillingDetails: function (data) {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                data: data,
                global: false,
                type: 'POST',
                url: baseUrl + 'clients/ajax_get_billing_details',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        getAnotherPayments: function (id) {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                data: {estimate_id: id},
                global: false,
                type: 'POST',
                url: baseUrl + 'estimates/ajax_get_estimate_payments',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        getCards: function (id) {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                data: {client_id: id},
                global: false,
                type: 'POST',
                url: baseUrl + 'clients/ajax_get_billing_details',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        getCardForm: function (id) {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                data: {client_id: id},
                global: false,
                type: 'POST',
                url: baseUrl + 'payments/ajax_get_card_form',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        render: function () {
            var deferred = $.Deferred();
            this.value = this.options.scope.dataset.value;
            this.pk = this.options.scope.dataset.pk;
            this.name = this.options.scope.dataset.name;
            this.client_id = this.options.scope.dataset.client_id;
            this.payment_driver = this.options.scope.dataset.payment_driver;
            this.client_name = this.options.scope.dataset.client_name;
            if (!this.paymentOpts.length)
                this.paymentOpts = this.getPaymentOpts();

            this.estimate_id = this.options.scope.dataset.estimate_id;
            this.$form = this.$tpl.parents('form:first');
            this.$statusSelect = this.$tpl.find('select[name=new_estimate_status]');
            if (!this.estimateStatuses.length)
                this.estimateStatuses = this.getEstimateStatuses();

            this.$reasonSelect = this.$tpl.find('select[name=reason]');
            this.$anotherPayments = this.$tpl.find('div.another-payments');
            this.$paymentMethod = this.$tpl.find('select[name=method]');
            this.$cardsSelect = this.$tpl.find('select[name=cc_id]');
            this.$deposit = this.$tpl.find('input[name=wo_deposit]');
            this.$preSatus = this.$tpl.find('input[name=pre_estimate_status]');
            this.$wo_confirm_how = this.$tpl.find('input[name=wo_confirm_how]');
            this.$wo_scheduling_preference = this.$tpl.find('input[name=wo_scheduling_preference]');
            this.$wo_extra_not_crew = this.$tpl.find('input[name=wo_extra_not_crew]');
            this.$file = this.$tpl.find('input[name=file]');
            this.$statusSelect.empty();
            this.$reasonSelect.empty();
            this.$paymentMethod.empty();
            this.$deposit.empty();
            this.$preSatus.empty();
            this.$cardsSelect.empty();
            this.$wo_confirm_how.empty();
            this.$preSatus.val(this.value);
            this.$form.find('input[name=estimate_id]').val(this.estimate_id);

            var self = this;

            var fillStatuses = function ($el, data) {
                if ($.isArray(data)) {
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].est_status_declined == "1")
                            self.estimateDeclineStatus = data[i].est_status_id;
                        if (data[i].est_status_confirmed == "1")
                            self.estimateConfirmedStatus = data[i].est_status_id;
                        $el.append($('<option>', {
                            value: data[i].est_status_id,
                        }).text(data[i].est_status_name));
                    }
                }
                return $el;
            };

            fillStatuses(this.$statusSelect, this.estimateStatuses);

            var fillReasons = function ($el, data) {
                if ($.isArray(data)) {
                    for (var i = 0; i < data.length; i++) {
                        $el.append($('<option>', {
                            value: data[i].reason_id
                        }).text(data[i].reason_name));
                    }
                }
                return $el;
            };

            var fillAnotherPayments = function ($el, data) {
                if ($.isArray(data)) {
                    var block = '';
                    for (var i = 0; i < data.length; i++) {
                        block += '<input type="radio" class="payment" name="payment_id" style="margin-top:-2px;" value="' + data[i].payment_id + '" checked="checked">';
                        block += '<span style="margin-left: 10px;">Payment - ' + new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD'
                        }).format(data[i].payment_amount) + '</span>';
                        block += '<br>';
                    }
                    $el.prepend(block);
                }
                $el.find('input').first().attr('checked', 'checked');
                self.$tpl.find('input[name=payment_id]').trigger('change');
                return $el;
            };

            var fillCards = function ($el, data) {
                if (data.cards && $.isArray(data.cards)) {
                    var cards = data.cards;
                    for (var i = 0; i < cards.length; i++) {
                        $el.append('<option value="'+ cards[i].card_id+'">'+ cards[i].number+'</option>');
                    }
                }
                return $el;
            };
            deferred.resolve();
            this.error = null;

            // this.onSourceReady(function () {
            //     console.log(this.sourceData);
            //     //fillItemsReasons(this.$reasonSelect, this.sourceData[0].text);
            //     deferred.resolve();
            // }, function () {
            //     this.error = this.options.sourceError;
            //     deferred.resolve();
            // });
            //
            // this.onSourceReady(function () {
            //     //fillItems(this.$statusSelect, this.sourceData[1].text);
            //     deferred.resolve();
            // }, function () {
            //     this.error = this.options.sourceError;
            //     deferred.resolve();
            // });

            $(this.$statusSelect).on('change', function () {
                var value = $(this).val();
                if (value == self.estimateConfirmedStatus) {
                    fillAnotherPayments(self.$anotherPayments, self.getAnotherPayments(self.estimate_id));
                    fillCards(self.$cardsSelect, self.getCards(self.client_id));
                    self.$form.find('.confirmed_block').show(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.fu_cc_block').hide(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.declined_block').hide(10, function () {
                        Popup.setPosition();
                    });
                    if (Object.keys(self.paymentOpts.methods).length) {
                        //self.$form.find('.fu_pm').find('option').remove();
                        $.each(self.paymentOpts.methods, function (key, val) {
                            self.$form.find('.fu_pm').append('<option value="' + key + '">' + val + '</option>');
                        });
                    }

                } else if (value == self.estimateDeclineStatus) {

                    if (!self.estimateReasons.length)
                        self.estimateReasons = self.getEstimateReasons();
                    fillReasons(self.$reasonSelect, self.estimateReasons);

                    self.$form.find('.confirmed_block').hide(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.fu_cc_block').hide(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.declined_block').show(10, function () {
                        Popup.setPosition();
                    });
                } else {
                    self.$form.find('.confirmed_block').hide(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.fu_cc_block').hide(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.declined_block').hide(10, function () {
                        Popup.setPosition();
                    });
                }
                setTimeout(function () {
                    Popup.setPosition()
                }, 100);
            });

            $(this.$paymentMethod).on('change', function () {
                var value = $(this).val();

                if (value == default_cc) {
                    self.$form.find('.file_block').hide(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.credit_card').append(self.getCardForm(self.client_id));
                    self.$form.find('.credit_card').show(10, function () {
                        Popup.setPosition();
                    });
                } else {
                    self.$form.find('.file_block').show(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.credit_card').hide(10, function () {
                        Popup.setPosition();
                    });
                }
                setTimeout(function () {
                    Popup.setPosition()
                }, 100);
            });

            $(this.$form).on('change','input[name=payment_id]', function () {
                var value = self.$tpl.find('input[name=payment_id]:checked').val();

                if (value == "0") {
                    self.$deposit.prop('disabled',false );
                    self.$file.prop('disabled',false );
                    self.$file.parent().removeAttr('disabled');
                } else {
                    self.$deposit.prop('disabled',true );
                    self.$file.prop('disabled',true );
                    self.$file.parent().attr('disabled','disabled');
                }
                setTimeout(function () {
                    Popup.setPosition()
                }, 100);
            });
            return deferred.promise();
        },

        /**
         Converts value to string.
         It is used in internal comparing (not for sending to server).

         @method value2str(value)
         **/
        value2str: function (value) {
            var str = '';
            if (value) {
                for (var k in value) {
                    str = str + k + ':' + value[k] + ';';
                }
            }
            return str;
        },
        /*
         Converts string to value. Used for reading value from 'data-value' attribute.

         @method str2value(str)
         */
        str2value: function (str) {
            /*
             this is mainly for parsing value defined in data-value attribute.
             If you will always set value by javascript, no need to overwrite it
             */
            return str;
        },

        /*
         Converts string to value. Used for reading value from 'data-value' attribute.

         @method str2value(str)
         */

        value2input: function (value) {
            if (!value)
                return false;

            this.$statusSelect.val(value);
            this.$preSatus.val(value);
        },
        /**
         Returns value of input.

         @method input2value()
         **/
        input2value: function () {
            return {
                estimate_id: this.$form.find('input[name=estimate_id]').val(),
                new_estimate_status: this.$statusSelect.val(),
                wo_confirm_how: this.$wo_confirm_how.val(),
                wo_scheduling_preference: this.$wo_scheduling_preference.val(),
                wo_extra_not_crew: this.$wo_extra_not_crew.val(),
                pre_estimate_status: this.$preSatus.val(),
                wo_deposit: this.$deposit.val(),
                method: this.$paymentMethod.val(),
                reason: this.$reasonSelect.val(),
                payment_id: this.$form.find('input[name=payment_id]:checked').val()
            };
        },
        /**
         Activates input: sets focus on the first field.

         @method activate()
         **/
        activate: function () {

            $(this.options.scope).editable('option', 'ajaxOptions', {
                dataType: 'json',
                contentType: false,
                processData: false,
                type: 'POST'
            });
            var self = this;
            $(this.options.scope).editable('option', 'params', function (p) {
                var d = new FormData(self.$form[0]);
                d.append('pk', self.pk);
                d.append('name', self.name);
                return d;
            });

            this.$statusSelect.focus();
        },

        html2value: function (html) {
            return null;
        },

        value2html: function (value, element) {

        }
    });

    EstimatesStatus.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {
        tpl: ' <div class="form-group">\
                    <label class="control-label">Estimate Status:</label>\
                    <div>\
                        <select name="new_estimate_status" id="sel_estimate_status" class="form-control">\
                        </select>\
                        <span class="help-inline"></span>\
                    </div>\
                </div>\
                <div class="declined_block form-group m-t-xs" style="display:none;">\
                    <label class="control-label">Select Reason</label>\
                    <div>\
                        <select class="form-control" name="reason">\
                        </select>\
                        <span class="help-inline"></span>\
                    </div>\
                </div>\
                <div class="confirmed_block m-t-xs" style="display:none;">\
                    <span>\
                        <div class="form-group">\
                            <label class="control-label">Confirmed how:</label>\
                            <div>\
                                <input type="text" name="wo_confirm_how" id="wo_confirm_how" class="form-control" placeholder="Over the phone, in person, by email etc.">\
                                <span class="help-inline"></span>\
                            </div>\
                        </div>\
                        <div class="form-group">\
                            <label class="control-label">Select payment:</label>\
                            <div class="another-payments">\
                                <input type="radio" name="payment_id" class="newpayment" style="margin-top:-2px;" value="0">\
                                <span style="margin-left: 10px;">New Payment</span><br>\
                                <span class="help-inline"></span>\
                            </div>\
                        </div>\
                        <div class="form-group">\
                            <label class="control-label">Deposit taken by:</label>\
                            <div>\
                                <select name="method" id="payment_method_status" class="form-control fu_pm">\
                                    <option value=""> -- Select -- </option>\
                                </select>\
                                <span class="help-inline"></span>\
                            </div>\
                        </div>\
                        <div class="form-group credit_card" style="display: none;">\
                            <label class="control-label">Payment Card:</label>\
                            <div>\
                                <select id="cc_select" name="cc_id" class="form-control">\
                                </select>\
                                <span class="help-inline"></span>\
                                <a href="#card-form" id="add_cc_card" class="pull-right" data-toggle="modal">Add card</a>\
                            </div>\
                        </div>\
                        <div class="form-group">\
                                <label class="control-label">Amount:</label>\
                                <div class="input-group">\
                                    <span class="input-group-addon">'+Common.get_currency()+'</span>\
                                    <input type="text" class="form-control" name="wo_deposit"\
                                           id="appendedPrependedInput_wo_deposit" type="text" disabled>\
                                </div>\
                                <span class="help-inline"></span>\
                            </div>\
                        <span class="validate-error"></span>\
                        <br>\
                        <div class="controls file_block">\
                            <span class="btn btn-primary btn-file" id="file">Choose File\
                                <input type="file" name="payment_file" id="paymentFileToUpload"\
                                       class="btn-upload" disabled>\
                            </span>\
                            <span class="help-inline"></span>\
                        </div>\
                        <div class="form-group">\
                            <label class="control-label">Scheduling preferences:</label>\
                            <div>\
                                <input type="text" name="wo_scheduling_preference" id="wo_scheduling_preference" class="form-control" placeholder="Example only on weekends, within 2 weeks, in winter etc.">\
                                <span class="help-inline"></span>\
                            </div>\
                        </div>\
                        <div class="form-group">\
                            <label class="control-label">Extra note for crew:</label>\
                            <div>\
                                <textarea name="wo_extra_not_crew" cols="40" rows="1" id="wo_extra_not_crew" class="form-control" placeholder="Extra note for crew"></textarea>\
                                <span class="help-inline"></span>\
                            </div>\
                        </div>\
                       <input type="hidden" name="pre_estimate_status" id="pre_estimate_status" class="form-control">\
                       <input type="hidden" name="estimate_id" id="estimate_id" class="form-control">\
                    </span>\
                </div>',
        inputclass: '',
        source: []
    });

    String.prototype.capitalize = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }

    String.prototype.stripSlashes = function () {
        return this.replace(/\\(.)/mg, "$1");
    }

    $.fn.editabletypes.estimates_status = EstimatesStatus;

}(window.jQuery));
