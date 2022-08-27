var ClientsLetters = function(){
    var config = {

        ui:{
            email_template_modal:'#email-template-modal',
            email_template_modal_body:'#email-template-modal-body',

            estimate_pdf_modal_body: '#estimate-pdf-modal-body',
            partial_invoice_modal_body:'#partial-invoice-modal-body',
            invoice_email_modal_body : '#invoice-email-modal-body',

            email_template_form:'#email-template-form',
            email_template_id: 'input[name="email_template_id"]',
            system_label: 'input[name="system_label"]',

            template_tiny: '#client_template_text',
            template_tiny_id: 'client_template_text',

            email_tags: 'email-tags',
            email_tags_id: '#email-tags',

            email_task_id: 'input[name="task_id"]',
            email_event_id: 'input[name="event_id"]',
            related_sms_id: 'input[name="related_sms_id"]',
            email_tags_select2: [{
                selector: '#email-tags',
                init_selected_data: window.email_tags ? window.email_tags : [],
                options: {
                    /*placeholder: "Client tags",*/
                    /**/
                    /*theme: 'default your-container-class',*/
                    tags: [],

                    tokenSeparators: [",", " "],
                    containerCss: 'background-color:green',

                    selectedTagClass: 'label label-success',

                    minimumResultsForSearch: -1,
                    selectOnClose: true,


                    width: '100%',
                    allowClear: true,
                    formatNoMatches: function() {
                        return '';
                    },
                    dropdownCssClass: 'select2-hidden'
                },

                values: [],

                onchange: function (obj) {

                   /* if (obj.added != undefined) {

                        window.email_tags.push(obj.added);
                        if ($('#cteate-client-tag') != undefined) {
                            $('#cteate-client-tag').find('#tag_name').val(obj.added.text);
                            $('#cteate-client-tag').trigger('submit');
                        }

                    }
                    if (obj.removed != undefined) {
                        console.log("Removed");
                        var deleteCondition = obj.removed;
                        window.client_tags = window.client_tags.filter(function (item) {
                            return (item.id != deleteCondition.id && item.text != deleteCondition.text);
                        });
                        if ($('#delete-client-tag') != undefined) {
                            $('#delete-client-tag').find('#tag_name').val(obj.removed.text);
                            $('#delete-client-tag').trigger('submit');
                        }
                    }*/

                    $('input[name="email_tags"]').val(JSON.stringify(window.email_tags));

                },
                createTag: function (params) {
                    console.log('createTag');
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }

                    return {
                        id: term,
                        text: term,
                        newTag: true // add additional parameters
                    }
                },
                insertTag: function (data, tag) {
                    // Insert the tag at the end of the results
                    console.log("insertTag");
                    data.push(tag);
                }
            }]
        },

        events:{
            email_template_modal:'#email-template-modal',
            send_client_email: '[data-save-template]',
            send_estimate_pdf_email: '#send-estimate-pdf-email',
            send_partial_invoice_email : '#send_pdf_to_email',
            send_invoice_email : '#send-invoice-email',
            reload_page: '.close-modal',

            crew_schedule_send_email:'.sendEmail',
        },

        route:{

        },

        templates:{
            email_template_modal_body : '#email-template-modal-body-tmp',
            estimate_pdf_modal_body : '#estimate-pdf-modal-body-tmp',
            partial_invoice_modal_body : '#partial-invoice-modal-body-tmp',
            invoice_email_modal_body : '#invoice-email-modal-body-tmp',
            paid_invoice_email_modal_body : '#invoice-email-modal-body-tmp',
            schedule_appointment_email_modal_body : '#schedule-appointment-email-modal-body-tmp',
            crews_schedule_letters_modal_body : '#crews-schedule-letters-modal-body-tmp'
        }
    };

    var _private = {
        init:function(){},
        initEmailTags: function(){
            Common.init_select2(config.ui.email_tags_select2);
        },
        load_modal:function (e) {
            if(e.relatedTarget.dataset.system_label!=undefined)
                $(config.ui.email_template_form+' '+config.ui.system_label).val(e.relatedTarget.dataset.system_label);
            if(e.relatedTarget.dataset.email_template_id!=undefined)
                $(config.ui.email_template_form+' '+config.ui.email_template_id).val(e.relatedTarget.dataset.email_template_id);

            if(e.relatedTarget.dataset.task_id)
                $(config.ui.email_template_form+' '+config.ui.email_task_id).val(e.relatedTarget.dataset.task_id);

            if(e.relatedTarget.dataset.event_id)
                $(config.ui.email_template_form+' '+config.ui.email_event_id).val(e.relatedTarget.dataset.event_id);

            if(e.relatedTarget.dataset.sms_id)
                $(config.ui.email_template_form+' '+config.ui.related_sms_id).val(e.relatedTarget.dataset.sms_id);

            $(config.ui.email_template_form).data('callback', e.relatedTarget.dataset.callback);
            $(config.ui.email_template_form).trigger('submit');
        },

        destroy_modal: function(e){
            if(tinyMCE.get(config.ui.template_tiny_id) && tinyMCE.get(config.ui.template_tiny_id)!=null && tinyMCE.get(config.ui.template_tiny_id)!=undefined)
                tinyMCE.get(config.ui.template_tiny_id).destroy();

            $(config.ui.email_template_form+' '+config.ui.email_tags_id).val('');
            $(config.ui.email_template_form+' '+config.ui.system_label).val('');
            $(config.ui.email_template_form+' '+config.ui.email_template_id).val('');
            $(config.ui.email_template_form+' '+config.ui.email_task_id).val('');
            $(config.ui.email_template_form+' '+config.ui.email_event_id).val('');
            $(config.ui.email_template_form+' '+config.ui.related_sms_id).val('');

            $(e.currentTarget).find('.modal-content').children().remove();
        },

        send_client_email: function(){

            var template_id = $(this).data('save-template');
            var client_id = $(this).data('client_id');
            $(this).attr('disabled', 'disabled');
            $(config.ui.email_template_modal + ' .modal-footer .btntext').hide();
            $(config.ui.email_template_modal + ' .modal-footer .preloader').show();
            $(config.ui.email_template_modal + ' .fromEmail').parents('.control-group').removeClass('has-error');
            $(config.ui.email_template_modal + ' .email').parents('.control-group').removeClass('has-error');
            $(config.ui.email_template_modal + ' .subject').parents('.control-group').removeClass('has-error');
            $(config.ui.email_template_modal + ' .mce-tinymce.mce-container.mce-panel').parents('.control-group').removeClass('has-error');
            var email = $(config.ui.email_tags_id).val();
            var fromEmail = $(config.ui.email_template_modal).find('.fromEmail').val();
            var subject = $(config.ui.email_template_modal).find('.subject').val();
            var text = $.trim(tinyMCE.get(config.ui.template_tiny_id).getContent());
            var span = $(this);
            if (!email) {
                $(config.ui.email_template_modal + ' .email').parents('.control-group').addClass('has-error');
                $(config.ui.email_template_modal + ' .modal-footer .btntext').show();
                $(config.ui.email_template_modal + ' .modal-footer .preloader').hide();
                $(this).removeAttr('disabled');
                return false;
            }
            if (!fromEmail) {
                $(config.ui.email_template_modal + ' .fromEmail').parents('.control-group').addClass('has-error');
                $(config.ui.email_template_modal + ' .modal-footer .btntext').show();
                $(config.ui.email_template_modal + ' .modal-footer .preloader').hide();
                $(this).removeAttr('disabled');
                return false;
            }
            if (!subject) {
                $(config.ui.email_template_modal + ' .subject').parents('.control-group').addClass('has-error');
                $(config.ui.email_template_modal + ' .modal-footer .btntext').show();
                $(config.ui.email_template_modal + ' .modal-footer .preloader').hide();
                $(this).removeAttr('disabled');
                return false;
            }
            if (!text || $.trim($(text).text()) == '') {
                $(config.ui.email_template_modal + ' .mce-tinymce.mce-container.mce-panel').addClass('form-control');
                $(config.ui.email_template_modal + ' .mce-tinymce.mce-container.mce-panel').parents('.control-group').addClass('has-error');
                $(config.ui.email_template_modal + ' .modal-footer .btntext').show();
                $(config.ui.email_template_modal + ' .modal-footer .preloader').hide();
                $(this).removeAttr('disabled');
                return false;
            }
            $.post(baseUrl + 'clients/ajax_send_email', {client_id : client_id, email : email, from_email : fromEmail, subject : subject, text : text}, function (resp) {
                $(span).removeAttr('disabled');
                $(config.ui.email_template_modal + ' .modal-footer .btntext').show();
                $(config.ui.email_template_modal + ' .modal-footer .preloader').hide();

                if(resp.status == 'error' || resp.status == 'email'){
                    $(config.ui.email_template_modal + ' .modal-footer .btntext').show();
                    $(config.ui.email_template_modal + ' .modal-footer .preloader').hide();
                    $(this).removeAttr('disabled');
                }
                if(resp.status == 'error')
                {
                    errorMessage(resp.message);
                    return false;
                }
                if(resp.status == 'email')
                {
                    var field = ' .email';
                    if(resp.field!=undefined)
                        field = ' .'+resp.field;

                    $(config.ui.email_template_modal + field).parents('.control-group').addClass('has-error');
                    $(config.ui.email_template_modal).animate({
                        scrollTop: $(config.ui.email_template_modal).scrollTop() + $(config.ui.email_template_modal).find('.has-error:first').offset().top
                    },'slow');
                    return false;
                }

                if (resp.status == 'ok')
                    $(config.ui.email_template_modal).modal('hide');

                successMessage(resp.message);
                return false;
            }, 'json');
            return false;
        },

        send_estimate_pdf_email: function() {
            const btn = $(this);

            if (btn.attr('disabled') === 'disabled') {
                return false;
            }

            $('#text').val(tinyMCE.get(config.ui.template_tiny_id).getContent());
            $('input[name="email_tags"]').val($(config.ui.email_tags_id).val());
            $(config.ui.template_tiny).val(tinyMCE.get(config.ui.template_tiny_id).getContent());

            const sms = $('#sent_sms').is(':checked');
            btn.attr('disabled', 'disabled');

            $.post(baseUrl + 'estimates/send_pdf_to_email', $(config.ui.email_template_modal_body+' form').serialize(), function (resp) {
                btn.removeAttr('disabled');

                const response = $.parseJSON($.trim(resp));
                if (response.type === 'error') {
                    errorMessage(response.message);

                    return false;
                }

                successMessage(response.message);

                if (sms) {
                    $(config.events.email_template_modal).modal('hide');
                    $('#sms-2').modal().show();
                } else {
                    location.reload();
                }
            });

            return false;
        },

        send_partial_invoice_email:function(){

            var obj = $(this);
            $(obj).find('button[type="submit"]').attr('disabled', 'disabled');
            $('input[name="email_tags"]').val($(config.ui.email_tags_id).val());
            $.post(baseUrl + 'workorders/send_pdf_to_email', $(config.ui.email_template_modal_body+' form').serialize(), function (resp) {
                response = $.parseJSON(resp);
                $(obj).find('button[type="submit"]').removeAttr('disabled');
                if(response.type == 'success' || response.type == 'ok'){
                    successMessage(response['message']);
                    $(config.ui.email_template_modal).modal('hide');
                }


            });
            return false;

        },

        send_invoice_email:function(){
            var sms = $('#sent_sms').is(':checked');
            $(config.ui.template_tiny).val(tinyMCE.get(config.ui.template_tiny_id).getContent());
            $('input[name="email_tags"]').val($(config.ui.email_tags_id).val());
            if ($(this).attr('disabled') == 'disabled')
                return false;
            $(this).attr('disabled', 'disabled');
            $.post(baseUrl + 'invoices/send_pdf_to_email', $(config.ui.email_template_modal_body+' form').serialize(), function (resp) {
                response = $.parseJSON(resp);
                alert(response['message']);
                $(config.events.send_invoice_email).removeAttr('disabled');
                if((response.type == 'success' || response.type == 'ok') && sms)
                {
                    $(config.ui.email_template_modal).modal('hide');
                    if($('#sms-5').length==0){
                        document.location.reload();
                    }
                    else{
                        $('#sms-5').modal().show();
                    }
                }
                else
                    document.location.href = document.location.href;
            });
            return false;
        },

        crew_schedule_send_email:function(){

            var obj = $(this).parent().parent();
            var cl_data = $(this).parent().parent().parent().parent().find('.clientData');
            $(obj).find('input[name="email_tags"]').val($(config.ui.email_tags_id).val())
            var email = $(obj).find('input[name="email_tags"]').val();
            var fromEmail = $(obj).find('.template_from_email').val();
            var text = tinyMCE.activeEditor.getContent();
            var subject = $(obj).find('.subject').val();
            var wo_id = $(obj).find('.wo_id').val();
            var estimate_id = $(obj).find('.estimate_id').val();

            //var text = $('#sms-4 .sms_text').data('text');
            var sms = $('#sent_sms').attr('data-sms_id');

            if(subject == '')
                subject = 'Subject';
            if(fromEmail == '')
                fromEmail = ACCOUNT_EMAIL_ADDRESS;

            $.post(baseUrl + 'clients/ajax_send_email', {email : email, from_email : fromEmail, text : text, subject : subject, sms : sms, estimate : estimate_id, callback : 'set_wo_pending', callback_args : {wo_id : wo_id}}, function(resp){

                $(config.ui.email_template_modal).modal('hide');
                if(resp.status != 'ok') {
                    alert(resp.message);
                }
                else
                {
                    successMessage(resp.message);

                    if(sms && $('#sent_sms').prop('checked'))
                    {
                        $('#sms- .client-name').replaceWith('<header class="panel-heading client-name">Sms to '+ cl_data.data('name') +'</header>');
                        $('#sms- .client-name2').replaceWith('<label class="control-label client-name2">Sms to '+ cl_data.data('name') +'</label>');
                        $('#sms- .client_number').replaceWith('<input class="client_number form-control" type="text" value="'+cl_data.data('client_phone')+'"  placeholder="Sms to..." style="background-color: #fff;"/>');
                        $('#sms- .sms_text').replaceWith('<textarea data-text="'+ resp.text.sms_text +'" class="form-control sms_text">' +
                            resp.text.sms_text.replace("[NAME]", cl_data.data('name')).replace("[EMAIL]", cl_data.data('client_email')).replace("[ADDRESS]" ,cl_data.data('address'))
                                .replace("[AMOUNT]", cl_data.data('amount')).replace("[DATE]", cl_data.data('date-ymd')).replace("[TIME AND DATE]", cl_data.data('event-time-interval'))
                                .replace("[COMPANY_NAME]", cl_data.data('brand-name')).replace("[COMPANY_EMAIL]", cl_data.data('brand-email')).replace("[COMPANY_PHONE]", cl_data.data('brand-phone'))
                                .replace("[COMPANY_ADDRESS]", cl_data.data('brand-address')).replace("[COMPANY_BILLING_NAME]", cl_data.data('brand-name')).replace("[COMPANY_WEBSITE]", cl_data.data('brand-site'))
                        + '</textarea>');
                        $('#sms- .addSMS').attr('data-client', cl_data.data('name'));
                        $('#sms- .addSMS').attr('data-number', cl_data.data('client_phone'));

                        $('#sms-').modal().show();
                    }
                }
                ScheduleUnit.resetTeams();
            }, 'json');
            return false;
        },

        email_templates_tiny:{},

        render_letter_modal: function (response, template) {
            var data = [];
            data.push(response);
            var renderView = {
                template_id:template,
                view_container_id:config.ui.email_template_modal_body,
                data:data
            };
            Common.renderView(renderView);
            Common.initTinyMCE(config.ui.template_tiny_id);
        },

        reload_cancel_modal: function () {
            if($(this).data('reload'))
                location.reload();
        }


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
            $(config.events.email_template_modal).on('show.bs.modal', _private.load_modal);
            $(config.events.email_template_modal).on('shown.bs.modal', _private.initEmailTags);
            $(config.events.email_template_modal).on('hidden.bs.modal', _private.destroy_modal);
            $('#sms-5, #sms-2').on('show.bs.modal', function() {
                if(window.estimate_total_due != undefined && window.estimate_total_due){
                    $(this).find('.sms_text').val($(this).find('.sms_text').val().replaceAll('[AMOUNT]', Common.money(window.estimate_total_due)));
                }
                if($('.client-info>[data-cc-id]').find('.primary-contact:checked').parents('tbody:first').find('[data-email]').data('email')){
                    $(this).find('.sms_text').val($(this).find('.sms_text').val().replaceAll('[EMAIL]', $('.client-info>[data-cc-id]').find('.primary-contact:checked').parents('tbody:first').find('[data-email]').data('email')));
                }
            });


            $(document).delegate(config.events.send_client_email, 'click', _private.send_client_email);
            $(document).delegate(config.events.send_estimate_pdf_email, 'click', _private.send_estimate_pdf_email);
            $(document).delegate(config.events.send_partial_invoice_email, 'submit', _private.send_partial_invoice_email);
            $(document).delegate(config.events.send_invoice_email, 'click', _private.send_invoice_email);
            $(document).delegate(config.events.reload_page, 'click', _private.reload_cancel_modal);

            $(document).delegate(config.events.crew_schedule_send_email, 'click', _private.crew_schedule_send_email);
        },
        /* ---------------------callbacks -------------------*/
        client_letter_modal:function (response) {
            _private.render_letter_modal(response, config.templates.email_template_modal_body);
        },

        estimate_pdf_letter_modal: function (response) {
            _private.render_letter_modal(response, config.templates.estimate_pdf_modal_body);
        },

        partial_invoice_modal: function (response) {
            _private.render_letter_modal(response, config.templates.partial_invoice_modal_body);
        },

        invoice_email_modal: function (response) {
            _private.render_letter_modal(response, config.templates.invoice_email_modal_body);
        },

        paid_invoice_email_modal: function (response) {
            _private.render_letter_modal(response, config.templates.invoice_email_modal_body);
        },

        schedule_appointment_email_modal: function (response) {
            _private.render_letter_modal(response, config.templates.schedule_appointment_email_modal_body);
        },

        crews_schedule_letters_modal: function(response){
            _private.render_letter_modal(response, config.templates.crews_schedule_letters_modal_body)
        },
        /* --------------------callbacks -----------------------*/

        appointment_email_callback: function (response) {
            if(response.status=='ok') {
                $(config.events.email_template_modal).modal('hide');
                successMessage('Email was sent successfully');
            } else {
                errorMessage(response.message);
            }
        },

        init_modal:function (template_id, callback) {
            if(!template_id || template_id==undefined || !callback || callback==undefined)
                return false;

            if($('a[data-email_template_id="'+template_id+'"]').length==0){
                var link = document.createElement('a');
                link.href = config.ui.email_template_modal;
                link.dataset['callback'] = callback;
                link.dataset['email_template_id'] = template_id;
                link.dataset['toggle'] = 'modal';
                link.dataset['backdrop'] = "static";
                link.dataset['keyboard'] = "false";
                document.body.appendChild(link);
                link.click();
            }
            else{
                $('a[data-email_template_id="'+template_id+'"]').trigger('click');
            }
        }

    };

    public.init();
    return public;
}();
