//let $ = require('jquery');
var SmsChat = function () {
    let mode = '';//all, users, clients, search
    let chatboxes = {all: [], supportchat: [], users: [], clients: [], search: []};
    let recipientsData = [];
    let searchRecipient = false;
    let cookieRegistry = [];
    //let searchChatboxes = [];
    let chatboxesHistory = {};

    const recipientSearchMinLength = 2;
    const messageSearchMinLength = 3;
    const numPattern = /^[+0-9()\s-]+/;
    const cleanNumPattern = /[+\(\)\s-]+/g;
    const messagesShowLimit = SMS_MESSAGES_SHOW_LIMIT;
    const chatsShowLimit = SMS_CHATS_SHOW_LIMIT;
    let searchValue = '';
    let searchCurrent = 0;
    let prevMode = null;
    let newMessageSent = false;
    let prevSearchEvent = null;
    const searchKeyPressInterval = 1500; // 1.5 sec
    let prevKeystrokeTime = null;
    let timerSearchRequest = null;
    const acceptedInputTypes = [
        'insertFromPaste',
        'deleteByCut',
        'deleteContentBackward'
    ];

    let XHR = null;
    let activeWindow = true;
    let isSocketActions = false;
    let historyClickAction = false;
    let updatedChatNumber = null;

    const phoneNumberInfo = function (number) {
        // number.length > 16 - according to E.164 standard plus (+) symbol
        if (!number || number === '' || number.length < PHONE_CLEAN_LENGTH || number.length > 16) {
            return false;
        }

        if (number.indexOf('+') === -1) {
            if (number.length === PHONE_CLEAN_LENGTH) {
                // add system phone country code if the length of the number matches the system
                number = PHONE_COUNTRY_CODE + number;
            } else {
                number = '+' + number;
            }
        }

        let info = {};

        try {
            info = new libphonenumber.parsePhoneNumber(number);
        }
        catch (e) {
            return false;
        }

        info.type = info.getType();
        info.nationalFormat = info.formatNational();
        info.isValidNumber = info.isValid();

        return info;
    }

    let render = function (template, render, obj) {
        var tmpl = $.templates(template);
        var html = tmpl.render(obj);
        $(render).html(html);
        $.views.helpers.smsDate = '';
    };
    let renderAppend = function (template, render, obj) {
        var tmpl = $.templates(template);
        var html = tmpl.render(obj);
        $(render).append(html);
        $.views.helpers.smsDate = '';
    };
    let renderPrepend = function (template, render, obj) {
        var tmpl = $.templates(template);
        var html = tmpl.render(obj);
        $(render).prepend(html);
        $.views.helpers.smsDate = '';
    };

    let config = {
        ui: {
            chat_box_tpl: '#chat_box_tpl',
            chat_box_tpl_partial: '#chat_box_tpl_partial',
            chat_box: '#chat_box',
            chat_box_search: '#chat_box_search',
            message_tpl: '#message_tpl',
            chat_box_load_more_tpl: '#chat_box_load_more_tpl',
            chat_box_history_load_more_tpl: '#chat_box_history_load_more_tpl',
            chatboxes_preloader: '#chatboxes_preloader',
            sms_history: '.sms-history',
            chat_box_history_container_tpl: '#chat_box_history_container_tpl',
            chat_box_history_search_container_tpl: '#chat_box_history_search_container_tpl',
            chat_box_history_container: '#chat_box_history_container',
            chat_box_history_search_container: '#chat_box_history_search_container',

            chatboxes_list: '#chatboxes_list_tpl',
            chatboxes_history_containers_list: '#chatboxes_history_containers_list_tpl',
            search_no_data_tpl: '#search_no_data_tpl',
            sms_preloader_tpl: '#sms_preloader_tpl',
            search_min_length_tpl: '#search_min_length_tpl',

            messagesWrapperVisible: '.messages-wrapper:visible',
            searchButtonsBlock: '.sms-search-buttons',
            messenger: '#messenger',
        },
        events: {
            openMessengerButton: '.open-messenger',
            messengerModal: '#messenger',
            chatBoxButton: '.chat_box_button',
            loadMoreChatboxes: '.load_more_chats',
            loadMoreHistory: '.load_more_messages',
            btnSendSms: '.btn-send-sms',
            messageFieldSms: '.message-field-sms',
            messengerCounter: '.messenger-counter',
            smsSearch: '#smsSearch',
            chatboxes: '#chatboxes',
            chatboxes_history_containers: '#chatboxes_history_containers',
            change_chatbox: '.change-chatbox',
            smsnav: '.smsnav',
            new_message_button: '#new_message_button',
            changeNotifications: '.change-notifications',
            smsToList: '.sms-to-list',
            smsSearchClear: '.sms-search-clear-icon',
            smsSearchBtn: 'a.sms-search-btn',
            smsUnreadBtn: 'a.set_unread_chat_box'
        },
        routes: {
            openMessenger: '/messaging/ajax_open',
            getSmsCounter: '/messaging/ajax_get_count_unread',
            getSmsHistory: '/messaging/ajax_get_history',
            searchRecipient: '/messaging/ajax_get_contacts',
            sendSms: '/messaging/ajax_send',
            smsChatWasRead: '/messaging/ajax_set_read',
            searchSms: '/messaging/ajax_search_sms',
            getUserSmsLimit: '/messaging/ajax_get_user_sms_limit',
            setSmsUnread: '/messaging/ajax_set_sms_unread',
            refreshChatboxes: '/messaging/ajax_refresh_chatboxes',
        }
    };

    let _private = {

        init: function () {
            $(document).ready(_private.events);
        },

        events: function () {
            ion.sound({
                sounds: [
                    {name: "Note"}
                ],
                path: "/assets/",
                preload: true,
                multiplay: true,
                volume: 1
            });
            _private.initBlocks();
            _private.listenCookieChange('sms_notifications', _private.changeNotificationsButton);
            _private.getMessengerCounter();

            $(config.events.change_chatbox).click(function(e) {
                e.preventDefault();
                $(".select2-sendto").val(null).trigger('change');
            });
            $(config.events.messengerModal).on('show.bs.modal', function () {
                _private.openMessenger();
            });
            $(document).on('show.bs.tab', config.events.change_chatbox, function () {
                if (SmsChat.mode !== 'search') {
                    $(config.ui.chat_box_history_container).find('.tab-pane.active').removeClass('active');
                    $(config.events.smsnav).find('li.active').removeClass('active');
                    const mode = $(this).data('mode');
                    SmsChat.mode = mode || '';
                    SmsChat.recipientsData = [];
                    _private.openMessenger(mode);
                }
            });
            $(document).on('show.bs.tab', config.events.new_message_button, function () {
                $(config.ui.chat_box_history_container).find('.tab-pane.active').removeClass('active');
                $(config.events.smsnav).find('li.active').removeClass('active');
                $(".select2-sendto").val(null).trigger('change');
                $('#chat_box_new_message > .smschat > .chat-footer').find(SmsChat.config.events.messageFieldSms).val('');

                function contains(str1, str2) {
                    try {
                        return new RegExp(str2, "i").test(str1);
                    }
                    catch (e) {
                        return false;
                    }
                }

                function format(state) {
                    if (!state.id) return state.text; // optgroup
                    if (state.text != state.id)
                        return state.text + "\n" + $.views.helpers.numberTo(state.id);
                    return $.views.helpers.numberTo(state.id);
                }

                $(".select2-sendto").select2('destroy');

                const params = {
                    placeholder: "Type Name Or Number",
                    data: [],
                    allowClear: true,
                    multiple: true,
                    dropdownCssClass: "search-recipients",
                    minimumInputLength: recipientSearchMinLength,

                    escapeMarkup: function (m) {
                        return m;
                    },
                    formatResult: function (data) {
                        let result = '';
                        let address = '';

                        if (data.type) {
                            let color = '#2095FE';

                            if (data.type === 'client') {
                                color = '#9DA3AE';
                                address = '<span class="select2-search-client-address"> &mdash; ' + data.address + '</span>'
                            }

                            result += '<span class="badge badge-sm select2-search-badge" style="background-color: ' + color + ';">'
                                + data.type + '</span>';
                        }

                        return result + '<span style="font-weight: bold">' + data.text + '</span>' + address;
                    },
                    formatSelection: function(data) {
                        return data.text;
                    },
                    formatSelectionCssClass: function(data) {
                        let customClass = 'select2-selected-custom';

                        if (data.type === 'client') {
                            customClass += ' select2-selected-client';
                        }
                        else if(data.type === 'user') {
                            customClass += ' select2-selected-user';
                        }
                        else {
                            customClass += ' select2-selected-phone';
                        }

                        return customClass;
                    }
                };

                params.query = function (q) {
                    const obj = this;
                    setTimeout(function () {
                        obj.data = SmsChat.recipientsData;
                        const pageSize = 50,
                            results = obj.data.filter(function (e) {
                                let text = e.text;
                                let term = q.term

                                // check number search
                                if (numPattern.test(term)) {
                                    term = _private.cleanNumber(term);
                                    text = e.id;
                                }

                                return contains(text, term);
                            });

                        let paged = [];
                        if (q.term) {
                            // if number search without any results
                            if (numPattern.test(q.term) && !results.length) {
                                paged.push({id: q.term, text: q.term});
                            }
                            paged = paged.concat(results.slice((q.page - 1) * pageSize, q.page * pageSize));
                        }

                        q.callback({
                            results: paged,
                            more: results.length >= q.page * pageSize
                        });
                    }, 500);
                };

                $(".select2-sendto").select2(params).on('change', function (e) {
                    SmsChat.searchRecipient = false;
                    SmsChat.recipientsData = [];
                });
            });
            $(document).click(function () {
                if ($(".select2-sendto").length) {
                    SmsChat.searchRecipient = false;
                    $(".select2-sendto").select2('close');
                }
            });
            $(document).on('click', config.events.chatBoxButton, _private.loadHistory);
            $(document).on('click', config.events.loadMoreChatboxes, _private.loadNextChats);
            $(document).on('click', config.events.loadMoreHistory, _private.loadNextHistory);
            $(document).on('click', config.events.btnSendSms, _private.sendSms);
            $(document).on('click', config.events.changeNotifications, _private.changeNotifications);
            $(document).on('click', config.events.smsToList, _private.smsToList);
            $(document).on('click', config.events.smsSearchClear, _private.smsSearchClear);
            $(document).on('click', config.events.smsSearchBtn, _private.smsSearchNextPrev);
            $(document).on('click', config.events.smsUnreadBtn, _private.setSmsUnread);
            $(document).on('keydown', config.events.messageFieldSms, _private.sendSms);
            $(document).on('keyup input', '.smschat .select2-input', _private.searchRecipient);
            $(document).on('keyup input', config.events.smsSearch, _private.smsSearch);

            // window activity
            $(window).on('focus', function() {
                SmsChat.activeWindow = true;

                if (SmsChat.isSocketActions) {
                    _private.refreshChatboxes();
                }
            });
            $(window).on('blur', function() {
                SmsChat.activeWindow = false;
                SmsChat.isSocketActions = false;
            });
        },

        listenCookieChange: function (cookieName, callback) {
            SmsChat.cookieRegistry[cookieName] = $.cookie(cookieName);
            setInterval(function () {
                if (SmsChat.cookieRegistry[cookieName]) {
                    if ($.cookie(cookieName) != SmsChat.cookieRegistry[cookieName]) {
                        SmsChat.cookieRegistry[cookieName] = $.cookie(cookieName);
                        return callback();
                    } else {
                        SmsChat.cookieRegistry[cookieName] = $.cookie(cookieName);
                    }
                }
            }, 100);
        },

        initBlocks: function () {
            SmsChat.renderAppend(config.ui.chatboxes_list, config.events.chatboxes, {id: 'chat_box'});
            SmsChat.renderAppend(config.ui.chatboxes_list, config.events.chatboxes, {id: 'chat_box_supportchat'});
            SmsChat.renderAppend(config.ui.chatboxes_list, config.events.chatboxes, {id: 'chat_box_users'});
            SmsChat.renderAppend(config.ui.chatboxes_list, config.events.chatboxes, {id: 'chat_box_clients'});
            SmsChat.renderAppend(config.ui.chatboxes_history_containers_list, config.events.chatboxes_history_containers, {id: 'chat_box_history_container'});
            $(config.ui.chat_box).addClass('active');

            SmsChat.renderAppend(config.ui.chatboxes_list, config.events.chatboxes, {id: 'chat_box_search'});
            $(config.ui.chat_box_search).removeClass('active');
        },

        cleanNumber: function (number) {
            number = number.toString();

            if (number && number !== '' && numPattern.test(number)) {
                const phoneCountryCodePattern = new RegExp('^' + (PHONE_COUNTRY_CODE * 1));
                number = number.replace(cleanNumPattern, '').replace(phoneCountryCodePattern, '');
            }

            return number;
        },

        searchRecipient: function (event) {
            if (!_private.checkSearchInput(event)) {
                return false;
            }

            let search = $.trim($(this).val());

            // prevent to request data if search contain country code until code is not full to trim it in query
            const minCountryCodeLength = PHONE_COUNTRY_CODE.length + 2; // 2 - number digits of phone number search
            if (numPattern.test(search) && search.length < minCountryCodeLength) {
                SmsChat.searchRecipient = false;
                SmsChat.recipientsData = [];
                return;
            }

            if (event.type === 'input' && event.originalEvent.inputType === 'deleteContentBackward') {
                SmsChat.searchRecipient = false;
            }

            if (search.length >= recipientSearchMinLength && !SmsChat.searchRecipient) {
                // clear number symbols
                search = _private.cleanNumber(search);

                SmsChat.searchRecipient = true;
                requests.send({
                    method: 'searchRecipient',
                    params: {
                        search: search,
                        mode: SmsChat.mode
                    }
                });
            }

            if (search.length <= 1 && SmsChat.searchRecipient) {
                SmsChat.searchRecipient = false;
                SmsChat.recipientsData = [];
            }
        },

        searchMode: function () {
            if (prevMode === null) {
                prevMode = SmsChat.mode;
            }

            SmsChat.mode = 'search';
            SmsChat.searchCurrent = 0;

            if ($(config.ui.chat_box_search).is(':visible')) {
                return false;
            }

            $(config.ui.chat_box_history_container).find('.tab-pane.active').removeClass('active');
            $(config.events.change_chatbox).prop('disabled', true).addClass('disabled');
            // switch to ALL tab
            $(config.events.change_chatbox + '[href="#chat_box"]').tab('show');

            $(config.events.smsnav).removeClass('active');
            SmsChat.render(SmsChat.config.ui.sms_preloader_tpl, SmsChat.config.ui.chat_box_search);
            $(config.ui.chat_box_search).addClass('active');
            $(config.events.smsSearchClear).fadeIn('slow');
        },

        smsToList: function () {
            $('.smssection .sms-history').show();
            $('.smssection .sms').hide();
        },

        listMode: function () {
            if (prevMode !== null) {
                SmsChat.mode = prevMode;
                prevMode = null;
            }

            const switchMode = SmsChat.mode !== '' && SmsChat.mode !== 'all' ? '_' + SmsChat.mode : '';

            SmsChat.searchValue = '';
            $(config.ui.chat_box_history_container).find('.tab-pane.active').removeClass('active');

            if ($(config.ui.chat_box + switchMode).is(':visible')) {
                return false;
            }

            let activeNumber = null;
            const activeChat = $(config.ui.chat_box + switchMode).find('li.active');

            if (activeChat.length) {
                activeNumber = activeChat.find('a.chat_box_button').data('number');
            }

            $(config.events.change_chatbox).prop('disabled', false).removeClass('disabled');
            $(config.events.change_chatbox + '[href="#chat_box' + switchMode + '"]').tab('show');

            // show.bs.tab does not run on the same tab
            if (SmsChat.mode === '' || SmsChat.mode === 'all') {
                _private.openMessenger();
            }

            $(config.ui.searchButtonsBlock).hide();
            $(config.ui.chat_box + switchMode).addClass('active');
            $(config.ui.chat_box_search).removeClass('active');

            // load chat that was active before the search
            if (activeNumber) {
                $(config.ui.chat_box + switchMode).find('li.chat_box_button_' + activeNumber).addClass('active');
                setTimeout(function () {
                    $('#chat_box_' + activeNumber).addClass('active');
                    $(config.ui.chat_box + switchMode).find('li.chat_box_button_' + activeNumber).find('a.chat_box_button').click();
                }, 500);
            } else {
                setTimeout(function () {
                    $(config.ui.chat_box + switchMode).find('li.active').removeClass('active');
                }, 200);
            }
        },

        smsSearchClear: function () {
            $(config.events.smsSearch).val('');
            SmsChat.searchValue = '';
            $(config.events.smsSearchClear).fadeOut('fast');
            $(config.ui.messagesWrapperVisible).find(config.ui.searchButtonsBlock).hide();
            $(config.ui.chat_box_history_container).find('.tab-pane.active').removeClass('active');
            SmsChat.searchChatboxes = [];
            _private.listMode();
        },

        smsSearchNextPrev: function () {
            const action = $(this).data('action');
            const searchResults = $(config.ui.messagesWrapperVisible).find('.search-highlight').length;

            if ((action === 'prev' && !SmsChat.searchCurrent) ||
                    (action === 'next' && SmsChat.searchCurrent === searchResults - 1)) {
                return false;
            }

            if (action === 'prev') {
                const btnNext = $(this).siblings('[data-action="next"]');
                if (btnNext.prop('disabled')) {
                    btnNext.prop('disabled', false).removeClass('disabled');
                }

                SmsChat.searchCurrent--;
            }
            else if (action === 'next') {
                const btnPrev = $(this).siblings('[data-action="prev"]');
                if (btnPrev.prop('disabled')) {
                    btnPrev.prop('disabled', false).removeClass('disabled');
                }

                SmsChat.searchCurrent++;
            } else {
                return false;
            }

            if ((action === 'prev' && !SmsChat.searchCurrent) ||
                    (action === 'next' && SmsChat.searchCurrent === searchResults - 1)) {
                $(this).prop('disabled', true).addClass('disabled');
            }

            _private.scrollToSearch();
        },

        showSearchButtons: function () {
            const msgSelector = $(config.ui.messagesWrapperVisible);
            const searchResults = msgSelector.find('.search-highlight').parent().length;
            const searchBtnBlock = msgSelector.prev('.chat-header').find(config.ui.searchButtonsBlock);

            if (searchResults > 1) {
                const btnPrev = searchBtnBlock.find('[data-action="prev"]');
                const btnNext = searchBtnBlock.find('[data-action="next"]');

                $(config.ui.searchButtonsBlock).fadeIn('slow');

                if (SmsChat.searchCurrent === 0) {
                    btnPrev.prop('disabled', true).addClass('disabled');
                    btnNext.prop('disabled', false).removeClass('disabled');
                }
                else if (SmsChat.searchCurrent === searchResults - 1) {
                    btnNext.prop('disabled', true).addClass('disabled');
                    btnPrev.prop('disabled', false).removeClass('disabled');
                } else {
                    btnPrev.prop('disabled', false).removeClass('disabled');
                    btnNext.prop('disabled', false).removeClass('disabled');
                }
            } else {
                searchBtnBlock.hide();
            }
        },

        scrollToSearch: function () {
            const selector = $(config.ui.messagesWrapperVisible);
            const search = selector.find('.search-highlight').eq(SmsChat.searchCurrent);

            if (search.length) {
                const msgScrollTopMax = selector[0].scrollTop;
                const searchFirstTop = search.closest('.message-row').position().top;
                const top = msgScrollTopMax + searchFirstTop;

                selector.scrollTop(top - 10);
            }
        },

        searchHighlight: function (parentBlock) {
            if (parentBlock && parentBlock !== '') {
                let elList = [];
                if (parentBlock === config.ui.chat_box_search) {
                    elList = [
                        '.sms-demo-name > strong',
                        '.message-preview'
                    ];
                }
                else if (parentBlock === config.ui.messagesWrapperVisible) {
                    elList = [
                        '.message'
                    ];
                }

                if (elList.length) {
                    $.each(elList, function(i, el) {
                        _private.highlighter(parentBlock + ' ' + el);
                    });
                }
            }
        },

        highlighter: function (element) {
            let search = $.trim($(config.events.smsSearch).val());

            if (element !== '' && SmsChat.searchValue !== '' && search !== '') {
                const searchPattern = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const reg = new RegExp(searchPattern, 'gi');
                const searchReplace = '<span class="search-highlight">' + search + '</span>';

                $(element).each(function (i, val) {
                    const text = val.innerText;

                    if (reg.test(text)) {
                        val.innerHTML = text.replace(reg, searchReplace);
                    }
                });
            }
        },

        smsSearch: function (event) {
            if (!_private.checkSearchInput(event)) {
                return false;
            }

            let search = $.trim($(config.events.smsSearch).val());

            if (search === '' && SmsChat.mode !== 'search') {
                $(config.events.smsSearch).val('');

                return false;
            }

            SmsChat.prevKeystrokeTime = !SmsChat.prevKeystrokeTime ? new Date().getTime() : SmsChat.prevKeystrokeTime;
            SmsChat.searchValue = _private.cleanNumber(search);

            _private.searchMode();

            if (SmsChat.searchValue.length >= messageSearchMinLength) {
                if (timerSearchRequest) {
                    // clear timeout with send request if typing continues
                    clearTimeout(timerSearchRequest);
                }

                const searchRequest = function() {
                    if (XHR && XHR.readyState !== 4) {
                        XHR.abort();
                    }

                    requests.send({
                        method: 'searchSms',
                        params: {
                            search: SmsChat.searchValue
                        }
                    });
                };

                const currTime = new Date().getTime();
                const timeLeft = currTime - SmsChat.prevKeystrokeTime;

                render(config.ui.sms_preloader_tpl, config.ui.chat_box_search);

                // if typing continues
                if (timeLeft > 5 && timeLeft <= searchKeyPressInterval) {
                    // if all characters were typed quickly and the typing stopped
                    timerSearchRequest = setTimeout(function() {
                        searchRequest();
                    }, searchKeyPressInterval + 100);

                    return false;
                } else {
                    timerSearchRequest = null;

                    searchRequest()
                }
            } else {
                SmsChat.searchChatboxes = [];
                render(config.ui.search_min_length_tpl, config.ui.chat_box_search);

                if (!search.length) {
                    $(config.events.smsSearchClear).fadeOut('fast');
                    _private.listMode();
                }
            }
        },

        checkSearchInput: function(event) {
            SmsChat.prevSearchEvent = !SmsChat.prevSearchEvent ? event.type : SmsChat.prevSearchEvent;

            // skip arrow keys, Shift, Ctrl, 'Process'
            if ((event.keyCode >= 37 && event.keyCode <= 40) ||
                event.keyCode === 16 || event.keyCode === 17 || event.keyCode === 229 ||
                // skip not accepted input types
                (event.type === 'input' && !acceptedInputTypes.includes(event.originalEvent.inputType)) ||
                // re-request prevention on 'paste', 'cut', 'backspace'
                (SmsChat.prevSearchEvent === 'input' &&
                    (event.keyCode === 86 || event.keyCode === 88 || event.keyCode === 8))) {
                return false;
            }

            return true;
        },

        sendSms: function (event) {
            if ((event.keyCode === 13 && !event.ctrlKey && !event.shiftKey) || event.type === 'click') {
                let number = $(this).data('number');
                const numbers = $(this).parents('.smschat:first').find(".select2-sendto").val();
                const textareaSelector = $(this).parents('.smschat:first').find(config.events.messageFieldSms);
                const message = $.trim($(textareaSelector).val().replace(/^\s+|\s+$/g, ""));

                if (!message) {
                    errorMessage('No message');
                    return false;
                }

                if (number) {
                    // number.toString().length > 15 - according to E.164 standard max 15 digits
                    if (number.toString().length < PHONE_CLEAN_LENGTH || number.toString().length > 15) {
                        errorMessage('Invalid phone number');
                        return false;
                    }

                    number = _private.cleanNumber(number);
                }
                else if (!number && numbers && numbers.toString().length >= PHONE_CLEAN_LENGTH) {
                    number = numbers.replace(cleanNumPattern, '');
                } else {
                    errorMessage('Invalid phone number');
                    return false;
                }

                requests.send({
                    method: 'sendSms',
                    params: {
                        message: message,
                        number: number,
                        type: 'chat_box'
                    }
                });
            }
        },

        changeNotifications: function () {
            if ($.cookie('sms_notifications') == '0') {
                $.cookie('sms_notifications', 1, {path: '/'});
            } else {
                $.cookie('sms_notifications', 0, {path: '/'});
            }
        },

        changeNotificationsButton: function () {
            if ($.cookie('sms_notifications') == '1') {
                $(config.events.changeNotifications).attr('title', 'Turn On Sms Notifications');
                $(config.events.changeNotifications).find('i').removeClass('fa-volume-off').addClass('fa-volume-up');
            } else {
                $(config.events.changeNotifications).attr('title', 'Turn Off Sms Notifications');
                $(config.events.changeNotifications).find('i').removeClass('fa-volume-up').addClass('fa-volume-off');
            }
        },

        getMessengerCounter: function () {
            requests.send({
                method: 'getSmsCounter'
            });
        },

        getUserSmsLimit: function () {
            requests.send({
                method: 'getUserSmsLimit'
            });
        },

        openMessenger: function (mode) {
            const offset = 0;
            mode = mode || 'all';

            if ($.cookie('sms_notifications') == '0') {
                $(config.events.changeNotifications).attr('title', 'Turn On Sms Notifications');
                $(config.events.changeNotifications).find('i').removeClass('fa-volume-up').addClass('fa-volume-off');
            }
            requests.send({
                method: 'openMessenger',
                params: {
                    mode: mode,
                    offset: offset,
                }
            });
        },

        loadNextChats: function () {
            let offset = SmsChat.chatboxes.all.length;
            requests.send({
                method: 'openMessenger',
                params: {
                    offset: offset
                }
            });
        },

        loadHistory: function (loadNumber) {
            SmsChat.historyClickAction = typeof loadNumber === 'object' && loadNumber.type === 'click';

            const offset = 0;
            let number = $(this).data('number') ? $(this).data('number') : loadNumber;

            if (typeof number !== 'object') {
                number = _private.cleanNumber(number);

                if ($(window).width() < 768) {
                    $('.smssection .sms-history').hide();
                }
                $('.smssection .sms').show();

                let lastId = null;

                if (SmsChat.chatboxesHistory[number.toString()] && SmsChat.mode !== 'search' && !SmsChat.historyClickAction) {
                    lastId = SmsChat.chatboxesHistory[number.toString()][SmsChat.chatboxesHistory[number.toString()].length - 1].sms_id;
                }

                requests.send({
                    method: 'getSmsHistory',
                    params: {
                        number: number,
                        offset: offset,
                        lastId: lastId,
                    }
                });
            } else {
                return false;
            }
        },

        smsChatWasRead: function (number) {
            requests.send({
                method: 'smsChatWasRead',
                params: {
                    number: number
                }
            });
        },

        refreshChatboxes: function () {
            requests.send({
                method: 'refreshChatboxes',
            });
        },

        loadNextHistory: function () {
            const number = $(this).data('number');
            let offset = SmsChat.chatboxesHistory[number.toString()].length;

            requests.send({
                method: 'getSmsHistory',
                params: {
                    number: number,
                    offset: offset
                }
            });
        },

        setSmsUnread: function () {
            requests.send({
                method: 'setSmsUnread',
                params: {
                    number: $(this).data('number')
                }
            });
        },
    };

    let ui = {};

    let requests = {
        send: function (data) {
            let url = config.routes[data.method];
            console.log('send=>', data.method, url, data);
            if (url) {
                if (SmsChat.activeWindow) {
                    XHR = $.ajax({
                        type: 'POST',
                        url: url,
                        data: data,
                        global: false,
                        success: function (response) {
                            console.log('response=>', response);

                            if (response.status === 'ok') {
                                // if some numbers are invalid
                                if (response.errors && $.isArray(response.errors)) {
                                    errorMessage(response.errors.join("\n"));
                                }

                                let method = data.method + 'Callback';
                                if (method in callback) {
                                    callback[method](response.result);
                                }
                            }
                            else if (response.status === 'error') {
                                let errorMsg = 'Error';

                                if (response.error) {
                                    errorMsg = response.error;
                                }
                                else if (response.errors) {
                                    if ($.isArray(response.errors)) {
                                        errorMsg = response.errors[Object.keys(response.errors)[0]][0];

                                        if (errorMsg.length === 1) {
                                            errorMsg = response.errors.join("\n");
                                        }
                                    }
                                }

                                errorMessage(errorMsg);
                            }

                            XHR = null;
                        }
                    });
                } else {
                    SmsChat.isSocketActions = true;
                }
            } else {
                ws.send(data);
            }
        }
    };

    let public = {
        render: render,
        _private: _private,
        renderAppend: renderAppend,
        renderPrepend: renderPrepend,
        phoneNumberInfo: phoneNumberInfo,
        config: config,
        chatboxes: chatboxes,
        chatboxesHistory: chatboxesHistory,
        recipientsData: recipientsData,
        mode: mode,
        cookieRegistry: cookieRegistry,
        messagesShowLimit: messagesShowLimit,
        chatsShowLimit: chatsShowLimit,
        searchValue: searchValue,
        searchCurrent: searchCurrent,
        newMessageSent: newMessageSent,
        prevSearchEvent: prevSearchEvent,
        prevKeystrokeTime: prevKeystrokeTime,
        isSocketActions: isSocketActions,
        activeWindow: activeWindow,
    };

    _private.init();

    return public;
}();

