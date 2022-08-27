/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [
    'assets/js/colpick.js'
];

exports.modules = [
    '/assets/vendors/diez/js/modules/colorpicker.js',
    '/assets/vendors/diez/js/modules/table.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/filter.js',
];

exports.App = {
    routes: {
        list: '/equipment/settings/ajax_get_repair_statuses',
        row: '/equipment/settings/ajax_get_repair_status',
        edit: '/equipment/settings/ajax_update_repair_status',
        create: '/equipment/settings/ajax_create_repair_status',
        delete: '/equipment/settings/ajax_delete_repair_status'
    },
    init: function (app) {
        this.app = app;
        this.idField = "repair_status_id";
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        this.rowTemplate = Handlebars.compile($('#repair-status-row-template').html());
        this.editTemplate = Handlebars.compile($('#repair-status-edit-template').html());
        this.defaultSort = ['repair_status_id', 'desc'];
        this.getList();
    },
    observers: {
        //
    },
    events: {
        //
    },
    refresh: function () {
        this.getList();
    },
    afterCallbackSubmitSuccess: function (pre, response, action, id, form) {
        if (action === "edit") {
            this.refresh();
        }
    }
};