<div id="workspace" style=" border: none!important;">
    <?php $this->load->view('payroll_date_range_table', $payroll);?>
    <?php if (isAdmin() || $this->router->fetch_class() == 'cron' || is_cli()) : ?>
        <?php $this->load->view('payroll_date_range_biweekly_table', $total);?>
    <?php endif; ?>
</div>
