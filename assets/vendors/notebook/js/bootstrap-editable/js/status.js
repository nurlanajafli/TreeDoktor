

(function ($) {
    "use strict";
    
    var StumpStatus = function (options) {
        this.init('stump_status', options, StumpStatus.defaults);
    };

    $.fn.editableutils.inherit(StumpStatus, $.fn.editabletypes.list);
    
    var assignedClass = 'assigned-сlass hide';
    var cleanedClass = 'cleaned-сlass hide';
    var assignedDisable = 'disabled';
    var cleanedDisable = 'disabled';

    $.extend(StumpStatus.prototype, {
       	
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
            this.$inputRemoval = this.$tpl.find('input[name=stump_removal]');
            this.$inputCleanedDate = this.$tpl.find('input[name=stump_cleaned_date]');

            this.$statusSelect = this.$tpl.find('select[name=stump_status]');
            this.$assignedSelect = this.$tpl.find('select[name=stump_assigned]');
            this.$cleanedSelect = this.$tpl.find('select[name=stump_cleaned]');

            this.$statusSelect.empty();
            this.$assignedSelect.empty();
       		
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
            fillItems(this.$statusSelect, this.sourceData[0].text);
                deferred.resolve();
            }, function () {
                this.error = this.options.sourceError;
                deferred.resolve();
            });

            this.onSourceReady(function () {
            fillItems(this.$assignedSelect, this.sourceData[1].text);
                deferred.resolve();
            }, function () {
                this.error = this.options.sourceError;
                deferred.resolve();
            });

            this.onSourceReady(function () {
            fillItems(this.$cleanedSelect, this.sourceData[1].text);
                deferred.resolve();
            }, function () {
                this.error = this.options.sourceError;
                deferred.resolve();
            });
            let dateFormat = $('.date-format').val();
            let timeFormat = $('.time-format').val();
            console.log(dateFormat);
            $('.datetimepicker').datetimepicker({format: dateFormat, viewformat: dateFormat, autoclose: true, clearBtn: true, orientation: 'bottom', showMeridian: timeFormat}).on('hide', function(){
		        $('.datetimepicker').blur();
		    });

            $(this.$statusSelect).on('change', function(){
       			var value = $(this).val();
       			
       			if(!$('.assigned-сlass').hasClass('hide')) {
       				$('.assigned-сlass').addClass('hide');
                    $('.assigned-сlass').find('select').attr('disabled', 'disabled');
                    $('.assigned-сlass').find('input').attr('disabled', 'disabled');
                }

       			if(!$('.cleaned-сlass').hasClass('hide')){
       				$('.cleaned-сlass').addClass('hide');
                    $('.cleaned-сlass').find('select').attr('disabled', 'disabled');
                    $('.cleaned-сlass').find('input').attr('disabled', 'disabled');
                }

       			if(value=='grinded'){
                    $('.assigned-сlass').removeClass('hide');
                    $('.assigned-сlass').find('select').removeAttr('disabled');
            		$('.assigned-сlass').find('input').removeAttr('disabled');
            	}
        		else if(value=='cleaned_up'){
            		$('.assigned-сlass').removeClass('hide');
                    $('.assigned-сlass').find('select').removeAttr('disabled');
                    $('.assigned-сlass').find('input').removeAttr('disabled');
       				$('.cleaned-сlass').removeClass('hide').find('select,input').removeAttr('disabled');
                    $('.cleaned-сlass').find('select').removeAttr('disabled');
                    $('.cleaned-сlass').find('input').removeAttr('disabled');
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

            if(value.stump_status==undefined)
            	value.stump_status = 'new';

            /*
            if(value.stump_assigned==undefined || !value.stump_assigned)
            	value.stump_assigned = 0;
            if(value.stump_cleaned==undefined || !value.stump_cleaned)
            	value.stump_cleaned = 0;
            */
            this.$statusSelect.val(value.stump_status);

            this.$assignedSelect.val(value.stump_assigned);

            this.$cleanedSelect.val(value.stump_cleaned);

            var date = new Date();
            var removalDate = value.stump_removal && value.stump_removal > '0000-00-00 00:00:00' ? value.stump_removal : (date.getYear() + 1900) + "-" + (date.getMonth() + 1) + "-" + date.getDate();
            var cleanedDate = value.stump_cleaned_date ? value.stump_cleaned_date : (date.getYear() + 1900) + "-" + (date.getMonth() + 1) + "-" + date.getDate();

            console.log(removalDate);
            console.log(value.stump_removal);
            console.log(cleanedDate);
            console.log(value);
            this.$inputRemoval.val(removalDate);
            this.$inputRemoval.datetimepicker('update', removalDate);

            this.$inputCleanedDate.val(cleanedDate);
            this.$inputCleanedDate.datetimepicker('update', cleanedDate);

            var Status = this.$statusSelect.val();
            if(!$('.assigned-сlass').hasClass('hide')) {
   				$('.assigned-сlass').addClass('hide');
                $('.assigned-сlass').find('select').attr('disabled', 'disabled');
                $('.assigned-сlass').find('input').attr('disabled', 'disabled');
            }

   			if(!$('.cleaned-сlass').hasClass('hide')) {
   				$('.cleaned-сlass').addClass('hide');
                $('.cleaned-сlass').find('select').attr('disabled', 'disabled');
                $('.cleaned-сlass').find('input').attr('disabled', 'disabled');
            }

   			if(Status=='grinded'){
        		$('.assigned-сlass').removeClass('hide');
                $('.assigned-сlass').find('select').removeAttr('disabled');
                $('.assigned-сlass').find('input').removeAttr('disabled');
        	}
    		else if(Status=='cleaned_up'){
        		$('.assigned-сlass').removeClass('hide');
                $('.assigned-сlass').find('select').removeAttr('disabled');
                $('.assigned-сlass').find('input').removeAttr('disabled');
   				$('.cleaned-сlass').removeClass('hide');
                $('.cleaned-сlass').find('select').removeAttr('disabled');
                $('.cleaned-сlass').find('input').removeAttr('disabled');
   			}
        },
        /**
         Returns value of input.

         @method input2value() 
         **/
        input2value: function () {

            return {
                stump_status: this.$statusSelect.val(),
                stump_assigned: this.$assignedSelect.val(),
                stump_cleaned: this.$cleanedSelect.val(),
                stump_removal: this.$inputRemoval.val(),
                stump_cleaned_date: this.$inputCleanedDate.val()
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
        	if(value.stump_status!=undefined) {
				var statusSuff = '';
				if(value.stump_status == 'grinded')
					statusSuff = '/Injected';
                if(value.stump_map === undefined)
                    $(element).html(value.stump_status.capitalize() + statusSuff + '<br>' + value.stump_last_status_changed);
                else
                    $(element).html(value.stump_status.capitalize() + statusSuff + ' (' + value.stump_last_status_changed + ')');
            }
            else 
            	$(element).html('Change Status');
        },

    });      

    StumpStatus.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {

        tpl:'<label for="stump_status" class="control-label col-sm-12 p-left-0">Status:</label>' +
                '<div class="col-sm-12 p-left-0 p-right-0 "><select name="stump_status" class="input-sm form-control w-100 col-sm-12"></select></div>'+
                
                '<label for="stump_assigned" class="control-label col-sm-12 p-left-0 '+assignedClass+'">Grind Crew:</label>' +
                '<div class="col-sm-12 p-left-0 p-right-0 '+assignedClass+'"><select name="stump_assigned" class="input-sm form-control w-100 col-sm-12" '+cleanedDisable+'></select></div>'+
                '<label for="stump_removal" class="control-label col-sm-12 p-left-0 '+assignedClass+'">Grind Date:</label>'+
                '<div class="col-sm-12 p-left-0 p-right-0 '+assignedClass+'"><input name="stump_removal" type="text" readonly '+assignedDisable+' class="datetimepicker input-sm form-control w-100 col-sm-12" data-date="" id="stump_removal"></div>'+
                
                '<label for="stump_cleaned" class="control-label col-sm-12 p-left-0 '+cleanedClass+'">Clean Crew:</label>' +
                '<div class="col-sm-12 p-left-0 p-right-0 '+cleanedClass+'"><select name="stump_cleaned" class="input-sm form-control w-100 col-sm-12" '+cleanedDisable+'></select></div>'+
                '<label for="stump_cleaned_date" class="control-label col-sm-12 p-left-0 '+cleanedClass+'">Clean Date:</label>'+
                '<div class="col-sm-12 p-left-0 p-right-0 '+cleanedClass+'"><input name="stump_cleaned_date" type="text" readonly '+cleanedDisable+' class="datetimepicker input-sm form-control w-100 col-sm-12" data-date="" id="stump_cleaned_date"></div>'+
                '<div class="clear"></div>',
        inputclass: '',
        source: []
    });

    String.prototype.capitalize = function() {
	    return this.charAt(0).toUpperCase() + this.slice(1);
	}

    String.prototype.stripSlashes = function(){
        return this.replace(/\\(.)/mg, "$1");
    }

    $.fn.editabletypes.stump_status = StumpStatus;      

}(window.jQuery));
