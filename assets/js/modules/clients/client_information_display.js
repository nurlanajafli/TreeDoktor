var ClientInformationDisplay = function() {
    const config = {
        ui: {},

        events: {
            paperText: '.paperText',
            deletePaper: '.deletePaper',
            initClientMap: '.initClientMap',
            clientDelete: '#confirmDelete',
            inputStars: '#input-stars',
            primaryContact: '.primary-contact',
            deleteContact: '.delete-contact',
        },

        route: {
            addPaper: '/clients/ajax_add_paper',
            deletePaper: '/clients/ajax_delete_paper',
            deleteClient: '/clients/ajax_client_removal',
            checkClientEmail: '/clients/check_client_email',
            updateRating: '/clients/ajax_update_rating',
            changePrimaryContact: '/clients/ajax_change_primary_contact',
            deleteContact: '/clients/ajax_delete_contact',
        },

        templates: {},

        views: {},

        images: {}
    };

    let ctrl = false;

    const _private = {
        init: function() {
            _private.initEditableFunc();
        },

        paperTextKeydown: function(event) {
            switch (event.which) {
                //case 13: return false; // отключаем стандартное поведение
                case 17: ctrl = true; // клавиша Ctrl нажата и удерживается
            }
        },

        paperTextKeyup: function(event) {
            const data = {
                id: $(this).attr('data-client_id'),
                text: $(this).val(),
            }

            if (data.text.trim() === '') {
                return false;
            }

            switch (event.which) {
                case 13:
                    if (ctrl) {
                        Common.request.send(
                            config.route.addPaper,
                            data,
                            _private.processAddPaper,
                            _private.handleError,
                            false
                        );
                    }
                    break;
                case 17: ctrl = false;
            }
        },

        processAddPaper: function (response) {
            if (!response) {
                errorMessage('Add paper error!');

                return false;
            }

            const html = '<div class="client-paper p-5">' +
                '<i class="fa fa-times text-danger pull-right deletePaper" data-paper_id="'+ response[0].cp_id +'"></i>' +
                '<span style="color: #000;">' + response[0].cp_text + ', </span>' +
                '<small class="text-muted">' + response[0].emailid +', ' + response[0].cp_date + '</small>' +
                '<div class="line"></div></div>';
            $('.papers-block').prepend(html).scrollTop(0);
            $('.paperText').val('');
        },

        deletePaper: function () {
            const obj = $(this);
            const data = {
                id: $(obj).attr('data-paper_id')
            };

            Common.request.send(
                config.route.deletePaper,
                data,
                function (response) {
                    if (!response) {
                        errorMessage('Delete paper error!');

                        return false;
                    }

                    obj.parent().remove();
                },
                _private.handleError,
                false
            );
        },

        initClientMap: function () {
            const geocoder = new google.maps.Geocoder();

            setTimeout(function() {
                geocoder.geocode({
                    address: mapAddress
                }, function(results, status) {
                    let myLatlng;
                    if (status === google.maps.GeocoderStatus.OK) {
                        myLatlng = results[0].geometry.location;
                    } else {
                        myLatlng = new google.maps.LatLng(officeLocation);
                    }

                    const clientMap = new google.maps.Map(document.getElementById('map_canvas'), {
                        zoom: 10,
                        center: myLatlng,
                        gestureHandling: 'greedy',
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    });
                    const markerOptions = {
                        map: clientMap,
                        position: myLatlng,
                        icon: "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNSIgaGVpZ2h0PSI1MiIgdmlld0JveD0iMCAwIDM4IDM4Ij48cGF0aCBmaWxsPSIjRkQ3NTY3IiBzdHJva2U9IiMwMDAiIHN0cm9rZS13aWR0aD0iMiIgZD0iTTM0LjMwNSAxNi4yMzRjMCA4LjgzLTE1LjE0OCAxOS4xNTgtMTUuMTQ4IDE5LjE1OFMzLjUwNyAyNS4wNjUgMy41MDcgMTYuMWMwLTguNTA1IDYuODk0LTE0LjMwNCAxNS40LTE0LjMwNCA4LjUwNCAwIDE1LjM5OCA1LjkzMyAxNS4zOTggMTQuNDM4eiIvPjx0ZXh0IHRyYW5zZm9ybT0idHJhbnNsYXRlKDE5IDI1KSIgZmlsbD0iIzAwMCIgc3R5bGU9ImZvbnQtZmFtaWx5OiBBcmlhbCwgc2Fucy1zZXJpZjtmb250LXdlaWdodDpib2xkO3RleHQtYWxpZ246Y2VudGVyOyIgZm9udC1zaXplPSIxMiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+JiM5ODk5OzwvdGV4dD48L3N2Zz4="
                    };
                    const clientMarker = createMarker(markerOptions);
                });
            }, 300);
        },

        clientDelete: function () {
            if (!isAdmin) {
                return false;
            }

            const data = {
                password: $('#yourPassword').val(),
                client_id: $(this).data('client_id'),
            }

            Common.request.send(
                config.route.deleteClient,
                data,
                function (response) {
                    if (response.status === 'error') {
                        alert('Wrong password');
                    } else {
                        location.href = baseUrl + 'clients';
                    }
                },
                _private.handleError,
                false
            );
        },

        initEditableFunc: function () {
            $('.stump_address').editable({
                container: 'body',
                success: function(response, newValue) {
                    response = $.parseJSON(response);
                    const name = response.name;
                    /*console.log(response); return false;*/
                    if (response.id) {
                        console.log(123, response);
                        const addressFields = {
                            stump_address: response.address,
                            stump_city: response.city,
                            stump_state: response.state,
                            stump_zip: response.zip,
                            stump_country: response.country,
                            stump_lat: response.lat??0,
                            stump_lon: response.lon??0,
                            stump_add_info: response.stump_add_info,
                            stump_main_intersection: response.main_intersection,
                        };
                        let address_tpl = `<td valign="top" class="` + name + `_address_block_` + response.id + `">
						<a href="#" data-name="`+ name +`_address" data-value='' data-placement="right" data-type="address" data-pk="` + response.id + `" class="stump_address" title="Client Address" data-url="` + baseUrl + `clients/ajax_change_address">`;
                        address_tpl += response.address + `<br>`
                            + response.city + `&#44;&nbsp;` + response.state + `<br>`
                            + response.country + `&nbsp;` + response.zip;

                        if (response.stump_add_info) {
                            address_tpl += `<br>` + response.stump_add_info + `</a></td>`;
                        } else {
                            address_tpl += `<br>` + response.main_intersection + `</a></td>`;
                        }
                        const div = document.createElement('div');
                        $(div).append(address_tpl);
                        $(div).find('a[data-type="address"]').attr('data-value', JSON.stringify(addressFields));
                        $('.' + name + '_address_block_' + response.id).replaceWith($(div).html());
                        _private.initEditableFunc();
                    }
                    if (response.taxText && response.taxName && response.taxRate && response.taxValue) {
                        $('.select2Tax').text(response.taxText);
                        $('#allTaxes').val(JSON.stringify(response.allTaxes));
                        $('.taxRecommendation').val(response.taxValue);
                        $('.taxRecommendationTitle').text(response.taxValue);

                        if (typeof (checkRecommendationTax) === "function") {
                            checkRecommendationTax();
                        } else {
                            if ($('.taxRecommendation').val() !== $('.taxEstimate').val()) {
                                $('.recommendation').show();
                            }
                        }
                    }
                },
            });

            $('.client_contact').editable({
                container: 'body',
                success: function(response, newValue) {
                    response = $.parseJSON(response);

                    if (typeof response.status !== 'undefined' && response.status === false){
                        console.log(response.message);
                    } else {
                        if (response.save === 'update') {
                            $('.contact-table[data-cc-id="' + response.id + '"]').replaceWith(response.view);
                            _private.initEditableFunc();
                        } else {
                            $('.client-info .contact-table:last').after(response.view);
                            $(this).editable('setValue', '');
                            _private.initEditableFunc();
                        }
                        _private.updatePrimaryContact();
                    }
                },
                display: function(value) {
                    return false;
                },
            });

            $('.stump_address').on('shown', function(e, editable) {
                autocompleteToInput($('[name="stump_address"]'));
            });
            $('.client_contact').on('shown', function(e, editable) {
                $('.cc_phone').inputmask(PHONE_NUMBER_MASK);
                if (!editable.$element.data('pk')) {
                    editable.container.$form.find('input').val('');
                    let num = 1;

                    $.each($('a.client_contact'), function(key, val) {
                        if (($(val).text().indexOf('Contact #') + 1)) {
                            num = parseInt($(val).text().replace('Contact #', '')) + 1;
                        }
                    });
                    editable.container.$form.find('.cc_title').val('Contact #' + num);
                }
                if (!editable.container.$form.find('.cc_client_id').val()) {
                    editable.container.$form.find('.cc_client_id').val(editable.container.$element.data('cc_client_id'));
                }
                if (editable.$element.data('pk') !== ''
                        && !editable.container.$form.parent().parent().find('.popover-title .delete-contact').length) {
                    editable.container.$form.parent().parent().find('.popover-title')
                        .append(`<a class="btn btn-danger btn-xs pull-right m-l-xs delete-contact" data-cc-id="` + editable.$element.data('pk') + `"><i class="fa fa-trash-o"></i></a>`);
                }
            });
        },

        checkEmail: function (email) {
            const data = {
                email: email,
                id: clientId
            };

            Common.request.send(
                config.route.checkClientEmail,
                data,
                function (response) {
                    if (response.status === 'error') {
                        $('a[data-email="' + email + '"]').addClass('text-danger').removeClass('text-success').removeClass('text-warning');
                        errorMessage('Sorry. Client email is ' + response.error);
                    }
                    else if (response.status === 'ok') {
                        $('a[data-email="' + email + '"]').addClass('text-success').removeClass('text-danger').removeClass('text-warning');
                    }
                    else if (response.status === 'invalid') {
                        $('a[data-email="' + email + '"]').addClass('text-danger').removeClass('text-success').removeClass('text-warning');
                    }
                    else if (response.status === 'unverifiable') {
                        $('a[data-email="' + email + '"]').addClass('text-warning').removeClass('text-success').removeClass('text-danger');
                        warningMessage('The Email is Unverifiable');
                    }
                },
                function (response) { return false; },
                false
            );

            return false;
        },

        updatePrimaryContact: function () {
            const primaryEmail = $('input.primary-contact:checked').parents('table.contact-table:first').find('a[data-email]').attr('data-email');
            const primaryPhone = $('input.primary-contact:checked').parents('table.contact-table:first').find('a[data-number]').attr('data-number');
            const primaryName = $.trim($('input.primary-contact:checked').parents('table.contact-table:first').find('.contact-name').text());

            $('input.email').val(primaryEmail);
            $('#emails').val(primaryEmail);

            $.each($('.sms_text'), function(key, val) {
                const text = $(val).attr('data-text');
                $(val).val(text.replace('[NAME]', primaryName).replace('[EMAIL]', primaryEmail));
                $(val).parents('.modal-body:first').find('.client_number:first').val(primaryPhone);
            });

            $.each($('textarea[id^="template_text_"],textarea[class^="template_text_"]'), function(key, val) {
                const content = $(val).val();
                const tmpDiv = document.createElement('html');

                $(tmpDiv).html(content);
                $(tmpDiv).find('._var_cc_name').text(primaryName);

                const newContent = $(tmpDiv)[0].outerHTML;
                $(val).val(newContent);

                if (tinyMCE.get($(val).attr('id'))) {
                    tinyMCE.get($(val).attr('id')).setContent(newContent);
                }
            });
        },

        changeRating: function (event, value) {
            if (!clientId) {
                return false;
            }

            const data = {
                client_id: clientId,
                rating: value
            }

            Common.request.send(
                config.route.updateRating,
                data,
                function (response) {
                    if (response.status === 'error') {
                        errorMessage('Sorry. Some trouble\'s with client rating');

                        return false;
                    }

                    clientRating = value;
                    $(config.events.inputStars).rating('update', clientRating);
                    successMessage('Thank you. Client rating was changed');
                },
                _private.handleError,
                false
            );
        },

        changePrimaryContact: function () {
            const obj = $(this);
            const data = {
                cc_id: obj.val(),
                cc_client_id: obj.data('client-id')
            };

            Common.request.send(
                config.route.changePrimaryContact,
                data,
                function (response) {
                    if (response.status === 'ok') {
                        $(obj).parents('.client-info:first').find('a.delete-contact[disabled="disabled"]').removeAttr('disabled');
                        $('#clientUpdateContact-' + data.cc_id).find('a.delete-contact').attr('disabled', 'disabled');
                        _private.updatePrimaryContact();
                    }
                },
                _private.handleError,
                false
            );
        },

        deleteContact: function () {
            const data = {
                cc_id: $(this).data('cc-id')
            };

            if (confirm('Are you sure you want to delete the contact?')) {
                Common.request.send(
                    config.route.deleteContact,
                    data,
                    function (response) {
                        if (response.status === 'ok') {
                            $('.contact-table[data-cc-id="' + data.cc_id + '"]').remove();
                        } else {
                            errorMessage(response.msg);
                        }
                    },
                    _private.handleError,
                    false
                );
            }
        },

        handleError: function (response) {
            errorMessage(response.error);

            return false;
        },

    };

    const pub = {
        init: function() {
            if (clientRating) {
                $(config.events.inputStars).rating('update', clientRating);
                $(config.events.inputStars).rating('refresh', { showClear: false, showCaption: false });
            }

            $(document).ready(function() {
                pub.events();
                _private.init();
            });
        },

        events: function() {
            $(document).on('keydown', config.events.paperText, _private.paperTextKeydown);
            $(document).on('keyup', config.events.paperText, _private.paperTextKeyup);
            $(document).on('click', config.events.deletePaper, _private.deletePaper);
            $(document).on('click', config.events.initClientMap, _private.initClientMap);
            $(document).on('click', config.events.clientDelete, _private.clientDelete);
            $(document).on('click', config.events.deleteContact, _private.deleteContact);
            $(document).on('rating.change', config.events.inputStars, _private.changeRating);
            $(document).on('change', config.events.primaryContact, _private.changePrimaryContact);
        },

        helpers: {},

        checkEmail: function (email) {
            if (!email || email === '') {
                return false;
            }

            _private.checkEmail(email);
        },
    };

    pub.init();
    return pub;
}();
