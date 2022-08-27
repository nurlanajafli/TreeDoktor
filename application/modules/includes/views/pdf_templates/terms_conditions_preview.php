<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo !empty($title) ? $title : ''; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">

    <!-- CSS -->
    <?php /*<link rel="stylesheet"
          href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/estimate_pdf.css"
          type="text/css" media="print">*/ ?>
    <link rel="stylesheet"
          href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/estimate_pdf.css"
          type="text/css" media="print">


    <?php
    if(!isset($brand_id))
        $brand_id = 0;

    $default_img = 'assets/'.$this->config->item('company_dir').'/print/header.png';
    $estimate_logo = get_brand_logo($brand_id, 'estimate_logo_file', $default_img);
    $estimate_watermark = get_brand_logo($brand_id, 'watermark_logo_file', false);
    $estimate_left_side = get_brand_logo($brand_id, 'estimate_left_side_file', 'assets/' . config_item('company_dir') . '/print/container_table_left_margin.png');
    //$estimate_terms = get_estimate_terms($brand_id);
    $pdf_footer = get_pdf_footer($brand_id);

    ?>

    <style type="text/css">
        .float-left{
            float:left;
        }
        .float-right{
            float:right;
        }
        @media print {
            body{
                font-family: Sans-Serif;
                font-size: 11pt;
                background-image: url('<?php echo base_url($estimate_left_side); ?>')!important;
                background-position: 0 -48px;
                background-repeat: no-repeat;
                margin: 15px 0 20px 7px;
            }
            .float-left{
                float:left;
            }
            .float-right{
                float:right;
            }
            .ql-align-center{
                text-align: center;
            }
        }
        @page {
            body {
                font-family: Sans-Serif;
                font-size: 11pt;
                background-image: url('<?php echo base_url($estimate_left_side); ?>')!important;
                background-position: 0 -48px;
                background-repeat: no-repeat;
                margin: 15px 0 20px 7px;
            }
            .terms_block {
                padding: 5px 0 0 55px;
            }
            .terms_block .title {
                margin-left: 0;
            }
            .terms_block .title_1 {
                padding-left: 0;
            }
            .terms_block .des_1 {
                padding-left: 20px;
            }
            .float-left{
                float:left;
            }
            .float-right{
                float:right;
            }
            .ql-align-center{
                text-align: center;
            }
            .ql-align-right{
                 text-align: right;
            }
            .ql-align-left{
                text-align: left;
            }
        }

    </style>

</head>
<body>
<?php if($template!='pdf_footer'): ?>
<?php echo $estimate_terms; ?>
<?php elseif($template=='pdf_footer'): ?>
<div class="address" style="position: absolute; bottom: 10px; right: 0; left: 0; text-align: center;">
    <?php echo $estimate_terms; ?>
</div>
<?php endif; ?>

<?php /*
    <span class="green">ADDRESS: </span>' . $config['office_address'] . ' ' . $config['office_city'] . ', ' . $config['office_state'] . ' ' . $config['office_zip'] . ' <span class="green">OFFICE: </span>' . $config['office_phone_mask'] . ' <span class="green">WEB: </span> ' . $config['company_site_name_upper'];
*/?>
</body>
</html>