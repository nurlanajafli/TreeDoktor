/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "colorpicker";

exports.scripts = [];

exports.Module = {
    component: undefined,
    init: function () {
        //
    },
    observers: {
        '.mycolorpicker': function () {
            $(this).colpick({
                submit: 0,
                colorScheme: 'dark',
                onChange: function (hsb, hex, rgb, el, bySetColor) {
                    $(el).css('background-color', '#' + hex)
                        .css('color', D.helper.contrastColor(hex));
                    if (!bySetColor) {
                        $(el).val('#' + hex);
                    }
                }
            }).keyup(function () {
                $(this).colpickSetColor(this.value);
            });
            var current_color = $(this).val();
            var current_color_short = current_color.replace(/^#/, '');
            $(this).colpickSetColor(current_color_short);
        },
    },
    events: {
        //
    }
};