callback.refreshChatboxesCallback = function(data) {
    const incoming = data && data.incoming;
    let withOpen = incoming || data.user_sms_limit || data.count_unreaded;

    // used for updating history
    if (data.number) {
        SmsChat.updatedChatNumber = Array.isArray(data.number) ? data.number : [data.number];
    }

    callback.getSmsCounterCallback(data.count_unreaded);

    if ($(SmsChat.config.ui.messenger).css('display') === 'block' && !data.count_unreaded_only && withOpen) {
        const mode = $('.mode-nav > li.active > a.change-chatbox').data('mode');
        SmsChat._private.openMessenger(mode);
    }

    if (data.user_sms_limit) {
        callback.getUserSmsLimitCallback(data.user_sms_limit);
    }

    if(incoming && $.cookie('sms_notifications') == '1') {
        ion.sound.play("Note");
    }

    if (!SmsChat.activeWindow) {
        SmsChat.isSocketActions = true;
    } else {
        SmsChat.isSocketActions = false;
    }
};

callback.openMessengerCallback = function (data) {
    const mode = data.mode === undefined || data.mode === 'all' ? '' : '_' + data.mode;

    if (!data.offset) {
        SmsChat.chatboxes[data.mode] = data.rows;
        const active_chat_number = $(SmsChat.config.ui.chat_box + mode + ' > li.active > a.chat_box_button').data('number') || false;
        SmsChat.render(SmsChat.config.ui.chat_box_tpl, SmsChat.config.ui.chat_box + mode, SmsChat.chatboxes[data.mode]);

        $.each(SmsChat.chatboxes[data.mode], function (key, val) {
            if (!$('#chat_box_' + val.sms_number).length) {
                SmsChat.renderAppend(SmsChat.config.ui.chat_box_history_container_tpl, SmsChat.config.ui.chat_box_history_container, val);
            }
        });

        if (active_chat_number) {
            $(SmsChat.config.ui.chat_box + mode + ' > li.chat_box_button_' + active_chat_number).addClass('active');
        }

        if (!$('#chat_box_new_message').length)
            SmsChat.renderAppend('#chat_box_new_message_container_tpl', SmsChat.config.ui.chat_box_history_container);

        autosize(document.getElementsByClassName('message-field-sms'));
        $('.message-field-sms').on('autosize:resized', function () {
            $(this).parents('.smschat:first').find('.messages-wrapper').css('bottom', (parseInt($(this).height()) + 34) + 'px');
        });

        if(data.rows.length == SmsChat.chatsShowLimit) {
            SmsChat.renderAppend(SmsChat.config.ui.chat_box_load_more_tpl, SmsChat.config.ui.chat_box);
        }
        $(SmsChat.config.ui.chatboxes_preloader).remove();
        $(SmsChat.config.ui.sms_history).show();//.fadeIn();
    } else {
        $(SmsChat.config.events.loadMoreChatboxes).parent().remove();
        SmsChat.chatboxes[data.mode] = SmsChat.chatboxes[data.mode].concat(data.rows);
        SmsChat.renderAppend(SmsChat.config.ui.chat_box_tpl, SmsChat.config.ui.chat_box, data.rows);
        SmsChat.renderAppend(SmsChat.config.ui.chat_box_history_container_tpl, SmsChat.config.ui.chat_box_history_container, data.rows);

        if(data.rows.length == SmsChat.chatsShowLimit) {
            SmsChat.renderAppend(SmsChat.config.ui.chat_box_load_more_tpl, SmsChat.config.ui.chat_box);
        }
    }

    // update message history window
    setTimeout(function () {
        const activeChatBox = $(SmsChat.config.ui.chat_box_history_container).find('.active:visible');
        if (activeChatBox.length) {
            const activeNumber = activeChatBox.attr('id').replace('chat_box_', '');

            if (activeNumber !== 'new_message' && $.inArray(activeNumber, SmsChat.updatedChatNumber) !== -1) {
                SmsChat.updatedChatNumber = null;
                SmsChat._private.loadHistory(activeNumber);
            }
        }
        else if (SmsChat.newMessageSent) {
            // show new message sent to new number
            const newSentNumber = data.rows[0].sms_number;
            const newSentBox = $(SmsChat.config.ui.chat_box + mode + ' > li.chat_box_button_' + newSentNumber);

            if (newSentBox.length) {
                newSentBox.addClass('active');
                SmsChat._private.loadHistory(newSentNumber);
                $('#chat_box_' + newSentNumber).addClass('active');
            }

            SmsChat.newMessageSent = false;
        }

        $('a.set_unread_chat_box').tooltip();
    }, 500);
};

