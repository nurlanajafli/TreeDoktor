/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [];

exports.modules = [
    '/assets/vendors/diez/js/modules/datepicker.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/select-two.js',
    '/assets/vendors/diez/js/modules/fileinput.js'
];

exports.App = {
    routes: {
        repairCreate: '/equipment/repairs/ajax_create_repair',
        repairTypes: '/equipment/repairs/ajax_get_types',
        equipment: '/equipment/ajax_get_equipment',
        repairUploadFile: '/equipment/repairs/ajax_file_upload',
        repairDeleteFile: '/equipment/repairs/ajax_file_delete',
    },
    init: function (app) {
        let self = this;
        this.app = app;
        this.idField = "eq_id";
        this.editFormSelector = '#repairCreate';
        this.where = {};
        this.repairTemplate = Handlebars.compile($('#repair-create-template').html());
    },
    observers: {},
    events: {
        'form.repair-create-form:submit': 'eventRepairSubmitCreate',
    },
    refresh: function () {
    },
    bindEvents: function () {
    },
    eventOnHideModal: function (event, el) {
        if (event.target == el) {
            $(el).find('form').removeClass('edit-form create-form repair-create-form');
            $(el).find('.modal-body').remove();
        }
    },
    eventRepairCreate: function (event) {
        this.renderRepairCreate(this.where);
        let editEl = $(this.app).find(this.editFormSelector);
        $(editEl).find('form').addClass('repair-create-form');
        let title = 'Create Repair Request';
        if ($(this.editFormEl).find('.panel-heading span').length !== 0) {
            $(this.editFormEl).find('.panel-heading span').text(title);
        } else {
            $(this.editFormEl).find('.panel-heading').text(title);
        }
        $(editEl).modal('show');
    },
    eventRepairSubmitCreate: function (event) {
        event.preventDefault();
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.repairCreate),
            data,
            ['repairCreate', null, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },
    renderRepairCreate: function (response) {
        $(this.app).find(this.editFormSelector + ' .modal-footer').before(this.repairTemplate(response));
        if ("multiRowsModule" in this._modules) {
            this.eventFillData(response);
        }
    },
};