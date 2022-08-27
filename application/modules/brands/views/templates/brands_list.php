<script id="brands-list-empty-tmp" type="text/x-jsrender">
    <li class="b-b b-light text-center p-10">
        <a href="#">Brands list is empty</a>
    </li>
</script>

<script id="brands-list-tmp" type="text/x-jsrender">
<li class="list-group-item brand_item relative {{if ~is_active_brand(b_id) }}hover{{/if}}" data-id="{{:b_id}}" {{if deleted_at}}style="text-decoration: line-through;"{{/if}}>
    <a href="#" class="thumb-sm pull-left m-r-sm">
        <img src="{{:main_logo}}" class="img-circle" style="width:36px;height:36px;">
    </a>
    <a href="#" class="pull-left">
        {{if !deleted_at && b_is_default==0}}
        <small class="confirmDelete" data-confirmation-massage="Are you sure to delete brand <strong>`{{:b_name}}`</strong> ?" data-yes-text="Yes" data-submit-form="#delete-{{:b_id}}-brand" style="position:absolute;padding:10px;z-index:3;right: 5px;top: 5px;"><button type="button" class="btn btn-xs btn-danger btn-rounded"><i class="fa fa-trash-o"></i></button></small>
        {{/if}}

        {{if deleted_at}}
        <small class="confirmDelete" data-confirmation-massage="Are you sure to restore brand <strong>`{{:b_name}}`</strong> ?" data-yes-text="Yes" data-submit-form="#restore-{{:b_id}}-brand" style="position:absolute;padding:10px;z-index:3;right: 5px;top: 5px;"><button type="button" class="btn btn-xs btn-success btn-rounded"><i class="fa fa-repeat"></i></button></small>
        {{/if}}

        <strong class="block">{{:b_name}}</strong>
    </a>
    <a class="clear">
        <i class="fa fa-map-marker"></i>
        {{if full_address }}
        <small>{{:full_address}}</small>
        {{else}}
        <small>No address</small>
        {{/if}}
    </a>

</li>
</script>