callback.getSmsHistoryCallback = function (data) {
    const selector = '#chat_box_' + data.number + ' .smschat .messages-wrapper';

    if (data.rows.length) {
        data.rows.sort(function(a, b) {
            const dateA = new Date(a.sms_date);
            const dateB = new Date(b.sms_date);

            return dateA - dateB;
        });

        const currScroll = $(selector).scrollTop();
        const selectorHeight = $(selector)[0].scrollHeight - $(selector).outerHeight() - 10;

        if (!data.offset) {
            let firstLoad = false;
            if (SmsChat.chatboxesHistory[data.number.toString()] && SmsChat.mode !== 'search' && !SmsChat.historyClickAction) {
                $.views.helpers.isNewDate(data.rows[0].sms_date);
                SmsChat.chatboxesHistory[data.number.toString()] = SmsChat.chatboxesHistory[data.number.toString()].concat(data.rows);
                SmsChat.renderAppend(SmsChat.config.ui.message_tpl, selector, data.rows);
            } else {
                firstLoad = true;
                SmsChat.chatboxesHistory[data.number.toString()] = data.rows;
                SmsChat.render(SmsChat.config.ui.message_tpl, selector, SmsChat.chatboxesHistory[data.number.toString()]);
            }

            if (data.rows.length == SmsChat.messagesShowLimit) {
                SmsChat.renderPrepend(SmsChat.config.ui.chat_box_history_load_more_tpl, selector, {number: data.number.toString()});
            }
            $(selector).show();//fadeIn('slow');

            if (firstLoad) {
                autosize(document.getElementsByClassName('message-field-sms'));
                $(selector).scrollTop($(selector)[0] ? $(selector)[0].scrollHeight : 0);
                $('.message-field-sms').on('autosize:resized', function () {
                    /*if($(this).parents('.smschat:first').find('.messages-wrapper').height() < 100) {
                        console.log($(this).parents('.smschat:first').find('.messages-wrapper').height());
                        $(this).css('height', ($(this).height() - 20) + 'px');
                        return false;
                    }
                    else {*/
                    $(this).parents('.smschat:first').find('.messages-wrapper').css('bottom', (parseInt($(this).height()) + 34) + 'px');
                    /*}*/
                });
            }
        } else {
            $(SmsChat.config.events.loadMoreHistory).parent().remove();
            $.views.helpers.isNewDate(SmsChat.chatboxesHistory[data.number.toString()][SmsChat.chatboxesHistory[data.number.toString()].length - 1].sms_date);
            SmsChat.chatboxesHistory[data.number.toString()] = data.rows.concat(SmsChat.chatboxesHistory[data.number.toString()]);
            var current_top_element = $(selector).find('.message-row:first');
            SmsChat.renderPrepend(SmsChat.config.ui.message_tpl, selector, data.rows);
            var previous_height = 0;
            current_top_element.prevAll().each(function () {
                previous_height += $(this).outerHeight();
            });

            $(selector).scrollTop(previous_height - 61);

            if (data.rows.length == SmsChat.messagesShowLimit) {
                SmsChat.renderPrepend(SmsChat.config.ui.chat_box_history_load_more_tpl, selector, {number: data.number.toString()});
            }
        }
        $('[data-toggle="tooltip"]').tooltip();

        const unreaded = $(selector).find('.unreaded-message').last();

        if (unreaded.length) {
            if (SmsChat.historyClickAction) {
                setTimeout(function() {
                    SmsChat._private.smsChatWasRead(data.number);
                }, 800);
            } else {
                if (currScroll >= selectorHeight) {
                    // set readed if new message is visible
                    $(selector).scrollTop($(selector)[0].scrollHeight);
                    setTimeout(function() {
                        SmsChat._private.smsChatWasRead(data.number);
                    }, 800);
                } else {
                    // set readed when scrolled to new message
                    const msgScrollTopMax = $(selector).scrollTop();
                    const parentUnreaded = unreaded.closest('.message-row');
                    const unreadedEl = parentUnreaded.position().top + parentUnreaded.height() + 5;
                    const bottom = msgScrollTopMax + unreadedEl;
                    const checkPos = bottom - $(selector).height();

                    $(selector).bind('scroll.unreaded', function () {
                        const selectorScroll = $(this).scrollTop();

                        if (selectorScroll >= checkPos) {
                            $(this).unbind('scroll.unreaded');
                            SmsChat._private.smsChatWasRead(data.number);

                            return false;
                        }
                    });
                }
            }
        }
        else if (currScroll >= selectorHeight) {
            $(selector).scrollTop($(selector)[0].scrollHeight);
        }
    }

    if (SmsChat.mode === 'search') {
        SmsChat.searchCurrent = 0;
        SmsChat._private.searchHighlight(SmsChat.config.ui.messagesWrapperVisible);
        SmsChat._private.scrollToSearch();
        SmsChat._private.showSearchButtons();
    }
}

