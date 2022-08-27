/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [];

exports.modules = [
    '/assets/vendors/diez/js/modules/table.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/filter.js',
];

exports.App = {
    routes: {
        list: '/equipment/settings/ajax_get_repair_types',
        row: '/equipment/settings/ajax_get_repair_type',
        edit: '/equipment/settings/ajax_update_repair_type',
        create: '/equipment/settings/ajax_create_repair_type',
        delete: '/equipment/settings/ajax_delete_repair_type'
    },
    init: function (app) {
        this.app = app;
        this.idField = "repair_type_id";
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        this.rowTemplate = Handlebars.compile($('#repair-type-row-template').html());
        this.editTemplate = Handlebars.compile($('#repair-type-edit-template').html());
        this.defaultSort = ['repair_type_id', 'desc'];
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