(function ($) {
    "use strict";

    var LeadsStatus = function (options) {
        /*console.log(options);*/
        this.init('leads_status', options, LeadsStatus.defaults);
    };

    $.fn.editableutils.inherit(LeadsStatus, $.fn.editabletypes.list);

    $.extend(LeadsStatus.prototype, {

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

        leadStatuses: [],
        leadDeclineStatus: null,
        getLeadStatuses: function () {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                global: false,
                type: 'POST',
                url: baseUrl + 'leads/ajax_get_lead_statuses',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        leadReasons: [],
        getLeadReasons: function () {
            var ajaxTpl = '';
            $.ajax({
                async: false,
                dataType: 'json',
                global: false,
                type: 'POST',
                url: baseUrl + 'leads/ajax_get_lead_reasons',
                success: function (resp) {
                    ajaxTpl = resp;
                }
            });
            return ajaxTpl;
        },

        render: function () {
            var deferred = $.Deferred();
            this.pk = this.options.scope.dataset.pk;
            this.name = this.options.scope.dataset.name;
            this.lead_id = this.options.scope.dataset.fu_item_id;
            this.$form = this.$tpl.parents('form:first');
            this.$statusSelect = this.$tpl.find('select[name=lead_status]');
            if (!this.leadStatuses.length)
                this.leadStatuses = this.getLeadStatuses();
            this.$reasonSelect = this.$tpl.find('select[name=lead_reason_status]');
            this.$statusSelect.empty();
            this.$reasonSelect.empty();
            this.$tpl.find('input[name=lead_id]').val(this.lead_id);

            var self = this;

            var fillStatuses = function ($el, data) {
                if ($.isArray(data)) {
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].lead_status_declined == "1")
                            self.leadDeclineStatus = data[i].lead_status_id;
                        $el.append($('<option>', {
                            value: data[i].lead_status_id,
                        }).text(data[i].lead_status_name));
                    }
                }
                return $el;
            };

            fillStatuses(this.$statusSelect, this.leadStatuses);

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

            deferred.resolve();
            this.error = null;

            // this.onSourceReady(function () {
            //     fillItems(this.$statusSelect, this.sourceData[1].text);
            //     deferred.resolve();
            // }, function () {
            //     this.error = this.options.sourceError;
            //     deferred.resolve();
            // });
            // this.onSourceReady(function () {
            //     //console.log(this.sourceData);
            //     fillItemsReasons(this.$reasonSelect, this.sourceData[0].text);
            //     deferred.resolve();
            // }, function () {
            //     this.error = this.options.sourceError;
            //     deferred.resolve();
            // });

            $(this.$statusSelect).on('change', function () {
                var value = $(this).val();
                if (value == self.leadDeclineStatus) {
                    if (!self.leadReasons.length)
                        self.leadReasons = self.getLeadReasons();
                    fillReasons(self.$reasonSelect, self.leadReasons);

                    self.$form.find('.declined_block').show(10, function () {
                        Popup.setPosition();
                    });
                } else {
                    self.$form.find('.declined_block').hide(10, function () {
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
                new_estimate_status: this.$statusSelect.val(),
                reason: this.$reasonSelect.val()
            }
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

        },

    });

    LeadsStatus.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {

        tpl: '<div>' +
            '<div class="editable-address">' +
            '<label class="control-label col-sm-12"> Status: </label>' +
            '<div class="col-sm-12"><select name="lead_status" class="form-control"></select></div><div class="clear"></div>' +
            '<div class="declined_block m-b-md" style="display:none;">' +
            '<label class="control-label col-sm-12">Select Reason: </label>' +
            '<div class="col-sm-12"><select name="lead_reason_status" class="form-control w-100"></select></div><div class="clear"></div>' +
            '<input type="hidden" name="lead_id">' +
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

    $.fn.editabletypes.leads_status = LeadsStatus;

}(window.jQuery));
