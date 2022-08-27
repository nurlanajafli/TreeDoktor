var opts = {
    basePath: '',
    textarea: 'epiceditor-content',
    clientSideStorage: false,
    theme: {
        base: '/assets/vendors/notebook/js/markdown/epiceditor.css',
        preview: '/assets/vendors/notebook/js/markdown/bartik.css',
        editor: '/assets/vendors/notebook/js/markdown/epic-light.css'
    }
}

var editor = new EpicEditor(opts).load();