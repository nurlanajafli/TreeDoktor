/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [];

exports.modules = [
    '/assets/vendors/diez/js/modules/datepicker.js',
    '/assets/vendors/diez/js/modules/table.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/filter.js',
    '/assets/vendors/diez/js/modules/select-two.js',
    '/assets/vendors/diez/js/modules/multi-rows.js',
    '/assets/vendors/diez/js/modules/pdf.js',
    '/assets/vendors/diez/js/modules/fileinput.js'
];

exports.App = {
    routes: {
        list: '/equipment/services/ajax_get_services',
        row: '/equipment/services/ajax_get_service',
        create: '/equipment/services/ajax_create_service',
        edit: '/equipment/services/ajax_update_service',
        editReport: '/equipment/service-reports/ajax_update_report',
        delete: '/equipment/services/ajax_delete_service',
        complete: '/equipment/services/ajax_complete_service',
        postpone: '/equipment/services/ajax_postpone_service',
        serviceTypes: '/equipment/services/ajax_get_service_types',
        users: '/user/ajax_get_users',
        equipment: '/equipment/ajax_get_equipment',
        pdf: '/equipment/service-reports/pdf/{id}',
        listPdf: '/equipment/services/pdf',
        uploadFile: '/equipment/services/ajax_file_upload',
        deleteFile: '/equipment/services/ajax_file_delete',
    },
    init: function (app) {
        this.app = app;
        if ($(this.app).data('equipmentId')) {
            this.where = {eq_id: $(this.app).data('equipmentId')};

        } else {
            this.where = {};
        }

        this.idField = 'service_id';
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.rowTemplate = Handlebars.compile($('#service-row-template').html());
        this.editTemplate = Handlebars.compile($('#service-edit-template').html());
        this.completeTemplate = Handlebars.compile($('#service-report-edit-template').html());
        this.postponeTemplate = Handlebars.compile($('#service-postpone-template').html());
        //this.editReportTemplate = Handlebars.compile($('#service-report-edit-template').html());

        this.defaultSort = ['service_next_date_due', 'asc'];
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        if ($(this.app).data('due')) {
            if (typeof this.filter === "undefined")
                this.filter = {};
            this.filter['due'] = $(this.app).data('due');
        }
        //this.filter = D.helper.url.getQuery('filter');
        //$(this.app).find('input#filter').val(this.filter);
        //this.bindEvents();
        this.getList();
    },
    observers: {
        // '[data-toggle="tooltip"]': function () {
        //     $(this).tooltip();
        // },
        // '.mycolorpicker': function () {
        //     $(this).colpick({
        //         submit: 0,
        //         colorScheme: 'dark',
        //         onChange: function (hsb, hex, rgb, el, bySetColor) {
        //             $(el).css('background-color', '#' + hex)
        //                 .css('color', D.helper.contrastColor(hex));
        //             if (!bySetColor) {
        //                 $(el).val('#' + hex);
        //             }
        //         }
        //     }).keyup(function () {
        //         $(this).colpickSetColor(this.value);
        //     });
        //     var current_color = $(this).val();
        //     var current_color_short = current_color.replace(/^#/, '');
        //     $(this).colpickSetColor(current_color_short);
        // },
        // '.datepicker': function () {
        //     if (!$(this).val()) {
        //         var now = new Date();
        //         $(this).val(now.format(DATE_FORMAT));
        //     }
        //     $(this).datepicker({
        //         format: DATE_FORMAT,
        //         todayBtn: true,
        //         todayHighlight: true
        //     });
        // },
        // '#edit': function () {
        //     $(this).on('show.bs.modal', function (event) {
        //         //
        //     });
        //     $(this).on('hide.bs.modal', function (event) {
        //         if (event.target == this) {
        //             $(this).find('.modal-dialog').removeClass('complete-modal-dialog');
        //             $(this).find('form').removeClass('edit-form create-form');
        //             $(this).find('.modal-body').remove()
        //         }
        //     });
        // }
    },
    events: {
        '.action-postpone:click': 'eventPostpone',
        '.action-complete:click': 'eventComplete',
        '.action-pdf:click': 'eventListPdf',
        //'.input-file:change': 'eventUpload',
        // 'form.edit-form:submit': 'eventSubmitEdit',
        // 'form.create-form:submit': 'eventSubmitCreate',
        'form.postpone-form:submit': 'eventSubmitPostpone',
        'form.postpone-form input[name=postpone_date]:change': 'eventPostponeDateChange',
        'form.complete-form:submit': 'eventSubmitComplete',
        'form.edit-report-form:submit': 'eventSubmitEditReport',
    },
    refreshed: false,
    refresh: function (app) {
        this.getList();
    },
    eventOnHideModal: function (event, el) {
        if (event.target == el) {
            $(el).find('.modal-dialog').removeClass('complete-modal-dialog edit-report-modal-dialog');
            $(el).find('form').removeClass('edit-form create-form complete-form postpone-form edit-report-form');
            $(el).find('.modal-body').remove();
            $(el).find('.modal-footer .download-pdf').hide();
            $(el).find('.btn[type=submit]').removeAttr('disabled');
            if (typeof EquipmentServiceReportsApp !== "undefined") {
                EquipmentServiceReportsApp.refresh();
            }
            if (typeof EquipmentProfileApp !== "undefined") {
                EquipmentProfileApp.refresh();
            }
            if (this.refreshed) {
                this.refreshed = false;
            } else {
                this.refresh();
            }

        }
    },
    afterEventSelect: function (route, event) {
        switch (route) {
            case "serviceTypes":
                this.eventSelectServiceType(event);
                break;
            case "equipment":
                this.eventFilter(event);
                break;
        }
    },
    eventSelectServiceType: function (event) {
        if (!event.hasOwnProperty('choice')) {
            return;
        }
        var formEl = $(event.currentTarget).closest('form');
        $(formEl).find('[name=service_name]').val(event.choice.service_type_name);
        $(formEl).find('[name=service_description]').val(event.choice.service_type_description);
    },

    eventPostpone: function (event) {
        let id = event.currentTarget.dataset.id;
        this.getRow(id, this.renderPostpone.bind(this));
        let editEl = $(this.app).find('#edit');
        $(editEl).find('form').addClass('postpone-form');
        let title = 'Postpone Service';
        if (typeof $(event.currentTarget).attr('title') !== "undefined")
            title = $(event.currentTarget).attr('title');
        if ($(this.editFormEl).find('.panel-heading span').length !== 0) {
            $(this.editFormEl).find('.panel-heading span').text(title);
        } else {
            $(this.editFormEl).find('.panel-heading').text(title);
        }
        $(editEl).modal('show');
    },

    eventSubmitPostpone: function (event) {
        event.preventDefault();
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.postpone),
            data,
            ['postpone', null, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },
    eventComplete: function (event) {
        let id = event.currentTarget.dataset.id;
        this.getRow(id, this.renderComplete.bind(this));
        let editEl = $(this.app).find('#edit');
        $(editEl).find('.modal-dialog').addClass('complete-modal-dialog');
        $(editEl).find('form').addClass('complete-form');
        let title = 'Complete Service';
        if (typeof $(event.currentTarget).attr('title') !== "undefined")
            title = $(event.currentTarget).attr('title');
        if ($(this.editFormEl).find('.panel-heading span').length !== 0) {
            $(this.editFormEl).find('.panel-heading span').text(title);
        } else {
            $(this.editFormEl).find('.panel-heading').text(title);
        }
        $(editEl).modal('show');
    },
    eventSubmitComplete: function (event) {
        event.preventDefault();
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.complete),
            data,
            ['complete', null, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },

    callbackSubmitSuccess: function (response, action, id, form) {
        $(form).find('.btn[type=submit]').removeAttr('disabled');
        if (action === 'complete') {
            successMessage('Service Report created successfully');
            this.modifyToEditReport(response);
            return;
        }
        if (action === 'editReport') {
            successMessage('Service Report updated successfully');
            return;
        }
        $(this.editFormEl).modal('hide');
        if (action === 'create') {
            if (typeof this.noPaginate === "undefined" || !this.noPaginate) {
                this.refreshed = true;
                this.eventPage(null, 1);
            }
        }
    },

    modifyToEditReport: function (response) {
        //let report_id = response.service_report_id;
        //$(this.editFormEl).find('.modal-body').append('<input type="hidden" name="service_report_id" value="' + report_id + '">');
        //$(this.editFormEl).find('.modal-body input.file-input').data('id', report_id);
        //this.eventInitFiles(response.files);
        $(this.editFormEl).find('.modal-dialog')
            .removeClass('complete-modal-dialog')
            .addClass('edit-report-modal-dialog');
        $(this.editFormEl).find('form')
            .removeClass('complete-form')
            .addClass('edit-report-form');
        // $(this.editFormEl).find('.modal-footer .download-pdf')
        //     .attr('href', D.helper.url.route(this.routes.pdf, {id: response.service_report_id}))
        //     .show();
        let title = 'Edit ' + response.service.service_name + ' on ' + response.equipment.eq_name + ' Service Report';
        if ($(this.editFormEl).find('.panel-heading span').length !== 0) {
            $(this.editFormEl).find('.panel-heading span').text(title);
        } else {
            $(this.editFormEl).find('.panel-heading').text(title);
        }
        this.renderServiceReport(response);
    },
    eventSubmitEditReport: function (event) {
        event.preventDefault();
        $(event.currentTarget).find('.btn[type=submit]').attr('disabled', 'disabled');
        $(event.currentTarget).find('.help-block').text("").parent().removeClass('has-error');
        let id = $(event.currentTarget).find('input[name=service_report_id]').val();
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.editReport),
            data,
            ['editReport', id, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },
    eventPostponeDateChange: function (event) {
        var formEl = $(event.currentTarget).closest('form');
        $(formEl).find('[name=counter]').removeAttr('disabled');
        $(formEl).find('[name=postpone_note]').removeAttr('disabled');
        $(formEl).find('.btn[type=submit]').removeAttr('disabled');
    },
    afterRenderRows: function (prev, response) {
        if (typeof EquipmentProfileApp !== "undefined") {
            EquipmentProfileApp.changeTabCounter('services', response.data.length);
        }
    },
    renderPostpone: function (response) {
        $(this.app).find('#edit .modal-footer').before(this.postponeTemplate(response))
        $(this.app).find('#edit .btn[type=submit]').attr('disabled', 'disabled');
    },
    renderComplete: function (response) {
        $(this.app).find('#edit .modal-footer').before(this.completeTemplate(response));
        this.eventFillData();
    },
    renderServiceReport: function (response) {
        $(this.editFormEl).find('.modal-body').replaceWith(this.completeTemplate(response));
        if ("multiRowsModule" in this._modules) {
            this.eventFillData(response);
        }
    },
    eventListPdf: function (event) {
        this.getOrdering();
        event.preventDefault();
        $(event.currentTarget).closest('.modal-footer').append('<div id="loading">\n' + '<img id="loading-image" src="/assets/img/ajax-loaders/32x32.gif" alt="Loading..." />\n' + '</div>');
        var parentEvent = event;
        var req = new XMLHttpRequest();
        req.open("POST", D.helper.url.route(this.routes.listPdf), true);
        req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        req.responseType = "blob";
        req.onload = function (event) {
            $(parentEvent.currentTarget).closest('.modal-footer').find('#loading').remove();
            var blob = req.response;
            var regex = /filename=\"(.*?)\"/i;
            var cd = req.getResponseHeader("content-disposition"); //if you have the fileName header available
            var match = regex.exec(cd);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = match[1];
            link.click();
        };
        req.send($.param({
            query: this.query,
            filter: this.filter,
            page: D.helper.url.getPage(),
            sort: this.sort,
            where: this.where,
            noPaginate: this.noPaginate
        }));
    }
};
