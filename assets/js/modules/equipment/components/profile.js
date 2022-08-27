/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [
    'assets/vendors/diez/js/includes/tab-manager.js'
];

exports.modules = [
    '/assets/vendors/diez/js/modules/datepicker.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/select-two.js',
    '/assets/vendors/diez/js/modules/fileinput.js'
];

exports.App = {
    routes: {
        tabs: '/equipment/{id}/{tab}',
        scheduleCheckbox: '/equipment/ajax_set_schedule',
        scheduleToolCheckbox: '/equipment/ajax_set_schedule_tool',
        repairCheckbox: '/equipment/ajax_set_repair',
        uploadThumb: '/equipment/ajax_update_thumb',
        row: '/equipment/ajax_get_item',
        edit: '/equipment/ajax_update_item',
        repairCreate: '/equipment/repairs/ajax_create_repair',
        repairTypes: '/equipment/repairs/ajax_get_types',
        groups: '/equipment/groups/ajax_get_groups',
        repairUploadFile: '/equipment/repairs/ajax_file_upload',
        repairDeleteFile: '/equipment/repairs/ajax_file_delete',
        sale: '/equipment/ajax_sale_item',
        unsold: '/equipment/ajax_unsold_item',
        uploadFile: '/equipment/repairs/ajax_file_upload',
        deleteFile: '/equipment/repairs/ajax_file_delete',
    },
    tabDefault: 'services',

    init: function (app) {
        let self = this;
        this.app = app;
        this.idField = "eq_id";
        this.editFormSelector = '#edit';
        this.id = this.app.dataset.equipmentId;
        this.name = $(this.app).find('#equipment_details .title div').text();
        this.where = {eq_id: this.id};
        //this.detailsEl = $(this.app).find('#equipment_details').get(0);
        //this.asideDetails = $(this.app).find('#aside_details').get(0);
        this.profileTemplate = Handlebars.compile($('#profile-template').html());
        this.editTemplate = Handlebars.compile($('#profile-edit-template').html());
        this.repairTemplate = Handlebars.compile($('#repair-create-template').html());
        this.saleTemplate = Handlebars.compile($('#sale-template').html());
        this.unsoldTemplate = Handlebars.compile($('#unsold-template').html());
        //TabManager.initialize($('#equipment_tabs .nav'));
        //this.bindEvents();
        this.renderProfile(equipmentData);
        // $(window).load(function (event) {
        //     self.loadDefaultActiveTab();
        // });
    },
    observers: {
        // '[data-toggle="tooltip"]': function () {
        //     $(this).tooltip();
        // },
        // '.mycolorpicker': function () {
        //     $(this).colpick({
        //         submit: 0,
        //         colorScheme: 'dark',
        //         onChange: function (hsb, hex, rgb, el, bySetColor) {
        //             $(el).css('background-color', '#' + hex)
        //                 .css('color', D.helper.contrastColor(hex));
        //             if (!bySetColor) {
        //                 $(el).val('#' + hex);
        //             }
        //         }
        //     }).keyup(function () {
        //         $(this).colpickSetColor(this.value);
        //     });
        //     var current_color = $(this).val();
        //     var current_color_short = current_color.replace(/^#/, '');
        //     $(this).colpickSetColor(current_color_short);
        // },
        // '.datepicker': function () {
        //     if (!$(this).val()) {
        //         var now = new Date();
        //         $(this).val(now.format(DATE_FORMAT));
        //     }
        //     $(this).datepicker({
        //         format: DATE_FORMAT,
        //         todayBtn: true,
        //         todayHighlight: true
        //     });
        // },
        // '#edit': function () {
        //     $(this).on('show.bs.modal', function (event) {
        //         //
        //     });
        //     $(this).on('hide.bs.modal', function (event) {
        //         if (event.target == this) {
        //             $(this).find('.modal-dialog').removeClass('complete-modal-dialog');
        //             $(this).find('form').removeClass('edit-form create-form');
        //             $(this).find('.modal-body').remove()
        //         }
        //     });
        // }
    },
    events: {
        '.action-schedule-checkbox:click': 'eventSetScheduleCheckbox',
        '.action-schedule-tool-checkbox:click': 'eventSetScheduleToolCheckbox',
        '.action-repair-checkbox:click': 'eventSetRepairCheckbox',
        '.action-repair:click': 'eventRepairCreate',
        '.action-sale:click': 'eventSale',
        '.action-unsold:click': 'eventUnsold',
        'form.repair-create-form:submit': 'eventRepairSubmitCreate',
        'form.sale-form:submit': 'eventSaleSubmit',
        'form.unsold-form:submit': 'eventUnsoldSubmit',
        '.thumb .action-upload:change': 'eventUploadThumb'
    },
    refresh: function () {
        this.getProfile(this.id);
        if (typeof EquipmentProfileNotesApp !== "undefined") {
            EquipmentProfileNotesApp.refresh();
        }
    },
    bindEvents: function () {
        let self = this;
    },
    eventOnHideModal: function (event, el) {
        if (event.target == el) {
            $(el).find('form').removeClass('edit-form create-form repair-create-form');
            $(el).find('.modal-body').remove();
            //$(el).find('.modal-footer .download-pdf').hide();
            //this.refresh();
        }
    },
    afterCallbackSubmitSuccess: function (prev, response, action, id, form) {
        if (action === "edit") {
            this.refresh();
        }

        if (action === "sale") {
            $(this.app).data('equipmentSold', 1);
            this.refresh();
            if (typeof EquipmentProfileTabServicesApp !== "undefined") {
                EquipmentProfileTabServicesApp.refresh();
            }
        }
        if (action === "unsold") {
            $(this.app).data('equipmentSold', 0);
            this.refresh();
        }
        if (action === "repairCreate") {
            if (typeof EquipmentProfileTabRepairsApp !== "undefined") {
                if ($(EquipmentProfileTabRepairsApp.app).parent().hasClass('active')) {
                    EquipmentProfileTabRepairsApp.refresh();
                }
            }
        }
    },
    eventSetScheduleCheckbox: function (event) {
        event.preventDefault();
        var input = $(event.currentTarget).children('input');
        let self = this;
        D.helper.ajax(
            D.helper.url.route(this.routes.scheduleCheckbox),
            {[this.idField]: this.id},
            [],
            function (response) {
                //$(input).attr('checked', response.checked);
                self.refresh();
            },
            function () {
                event.stopPropagation();
            },
            "POST",
            $(event.currentTarget).parent()
        );
    },
    eventSetScheduleToolCheckbox: function (event) {
        event.preventDefault();
        var input = $(event.currentTarget).children('input');
        let self = this;
        if ($(input).attr('disabled')) {
            return;
        }

        D.helper.ajax(
            D.helper.url.route(this.routes.scheduleToolCheckbox),
            {[this.idField]: this.id},
            [],
            function (response) {
                //$(input).attr('checked', response.checked);
                self.refresh();
            },
            function () {
                event.stopPropagation();
            },
            "POST",
            $(event.currentTarget).parent()
        );
    },
    eventSetRepairCheckbox: function (event) {
        event.preventDefault();
        //event.stopPropagation();
        var input = $(event.currentTarget).children('input');
        let self = this;
        D.helper.ajax(
            D.helper.url.route(this.routes.repairCheckbox),
            {[this.idField]: this.id},
            [],
            function (response) {
                //$(input).attr('checked', response.checked);
                self.refresh();
            },
            function () {
                event.stopPropagation();
            },
            "POST",
            $(event.currentTarget).parent()
        );
    },
    eventUploadThumb: function (event) {
        event.preventDefault();
        event.stopPropagation();
        if (typeof event.currentTarget.files == 'undefined')
            return;
        var data = new FormData();
        data.append('thumb', event.currentTarget.files[0]);
        data.append(this.idField, this.id);
        // var input = $(event.currentTarget).children('input');
        D.helper.ajax(
            D.helper.url.route(this.routes.uploadThumb),
            data,
            [event.currentTarget],
            function (response) {
                $(event.currentTarget).siblings("img").attr('src', response.thumb);
            },
            function () {
                console.log('error')
            },
            "POST",
            this.app
        );
    },
    eventRepairCreate: function (event) {
        this.renderRepairCreate(this.where);
        let editEl = $(this.app).find(this.editFormSelector);
        $(editEl).find('form').addClass('repair-create-form');
        let title = 'Create Repair Request';
        if (typeof $(event.currentTarget).attr('title') !== "undefined")
            title = $(event.currentTarget).attr('title');
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
    eventSale: function (event) {
        this.renderSale(this.where);
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
    getProfile: function (id, successCallback = null, loader = null) {
        if (successCallback === null)
            successCallback = this.renderProfile.bind(this);
        if (loader === null)
            loader = this.app;
        D.helper.ajax(D.helper.url.route(this.routes.row), {
            eq_id: id,
        }, [id], successCallback, false, "POST", loader)
    },
    changeTabCounter: function (name, counter) {
        if (typeof EquipmentProfileTabsApp !== "undefined") {
            let badge = $(EquipmentProfileTabsApp.app).find('.nav a[href=#' + name + '] .badge');
            if (badge.length === 0)
                return;
            $(badge).text(counter);
        }
    },
    getTabCounter: function (name) {
        if (typeof EquipmentProfileTabsApp !== "undefined") {
            let badge = $(EquipmentProfileTabsApp.app).find('.nav a[href=#' + name + '] .badge');
            if (badge.length === 0)
                return false;
            return parseInt($(badge).text());
        }
        return false;
    },
    renderProfile: function (row) {
        $(this.app).find('.eq-details').html(this.profileTemplate(row));
        this.name = $(this.app).find('.eq-details .title').text();
    },
    renderRepairCreate: function (response) {
        $(this.app).find(this.editFormSelector + ' .modal-footer').before(this.repairTemplate(response));
        if ("multiRowsModule" in this._modules) {
            this.eventFillData(response);
        }
    },
    renderSale: function (response) {
        $(this.app).find(this.editFormSelector + ' .modal-footer').before(this.saleTemplate(response));
    },
    renderUnsold: function (response) {
        $(this.app).find(this.editFormSelector + ' .modal-footer').before(this.unsoldTemplate(response));
    },
    afterEventSelect: function (route, event) {
        switch (route) {
            case "groups":
                this.eventSelectGroup(event);
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
    }
};