<?php $this->load->view('includes/header'); ?>

<link href="<?php echo base_url('assets/css/modules/billing/billing.css?v=1.01'); ?>" rel="stylesheet">

<section class="hbox stretch brands">
    <aside class="aside-md bg-light lter b-r billing-menu" id="subNav">
        <?php $this->load->view('billing/billing_menu'); ?>
    </aside>

    <aside class="bg-white">
        <section class="vbox billing-content">
            <section class="scrollable wrapper">
                <?php $this->load->view('billing/' . $view_name); ?>
            </section>
        </section>
    </aside>
</section>

<input id="cards_list_json" type="hidden" value='<?php echo isset($cards) && is_array($cards) ? json_encode($cards) : json_encode([]); ?>'>
<div id="card-block"></div>

<script>
    const isSystemUser = <?php echo isSystemUser() ? 'true' : 'false'; ?>;
</script>

<script src="<?php echo base_url('assets/js/modules/billing/billing.js?v=1.00'); ?>"></script>

<?php $this->load->view('includes/footer'); ?>
