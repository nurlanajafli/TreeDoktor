/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "pdf";

exports.scripts = [];

exports.Module = {
    component: undefined,
    init: function () {
        //
    },
    observers: {},
    events: {
        '.download-pdf:click': 'eventDownloadPdf',
    },
    eventDownloadPdf: function (event) {
        event.preventDefault();
        $(event.currentTarget).closest('.modal-footer').append('<div id="loading">\n' + '<img id="loading-image" src="/assets/img/ajax-loaders/32x32.gif" alt="Loading..." />\n' + '</div>');
        var parentEvent = event;
        var req = new XMLHttpRequest();
        req.open("GET", $(event.currentTarget).attr('href'), true);
        req.responseType = "blob";
        req.onload = function (event) {
            $(parentEvent.currentTarget).closest('.modal-footer').find('#loading').remove();
            var blob = req.response;
            var regex = /filename=\"(.*?)\"/i;
            var cd = req.getResponseHeader("content-disposition"); //if you have the fileName header available
            var match = regex.exec(cd);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = match[1];
            link.click();
        };
        req.send();
    }
};