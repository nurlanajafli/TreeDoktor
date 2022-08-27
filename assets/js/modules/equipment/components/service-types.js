/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [
    'assets/js/colpick.js'
];

exports.modules = [
    '/assets/vendors/diez/js/modules/table.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/filter.js',
];

exports.App = {
    routes: {
        list: '/equipment/settings/ajax_get_service_types',
        row: '/equipment/settings/ajax_get_service_type',
        edit: '/equipment/settings/ajax_update_service_type',
        create: '/equipment/settings/ajax_create_service_type',
        delete: '/equipment/settings/ajax_delete_service_type'
    },
    init: function (app) {
        this.app = app;
        this.idField = "service_type_id";
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        this.rowTemplate = Handlebars.compile($('#service-type-row-template').html());
        this.editTemplate = Handlebars.compile($('#service-type-edit-template').html());
        this.defaultSort = ['service_type_created_at', 'desc'];
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