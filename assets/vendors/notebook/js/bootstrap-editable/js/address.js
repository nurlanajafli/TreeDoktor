/**
Address editable input.
Internally value stored as {city: "Moscow", street: "Lenina", building: "15"}

@class address
@extends abstractinput
@final
@example
<a href="#" id="address" data-type="address" data-pk="1">awesome</a>
<script>
$(function(){
    $('#address').editable({
        url: '/post',
        title: 'Enter city, street and building #',
        value: {
            city: "Moscow", 
            street: "Lenina", 
            building: "15"
        }
    });
});
</script>
**/
(function ($) {
    "use strict";
    
    var Address = function (options) {
		var components = {};
		var tpl = {
			stump_main_intersection: '<div class="editable-address"><label><span>Add. Info: </span><input type="text" name="stump_main_intersection" class="form-control"></label></div>',
			stump_address: '<div class="editable-address"><label><span>Address: </span><input type="text" data-part-address="address" name="stump_address" class="form-control" autocomplete="off"></label></div>',
			stump_country: '<div class="editable-address"><label><span>Country: </span><input type="text" data-part-address="country" name="stump_country" class="form-control"></label></div>',
			stump_city: '<div class="editable-address"><label><span>City: </span><input type="text" data-part-address="locality" name="stump_city" class="form-control"></label></div>',
			stump_state: '<div class="editable-address"><label><span>State: </span><input type="text" data-part-address="administrative_area_level_1" name="stump_state" class="form-control"></label></div>',
			stump_zip: '<div class="editable-address"><label><span>Zip: </span><input type="text" data-part-address="postal_code" name="stump_zip" class="form-control"></label></div>',
			stump_lat: '<input type="hidden" name="stump_lat" data-part-address="lat">',
			stump_lon: '<input type="hidden" name="stump_lon" data-part-address="lon">',
            stump_add_info : '<div class="editable-address"><label><span>Add. Info: </span><input type="text" name="stump_add_info" class="form-control"></label></div>',
		}
		eval('components = new Object(' + options.scope.dataset.value + ')');
		
		if(Object.keys(components).length) {
			Address.defaults.tpl = '<form id="stump-xeditable">';
			$.each(components, function(key, val) {
				if(tpl[key] != undefined)
					Address.defaults.tpl += tpl[key];
			});
			Address.defaults.tpl += '</form>';
		}
		this.init('address', options, Address.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(Address, $.fn.editabletypes.abstractinput);

    $.extend(Address.prototype, {
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
            var html = value.stump_address + ', ' + value.stump_city + ', ' + value.stump_state;
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
           if(this.$input.filter('[name="stump_main_intersection"]').length)
			   this.$input.filter('[name="stump_main_intersection"]').val(value.stump_main_intersection);
		   if(this.$input.filter('[name="stump_country"]').length)
			   this.$input.filter('[name="stump_country"]').val(value.stump_country);
           if(this.$input.filter('[name="stump_city"]').length)
			   this.$input.filter('[name="stump_city"]').val(value.stump_city);
		   if(this.$input.filter('[name="stump_address"]').length)
			   this.$input.filter('[name="stump_address"]').val(value.stump_address);
		   if(this.$input.filter('[name="stump_state"]').length)
			   this.$input.filter('[name="stump_state"]').val(value.stump_state);
		   if(this.$input.filter('[name="stump_zip"]').length)
			   this.$input.filter('[name="stump_zip"]').val(value.stump_zip);
		   if(this.$input.filter('[name="stump_lat"]').length)
			   this.$input.filter('[name="stump_lat"]').val(value.stump_lat);
		   if(this.$input.filter('[name="stump_lon"]').length)
			   this.$input.filter('[name="stump_lon"]').val(value.stump_lon);
		   if(this.$input.filter('[name="stump_add_info"]').length)
			   this.$input.filter('[name="stump_add_info"]').val(value.stump_add_info);
       },       
       
       /**
        Returns value of input.
        
        @method input2value() 
       **/          
       input2value: function() {
		   var obj = {};
		   if(this.$input.filter('[name="stump_main_intersection"]').length)
			   obj.stump_main_intersection = this.$input.filter('[name="stump_main_intersection"]').val();
		   if(this.$input.filter('[name="stump_country"]').length)
			   obj.stump_country = this.$input.filter('[name="stump_country"]').val();
		   if(this.$input.filter('[name="stump_city"]').length)
			   obj.stump_city = this.$input.filter('[name="stump_city"]').val();
		   if(this.$input.filter('[name="stump_address"]').length)
			   obj.stump_address = this.$input.filter('[name="stump_address"]').val();
		   if(this.$input.filter('[name="stump_state"]').length)
			   obj.stump_state = this.$input.filter('[name="stump_state"]').val();
		   if(this.$input.filter('[name="stump_zip"]').length)
			   obj.stump_zip = this.$input.filter('[name="stump_zip"]').val();
		   if(this.$input.filter('[name="stump_lat"]').length)
			   obj.stump_lat = this.$input.filter('[name="stump_lat"]').val();
		   if(this.$input.filter('[name="stump_lon"]').length)
			   obj.stump_lon = this.$input.filter('[name="stump_lon"]').val();
		   if(this.$input.filter('[name="stump_add_info"]').length)
			   obj.stump_add_info = this.$input.filter('[name="stump_add_info"]').val();
           return obj;
       },        
       
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
            this.$input.filter('[name="address"]').focus();
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

    Address.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<form id="stump-xeditable">' + 
			 '<div class="editable-address"><label><span>Address: </span><input type="text" data-part-address="address" name="stump_address" class="form-control" autocomplete="off"></label></div>'+
			 '<div class="editable-address"><label><span>City: </span><input type="text" data-part-address="locality" name="stump_city" class="form-control" readonly></label></div>'+
             '<div class="editable-address"><label><span>State: </span><input type="text" data-part-address="administrative_area_level_1" name="stump_state" class="form-control" readonly></label></div>' + 
             '<div class="editable-address"><label><span>Country: </span><input type="text" data-part-address="country" name="stump_country" class="form-control" readonly></label></div>' + 
             '<div class="editable-address"><label><span>Zip: </span><input type="text" data-part-address="postal_code" name="stump_zip" class="form-control" readonly></label></div>' + 
             '<input type="hidden" name="stump_lat" data-part-address="lat">' + 
             '<input type="hidden" name="stump_lon" data-part-address="lon">' + 
             '</form>',
        inputclass: ''
    });

    $.fn.editabletypes.address = Address;

}(window.jQuery));
