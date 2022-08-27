/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

// exports.scripts = [
//     'assets/js/colpick.js'
// ];

exports.App = {
    routes: {
        notes: '/equipment/notes/ajax_get_notes',
        note: '/equipment/notes/ajax_get_note',
        create: '/equipment/notes/ajax_create_note',
        edit: '/equipment/notes/ajax_update_note',
        delete: '/equipment/notes/ajax_delete_note'
    },
    init: function (app) {

        this.app = app;
        this.noteTemplate = Handlebars.compile($('#note-block-template').html());
        this.listEl = $(this.app).find('.comment-list').first();
        this.eqId = this.app.dataset.equipmentId;
        try {
            this.where = JSON.parse(this.app.dataset.where);
        } catch (e) {
            this.where = false;
        }
        if (this.eqId === "" || this.eqId === null)
            return false;
        this.filterNoteType = false;
        this.filterNoteFor = false;
        D.helper.ajaxLoader(this.listEl);
        this.bindEvents();
        this.getList();
    },
    events: {
        '.action-refresh:click': 'refresh',
    },
    refresh: function (app) {
        this.getList();
    },
    bindEvents: function () {
        let self = this;

        $.initialize('.comment-item .comment-action .action-reply', function () {
            $(this).on('click', self.eventReply.bind(self));
        }, {target: this.app});
        $.initialize('.comment-item .comment-action .action-delete', function () {
            $(this).on('click', self.eventDelete.bind(self));
        }, {target: this.app});
        $.initialize('.comment-item .comment-action .action-edit', function () {
            $(this).on('click', self.eventEdit.bind(self));
        }, {target: this.app});
        $.initialize('.comment-item .action-edit-cancel', function () {
            $(this).on('click', self.eventEditCancel.bind(self));
        }, {target: this.app});
        $.initialize('.comment-item .action-edit-submit', function () {
            $(this).on('click', self.eventEditSubmit.bind(self));
        }, {target: this.app});
        $.initialize('.comment-form:not(.comment-reply) .action-reply-cancel', function () {
            $(this).on('click', self.eventCancel.bind(self));
        }, {target: this.app});
        $.initialize('.comment-form.comment-reply .action-reply-cancel', function () {
            $(this).on('click', self.eventReplyCancel.bind(self));
        }, {target: this.app});
        $.initialize('.comment-item .action-upload', function () {
            $(this).on('change', self.eventUpload.bind(self));
        }, {target: this.app});
        $.initialize('#note_filter_type', function () {
            $(this).on('change', self.eventFilterType.bind(self));
        }, {target: this.app});
        $.initialize('#note_filter_for', function () {
            $(this).on('change', self.eventFilterFor.bind(self));
        }, {target: this.app});
        $.initialize('.comment-form form', function () {
            $(this).on('submit', self.eventSubmit.bind(self));
        }, {target: this.app});
    },
    eventFilterFor: function (event) {
        this.filterNoteFor = event.currentTarget.value === "0" ? false : event.currentTarget.value;
        this.refresh();
    },
    eventFilterType: function (event) {
        this.filterNoteType = event.currentTarget.value === "0" ? false : event.currentTarget.value;
        this.refresh();
    },
    eventReply: function (event) {
        event.preventDefault();
        event.stopPropagation();
        var noteId = event.currentTarget.dataset.noteId;
        let replyEl = $(this.app).find('#reply_to_' + noteId);
        if (replyEl.length !== 0) {
            replyEl.find('textarea[name=note_description]').focus();
            return;
        }
        let otherReplyEls = $(this.app).find('.comment-item.comment-reply');
        if (otherReplyEls.length !== 0) {
            otherReplyEls.remove();
        }
        var commentEl = $(this.app).find('.comment-form').first().clone();
        commentEl.find('.action-reply-cancel').show();
        commentEl.find('form').append('<input type="hidden" name="note_parent_id" value="' + noteId + '">');
        commentEl.find('form').find('.help-block').text("").parent().removeClass('has-error');
        commentEl.addClass('comment-reply').attr('id', 'reply_to_' + noteId);
        $(this.app).find('#note_' + noteId).after(commentEl);
        // $(this.app).find('.scrollable').first().animate(
        //     {
        //         scrollTop: $(commentEl).offset().top
        //     },
        //     800
        // );
        commentEl.find('textarea[name=note_description]').focus();
    },
    eventCancel: function (event) {
        event.preventDefault();
        event.stopPropagation();
        let form = $(event.currentTarget).closest('.comment-form');
        $(form).find('textarea[name=note_description]').val("");
        $(form).find('input[name=file]').val(null);
        $(form).find('.upload-list li').remove();
    },
    eventReplyCancel: function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(event.currentTarget).closest('.comment-form').remove();
    },
    eventEdit: function (event) {
        event.preventDefault();
        event.stopPropagation();
        var noteId = event.currentTarget.dataset.noteId;
        var el = $(event.currentTarget).closest('.comment-body');
        $(el).find('textarea').show();
        $(el).find('.panel-body').hide();
        $(el).find('.panel-footer').show();
        $(el).find('.panel-footer .btn').show();
        $(el).find('.panel-footer .ul-upload').show();
        $(el).find('.panel-footer .upload-list').show();
        $(el).find('.panel-footer .action-remove-file').show();
        $(el).find('textarea[name=note_description]').focus();
    },
    eventEditCancel: function (event) {
        event.preventDefault();
        event.stopPropagation();
        var el = $(event.currentTarget).closest('.comment-body');
        $(el).find('textarea').hide();
        $(el).find('.panel-body').show();
        if ($(el).find('.uploaded-list li').length == 0) {
            $(el).find('.panel-footer').hide();
        }
        $(el).find('.panel-footer .btn').hide();
        $(el).find('.panel-footer .ul-upload').hide();
        $(el).find('.panel-footer .upload-list').hide();
        $(el).find('.panel-footer .action-remove-file').hide();
    },
    eventEditSubmit: function (event) {
        event.preventDefault();
        event.stopPropagation();
        var noteId = event.currentTarget.dataset.noteId;
        var data = new FormData($(event.currentTarget).closest('form').get(0));
        D.helper.ajax(
            D.helper.url.route(this.routes.edit),
            data,
            ['edit', noteId, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            this.app
        );
    },
    eventUpload: function (event) {
        var listEl = $(event.currentTarget).closest('.comment-item').find('.upload-list').first();
        $(listEl).empty();
        $.each(event.currentTarget.files, function (key, val) {
            if (val.name)
                $(listEl).append('<li><i class="fa fa-file text-muted m-r-xs"></i>' + val.name + '</li>');
        });
    },
    eventSubmit: function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(event.currentTarget).find('.help-block').text("").parent().removeClass('has-error');
        var data = new FormData(event.currentTarget);
        D.helper.ajax(
            D.helper.url.route(this.routes.create),
            data,
            ['create', null, event.currentTarget],
            this.callbackSubmitSuccess.bind(this),
            this.callbackSubmitError.bind(this),
            "POST",
            this.app
        );
    },
    eventDelete: function (event) {
        event.preventDefault();
        event.stopPropagation();
        let noteId = event.currentTarget.dataset.noteId;
        if (confirm('Are you sure delete this note?')) {
            D.helper.ajax(
                D.helper.url.route(this.routes.delete),
                {note_id: noteId},
                ['delete', noteId, event.currentTarget],
                this.callbackSubmitSuccess.bind(this),
                this.callbackSubmitError.bind(this)
            );
        }
    },
    callbackSubmitSuccess: function (response, action, id, form) {
        this.sort = this.defaultSort;
        if (action === "delete") {
            $(this.app).find('.comment-list #note_' + id).remove();
            return;
        }
        if (action === "create") {
            var el = $(form).closest('.comment-form');
            if ($(el).hasClass('comment-reply')) {
                $(el).remove();
            } else {
                $(form).find('[name=note_description]').val('');
                $(form).find('input[name=file]').val(null);
                $(form).find('.upload-list li').remove();
            }
        }
        if (action === "edit") {
            this.getRow(id);
        } else {
            this.refresh();
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
    },
    getList: function (successCallback = null) {
        if (successCallback === null)
            successCallback = this.renderRows.bind(this);
        D.helper.ajax(D.helper.url.route(this.routes.notes), {
            where: this.where !== false
                ? Object.assign({}, {eq_id: this.eqId}, this.where)
                : {eq_id: this.eqId},
            type: this.filterNoteType,
            for: this.filterNoteFor
        }, [], successCallback, false, "POST", this.app)
    },
    getRow: function (id, successCallback = null) {
        if (successCallback === null)
            successCallback = this.renderRow.bind(this);
        D.helper.ajax(D.helper.url.route(this.routes.note), {
            note_id: id,
        }, [id], successCallback)
    },
    renderRows: function (response) {
        $(this.listEl).find('.comment-item:not(.comment-form)').remove();
        if (response.notes !== false) {
            var self = this;
            $.each(response.notes, function (key, row) {
                self.renderRow(row);
                if (row.replies.length != 0) {
                    $.each(row.replies, function (key, row) {
                        self.renderRow(row);
                    });
                }
            });
        }
        this.renderPaginator(response.equipments);
    },
    renderRow: function (row, id = null) {
        if (id !== null) {
            $(this.listEl).find('.comment-item#note_' + id).replaceWith(this.noteTemplate(row));
        } else {
            $(this.listEl).append(this.noteTemplate(row));
        }
    },
    renderPaginator: function (paginator) {
        //$('.paginator').html(this.paginatorTemplate(paginator));
    }
};