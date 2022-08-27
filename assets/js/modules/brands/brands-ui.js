var BrandsUi = function(){
    var config = {
        ui:{
            brands_select: '#brand-select',
            update_client_brand_form: '#update-client-brand',
            update_estimate_brand_form: '#update-estimate-brand',

            update_client_client_id: 'input[name="brand_client_id"]',
            update_client_brand_id: 'input[name="client_brand_id"]',

            update_estimate_estimate_id: 'input[name="estimate_id"]',
            update_estimate_brand_id: 'input[name="estimate_brand_id"]',
        },
        select2:[
            {
                selector:'#brand-select',
                options:{
                    //width:'100%',
                    escapeMarkup: function(m) {
                        return m;
                    },

                    allowClear: true,

                    formatResult: function(state){
                        var image = $(state.element[0]).data('url');
                        var is_deleted = ($(state.element[0]).data('deleted')!=undefined)?' text-muted ':'';

                        if(is_deleted)
                            state.text=state.text+'&nbsp;<i class="fa fa-ban"></i>';

                        var image_tag = '<div class="clearfix'+is_deleted+'"><div class="col-md-3 m-n"><img src="'+image+'" style="width:30px;height:30px;margin-top:2px;border-radius:3px;"></div>';
                        var result = image_tag+'<div class="col-md-9 m-n" style="padding-top:4px">'+state.text+'</div></div>';
                        return result;
                    },
                    formatSelection: function(state){

                        if(state.text.trim().length){
                            state.text = "&nbsp;&nbsp;&nbsp;"+state.text.trim()+"&nbsp;&nbsp;&nbsp;";
                        }

                        var image = $(state.element[0]).data('url');
                        var is_deleted = ($(state.element[0]).data('deleted')!=undefined)?' text-muted ':'';
                        if(is_deleted)
                            state.text=state.text+'&nbsp;<i class="fa fa-ban"></i>';

                        var image_tag = '<div class="col-md-3 col-xs-3 col-sm-3 col-lg-3 m-n p-n'+is_deleted+'"><img src="'+image+'" style="width:30px;height:30px;margin-top:2px;border-radius:3px;" class="bg-light"></div>';
                        var result = image_tag+'<div class="col-md-9 m-n" style="padding-top:4px"><strong class="'+is_deleted+'">'+state.text.trim()+'</strong></div>';
                        return result;
                    },
                },
                onchange:function(e){
                    if(e.added != undefined && e.added.element != undefined){
                        var data = $(e.added.element[0]).data();
                        if(data.client_id!=undefined){
                            BrandsUi.update_client(data.client_id, parseInt($(this).val()));
                        }
                    }
                },
                values:false,
            },
            {
                selector:'#estimate-brand-select',
                options:{
                    minimumResultsForSearch: -1,
                    width:'100%',
                    escapeMarkup: function(m) {
                        return m;
                    },

                    allowClear: true,

                    formatResult: function(state){
                        var image = $(state.element[0]).data('url');
                        var is_deleted = ($(state.element[0]).data('deleted')!=undefined)?' text-muted ':'';
                        //fa fa-ban
                        if(is_deleted)
                            state.text=state.text+'&nbsp;<i class="fa fa-ban"></i>';

                        var image_tag = '<div class="clearfix'+is_deleted+'"><div class="col-md-3 col-xs-3 col-sm-3 col-lg-3 m-n p-left-0"><img src="'+image+'" style="width:30px;height:30px;margin-top:2px;border-radius:3px;" class="bg-light"></div>';
                        var result = image_tag+'<div class="col-md-9 m-n" style="padding-top:4px">'+state.text+'</div></div>';
                        return result;
                    },
                    formatSelection: function(state){
                        var image = $(state.element[0]).data('url');
                        var is_deleted = ($(state.element[0]).data('deleted')!=undefined)?' text-muted ':'';

                        if(state.text.trim().length){
                            state.text = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+state.text.trim()+"&nbsp;&nbsp;&nbsp;&nbsp;";
                        }

                        if(is_deleted)
                            state.text=state.text+'&nbsp;<i class="fa fa-ban"></i>';

                        var image_tag = '<div class="col-md-3 m-n'+is_deleted+'"><img src="'+image+'" style="width:30px;height:30px;margin-top:2px;border-radius:3px;"></div>';
                        var result = image_tag+'<div class="col-md-9 m-n p-right-0 p-left-10" style="padding-top:4px"><strong class="'+is_deleted+'">'+state.text+'</strong></div>';
                        return result;
                    },
                },
                onchange:function(e){
                    if(e.added != undefined && e.added.element != undefined){
                        var data = $(e.added.element[0]).data();
                        if(data.estimate_id!=undefined){
                            BrandsUi.update_estimate(data.estimate_id, parseInt($(this).val()));
                        }
                    }
                },
                values:false,
            },
            {
                selector:'.links-select2',
                options:{
                    minimumResultsForSearch: -1,
                    width:'20%',
                    containerCss: ['padding-top : 3px;'],
                    escapeMarkup: function(m) {
                        return m;
                    },

                    allowClear: true,
                },
                values:false,
            }
        ],
        events:{},
        route:{},
        templates:{},
        view:{}
    }

    var _private = {

        init:function(){
            Common.init_select2(config.select2);
        },


    };

    var public = {

        init:function(){
            $(document).ready(function(){
                public.events();
                _private.init();
            });
        },

        events:function(){

        },

        init_select2:function(){
            Common.init_select2(config.select2);
        },

        update_client: function(client_id, brand_id){

            $(config.ui.update_client_client_id).val(client_id);
            $(config.ui.update_client_brand_id).val(brand_id);

            $(config.ui.update_client_brand_form).trigger('submit');
        },

        update_estimate: function(estimate_id, brand_id){

            $(config.ui.update_estimate_estimate_id).val(estimate_id);
            $(config.ui.update_estimate_brand_id).val(brand_id);

            $(config.ui.update_estimate_brand_form).trigger('submit');

        },

        update_brand_callback:function(response){
            console.log(response);
        },

        helpers: {
            is_active_brand:function(id){
                return (window.active_brand==id);
            }
        }
    };




    public.init();
    return public;
}();