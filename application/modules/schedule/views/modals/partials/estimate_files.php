<header class="panel-heading">Estimate Files</header>

<table class="table table-striped b-t bg-white m-n">
    <thead>
    <tr>
        <td colspan="2"><strong>File</strong></td>
    </tr>
    </thead>
    <tbody>

    {{if pictures && pictures.length}}
        {{for pictures }}
            <tr>
                <td colspan="2">
                    <a href="#" role="button" data-estimate_id="{{:estimate_id}}" data-path="{{:filepath}}" class="btn btn-xs btn-mini btn-danger pull-left m-r-sm deleteEstimatePhotoClass">
                        <i class="fa fa-trash-o"></i>
                    </a>
                    <a target="_blank" class="pull-left" href="{{:fileurl}}">{{:file}}</a>
                </td>
            </tr>

        {{/for}}
    {{/if}}

    </tbody>

</table>