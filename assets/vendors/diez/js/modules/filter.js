/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.name = "filter";

exports.scripts = [];

exports.Module = {
    component: undefined,
    init: function () {
        $(this.app).find('.input-query').val(this.component.query);
        if (typeof this.component.filter !== "undefined") {
            for (const key in this.component.filter) {
                $(this.app).find('[name=filter\\[' + key + '\\]]').val(this.component.filter[key]);
            }
        }
    },
    observers: {},
    events: {
        '.action-filter:change': 'eventFilter',
        '.action-query:click': 'eventQuery',
        '.input-query:keyup': function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                $(this.app).find('.action-query').click();
            }
        },
    },
    bindEvents: function () {

    },
    eventFilter: function (event) {
        if (event.type === "select2-selecting") {
            $(event.currentTarget).val(event.val);
        } else if (event.type === "select2-clearing") {
            $(event.currentTarget).val('');
        } else {
            event.preventDefault();
        }
        let value = $(event.currentTarget).val();
        let name = $(event.currentTarget).attr('name');
        let re = /filter\[(.*?)\]/i;
        let match = re.exec(name);
        if (match === null)
            return;
        let key = match[1];
        if (typeof this.component.filter === "undefined") {
            this.component.filter = {};
        }
        if (typeof value === "undefined" || value === "") {
            if (key in this.component.filter) delete this.component.filter[key];
        } else {
            this.component.filter[key] = value;
        }
        this.changeUrl();
    },
    eventQuery: function (event) {
        event.preventDefault();
        this.component.query = $(this.app).find('.input-query').val();
        this.changeUrl();
    },

    changeUrl: function (callback) {
        var newUrl = D.helper.url.setQuery(
            {filter: this.component.filter, query: this.component.query},
            D.helper.url.setPage(1)
        );
        if (history.pushState) {
            window.history.pushState("Filter " + Date(), document.title, newUrl);
        } else {
            document.location.href = newUrl;
        }
        if (callback !== undefined)
            callback();
        else if (typeof this.refresh === "function") {
            this.refresh();
        }
    }
};