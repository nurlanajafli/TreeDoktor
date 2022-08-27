<div class="wrapper b-b header">
    <i class="fa fa-credit-card text-success"></i>&nbsp;&nbsp;<strong>Billing Management</strong>
</div>

<div class="nav-primary">
    <ul class="nav">
        <li class="<?php echo (strpos($this->uri->segment(2), 'overview') !== false || !$this->uri->segment(2)) ? 'active' : 'inactive'; ?>">
            <a href="<?php echo base_url('billing/overview'); ?>">
                <span class="pull-right">
                    <i class="fa fa-angle-right"></i>
                </span>
                <span>Overview</span>
            </a>
        </li>
        <li class="<?php echo strpos($this->uri->segment(2), 'transactions') !== false ? 'active' : 'inactive'; ?>">
            <a href="<?php echo base_url('billing/transactions'); ?>">
                <span class="pull-right">
                    <i class="fa fa-angle-right"></i>
                </span>
                <span>Transactions</span>
            </a>
        </li>
    </ul>
</div>

<div class="billing-menu-add-block hidden-xs">
    <ul class="list-group no-radius list-group-lg no-border p-top-15 m-b the-icons">
        <li class="b-light text-center">
            <a href="<?php echo base_url('billing/sms_subscriptions') ?>" class="btn btn-success btn-rounded btn-sm">
                <i class="fa fa-plus m-t-xs text-xs"></i>&nbsp;&nbsp;New order
            </a>
        </li>
    </ul>
</div>
