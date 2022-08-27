<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li class="active">Near Miss / Incidents</li>
</ul>
<div class="row">
    <div class="col-sm-12">
        <section>
            <section class="panel panel-default p-n">

                    <header class="panel-heading">Near Miss / Incidents List</header>

                    <div class="table-responsive">
                        <table class="table table-striped bg-whote">
                            <thead>
                            <tr>
                                <th class="text-center" width="50px">ID</th>
                                <th class="text-center">Type</th>
                                <th>User</th>
                                <th class="text-center">Workorder</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Created At</th>
                                <th class="text-center">File</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if($incidents && !empty($incidents)) : ?>
                            <?php foreach($incidents as $key => $val) : ?>
                                <tr>
                                    <td class="text-center"><?php echo $val->inc_id; ?></td>
                                    <td class="text-center"><?php echo isset($val->inc_payload->type) && $val->inc_payload->type ? $val->inc_payload->type : 'N/A'; ?></td>
                                    <td><?php echo $val->firstname . " " . $val->lastname; ?></td>
                                    <td class="text-center">
                                        <?php if($val->workorder_no) : ?>
                                            <a href="<?php echo base_url($val->workorder_no); ?>" target="_blank">
                                                <?php echo $val->workorder_no; ?>
                                            </a>
                                        <?php else : ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
<!--                                        --><?php //echo isset($val->inc_payload->date) && $val->inc_payload->date && isset($val->inc_payload->time) && $val->inc_payload->time ? $val->inc_payload->date . ' ' . $val->inc_payload->time : 'N/A'; ?>
                                        <?php echo isset($val->inc_payload->date) && $val->inc_payload->date && isset($val->inc_payload->time) && $val->inc_payload->time ? getDateTimeWithDate( $val->inc_payload->date . ' ' . $val->inc_payload->time , 'Y-m-d H:i', true) : 'N/A'; ?>

                                    </td>
<!--                                    <td class="text-center">--><?php //echo $val->inc_created_at; ?><!--</td>-->
                                    <td class="text-center"><?php echo getDateTimeWithDate($val->inc_created_at, 'Y-m-d H:i:s', true); ?></td>
                                    <td class="text-center">
                                        <a class="btn btn-xs btn-default" href="<?php echo base_url('business_intelligence/incident/'.$val->inc_id); ?>" target="_blank">
                                            <i class="fa fa-book"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-danger text-center h4 font-bold">No Records Found</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php if($links) : ?>
                <footer class="panel-footer">
                    <div class="row">
                        <div class="col-sm-5 text-right text-center-xs pull-right">
                            <?php echo $links; ?>
                        </div>
                    </div>
                </footer>
                <?php endif; ?>
            </section>
        </section>
    </div>
</div>
</section>

<?php $this->load->view('includes/footer'); ?>
