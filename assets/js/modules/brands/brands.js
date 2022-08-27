var BaseImageFormat = Quill.import('formats/image');
var Delta = Quill.import('delta');
const ImageFormatAttributesList = [
    'alt',
    'height',
    'width',
    'style',
    'class'
];

class ImageFormat extends BaseImageFormat {
    /*
    static create(value, i) {
        console.log(i);
        let node = super.create();
        console.log(value);
        //node.setAttribute('alt', value.alt);
        node.setAttribute('src', value);
        node.setAttribute('width', value.width);
        //node.setAttribute('height', value.height);
        node.setAttribute('style', value.style);
        if(node.parentNode && node.parentNode.className=='terms-row')
            node.parentNode = node.parentNode.parentNode;

        node.setAttribute('class', "img-fluid");
        return node;
    }*/

    static formats(domNode) {
        return ImageFormatAttributesList.reduce(function(formats, attribute) {
            if (domNode.hasAttribute(attribute)) {
                formats[attribute] = domNode.getAttribute(attribute);
            }
            return formats;
        }, {});
    }
    format(name, value) {
        if (ImageFormatAttributesList.indexOf(name) > -1) {
            if (value) {
                this.domNode.setAttribute(name, value);
            } else {
                this.domNode.removeAttribute(name);
            }
        } else {
            super.format(name, value);
        }
    }
    /*
    static create(value) {
        //let node = super.create();
        //node.setAttribute('alt', value.alt);
        //node.setAttribute('src', value.url);
        //node.setAttribute('class', "img-fluid");
        //console.log("create image");
        return value;
    }*/

    /*static value(node) {
        console.log("value image");
        console.log(node);
        return {
            alt: node.getAttribute('alt'),
            url: node.getAttribute('src')
        };
    }*/
}
ImageFormat.blotName = 'image';
ImageFormat.tagName = 'img';
Quill.register(ImageFormat, true);

var ListItem = Quill.import('formats/list/item');

class PlainListItem extends ListItem {
    formatAt(index, length, name, value) {
        if (name === 'list') {
            // Allow changing or removing list format
            super.formatAt(name, value);
        }
        // Otherwise ignore
    }
}

Quill.register(PlainListItem, true);

var BlockFormat = Quill.import('formats/header');
var Block = Quill.import('blots/block');
var Inline = Quill.import('blots/inline');
var InlineBase = QuillBase.import('blots/inline');
var BlockFormatBase = QuillBase.import('formats/header');
var BlockBase = QuillBase.import('blots/block');
class RowTitle extends BlockFormat{}
class PageTitle extends BlockFormat{}
class RowTitleBase extends BlockFormatBase{}
class PageTitleBase extends BlockFormatBase{}
class GreenTitle extends Inline{
    static blotName = 'greentitle';
    static className = 'green';
    static tagName = 'span';

    static formats(){
        return true;
    }
}
class GreenTitleBase extends InlineBase{
    static blotName = 'greentitle';
    static className = 'green';
    static tagName = 'span';

    static formats(){
        return true;
    }
}

RowTitle.blotName = 'rowtitle';
RowTitle.tagName = 'div';
RowTitle.className = 'title_1';

PageTitle.blotName = 'pagetitle';
PageTitle.tagName = 'div';
PageTitle.className = 'title';

RowTitleBase.blotName = 'rowtitle';
RowTitleBase.tagName = 'div';
RowTitleBase.className = 'title_1';

PageTitleBase.blotName = 'pagetitle';
PageTitleBase.tagName = 'div';
PageTitleBase.className = 'title';
/*
GreenTitle.blotName = 'greentitle';
GreenTitle.tagName = 'span';
GreenTitle.className = 'green';
*/

Quill.register(RowTitle, true);
Quill.register(PageTitle, true);
Quill.register(GreenTitle);
QuillBase.register(RowTitleBase, true);
QuillBase.register(PageTitleBase, true);
QuillBase.register(GreenTitleBase);

var icons = Quill.import('ui/icons');
var iconsBase = QuillBase.import('ui/icons');
icons['rowtitle'] = '<b>Title</b>';
icons['pagetitle'] = '<b>Head</b>';
icons['greentitle'] = '<b>Green</b>';

iconsBase['rowtitle'] = '<b>Title</b>';
iconsBase['pagetitle'] = '<b>Head</b>';
iconsBase['greentitle'] = '<b>Green</b>';


Block.tagName = 'div';
Block.className = 'des_1';
Quill.register(Block, true);

