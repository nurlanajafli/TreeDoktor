var Workorders = function(){
    var config = {

        ui:{},

        events:{
            btn_save_note: '.btn-save-note',
            workorder_note_text: '.workorder-note-text',
            list_note_text: '.wo-note',
            complete_service_status: '.complete-service-status input[type="checkbox"]',
            complete_all_services: '#complete-all-services',
            save_complete_selected: '#save-complete-selected'
        },

        route:{
            update_notes:"/workorders/update_notes"
        },

        templates:{
            profile_files:'#workorders-profile-files-tmp',
        },

        views:{
            profile_files:'#workorders-profile-files'
        },

        images: {}
    };

    var model = {
        workorder_notes:[],
        ctrl_button:false,
        profile_files:[],
    };

    var _private = {
        init:function(){
            _private.set_workorder_notes_model();
            _private.set_workorder_profile_files_model();
            _private.render_files();
        },

        set_workorder_notes_model:function(){
            $(config.events.workorder_note_text).each(function () {
                model.workorder_notes[$(this).attr('name')] = $(this).val();
            });
        },
        set_workorder_profile_files_model:function(){

            if(window.workorder_files!=undefined && window.workorder_files.length){
                filepath = '';
                window.workorder_files.forEach(function (value) {
                    filepath = 'uploads/payment_files/' + client_id.value + '/' + window.estimate_no + '/' + value;
                    console.log(window.workorder_pdf_files.indexOf(filepath)!=-1);
                    checked = (window.workorder_pdf_files.indexOf(filepath)!=-1)?true:false;

                    model.profile_files.push({name: value, estimate_id:estimate_id, filepath:filepath, checked:checked});
                });
            }
        },
        save_note: function(e){
            var data = e.currentTarget.dataset;
            var button = e.currentTarget;
            data[$(e.currentTarget.dataset.href).attr("name")] = $(e.currentTarget.dataset.href).val();
            Common.request.send(config.route.update_notes, data, function (response) {
                model.workorder_notes.wo_office_notes = response.workorder.wo_office_notes;
                model.workorder_notes.estimate_crew_notes = response.workorder.estimate.estimate_crew_notes;
                console.log(model.workorder_notes.estimate_crew_notes);
                $(button).hide();
            });
        },

        workorder_note_text:function (e) {

            var field = e.currentTarget;
            var wo_id = $(field).data("id");
            var text = $(field).val();

            if(typeof model.workorder_notes[$(field).attr("name")] != "undefined"){
                if(model.workorder_notes[$(field).attr("name")]!=text)
                    $(field).parent().find(config.events.btn_save_note).show();
                else
                    $(field).parent().find(config.events.btn_save_note).hide();
            }
        },

        press_ctrl:function (event) {
            if(event.which === 17)
                model.ctrl_button = true;
        },

        save_workorder_list_note:function (event) {

            let obj = $(this);
            var data = {id: $(obj).attr('data-wo_id')};
            data[$(obj).attr('name')] = $(obj).val();

            switch (event.which) {
                case 13:
                    if (model.ctrl_button){
                        Common.request.send(config.route.update_notes, data, function (response) {
                            $(obj).blur();
                        }, function () {}, true);
                        return false;
                    }
                    break;
                case 17:
                    model.ctrl_button = false;
                    break;
            }
        },

        render_files: function () {

            Common.renderView({
                template_id:config.templates.profile_files,
                view_container_id:config.views.profile_files,
                data:[{files:model.profile_files}],
                helpers:public.helpers
            });

        },

        checked_services: [],

        complete_service_status: function(e){
            _private.checked_services = [];

            $(config.events.complete_service_status).each(function (k, item) {
                if($(item).prop('checked')==true)
                    _private.checked_services.push($(item).val());
            });

            if(!_private.checked_services.length){
                $(config.events.complete_all_services).prop('checked', false);
                $(config.events.save_complete_selected).hide();
            }
            else{
                $(config.events.save_complete_selected).show();
            }
        },

        complete_all_services:function(e){
            _private.checked_services = [];
            var checkAll = $(e.currentTarget).prop('checked');

            $(config.events.complete_service_status).each(function (k, item) {
                $(item).prop('checked', checkAll);
            });
            $(config.events.complete_service_status).each(function (k, item) {
                if($(item).prop('checked')==true)
                    _private.checked_services.push($(item).val());
            });
            _private.checked_services = _private.checked_services.map(i=>Number(i));
            if(!_private.checked_services.length){
                $(config.events.save_complete_selected).hide();
                return false;
            }

            $(config.events.save_complete_selected).show();
        },

        save_complete_selected: function(e){
            var estimate_id = $(e.currentTarget).data('estimate_id');
            Common.request.send('/estimates/completeServices', {services:_private.checked_services, estimate_id:estimate_id}, function (response) {

                if(response.finish != 0 && response.invoice)
                    return location.reload();

                if(!confirm('All services for this Estimate was completed. Do you want change workorder status to Finished?'))
                    return location.reload();

                Common.request.send('/workorders/ajax_change_workorder_status', {workorder_id:response.estimate.workorder.id, workorder_status:0}, function (response) {
                    $('#email-template-form').find('input[name="estimate_id"]').val(response.workorder_data.estimate_id);
                    DamagesModal.init(response.workorder_data.id, false, function(){
                        ClientsLetters.init_modal(response.invoice_email_template, 'ClientsLetters.invoice_email_modal');
                    });
                }, function (response) {}, false);


            }, function (response) {
                errorMessage('Ooops! Error...');
            }, false);
        },

        upload_file:function (event) {

            if ($(event.currentTarget).parent().is('.disabled'))
                return false;

            $('#preloader').show();
            $('#fileToUpload').parent().addClass('disabled');
            var segments = location.href.split('/');
            var workorder_id = $('[name="workorder_id"]').val();

            //starting setting some animation when the ajax starts and completes
            $("#loading").ajaxStart(function () {
                $(this).show();
            }).ajaxComplete(function () {
                $(this).hide();
            });

            /*
             prepareing ajax file upload
             url: the url of script file handling the uploaded files
             fileElementId: the file type of input element id and it will be the index of  $_FILES Array()
             dataType: it support json, xml
             secureuri:use secure protocol
             success: call back function when the ajax complete
             error: callback function when the ajax failed
            */
            $.ajaxFileUpload
            (
                {
                    url: baseUrl + 'workorders/ajax_save_file/',
                    secureuri: false,
                    fileElementId: 'fileToUpload',
                    dataType: 'json',
                    data: {id: workorder_id},
                    success: function (data, status) {

                        $('#preloader').hide();
                        $('#fileToUpload').parent().removeClass('disabled');

                        if (data.status == 'error'){
                            alert('Error');
                        }
                        else{
                            model.profile_files.unshift({name: data.filename, estimate_id:estimate_id, filepath:data.filepath, checked:false});
                            _private.render_files();
                        }
                    },
                    error: function (data, status, e) {
                        alert(e);
                        $('#preloader').hide();
                        $('#fileToUpload').parent().removeClass('disabled');
                    }
                }
            )

            return false;

        }
    };

    var public = {
        init:function(){
            $(document).ready(function(){
                public.events();
                _private.init();
            });
        },

        events:function(){
            $(document).on('click', config.events.btn_save_note, _private.save_note);
            $(document).on('keyup', config.events.workorder_note_text, _private.workorder_note_text);

            $('#wo_status li a').click(function () {
                $('#wo_status li.active').removeClass('active');
                var text = $(this).not('span').text();
                $('#wo_status').prev('.dropdown-toggle').html(text + '<span class="caret" style="margin-left:5px;"></span>');
                $('#statusMapper').attr('href', baseUrl + 'workorders/workorders_mapper/' + $(this).data('statusname'));
            });

            var list_table = $("#workordersTable");
            if(list_table.length)
                list_table.tablesorter({sortList: [ [1, 1] ]});

            $(document).on('keydown', config.events.list_note_text, _private.press_ctrl);
            $(document).on('keyup', config.events.list_note_text, _private.save_workorder_list_note);
            $(document).on('change', '#fileToUpload', _private.upload_file);

            $(document).on('click', '.deleteEstimatePhoto', function(){
                var obj = $(this);
                var estimate_id = $(obj).attr('data-estimate_id');
                var path = $(obj).attr('data-path');
                if(confirm('Are you sure?'))
                {
                    $.ajax({
                        method: "POST",
                        data: {estimate_id:estimate_id,path:path},
                        url: base_url + "estimates/deleteFile",
                        dataType:'json',
                        success: function(response){
                            if(response.type != 'ok')
                                alert(response.message);
                            else
                            {
                                model.profile_files.forEach(function (value, key) {
                                    if(value.filepath==path && value.estimate_id==estimate_id)
                                        model.profile_files.splice(key, 1);
                                });
                            }
                            _private.render_files();
                        }
                    });
                }
                return false;
            });

            $(document).on("change", config.events.complete_service_status, _private.complete_service_status);
            $(document).on("change", config.events.complete_all_services, _private.complete_all_services);
            $(document).on("click", config.events.save_complete_selected, _private.save_complete_selected);

        },

        helpers:{

        }

    };

    public.init();
    return public;
}();