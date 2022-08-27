/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [];

exports.deferred = true;

exports.App = {
    routes: {
        // list: 'equipments/services/ajax_get_services',
        // row: 'equipments/services/ajax_get_service',
        // create: 'equipments/services/ajax_create_service',
        // edit: 'equipments/services/ajax_update_service',
        // delete: 'equipments/services/ajax_delete_service',
        // complete: 'equipments/services/ajax_complete_service',
        // postpone: 'equipments/services/ajax_postpone_service',
        // types: '/equipments/services/ajax_get_service_types',
        // users: 'user/ajax_get_users',
    },
    init: function (app) {
        this.app = app;
        this.id = this.app.dataset.id;
        this.employeeRowTemplate = Handlebars.compile($('#form-multi-employee-row-template').html());
        this.partRowTemplate = Handlebars.compile($('#form-multi-part-row-template').html());
        this.fileRowTemplate = Handlebars.compile($('#form-multi-file-row-template').html());
        this.bindEvents();
        this.eventFillData(D.exchanger.get('multiRowsData'));
    },
    refresh: function (app) {
        this.init(app);
    },
    bindEvents: function () {
        let self = this;
        $.initialize('.action-add-employee-row', function () {
            $(this).unbind('click').on('click', self.eventAddEmployeeRow.bind(self));
        }, {target: this.app});
        $.initialize('.action-delete-employee-row', function () {
            $(this).unbind('click').on('click', self.eventDeleteEmployeeRow.bind(self));
        }, {target: this.app});
        $.initialize('.action-add-part-row', function () {
            $(this).unbind('click').on('click', self.eventAddPartRow.bind(self));
        }, {target: this.app});
        $.initialize('.action-delete-part-row', function () {
            $(this).unbind('click').on('click', self.eventDeletePartRow.bind(self));
        }, {target: this.app});
        $.initialize('.action-add-file-row', function () {
            $(this).unbind('click').on('click', self.eventAddFileRow.bind(self));
        }, {target: this.app});
        $.initialize('.action-delete-file-row', function () {
            $(this).unbind('click').on('click', self.eventDeleteFileRow.bind(self));
        }, {target: this.app});
        $.initialize('.file-row-input', function () {
            $(this).unbind('change').on('change', self.eventInputFileRow.bind(self));
        }, {target: this.app});
        $.initialize('.part-price', function () {
            $(this).unbind('change').on('change', self.eventCalcPartPrice.bind(self));
        }, {target: this.app});
        $.initialize('.part-tax', function () {
            $(this).unbind('change').on('change', self.eventSelectPartTax.bind(self));
        }, {target: this.app});
        $.initialize('.employee-hours', function () {
            $(this).unbind('change').on('change', self.eventCalcEmployeePrice.bind(self));
        }, {target: this.app});
        $.initialize('.employee-hourly-rate', function () {
            $(this).unbind('change').on('change', self.eventCalcEmployeePrice.bind(self));
        }, {target: this.app});
        $.initialize('.action-upload', function () {
            $(this).on('change', self.eventUpload.bind(self));
        }, {target: this.app});
        // $.initialize('.action-file-row', function () {
        //     $(this).on('click', function () {
        //         var f = $(this).attr('for');
        //         $(self.app).find('input#'+f).click();
        //     });
        // }, {target: this.app});
        $.initialize('input.select-two', function () {
            $(this).unbind('select2-selecting').on('select2-selecting', self.eventSelect.bind(self));
        }, {target: this.app});
    },
    eventFillData: function (data) {
        let self = this;
        if (data && typeof data.employees !== "undefined" && Array.isArray(data.employees)) {
            $(data.employees).each(function () {
                this.num = this.emp_id;
                self.eventAddEmployeeRow(null, this);
            });
            this.eventCalcEmployeePrice(null);
        } else {
            this.eventAddEmployeeRow();
        }
        if (data && typeof data.parts !== "undefined" && Array.isArray(data.parts)) {
            $(data.parts).each(function () {
                this.num = this.part_id;
                self.eventAddPartRow(null, this);
            });
            this.eventCalcPartPrice(null);
        } else {
            this.eventAddPartRow();
        }
    },
    eventAddEmployeeRow: function (event, row) {
        let id = 'new_' + Date.now();
        let data = {num: id};
        if (typeof row !== "undefined")
            data = row;
        this.renderEmployeeRow(data);
    },
    eventDeleteEmployeeRow: function (event, row) {
        let id = $(event.currentTarget).data('num');
        $(this.app).find('.employee-' + id).remove();
        this.eventCalcEmployeePrice();
    },
    eventAddPartRow: function (event, row) {
        let id = 'new_' + Date.now();
        let data = {num: id};
        if (typeof row !== "undefined")
            data = row;
        this.renderPartRow(data);
    },
    eventDeletePartRow: function (event) {
        let id = $(event.currentTarget).data('num');
        $(this.app).find('.part-' + id).remove();
        this.eventCalcPartPrice();
    },
    eventAddFileRow: function (event, row) {
        let id = 'new_' + Date.now();
        let data = {num: id};
        if (typeof row !== "undefined")
            data = row;
        this.renderFileRow(data);
    },
    eventDeleteFileRow: function (event) {
        let id = $(event.currentTarget).data('num');
        $(this.app).find('.file-' + id).remove();
    },
    eventInputFileRow: function (event) {
        var btnEl = $(event.currentTarget).closest('div').find('.input-file-text').first();
        $(btnEl).val('');
        var file = $(event.currentTarget.files).get(0);
        if (typeof file !== "undefined") {
            $(btnEl).val(file.name);
        } else {
            $(btnEl).val("");
        }
    },
    eventUpload: function (event) {
        var btnEl = $(event.currentTarget).closest('.btn-file').first();
        $(btnEl).val('');
        var file = $(event.currentTarget.files).get(0);
        if (typeof file !== "undefined") {
            $(btnEl)
                .removeClass('btn-default btn-success')
                .addClass('btn-success')
                .attr('title', file.name)
                .find('span').text(file.name);
        } else {
            $(btnEl)
                .removeClass('btn-default btn-success')
                .addClass('btn-default')
                .attr('title', 'Select')
                .find('span').text('Select');
        }
    },
    eventSelectPartTax: function (event) {
        let taxEl = $(event.currentTarget).children("option:selected");
        let rate = 1 + (parseFloat($(taxEl).data('tax')) / 100);
        $(event.currentTarget).siblings('.part-tax-rate').val(D.helper.math.round(rate, 2));
        this.eventCalcPartPrice(event);
    },
    eventCalcPartPrice: function (event) {
        var total = 0;
        var tax = 0;
        $(this.app).find('.parts .part').each(function (idx, row) {
            let cost = parseFloat($(row).find('.part-price').val());
            let taxEl = $(row).find('.part-tax').children("option:selected");
            let curTax = (cost / 100) * parseFloat($(taxEl).data('tax'));
            total += (cost + curTax);
            tax += curTax;
        });
        $(this.app).find('.part-total-tax').val(D.helper.math.round(tax, 2));
        $(this.app).find('.part-total').val(D.helper.math.round(total, 2));
        this.eventCalcTotalPrice();
    },
    eventCalcEmployeePrice: function (event) {
        var total = 0;
        $(this.app).find('.employees .employee').each(function (idx, row) {
            let hours = parseFloat($(row).find('.employee-hours').val());
            let rate = parseFloat($(row).find('.employee-hourly-rate').val());
            let cost = D.helper.math.round((hours * rate), 2);
            $(row).find('.employee-price').val(cost);
            total += cost;

        });
        $(this.app).find('.employee-total').val(D.helper.math.round(total, 2));
        this.eventCalcTotalPrice();
    },
    eventCalcTotalPrice: function (event) {
        let partTotal = parseFloat($(this.app).find('.part-total').val());
        let employeeTotal = parseFloat($(this.app).find('.employee-total').val());
        $(this.app).find('.total-price').val(D.helper.math.round(partTotal + employeeTotal, 2));
    },
    eventSelect: function (event) {
        var route = $(event.currentTarget).data('selectRoute');
        switch (route) {
            case "users":
                this.eventSelectUser(event);
        }
    },
    eventSelectUser: function (event) {
        var num = $(event.currentTarget).data('num');
        $(this.app).find('.row.employee-' + num + ' .employee-hourly-rate').val(event.object.employee.emp_hourly_rate);
    },
    renderEmployeeRow: function (response) {
        $(this.app).find('.employees .employee-footer').before(this.employeeRowTemplate(response))
    },
    renderPartRow: function (response) {
        $(this.app).find('.parts .part-footer').before(this.partRowTemplate(response))
    },
    renderFileRow: function (response) {
        $(this.app).find('.files .file-footer').before(this.fileRowTemplate(response))
    }
};