<?php
$total_expenses_summ = $total_time_summ = $totalForEvents = $sum = $plannedTime = 0;

foreach ($estimate_data->mdl_services_orm as $service_data) {
    if($service_data->service->is_bundle){
        if(!empty($service_data->bundle_records))
        foreach ($service_data->bundle_records as $record){
            if($record->service_status != 1)
                $sum += $record->service_price;
            $plannedTime += ($record->service_time + $record->service_travel_time + $record->service_disposal_time) * count($record->crew);
        }
    }else{
        if($service_data->service_status != 1)
            $sum += $service_data->service_price;
        $plannedTime += ($service_data->service_time + $service_data->service_travel_time + $service_data->service_disposal_time) * count($service_data->crew);
    }
}

if($events && !empty($events)) {
    foreach ($events as $event) {
        $total_expenses_summ += isset($event['expense_amount_sum']) ? $event['expense_amount_sum'] : 0;
        $total_time_summ += isset($event['event_man_hours']) ? $event['event_man_hours'] : 0;
        $totalForEvents += isset($event['event_price']) ? $event['event_price'] : 0;
    }
}

$actualCrewsCost = $total_time_summ && $plannedTime ? ($estimate_data->estimate_planned_crews_cost / $plannedTime * $total_time_summ) : 0;
$actualEqCost = $total_time_summ && $plannedTime ? ($estimate_data->estimate_planned_equipments_cost / $plannedTime * $total_time_summ) : 0;
$actualOverheadCost = $total_time_summ && $plannedTime ? ($estimate_data->estimate_planned_overheads_cost / $plannedTime * $total_time_summ) : 0;
$actualCompanyCost = ($actualCrewsCost + $actualEqCost + $actualOverheadCost + $total_expenses_summ);
$actualProfit = $totalForEvents - ($actualCrewsCost + $actualEqCost + $actualOverheadCost + $total_expenses_summ);
$actualProfitPerc = floatval($estimate_data->estimate_planned_profit) ? round($actualProfit * 100 / $estimate_data->estimate_planned_profit, 2) : 0;

?>
<style type="text/css">
    .profitability-table td{ padding: 9px 15px!important; }
</style>
    <section class="panel panel-default p-n profitability-table" style="overflow-x:hidden; margin-bottom: 2px;">
        <header class="panel-heading">Profitability</header>
        <div class="scrollable" style="height: 400px; margin-right: -6px; overflow-x: hidden;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th></th>
                    <th class="text-center">Planned</th>
                    <th class="text-center">Actual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Man Hours</td>
                    <td class="text-center"><?php echo number_format($plannedTime, 2); ?></td>
                    <td class="text-center"><?php echo number_format($total_time_summ, 2); ?></td>
                </tr>
                <tr>
                    <td>Crews Cost</td>
                    <td class="text-center"><?php echo money($estimate_data->estimate_planned_crews_cost); ?></td>
                    <td class="text-center"><?php echo money($actualCrewsCost); ?></td>
                </tr>
                <tr>
                    <td>Equipment Cost</td>
                    <td class="text-center"><?php echo money($estimate_data->estimate_planned_equipments_cost); ?></td>
                    <td class="text-center"><?php echo money($actualEqCost); ?></td>
                </tr>
                <tr>
                    <td>Overhead Cost</td>
                    <td class="text-center"><?php echo money($estimate_data->estimate_planned_overheads_cost); ?></td>
                    <td class="text-center"><?php echo money($actualOverheadCost); ?></td>
                </tr>
                <tr>
                    <td>Extra Expenses</td>
                    <td class="text-center"><?php echo money($estimate_data->estimate_planned_extra_expenses); ?></td>
                    <td class="text-center"><?php echo money($total_expenses_summ); ?></td>
                </tr>
                <tr>
                    <td>Company Cost</td>
                    <td class="text-center"><?php echo money($estimate_data->estimate_planned_company_cost); ?></td>
                    <td class="text-center"><?php echo money($actualCompanyCost); ?></td>
                </tr>
                <tr>
                    <td>Invoiced</td>
                    <td class="text-center"><?php echo money($estimate_data->estimate_planned_total_for_services); ?></td>
                    <td class="text-center"><?php echo money($totalForEvents); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <th>Profit <span class="<?php if($actualProfitPerc > 100) : ?>text-success<?php elseif($actualProfitPerc < 100 && $actualProfitPerc > 0) : ?>text-warning<?php else : ?>text-danger<?php endif; ?>">(<?php echo number_format($actualProfitPerc, 2)?>%)</span></th>
                <th class="text-center"><?php echo money($estimate_data->estimate_planned_profit); ?></th>
                <th class="text-center"><?php echo money($actualProfit); ?></th>
            </tfoot>
        </table>
        </div>
    </section>
