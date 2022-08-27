var FollowupTags = function() {
    var config = {
        autoscroll_enabled: false,
        ui: {},
        events: {},
        route: {},
        templates: {},
        select2: [{
            selector: '.followup-tags',
            init_selected_data: window.followup_tags,
            options: {
                /*placeholder: "Flollowup tags",*/
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
                        public.isCurrentlyChosenTag = false;
                        return {id: term, text: term};
                    } else {
                        public.isCurrentlyChosenTag = true;
                    }
                },

                width: '90%',
                allowClear: true,
                ajax: {
                    'url': '/administration/ajax_search_tag',
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

            values: window.followup_tags,

            onchange: function (obj) {
                if (obj.added != undefined) {
                    if (public.isCurrentlyChosenTag === false) {
                        alert('You can not add new tag hear');
                        $('.select2-choices').find('.select2-search-choice').last().remove();
                        return false;
                    }
                    window.followup_tags.push(obj.added);
                }
                if (obj.removed != undefined) {
                    console.log("Removed");
                    var deleteCondition = obj.removed;
                    window.followup_tags = window.followup_tags.filter(function (item) {
                        return (item.id != deleteCondition.id && item.text != deleteCondition.text);
                    });
                }

                $('input[name="followup_tags"]').val(JSON.stringify(window.followup_tags));

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
        isCurrentlyChosenTag: true,
        isNumber: function (n) { return /^-?[\d.]+(?:e-?\d+)?$/.test(n); },
        init: function () {
            $(document).ready(function () {
                public.events();
                _private.init();
            });
        },
        helpers: {},
        events: function () {
        }
    }

    public.init();
    return public;
}();