callback.sendSmsCallback = function (data) {
    successMessage('Message sent');
    SmsChat.newMessageSent = true;

    const textareaSelector = $('#chat_box_history_container > .active > .smschat > .chat-footer').find(SmsChat.config.events.messageFieldSms);
    $(textareaSelector).val('');
    autosize.destroy(textareaSelector[0]);
    $(textareaSelector).focus();
    $(textareaSelector).css('height', '34px');
    autosize(textareaSelector[0]);

    const newMsgBox = $('#chat_box_new_message.active');

    if (newMsgBox.length) {
        $(".select2-sendto").val(null).trigger('change');
        newMsgBox.removeClass('active');
    }
}

callback.newSmsMessageCallback = function (data) {
    var selector = '#chat_box_' + data.number + ' .smschat .messages-wrapper';
    var buttonSelector = '.chat_box_button_' + data.number.toString();
    SmsChat._private.getMessengerCounter();

    if (SmsChat.chatboxes.all.length) {
        if (!$('.chat_box_button_' + data.number).length) {
            SmsChat.chatboxes.all = [data.smschatbox].concat(SmsChat.chatboxes.all);
            SmsChat.renderPrepend(SmsChat.config.ui.chat_box_tpl, SmsChat.config.ui.chat_box, data.smschatbox);

            if (data.smschatbox.emp_phone && SmsChat.chatboxes.users.length) {
                SmsChat.chatboxes.users = [data.smschatbox].concat(SmsChat.chatboxes.users);
                SmsChat.renderPrepend(SmsChat.config.ui.chat_box_tpl, SmsChat.config.ui.chat_box + '_users', data.smschatbox);
            }
            if (!data.smschatbox.emp_phone && SmsChat.chatboxes.clients.length) {
                SmsChat.chatboxes.clients = [data.smschatbox].concat(SmsChat.chatboxes.clients);
                SmsChat.renderPrepend(SmsChat.config.ui.chat_box_tpl, SmsChat.config.ui.chat_box + '_clients', data.smschatbox);
            }

            SmsChat.renderPrepend(SmsChat.config.ui.chat_box_history_container_tpl, SmsChat.config.ui.chat_box_history_container, data.smschatbox);
        } else {
            SmsChat.render(SmsChat.config.ui.chat_box_tpl_partial, buttonSelector, data.smschatbox);
            $(SmsChat.config.ui.chat_box).prepend($(buttonSelector));
            if (data.smschatbox.emp_phone && SmsChat.chatboxes.users.length) {
                $(SmsChat.config.ui.chat_box + '_users').prepend($(buttonSelector));
            }
            if (!data.smschatbox.emp_phone && SmsChat.chatboxes.clients.length) {
                $(SmsChat.config.ui.chat_box + '_clients').prepend($(buttonSelector));
            }
            if ($(buttonSelector).is('.active'))
                $(SmsChat.config.ui.chat_box).scrollTop(0);
        }
    }

    if (SmsChat.chatboxesHistory[data.number.toString()] != undefined) {
        $.views.helpers.isNewDate(new Date());
        SmsChat.chatboxesHistory[data.number.toString()] = SmsChat.chatboxesHistory[data.number.toString()].concat(data.rows);
        SmsChat.renderAppend(SmsChat.config.ui.message_tpl, selector, data.rows);
        $(selector).scrollTop($(selector)[0].scrollHeight);
        $('[data-toggle="tooltip"]').tooltip();
    }
}

