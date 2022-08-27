/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "table";

exports.scripts = [];

exports.Module = {
    component: undefined,
    filter: false,
    paginatorTemplate: Handlebars.compile($('#paginator-template').html()),
    init: function () {
        if (typeof this.idField === "undefined")
            this.idField = "id";
        this.getOrdering();
    },
    observers: {
        '[data-toggle="tooltip"]': function () {
            $(this).tooltip();
        },
    },
    events: {
        '.action-refresh:click': 'refresh',
        'a.page-link:click': 'eventPage',
        '.table th.sortable:click': 'eventSort',
    },
    eventPage: function (event, page = null, callback = undefined) {
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
        if (callback !== undefined)
            callback();
        else if (typeof this.getList === "function") {
            this.getList();
        }
    },
    eventSort: function (event) {
        let col = event.currentTarget.dataset.sort;
        let orderEl = $(event.currentTarget).find('i');
        let newOrder = 'asc';
        if (orderEl.length !== 0) {
            newOrder = $(orderEl).hasClass('fa-caret-up') ? 'desc' : 'asc';
        }
        this.sort = [col, newOrder];
        $(this.app).find('.table th.sortable i').remove();
        $(event.currentTarget).append('<i class="fa ' + (newOrder === "asc" ? 'fa-caret-up' : 'fa-caret-down') + '"></i>');
        var newUrl = D.helper.url.setQuery({sort: col, order: newOrder});
        if (history.pushState) {
            window.history.pushState("order " + col, document.title, newUrl);
            this.eventPage(null, 1);
        } else {
            newUrl = D.helper.url.setPage(1, newUrl);
            document.location.href = newUrl;
        }
    },
    getOrdering: function () {
        var sort = D.helper.url.getQuery('sort');
        if (sort === undefined)
            sort = this.defaultSort[0];
        var order = D.helper.url.getQuery('order');
        if (order === undefined)
            order = this.defaultSort[1];
        this.sort = [sort, order];
        $(this.app).find('.table th.sortable i').remove();
        $(this.app).find('.table th.sortable[data-sort=' + sort + ']').append('<i class="fa ' + (order === "asc" ? 'fa-caret-up' : 'fa-caret-down') + '"></i>');
    },
    getList: function (successCallback = null) {
        if (successCallback === null)
            successCallback = this.renderRows.bind(this);
        this.getOrdering();
        D.helper.ajax(
            D.helper.url.route(this.routes.list),
            {
                query: this.query,
                filter: this.filter,
                page: D.helper.url.getPage(),
                sort: this.sort,
                where: this.where,
                noPaginate: this.noPaginate
            },
            [],
            successCallback,
            false,
            "POST",
            this.app
        );
    },
    getRow: function (id, successCallback = null, loader) {
        if (successCallback === null)
            successCallback = this.renderRow.bind(this);
        if (loader === null)
            loader = this.app;
        D.helper.ajax(D.helper.url.route(this.routes.row), {
                [this.idField]: id,
            },
            [id],
            successCallback,
            false,
            "POST",
            loader
        )
    },
    renderRows: function (response) {
        $(this.app).find('.data-list').empty();
        $(this.app).find('.no-items-found').remove();
        if (typeof response.current_page !== "undefined" && parseInt(D.helper.url.getPage()) !== response.current_page) {
            let newUrl = D.helper.url.setPage(response.current_page);
            if (history.pushState) {
                window.history.pushState("Page " + response.current_page, document.title, newUrl);
            } else {
                document.location.href = newUrl;
            }
        }
        if (response.data !== false && response.data.length > 0) {
            var self = this;
            $.each(response.data, function (key, row) {
                self.renderRow(row);
            });
        } else {
            $(this.app).find('.data-list').parent().parent().append('<span class="no-items-found" style="text-align: center;display: block;font-weight: bold;padding: 10px 0px;">No items found</span>')
        }
        if (typeof this.noPaginate === "undefined" || !this.noPaginate) {
            this.renderPaginator(response);
        }
    },
    renderRow: function (row, id = null) {
        if (id !== null) {
            $(this.app).find('.data-list #row_' + id).replaceWith(this.rowTemplate(row));
        } else {
            $(this.app).find('.data-list').append(this.rowTemplate(row));
        }
    },
    renderPaginator: function (paginator) {
        $(this.app).find('.paginator').html(this.paginatorTemplate(paginator));
    },
};