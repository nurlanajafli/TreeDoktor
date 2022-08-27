<?php if(isset($rows) && $rows) : ?>
    <?php foreach ($rows as $row) : ?>
        <tr class="">
            <td>
                <?php if($row->client_type == 1) : ?>
                    <?php $icon = 'icon_residential.png'?>
                <?php elseif($row->client_type == 2) : ?>
                    <?php $icon = "icon_corp.png"; ?>
                <?php else : ?>
                    <?php $icon = "icon_municipal.png";?>
                <?php endif; ?>
                <img height="17px" src="<?php echo base_url('assets/vendors/notebook/images') . '/' . $icon; ?>">
                <a href="<?php echo base_url() . $row->client_id ; ?>" target="_blank"><?php echo  $row->client_name; ?></a>
            </td>
            <td class="text-left">
                <a href="<?php echo base_url($row->estimate_no) ?>" title="Estimate <?php echo $row->estimate_no ?>" target="_blank" style="text-decoration: none;">
                    <?php if($row->workorder_id) : ?>
                        <i class="fa fa-circle text-success m-t-xs"></i>
                    <?php else : ?>
                        <i class="fa fa-circle text-warning m-t-xs"></i>
                    <?php endif; ?>
                </a>

                <?php if($row->workorder_no) : ?>
                    <a href="<?php echo base_url($row->workorder_no) ?>" title="Workorder <?php echo $row->workorder_no ?>" target="_blank" style="text-decoration: none;">
                <?php endif; ?>
                    <?php if(!$row->workorder_no) : ?>
                        <i class="fa fa-circle text-danger m-t-xs" title="Workorder not found"></i>
                    <?php elseif($row->invoice_no) : ?>
                        <i class="fa fa-circle text-success m-t-xs"></i>
                    <?php else : ?>
                        <i class="fa fa-circle text-warning m-t-xs"></i>
                    <?php endif; ?>
                <?php if($row->workorder_no) : ?>
                    </a>
                <?php endif; ?>

                <?php if($row->invoice_no) : ?>
                    <a href="<?php echo base_url($row->invoice_no) ?>" title="Invoice <?php echo $row->invoice_no ?>" target="_blank" style="text-decoration: none;">
                <?php endif; ?>
                    <?php if(!$row->invoice_no) : ?>
                        <i class="fa fa-circle text-danger m-t-xs" title="Invoice not found"></i>
                    <?php elseif($row->completed) : ?>
                        <i class="fa fa-circle text-success m-t-xs"></i>
                    <?php else : ?>
                        <i class="fa fa-circle text-warning m-t-xs"></i>
                    <?php endif; ?>
                <?php if($row->invoice_no) : ?>
                    </a>
                <?php endif; ?>
            </td>
            <td>
                <?php echo money($row->sum_services_without_discount); ?>
            </td>
            <!--<td>
                <?php /*echo money($row->sum_without_tax); */?>
            </td>-->
            <td>
                <?php echo getDateTimeWithTimestamp($row->estimate_date_created); ?>
            </td>
            <td><?php echo $row->firstname . ' ' . $row->lastname; ?></td>
        </tr>
    <?php endforeach; ?>
<?php else : ?>
    <tr>
        <td colspan="6" class="text-center text-danger">No records found</td>
    </tr>
<?php endif; ?>
