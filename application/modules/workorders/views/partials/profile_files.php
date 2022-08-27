
{{if files!=undefined && files.length}}

<div class="p-right-15 p-left-15"><hr class="m-top-15 m-bottom-10"></div>

<h5 class="text-left text-muted p-left-10">
    <b class="bg-white p-5"><i class="fa fa-file-o text-primary"></i>&nbsp;Files</b>
</h5>

<ul class="p-left-15 p-right-15 m-n text-left workorders-profile-files-list">
    {{for files itemVar="~file"}}
            <li class="list-group-item inline-block workorders-profile-files-list-item">
                <div class="media">
                    <span class="pull-left thumb-sm p-top-10" style="width: 20px"><i class="fa fa-paperclip h4 text-danger"></i></span>
                    <div class="pull-right text-success m-t-xs">
                        <a href="#" class="btn btn-rounded btn-sm btn-icon btn-danger deleteEstimatePhoto" role="button" data-estimate_id="{{:~file.estimate_id}}" data-path="{{:~file.filepath}}">
                            <i class="fa fa-trash-o"></i>
                        </a>
                    </div>
                    <div class="media-body">
                        <div class="text-left">
                            <a target="_blank" class="file-list-name-block" href="/{{:~file.filepath}}">
                                {{:~file.name}}
                            </a>
                        </div>

                        <label class="checkbox m-t-none m-b-none">
                            <input type="checkbox" {{if ~file.checked}}checked="checked"{{/if}} data-file-name="{{:~file.filepath}}">
                            Print in PDF
                        </label>

                    </div>
                </div>
            </li>
    {{/for}}
    </ul>
<div style="position: absolute;top: 11px;right: 15px;">
    <span class="btn btn-primary btn-file btn-reverse btn-rounded m-top-5 bg-white btn-sm" style="padding: 2px 8px;">Choose File&nbsp;<i class="fa fa-cloud-upload"></i>
        <input type="file" name="file" id="fileToUpload" class="btn-upload">
    </span>
    <img id="preloader" src="/assets/img/ajax-loader.gif" style="display:none;">
</div>

<div class="p-right-15 p-left-15"><hr class="m-top-15 m-bottom-10"></div>
{{else}}
    <div class="col-md-12 text-center">
        <div class="col-md-12 dropfile m-top-10 text-muted btn-file">
        <span class="btn-file h4 btn-block p-10" style="font-weight: 300;">Choose File&nbsp;<i class="fa fa-cloud-upload text-primary"></i>
            <input type="file" name="file" id="fileToUpload" class="btn-upload btn-block">
        </span>
        <img id="preloader" src="/assets/img/ajax-loader.gif" style="display:none;">
        </div>
    </div>
{{/if}}
