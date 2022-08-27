<?php if (!empty($estimate_tree_inventory_services_data)): ?>
<div class="column_holder" >
    <table width="100%" style="border: 1px solid black">
        <tr>
            <?php if(!empty($work_types) && is_array($work_types)): ?>
                <td width="58%">
                    <table>
                        <tr>
                            <td colspan="2" class="acceptance_1_title"><strong>Explanation of abbreviations WORK TYPE</strong></td>
                        </tr>
                        <?php for($i = 0 ; $i <= count($work_types)+1; $i += 2):?>
                            <tr>
                                <?php if(isset($work_types[$i])): ?><td class="acceptance_1_fineprint"><?= $work_types[$i]['ip_name_short'] ?> - <?= $work_types[$i]['ip_name'] ?></td> <?php endif; ?>
                                <?php if(isset($work_types[$i+1])): ?><td class="acceptance_1_fineprint"><?= $work_types[$i+1]['ip_name_short'] ?> - <?= $work_types[$i+1]['ip_name'] ?></td> <?php endif; ?>
                            </tr>
                        <?php endfor; ?>
                    </table>
                </td>
            <?php endif; ?>
            <?php if(!empty($tree_inventory_priorities) && is_array($tree_inventory_priorities)):  ?>
                <td width="42%" valign="top">
                    <table>
                        <tr>
                            <td class="acceptance_1_title"><strong>Explanation of abbreviations PRIORITY</strong></td>
                        </tr>
                        <?php if(in_array('low', $tree_inventory_priorities)): ?>
                            <tr>
                                <td class="acceptance_1_fineprint">L - Low (Pruning for aesthetic purposes, optional)</td>
                            </tr>
                        <?php endif; if(in_array('medium', $tree_inventory_priorities)): ?>
                            <tr>
                                <td class="acceptance_1_fineprint">M - Mid (General trimming)</td>
                            </tr>
                        <?php endif; if(in_array('high', $tree_inventory_priorities)): ?>
                            <tr>
                                <td class="acceptance_1_fineprint">H - High (Priority pruning)</td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </td>
            <?php endif; ?>
        </tr>
    </table>
</div>
<?php endif; ?>
