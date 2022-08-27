

(function ($) {
    "use strict";
    
    var FollowUpStatus = function (options) {
        this.init('followup_status', options, FollowUpStatus.defaults);
    };

    $.fn.editableutils.inherit(FollowUpStatus, $.fn.editabletypes.list);

    $.extend(FollowUpStatus.prototype, {
       	
        value2htmlFinal: function(value, element) {

            var text = '', 
                items = $.fn.editableutils.itemsByValue(value, this.sourceData);
           	
            if(items.length) {
                text = items[0].text;
            }
            $.fn.editabletypes.abstractinput.prototype.value2html.call(this, text, element);
        },
        
        autosubmit: function() {
            this.$input.off('keydown.editable').on('change.editable', function(){
                $(this).closest('form').submit();
            });
        },

        render: function () {

            this.$statusSelect = this.$tpl.find('select[name=fu_status]');
            this.$commentField = this.$tpl.find('textarea[name=fu_comment]');
            this.$dateField = this.$tpl.find('input[name=fu_date]');

            this.$statusSelect.empty();
            this.$commentField.empty();
            this.$dateField.empty();
       		
            var fillItems = function ($el, data) {
            	

                if ($.isArray(data)) {
                    for (var i = 0; i < data.length; i++) {

                        $el.append($('<option>', {
                            value: data[i].value
                        }).text(data[i].text));
                    }
                }

                return $el;
            };

            var deferred = $.Deferred();

            this.error = null;
            

            this.onSourceReady(function () {
            fillItems(this.$statusSelect, this.sourceData);
                deferred.resolve();
            }, function () {
                this.error = this.options.sourceError;
                deferred.resolve();
            });

            $('.datepicker').datepicker({format: 'yyyy-mm-dd', orientation: 'bottom', todayHighlight: true, startDate: new Date(new Date().getTime() + 24 * 60 * 60 * 1000)}).on('hide', function(){
                $('.datepicker').blur();
            }).on('changeDate', function(ev){
                $(this).datepicker('hide');
            });

            $(this.$statusSelect).on('change', function(){
                var value = $(this).val();

                if(value=='postponed') {
                    $('#postponeBlock').css('display', 'block');
                    $('#postponeBlock').find('input[name="fu_date"]').removeAttr('disabled');
                }
                else {
                    $('#postponeBlock').css('display', 'none');
                    $('#postponeBlock').find('input[name="fu_date"]').attr('disabled', 'disabled');
                }
                Popup.setPosition();
                
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
            this.$statusSelect.val(value.fu_status);
            this.$commentField.val(value.fu_comment); //value.fu_comment.stripSlashes());
            this.$dateField.val(value.fu_date);

            if(value.fu_status=='postponed') {
                $('#postponeBlock').css('display', 'block');
                $('#postponeBlock').find('input[name="fu_date"]').removeAttr('disabled');
            }
            else {
                $('#postponeBlock').css('display', 'none');
                $('#postponeBlock').find('input[name="fu_date"]').attr('disabled', 'disabled');
            }
        },
        /**
         Returns value of input.

         @method input2value() 
         **/
        input2value: function () {

            return {
                fu_status: this.$statusSelect.val(),
                fu_comment: this.$commentField.val(),
                fu_date: this.$dateField.val(),
            };
        },
        /**
         Activates input: sets focus on the first field.

         @method activate() 
         **/
        activate: function () {
            this.$statusSelect.focus();
        },

        html2value: function (html) {
            return null;
        },


        value2html: function(value, element) {
           $(element).html(value.fu_status.capitalize());
        },

    });      

    FollowUpStatus.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {

        tpl:'<label for="stump_status" class="control-label col-sm-12">Status:</label>' +
            '<div class="col-sm-12"><select name="fu_status" class="input-sm form-control w-100 col-sm-12"></select></div>'+
            '<div id="postponeBlock"><label for="fu_date" class="control-label col-sm-12">Postpone Date:</label>'+
            '<div class="col-sm-12"><input name="fu_date" readonly type="text" class="datepicker input-sm form-control w-100 col-sm-12" id="fu_date"></div></div>' +
            '<label for="fu_comment" class="control-label col-sm-12">Notes:</label>'+
            '<div class="col-sm-12"><textarea name="fu_comment" type="text" class="input-sm form-control w-100 col-sm-12" id="fu_comment"></textarea></div>' +
            '<div class="clear"></div>',
        inputclass: ''
    });

    String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }

    String.prototype.stripSlashes = function(){
        return this.replace(/\\(.)/mg, "$1");
    }

    $.fn.editabletypes.followup_status = FollowUpStatus;      

}(window.jQuery));
