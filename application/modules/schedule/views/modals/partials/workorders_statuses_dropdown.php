<div class="btn-group filters-btn-group" style="width: 100%">
    <a href="#" class="hidden-sm pull-left btn-xs primary-hover xs-width" data-toggle="class:hide animated fadeInLeft,hide,hide" data-target="#search-workorders-block,.eventDate,#search-workorders-reset-filter" id="woSearchShow">
        <i class="fa fa-fw fa-search arb-btn-color" id="woSearchShowIcon"></i>
    </a>
    <a class="showFilter pull-left btn-xs warning-hover xs-width position-relative" data-toggle="class:hide,hide animated fadeInTop,hide,opacity-0" data-target="#search-workorders-reset,#search-workorders-block,.eventDate,#woSearchShowIcon" title="Show Filters">
        <i class="fa fa-filter text-warning"></i>
        {{if count_filters > 0}}
        <span class="badge badge-xs up bg-danger count">{{:count_filters}}</span>
        {{/if}}
    </a>

    <a class="statuses-dropdown pull-right primary-hover" data-toggle="class:hide animated fadeInRight" data-target="#workorders-statuses-dropdown-body">
        <span class="hidden-xs arb-btn-color" style="text-shadow: 1px 0 #5f833d4f;">{{if selectes_string}}{{:selectes_string}}{{else}}All&nbsp;{{/if}}</span>

        <i class="fa fa-caret-down arb-btn-color" style="position: absolute;right: 3px;top: 8px;"></i>

        <span class="badge badge-sm down m-l-n-sm status-main-counter">{{:selected_total}}</span>
    </a>

</div>
<div class="clear"></div>