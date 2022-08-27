/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "form";

exports.scripts = [];

exports.Module = {
    component: undefined,
    where: {},
    init: function () {
        this.editFormEl = $(this.app).find(typeof this.editFormSelector !== "undefined" ? this.editFormSelector : "#edit");
        if (typeof this.idField === "undefined")
            this.idField = "id";
        this.bindEvents();

    },
    observers: {},
    events: {
        '.action-create:click': 'eventCreate',
        '.action-edit:click': 'eventEdit',
        '.action-delete:click': 'eventDelete',
        'form.edit-form:submit': 'eventSubmitEdit',
        'form.create-form:submit': 'eventSubmitCreate',
        '.input-file:change': 'eventUpload',
    },
    bindEvents: function () {
        let self = this;
        let tmpSel = typeof this.editFormSelector !== "undefined" ? this.editFormSelector : "#edit";
        this.observers[tmpSel] = function () {
            $(this).on('show.bs.modal', function (event) {
                self.eventOnShowModal(event, this)
            });
            $(this).on('hide.bs.modal', function (event) {
                self.eventOnHideModal(event, this)
            });
        };

        this.events[tmpSel + ':keydown'] = "eventPressEnter";
    },
    eventOnShowModal: function (event, el) {
        if (event.target == el) {
            $(el).find('.btn[type=submit]').removeAttr('disabled');
        }
    },
    eventOnHideModal: function (event, el) {
        if (event.target == el) {
            $(el).find('.modal-dialog').removeClass('edit-modal-dialog create-modal-dialog');
            $(el).find('form').removeClass('edit-form create-form');
            $(el).find('.modal-body').remove()
            if (typeof this.component.refreshed !== "undefined" && this.component.refreshed) {
                this.component.refreshed = false;
            } else {
                this.refresh();
            }
        }
    },
    eventPressEnter: function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            event.stopPropagation();
            if (event.target.nodeName.toLowerCase() !== "textarea") {
                $(event.currentTarget).find('form').submit();
            }
        }
    },
    eventCreate: function (event) {
        event.preventDefault();
        let eq = $('#equipment_item');
        if (eq.length !== 0 && eq.data('equipmentSold') === 1) {
            alert('Item is sold!');
            return;
        }
        this.renderEdit(this.where);
        let title = 'Create';
        if (typeof $(event.currentTarget).attr('title') !== "undefined")
            title = $(event.currentTarget).attr('title');

        $(this.editFormEl).find('form').addClass('create-form');
        if ($(this.editFormEl).find('.panel-heading span').length !== 0) {
            $(this.editFormEl).find('.panel-heading span').text(title);
        } else {
            $(this.editFormEl).find('.panel-heading').text(title);
        }
        $(this.editFormEl).modal('show');
    },
    eventSubmitCreate: function (event) {
        event.preventDefault();
        $(event.currentTarget).find('.btn[type=submit]').attr('disabled', 'disabled');
        $(event.currentTarget).find('.help-block').text("").parent().removeClass('has-error');
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.create),
            data,
            ['create', null, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },
    eventEdit: function (event) {
        event.preventDefault();
        if ($(event.currentTarget).data("id") === undefined)
            return;
        let id = $(event.currentTarget).data("id");
        let title = 'Edit';
        if (typeof $(event.currentTarget).attr('title') !== "undefined")
            title = $(event.currentTarget).attr('title');

        this.getFormItem(id, this.renderEdit.bind(this), this.editFormEl);

        $(this.editFormEl).find('.modal-dialog').addClass('edit-modal-dialog');
        $(this.editFormEl).find('form').addClass('edit-form');
        if ($(this.editFormEl).find('.panel-heading span').length !== 0) {
            $(this.editFormEl).find('.panel-heading span').text(title);
        } else {
            $(this.editFormEl).find('.panel-heading').text(title);
        }
        $(this.editFormEl).modal('show');
    },
    eventSubmitEdit: function (event) {
        event.preventDefault();
        $(event.currentTarget).find('.btn[type=submit]').attr('disabled', 'disabled');
        $(event.currentTarget).find('.help-block').text("").parent().removeClass('has-error');
        let id = $(event.currentTarget).find('input[name=' + this.idField + ']').val();
        let modal = $(event.currentTarget).parents('.modal-dialog').first();
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.edit),
            data,
            ['edit', id, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            modal
        );
    },
    eventDelete: function (event) {
        event.preventDefault();
        if ($(event.currentTarget).data("id") === undefined)
            return;
        let id = $(event.currentTarget).data("id");
        let question = 'Are you sure delete #' + id + "?";
        if (typeof $(event.currentTarget).attr('title') !== "undefined") {
            question = 'Are you sure you want to remove "' + $(event.currentTarget).attr('title') + '"?';
        }
        if (confirm(question)) {
            D.helper.ajax(
                D.helper.url.route(this.routes.delete),
                {[this.idField]: id},
                ['delete', id],
                this.callbackSubmitSuccess.bind(this),
                this.callbackSubmitError.bind(this),
                "POST",
                this.app
            );
        }
    },
    eventUpload: function (event) {
        var textEl = $(event.currentTarget).closest('div').find('.input-file-text').first();
        $(textEl).val('');
        $.each(event.currentTarget.files, function (key, val) {
            if (val.name)
                $(textEl).val(val.name);
        });
    },
    getFormItem(id, successCallback = null, loader) {
        if (successCallback === null)
            successCallback = this.renderEdit.bind(this);
        if (loader === null)
            loader = this.app;
        D.helper.ajax(D.helper.url.route(this.routes.row), {
                [this.idField]: id,
            },
            [id],
            successCallback,
            false,
            "POST",
            loader
        )
    },
    callbackSubmitSuccess: function (response, action, id, form) {
        $(form).find('.btn[type=submit]').removeAttr('disabled');
        $(this.editFormEl).modal('hide');
        if (action === 'create') {
            if (typeof this.noPaginate === "undefined" || !this.noPaginate) {
                this.component.refreshed = true;
                this.eventPage(null, 1);
            }
        }
    },
    callbackSubmitError: function (error, errors, action, id, form) {
        if (typeof form === "undefined") {
            if (error !== null)
                errorMessage(error);
            return;
        }
        if (error !== null) {
            $(form).find('.feedback')
                .addClass('has-error')
                .find('.help-block')
                .text("Error: " + error);

        }
        if (errors !== null) {
            $.each(errors, function (field, value) {
                if (Array.isArray(value))
                    value = value.join('<br />');
                $(form).find('#' + field + '-error')
                    .text(value)
                    .parent()
                    .addClass('has-error');
            });
        }
        if (error === null || error.substr(0, 1) !== "!") {
            $(form).find('.btn[type=submit]').removeAttr('disabled');
        }
    },
    renderEdit: function (response) {
        $(this.editFormEl).find('.modal-footer').before(this.editTemplate(response));
        if ("multiRowsModule" in this._modules) {
            this.eventFillData(response);
        }
    }
};