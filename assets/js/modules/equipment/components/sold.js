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
        list: '/equipment/ajax_get_sold_equipment',
        row: '/equipment/ajax_get_item',
        edit: '/equipment/ajax_update_item',
        create: '/equipment/ajax_create_item',
        delete: '/equipment/ajax_delete_item',
        unsold: '/equipment/ajax_unsold_item',
        groups: '/equipment/groups/ajax_get_groups',
    },
    init: function (app) {
        this.app = app;
        this.idField = "eq_id";
        this.editFormSelector = "#edit";
        this.rowTemplate = Handlebars.compile($('#row-template').html());
        this.editTemplate = Handlebars.compile($('#profile-edit-template').html());
        this.unsoldTemplate = Handlebars.compile($('#unsold-template').html());
        this.defaultSort = ['eq_created_at', 'desc'];
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        this.getList();
    },
    observers: {},
    events: {
        '.action-unsold:click': 'eventUnsold',
        'form.unsold-form:submit': 'eventUnsoldSubmit',
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
        if (action === "unsold") {
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
    eventUnsold: function (event) {
        //this.renderUnsold(this.where);
        let editEl = $(this.app).find(this.editFormSelector);
        if ($(event.currentTarget).data("id") === undefined)
            return;
        let id = $(event.currentTarget).data("id");
        this.getFormItem(id, this.renderUnsold.bind(this), editEl);
        $(editEl).find('form').addClass('unsold-form');
        let title = 'Unsold Equipment';
        if (typeof $(event.currentTarget).attr('title') !== "undefined")
            title = $(event.currentTarget).attr('title');
        if ($(editEl).find('.panel-heading span').length !== 0) {
            $(editEl).find('.panel-heading span').text(title);
        } else {
            $(editEl).find('.panel-heading').text(title);
        }
        $(editEl).modal('show');
    },
    eventUnsoldSubmit: function (event) {
        event.preventDefault();
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.unsold),
            data,
            ['unsold', null, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },
    renderUnsold: function (response) {
        $(this.app).find(this.editFormSelector + ' .modal-footer').before(this.unsoldTemplate(response));
    }
};