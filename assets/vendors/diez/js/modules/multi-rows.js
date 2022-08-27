/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "multiRows";

exports.scripts = [
    'assets/vendors/kartik-v/fileinput/js/fileinput.js',
];

exports.Module = {
    component: undefined,
    init: function () {
        this.employeeRowTemplate = Handlebars.compile($('#form-multi-employee-row-template').html());
        this.partRowTemplate = Handlebars.compile($('#form-multi-part-row-template').html());
        this.fileRowTemplate = Handlebars.compile($('#form-multi-file-row-template').html());
    },
    observers: {
        //
    },
    events: {
        '.action-add-employee-row:click': 'eventAddEmployeeRow',
        '.action-delete-employee-row:click': 'eventDeleteEmployeeRow',
        '.employee-hours': {
            'keyup': 'eventCalcEmployeePrice',
            'change': 'eventCalcEmployeePrice'
        },
        '.employee-hourly-rate': {
            'keyup': 'eventCalcEmployeePrice',
            'change': 'eventCalcEmployeePrice'
        },
        '.action-add-part-row:click': 'eventAddPartRow',
        '.action-delete-part-row:click': 'eventDeletePartRow',
        '.part-price': {
            'keyup': 'eventCalcPartPrice',
            'change': 'eventCalcPartPrice'
        },
        '.part-tax:change': 'eventSelectPartTax',

        '.action-add-file-row:click': 'eventAddFileRow',
        '.action-delete-file-row:click': 'eventDeleteFileRow',
        '.file-row-input:change': 'eventInputFileRow',

        '.part .action-upload:change': 'eventPartUpload',

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
            //this.eventAddEmployeeRow();
        }
        if (data && typeof data.parts !== "undefined" && Array.isArray(data.parts)) {
            $(data.parts).each(function () {
                this.num = this.part_id;
                self.eventAddPartRow(null, this);
            });
            this.eventCalcPartPrice(null);
        } else {
            //this.eventAddPartRow();
        }

        if (data && typeof data.files !== "undefined" && Array.isArray(data.files)) {
            if (self.component._loadedModules.includes('fileInputModule')) {
                self.eventInitFiles(data.files);
            }
            $(data.files).each(function () {
                this.num = this.file_id;
                self.eventAddFileRow(null, this);
            });
        } else {
            //this.eventAddFileRow();
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
    eventPartUpload: function (event) {
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
        let rate = parseFloat($(taxEl).data('tax')) || 1;
        $(event.currentTarget).siblings('.part-tax-rate').val(D.helper.math.round(rate, 2));
        this.eventCalcPartPrice(event);
    },
    eventCalcPartPrice: function (event) {
        var total = 0;
        var tax = 0;
        $(this.app).find('.parts .part').each(function (idx, row) {
            let cost = parseFloat($(row).find('.part-price').val()) || 0;
            let taxEl = $(row).find('.part-tax').children("option:selected");
            let curTax = (cost * (parseFloat($(taxEl).data('tax')) || 1)) - cost;
            total += (cost + curTax);
            tax += curTax;
        });
        $(this.app).find('.part-total-tax').val(D.helper.math.round(tax, 2).toFixed(2));
        $(this.app).find('.part-total').val(D.helper.math.round(total, 2).toFixed(2));
        this.eventCalcTotalPrice();
    },
    eventCalcEmployeePrice: function (event) {
        var total = 0;
        $(this.app).find('.employees .employee').each(function (idx, row) {
            let hours = parseFloat($(row).find('.employee-hours').val()) || 0;
            let rate = parseFloat($(row).find('.employee-hourly-rate').val()) || 0;
            let cost = D.helper.math.round((hours * rate), 2);
            $(row).find('.employee-price').val(cost.toFixed(2));
            total += cost;

        });
        $(this.app).find('.employee-total').val(D.helper.math.round(total, 2).toFixed(2));
        this.eventCalcTotalPrice();
    },
    eventCalcTotalPrice: function (event) {
        let partTotal = parseFloat($(this.app).find('.part-total').val()) || 0;
        let employeeTotal = parseFloat($(this.app).find('.employee-total').val()) || 0;
        $(this.app).find('.total-price').val(D.helper.math.round(partTotal + employeeTotal, 2).toFixed(2));
    },
    // eventSelect: function (event) {
    //     var route = $(event.currentTarget).data('selectRoute');
    //     switch (route) {
    //         case "users":
    //             this.eventSelectUser(event);
    //     }
    // },
    // eventSelectUser: function (event) {
    //     var num = $(event.currentTarget).data('num');
    //     $(this.app).find('.row.employee-' + num + ' .employee-hourly-rate').val(event.object.employee.emp_hourly_rate);
    // },
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