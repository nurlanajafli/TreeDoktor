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
        list: '/equipment/service-reports/ajax_get_reports',
        row: '/equipment/service-reports/ajax_get_report',
        create: '/equipment/service-reports/ajax_create_report',
        edit: '/equipment/service-reports/ajax_update_report',
        delete: '/equipment/service-reports/ajax_delete_report',
        users: '/user/ajax_get_users',
        equipment: '/equipment/ajax_get_equipment',
        pdf: '/equipment/service-reports/pdf/{id}',
        uploadFile: '/equipment/service-reports/ajax_file_upload',
        deleteFile: '/equipment/service-reports/ajax_file_delete',
    },
    init: function (app) {
        this.app = app;
        if ($(this.app).data('equipmentId')) {
            this.where = {eq_id: $(this.app).data('equipmentId')};
        } else {
            this.where = {};
        }
        this.idField = 'service_report_id';
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.rowTemplate = Handlebars.compile($('#service-report-row-template').html());
        this.editTemplate = Handlebars.compile($('#service-report-edit-template').html());
        this.defaultSort = ['service_report_created_at', 'desc'];
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        this.getList();
    },
    refreshed: false,
    refresh: function (app) {
        this.getList();
    },
    observers: {},
    events: {},
    afterEventSelect: function (route, event) {
        switch (route) {
            case "equipment":
                this.eventFilter(event);
                break;
            case "users":
                this.eventFilter(event);
                break;
        }
    },
    eventOnHideModal: function (event, el) {
        if (event.target == el) {
            $(el).find('.modal-dialog').removeClass('edit-modal-dialog');
            $(el).find('form').removeClass('edit-form create-form');
            $(el).find('.modal-body').remove();
            $(el).find('.modal-footer .download-pdf').hide();
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
    afterRenderEdit: function (pre, response) {
        $(this.editFormEl).find('.modal-footer .download-pdf')
            .attr('href', D.helper.url.route(this.routes.pdf, {id: response.service_report_id}))
            .show();
    },
    callbackSubmitSuccess: function (response, action, id, form) {
        $(form).find('.btn[type=submit]').removeAttr('disabled');
        //$(this.editFormEl).modal('hide');
        if (action === "delete") {
            $(this.app).find('.data-list #row_' + id).remove();
            return;
        }
        if (action === 'edit') {
            successMessage('Service Report saved successfully');
        }
        if (action === 'create') {
            $(this.editFormEl).modal('hide');
            if (typeof this.noPaginate === "undefined" || !this.noPaginate) {
                this.refreshed = true;
                this.eventPage(null, 1);
            }
        }
    },
};