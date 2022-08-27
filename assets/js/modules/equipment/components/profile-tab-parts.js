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
    //'/assets/vendors/diez/js/modules/select-two.js',
    //'/assets/vendors/diez/js/modules/multi-rows.js',
    //'/assets/vendors/diez/js/modules/pdf.js',
    //'/assets/vendors/diez/js/modules/fileinput.js'
];

exports.App = {
    routes: {
        list: '/equipment/parts/ajax_get_parts',
        row: '/equipment/parts/ajax_get_part',
        create: '/equipment/parts/ajax_create_part',
        edit: '/equipment/parts/ajax_update_part',
        delete: '/equipment/parts/ajax_delete_part'
    },
    init: function (app) {
        this.app = app;
        if ($(this.app).data('equipmentId')) {
            this.where = {eq_id: $(this.app).data('equipmentId')};
        } else {
            this.where = {};
        }
        this.idField = 'part_id';
        //this.nameField = 'part_name';
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.rowTemplate = Handlebars.compile($('#profile-tab-part-row-template').html());
        this.editTemplate = Handlebars.compile($('#profile-tab-part-edit-template').html());
        this.paginatorTemplate = Handlebars.compile($('#paginator-template').html());
        this.defaultSort = ['part_created_at', 'desc'];
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        $(this.app).find('input#filter').val(this.filter);
        this.bindEvents();
        this.getList();
    },
    events: {
        '.action-remove-file:click': 'eventRemoveFile',
    },
    refresh: function (app) {
        this.getList();
    },
    bindEvents: function () {
        let self = this;
        $.initialize('.input-file', function () {
            $(this).on('change', self.eventUpload.bind(self));
        }, {target: this.app});
    },
    afterEventOnHideModal: function (prev, event, el) {
        if (event.target == el) {
            if (typeof EquipmentProfileApp !== "undefined") {
                EquipmentProfileApp.refresh();
            }
        }
    },
    eventUpload: function (event) {
        var textEl = $(event.currentTarget).closest('.modal-body').find('.input-file-text').first();
        $(textEl).val('');
        $.each(event.currentTarget.files, function (key, val) {
            if (val.name)
                $(textEl).val(val.name);
        });
    },
    eventRemoveFile: function (event) {
        var el = $(event.currentTarget).closest('li').first();
        $(el).remove();
    },
};