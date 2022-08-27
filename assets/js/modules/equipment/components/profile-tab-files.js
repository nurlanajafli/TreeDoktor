/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [];

exports.modules = [
    '/assets/vendors/diez/js/modules/fileinput.js'
];

exports.App = {
    routes: {
        list: '/equipment/files/ajax_get_files',
        row: '/equipment/files/ajax_get_file',
        create: '/equipment/files/ajax_create_file',
        delete: '/equipment/files/ajax_delete_file',
    },
    init: function (app) {
        this.app = app;
        this.where = {eq_id: this.app.dataset.equipmentId};
        this.idField = 'file_id';
        this.nameField = '';
        this.rowTemplate = Handlebars.compile($('#profile-tab-file-row-template').html());
        this.editTemplate = Handlebars.compile($('#profile-tab-file-edit-template').html());
        this.paginatorTemplate = Handlebars.compile($('#paginator-template').html());
        this.defaultSort = ['file_created_at', 'desc'];
        this.filter = D.helper.url.getQuery('filter');
        //$(this.app).find('input#filter').val(this.filter);
        this.bindEvents();
        this.getList();
    },
    fileInputSettings: {
        layoutTemplates: {
            main1:
                '<div class="kv-upload-progress kv-hidden"></div><div class="clearfix"></div>\n' +
                '<div class="input-group {class}">\n' +
                '  {caption}\n' +
                '  <div class="input-group-btn">\n' +
                '    {browse}\n' +
                '  </div>\n' +
                '</div>\n' +
                '{preview}',
            main2: '<div class="kv-upload-progress hide"></div>\n{browse}\n{preview}\n',
        }
    },
    refresh: function (app) {
        this.getList();
    },
    bindEvents: function () {
        let self = this;
        $.initialize('a.page-link', function () {
            $(this).on('click', self.eventPage.bind(self));
        }, {target: this.app});
    },
    eventPage: function (event, page = null) {
        if (event !== null) {
            event.preventDefault();
            page = event.currentTarget.dataset.pageNum;
        }
        if (page === null)
            return;
        var newUrl = D.helper.url.setPage(page);
        if (history.pushState) {
            window.history.pushState("Page " + page, document.title, newUrl);
        } else {
            document.location.href = newUrl;
        }
        this.getList();
    },
    getList: function (successCallback = null) {
        if (successCallback === null)
            successCallback = this.renderRows.bind(this);
        //this.getOrdering();
        D.helper.ajax(
            D.helper.url.route(this.routes.list),
            {
                filter: this.filter,
                page: D.helper.url.getPage(),
                sort: this.sort,
                where: this.where
            },
            [],
            successCallback,
            false,
            "POST",
            this.app
        )
    },
    renderRows: function (response) {
        if (response.data !== false) {
            this.eventInitFiles(response.data.reverse());
        }
        this.renderPaginator(response);
    },

    renderPaginator: function (paginator) {
        $(this.app).find('.paginator').html(this.paginatorTemplate(paginator));
    },
};