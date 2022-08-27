<section class="media">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel panel-default p-n">
                <header class="header bg-gradient bg-white"
                        style="display: flex; align-items: center; justify-content: space-between;">
                    <p class="h4 text-success pull-left" style="margin: 0;"><i class="fa fa-bar-chart-o"
                                                                               style="margin-right: 10px;"
                                                                               aria-hidden="true"></i>Own Estimates</p>
                    <div class="panel-heading"
                         style="display: flex; align-items: center; justify-content: flex-end; padding: 0;">Planned
                        In <?php echo getDateTimeWithDate($date, 'Y-m', false, true); ?>
                        (Goal: <?php echo money($goal); ?>)
                        <div class="pull-right" style="margin-left: 15px;">
                            <a href="<?php echo base_url('estimates/own/' . date('Y-m', strtotime($date . '-01 - 1 month'))); ?>"
                               class="btn btn-sm btn-info">&lt;&lt; Previous Month</a>
                            <a href="<?php echo base_url('estimates/own'); ?>" class="btn btn-sm btn-default">Current
                                Month</a>
                            <a href="<?php echo base_url('estimates/own/' . date('Y-m', strtotime($date . '-01 + 1 month'))); ?>"
                               class="btn btn-sm btn-info">Next Month &gt;&gt;</a>
                        </div>
                        <div class="clear"></div>
                    </div>
                </header>
            </div>
        </div>
    </div>
    <div class="row">
        <?php $symbols = array(' - ', ' ', '-'); ?>
        <?php //$colors = array('success', 'info', 'warning', 'danger'); ?>
        <div class="col-sm-4">
            <section class="panel panel-default p-n">
                <header class="panel-heading">My Estimates By Quantity</header>
                <div style="color:#fff; padding: 10px 0;" class="text-center">
                    <?php if (!isset($estimates) || !isset($count_my_estimates)) : ?>
                        <div style="display:flex; align-items: center; justify-content: center; color: #000; font-size: 50px; height:100%; min-height: 318px;">
                            NO DATA
                        </div>
                    <?php else : ?>
                        <canvas id="OwnEstQuantity" style="height:100%; min-height: 300px; width:25vw"></canvas>
                        <script>
                            const OwnEstQuantityCtx = document.getElementById('OwnEstQuantity').getContext('2d');
                            const OwnEstQuantity = new Chart(OwnEstQuantityCtx, {
                                type: 'pie',
                                data: data = {
                                    labels: [
                                        <?php if(isset($estimates) || isset($count_my_estimates)) : ?>
                                        <?php foreach($statuses as $key=>$status) : ?>
                                        <?php if(isset($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))]) && !empty($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))])) : ?>
                                        "<?php echo($status->est_status_name);?>",
                                        <?php endif;?>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    ],
                                    datasets: [{
                                        label: 'OWN Estimates',
                                        borderWidth: 0.5,
                                        data: [
                                            <?php if(isset($estimates) || isset($count_my_estimates)) : ?>
                                            <?php foreach($statuses as $key=>$status) : ?>
                                            <?php if(isset($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))]) && !empty($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))])) : ?>
                                            <?php echo round((count($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))]) - 1) / $count_my_estimates * 100, 2) . ', ';?>
                                            <?php endif;?>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        ],
                                        backgroundColor: ["#d9534f", "#5cb85c", "#f0ad4e", "#FF851B", "#7FDBFF", "#B10DC9", "#FFDC00", "#001f3f", "#39CCCC", "#01FF70", "#85144b", "#F012BE", "#3D9970", "#111111", "#AAAAAA"],
                                        hoverOffset: 4
                                    }]
                                },
                                options: {
                                    plugins: {
                                        datalabels: {
                                            display: false
                                        },
                                    },
                                    legend: {
                                        position: "bottom",
                                        labels: {
                                            generateLabels: function (chart, a) {
                                                var data = chart.data;
                                                if (data.labels.length && data.datasets.length) {
                                                    return data.labels.map(function (label, i) {
                                                        var meta = chart.getDatasetMeta(0);
                                                        var ds = data.datasets[0];
                                                        var arc = meta.data[i];
                                                        var custom = arc && arc.custom || {};
                                                        var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                                                        var arcOpts = chart.options.elements.arc;
                                                        var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                                                        var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                                                        var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

                                                        return {
                                                            text: label + ': ' + ds.data[i].toFixed(2) + '%',
                                                            fillStyle: fill,
                                                            strokeStyle: stroke,
                                                            lineWidth: bw,
                                                            hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                                            index: i
                                                        };
                                                    });
                                                }
                                            }
                                        }
                                    },
                                    tooltips: {
                                        callbacks: {
                                            label: function (tooltipItems, data) {
                                                return ' ' + data.labels[tooltipItems.index] + ': ' + data.datasets[0].data[tooltipItems.index].toFixed(2) + '%';
                                            }
                                        }

                                    }
                                }
                            })
                        </script>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <div class="col-sm-4">
            <section class="panel panel-default p-n">
                <header class="panel-heading">My Estimates By $</header>
                <div style="color:#fff; padding: 10px 0;" class="text-center">
                    <?php if (empty($estimates) || empty($count_my_estimates) || empty($total_sum)) : ?>
                        <div style="display:flex; align-items: center; justify-content: center; color: #000; font-size: 50px; height:100%; min-height: 318px;">
                            NO DATA
                        </div>
                    <?php else : ?>
                        <canvas id="OwnNewEst" style="height:100%; min-height: 300px; width:25vw"></canvas>
                        <script>
                            const OwnNewEstCtx = document.getElementById('OwnNewEst').getContext('2d');
                            const myOwnNewEst = new Chart(OwnNewEstCtx, {
                                type: 'pie',
                                data: data = {
                                    labels: [
                                        <?php if(!empty($estimates) && !empty($statuses)) : ?>
                                            <?php foreach($statuses as $key=>$status) : ?>
                                                <?php if(isset($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))]) && !empty($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))])) : ?>
                                                    "<?php echo($status->est_status_name);?>",
                                                <?php endif;?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    ],
                                    datasets: [{
                                        borderWidth: 0.5,
                                        label: 'My Estimates By $',
                                        data: [
                                            <?php if(!empty($estimates) && !empty($statuses)) : ?>
                                                <?php foreach($statuses as $key=>$status) : ?>
                                                    <?php if(isset($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))]) && !empty($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))])) : ?>
                                                        <?php if(!empty($total_sum)): ?>
                                                            <?= round($estimates[mb_strtolower(str_replace($symbols, '_', $status->est_status_name))]['sum'] / $total_sum * 100, 2) . ", ";?>
                                                        <?php endif; ?>
                                                    <?php endif;?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        ],
                                        backgroundColor: ["#d9534f", "#5cb85c", "#f0ad4e", "#FF851B", "#7FDBFF", "#B10DC9", "#FFDC00", "#001f3f", "#39CCCC", "#01FF70", "#85144b", "#F012BE", "#3D9970", "#111111", "#AAAAAA"],
                                        hoverOffset: 4
                                    }]
                                },
                                options: {
                                    plugins: {
                                        datalabels: {
                                            display: false
                                        },
                                    },
                                    legend: {
                                        position: "bottom",
                                        labels: {
                                            generateLabels: function (chart, a) {
                                                var data = chart.data;
                                                if (data.labels.length && data.datasets.length) {
                                                    return data.labels.map(function (label, i) {
                                                        var meta = chart.getDatasetMeta(0);
                                                        var ds = data.datasets[0];
                                                        var arc = meta.data[i];
                                                        var custom = arc && arc.custom || {};
                                                        var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                                                        var arcOpts = chart.options.elements.arc;
                                                        var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                                                        var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                                                        var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

                                                        return {
                                                            text: label + ': ' + ds.data[i].toFixed(2) + '%',
                                                            fillStyle: fill,
                                                            strokeStyle: stroke,
                                                            lineWidth: bw,
                                                            hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                                            index: i
                                                        };
                                                    });
                                                }
                                            }
                                        }
                                    },
                                    tooltips: {
                                        callbacks: {
                                            label: function (tooltipItems, data) {
                                                return ' ' + data.labels[tooltipItems.index] + ': ' + data.datasets[0].data[tooltipItems.index].toFixed(2) + '%';
                                            }
                                        }
                                    }
                                }
                            })
                        </script>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <div class="col-sm-4">
            <section class=" panel panel-default p-n  ">
                <header class="panel-heading">Made</header>
                <div style="color:#fff; padding: 10px 0;" class="text-center">
                    <?php
                    if ($complete_estimator) {
                        $est_h = $complete_estimator * 100 / $complete_company;
                    }
                    if ($other_estimators && $complete_company) {
                        $other_h = $other_estimators * 100 / $complete_company;
                    }
                    ?>
                    <?php if (!isset($est_h) && !isset($other_h)) : ?>
                        <div style="display: flex; justify-content: center; align-items: center; color: #000; font-size: 50px; height: 100%; min-height: 318px;">NO DATA
                        </div>
                    <?php else : ?>
                        <canvas id="OwnEstMade" style="min-height:300px; height: 100%; width:25vw;"></canvas>
                        <script>
                            let seriesData = [<?php echo($other_estimators);?>, <?php echo($complete_estimator);?>];
                            let total = seriesData.reduce((a, v) => a + v);
                            let inPercent = seriesData.map(v => Math.max(v / total * 100, 1));

                            let labelsData = ["Other Estimators", "You"];
                            let backgroundColors = ["#d9534f", "#5cb85c", "#f0ad4e", "#FF851B", "#7FDBFF", "#B10DC9", "#FFDC00", "#001f3f", "#39CCCC", "#01FF70", "#85144b", "#F012BE", "#3D9970", "#111111", "#AAAAAA"];

                            const OwnMadeEstCtx = document.getElementById('OwnEstMade').getContext('2d');
                            const myOwnMadeEst = new Chart(OwnMadeEstCtx, {
                                type: 'pie',
                                data: data = {
                                    datasets: [{
                                        data: seriesData,
                                        backgroundColor: backgroundColors,
                                        borderWidth: 0.5,
                                    }],
                                    labels: labelsData,
                                },
                                options: {
                                    plugins: {
                                        datalabels: {
                                            display: false
                                        }
                                    },
                                    legend: {
                                        position: "bottom",
                                        labels: {
                                            generateLabels: function (chart, a) {
                                                var data = chart.data;
                                                if (data.labels.length && data.datasets.length) {
                                                    return data.labels.map(function (label, i) {
                                                        var meta = chart.getDatasetMeta(0);
                                                        var ds = data.datasets[0];
                                                        var arc = meta.data[i];
                                                        var custom = arc && arc.custom || {};
                                                        var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                                                        var arcOpts = chart.options.elements.arc;
                                                        var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                                                        var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                                                        var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

                                                        return {
                                                            text: label + ': ' + ds.data[i].toFixed(2) + '%',
                                                            fillStyle: fill,
                                                            strokeStyle: stroke,
                                                            lineWidth: bw,
                                                            hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                                            index: i
                                                        };
                                                    });
                                                }
                                            }
                                        },
                                    },
                                    tooltips: {
                                        callbacks: {
                                            label: function (tooltipItems, data) {
                                                return ' ' + data.labels[tooltipItems.index] + ': ' + data.datasets[0].data[tooltipItems.index].toFixed(2) + '%';
                                            }
                                        }

                                    }
                                }
                            })
                        </script>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <section class="panel panel-default p-n" style="height: 100%; width: 100%;">
                <header class="panel-heading">My Declined Estimates By Reasons</header>
                <div style="padding: 10px 10px;" class="text-center">
                    <?php if (empty(array_filter(array_values($declined)))) : ?>
                        <div style="display:flex; justify-content: center; align-items: center; color: #000; font-size: 50px;">
                            NO DATA
                        </div>
                    <?php elseif (isset($declined)) : ?>
                    <?php $declinedTotal = 0; ?>
                        <canvas id="DeclinedEstBy" style="height: 100%; min-height:286px; max-width:870px; width: 100%;"></canvas>
                    <?php if (isset($declined)) : ?>
                        <?php foreach ($statuses as $key => $status) : ?>
                            <?php if (isset($status->mdl_est_reason) && !empty($status->mdl_est_reason)) : ?>
                                <?php foreach ($status->mdl_est_reason as $jkey => $reason) : ?>
                                    <?php if (isset($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))]) && !empty($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))])) : ?>
                                        <?php $declineAll = round(count($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))]) / $count_my_estimates * 100, 2); ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                        <script>
                            const OwnDecEstCtx = document.getElementById('DeclinedEstBy').getContext('2d');
                            const myOwnDecEst = new Chart(OwnDecEstCtx, {
                                type: 'pie',
                                data: data = {
                                    labels: [
                                        <?php if(isset($declined)) : ?>
                                        <?php foreach($statuses as $key=>$status) : ?>
                                        <?php if(isset($status->mdl_est_reason) && !empty($status->mdl_est_reason)) : ?>
                                        <?php foreach($status->mdl_est_reason as $jkey=>$reason) : ?>
                                        <?php if(isset($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))]) && !empty($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))])) : ?>
                                        "<?php echo($reason->reason_name);?>",
                                        <?php endif;?>
                                        <?php endforeach; ?>
                                        <?php endif;?>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    ],
                                    datasets: [{
                                        label: 'My Declined Estimates By $',
                                        borderWidth: 0.5,
                                        data: [
                                            <?php if(isset($declined)) : ?>
                                            <?php foreach($statuses as $key=>$status) : ?>
                                            <?php if(isset($status->mdl_est_reason) && !empty($status->mdl_est_reason)) : ?>
                                            <?php foreach($status->mdl_est_reason as $jkey=>$reason) : ?>
                                            <?php if(isset($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))]) && !empty($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))])) : ?>
                                            <?php echo round(count($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))]) / $count_my_estimates * 100, 2) . ', ';?>
                                            <?php endif;?>
                                            <?php endforeach; ?>
                                            <?php endif;?>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        ],
                                        backgroundColor: ["#d9534f", "#5cb85c", "#f0ad4e", "#FF851B", "#7FDBFF", "#B10DC9", "#FFDC00", "#001f3f", "#39CCCC", "#01FF70", "#85144b", "#F012BE", "#3D9970", "#111111", "#AAAAAA"],
                                    }]
                                },
                                options: {
                                    plugins: {
                                        datalabels: {
                                            display: false,
                                        },
                                    },
                                    legend: {
                                        position: "right",
                                        labels: {
                                            generateLabels: function (chart, a) {
                                                var data = chart.data;
                                                if (data.labels.length && data.datasets.length) {
                                                    return data.labels.map(function (label, i) {
                                                        var meta = chart.getDatasetMeta(0);
                                                        var ds = data.datasets[0];
                                                        var arc = meta.data[i];
                                                        var custom = arc && arc.custom || {};
                                                        var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                                                        var arcOpts = chart.options.elements.arc;
                                                        var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                                                        var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                                                        var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

                                                        return {
                                                            text: label + ': ' + ds.data[i].toFixed(2) + '%',
                                                            fillStyle: fill,
                                                            strokeStyle: stroke,
                                                            lineWidth: bw,
                                                            hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                                            index: i
                                                        };
                                                    });
                                                }
                                            }
                                        }
                                    },
                                    tooltips: {
                                        callbacks: {
                                            label: function (tooltipItems, data) {
                                                return ' ' + data.labels[tooltipItems.index] + ': ' + data.datasets[0].data[tooltipItems.index].toFixed(2) + '%';
                                            }
                                        }
                                    },
                                },
                            })
                        </script>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</section>