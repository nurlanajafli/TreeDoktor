/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "datepicker";

exports.scripts = [];

exports.Module = {
    component: undefined,
    init: function () {
        //
    },
    observers: {
        '.datepicker': function () {
            let now = moment();
            if (!$(this).val() && !$(this).data('empty')) {
                $(this).val(now.format(MOMENT_DATE_FORMAT));
            }
            let options = {
                format: DATE_FORMAT,
                todayBtn: 'linked',
                todayHighlight: true,
                autoclose: true,
                immediateUpdates: true,
                clearBtn: true,
            };
            if (typeof $(this).data('dateEndDate') === "undefined") {
                options['endDate'] = now.format(MOMENT_DATE_FORMAT);
            }
            $(this).datepicker(options);
        },
    },
    events: {
        //
    }
};