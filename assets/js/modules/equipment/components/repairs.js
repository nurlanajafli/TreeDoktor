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
    '/assets/vendors/diez/js/modules/fileinput.js'
];

exports.App = {
    routes: {
        list: '/equipment/repairs/ajax_get_repairs',
        row: '/equipment/repairs/ajax_get_repair',
        create: '/equipment/repairs/ajax_create_repair',
        edit: '/equipment/repairs/ajax_update_repair',
        delete: '/equipment/repairs/ajax_delete_repair',
        repairTypes: '/equipment/repairs/ajax_get_types',
        repairStatuses: '/equipment/repairs/ajax_get_statuses',
        assign: '/equipment/repairs/ajax_assign_user',
        users: '/user/ajax_get_users',
        filterAssigned: '/user/ajax_get_users',
        equipment: '/equipment/ajax_get_equipment',
        pdf: '/equipment/repairs/pdf/{id}',
        uploadFile: '/equipment/repairs/ajax_file_upload',
        deleteFile: '/equipment/repairs/ajax_file_delete',
    },
    init: function (app) {
        this.app = app;
        if ($(this.app).data('equipmentId')) {
            this.where = {eq_id: $(this.app).data('equipmentId')};
        } else {
            this.where = {};
        }
        this.idField = 'repair_id';
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.rowTemplate = Handlebars.compile($('#repair-row-template').html());
        this.createTemplate = Handlebars.compile($('#repair-create-template').html());
        this.editTemplate = Handlebars.compile($('#repair-edit-template').html());
        this.assignTemplate = Handlebars.compile($('#repair-assign-template').html());
        this.notesTemplate = Handlebars.compile($('#repair-notes-template').html());
        this.defaultSort = ['repair_created_at', 'desc'];
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        this.bindEvents();
        this.getList();
    },
    refreshed: false,
    refresh: function (app) {
        this.getList();
    },
    observers: {},
    events: {
        '.action-assign:click': 'eventAssign',
        'form.assign-form:submit': 'eventSubmitAssign',
        '.download-pdf:click': 'eventDownloadPdf',

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
    bindEvents: function () {
        let self = this;
        // $.initialize('.download-pdf', function () {
        //     $(this).unbind('click').on('click', self.eventDownloadPdf.bind(self));
        // }, {target: this.app});
        // $.initialize('input.select-two', function () {
        //     $(this).select2({
        //         query: self.eventSelectTwo.bind(self),
        //         initSelection: function (element, callback) {
        //             if (element.val() !== "") {
        //                 var data = {id: element.val(), text: element.data('name')};
        //                 callback(data);
        //             }
        //         }
        //     });
        // }, {target: this.app});
    },
    afterEventSelect: function (route, event) {
        switch (route) {
            case "filterAssigned":
                this.eventFilter(event);
                break;
            case "repairStatuses":
                this.eventRepairStatus(event);
                break;
            case "equipment":
                this.eventFilter(event);
                break;
        }
    },
    eventRepairStatus: function (event) {
        var formEl = $(event.currentTarget).closest('form');
        if (event.val === repairCompleteStatus.repair_status_id) {
            $(formEl).find('.show-on-complete').show();
        } else {
            $(formEl).find('.show-on-complete').hide();
        }
    },
    eventAssign: function (event) {
        let eq = $('#equipment_item');
        if (eq.length !== 0 && eq.data('equipmentSold') === 1) {
            alert('Item is sold!');
            return;
        }
        let id = event.currentTarget.dataset.id;
        this.getRow(id, this.renderAssign.bind(this));
        let editEl = $(this.app).find('#edit');
        $(editEl).find('form').addClass('assign-form');
        $(editEl).find('.panel-heading').text('Assign User');
        $(editEl).modal('show');
    },
    eventSubmitAssign: function (event) {
        event.preventDefault();
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        D.helper.ajax(
            D.helper.url.route(this.routes.assign),
            $(event.currentTarget).serialize(),
            ['assign', null, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },
    eventUpload: function (event) {
        var textEl = $(event.currentTarget).closest('.modal-body').find('.input-file-text').first();
        $(textEl).val('');
        $.each(event.currentTarget.files, function (key, val) {
            if (val.name)
                $(textEl).val(val.name);
        });
    },
    eventCreate: function (event) {
        event.preventDefault();
        let eq = $('#equipment_item');
        if (eq.length !== 0 && eq.data('equipmentSold') === 1) {
            alert('Item is sold!');
            return;
        }
        this.renderCreate(this.where);
        let title = 'Create';
        if (typeof $(event.currentTarget).attr('title') !== "undefined")
            title = $(event.currentTarget).attr('title');

        $(this.editFormEl).find('form').addClass('create-form');
        $(this.editFormEl).find('.panel-heading').text(title);
        $(this.editFormEl).modal('show');
    },
    callbackSubmitSuccess: function (response, action, id, form) {
        $(form).find('.btn[type=submit]').removeAttr('disabled');
        //$(this.editFormEl).modal('hide');
        if (action === "delete") {
            $(this.app).find('.data-list #row_' + id).remove();
            if (typeof EquipmentProfileApp !== "undefined") {
                let counter = EquipmentProfileApp.getTabCounter('repairs');
                if (counter) {
                    EquipmentProfileApp.changeTabCounter('repairs', counter - 1);
                }
            }
            return;
        }
        if (action === 'edit') {
            successMessage('Repair Request saved successfully');
        }
        if (action === 'create') {
            $(this.editFormEl).modal('hide');
            if (typeof this.noPaginate === "undefined" || !this.noPaginate) {
                this.refreshed = true;
                this.eventPage(null, 1);
            }
        }
        if (action === "assign") {
            $(this.editFormEl).modal('hide');
            this.refresh();
        }
    },
    renderAssign: function (response) {
        $(this.app).find('#edit .modal-footer').before(this.assignTemplate(response));
    },
    renderCreate: function (response) {
        $(this.editFormEl).find('.modal-footer').before(this.createTemplate(response));
    },
    afterRenderEdit: function (pre, response) {
        $(this.editFormEl).find('.modal-footer .download-pdf')
            .attr('href', D.helper.url.route(this.routes.pdf, {id: response.repair_id}))
            .show();

        if (parseInt(response.repair_status_id) === repairCompleteStatus.repair_status_id) {
            $(this.editFormEl).find('.show-on-complete').show();
        }
        $(this.editFormEl).find('.modal-dialog').append(this.notesTemplate({
            "eq_id": response.eq_id,
            "repair_id": response.repair_id,
            "where": JSON.stringify({"repair_id": response.repair_id})
        }));
    },
    afterRenderRows: function (prev, response) {
        if (typeof EquipmentProfileApp !== "undefined") {
            EquipmentProfileApp.changeTabCounter('repairs', response.data.length);
        }
    },
};