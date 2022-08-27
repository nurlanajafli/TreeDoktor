var WorkordersStatus = function(){
    var config = {

        ui:{
            extra_status_logic_container:'.extra-status-logic-container',
            status_modal: '#status-modal',
            color_input: 'input[name="wo_status_color"]',
            color_type: 'input[name="status_color_type"]:checked'
        },
        
        events:{
            extra_status_logic:'.extra-status-logic',
            save: '#save-workorder-status-form',
            status_flag: '#is_finished_by_field, #is_confirm_by_client, #is_delete_invoice'
        },

        route:{
            
        },
        
        templates:{
            workorders_status_modal: '#workorders-status-modal-tpl'
        },

        views:{
            workorders_status_modal: '#workorders-status-modal'
        }
    }
    var model = {
        statuses: window.workorder_statuses,
        extra_status: []
    };
    var _private = {
        init:function(){

            _private.check_extra_status();
        },

        set_extra_status:function(e){

            if($(e.currentTarget).prop('checked')){
                $(e.currentTarget).closest(config.ui.extra_status_logic_container).find(config.events.extra_status_logic).not(e.currentTarget).attr("disabled", "disabled");
                $(e.currentTarget).closest(config.ui.extra_status_logic_container).find(config.events.extra_status_logic).not(e.currentTarget).parent().find('i').addClass('disabled');
                return;
            }

            var extra_statuses = $(e.currentTarget).closest(config.ui.extra_status_logic_container).find(config.events.extra_status_logic).not(e.currentTarget);
            jQuery.each(extra_statuses, function(key, value){
                console.log(key, value, $(value).attr('id'), public.exist_extra_status[$(value).attr('id')]);

                if(public.exist_extra_status[$(value).attr('id')]==undefined || public.exist_extra_status[$(value).attr('id')]==0){
                    $(value).removeAttr("disabled");
                    $(value).parent().find('i').removeClass('disabled');
                }
            });
        },

        check_extra_status : function(){
            model.extra_status = window.workorder_statuses.map(function (status) {
                return {
                    'wo_status_id' : status.wo_status_id,
                    'is_confirm_by_client' : status.is_confirm_by_client,
                    'is_finished_by_field' : status.is_finished_by_field,
                    'is_delete_invoice'	: status.is_delete_invoice,
                    'is_protected'	: status.is_protected
                };
            });


            $.each(model.extra_status, function(key, value){
                $.each(value, function(i, v){
                    if(parseInt(v)==1)
                        public.exist_extra_status[i] = parseInt(v);
                });
            });
        },

        workorders_status: function (e) {
            var status = {};
            var status_id = $(e.relatedTarget).data('id');
            if(status_id != undefined && model.statuses.length){
                index = model.statuses.findIndex(function (status_val) {
                    return (status_val.wo_status_id == status_id);
                });

                if(index!=-1)
                    status = model.statuses[index];
            }

            Common.renderView({
                template_id:config.templates.workorders_status_modal,
                view_container_id:config.views.workorders_status_modal,
                data:[{wo_status_id: status_id, status:status, exist_extra_status:public.exist_extra_status}],
                helpers:[]
            });

            setMyColorpicker($('.mycolorpicker'));
            Common.init_checkbox();
        },

        status_flag: function (e) {
            var value = ($(e.currentTarget).prop('checked'))?1:0;
            var name = $(e.currentTarget).attr('id');
            $('input[name="'+name+'"]').val(value);

            if(value==1)
                public.exist_extra_status[name] = value;
            else
                delete public.exist_extra_status[name];

            _private.set_extra_status(e);
        },

        change_color_type: function () {
            if($(config.ui.color_type).val()=='wo_status_color'){
                $(config.ui.color_input).removeAttr('disabled');
            }
            else{
                $(config.ui.color_input).val('');
                $(config.ui.color_input).attr('disabled','disabled');
            }
        }
    }
    
    var selected_date;
    var public = {
        exist_extra_status: {},
        init:function(){
            
            $(document).ready(function(){
                public.events();
                _private.init();
            });
        },
        events:function(){
            //$(config.events.extra_status_logic).change(_private.set_extra_status);
            $(config.ui.status_modal).on('show.bs.modal', _private.workorders_status);
            $(config.ui.status_modal).on('hide.bs.modal', _private.check_extra_status);
            $(document).on('change', config.events.status_flag, _private.status_flag);
            $(document).on('change', config.ui.color_type, _private.change_color_type);
        },

    }

    public.init();
    return public;
}();
