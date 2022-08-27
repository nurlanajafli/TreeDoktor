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
    '/assets/vendors/diez/js/modules/datepicker.js',
    '/assets/vendors/diez/js/modules/table.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/select-two.js'
];

exports.App = {
    routes: {
        list: '/equipment/groups/ajax_get_groups',
        row: '/equipment/groups/ajax_get_group',
        edit: '/equipment/groups/ajax_update_group',
        create: '/equipment/groups/ajax_create_group',
        delete: '/equipment/groups/ajax_delete_group'
    },
    init: function (app) {
        this.app = app;
        this.idField = "group_id";
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.filter = undefined;
        this.rowTemplate = Handlebars.compile($('#group-row-template').html());
        this.editTemplate = Handlebars.compile($('#group-edit-template').html());
        this.defaultSort = ['group_created_at', 'desc'];
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
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
};