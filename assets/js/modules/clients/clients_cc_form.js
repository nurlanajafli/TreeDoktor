var ClientsCCForm = function(){
    var config = {

        ui:{
            get_card_form:'.add_credit_card',
            client_id: '#client_id',
            content_block: '#card-block',
            card_form: '#card-form'
            //form : this.$tpl.parents('form:first')
        },

        events:{
            get_card_form:'.add_credit_card',
            client_id: '#client_id',
            content_block: '#card-block',
            card_form: '#card-form'
        },

        route:{

        },

        templates:{

        }
    };

    var _private = {
        init:function(){},

        get_card_form: function () {
            var ajaxTpl = '';
            var client_id = $(config.ui.client_id).val();
            $(config.ui.get_card_form).addClass('disabled');
            $.ajax({
                dataType: 'json',
                data: {client_id: client_id},
                global: false,
                method: 'POST',
                url: baseUrl + 'payments/ajax_get_card_form',
                success: function (resp) {
                    if (resp.status === 'error') {
                        errorMessage(resp.error + ' Please try again later');
                        $(config.ui.get_card_form).removeClass('disabled');
                    }
                    else {
                        $(config.ui.content_block).append(resp.html);
                        $(config.ui.card_form).modal('show');
                        $(config.ui.card_form).on('hidden.bs.modal', function () {
                            $(config.ui.content_block).html('');
                        });
                        ajaxTpl = resp.html;
                        setTimeout(function () {
                            $(config.ui.get_card_form).removeClass('disabled');
                        }, 100);
                    }
                }
            });
            return ajaxTpl;
        },
    };

    var public = {

        init:function(){
            $(document).ready(function(){
                public.events();
                _private.init();
            });
        },

        helpers: {},

        events:function(){
            $(config.events.get_card_form).click(_private.get_card_form);
        }

    };

    public.init();
    return public;
}();