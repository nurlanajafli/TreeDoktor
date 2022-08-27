<?php
    use application\modules\references\models\Reference;
?>
<div class="col-lg-6">
    <section class="panel panel-default">
        <div class="row m-l-none m-r-none bg-light">
            <span class="h5 block p-10"><strong>References:</strong></span>
            <div class="sortable references">
            <?php foreach ($references as $key => $reference): ?>
            <?php $no_active = (!$reference->getAttribute('is_'.$reference['slug'].'_active') && in_array($reference['slug'], array_keys(Reference::HIDE_REFERENCE_ARRAY))); ?>
            <div class="p-5 padder-v b-r b-light m-bottom-5 lter item <?php if($reference->trashed() || $no_active): ?>fixed<?php endif; ?>" style="display: inline-block; border-radius: 4px" data-id="<?php echo $reference['id']; ?>">
                <div class="clear">
                    <a href="#" data-request_type="GET"
                       data-url="<?= base_url('references/get_edit_data/' . $reference['id']); ?>"
                       class="reference-edit-identity m-right-10 <?php if($reference->trashed() || $no_active): ?>text-muted<?php endif; ?>"
                       style="display: inline-block; <?php if($reference->trashed() || $no_active): ?>filter: blur(1px);<?php endif; ?>">
                        <span class="h6 block m-t-xs"><strong><?= $reference['name'] ?></strong></span>
                    </a>
                    <?php if($reference->trashed()): ?>
                        <button style="width: 23px;" class="btn btn-rounded btn-xs btn-icon btn-info reference-restore-identity"
                                data-request_type="POST"
                                data-url="<?= base_url('references/restore/' . $reference['id']); ?>">
                            <i class="fa fa-reply"></i>
                        </button>
                    <?php else: ?>
                        <?php if(in_array($reference['slug'], array_keys(Reference::HIDE_REFERENCE_ARRAY))): ?>
                            <button style="width: 23px;" class="btn btn-rounded btn-xs btn-icon btn-<?= $reference->getAttribute('is_'.$reference['slug'].'_active') ? 'warning' : 'success'?> reference-delete-identity"
                                    data-request_type="POST"
                                    data-url="<?= base_url($reference->getAttribute('is_'.$reference['slug'].'_active') ? 'references/delete/' . $reference['id'] : 'references/restore/' . $reference['id']); ?>">
                                <i class="<?= $reference->getAttribute('is_'.$reference['slug'].'_active') ? 'fa fa-eye-slash' : 'fa fa-eye'?>"></i></button>
                        <?php else: ?>
                            <button style="width: 23px;" class="btn btn-rounded btn-xs btn-icon btn-danger reference-delete-identity"
                                    data-request_type="POST"
                                    data-url="<?= base_url('references/delete/' . $reference['id']); ?>">
                                <i class="fa fa-trash-o"></i></button>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
                <div class="p-5 padder-v m-bottom-5 item fixed" draggable="false" style="display: inline-block; border-radius: 4px">
                    <div class="clear">
                        <a href="#" class="btn btn-xs btn-success triggerModalClass create">Add <i class="fa fa-plus"></i></a>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
<style>

    .ui-sortable-placeholder {
        display: inline-block;
        height: 34px;
        width:10px;
        margin-top: -11px;
    }
    #scrollable {
        overflow-x: scroll;
    }
    .sortable {
        border-width: 6px 0;
        padding: 4px 0;
        width:calc(4 * 200px + 1px);
    }

    .item {
        white-space: nowrap;
        box-sizing: border-box;
    }
</style>
<script>

    jQuery.ui.sortable.prototype._create = function() {
        var o = this.options;
        this.containerCache = {};
        this.element.addClass("ui-sortable");
        //Get the items
        this.refresh();
        //Let's determine if the items are floating, threat inline-block as floating as well
        this.floating = this.items.length ? (/left|right/).test(this.items[0].item.css('float')) || this.items[0].item.css('display') == 'inline-block' : false;
        //Let's determine the parent's offset
        this.offset = this.element.offset();
        //Initialize mouse events for interaction
        this._mouseInit();
    };

    $(function() {
        $('.references').disableSelection().sortable({
            scroll: false,
            items: '.item:not(.fixed)',
            update: function (event, ui) {
                var idsInOrder = $(".references").sortable('toArray', { attribute: 'data-id' });
                Common.request.send('/references/save_positions/', {'list': idsInOrder}, function (response) {
                    if(response.status === 'error'){
                        errorMessage(response.message);
                        return false;
                    }
                    Common.request.get('/references/list', function (response) {
                        $('#references_list_wrapper').html(response.html);
                    });
                }, function () {});
            }
        });
    })
</script>