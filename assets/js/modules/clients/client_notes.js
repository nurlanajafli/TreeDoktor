var ClientNotes = function() {
    const config = {
        ui: {
            load_more_container: '#load-more-notes',
            notes: '#client-notes',
            tabs: '#client-notes-tabs',
        },

        events: {
            tabs: '#client-notes-tabs',
            tab: '.client-notes-tab',
            sms_tab: '.client-sms-notes-tab',
            call_tab: '.client-call-notes-tab',
            note_link: '.client-note a',
            show_client_notes: '#show-client-notes',
            email_log_toggle_show_more: '.note-email-toggle'
        },

        route: {
            notes: '/clients/clientsNotes/notes',
            sms_notes: '/clients/clientsNotes/smsNotes',
            call_notes: '/clients/clientsNotes/callNotes',
        },

        templates: {
            infowindow: {},

            client_notes:'#client-notes-tmp',
            client_notes_body: '#client-notes-body-tmp',
            client_sms_notes: '#client-sms-notes-tmp',
            client_call_notes: '#client-call-notes-tmp',
        },

        views: {
            client_notes:'#client-notes',
            tab_content:'#client-notes-tab-content'
        },

        images: {}
    };

    const _private = {
        init: function() {
            // load profile notes
            if (typeof CLIENT_NOTES !== 'undefined' && typeof NOTES_DATA !== 'undefined') {
                const data = {
                    client_note_type: null,
                    client_only: NOTES_DATA.client_only,
                    client_id: NOTES_DATA.client_id,
                    lead_id: NOTES_DATA.lead_id
                };

                _private.get_client_notes(data);
                _private.clients_notes_enable();
            }

            setTimeout(() => {
                const noteEmailLogs = document.querySelectorAll('div.note-email-logs');
                noteEmailLogs.forEach((el) => {
                    const liCount =  el
                        .getElementsByTagName('li')
                        .length;

                    if(liCount > 10){
                        const a = document.createElement('a');
                        a.classList.add('note-email-toggle')
                        a.append('Load more')

                        el.append(a)
                    }
                })
            }, 3000);

        },

        get_client_notes: function(data){
            Common.request.send(config.route.notes, data, _private.renderNotes, function (response) { return false; }, false);
        },

        get_client_sms_notes: function(data){
            Common.request.send(config.route.sms_notes, data, _private.renderSmsNotes, function (response) { return false; }, false);
        },

        get_client_call_notes: function(data){
            Common.request.send(config.route.call_notes, data, _private.renderCallNotes, function (response) { return false; }, false);
        },

        notes_tab: function(e){
            if ($(e.currentTarget).parent().hasClass('active')) {
                return false;
            }

            Common.renderPreloader(config.views.client_notes+' .tab-pane.active', {text:'Loading...'}, 1000);
            _private.get_client_notes(e.currentTarget.dataset);

            return false;
        },

        sms_notes_tab: function(e){
            if ($(e.currentTarget).parent().hasClass('active')) {
                return false;
            }

            $(e.currentTarget).closest(config.events.tabs).find('.active').removeClass('active');
            $(e.currentTarget).parent().addClass('active');

            Common.renderPreloader(config.views.client_notes+' .tab-pane.active', {text:'Loading...'}, 1000);
            _private.get_client_sms_notes(e.currentTarget.dataset);

            return false;
        },

        call_notes_tab: function(e){
            if ($(e.currentTarget).parent().hasClass('active')) {
                return false;
            }

            $(e.currentTarget).closest(config.events.tabs).find('.active').removeClass('active');
            $(e.currentTarget).parent().addClass('active');

            Common.renderPreloader(config.views.client_notes+' .tab-pane.active', {text:'Loading...'}, 1000);
            _private.get_client_call_notes(e.currentTarget.dataset);

            return false;
        },

        renderNotes: function (response) {
            let client_note_type = 0;
            
            if(response.client_note_type !== undefined) {
                client_note_type = response.client_note_type;
            }
            if(response.client_only === undefined) {
                response.client_only = false;
            }
            if(response.client.notes === undefined) {
                response.client.notes = [];
            }

            Common.renderView({
                template_id: config.templates.client_notes,
                view_container_id: config.views.client_notes,
                data: [{
                    "notes_files": (response.notes_files === undefined) ? [] : response.notes_files,
                    "client": response.client,
                    'limit': response.limit,
                    'lead': response.lead,
                    'client_note_type': client_note_type,
                    'client_only': response.client_only
                }],
                helpers: pub.helpers
            });
            Common.init_checkbox();
        },

        renderMore: function (response) {
            Common.renderView({
                template_id: config.templates.client_notes_body,
                view_container_id: $(config.events.tabs).find('.active>a').attr("href"),
                data: [{
                    "notes_files": response.notes_files,
                    "client": response.client,
                    /*'limit':response.limit,*/
                    'lead': response.lead,
                    'client_note_type': response.client_note_type,
                    'client_only': response.client_only
                }],
                helpers: pub.helpers,
                render_method: 'append'
            });
            $($(config.events.tabs).find('.active>a').attr("href")).find(config.ui.load_more_container).remove();
            Common.init_checkbox();
        },

        renderSmsNotes:function (response) {
            let client_note_type = 0;
            
            if (response.client_note_type !== undefined) {
                client_note_type = response.client_note_type;
            }

            Common.renderView({
                template_id: config.templates.client_sms_notes,
                view_container_id: config.views.tab_content,
                data: [{
                    "client": response.client,
                    'notes': response.notes,
                    'limit': response.limit,
                    'client_note_type': client_note_type
                }],
                helpers: pub.helpers
            });
            Common.init_checkbox();
        },

        renderCallNotes:function (response) {
            let client_note_type = 0;
            
            if (response.client_note_type !== undefined) {
                client_note_type = response.client_note_type;
            }

            Common.renderView({
                template_id: config.templates.client_call_notes,
                view_container_id: config.views.tab_content,
                data: [{
                    "client": response.client,
                    'notes': response.notes,
                    'limit': response.limit,
                    'client_note_type': client_note_type
                }],
                helpers: pub.helpers
            });
            Common.init_checkbox();

            setTimeout(function () {
                soundManager.reboot();
            }, 200);
        },

        open_link: function (e) {

        },

        show_client_notes:function (e) {
            const checked = $(e.currentTarget).prop("checked");

            if (checked) {
                _private.clients_notes_enable();
                $(config.ui.notes).show();
            }
            else {
                $(config.ui.notes).hide();
            }
        },

        clients_notes_enable: function() {
            const active_tab = $(config.ui.tabs).find('.active a');
            active_tab.parent().removeClass('active');
            active_tab.trigger('click');
        },

        delete_client_note: function (e) {
            if (!confirm('Are you sure?')) {
                e.preventDefault();

                return false;
            }
        },
        email_log_toggle_show_more: function (e) {
            const toggleButton = e.target;
            const emailsList  = toggleButton.parentElement.getElementsByClassName('emails-stat-details-list');

            if(!emailsList[0].classList.contains('active')) {
                emailsList[0].classList.add('active');
                toggleButton.textContent = 'Show less';
            } else {
                emailsList[0].classList.remove('active');
                toggleButton.textContent = 'Load more'
            }
        }
    };

    const pub = {
        init: function(){
            $(document).ready(function() {
                pub.events();
                _private.init();
            });
        },

        events: function() {
            $(document).on('click', config.events.tab, _private.notes_tab);
            $(document).on('click', config.events.sms_tab, _private.sms_notes_tab);
            $(document).on('click', config.events.call_tab, _private.call_notes_tab);
            $(document).on('click', config.events.note_link, _private.open_link);
            $(document).on('click', '.delete-client-note', _private.delete_client_note);
            $(document).on('change', config.events.show_client_notes, _private.show_client_notes);
            $(document).on('click', config.events.email_log_toggle_show_more, _private.email_log_toggle_show_more);
        },

        helpers: {
            getAdminDeleteUrl: function (client_note_id) {
                if (!client_note_id) {
                    return '#';
                }

                return baseUrl + 'clients/delete_note/' + client_note_id;
            }
        },

        renderNotes: function (response) {
            _private.renderNotes(response);
        },

        renderMore: function (response) {
            _private.renderMore(response);
        }
    };

    pub.init();
    return pub;
}();
