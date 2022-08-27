(function ($) {
    "use strict";

    var InvoicesStatus = function (options) {
        this.init('invoices_status', options, InvoicesStatus.defaults);
    };

    $.fn.editableutils.inherit(InvoicesStatus, $.fn.editabletypes.list);

    $.extend(InvoicesStatus.prototype, {

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

        invoiceStatuses: [],
        invoicePaidStatus: null,
        getInvoiceStatuses: function () {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                global: false,
                type: 'POST',
                url: baseUrl + 'invoices/ajax_get_invoice_statuses',
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
            if (!this.paymentOpts.length)
                this.paymentOpts = this.getPaymentOpts();

            this.value = this.options.scope.dataset.value;
            this.pk = this.options.scope.dataset.pk;
            this.name = this.options.scope.dataset.name;
            this.client_id = this.options.scope.dataset.client_id;
            this.estimate_id = this.options.scope.dataset.estimate_id;
            this.invoice_id = this.options.scope.dataset.fu_item_id;
            this.estimate_balance = this.options.scope.dataset.estimate_balance;
            this.client_unsubscribed = this.options.scope.dataset.client_unsubscribed;
            this.$form = this.$tpl.parents('form:first');
            this.$statusSelect = this.$tpl.find('select[name=new_invoice_status]');
            if (!this.invoiceStatuses.length)
                this.invoiceStatuses = this.getInvoiceStatuses();
            this.$preSatus = this.$tpl.find('input[name=pre_invoice_status]');
            this.$paymentMethod = this.$tpl.find('select[name=method]');
            this.$cardsSelect = this.$tpl.find('select[name=cc_id]');
            this.$deposit = this.$tpl.find('input[name=payment_amount]');
            this.$file = this.$tpl.find('input[name=file]');
            this.$statusSelect.empty();
            this.$paymentMethod.empty();
            this.$deposit.empty();
            this.$cardsSelect.empty();
            this.$preSatus.val(this.value);
            //this.$form.find('.fuPk').val(this.pk);
            this.$form.find('input[name=estimate_id]').val(this.estimate_id);
            this.$form.find('input[name=invoice_id]').val(this.invoice_id);
            this.$form.find('input[name=client_unsubscribed]').val(this.client_unsubscribed);

            var self = this;

            var fillStatuses = function ($el, data) {
                if ($.isArray(data)) {
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].completed == "1")
                            self.invoicePaidStatus = data[i].invoice_status_id;
                        $el.append($('<option>', {
                            value: data[i].invoice_status_id,
                        }).text(data[i].invoice_status_name));
                    }
                }
                return $el;
            };

            fillStatuses(this.$statusSelect, this.invoiceStatuses);

            var fillCards = function ($el, data) {
                if (data.cards && $.isArray(data.cards)) {
                    var cards = data.cards;
                    for (var i = 0; i < cards.length; i++) {
                        $el.append('<option value="' + cards[i].card_id + '">' + cards[i].number + '</option>');
                    }
                }
                return $el;
            };

            deferred.resolve();
            this.error = null;

            // this.onSourceReady(function () {
            //     fillItems(this.$statusSelect, this.sourceData[1].text);
            //     deferred.resolve();
            // }, function () {
            //     this.error = this.options.sourceError;
            //     deferred.resolve();
            // });

            $(this.$statusSelect).on('change', function () {
                var value = $(this).val();
                if (value == self.invoicePaidStatus) {
                    fillCards(self.$cardsSelect, self.getCards(self.client_id));
                    self.$form.find('input[name=payment_amount]').val(self.estimate_balance);
                    self.$form.find('.paid_block').show(1, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.fu_cc_block').hide(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.fu_cc_block').html(ccTpls['tpl_' + self.client_id]);

                    if (Object.keys(self.paymentOpts.methods).length)
                        //self.$form.find('.fu_pm').find('option').remove();
                    $.each(self.paymentOpts.methods, function (key, val) {
                        self.$form.find('.fu_pm').append('<option value="' + key + '">' + val + '</option>');
                    });
                } else {
                    self.$form.find('.paid_block').hide(1, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.fu_cc_block').hide(10, function () {
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
                    self.$form.find('.credit_card').hide(10, function () {
                        Popup.setPosition();
                    });
                    self.$form.find('.file_block').show(10, function () {
                        Popup.setPosition();
                    });
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
            var Status = this.$statusSelect.val();
        },
        /**
         Returns value of input.

         @method input2value()
         **/
        input2value: function () {
            return {
                new_invoice_status: this.$statusSelect.val(),
                pre_invoice_status: this.$preSatus.val(),
                payment_amount: this.$deposit.val(),
                payment_method: this.$paymentMethod.val()
            };
        },
        /**
         Activates input: sets focus on the first field.

         @method activate()
         **/
        activate: function () {
            var url = this.options.scope.dataset.url;

            if (this.$statusSelect.val() == 4)
                url = baseUrl + 'clients/ajax_update_followup_item_status_paid_invoice';

            $(this.options.scope).editable('option', 'ajaxOptions', {
                dataType: 'json',
                contentType: false,
                processData: false,
                type: 'POST',
                //url: url
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

        },

    });

    InvoicesStatus.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {
        tpl: ' <div class="form-group">\
                    <label class="control-label">Invoice Status:</label>\
                    <div>\
                        <select name="new_invoice_status" id="sel_invoice_status" class="form-control">\
                        </select>\
                        <span class="help-inline"></span>\
                    </div>\
                </div>\
                <div class="paid_block m-t-xs" style="display:none;">\
                    <span>\
                        <div class="form-group">\
                            <label class="control-label">Payment taken by:</label>\
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
                                    <input type="text" class="form-control" name="payment_amount"\
                                           id="payment_amount" type="text">\
                                    <span class="input-group-addon">.00</span>\
                                </div>\
                                <span class="help-inline"></span>\
                            </div>\
                        <span class="validate-error"></span>\
                        <br>\
                        <div class="controls file_block">\
                            <span class="btn btn-primary btn-file" id="file">Choose File\
                                <input type="file" name="payment_file" id="paymentFileToUpload"\
                                       class="btn-upload">\
                            </span>\
                            <span class="help-inline"></span>\
                        </div>\
                       <input type="hidden" name="pre_invoice_status" id="pre_invoice_status" class="form-control">\
                       <input type="hidden" name="estimate_id" id="estimate_id" class="form-control">\
                       <input type="hidden" name="invoice_id" id="invoice_id" class="form-control">\
                       <input type="hidden" name="payment_type" id="payment_type" class="form-control" value="invoice">\
                       <input type="hidden" name="client_unsubscribed" id="client_unsubscribed" class="form-control">\
                    </span>\
                </div>',
        tpl2: '<div>' +
            '<label class="control-label col-sm-12">Status: </label>' +
            '<div class="col-sm-12"><select name="new_invoice_status" style="width: 238px!important;" class="form-control w-100"></select></div><div class="clear"></div>' +
            '<div class="paid_block m-b-md" style="display:none;">' +
            '<label class="control-label col-sm-12">Payment Mode: </label>' +
            '<div class="col-sm-12"><select name="payment_method_int" class="form-control w-100 fu_pm"><option value="cash">Cash</option><option value="cheque">Cheque</option><option value="dc">Debit Card</option><option value="etransfer">E-Transfer</option><option value="cc">Credit Card</option></select></div>' +
            '<div class="col-sm-12"><a href="#" class="pull-right add_cr_card">Edit card</a></div>' +
            '<div style="display: none; background: rgb(242, 242, 242);" class="fu_cc_block clear"></div>' +
            '<label class="control-label col-sm-12">Amount: </label>' +
            '<div class="col-sm-6"><input type="text" class="form-control w-100 payment_amount" name="payment_amount"><span style="display:none; " id="processingCC" data-price_validation="true"><a href="#" class="pull-right">Processing</a></span></div><div class="col-sm-6"><span class="btn btn-primary btn-file" id="file1">Choose File<input type="file" name="payment_file" id="paymentFileToUpload" class="btn-upload"></span></div>' +
            '<input type="hidden" name="pre_invoice_status"><input type="hidden" name="estimate_id" class="estimate_id"><input type="hidden" name="payment_type" value="invoice"><input type="hidden" name="ccReceipt" id="ccReceipt" value=""><input type="hidden" class="fuPk" value=""><div class="clear"></div>' +
            '</div>' +
            '</div>',
        inputclass: '',
        source: []
    });

    String.prototype.capitalize = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }

    String.prototype.stripSlashes = function () {
        return this.replace(/\\(.)/mg, "$1");
    }

    $.fn.editabletypes.invoices_status = InvoicesStatus;

}(window.jQuery));
