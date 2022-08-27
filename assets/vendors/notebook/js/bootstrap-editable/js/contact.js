
(function ($) {
    "use strict";
    
    var Contact = function (options) {
		this.init('contact', options, Contact.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(Contact, $.fn.editabletypes.abstractinput);

    $.extend(Contact.prototype, {
        /**
        Renders input from tpl

        @method render() 
        **/        
        render: function() {
           this.$input = this.$tpl.find('input');
        },
        
        /**
        Default method to show value in element. Can be overwritten by display option.
        
        @method value2html(value, element) 
        **/
        value2html: function(value, element) {
            if(!value) {
                $(element).empty();
                return; 
            }
            var html = value.cc_title + ', ' + value.cc_name + ', ' + value.cc_phone + ', ' + value.cc_email + ', ' + value.cc_email_check + ', ' + value.cc_email_manual_approve + ', ' + value.cc_client_id;
            $(element).html(html); 
        },
        
        /**
        Gets value from element's html
        
        @method html2value(html) 
        **/        
        html2value: function(html) {        
          /*
            you may write parsing method to get value by element's html
            e.g. "Moscow, st. Lenina, bld. 15" => {city: "Moscow", street: "Lenina", building: "15"}
            but for complex structures it's not recommended.
            Better set value directly via javascript, e.g. 
            editable({
                value: {
                    city: "Moscow", 
                    street: "Lenina", 
                    building: "15"
                }
            });
          */ 
          return null;  
        },
      
       /**
        Converts value to string. 
        It is used in internal comparing (not for sending to server).
        
        @method value2str(value)  
       **/
       value2str: function(value) {
           var str = '';
           if(value) {
               for(var k in value) {
                   str = str + k + ':' + value[k] + ';';  
               }
           }
           return str;
       }, 
       
       /*
        Converts string to value. Used for reading value from 'data-value' attribute.
        
        @method str2value(str)  
       */
       str2value: function(str) {
           /*
           this is mainly for parsing value defined in data-value attribute. 
           If you will always set value by javascript, no need to overwrite it
           */
           return str;
       },                
       
       /**
        Sets value of input.
        
        @method value2input(value) 
        @param {mixed} value
       **/         
       value2input: function(value) {
           if(!value) {
             return;
           }

           let clickedElement = this.options.scope;
           let tbody = $(clickedElement).parents('tbody');
           let cc_id = value.cc_id ? value.cc_id : false;
           let cc_title = value.cc_title ? value.cc_title : $(clickedElement).text();
           let cc_name = value.cc_name ? value.cc_name : tbody.find('.contact-name').text();
           let cc_phone = value.cc_phone ? value.cc_phone : tbody.find('a[data-number]').text();
           let cc_email = value.cc_email ? value.cc_email : tbody.find('a[data-email]').text();
           let cc_email_check = value.cc_email_check ? value.cc_email_check : false;
           let cc_email_manual_approve = value.cc_email_manual_approve ? value.cc_email_manual_approve : false;
           let cc_client_id = value.cc_client_id ? value.cc_client_id : tbody.find('a[data-client-id]').attr('data-client-id');

           if(this.$input.filter('[name="cc_title"]').length)
             this.$input.filter('[name="cc_title"]').val(cc_title.trim());
           if(this.$input.filter('[name="cc_name"]').length)
             this.$input.filter('[name="cc_name"]').val(cc_name.trim());
           if(this.$input.filter('[name="cc_phone"]').length)
             this.$input.filter('[name="cc_phone"]').val(cc_phone.trim());
           if(this.$input.filter('[name="cc_email"]').length)
             this.$input.filter('[name="cc_email"]').val(cc_email.trim());
           if(this.$input.filter('[name="cc_client_id"]').length)
             this.$input.filter('[name="cc_client_id"]').val(cc_client_id.trim());
           if(this.$input.filter('[name="cc_client_id"]').length)
               this.$input.filter('[name="cc_client_id"]').val(cc_client_id.trim());


           let approveBtn = $('[name="manual_approve"]');

           if(cc_email.length && (cc_email_check == 0 && cc_email_check.length)) {
               $(approveBtn).css('display', 'initial');
               $(approveBtn).next('[name="approve_status"]').attr('data-approve-status', cc_email_manual_approve);

               if (cc_email.length && cc_email_manual_approve == 1) {
                   $(approveBtn).text('Mark Email as incorrect')
                       .removeClass('btn-success')
                       .addClass('btn-danger')
                       .attr('data-approve-status', 1);

                   $(approveBtn).next('[name="cc_approve_status"]').attr('data-approve-status', 1);
               }
           } else {
               $(approveBtn).parent().remove();
           }
       },
       
       /**
        Returns value of input.
        
        @method input2value() 
       **/          
       input2value: function() {
		   var obj = {};
		   
		   if(this.$input.filter('[name="cc_title"]').length)
			   obj.cc_title = this.$input.filter('[name="cc_title"]').val();
		   if(this.$input.filter('[name="cc_name"]').length)
			   obj.cc_name = this.$input.filter('[name="cc_name"]').val();
		   if(this.$input.filter('[name="cc_phone"]').length)
			   obj.cc_phone = this.$input.filter('[name="cc_phone"]').val();
		   if(this.$input.filter('[name="cc_email"]').length)
			   obj.cc_email = this.$input.filter('[name="cc_email"]').val();
		   if(this.$input.filter('[name="cc_client_id"]').length)
			   obj.cc_client_id = this.$input.filter('[name="cc_client_id"]').val();
           if(this.$input.filter('[name="cc_approve_status"]').length)
               obj.cc_approve_status = this.$input.filter('[name="cc_approve_status"]').attr('data-approve-status');

           return obj;
       },        
       
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
            this.$input.filter('[name="client_contact"]').focus();
       },
       
       /**
        Attaches handler to submit form in case of 'showbuttons=false' mode
        
        @method autosubmit() 
       **/       
       autosubmit: function() {
           this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
           });
       }       
    });

    Contact.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<form id="client_contact-xeditable"> ' +
			 '<div class="editable-address"><label><span>Title: </span><input type="text" name="cc_title" class="form-control cc_title"></label></div>' +
			 '<div class="editable-address"><label><span>Name: </span><input type="text" name="cc_name" class="form-control" autocomplete="off"></label></div>' +
			 '<div class="editable-address"><label><span>Phone: </span><input type="text" name="cc_phone" class="form-control cc_phone"></label></div>' +
			 '<div class="editable-address"><label><span>Email: </span><input type="text" name="cc_email" class="form-control"></label></div>' +
             '<div class="editable-address btn-group-xs text-right">' +
                '<button type="button" name="manual_approve" class="btn btn-xs btn-success" style="outline: none; display: none">Mark Email as correct</button>' +
                '<input type="hidden" name="cc_approve_status" data-approve-status="0">' +
             '</div>' +
			 '<input type="hidden" name="cc_client_id" class="cc_client_id">' +
             '</form>',
        inputclass: ''
    });

    $.fn.editabletypes.contact = Contact;
}(window.jQuery));
