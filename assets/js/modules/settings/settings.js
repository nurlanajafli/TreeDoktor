var Settings = function () {
    var config = {
        autoscroll_enabled: false,
        ui: {
            select2: '.select2',
            qb_desktop_import: '.qb-desktop-import',
            qb_desktop_export: '#desktopExport',
            qb_desktop_select_logs: '#desktop-logs',
            qb_desktop_logs_content: '#desktop-logs-content',
            qb_desktop_close: '#qb-desktop-close',
            qb_desktop_logs_panel: '.qb-desktop-logs-panel'
        },

        events: {
            qb_desktop_import: '.qb-desktop-import',
            qb_desktop_export: '#desktopExport',
            qb_desktop_select_logs: '#desktop-logs',
            qb_desktop_close: '#qb-desktop-close'
        },

        route: {},
        templates: {}
    }

    var assigned_user_id = 0;
    var _private = {
        init: function () {
            _private.init_select2();
        },

        init_select2: function () {
            $(document).ready(function () {

                if (!$(config.ui.select2).length)
                    return true;

                $(config.ui.select2).each(function () {
                    var data_json_selector = $(this).data('href');
                    
                    var data_json = $(data_json_selector).val();
                    if (typeof (data_json) != "undefined") {
                        var data = JSON.parse(data_json);
                        $(this).select2({data: data});

                        if ($(this).attr('id') === 'tax') {
                            $(this).addClass('taxSelect');

                            if ($(this).val() === 'Tax (0%)') {
                                $('.editTaxBtn').prop('disabled', true).addClass('disabled');
                                $('.deleteTaxBtn').prop('disabled', true).addClass('disabled');
                            }
                        }
                    }
                });

                if ($('#tax').length) {
                    $('#tax').on('change', function() {
                        // disabled edit and delete for Tax 0%
                        if ($(this).val() === 'Tax (0%)') {
                            $('.editTaxBtn').prop('disabled', true).addClass('disabled');
                            $('.deleteTaxBtn').prop('disabled', true).addClass('disabled');
                        } else {
                            $('.editTaxBtn').prop('disabled', false).removeClass('disabled');
                            $('.deleteTaxBtn').prop('disabled', false).removeClass('disabled');
                        }
                    });
                }
            });
        },
        qb_desktop_import: function () {
            if ($(this).val() != '') {
                let files = $(this)[0].files;
                let fd = new FormData();
                if(files.length > 0) {
                    fd.append('file',files[0]);
                    console.log(files[0]);
                    $.ajax({
                        type: "POST",
                        url: "settings/importQbDesktop",
                        contentType: false,
                        processData: false,
                        data: fd
                    }).done(function (msg) {
                        console.log(msg);
                    });
                }
            }
        },

        qb_desktop_select_logs: function(){
            let timestamp = $(this).val();
            $.ajax({
                type: "POST",
                url: "settings/getDateQbDesktopLogs",
                data: {timestamp: timestamp}
            }).done(function (msg) {
                if(msg.status === 'ok' && msg.message === 'success' && msg.content.length){
                    $(config.ui.qb_desktop_logs_content).html(msg.content);
                    $(config.ui.qb_desktop_logs_panel).show();
                }
                else{
                    $(config.ui.qb_desktop_logs_panel).hide();
                }
                $(config.ui.qb_desktop_select_logs).select2('val', '');
            });
        },

        qb_desktop_close: function(){
            $(config.ui.qb_desktop_logs_panel).hide();
        },

        qb_desktop_export: function () {
            $.ajax({
                type: "GET",
                url: "settings/exportQbDesktop",
                contentType: false,
                processData: false
            }).done(function (msg) {
                if(msg && msg.status == 'error')
                    errorMessage(msg.error);
                else if(msg && msg.status == 'ok' && msg.message && msg.link && msg.name){
                    console.log(msg);
                    const dummy = document.createElement('a');
                    dummy.href = msg.link;
                    dummy.download = msg.name;

                    document.body.appendChild(dummy);
                    dummy.click();
                    location.reload();
                }
            });
        }
    }

    var selected_date;
    var public = {

        init: function () {
            $(document).ready(function () {
                public.events();
                _private.init();
            });
        },

        events: function () {
            /*
            $(config.events.scheduled_check).change(_private.scheduled_check);
            $(document).delegate(config.events.estimator_appointment, 'click', _private.select_estimator);
            */

            $(document).delegate(config.events.qb_desktop_import, 'change', _private.qb_desktop_import);
            $(document).delegate(config.events.qb_desktop_export, 'click', _private.qb_desktop_export);
            $(document).delegate(config.events.qb_desktop_select_logs, 'change', _private.qb_desktop_select_logs);
            $(document).delegate(config.events.qb_desktop_close, 'click', _private.qb_desktop_close);
            $(function () {
                $('.popover-markup>.trigger').popover({
                    html: true,
                    title: function () {
                        return $(this).parent().find('.head').html();
                    },
                    content: function () {
                        return $('.content').html();
                    }
                }).click(function (e) {
                    $(this).popover('toggle');
                    e.stopPropagation();
                })
            });

            //*** Payroll Lunch & Deduction section start ***//
            $(function() {
                let lunch_cur_state = $(document).find('#payroll_lunch_state').val();

                if(lunch_cur_state == 0) {
                    $('#lunch_switcher').bootstrapToggle('off');
                    $('.lunch').attr('disabled', true);
                }

                if(lunch_cur_state == 1) {
                    $('#lunch_switcher').bootstrapToggle('on');
                    $('.lunch').attr('disabled', false);
                }

                $(document).on('change','#lunch_switcher', function() {
                    if ($(this).val() == 0) {
                        $('.lunch').attr('disabled', false);
                    } else {
                        $('.lunch').attr('disabled', true);
                    }

                    $(document).find('#payroll_lunch_state').val($(this).prop('checked') ? 1 : 0);
                });

                let deduction_cur_state = $('#payroll_deduction_state').val();

                if(deduction_cur_state == 0) {
                    $('#deduction_switcher').bootstrapToggle('off');
                }

                if(deduction_cur_state == 1) {
                    $('#deduction_switcher').bootstrapToggle('on');
                }

                $(document).on('change', '#deduction_switcher', function() {
                    $(document).find('#payroll_deduction_state').val($(this).prop('checked') ? 1 : 0);
                });

                var show_weekend_switcher = parseInt($("#show_weekend_switcher").val());
                if(show_weekend_switcher == 0) {
                    $('#show_weekend_switcher').bootstrapToggle('off');
                }else{
                    $('#show_weekend_switcher').bootstrapToggle('on');
                }
                $(document).on('change', '#show_weekend_switcher', function() {
                    $(this).val($(this).prop('checked') ? 1 : 0);
                    console.log($('#show_weekend_switcher').val());
                });

            });
            //*** Payroll Lunch & Deduction section end ***//

            if(typeof classes != 'undefined')
                $('.classesSelect').select2({data: classes});
            if(typeof classesForParent != 'undefined')
                $('.classesSelectParent').select2({data: classesForParent});
            $('.classesSelect').on('change', function (){
                let itemObj = $(this).select2('data');
                if(itemObj == null)
                    return;
                if(itemObj.active == false){
                    if ($('.delete i').hasClass('fa-eye-slash')){
                        $('.delete i').toggleClass('fa-eye fa-eye-slash');
                    }
                }else if(itemObj.active == true){
                    if ($('.delete i').hasClass('fa-eye')){
                        $('.delete i').toggleClass('fa-eye-slash fa-eye');
                    }
                }

            });
            $('.triggerClass').on('click', function () {
                let classObj = $('.classesSelect').select2('data');
                if ((classObj == null || classObj.id == 0) && !$(this).hasClass('create')){
                    errorMessage('Choose a class');
                    return;
                }
                let classModal =  $('.classModal');
                let deleteText = "Are you sure you want to make ";
                classModal.find('.bodyCreateEdit').show();
                classModal.find('.bodyDelete').hide();
                classModal.find('.classId').val(null);
                classModal.find('.saveClass').text('Save').removeClass('delete');
               if($(this).hasClass('create')){
                   classModal.find('#modalLabel').text('Create Class');
                   classModal.find('.class_name').val('');
                   classModal.find('.classesSelectParent').val(0).trigger('change');
                   classModal.modal('toggle');
                   $('.classesSelect').val(0).trigger('change');
               } else if($(this).hasClass('edit')){
                   if(classObj.text.indexOf('(deleted)') > 0){
                       errorMessage('You cannot modify a list element that has been deleted.');
                       return;
                   }
                   classModal.find('.classId').val(classObj.id);
                   classModal.find('#modalLabel').text('Edit Class');
                   classModal.find('.class_name').val(classObj.text.replace('(deleted)', ''));
                   classModal.find('.classesSelectParent').val(classObj.parent).trigger('change');
                   classModal.modal('toggle');
                } else if($(this).hasClass('delete')){
                   classModal.find('.saveClass').text('Yes').addClass('delete');
                   if(classObj.text.indexOf('(deleted)') > 0){
                       $('.saveClass.delete').trigger('click');
                       return;
                   }
                   classModal.find('.classId').val(classObj.id);
                   classModal.find('#modalLabel').text('Make inactive (reduces usage)');
                   classModal.find('.bodyCreateEdit').hide();
                   classModal.find('.bodyDelete').text(deleteText + classObj.text + ' and all its child classes inactive?').show();
                   classModal.modal('toggle');
               }
            });
            $('.saveClass').on('click', function () {
                let isActive = 1;
                let classModal =  $('.classModal');
                let classObj =  $('.classesSelect').select2('data');
                let parentObj =  $('.classesSelectParent').select2('data');
                let classId;
                let parentId;
                let className = classModal.find('.class_name').val();
                if(parentObj != null){
                    parentId = parentObj.id;
                }
                if($(this).hasClass('delete') && classObj != null){
                    if(classObj.active)
                        isActive = 0;
                    else {
                        parentId = classObj.parent;
                        className = classObj.text.replace('(deleted)', '');
                    }
                }
                if(classObj != null){
                    classId = classObj.id;
                }
                if(className.trim() === '' && !$(this).hasClass('delete')){
                    errorMessage('Empty Class Name!');
                    return;
                }
                $.ajax({
                   type: 'POST',
                   url: 'classes/ajaxSaveClass',
                   dataType: 'json',
                   data: {
                       classId: classId,
                       parentId: parentId,
                       className: className,
                       isActive: isActive,
                   },
                   success: function (response) {
                        let classes = [];
                        let classesForParent = [];
                        if(response.data){
                            classes = response.data['classes'];
                            $('.classesSelect').select2({data:classes});
                            classesForParent = response.data['classesForParent'];
                            $('.classesSelectParent').select2({data:classesForParent});
                            successMessage('Success!');
                        }
                       if(response.status === 'error'){
                           errorMessage(response.message);
                       }

                       if(classModal.is(':visible'))
                           classModal.modal('toggle');
                       $('.classesSelect').trigger('change');
                   }
                });
            });
            $('#location').change(function () {
                $(".select2-search-choice-close").attr("style", "height: 10px !important");
            });
            $('.edit').on('shown.bs.popover', function (e) {
                $.ajax({
                    type: "GET",
                    url: "settings/getTaxForEdit",
                    dataType: 'json',
                    data: {text: $('#tax').val()}
                }).done(function (msg) {
                    $('.nameTax').val(msg['name']);
                    $('.rateTax').val(msg['value']);
                    $('#taxId').val(msg['name'] + ' (' + msg['value'] + '%)');
                    $('#taxIdx').val(msg['taxIdx']);
                });
            });
            $('.changeSync').on('click', function () {
                let result = confirm('Are you sure?');
                if (result) {
                    let syncType = $(this).attr('data-id');
                    $.ajax({
                        type: "GET",
                        url: "settings/getDataFromSync",
                        dataType: 'json',
                        data: {syncType: syncType}
                    }).done(function (msg) {
                        $("[data-id='" + syncType + "']").prop('value', msg);
                    });
                }
            });
            $('.syncInvoiceNO').on('click', function () {
                let result = confirm('Are you sure?');
                if (result) {
                    let value = $('.syncInvoiceNO').val();
                    $.ajax({
                        type: "GET",
                        url: "settings/changeSyncInvoiceNO",
                        dataType: 'json',
                        data: {value: value}
                    }).done(function (msg) {
                        // $("[data-id='" + syncType + "']").prop('value', msg);
                        $('.syncInvoiceNO').val(msg);
                    });
                }
            });
            $.ajax({
                type: "GET",
                url: "settings/getTimeFormats",
                dataType: 'json'
            }).done(function (msg) {
                $("#timeFormats").select2({
                    data: msg
                })
            });
            $.ajax({
                type: "GET",
                url: "settings/getDateFormats",
                dataType: 'json'
            }).done(function (msg) {
                $("#dateFormats").select2({
                    data: msg
                })
            });
            return $.ajax({
                type: "GET",
                url: "settings/getLocations",
                dataType: 'json'
            }).done(function (msg) {
                $("#location").select2({
                    data: msg,
                    multiple: true
                });
                $("#location").prop("disabled", true);
            });

        },


        init_datapicker: function () {
            /*
            var dates_calendar = $(config.events.scheduled_datepicker).datepicker({
                format: 'dd-mm-yyyy',
                todayHighlight:true,
                showOn: "button",
                  buttonText: '<i class="fa fa-calendar"></i>',
            });

            dates_calendar.on('changeDate', function(e) {

                if(selected_date==$(config.events.scheduled_datepicker).datepicker('getFormattedDate'))
                    return false;

                $(config.ui.select_schedule_date+' '+config.ui.task_date).val($(config.events.scheduled_datepicker).datepicker('getFormattedDate'));
                selected_date = $(config.events.scheduled_datepicker).datepicker('getFormattedDate');

                date_points[$(config.events.scheduled_datepicker).datepicker('getFormattedDate')] = [];
                public.get_schedule_intervals($(config.events.scheduled_datepicker).datepicker('getFormattedDate'), {});

            });
            */
        }
    }

    public.init();
    return public;
}();

