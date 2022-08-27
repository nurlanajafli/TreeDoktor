<?php if (!empty($estimate_tree_inventory_services_data) && is_array($estimate_tree_inventory_services_data)): ?>
<div class="table-responsive-md" style="padding-bottom: 30px">
    <table class="table" style="word-wrap: break-word; table-layout:fixed; width: 100%;">
        <thead>
        <tr>
            <th scope="col" class="text-left">ID</th>
            <th scope="col" class="text-left">TREE</th>
            <th scope="col">PRIORITY</th>
            <th scope="col" style="width: 15%">WORK TYPE</th>
            <?php if(!isset($is_wo) || config_item('show_workorder_pdf_amounts') && (!isset($event_id) || !$event_id)): ?>
            <th scope="col" class="text-right">COST</th>
            <th scope="col" class="text-right">STUMP</th>
            <th scope="col" class="text-right">PRICE</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($estimate_tree_inventory_services_data as $tree_inventory_data): ?>
            <?php $service_ids[$tree_inventory_data['id']]['name'] = isset($tree_inventory_data['estimate_service_ti_title']) && !empty($tree_inventory_data['estimate_service_ti_title']) ? $tree_inventory_data['estimate_service_ti_title'] : '#'.$tree_inventory_data['ties_number'];?>
            <tr>
                <td style="font-size: xx-small">
                    <strong>#<?= $tree_inventory_data['ties_number'] ?></strong>
                </td>
                <td style="font-size: xx-small">
                    <strong><?= $tree_inventory_data['ties_type'] ?: '-'?></strong>
                </td>
                <td class="text-center" style="font-size: xx-small">
                    <?= $tree_inventory_data['ties_priority'] ?: '-'?>
                </td>
                <td class="text-center" style="font-size: xx-small">
                    <?= $tree_inventory_data['work_types'] ?? '-' ?>
                </td>
                <?php if(!isset($is_wo) ||config_item('show_workorder_pdf_amounts') && (!isset($event_id) || !$event_id)): ?>
                <td class="text-right" style="font-size: xx-small">
                    <?= nl2br(money($tree_inventory_data['ties_cost'])); ?>
                </td>
                <td class="text-right" style="font-size: xx-small">
                    <?= nl2br(money($tree_inventory_data['ties_stump_cost'] ?? $tree_inventory_data['ties_stump'])); ?>
                </td>
                <td class="text-right" style="font-size: xx-small">
                    <?= nl2br(money($tree_inventory_data['service_price'])); ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php if($tree_inventory_data['service_description']): ?>
                <tr>
                    <td></td>
                    <td colspan="6" style="font-size: xx-small">
                        <?= $tree_inventory_data['service_description'] ?>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <td colspan="7" style="border-bottom: 1px solid #bebebe;"></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
