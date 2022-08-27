<?php $this->load->view('includes/header'); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="<?php echo base_url('estimates'); ?>">Estimates</a></li>
        <li class="active">Estimators Statistic</li>
    </ul>
    <section class="panel panel-default p-n">

        <header class="panel-heading">Filter
            <div class="pull-right" style="margin-top:-14px;">

                <form id="dates" method="post" action="<?php echo base_url('business_intelligence/estimates_statistic'); ?>" class="input-append m-t-md">
                    <input id="date_submit" type="submit" class="btn btn-info date-input-client pull-right" style="width:114px; margin-top:-8px;margin-left: 21px;" value="GO!">
                    <div class="pull-right reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; margin-top: -6px; margin-right: -11px;"
                         data-dates="<?php echo isset($dates) ? htmlspecialchars(json_encode($dates)) : ''; ?>">
                        <i class="fa fa-calendar"></i>&nbsp;
                        <span></span> <i class="fa fa-caret-down"></i>
                        <input type="hidden" name="dates" value="<?php echo isset($from) ? date(getDateFormat(), strtotime($from)) . ' - ' . date(getDateFormat(), strtotime($to)) : ''; ?>">
                    </div>


                    <?php /*<div class="pull-right m-r" style="margin-top: -8px;">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" <?php if($active_flag): ?>checked<?php endif; ?> name="active_flag" id="active_flag"> Current Only
                            </label>
                        </div>
                    </div> */ ?>

                </form>
            </div>
            <div class="clear"></div>
            <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
        </header>
        <script>
            var fromDate = "<?php echo isset($from) ? date(getDateFormat(), strtotime($from)) : '' ?>";
            var toDate = "<?php echo isset($to) ? date(getDateFormat(), strtotime($to)) : '' ?>";

            Common.initDateRangePicker('.reportrange', fromDate, toDate);
        </script>


    </section>

    <section class="col-sm-12 panel ovrflw-x panel-default p-n">
        <header class="panel-heading">Estimators Reports:</header>
        <div class="table-responsive">
            <table class="table table-hover" id="tbl_Estimated">
                <?php if (isset($estimators_files) && $estimators_files != "") : ?>
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <?php foreach($estimate_stats as $key=>$val) : ?>
                            <th class="text-center"><?php echo isset($val['firstname']) ? $val['firstname'] : '-' ; ?>
                                <?php echo isset($val['lastname']) ? $val['lastname'] : ''; ?>
                            </th>
                        <?php endforeach; ?>
                        <th class="text-center">Company AVG</th>
                        <th class="text-center">Company Total</th>

                    </tr>
                    </thead>
                    <tbody id="estimatorFiles">


                    <?php foreach($fields as $key=>$val) : ?>
                        <?php $total = 0;?>
                        <tr>
                            <td width="200px"><?php echo $fields[$key]; ?></td>
                            <?php if(isset($estimators_files[$fields_keys[$key]])) : ?>
                                <?php

                                //echo "<pre>";
                                //var_dump($estimators_files[$fields_keys[$key]]);
                                //die;

                                foreach ($estimators_files[$fields_keys[$key]] as $jkey=>$jval) : ?>
                                    <td class="text-center">
                                        <?php if(is_array($jval)) : ?>
                                            <?php foreach($jval as $zkey=>$zval) : ?>
                                                <?php if($fields_signes[$key] == get_currency() && $zkey == 'total') : ?>
                                                    <?php echo money($zval); ?>
                                                    <?php $total += floatval($zval); ?>
                                                <?php elseif($fields_signes[$key] == '%' && $zkey == 'total') : ?>
                                                    <?php echo $zval . '%'; ?>
                                                    <?php $total += floatval($zval); ?>
                                                <?php else : ?>
                                                    <br><small><strong>(<?php echo isset($estimators_files[$fields_keys[$key] . '_company']['count']) ? $estimators_files[$fields_keys[$key] . '_company']['count'] . '/' : ''; ?><?php echo $zval; ?>)</strong></small>
                                                <?php endif; ?>

                                            <?php endforeach;?>
                                        <?php else : ?>
                                            <?php if($fields_signes[$key] == get_currency()) : ?>
                                                <?php echo money($jval); ?>
                                            <?php elseif($fields_signes[$key] == '%') : ?>
                                                <?php echo $jval . '%'; ?>
                                            <?php else : ?>
                                                <?php echo $jval; ?>
                                            <?php endif; ?>
                                            <?php $total += floatval($jval); ?>
                                        <?php endif; ?>
                                    </td>

                                <?php endforeach; ?>
                                <td class="text-center">
                                <?php if(isset($estimate_stats) && !empty($estimate_stats) && count($estimate_stats)) :  ?>
                                    <?php if($fields_signes[$key] == get_currency()) : ?>
                                        <?php echo money(round($total /(count($estimate_stats) ? count($estimate_stats) : 1) , 2), 2 );//countOk ?>
                                    <?php elseif($fields_signes[$key] == '%') : ?>
                                        <?php echo round($total/(count($estimate_stats) ? count($estimate_stats) : 1)) . '%';//countOk ?>
                                    <?php else : ?>
                                        <?php echo round($total/(count($estimate_stats) ? count($estimate_stats) : 1));//countOk ?>
                                    <?php endif; ?>
                                    <?php if($fields_keys[$key] == 'invoiced_sum' && $estimators_files['invoiced_sum_company']['count']) : ?>
                                        <br><small><strong>(<?php echo round($estimators_files['invoiced_sum_company']['count'] / (count($estimate_stats) ? count($estimate_stats) : 1)); ?>)</strong></small>
                                    <?php elseif($fields_keys[$key] == 'paid_sum' && $estimators_files['paid_sum_company']['count']) : ?>
                                        <br><small><strong>(<?php echo round($estimators_files['paid_sum_company']['count'] / (count($estimate_stats) ? count($estimate_stats) : 1)); ?>)</strong></small>
                                    <?php endif; ?>
                                <?php else : ?>
                                    0
                                <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <?php if($fields_keys[$key] == 'confirmed_sum') : ?>
                                <td class="text-center"><?php echo money($estimators_files['confirmed_sum_company']); ?></td>

                            <?php elseif($fields_keys[$key] == 'confirmed_count') : ?>
                                <td class="text-center"><?php echo $estimators_files['confirmed_count_company']; ?></td>

                            <?php elseif($fields_keys[$key] == 'total_count') : ?>
                                <td class="text-center"><?php echo $estimators_files['total_count_company']; ?></td>

                            <?php elseif($fields_keys[$key] == 'total_sum') : ?>
                                <td class="text-center"><?php echo money($estimators_files['total_sum_company']); ?></td>
                            <?php elseif($fields_keys[$key] == 'conf_new_client') : ?>
                                <td class="text-center"><?php echo $estimators_files['conf_new_client_company']['total']; ?>% <br><small><strong>(<?php echo $estimators_files['conf_new_client_company']['all']; ?> / <?php echo $estimators_files['conf_new_client_company']['count']; ?>)</strong></small></td>
                            <?php elseif($fields_keys[$key] == 'conf_old_client') : ?>
                                <td class="text-center"><?php echo $estimators_files['conf_old_client_company']['total']; ?>% <br><small><strong>(<?php echo $estimators_files['conf_old_client_company']['all']; ?> / <?php echo $estimators_files['conf_old_client_company']['count']; ?>)</strong></small></td>
                            <?php elseif($fields_keys[$key] == 'invoiced_sum') : ?>
                                <td class="text-center"><?php echo money($estimators_files['invoiced_sum_company']['total']); ?><br><small><strong>(<?php echo $estimators_files['invoiced_sum_company']['count'];?>)</strong></small></td>
                            <?php elseif($fields_keys[$key] == 'paid_sum') : ?>
                                <td class="text-center"><?php echo money($estimators_files['paid_sum_company']['total']); ?> <br><small><strong>(<?php echo $estimators_files['paid_sum_company']['count'];?>)</strong></small></td>
                            <?php else : ?>
                                <td class="text-center">-</td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                <?php else : ?>
                    <tr>
                        <td>
                            <p style="color:#FF0000;"> No record found</p>
                        </td>
                    </tr>
                <?php endif;  ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="row">
        <section class="col-sm-12">
            <section class="panel panel-default p-n">
                <?php $this->load->view('partials/employees_report_view'); ?>
            </section>
        </section>

        <section class="col-sm-12">
            <section class="panel panel-default p-n">
                <?php $this->load->view('partials/estimators_report_view'); ?>
            </section>
        </section>
    </div>

    <script>

        $('#count_month').on('change', function(){
            var count = parseInt($(this).val());
            var date = new Date();
            var yyyy = date.getFullYear().toString();
            var currYear = date.getFullYear();
            var mm = (date.getMonth() + 1).toString();
            var mmFrom = (date.getMonth() + 1 - count).toString();
            var dd = date.getDate().toString();
            var mmChars = mm.split('');

            if (mmChars[1] && mm > 12) {
                while (mmChars[1] && mm > 12) {
                    mm -= 12;
                    mm = mm.toString();
                    delete mmChars;
                    var mmChars = mm.split('');
                    yyyy = (parseInt(yyyy) + 1).toString();
                }
            }
            if(mmFrom.length == 1)
                mmFrom = '0' + mmFrom;
            if(count)
            {


                var ModMonth = date.getMonth() + 1 - count;
                if (ModMonth < 0)
                {
                    ModMonth = 12 + ModMonth;
                    currYear = yyyy - 1;
                }
                ModMonth = ModMonth.toString();
                if(ModMonth.length == 1)
                    ModMonth = '0' + ModMonth;
                var ddChars = dd.split('');
                var to = yyyy + '-' + (mmChars[1] ? mm : "0" + mmChars[0]) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]);
                var from = currYear + '-' + (ModMonth) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]);


                //console.log(from, to, count); return false;

                $('#dates [name="to"]').val(to);
                $('#dates [name="from"]').val(from);
                $('#count_month').val(count);
                $('#dates').submit();
            }
            else
            {
                var ddChars = dd.split('');
                var frMm = date.getMonth();
                frMm = frMm.toString();
                if(frMm.length == 1)
                    frMm = '0' + frMm;
                console.log();
                $('#dates [name="to"]').val(yyyy + '-' + (mmChars[1] ? mm : "0" + mmChars[0]) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]));
                $('#dates [name="from"]').val(yyyy + '-' + frMm + '-' + (ddChars[1] ? dd : "0" + ddChars[0]));
                $('#count_month').val(0);
                $('#dates').submit();
            }
            return false;
        });
    </script>
</section>
<?php $this->load->view('includes/footer'); ?>