function changeLocation() {
    return $.ajax({
        type: "GET",
        url: "settings/getQBLocations",
        dataType: 'json'
    }).done(function (msg) {
        $("#location").select2({
            data: msg,
            multiple: true
        })
        $("#location").prop("disabled", false);
        $(".select2-search-choice-close").attr("style", "height: 10px !important");
    });
}

function saveTax() {
    const taxIdx = $('#taxIdx').val() || null;

    return $.ajax({
        type: "POST",
        url: "settings/saveTax",
        dataType: 'json',
        data: {taxName: $('.nameTax').val(), taxRate: $('.rateTax').val(), taxId: $('#taxId').val()}
    }).done(function (msg) {
        closePopover();
        $("#tax").select2({
            data: msg,
        });

        if (taxIdx && taxIdx !== '') {
            $('#tax').val(msg[taxIdx].id).trigger('change');
        }
    });
}

function editTax() {
    const taxIdx = $('#taxIdx').val();

    return $.ajax({
        type: "POST",
        url: "settings/saveTax",
        dataType: 'json',
        data: $(".editTax").serialize()
    }).done(function (msg) {
        closePopover();
        $("#tax").select2({
            data: msg,
        });

        if (taxIdx && taxIdx !== '') {
            $('#tax').val(msg[taxIdx].id).trigger('change');
        }
    });
}

function closePopover() {
    $('.popover-markup>.trigger').popover('hide');
}

function handleChange(input) {
    if (input.value < 0) input.value = 0;
    if (input.value > 100) input.value = 100;
}

function deleteTax() {
    let result = confirm('Are you sure you want to remove the tax?');
    if (result) {
        $.ajax({
            type: "POST",
            url: "settings/deleteTax",
            dataType: 'json',
            data: {taxId: $('#tax').val()}
        }).done(function (msg) {
            closePopover();
            $("#tax").select2({
                data: msg,
            });

            if (msg[0] !== undefined && msg[0].id !== undefined) {
                $('#tax').val(msg[0].id).trigger('change');
            }
        });
    }
}