var Brands = function(){
    var config = {

        ui:{
            form_estimate_terms: 'estimate-terms',
            form_payment_terms: 'payment-terms',
            form_pdf_footer: 'pdf-footer',
            form_review_header: 'review-header',
            form_like_message: 'like-message',
            form_dislike_message: 'dislike-message',

            files_input:'.bootstrap-filestyle',
            edit_main_logo:'.edit-main-logo',

            main_logo:'.main-logo',
            //logo_preview:'.logo-preview',
            //preview:'.preview',

            main_logo_container:'.main-logo-container',
            main_logo_input: '.brands .main-logo',
            main_logo_image_id: 'main-logo-image',

            image_row:'.image-row-container',
            active_image_row:'.active.image-row-container',
            //image_file:'.main-logo-container input[type="file"]',

            create:'#create-new-brand',
            brand_item:'.brand_item',
            phone: '.phonemask'
        },
        events:{},
        route:{},
        
        templates:{
            brands_list:'#brands-list-tmp',
            brands_list_empty:'#brands-list-empty-tmp',
            brands_form:'#brands-form-tmp',
            brands_form_empty:'#brands-form-empty-tmp',
            brand_images:'#brand-images-tmp',
            delete_forms:'#delete-forms-tmp',
            restore_forms:'#restore-forms-tmp',

        },

        view:{
            brands_list:'#brands-list',
            brands_form:'#brand-form',
            brand_images:'#brand-images',
            delete_forms:'#delete-forms',
            restore_forms:'#restore-forms',
        }
    }
    
    var _private = {
        
        init:function(){

            _private.brands_list();
            _private.brands_form();
            
            //_private.brand_images();
            $(config.ui.files_input).each(function () {
                $(this).filestyle({
                    buttonText: $(this).attr("data-buttonText"),
                    input: $(this).attr("data-input") === "false" ? false : true,
                    icon: $(this).attr("data-icon") === "false" ? false : true,
                    classButton: $(this).attr("data-classButton"),
                    classInput: $(this).attr("data-classInput"),
                    classIcon: $(this).attr("data-classIcon")
                });
            });
        },

        validation: function(e){

            var validated = false;
            $('[data-required="true"]').each(function () {
                return (validated = $(this).parsley('validate'));
            });

            if ($(this).hasClass('btn-next') && !validated) {
                return false;
            }
        },
        text_obj:{},
        init_text:function(id, quillBase = false){
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                ['blockquote' /*, 'code-block'*/],
                ['pagetitle'], // custom button values
                ['rowtitle'], // custom button values
                ['greentitle'], // custom button values

                ['link', 'image'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
               /* [{ 'script': 'sub'}, { 'script': 'super' }],    */  // superscript/subscript
                /*[{ 'indent': '-1'}, { 'indent': '+1' }],*/          // outdent/indent
                /*[{ 'direction': 'rtl' }], */                        // text direction
                /*[{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown*/
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

                [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                [{ 'font': [] }],
                [{ 'align': [] }],
                /*['clean'],*/
                ['html']
            ];

            var options = {
                debug: 'error',
                modules: {
                    imageResize: {
                        displaySize: true,
                    },
                    toolbar: {
                        //id:id+'-toolbar',
                        container:toolbarOptions,
                    }
                },
                // placeholder: 'Write your Terms...',
                placeholder: $('#'+id+'-text').data('placeholder') ? $('#'+id+'-text').data('placeholder') : 'Write your Terms...',
                readOnly: false,
                theme: 'snow',
                config: {
                    autoParagraph: true
                }
            };


            if(quillBase) {
                QuillBase.prototype.getHtml = function() {
                    return this.container.querySelector('.ql-editor').innerHTML;
                };
                delete options.modules.imageResize;
                _private.text_obj[id] = new QuillBase('#' + id, options);
            }
            else {
                Quill.prototype.getHtml = function() {
                    return this.container.querySelector('.ql-editor').innerHTML;
                };
                _private.text_obj[id] = new Quill('#' + id, options);
            }
            _private.text_obj[id].pasteHTML(document.getElementById(id+'-text').value);

            var quillEd_txtArea_1 = document.createElement('textarea');
            var attrQuillTxtArea = document.createAttribute('quill__html');
            quillEd_txtArea_1.setAttributeNode(attrQuillTxtArea);
            var quillCustomDiv = _private.text_obj[id].addContainer('ql-custom');
            quillCustomDiv.appendChild(quillEd_txtArea_1);

            var quillsHtmlBtns = document.querySelectorAll('.ql-html');

            for (var i = 0; i < quillsHtmlBtns.length; i++){
                quillsHtmlBtns[i].addEventListener('click', function(evt) {
                    var wasActiveTxtArea_1 = (quillEd_txtArea_1.getAttribute('quill__html').indexOf('-active-') > -1);
                    if (wasActiveTxtArea_1) {
                        //html editor to quill
                        _private.text_obj[id].pasteHTML(quillEd_txtArea_1.value);
                        evt.target.classList.remove('ql-active');
                    } else {
                        //quill to html editor
                        quillEd_txtArea_1.value = _private.text_obj[id].getHtml();
                        evt.target.classList.add('ql-active');
                    }
                    quillEd_txtArea_1.setAttribute('quill__html', wasActiveTxtArea_1 ? '' : '-active-');
                });
            }

            _private.text_obj[id].getModule('toolbar').container.id = id+'-toolbar';
            _private.text_obj[id].on('editor-change', function(delta, oldDelta, source) {


                $('#'+id+' .ql-editor').find('img').each(function(){

                    if($(this).parent().hasClass('ql-editor'))
                    {
                       return false;
                    }

                    img_clone = this.cloneNode();
                    img_html = img_clone.outerHTML;
                    if(img_html.length != $(this).parent().html().length)
                    {
                        var parent_need = $(this).parent();
                        detach_img = $(this).detach();
                        detach_img.insertAfter(parent_need);
                    }
                    else{
                        if(!$(this).attr('style'))
                            return;

                        var im_width = parseInt($(this).width());
                        var float = $(this).css('float');
                        if(parseInt($(this).parent().css('width'))!=im_width)
                        {
                            $(this).parent().attr('width', im_width);
                            //$(this).parent().css('width', im_width);
                        }
                        if(float && float!='none'){
                            $(this).parent().removeClass('float-right');
                            $(this).parent().removeClass('float-left');
                            $(this).parent().addClass('float-'+float);
                        }

                        if(float=='none'){
                            $(this).parent().removeClass('floar-left');
                            $(this).parent().removeClass('floar-right');
                        }

                    }
                });

            });
            _private.text_obj[id].on('text-change', function(delta, oldDelta, source) {
                document.getElementById(id+'-text').value = _private.text_obj[id].getHtml();
            });

        },

        resize_form:function(){
            
            $('#estimate_terms_tab .slim-scroll, #payment_terms_tab .slim-scroll').slimScroll({destroy: true});
            $('.main-logo-container .slim-scroll').slimScroll({destroy: true});
            $('.brands-list-container .slim-scroll').slimScroll({destroy: true});

            $('.height-100').height(($(window).height()-100));
            
            $('#estimate_terms_tab .slim-scroll, #payment_terms_tab .slim-scroll').slimScroll({height:$(window).height()-130});
            $('.main-logo-container .slim-scroll').slimScroll({height:$(window).height()-130});
            $('.brands-list-container .slim-scroll').slimScroll({height:$(window).height()-160});
        },

        brands_list:function(response){
            var brands = window.brands;
            
            if(response!=undefined && response.brands!=undefined)
                brands = response.brands;

            var renderView = {
                template_id:config.templates.brands_list, 
                empty_template_id:config.templates.brands_list_empty,
                view_container_id:config.view.brands_list, 
                data:brands,
                helpers: Brands.helpers
            };
            Common.renderView(renderView);

            var renderForms = {
                template_id:config.templates.delete_forms, 
                view_container_id:config.view.delete_forms, 
                data:brands,
                helpers: Brands.helpers
            };
            Common.renderView(renderForms);

            var restoreForms = {
                template_id:config.templates.restore_forms, 
                view_container_id:config.view.restore_forms, 
                data:brands,
                helpers: Brands.helpers
            };
            Common.renderView(restoreForms);
            
        },

        brands_form: function(response){
            let form = window.form;

            if(response!=undefined && response.form!=undefined)
                form = response.form;

            if(form===null || form.length==0)
               form = {};


            var renderView = {
                template_id:config.templates.brands_form, 
                view_container_id:config.view.brands_form,
                data:[form],
                helpers: Brands.helpers
            };
            

            Common.renderView(renderView);

            _private.brand_images();
            Common.init_autocompleate();

            $(config.ui.phone).inputmask({"mask":PHONE_NUMBER_MASK, removeMaskOnSubmit: true });

            setTimeout(function () {
                _private.init_text(config.ui.form_estimate_terms);
                _private.init_text(config.ui.form_payment_terms);
                _private.init_text(config.ui.form_pdf_footer);
                _private.init_text(config.ui.form_review_header, true);
                _private.init_text(config.ui.form_dislike_message, true);
                _private.init_text(config.ui.form_like_message, true);

            }, 500);

            _private.resize_form();
        },

        brand_images:function(response){
            var images_config = window.brand_images_config;
            var brand_images = window.images;

            if(response!=undefined && response.form.images!=undefined)
                brand_images = response.form.images;


            var search_images = {};
            var result_images = [];
            $.each(brand_images, function (key, value){
                search_images[value.bi_key] = value;
            });
            $.each(images_config, function(key, value){
                if(search_images[value.logo_file] != undefined){
                    result_images.push({'default':value, 'image':search_images[value.logo_file], 'form':window.form});
                }
                else
                {
                    result_images.push({'default':value, 'image':false, 'form':window.form});
                }
            });

            var renderView = {
                template_id:config.templates.brand_images, 
                view_container_id:config.view.brand_images, 
                data:result_images
            };

            Common.renderView(renderView);

            _private.resize_form();
        },

        create:function(){
            history.pushState({}, null, '/brands/');
            document.location.reload();
            /*
            $(config.ui.brand_item).removeClass('hover');
            window.form = {};
            _private.brands_form();
            */
        },

        edit:function(e){
            $(config.ui.brand_item).removeClass('hover');
            $(this).addClass('hover');
            var id = $(this).data('id');
            
            if(e.target.className!='confirmDelete' && $(e.target.parentNode).hasClass('confirmDelete')==false && $(e.target.parentNode).closest('.confirmDelete').length==0){
                history.pushState({}, null, '/brands/'+id);
                document.location.reload();
            }

            /*
            window.active_brand = id;
            var brands = window.brands;
            $.each(brands, function(key, value){
                if(value.b_id==id){
                    window.form = value;
                    window.images = value.images;
                    return _private.brands_form();
                }
            });
            */
        },

        edit_main_logo:function(){
            $(config.ui.main_logo).val(null);
            $(this).closest('form').find(config.ui.main_logo).trigger('click');
        },

        init_cropper:function(width, heading){
            const image = document.getElementById(config.ui.main_logo_image_id);
            //var previews = document.querySelectorAll(config.ui.main_logo_container+' '+config.ui.preview);

            //$(config.ui.preview).find('img').remove();
            
            var image_width = $(config.ui.active_image_row).find('input[data-type="file"]').data('width');
            var image_height = $(config.ui.active_image_row).find('input[data-type="file"]').data('height');

            public.cropper = new Cropper(image, {

                viewMode:0,
                dragMode: 'move',
                background:true,
                checkCrossOrigin:true,
                aspectRatio: image_width / image_height,
                autoCropArea: 1,
                /*
                restore: false,
                guides: false,
                highlight: false,
                */
                cropBoxMovable: false,
                center: true,
                cropBoxResizable: false,
                toggleDragModeOnDblclick: false,
                //preview: '.preview',
                background:true,
                getCroppedCanvas:{fillcolor: "#FFFFFF"},

                ready: function (e) {
                    console.log("ready");
                },

                crop: function (event) {

                },
            });

            
        },

        cropper_on:function(){
            $('#'+config.ui.main_logo_image_id).closest(config.ui.main_logo_container).find('.disabled').removeClass('disabled');
            $('.cropper-init').addClass('disabled');
        },

        cropper_off:function(all){
            //console.log(all);
            $('.cropper-save, .cropper-rotate, .cropper-reset, .cropper-move-left, .cropper-move-right, .cropper-move-up, .cropper-move-down, .cropper-zoom-in, .cropper-zoom-out').addClass('disabled');




            if(all!==true)
                $('.cropper-init').removeClass('disabled');
        },

        cropper_save:function(e){
            var ImWidth = $(config.ui.active_image_row).find('input[data-type="file"]').data('width');
            var ImHeight = $(config.ui.active_image_row).find('input[data-type="file"]').data('height');

            var canvasIm = public.cropper.getCroppedCanvas({fillColor: '#fff0', width:ImWidth, height:ImHeight});
            setTimeout(function(){
                image = canvasIm.toDataURL('image/png');

                public.cropper.destroy();
                $('#'+config.ui.main_logo_image_id).attr('src', image);
                $(config.ui.active_image_row).find('input[data-type="file"]').val(image);
                $(config.ui.active_image_row).find('img').attr('src', image);

                _private.cropper_off();
                //$(config.ui.image_file).val(null).trigger('change');
            }, 500);

           /*public.cropper.getCroppedCanvas().toBlob((blob) => {});*/
        },

        cropper_init:function(e){
            _private.init_cropper();
            _private.cropper_on();
        },

        cropper_rotate:function(e){
            public.cropper.rotate(45);
            public.cropper.resize();
        },

        cropper_reset:function(e){
            public.cropper.reset();
            //$(config.ui.image_file).val(null).trigger('change');
        },

        cropper_zoom_in: function(e){
            public.cropper.zoom(0.1);
        },

        cropper_zoom_out: function(e){
            public.cropper.zoom(-0.1);
        },

        cropper_move_left: function(e){
            public.cropper.move(-5, 0);
        },

        cropper_move_right: function(e){
            public.cropper.move(5, 0);
        },

        cropper_move_up: function(e){
            public.cropper.move(0, -10);
        },

        cropper_move_down: function(e){
            public.cropper.move(0, 10);
        },

        select_logo:function(e){

            $(config.ui.image_row).removeClass('active');
            $(this).addClass('active');

            if(Object.keys(public.cropper).length > 0){
                public.cropper.destroy();
            }

            var image = $(this).find('img').attr('src');
            $('#'+config.ui.main_logo_image_id).attr('src', image);
            //$(config.ui.preview).find('img').remove();
            
            
            if($(e.target).hasClass('edit-main-logo')==false && $(e.target).parent().hasClass('edit-main-logo')==false){
                //_private.init_cropper();
                //if($(this).hasClass('default')){
                //    _private.cropper_on();
                //}
                //else{
                    _private.cropper_off(true);   
                //}
            }

        },

        pdf_preview: function () {
            var editor = $(this).data('editor');
            var pdf_template = $(this).data('template');

            pdf_data = _private.text_obj[editor].getHtml();

            $('#pdf-preview-form').find('input[name="pdf_data"]').val(pdf_data);
            $('#pdf-preview-form').find('input[name="pdf_template"]').val(pdf_template);
            $('#pdf-preview-form').trigger('submit');
        },

        create_link: function () {
            let  popover = $(this).closest('.popover');
            let link = popover.find('.link').val();
            let link_name = popover.find('.linkName').val();
            let link_id = popover.find('.linkId').val();
            if(link_name.trim() === ''){
                popover.find('.linkName').css('border-color', 'red');
                return;
            }
            if(!_private.is_url_valid(link)){
                popover.find('.linkName').css('border-color', '');
                popover.find('.link').css('border-color', 'red');
                return;
            }
            let data = {
                id: link,
                text: link_name
            };
            if(link_id){
                _private.change_select2_item($(".links-select2"), data);
                $(".links-select2").select2('val', data.id);
            } else {
                _private.add_select2_items($(".links-select2"), [data]);
                $(".links-select2").select2({minimumResultsForSearch: -1, width:'20%', containerCss: ['padding-top : 3px;']}).select2('val', data.id);
            }
            _private.set_all_review_links();
            popover.popover('hide');
        },

        set_all_review_links: function(){
            let options = $('.links-select2').find("option");
            let links = [];
            $.each(options, function () {
                let text = $(this).text();
                let id = $(this).val();
                links.push({text:text, id:id});
            });
            console.log(links);
            $('[name="b_review_links"]').val(JSON.stringify(links));
        },

        delete_link: function (){
            let select2 = $(".links-select2");
            select2.find(':selected').remove();
            select2.select2('val', '');
            _private.set_all_review_links();
        },

        close_link_popover: function () {
            $(this).closest('.popover').popover('hide');
        },

        change_select2_item: function(select2, item){
            let selected = select2.find(':selected');
            selected.val(item.id);
            selected.text(item.text);
        },

        add_select2_items:  function (select2, items) {
            select2.select2("destroy");
            for (let k in items) {
                let rand = _private.get_random(1, 100);
                let data = items[k];
                select2.append("<option value='" + data.id + "' data-id='" + rand +"'>" + data.text + "</option>");
            }
        },

        get_random: function (min, max) {
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min)) + min;
        },

        is_url_valid: function (url) {
            let pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
                '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
                '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
                '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
                '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
                '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
            return !!pattern.test(url);
        }

    };
    
    var selected_date;
    var public = {

        init:function(){
            $(document).ready(function(){
                public.events();
                _private.init();
            });
        },
        cropper:{},
        events:function(){
            $(document).on('click', '.create-link', _private.create_link);
            $(document).on('click', '.close-link-popover', _private.close_link_popover);
            $(document).on('click', '.delete-link', _private.delete_link);
            $(document).on('click', '#brand-form button[type="submit"]', _private.validation);

            $(document).on('change', config.ui.main_logo_input, function(){
                if(typeof this.files == 'undefined')
                    return;

                file = this.files[0];
                if(typeof file == 'undefined')
                    return;

                $(config.ui.active_image_row).find('input[data-type="filename"]').val(file.name);
                
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    $('#'+config.ui.main_logo_image_id).attr('src', e.target.result);
                    //$(config.ui.logo_preview).remove();
                    _private.cropper_on();
                    _private.init_cropper();
                }
            });

            $(function () {
                $('.popover-markup>.trigger').popover({
                    html: true,
                    container: 'body',
                    title: function () {
                        return $(this).parent().find('.head').html();
                    },
                    content: function () {
                        return $('.content').html();
                    }
                }).click(function (e) {
                    $(this).popover('toggle');
                    e.stopPropagation();
                })
            });

            $(function () {
                $('.edit').on('shown.bs.popover', function (e) {
                   let data = $('.links-select2').select2('data');
                   if(data) {
                       let selected = $('.links-select2').find(':selected');
                       $('.linkName').val(data.text);
                       $('.link').val(data.id);
                       $('.linkId').val(selected.data('id'));
                   }
                });
            });

            $(document).on('click', '.pdf-preview', _private.pdf_preview);

            /*   ---   start cropper events  ---  */
            $(document).on('click', '.cropper-rotate', _private.cropper_rotate);
            $(document).on('click', '.cropper-reset', _private.cropper_reset);
            $(document).on('click', '.cropper-save', _private.cropper_save);
            $(document).on('click', '.cropper-init', _private.cropper_init);

            $(document).on('click', '.cropper-zoom-in', _private.cropper_zoom_in);
            $(document).on('click', '.cropper-zoom-out', _private.cropper_zoom_out);  //cropper.zoom(-0.1)

            $(document).on('click', '.cropper-move-left', _private.cropper_move_left);
            $(document).on('click', '.cropper-move-right', _private.cropper_move_right);
            $(document).on('click', '.cropper-move-up', _private.cropper_move_up);
            $(document).on('click', '.cropper-move-down', _private.cropper_move_down);
            /*   ---   end cropper events  ---  */


            $(document).on('click', config.ui.edit_main_logo, _private.edit_main_logo);
            $(document).on('click', config.ui.image_row, _private.select_logo);
            
            $(document).on('click', config.ui.create, _private.create);            
            $(document).on('click', config.ui.brand_item, _private.edit);

            $(document).on('click', '.brands-nav', function (e) {
                var nav_href = $(this).attr('href');
                if(nav_href)
                    history.pushState({}, null, nav_href);

                return;
            });


            $('#terms-preview-modal').on('hidden.bs.modal', function (e) {
                var link = $('#terms-preview-modal iframe').data('default');
                $('#terms-preview-modal iframe').attr('src', link);
                $('#pdf-preview-form').find('input').val('');
            })

            $(window).resize(function(){
                _private.resize_form();
            });
        },
        
        set_preloader:function(e){
            /*
            if(e.target.id == config.ui.edit_modal_id){
                $(config.events.edit_modal+' .modal-body').html(Leads.preloader);
            }
            */
        },

        save_callback:function(response){
            
            if(response.errors!=undefined)
                return false;

            window.active_brand = response.active;

            var id = '';
            if(response.active != undefined)
                id = response.active;

            history.pushState({}, null, '/brands/'+id+document.location.hash);
            document.location.reload();
            //document.location.hash
            _private.init();
        },

        pdf_preview_callback:function(response){

            $('#terms-preview-modal iframe').attr('src', response.link);
            $('#terms-preview-modal').modal();
            /*
            if(response.template=='pdf_footer'){
                $('#terms-preview-modal iframe').animate({ scrollTop: $('#terms-preview-modal iframe').height()}, 300);
            }
            */
        },

        helpers: {
            is_active_brand:function(id){ 
                return (window.active_brand==id); 
            }
        }
    }

    public.init();
    return public;
}();
//
// $.fn.addSelect2Items = function (items, config) {
//     let that = this;
//     that.select2("destroy");
//     for (let k in items) {
//         let data = items[k];
//         that.append("<option value='" + data.id + "'>" + data.text + "</option>");
//     }
//     that.select2(config || {});
// };