callback.updateSmsStatusCallback = function (data) {
    const tooltipObj = $('#chat-sms-' + data.sms_id).find('.sms-status');
    let badgeClass = '';
    let tooltip = $.views.helpers.ucfirst(data.sms_status);
    if (data.sms_status === 'undelivered') {
        tooltip += '<br>' + data.sms_error;
        badgeClass = 'bg-danger';
    }
    if (data.sms_status === 'delivered') {
        badgeClass = 'bg-success';
    }
    if (data.sms_status !== 'delivered' && data.sms_status !== 'undelivered') {
        badgeClass = 'bg-warning';
    }
    $(tooltipObj).attr('data-original-title', tooltip);
    $(tooltipObj).removeClass('bg-warning').removeClass('bg-danger').addClass(badgeClass);
}

callback.getSmsCounterCallback = function (data) {
    $.views.helpers.smsCounter = data;

    if ($.views.helpers.smsCounter > 0) {
        $(SmsChat.config.events.messengerCounter).text($.views.helpers.smsCounter);
    } else {
        $(SmsChat.config.events.messengerCounter).text('');
    }
}

callback.getUserSmsLimitCallback = function (data) {
    if (!SMS_UNLIMITED) {
        const limitInfo = $('.messenger-limit-info');

        if (limitInfo.length) {
            if (data.remain > 0 && data.limit > 0) {
                limitInfo.attr('data-original-title', data.remain + '&nbsp;/&nbsp;' + data.limit);
            } else {
                limitInfo.removeClass('bg-info').addClass('bg-danger')
                    .attr('data-original-title', 'You&nbsp;can\'t&nbsp;send&nbsp;SMS. Please&nbsp;increase&nbsp;limit&nbsp;of SMS&nbsp;messages');
            }
        }
    }
}

