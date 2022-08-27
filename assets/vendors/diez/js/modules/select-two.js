/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "selectTwo";

exports.scripts = [];

exports.Module = {
    component: undefined,
    init: function () {
        console.log('ys');
    },
    observers: {
        'input.select-two': function (app) {
            $(this).select2({
                query: app.eventSelectTwo.bind(app),
                allowClear: true,
                initSelection: function (element, callback) {
                    if (element.val() !== "") {
                        var data = {id: element.val(), text: element.data('name')};
                        callback(data);
                    }
                }
            });
        },
        'input.select-two-multiple': function (app) {
            $(this).select2({
                query: app.eventSelectTwo.bind(app),
                multiple: true,
                separator: "|",
                allowClear: true,
                initSelection: function (element, callback) {
                    if (element.val() !== "") {
                        var data = {id: element.val(), text: element.data('name')};
                        callback(data);
                    }
                }
            });
        }
    },
    events: {
        'input.select-two:select2-selecting': 'eventSelect',
        'input.select-two:select2-clearing': 'eventSelect',
        'input.select-two:select2-loaded': function (event) {
            console.log('loaded', event);
        },
    },
    eventSelectTwo: function (event) {
        console.log(event);
        var route = event.element.data('selectRoute');
        if (!route) {
            event.callback({results: []});
            return;
        }
        D.helper.ajax(
            D.helper.url.route(this.routes[route]),
            {
                query: event.term,
                page: event.page,
            },
            [route, event],
            this.callbackSelectTwo.bind(this),
            false,
            "POST",
            false
        )
    },
    eventSelect: function (event) {
        var route = $(event.currentTarget).data('selectRoute');
        switch (route) {
            case "users":
                this.eventSelectUser(event);
                break;
        }
        return route;
    },
    eventSelectUser: function (event) {
        var num = $(event.currentTarget).data('num');
        /*if (typeof event.object !== "undefined" && typeof event.object.employee !== "undefined" && event.object.employee !== null) {
            $(this.app).find('.row.employee-' + num + ' .employee-hourly-rate').val(event.object.employee.emp_hourly_rate);
        }*/
    },
    callbackSelectTwo: function (response, action, event) {
        var data = $.map(response.data, function (obj) {
            switch (action) {
                case "groups":
                    obj.id = obj.id || obj.group_id;
                    obj.text = obj.text || obj.group_name;
                    break;
                case "equipment":
                    obj.id = obj.id || obj.eq_id;
                    obj.text = obj.text || (typeof obj.group !== "undefined" && obj.group !== null ? obj.group.group_name + '/' : "") + obj.eq_prefix + ' ' + obj.eq_name;
                    break;
                case "serviceTypes":
                    obj.id = obj.id || obj.service_type_id;
                    obj.text = obj.text || obj.service_type_name;
                    break;
                case "users":
                    obj.text = obj.text || obj.firstname + " " + obj.lastname;
                    break;
                case "repairTypes":
                    obj.id = obj.id || obj.repair_type_id;
                    obj.text = obj.text || obj.repair_type_name;
                    break;
                case "repairStatuses":
                    obj.id = obj.id || obj.repair_status_id;
                    obj.text = obj.text || obj.repair_status_name;
                    break;
                case "filterAssigned":
                    obj.text = obj.text || obj.firstname + " " + obj.lastname;
                    break;
            }
            if (event.element.val() == obj.id)
                obj.selected = true;
            return obj;
        });
        var more = (event.page * 20) < response.total;
        event.callback({
            results: data,
            more: more
        });
    }
};
