/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "fileInput";

exports.scripts = [
    'assets/vendors/kartik-v/fileinput/js/fileinput.js',
    'assets/vendors/diez/js/includes/lodash.core.min.js',
];

exports.Module = {
    component: undefined,
    _fileInputSettings: {
        initialPreview: [],
        initialPreviewConfig: [],
        overwriteInitial: false,
        uploadUrl: "",
        deleteUrl: "",
        initialPreviewShowDelete: true,
        previewFileType: 'any',
        showClose: false,
        browseOnZoneClick: true,
        showCaption: false,
        showRemove: false,
        showUpload: false,
        showDrag: false,
        reversePreviewOrder: true,
        browseClass: "btn btn-primary btn-block",
        type: 'other',
        fileActionSettings: {
            showDrag: false,
        },
        ajaxSettings: {
            global: false,
        },
        ajaxDeleteSettings: {
            global: false,
        },
        previewSettings: {
            image: {width: "auto", height: "100px", 'max-width': "100%", 'max-height': "100%"},
            html: {width: "180px", height: "100px"},
            text: {width: "180px", height: "100px"},
            office: {width: "180px", height: "100px"},
            gdocs: {width: "180px", height: "100px"},
            video: {width: "180px", height: "100px"},
            audio: {width: "180px", height: "30px"},
            flash: {width: "180px", height: "100px"},
            object: {width: "180px", height: "100px"},
            pdf: {width: "180px", height: "100px"},
            other: {width: "180px", height: "100px"}
        },
        fileTypeSettings: {
            image: function (vType, vName) {
                return (typeof vType !== "undefined") ? vType.match('image.*') && !vType.match(/(tiff?|wmf)$/i) : vName.match(/\.(gif|png|jpe?g)$/i);
            },
            html: function (vType, vName) {
                return (typeof vType !== "undefined") ? vType == 'text/html' : vName.match(/\.(htm|html)$/i);
            },
            office: function (vType, vName) {
                return vType.match(/(word|excel|powerpoint|office)$/i) ||
                    vName.match(/\.(docx?|xlsx?|pptx?|pps|potx?)$/i);
            },
            gdocs: function (vType, vName) {
                return vType.match(/(word|excel|powerpoint|office|iwork-pages|tiff?)$/i) ||
                    vName.match(/\.(rtf|docx?|xlsx?|pptx?|pps|potx?|ods|odt|pages|ai|dxf|ttf|tiff?|wmf|e?ps)$/i);
            },
            text: function (vType, vName) {
                return typeof vType !== "undefined" && vType.match('text.*') || vName.match(/\.(txt|md|csv|nfo|php|ini)$/i);
            },
            video: function (vType, vName) {
                return typeof vType !== "undefined" && vType.match(/\.video\/(ogg|mp4|webm)$/i) || vName.match(/\.(og?|mp4|webm)$/i);
            },
            audio: function (vType, vName) {
                return typeof vType !== "undefined" && vType.match(/\.audio\/(ogg|mp3|wav)$/i) || vName.match(/\.(ogg|mp3|wav)$/i);
            },
            flash: function (vType, vName) {
                return typeof vType !== "undefined" && vType == 'application/x-shockwave-flash' || vName.match(/\.(swf)$/i);
            },
            object: function (vType, vName) {
                return true;
            },
            other: function (vType, vName) {
                return true;
            },
        }
    }
    ,
    init: function () {
        if ('fileInputSettings' in this.component) {
            this._fileInputSettings = Object.assign({}, this._fileInputSettings, this.component.fileInputSettings);
        }
    },
    observers: {
        'input.file-input': function (app) {
            let settings = Object.assign({}, app._fileInputSettings);
            if ('fileInputSettings' in app) {
                settings = Object.assign({}, settings, app.fileInputSettings);
            }
            settings.initialPreviewAsData = true;
            settings.uploadExtraData = {id: $(this).data('id')};
            settings.uploadUrl = D.helper.url.route(app.routes[$(this).data('uploadRoute')]);
            settings.deleteUrl = D.helper.url.route(app.routes[$(this).data('deleteRoute')]);
            $(this).fileinput(settings).on('filebatchselected', function (event, files) {
                $(event.currentTarget).fileinput("upload");
            });
            $(this).fileinput(settings).on('fileuploaded', function (event, data, previewId, index) {
                app.eventFileUploaded(event, data, index);
                //console.log(event, data, previewId, index);
            });
            $(this).fileinput(settings).on('filedeleted', function (event, key, jqXHR, data) {
                app.eventFileDeleted(event, key);
                //console.log(event, key, jqXHR, data);
            });
        }
    },
    events: {
        //
    },
    eventFileUploaded: function (event, data, index) {
        let response = data.response;
        let id = response.initialPreviewConfig[0].key;
        if (String(id).startsWith('uploads/tmp')) {
            let formEl = $(event.currentTarget).closest('form');
            $(formEl).append('<input type="hidden" name="tmp_files[]" value="' + id + '" />');
        }
    },
    eventFileDeleted: function (event, key) {
        if (String(key).startsWith('uploads/tmp')) {
            let formEl = $(event.currentTarget).closest('form');
            $(formEl).find('input[value="' + key + '"]').remove();
        }
    },
    eventInitFiles: function (files) {
        if ($(this.app).find('input.file-input').length === 0)
            return;
        let inputEl = $(this.app).find('input.file-input').get(0);
        //let settings = Object.assign({}, this._fileInputSettings);
        let settings = Object.assign({}, this._fileInputSettings);
        settings.initialPreview = [];
        settings.initialPreviewConfig = [];
        settings.initialPreviewAsData = true;
        settings.uploadExtraData = {id: $(inputEl).data('id')};
        settings.uploadUrl = D.helper.url.route(this.routes[$(inputEl).data('uploadRoute')]);
        settings.deleteUrl = D.helper.url.route(this.routes[$(inputEl).data('deleteRoute')]);
        if (typeof files !== "undefined") {
            $(files).each(function () {
                settings.initialPreview.push(this.file_url);
                let initialPreviewConfig = {
                    caption: this.file_name,
                    filename: this.file_name,
                    downloadUrl: this.file_url,
                    //width: "60px",
                    key: this.file_id
                };
                if (this.file_size !== null) {
                    initialPreviewConfig.size = parseFloat(this.file_size);
                }
                initialPreviewConfig.type = D.helper.file.type(this.file_mime, this.file_name);
                initialPreviewConfig.filetype = this.file_mime;
                settings.initialPreviewConfig.push(initialPreviewConfig);
            });
        }
        $(inputEl).fileinput('destroy').fileinput(settings);
    }
};