callback.smsChatWasReadCallback = function (data) {
    const number = data.toString();
    const unreaded = $('#chat_box_' + number + ' .smschat .messages-wrapper').find('.unreaded-message');
    const buttonSelector = $('.chat_box_button_' + number);

    if (buttonSelector.find('.unreaded').length) {
        buttonSelector.find('.unreaded').remove();
    }

    buttonSelector.find('.set_unread_chat_box').removeClass('hidden');

    if (unreaded.length) {
        unreaded.removeClass('unreaded-message');
    }
}

callback.searchSmsCallback = function (data) {
    SmsChat.prevSearchEvent = null;
    SmsChat.prevKeystrokeTime = null;
    SmsChat.searchChatboxes = data.rows;

    // show results or `No matches found` text
    if (SmsChat.searchChatboxes.length) {
        SmsChat.render(SmsChat.config.ui.chat_box_tpl, SmsChat.config.ui.chat_box_search, SmsChat.searchChatboxes);
        SmsChat.render(SmsChat.config.ui.chat_box_history_container_tpl, SmsChat.config.ui.chat_box_history_container, SmsChat.searchChatboxes);

        SmsChat._private.searchHighlight(SmsChat.config.ui.chat_box_search);
    } else {
        SmsChat.render(SmsChat.config.ui.search_no_data_tpl, SmsChat.config.ui.chat_box_search);
    }
}

