<!-- .aside -->
<aside class="aside" id="note-list">
    <section class="flex">
        <header class="header clearfix">
            <p class="h3">Twilio soft</p>
        </header>
        <section>
            <nav class="nav-primary hidden-xs">
                <ul class="nav">
                    <li style="min-height: 44px;">
                        <a style="font-size: 10px;min-height: 44px;padding: 12px 15px;" href="<?=base_url('/settings/integrations/twilio/flows')?>">
                            <i class="fa fa-gears icon"><b class="bg-warning"></b></i>
                            <span>Flows List</span>
                        </a>
                    </li>
                    <li style="min-height: 44px;">
                        <a style="font-size: 10px;min-height: 44px;padding: 12px 15px;" href="#">
                            <i class="fa fa-gears icon"><b class="bg-warning"></b></i>
                            <span class="pull-right">
                                <i class="fa fa-angle-down text"></i>
                                <i class="fa fa-angle-up text-active"></i>
                            </span>
                            <span>Active Numbers</span>
                        </a>
                        <ul class="nav lt">
                            <li class="">
                                <a href="<?=base_url('/settings/integrations/twilio/active-numbers')?>">
                                    <i class="fa fa-angle-right"></i>
                                    <span>Active Numbers List</span>
                                </a>
                            </li>
                            <li class="">
                                <a href="<?=base_url('/settings/integrations/twilio/application')?>">
                                    <i class="fa fa-angle-right"></i>
                                    <span>Twiml App List</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li style="min-height: 44px;" class="active">
                        <a style="font-size: 10px;min-height: 44px;padding: 12px 15px;" href="#">
                            <i class="fa fa-gears icon"><b class="bg-warning"></b></i>
                            <span class="pull-right">
                                <i class="fa fa-angle-down text"></i>
                                <i class="fa fa-angle-up text-active"></i>
                            </span>
                            <span>Task Router</span>
                        </a>
                        <ul class="nav lt">
                            <li class="">
                                <a href="<?=base_url('/settings/integrations/twilio/workspace')?>">
                                    <i class="fa fa-angle-right"></i>
                                    <span>Workspace List</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </section>
    </section>
</aside>
<!-- /.aside -->