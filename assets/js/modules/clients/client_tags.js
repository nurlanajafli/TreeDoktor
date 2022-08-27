var ClientTags = function() {
    var config = {
        autoscroll_enabled: false,
        ui: {},

        events: {},
        route: {},
        templates: {},
        select2: [{
            selector: '#client-tags',
            init_selected_data: window.client_tags,
            options: {
                /*placeholder: "Client tags",*/
                /**/
                /*theme: 'default your-container-class',*/
                tags: true,

                tokenSeparators: [","],
                containerCss: 'background-color:green',
                //formatResultCssClass:function (obj) {},
                containerCssClass: function (obj) {
                    return 'client-tags-dropdown-container pull-right';
                },
                /*adaptContainerCssClass:function (clazz) {
                    console.log(clazz, "adaptContainerCssClass");
                    return 'adaptContainerCssClass';
                },*/
                //dropdownCss:'',
                dropdownCssClass: "client-tags-dropdown",
                selectedTagClass: 'label label-success',
                maximumInputLength: 20,
                minimumInputLength: 2,
                selectOnClose: true,

                //Allow manually entered text in drop down.
                createSearchChoice: function (term, data) {
                    if ($(data).filter(function () {
                        return this.text.localeCompare(term) === 0;
                    }).length === 0) {
                        return {id: term, text: term};
                    }
                },

                width: 'calc(100% - 55px)',
                allowClear: true,
                ajax: {
                    'url': '/clients/ajax_search_tag',
                    dataType: 'json',
                    quietMillis: 250,
                    global: false,
                    params: {
                        global: false,
                    },

                    data: function (term, page) {
                        return {
                            q: term, // search term
                        };
                    },
                    results: function (data, page) { // parse the results into the format expected by Select2.
                        // since we are using custom formatting functions we do not need to alter the remote JSON data
                        return {results: data.items};
                    },
                    cache: true
                },
                /*initSelection : function (element, callback) {
                    var data = {id: element.val(), text: element.val()};
                    callback(data);
                }*/
            },

            values: window.client_tags,

            onchange: function (obj) {

                if (obj.added != undefined) {

                    window.client_tags.push(obj.added);
                    if ($('#cteate-client-tag') != undefined) {
                        $('#cteate-client-tag').find('#tag_name').val(obj.added.text);
                        $('#cteate-client-tag').trigger('submit');
                    }

                }
                if (obj.removed != undefined) {
                    console.log("Removed");
                    var deleteCondition = obj.removed;
                    window.client_tags = window.client_tags.filter(function (item) {
                        return (item.id != deleteCondition.id && item.text != deleteCondition.text);
                    });
                    if ($('#delete-client-tag') != undefined) {
                        $('#delete-client-tag').find('#tag_name').val(obj.removed.text);
                        $('#delete-client-tag').trigger('submit');
                    }
                }

                $('input[name="client_tags"]').val(JSON.stringify(window.client_tags));

            },
            createTag: function (params) {
                console.log('createTag');
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }

                return {
                    id: term,
                    text: term,
                    newTag: true // add additional parameters
                }
            },
            insertTag: function (data, tag) {
                // Insert the tag at the end of the results
                console.log("insertTag");
                data.push(tag);
            }
        }]
    };
    var _private = {
        init: function () {
            _private.init_select2();
        },
        init_select2: function () {

            Common.init_select2(config.select2);
        }
    };
    var public = {

        init: function () {
            $(document).ready(function () {
                public.events();
                _private.init();
            });
        },
        helpers: {},

        events: function () {
            if (isClientData) {
                $('#client-tags-form').disableAutoFill({
                    passwordField: '#client-tags',
                    debugMode: false,
                    randomizeInputName: false
                });
            }
        }
    }

    public.init();
    return public;
}();