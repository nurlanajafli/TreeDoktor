var ClientDetails = function () {
    const config = {
        ui: {
            tabsBlock: '.client-files-tabs',
            changeLocationListItem: '#select-location-dropdown>li',
            locationSelectedLabel: '#location-selected-label',
            badgeCountNoFilter: '.status-count-no-filter',
            badgeCountWithFilter: '.status-count-with-filter',
            selectLeadAddressSingle: '.select-lead-address-single',
            selectLeadAddressMulti: '.select-lead-address-multi',
        },

        events: {
            tab: 'a.change-client-files',
            btnDelete: 'a.btnDelete',
            leadCommentNote: '.lead_comment_note',
            leadNoteInput: '.leadNoteInput',
            changeInvoiceLikeBtn: '.changeInvoiceLikeBtn',
            actionsList: '.actionsList',
            addCall: '.addCall',
            changeLocationOption: '.change-location',
        },

        route: {
            getClientFiles: '/clients/clientsFiles/getClientFiles',
            getClientLocations: '/clients/clientsFiles/getClientLocations',
            saveLeadNote: '/clients/ajax_save_lead_note',
            changeInvoiceLike: '/clients/ajax_change_like',
            sendVoiceMsg: '/client_calls/send_voice_msg',
            getBillingDetails: '/clients/ajax_get_billing_details',
            deleteBilling: '/clients/ajax_delete_billing',
            changeTaskStatus: '/tasks/ajax_change_status',
        },

        templates: {
            clientFiles:'#client_files_tpl',
            clientFilesView:'#client_files_view_tpl',
            clientLocations:'#client_locations_filter_tpl',
            clientLocationsView:'#client_locations_filter_view_tpl',
        },

        views: {
            filesBlock: '#client-files-block',
        },
    };

    let loaded = [];
    const leadNote = {};
    let prevTab, prevAddress;

    const _private = {

        address: undefined,

        init: function() {
            $('.datepicker').datepicker({format: $('#php-variable').val()});
            initQbLogPopover();

            const tabs = $(config.ui.tabsBlock).find('li > a');

            $.each(tabs, function (i, tab) {
                const status = $(tab).data('status');
                const defaultTab = $(tab).data('defaultTab');

                let activeTab = null;
                let renderMethod = null;

                if (defaultTab) {
                    prevTab = status;
                    activeTab = ' active';
                } else {
                    renderMethod = 'append';
                }

                const data = {
                    status: status,
                    activeTab: activeTab
                };

                _private.render(config.templates.clientFiles, config.views.filesBlock, data, renderMethod);
            });

            const data = {
                client_id: $(tabs[0]).data('client_id'),
                address: _private.address || null,
                status: prevTab,
            };

            _private.getClientFiles(data);
            _private.getClientLocations({
                client_id: $(tabs[0]).data('client_id')
            });

            if (itemsForSelect2 || selectTagsEstimators) {
                _private.initNewLeadSelect2();
            }
        },

        initNewLeadSelect2: function () {
            if (itemsForSelect2.services) {
                initSelect2($('#new_lead form').find("input.est_services"), itemsForSelect2.services, 'Select Services');
            }
            if(itemsForSelect2.products) {
                initSelect2($('#new_lead form').find("input.est_products"), itemsForSelect2.products, 'Select Products');
            }
            if(itemsForSelect2.bundles) {
                initSelect2($('#new_lead form').find("input.est_bundles"), itemsForSelect2.bundles, 'Select Bundles');
            }
            if(selectTagsEstimators) {
                initSelect2($('#new_lead form').find("input.estimators"), selectTagsEstimators, 'Select Estimators', false);
            }
        },

        getClientFiles: function(data) {
            Common.request.send(
                config.route.getClientFiles,
                data,
                _private.renderFiles,
                function (response) { return false; },
                false
            );
        },

        getClientLocations: function(data) {
            Common.request.send(
                config.route.getClientLocations,
                data,
                _private.handleClientLocations,
                function (response) { return false; },
                false
            );
        },

        handleClientLocations: function (response) {
            _private.renderLocations(response);

            // init select address in new lead modal
            if (response.data && response.data.length) {
                if (response.data.length > 1 || (response.data.length === 1 && response.data[0].lead_address !== defaultClientSelectedAddress.id)) {
                    $(config.ui.selectLeadAddressMulti).removeClass('hidden');

                    const selectData = response.data.reduce(function (acc, obj) {
                        if (!acc.length) {
                            acc.push(defaultClientSelectedAddress);
                        }

                        if (obj.lead_address !== defaultClientSelectedAddress.id) {
                            acc.push({
                                id: obj.lead_address,
                                text: obj.location,
                                newAddress: {
                                    new_address: obj.lead_address,
                                    new_city: obj.lead_city,
                                    new_state: obj.lead_state,
                                    new_zip: obj.lead_zip,
                                    new_country: obj.lead_country,
                                    stump_add_info: obj.lead_add_info,
                                    new_lat: obj.latitude,
                                    new_lon: obj.longitude,
                                }
                            });

                        }

                        return acc;
                    }, []);

                    const selectLeadAddress = $('#new_lead form').find("input.select_lead_address");
                    initSelect2(selectLeadAddress, JSON.stringify(selectData), 'Select lead address', false, false);

                    selectLeadAddress.on('change', function (obj) {
                        if (obj.added && obj.added.newAddress) {
                            $.each(obj.added.newAddress, function (key, val) {
                                $('#new_lead input[name="' + key + '"]').val(val);
                            });
                        }
                    });
                } else {
                    $(config.ui.selectLeadAddressSingle).removeClass('hidden');
                }
            }
        },

        filesChangeTab: function() {
            const target = $(config.ui.tabsBlock + '> li.active > a.change-client-files');
            const status = $(target).data('status');

            if (prevAddress === _private.address) {
                if (prevTab === status) {
                    return false;
                }

                if (loaded[status] !== undefined) {
                    return false;
                }
            } else {
                loaded = [];
            }

            prevTab = status;
            prevAddress = _private.address;

            const data = {
                status: status,
                address: _private.address,
                client_id: $(target).data('client_id')
            };

            return _private.getClientFiles(data);
        },

        renderFiles: function (response) {
            const status = response.status;

            loaded[status] = true;

            if (response.data === undefined) {
                response.data = [];
            }

            // update count badges
            if (response.count_statuses) {
                $(config.ui.badgeCountNoFilter).addClass('hidden');
                $.each(response.count_statuses, function (statusKey, statusCount) {
                    if ($(config.events.tab + '[data-status="' + statusKey + '"]').length) {
                        $(config.events.tab + '[data-status="' + statusKey + '"] > .badge >' + config.ui.badgeCountWithFilter).text(statusCount.count);
                    }
                });
            } else {
                $(config.ui.badgeCountNoFilter).removeClass('hidden');
                $(config.events.tab + ' > .badge >' + config.ui.badgeCountWithFilter).text('');
            }

            _private.render(
                config.templates.clientFilesView,
                '#' + status + '_files',
                {
                    data: response.data
                }
            );

            if (response.data.length) {
                setTimeout(function () {
                    initQbLogPopover();
                }, 500);
            }
        },

        renderLocations: function (response) {
            if (response.data === undefined) {
                response.data = [];
            }

            if(response.data.length) {
                _private.render(
                    config.templates.clientLocations,
                    config.templates.clientLocationsView,
                    {
                        data: response.data
                    }
                );
            }
        },

        showLeadCommentNote: function () {
            const obj = $(this);
            const text = $(obj).text();
            const id = $(obj).attr('data-id');
            const textarea = '<textarea data-id="' + id + '" class="form-control leadNoteInput" cols="100" rows="4">%s</textarea>';

            if (text === 'Click to edit') {
                $(obj).replaceWith(textarea.replace(/%s/, ''));
                $('.leadNoteInput').focus();
            } else {
                $(obj).replaceWith(textarea.replace(/%s/, text));
                $('.leadNoteInput').focus().val(text);
            }

            return false;
        },

        saveLeadNote: function () {
            const obj = $(this);
            const text = $(obj).val().trim();
            const id = $(obj).attr('data-id');
            const comment = '<strong class="lead_comment_note" data-id="' + id + '">%s</strong>';
            const defaultText = 'Click to edit';

            if (text === defaultText || (leadNote[id] === undefined && text === '')) {
                $(obj).replaceWith(comment.replace(/%s/, defaultText));

                return false;
            }

            if (leadNote[id] === text) {
                $(obj).replaceWith(comment.replace(/%s/, text));

                return false;
            }

            Common.request.send(
                config.route.saveLeadNote,
                { text: text, id: id },
                function (response) {
                    if (response.status === 'ok') {
                        if (text !== '') {
                            $(obj).replaceWith(comment.replace(/%s/, text));
                            leadNote[id] = text;

                            return false;
                        }
                    }

                    $(obj).replaceWith(comment.replace(/%s/, defaultText));
                    leadNote[id] = undefined;

                    return false;
                },
                function (response) {
                    $(obj).replaceWith(comment.replace(/%s/, defaultText));

                    return false;
                },
                false
            );

            return false;
        },

        changeInvoiceLike: function () {
            const obj = $(this);
            const data = {
                id: obj.data('id'),
                val: obj.data('like')
            };

            Common.request.send(
                config.route.changeInvoiceLike,
                data,
                function (response) {
                    const likeBtn = $('#feedback-' + data.id).find('.like-btn');
                    let margin = '';
                    let img = 'down';

                    if (response.status) {
                        margin = 'margin-top:-3px;';
                        img = 'up';
                    }

                    likeBtn.html('<img src="' + baseUrl + 'assets/img/' + img + '-sm.png" height="15" style="' + margin + 'margin-right: 3px;">Change');
                    likeBtn.data('like', response.status);
                    $('.feedback-link-' + data.id).html('<img src="' + baseUrl + 'assets/img/' + img + '-sm.png" height="15">');

                    return false;
                },
                function (response) { return false; },
                false
            );

            return false;
        },

        bodyClick: function (e) {
            if (!$('.actionsDropdown').is(e.target)
                && $('.actionsDropdown').has(e.target).length === 0
                && $('.open').has(e.target).length === 0
                && !$(e.target).is('.modal.fade')
                && !$(e.target).parents('.modal.fade').length
            ) {
                $('.actionsDropdown').removeClass('open');
                $('.actionsDropdown').parent().removeClass('open');
            }
        },

        render: function (template, view, data, renderMethod) {
            Common.renderView({
                template_id: template,
                view_container_id: view,
                render_method: renderMethod,
                data: [data],
                helpers: pub.helpers
            });
        },

        deleteFilesItem: function(e) {
            const obj = $(this);
            const msg = 'Are you sure you want to delete '
                + obj.data('title') + '? You can delete ' + (obj.data('text') !== '' ? obj.data('text') + ', ' : '')
                + 'client payments, scheduled items will be deleted as well.';

            if (confirm(msg)) {
                location.href = obj.attr('href');
            }

            return false;
        },

        addCall: function () {
            const obj = $(this);
            const data = {
                voice: $(obj).data('voice'),
                PhoneNumber: $(obj).data('number')
            }

            Common.request.send(
                config.route.sendVoiceMsg,
                data,
                function (response) {
                    return false;
                },
                function (response) { return false; },
                false
            );
        },

        closeEstSelect2: function () {
            $(this).find("input.est_services").select2('close');
        },

        showBillingDetails: function () {
            const data = {
                client_id: $('#billing_details').data('clientId')
            };

            Common.request.send(
                config.route.getBillingDetails,
                data,
                function (response) {
                    if (response.status === 'ok') {
                        $('#billing_details').find('.modal-body .cards-info').html('').append(response.html);
                    }
                },
                function (response) { return false; },
                false
            );
        },

        deleteCard: function () {
            const data = {
                card: $(this).parents('tr:first').attr('data-id'),
                id: $('#billing_details').data('clientId')
            };

            Common.request.send(
                config.route.deleteBilling,
                data,
                function (response) {
                    if (response.status === 'error') {
                        errorMessage(response.error || 'Delete card error');
                        return false;
                    }

                    $('#billing_details').find('[data-id="' + data.card + '"]').remove();
                },
                function (response) { return false; },
                false
            );
        },

        changeTaskStatus: function () {
            const id = $(this).parents('.modal.fade.overflow').data('task-id');
            const currentTaskElement = $('#task-' + id);

            const data = {
                id: id,
                status: currentTaskElement.find('.task_status_change').val(),
                text: currentTaskElement.find('.new_status_desc').val()
            };

            Common.request.send(
                config.route.changeTaskStatus,
                data,
                function (response) {
                    if (response.status === 'ok') {
                        location.reload();
                    } else {
                        errorMessage(response.msg || 'Task status change error');
                    }
                },
                function (response) { return false; },
                false
            );
        },

        changeLocation: function () {
            $(config.ui.changeLocationListItem + '.active').removeClass('active');
            $(this).parents('li:first').addClass('active');
            $(config.ui.locationSelectedLabel).text($.trim($(this).text()));
            _private.address = $(config.ui.changeLocationListItem + '.active>a').data('address');
            _private.filesChangeTab();
        },

    };

    const pub = {
        init: function(){
            $(document).ready(function() {
                pub.events();
                _private.init();
            });
        },

        events: function() {
            $(document).on('click', config.events.tab, _private.filesChangeTab);
            $(document).on('click', config.events.btnDelete, _private.deleteFilesItem);
            $(document).on('click', config.events.leadCommentNote, _private.showLeadCommentNote);
            $(document).on('focusout', config.events.leadNoteInput, _private.saveLeadNote);
            $(document).on('click', config.events.changeInvoiceLikeBtn, _private.changeInvoiceLike);
            $(document).on('click', 'body', _private.bodyClick);
            $(document).on('click', config.events.addCall, _private.addCall);
            $(document).on('click', '.editLeadForm', _private.closeEstSelect2);
            $(document).on('click', '#new_lead form', _private.closeEstSelect2);
            $(document).on('show.bs.modal', '#billing_details', _private.showBillingDetails);
            $(document).on('click', '.delete-card', _private.deleteCard);
            $(document).on('click', '.submit', _private.changeTaskStatus);
            $(document).on('click', config.events.changeLocationOption, _private.changeLocation);

            $(document).on('click', config.events.actionsList, function () {
                $(this).parent().toggleClass('open');
            });

            $(document).on('focusin', function(e) {
                if ($(e.target).closest(".mce-window").length) {
                    e.stopImmediatePropagation();
                }
            });

            $(document).on('hide.bs.modal', '#new_lead', function () {
                $("#reff_id").select2('close');
            });
        },

        helpers: {
            getBaseUrl: function () {
                return baseUrl;
            },

            getDateFormat: function (val) {
                val = /^\d+$/.test(val) ? val * 1000 : val;
                const date = new Date(val);

                return date.toLocaleString('en-CA', {day: '2-digit', month: '2-digit', year: 'numeric'});
            },

            getSubTotal: function (total_time, sum_without_tax, total_tax, estimate_tax_name, estimate_tax_value) {
                total_time = Number.parseFloat(total_time);
                const totalTime = total_time
                    ? Number.isInteger(total_time) ? total_time + 'hrs.' : total_time.toFixed(1) + 'hrs.'
                    : 0 + 'hrs.';
                const taxValue = Number.parseFloat(estimate_tax_value).toFixed(2);

                return sum_without_tax
                    ? Common.money(sum_without_tax) + ' ' + totalTime + ' + (' + Common.money(total_tax) + ' ' + estimate_tax_name + ' ' + taxValue + '%)'
                    : '—';
            },

            getTotalWithTax: function (total_with_tax) {
                return Common.money(total_with_tax);
            },

            showEstimateCrews: function (estimate_crews) {
                return estimate_crews && estimate_crews[0] !== undefined
                    ? estimate_crews[0].crew_name || ''
                    : '';
            },

            getTotalDue: function (total_due) {
                return Common.money(total_due);
            }
        },
    };

    pub.init();
    return pub;
}();
