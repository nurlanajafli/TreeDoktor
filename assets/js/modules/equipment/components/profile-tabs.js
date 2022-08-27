/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [
    'assets/vendors/diez/js/includes/tab-manager.js'
];

exports.App = {
    routes: {
        tabs: '/equipment/{id}/{tab}',
    },
    tabDefault: 'services',

    init: function (app) {
        let self = this;
        this.app = app;
        this.idField = "";
        this.id = this.app.dataset.equipmentId;
        TabManager.initialize($('#equipment_tabs header .nav'));
        $(window).resize(function () {
            TabManager.initialize($('#equipment_tabs header .nav'))
        });
        $(window).load(function (event) {
            self.loadDefaultActiveTab();
        });
    },
    postInit: function (app) {
        //
    },
    observers: {
        'header .nav a.nav-link:not(.dropdown-toggle)': function (app) {
            $(this).on('click', app.eventTabClick.bind(app));
            // $(this).on('shown.bs.tab', function (event) {
            //     app.eventTabShown(event);
            // });
        },
    },
    events: {},
    refresh: function () {
        //
    },
    bindEvents: function () {
        //
    },
    loadDefaultActiveTab: function () {
        if (!(activeTab = D.helper.url.segment(3, false)))
            var activeTab = this.tabDefault;
        let tab = $(this.app).find('header .nav a[href=#' + activeTab + ']').first();
        $(tab).tab('show');
    },
    eventTabClick: function (event) {
        event.preventDefault();
        var tab = $(event.currentTarget).attr('href').replace('#', '');
        if (D.helper.url.segment(3) === tab)
            return;
        var route = D.helper.url.route(this.routes.tabs, {id: this.id, tab: tab});
        if (history.pushState) {
            window.history.pushState("Tab " + tab, document.title, D.helper.url.prepare(route));
        } else {
            document.location.href = D.helper.url.prepare(route);
        }
        $(event.currentTarget).tab('show');
    },
    // eventTabShown: function (event) {
    //     let tab = $(this.app).find($(event.currentTarget).attr('href'));
    //     $(tab).find('[diez-app]').each(function (idx, el) {
    //         var name = $(el).attr('diez-app');
    //         //D.initApp(el, name);
    //     })
    // }
};