callback.searchRecipientCallback = function (data) {
    SmsChat.prevSearchEvent = null;
    SmsChat.recipientsData = data.rows;
}

callback.setSmsUnreadCallback = function (data) {
    const number = data.number.toString();

    if (!number) {
        errorMessage('Error. Message status not changed.');
        return false;
    }

    const setUnreadBlock = $('.chat_box_button_' + number).find('a.set_unread_chat_box');

    if (setUnreadBlock.length) {
        setUnreadBlock.tooltip('hide');
        const parent = setUnreadBlock.parent();
        parent.find('.chat_box_button').prepend('<span class="inline pos-abt unreaded"></span>');
        setUnreadBlock.addClass('hidden');

        if (parent.hasClass('active')) {
            parent.removeClass('active');
            const activeChat = parent.find('.chat_box_button').attr('href');
            $(activeChat).removeClass('active');
        }
    }
}

$.views.settings.allowCode(true);
$.views.helpers({
    baseUrl: baseUrl,

    smsDate: '',
    smsCounter: 0,

    getBaseUrl: function () {
        return this.baseUrl;
    },

    isNewDate: function (val) {
        const messDate = $.views.helpers.getMessageDate(val);

        if ($.views.helpers.smsDate != messDate) {
            $.views.helpers.smsDate = messDate;
            return true;
        }
        return false;
    },

    substrName: function (name) {
        if (!name)
            return 'NA';

        if ($.isArray(name)) {
            if (typeof name[0] === 'undefined' || name[0].cc_name === '') {
                return 'NA';
            }

            name = name[0].cc_name;
        }
        name = name.toString();
        const split = name.split(' ');

        if (split.length > 1) {
            return split[0].substr(0, 1) + split[1].substr(0, 1);
        }

        return name.substr(0, 2);
    },

    getDateFormat: function (val) {
        val = typeof (val) != 'string' ? val : val.replace(/-/g, '/');
        const date = new Date(val);
        const dateYest = new Date();
        dateYest.setDate(dateYest.getDate() - 1);

        const dateLocaleString = date.toLocaleString('en-us', {day: '2-digit', month: '2-digit', year: 'numeric'});
        const todayLocaleString = new Date().toLocaleString('en-us', {day: '2-digit', month: '2-digit', year: 'numeric'});

        if (dateLocaleString === todayLocaleString) {
            return $.views.helpers.getTimeFormat(val);
        }

        const yestLocaleString = dateYest.toLocaleString('en-us', {day: '2-digit', month: '2-digit', year: 'numeric'});

        if (dateLocaleString === yestLocaleString) {
            return 'Yesterday';
        }

        const month = date.toLocaleString('en-us', {month: "2-digit"});
        const day = date.toLocaleString('en-us', {day: "2-digit"});
        const year = date.toLocaleString('en-us', {year: "numeric"});
        return day + '.' + month + '.' + year;
    },

    getTimeFormat: function (val) {
        val = typeof (val) != 'string' ? val : val.replace(/-/g, '/');
        return new Date(val).toLocaleString(undefined, {hour: "2-digit", minute: "2-digit"});
    },

    getMessageDate: function (val) {
        val = typeof (val) != 'string' ? val : val.replace(/-/g, '/');
        const date = new Date(val);
        const month = date.toLocaleString('en-us', {month: "2-digit"});
        const day = date.toLocaleString('en-us', {day: "2-digit"});
        const year = date.toLocaleString('en-us', {year: "numeric"});
        return day + '.' + month + '.' + year;
    },

    getHistoryDateFormat: function (val) {
        val = typeof (val) != 'string' ? val : val.replace(/-/g, '/');
        const date = new Date(val);
        const weekday = date.toLocaleString('en-us', {weekday: 'short'});
        const month = date.toLocaleString('en-us', {month: 'short'});
        const day = date.toLocaleString('en-us', {day: '2-digit'});
        const year = date.toLocaleString('en-us', {year: "numeric"});
        return weekday + ', ' + day + ' ' + month + ' ' + year;
    },

    trimNumber: function (number) {
        if (number && number.length) {
            const phoneCode = PHONE_COUNTRY_CODE.replace('+', '');
            if (number.length - phoneCode.length === PHONE_CLEAN_LENGTH) {
                number = SmsChat._private.cleanNumber(number);
            }
        }
        return number;
    },

    numberTo: function (number) {
        const numberInfo = SmsChat.phoneNumberInfo(number);

        if (!numberInfo) {
            return false;
        }

        let reg = PHONE_MASK_REGEX_PATTERN;
        let pattern = PHONE_MASK_REGEX_PATTERN_PREVIEW;

        if (number.length !== PHONE_CLEAN_LENGTH) {
            number = numberInfo.nationalNumber;
            reg = /^(\d{2,3})(\d{3})(\d{4})(\d{0,})$/;
            pattern = '($1) $2-$3';
        }

        return '+' + numberInfo.countryCallingCode + ' ' + number.toString().replace(reg, pattern);
    },

    numberToE164: function (number) {
        const numberInfo = SmsChat.phoneNumberInfo(number);

        if (!numberInfo) {
            return false;
        }

        return numberInfo.number;
    },

    ucfirst: function (string) {
        if (string === undefined) {
            return false;
        }

        return string.charAt(0).toUpperCase() + string.slice(1);
    },

    splitName: function (data) {
        if (!data || !$.isArray(data)) {
            return '';
        }

        const realArrLength = $.views.helpers.arrLength(data);

        let name = '';
        $.each(data, function(key, val) {
            if (val.cc_name !== '') {
                name += val.cc_name + (realArrLength !== key + 1 ? ', ' : '');
            }
        });

        return name;
    },

    arrLength: function(arr) {
        if (!$.isArray(arr)) {
            return false;
        }

        return arr.filter(function(v) {
            return v.cc_name !== '';
        }).length
    },
});

//module.exports = SmsChat;
