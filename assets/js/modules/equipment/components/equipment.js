/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [
    'assets/js/colpick.js',
];

exports.modules = [
    '/assets/vendors/diez/js/modules/colorpicker.js',
    '/assets/vendors/diez/js/modules/datepicker.js',
    '/assets/vendors/diez/js/modules/table.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/filter.js',
    '/assets/vendors/diez/js/modules/select-two.js'
];

exports.App = {
    routes: {
        list: '/equipment/ajax_get_equipment',
        row: '/equipment/ajax_get_item',
        edit: '/equipment/ajax_update_item',
        create: '/equipment/ajax_create_item',
        delete: '/equipment/ajax_delete_item',
        groups: '/equipment/groups/ajax_get_groups',
        sale: '/equipment/ajax_sale_item',
    },
    init: function (app) {
        this.app = app;
        this.idField = "eq_id";
        this.editFormSelector = "#edit";
        this.rowTemplate = Handlebars.compile($('#row-template').html());
        this.editTemplate = Handlebars.compile($('#profile-edit-template').html());
        this.saleTemplate = Handlebars.compile($('#sale-template').html());
        this.defaultSort = ['eq_created_at', 'desc'];
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        this.getList();
    },
    observers: {},
    events: {
        '.action-sale:click': 'eventSale',
        'form.sale-form:submit': 'eventSaleSubmit',
        '.action-edit-schedule-checkbox:click': 'eventSetEditScheduleCheckbox',
        //'.action-edit-switch-tool-checkbox:click': 'eventSale',
    },
    refresh: function () {
        this.getList();
    },
    afterEventSelect: function (route, event) {
        switch (route) {
            case "groups":
                this.eventSelectGroup(event);
        }
    },
    afterCallbackSubmitSuccess: function (prev, response, action, id, form) {
        if (action === "edit") {
            this.refresh();
        }
        if (action === "sale") {
            this.refresh();
        }
    },
    eventSelectGroup: function (event) {
        var formEl = $(event.currentTarget).closest('form');
        if (typeof event.choice === "undefined")
            return;
        if (event.choice.group_prefix === "" || event.choice.group_prefix === null) {
            $(formEl).find('[name=eq_prefix]').val("").removeAttr('readonly');
        } else {
            $(formEl).find('[name=eq_prefix]').val(event.choice.group_prefix).attr('readonly', 'readonly');
        }
    },
    eventSale: function (event) {
        if ($(event.currentTarget).data("id") === undefined)
            return;
        let id = $(event.currentTarget).data("id");
        this.renderSale({eq_id: id});
        let editEl = $(this.app).find(this.editFormSelector);
        $(editEl).find('form').addClass('sale-form');
        let title = 'Sale Equipment';
        if (typeof $(event.currentTarget).attr('title') !== "undefined")
            title = $(event.currentTarget).attr('title');
        if ($(editEl).find('.panel-heading span').length !== 0) {
            $(editEl).find('.panel-heading span').text(title);
        } else {
            $(editEl).find('.panel-heading').text(title);
        }
        $(editEl).modal('show');
    },
    eventSaleSubmit: function (event) {
        event.preventDefault();
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.sale),
            data,
            ['sale', null, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },
    eventSetEditScheduleCheckbox: function (event) {
        var input = $(event.currentTarget);
        let self = this;

        if ($(input).prop('checked')) {
            //$(input).prop('checked', true);
            $('.input-tool-checkbox').removeAttr('disabled');
            $('.input-tool-checkbox').parents('.switch').removeClass('disabled');
        } else {
            //$(input).prop('checked', false);
            $('.input-tool-checkbox').attr('disabled', 'disbaled');
            $('.input-tool-checkbox').parents('.switch').addClass('disabled');
            $('.input-tool-checkbox').prop('checked', false);
        }

    },
    renderSale: function (response) {
        $(this.app).find(this.editFormSelector + ' .modal-footer').before(this.saleTemplate(response));
    },
};