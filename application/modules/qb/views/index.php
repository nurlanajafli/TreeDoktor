<?php $this->load->view('includes/header'); ?>
<script>
    function toggle() {
        $("#sync").toggleClass("height");
    }

    var url = '<?php echo $authUrl; ?>';

    var OAuthCode = function (url) {

        this.loginPopup = function (parameter) {
            this.loginPopupUri(parameter);
        }
        this.loginPopupUri = function (parameter) {
            // Launch Popup
            var parameters = "location=1,width=800,height=650";
            parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

            var win = window.open(url, 'connectPopup', parameters);
            var pollOAuth = window.setInterval(function () {
                try {

                    if (win.document.URL.indexOf("code") != -1) {
                        window.clearInterval(pollOAuth);
                        win.close();
                        location.reload();
                    }
                } catch (e) {
                    console.log(e)
                }
            }, 100);
        }
    }
    var oauth = new OAuthCode(url);
</script>
<script src="<?php echo base_url('assets/js/modules/qb/qb.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/qb/qb.css'); ?>" type="text/css"/>

<section class="hbox stretch">

    <section id="content">
        <section class="vbox">
            <section class="scrollable wrapper">
                <div class="row">
                    <div class="col-md-6">
                        <section class="panel panel-default p-n">

                            <header class="panel-heading">
                                <p class="h4 text-success pull-left"><i class="fa fa-money"></i> QuickBooks Connection
                                </p>
                                <div class="clearfix"></div>

                            </header>
                            <div class="pos-rlt p-xl">
                                <p>If there is no access token or the access token is invalid, click the <b>Connect to
                                        QuickBooks</b> button
                                    below.</p>
                                <pre id="accessToken"><?php
                                    $displayString = isset($accessTokenJson) ? $accessTokenJson : "No Access Token Generated Yet";
                                    if (isset($displayString['access_token']))
                                        $displayString['access_token'] = '******';
                                    if (isset($displayString['refresh_token']))
                                        $displayString['refresh_token'] = '******';
                                    echo json_encode($displayString, JSON_PRETTY_PRINT); ?></pre>
                                <button type="button" class="btn btn-success" onclick="oauth.loginPopup()">Connect to
                                    QuickBooks
                                </button>
                            </div>
                        </section>
                    </div>
                    <div class="col-md-6">
                        <section class="panel panel-default p-n">

                            <header class="panel-heading">
                                <p class="h4 text-success pull-left"><i class="fa fa-gears"></i> Sample API call</p>
                                <div class="clearfix"></div>

                            </header>
                            <div class="pos-rlt p-xl">
                                <p>If there is no access token or the access token is invalid, click either the <b>Connect
                                        to QuickBooks</b>
                                    button above.</p>
                                <pre id="apiCall">NO DATA</pre>
                                <button type="button" class="btn btn-success"
                                        <?php if (isset($accessTokenJson)) : ?>onclick="apiCall.getCompanyInfo()"
                                        <?php else : ?>disabled="disabled"<?php endif; ?>>Get Company Info
                                </button>
                                <?php if ($this->session->userdata('system_user')) : ?>
                                    <div class="sync" id="sync">
                                        <div class="btn-group dropdown">
                                            <button class="btn btn-warning dropdown-toggle"
                                                    data-toggle="dropdown" aria-expanded="false" onclick="toggle()">
                                                Export Data <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" style="max-height: 500px; overflow: auto;">
                                                <li class="dropdown-item exportItem" onclick="exportDatav2(this)"
                                                    id="all">All Data
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="exportDatav2(this)"
                                                    id="customer">Clients
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="exportDatav2(this)"
                                                    id="item">Items
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="exportDatav2(this)"
                                                    id="invoice">Invoices
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="exportDatav2(this)"
                                                    id="payment">Payments
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="exportDatav2(this)"
                                                    id="paymentRounding">Payments for rounding invoices ($ 0.01)
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="exportDatav2(this)"
                                                    id="interest">Interests
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="btn-group dropdown">
                                            <button class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                                                    onclick="toggle()">Import Data  <span class="caret">
                                            </button>
                                            <ul class="dropdown-menu" style="max-height: 500px; overflow: auto;">
                                                <li class="dropdown-item exportItem" onclick="importDatav2(this)"
                                                    id="all">All Data
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="importDatav2(this)"
                                                    id="customer">Clients
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="importDatav2(this)"
                                                    id="class">Classes
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="importDatav2(this)"
                                                    id="category">Categories
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="importDatav2(this)"
                                                    id="estimate">Estimates
                                                </li>
                                                <li class="dropdown-item exportItem" onclick="importDatav2(this)"
                                                    id="invoice">Invoices
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                <?php endif; ?>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </section>
    </section>
</section>

<?php $this->load->view('includes/footer'); ?>
