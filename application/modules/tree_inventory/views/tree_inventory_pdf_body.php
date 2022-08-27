<style type="text/css">
    @page {
        size: portrait;
    }
</style>

<div class="col-xs-12">
    <div class="row">
        <div class="col-xs-6 p-n">
            <h4 class="text-success text-left">Tree Inventory</h4>
        </div>
        <div class="col-xs-5 pull-right" style="padding-top: 1px">
            <h5 class="text-muted text-right"><strong>Inventory Date:</strong>&nbsp;<?php echo date("d/m/Y"); ?></h5>
        </div>
    </div>
</div>


<div class="col-xs-12">
    <div class="col-xs-12 p-n">
        <div class="row">

            <div class="col-xs-6 p-n">
                <div><strong>Client:</strong> <?php echo $client->client_name; ?></div>
                <div><strong>Address:</strong> <?php echo client_address((array)$client); ?></div>
            </div>

            <div class="col-xs-5 pull-right">
                <div class="text-right"><strong>Phone:</strong> <?php echo numberTo($client->cc_phone); ?></div>
                <div class="text-right"><strong>Email:</strong> <?php echo $client->cc_email; ?></div>
            </div>

        </div>
    </div>
</div>

<div class="col-xs-12"></div>


<div class="col-xs-12 m-top-10 m-bottom-10 p-n">
    <hr class="col-xs-12">
</div>

<?php $labels = ['low' => 'Low', 'medium' => 'Mid', 'high' => 'High']; ?>

<div class="col-xs-12 p-n">
    <table class="table table-bordered p-n">
        <thead>
        <tr>
            <th width="8%" class="text-center">Tree</th>
            <th width="8%">Tree #</th>
            <th width="17%">Tree Type</th>
            <th class="text-center" width="6%">Size</th>
            <th class="text-center" width="8%">Priority</th>
            <th width="10%">Work Type</th>
            <th width="23%">Notes</th>
            <th width="13%" class="text-center">Cost</th>
            <th width="13%" class="text-center">Stump</th>
        </tr>
        </thead>
        <tbody id="tree-list-table">
        <?php $sum = 0;
        $sum_stump_cost = 0; ?>
        <?php if (isset($tree_inventory) && !empty($tree_inventory)): ?>
            <?php $total = 0;
            $total_cost = 0;
            $sum_stump_cost = 0; ?>
            <?php foreach ($tree_inventory as $item): ?>
                <tr>
                    <td class="text-center">
                        <img src="<?php echo $item->ti_file; ?>" alt="<?php echo $item->ti_tree_type; ?>" class="img-circle"
                             width="60px" height="60px">
                    </td>
                    <td class="text-center"><?php echo $item->ti_tree_number; ?></td>
                    <td><?php if (isset($item->tree_type) && isset($item->tree_type->trees_name_eng) && $item->tree_type->trees_name_eng): ?><?php echo ucwords($item->tree_type->trees_name_eng); ?>&nbsp;(<?php echo (isset($item->tree_type) && isset($item->tree_type->trees_name_lat)) ? $item->tree_type->trees_name_lat : ""; ?>)<?php endif; ?></td>
                    <td class="text-center"><?php echo $item->ti_size; ?></td>
                    <td class="text-center"><?php echo element($item->ti_tree_priority, $labels, ''); ?></td>
                    <td><?php echo work_types_string($item->work_types, $work_types); ?></td>
                    <td><?php echo $item->ti_remark; ?></td>
                    <td class="text-center"><?php echo money(round($item->ti_cost)); ?></td>
                    <td class="text-center"><?php echo money(round($item->ti_stump_cost)); ?></td>
                    <?php $sum += $item->ti_cost;
                    $sum_stump_cost += $item->ti_stump_cost; ?>
                </tr>
            <?php endforeach; ?>

        <?php endif; ?>


        <?php $tax = $sum * $taxRate - $sum; ?>
        <?php $tax_stump_cost = $sum_stump_cost * $taxRate - $sum_stump_cost; ?>
        <?php $total = $sum * $taxRate; ?>
        <?php $total_stump_cost = $sum_stump_cost * $taxRate; ?>


        <tr>
            <td colspan="7"><strong>Sum:</strong></td>
            <th class="text-center"><?php echo money($sum); ?></th>
            <th class="text-center"><?php echo money($total_stump_cost); ?></th>
        </tr>
        <tr>
            <td colspan="7"><strong>Tax:</strong></td>
            <th class="text-center"><?php echo money($tax); ?></th>
            <th class="text-center"><?php echo money($tax_stump_cost); ?></th>
        </tr>
        <tr>
            <td colspan="7"><strong>Total:</strong></td>
            <th class="text-center"><?php echo money($total); ?></th>
            <th class="text-center"><?php echo money($total_stump_cost); ?></th>
        </tr>
        </tbody>
    </table>
</div>
<?php /*if(count($tree_inventory)>7 && count($tree_inventory)<11 ): ?>
        <?php for($i=0; $i < (11 - count($tree_inventory)); $i++): ?>
            <br>
        <?php endfor; ?>
    <?php endif;*/ ?>
<style type="text/css">
    @page {
        size: portrait;
    }
</style>

<div class="col-xs-12 p-n">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="row border">
            <div class="col-md-4 col-sm-4 col-xs-4 col-padding" style="padding-left: 5px">Key to Priorities</div>
            <div class="col-md-6 col-sm-6 col-xs-6 col-padding border-left" style="padding-left: 5px">Key to Work Type</div>
        </div>
        <div class="row border-bottom border-left border-right">
            <div class="col-md-4 col-sm-4 col-xs-4 col-padding" style="padding-left: 5px">
                <p><strong>Low</strong> = Pruning for aesthetic purposes, optional</p>
                <p><strong>Mid</strong> = General trimming</p>
                <p><strong>High</strong> = Priority pruning</p>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-6 col-padding border-left" style="padding-left: 5px">
                <?php if (isset($work_types) && !empty($work_types)): ?>
                    <div class="row">
                        <?php foreach ($work_types as $type): ?>
                            <div class="col-md-5 col-sm-5 col-xs-5"><strong><?php echo $type->ip_name_short; ?>
                                    :</strong><?php echo $type->ip_name; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<br><br><br>
<style type="text/css">
    @page {
        size: portrait;
    }
</style>

<div class="col-xs-12 p-n">
    <?php if ($screen_src): ?>
        <img src="<?php echo $screen_src; ?>">
    <?php endif; ?>
</div>
