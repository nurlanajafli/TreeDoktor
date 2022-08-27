<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font-awesome.min.css'); ?>" type="text/css"/>
<style>
    html,
    *,
    *:before,
    *:after {
        box-sizing: border-box;
    }
    * {
        outline: 0;
        -webkit-user-select: none;
        user-select: none;
        -webkit-tap-highlight-color:  rgba(255, 255, 255, 0);
        font-family: 'Roboto', sans-serif;
    }
    input, textarea {
        -webkit-user-select: initial;
        user-select: initial;
    }
    html {
        font-size: 14px;
        background: transparent;
    }
    body {
        margin: 0;
        padding: 20px;
        overflow-y: auto;
        overflow-x: hidden;
        cursor: default;
        color: #000000;
        line-height: 1.36;
        position: relative;
        word-wrap: break-word;
        word-break: break-word;
    }
    @media screen and (max-width: 280px) {
        body {
            padding: 10px;
        }
    }
    html,body {
        background:transparent;
    }
    .input, .expiry {
        display: inline-flex;
        align-items: center;
        vertical-align: top;
        width: 100%;
        height: 40px;
        padding: 0 12px;
        font-size: 14px;
        background: #ffffff;
        border: 1px solid rgba(164, 176, 190, 0.5);
        transition: border 0.25s;
        -webkit-appearance: none;
        border-radius: 4px;
        font-weight: 400;
        word-wrap: normal;
        word-break: normal;
    }
    .input:focus, .expiry:focus, .input.bambora-checkoutfield-focus {
        border: 1px solid #169148;
    }
    .input:disabled, .expiry:disabled {
        opacity: 0.4;
        background: #F9F9F9;
        color: #A4B0BE;
    }
    .input:focus:disabled, .expiry:focus:disabled {
        opacity: 0.4;
        background: #F9F9F9;
        color: #A4B0BE;
    }
    .input::-webkit-input-placeholder, .expiry::-webkit-input-placeholder {
        color: #A4B0BE;
    }
    #crd_form>*{
        position: relative;
    }
    #crd_form .input, #crd_form .expiry {
        padding-left: 35px;
    }
    .icon {
        position: absolute;
        left: 16px;
        top: 11px;
    }
    h3 {
        font-size: 14px;
        letter-spacing: 0.5px;
        margin-top: 0;
        margin-bottom: 5px;
        font-weight: 500;
    }
    h4 {
        font-size: 13px;
        margin-bottom: 2px;
        font-weight: 500;
        margin-top: 0;
        margin-left: 10px;
    }
    .row {
        display: flex;
        flex-wrap: wrap;
        width: calc(100% + 20px);
        margin-left: -10px;
    }
    .col-2 {
        width: 50%;
        padding: 0 10px 10px;
    }
    .col-1, .card-number-wrapper {
        width: 100%;
        padding: 0 10px 10px;
    }
    .col-3, .name-wrapper, .expiry-container, .cvc-container {
        width: 33.33%;
        padding: 0 10px 10px;
    }
    @media screen and (max-width: 634px) {
        .col-2, .name-wrapper, .expiry-container, .cvc-container, .col-3 {
            width: 100%;
        }
    }
    @media screen and (max-width: 634px) {
        .col-2m {
            width: 50% !important;
        }
    }
    .is-req {
        position: relative;
    }
    .is-req:before {
        content: '*';
        color: #f77070;
        position: absolute;
        left: -8px;
        top: 0px;
        font-size: 13px;
        font-weight: 700;
    }
    .btn {
        position: relative;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 0 15px;
        vertical-align: top;
        min-width: 96px;
        height: 40px;
        font: 500 15px Roboto;
        white-space: nowrap;
        text-transform: uppercase;
        background: transparent;
        border: 1px solid #A4B0BE;
        color: #A4B0BE;
        border-radius: 27px;
        overflow: hidden;
        transition: all 0.3s;
        cursor: pointer;
        text-decoration: none;
    }

    .btn:disabled {
        opacity: .25;
        box-shadow: none;
        color: #A4B0BE;
        background: #F9F9F9;
        border: 1px solid #A4B0BE;
    }
    .modal-btns {
        margin-top: 40px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    .btn+.btn {
        margin-left: 20px;
    }
    @media screen and (max-width: 634px) {
        .modal-btns {
            margin-top: 20px;
            justify-content: center;
        }
        .btn {
            width: calc(50% - 10px);
        }
    }
    .btn.btn-info, .btn.btn-primary {
        background: #169148;
        color: #ffffff;
        border: 1px solid #169148;
    }
    .ta-r {
        text-align: right;
    }
    .mt15 {
        margin-top: 15px;
    }
    .ml15 {
        margin-left: 15px;
    }
    .edit-info {
        display: inline-block;
        color: #169148;
        margin-bottom: 15px;
        font-weight: 500;
        margin-top: 10px;
    }
    .error {
        color: #f77070;
        line-height: 1.4;
        font-size: 12px;
        margin-top: 5px;
    }
    body.darkmode {
        color: #fff;
    }
    body.darkmode input, body.darkmode .input  {
        box-shadow: none;
        background: #242424;
        color: #ffffff;
    }
    body.darkmode .icon {
        fill: #F1F3F7;
    }
    .showinfotext {
        text-decoration: underline;
        color: #169148;
        margin-bottom: 5px;
        margin-top: 5px;
    }
    .showinfo {
        display: none;
    }
    #showInfoCheckbox:checked ~ .showinfo {
        display: block;
    }
    #showInfoCheckbox:checked ~ .hideinfo {
        display: none;
    }
    iframe {
        height: 18px!important;
    }
    .icon-field {
        position: relative;
        padding-left: 35px;
    }
    #cardholder-name.icon-field {
        padding-left: 0;
    }
    #crd_name {
        padding-left: 35px;
    }
    .icon-field > i {
        position: absolute;
        left: 11px;
        top: 11px;
        color: #717171;
        font-size: 16px;
    }
    .icon-field > i.fa-credit-card {
        left: 9px;
    }
    .icon-field > i.fa-calendar-o {
        left: 10px;
    }
</style>
<div id="driver_bambora" class="drivers">
    <div class="row">
        <div class="col-3" id="cardholder-name-bootstrap">
            <div id="cardholder-name" class="form-control icon-field">
                <i class="fa fa-user"></i>
                <input id="crd_name" name="crd_name" class="input" value="" placeholder="Cardholder Name">
            </div>
            <label class="help-block error" for="cardholder-name" id="cardholder-name-error"></label>
        </div>
        <div class="col-1" id="card-number-bootstrap">
            <div id="card-number" class="input icon-field">
                <i class="fa fa-credit-card"></i>
            </div>
            <label class="help-block error" for="card-number" id="card-number-error"></label>
        </div>
        <div class="col-3 col-2m" id="card-cvv-bootstrap">
            <div id="card-cvv" class="input icon-field">
                <i class="fa fa-lock"></i>
            </div>
            <label class="help-block error" for="card-cvv" id="card-cvv-error"></label>
        </div>
        <div class="col-3 col-2m" id="card-expiry-bootstrap">
            <div id="card-expiry" class="input icon-field">
                <i class="fa fa-calendar-o"></i>
            </div>
            <label class="help-block error" for="card-expiry" id="card-expiry-error"></label>
        </div>
    </div>
    <div class="additional-form">
        <?php if (!$profileExist): ?>
        <input type="checkbox" id="showInfoCheckbox" hidden>
        <label class="hideinfo" for="showInfoCheckbox">
            <div class="showinfotext">Show billing information</div>
        </label>
        <label class="showinfo" for="showInfoCheckbox">
            <div class="showinfotext">Hide billing information</div>
        </label>
        <div class="showinfo">
            <div class="row">
                <div class="col-2" id="card-expiry-bootstrap">
                    <h4 class="is-req">First name</h4>
                    <input id="bill_fname" name="bill_fname" class="input" placeholder="First name"
                           value="<?php echo $billFname ?? ""; ?>">
                    <label class="help-block error" for="bill_fname" id="bill_fname-error"></label>
                </div>
                <div class="col-2" id="card-expiry-bootstrap">
                    <h4 class="is-req">Last name</h4>
                    <input id="bill_lname" name="bill_lname" class="input" placeholder="Last name"
                           value="<?php echo $billLname ?? ""; ?>">
                    <label class="help-block error" for="bill_fname" id="bill_fname-error"></label>
                </div>
            </div>
                <div id="bill-addr" class="row">
                    <div class="col-3" id="card-expiry-bootstrap">
                        <h4 class="is-req">Address</h4>
                        <input id="bill_address" name="bill_address" class="input" placeholder="Address"
                               value="<?php echo $billAddress ?? ""; ?>">
                        <label class="help-block" for="bill_address" id="bill_address-error"></label>
                    </div>
                    <div class="col-3" id="card-expiry-bootstrap">
                        <h4 class="is-req">City</h4>
                        <input id="bill_city" name="bill_city" class="input" placeholder="City"
                               value="<?php echo $billCity ?? ""; ?>">
                        <label class="help-block" for="bill_city" id="bill_city-error"></label>
                    </div>
                    <div class="col-3" id="card-expiry-bootstrap">
                        <h4 class="is-req">State</h4>
                        <select name="bill_state" id="bill_state" class="input"
                                data-bill-state="<?php echo $billState ?? ""; ?>">
                            <option id="bill_state_None" value="" disabled="" selected>Select your state/province:
                            </option>
                            <option id="bill_state_Outside" value="--" disabled="" style="display: none;">Outside
                                U.S./Canada
                            </option>
                            <optgroup id="bill_state_CA" label="Canada" disabled="" style="display: none;">
                                <option value="AB" disabled="" style="display: none;">Alberta</option>
                                <option value="BC" disabled="" style="display: none;">British Columbia</option>
                                <option value="MB" disabled="" style="display: none;">Manitoba</option>
                                <option value="NB" disabled="" style="display: none;">New Brunswick</option>
                                <option value="NL" disabled="" style="display: none;">Newfoundland and Labrador</option>
                                <option value="NT" disabled="" style="display: none;">Northwest Territories</option>
                                <option value="NS" disabled="" style="display: none;">Nova Scotia</option>
                                <option value="NU" disabled="" style="display: none;">Nunavut</option>
                                <option value="ON" disabled="" style="display: none;">Ontario</option>
                                <option value="PE" disabled="" style="display: none;">Prince Edward Island</option>
                                <option value="QC" disabled="" style="display: none;">Quebec</option>
                                <option value="SK" disabled="" style="display: none;">Saskatchewan</option>
                                <option value="YT" disabled="" style="display: none;">Yukon</option>
                            </optgroup>
                            <optgroup id="bill_state_US" label="United States" disabled="" style="display: none;">
                                <option value="AL" disabled="" style="display: none;">Alabama</option>
                                <option value="AK" disabled="" style="display: none;">Alaska</option>
                                <option value="AS" disabled="" style="display: none;">American Samoa</option>
                                <option value="AZ" disabled="" style="display: none;">Arizona</option>
                                <option value="AR" disabled="" style="display: none;">Arkansas</option>
                                <option value="AA" disabled="" style="display: none;">Armed Forces Americas</option>
                                <option value="AE" disabled="" style="display: none;">Armed Forces Europe</option>
                                <option value="AP" disabled="" style="display: none;">Armed Forces Pacific</option>
                                <option value="CA" disabled="" style="display: none;">California</option>
                                <option value="CO" disabled="" style="display: none;">Colorado</option>
                                <option value="CT" disabled="" style="display: none;">Connecticut</option>
                                <option value="DE" disabled="" style="display: none;">Delaware</option>
                                <option value="DC" disabled="" style="display: none;">District of Columbia</option>
                                <option value="FL" disabled="" style="display: none;">Florida</option>
                                <option value="GA" disabled="" style="display: none;">Georgia</option>
                                <option value="GU" disabled="" style="display: none;">Guam</option>
                                <option value="HI" disabled="" style="display: none;">Hawaii</option>
                                <option value="ID" disabled="" style="display: none;">Idaho</option>
                                <option value="IL" disabled="" style="display: none;">Illinois</option>
                                <option value="IN" disabled="" style="display: none;">Indiana</option>
                                <option value="IA" disabled="" style="display: none;">Iowa</option>
                                <option value="KS" disabled="" style="display: none;">Kansas</option>
                                <option value="KY" disabled="" style="display: none;">Kentucky</option>
                                <option value="LA" disabled="" style="display: none;">Louisiana</option>
                                <option value="ME" disabled="" style="display: none;">Maine</option>
                                <option value="MD" disabled="" style="display: none;">Maryland</option>
                                <option value="MA" disabled="" style="display: none;">Massachusetts</option>
                                <option value="MI" disabled="" style="display: none;">Michigan</option>
                                <option value="MN" disabled="" style="display: none;">Minnesota</option>
                                <option value="MS" disabled="" style="display: none;">Mississippi</option>
                                <option value="MO" disabled="" style="display: none;">Missouri</option>
                                <option value="MT" disabled="" style="display: none;">Montana</option>
                                <option value="NE" disabled="" style="display: none;">Nebraska</option>
                                <option value="NV" disabled="" style="display: none;">Nevada</option>
                                <option value="NH" disabled="" style="display: none;">New Hampshire</option>
                                <option value="NJ" disabled="" style="display: none;">New Jersey</option>
                                <option value="NM" disabled="" style="display: none;">New Mexico</option>
                                <option value="NY" disabled="" style="display: none;">New York</option>
                                <option value="NC" disabled="" style="display: none;">North Carolina</option>
                                <option value="ND" disabled="" style="display: none;">North Dakota</option>
                                <option value="MP" disabled="" style="display: none;">Northern Marianas</option>
                                <option value="OH" disabled="" style="display: none;">Ohio</option>
                                <option value="OK" disabled="" style="display: none;">Oklahoma</option>
                                <option value="OR" disabled="" style="display: none;">Oregon</option>
                                <option value="PW" disabled="" style="display: none;">Palau</option>
                                <option value="PA" disabled="" style="display: none;">Pennsylvania</option>
                                <option value="PR" disabled="" style="display: none;">Puerto Rico</option>
                                <option value="RI" disabled="" style="display: none;">Rhode Island</option>
                                <option value="SC" disabled="" style="display: none;">South Carolina</option>
                                <option value="SD" disabled="" style="display: none;">South Dakota</option>
                                <option value="TN" disabled="" style="display: none;">Tennessee</option>
                                <option value="TX" disabled="" style="display: none;">Texas</option>
                                <option value="UT" disabled="" style="display: none;">Utah</option>
                                <option value="VT" disabled="" style="display: none;">Vermont</option>
                                <option value="VI" disabled="" style="display: none;">Virgin Islands</option>
                                <option value="VA" disabled="" style="display: none;">Virginia</option>
                                <option value="WA" disabled="" style="display: none;">Washington</option>
                                <option value="WV" disabled="" style="display: none;">West Virginia</option>
                                <option value="WI" disabled="" style="display: none;">Wisconsin</option>
                                <option value="WY" disabled="" style="display: none;">Wyoming</option>
                            </optgroup>
                        </select>
                        <label class="help-block" for="bill_state" id="bill_state-error"></label>
                    </div>
                    <div class="col-3" id="card-expiry-bootstrap">
                        <h4 class="is-req">Country</h4>
                        <select class="input" name="bill_country" id="bill_country"
                                data-bill-country="<?php echo $billCountry ?? ""; ?>">
                            <option value="" selected>-None-</option>
                            <option value="CA">Canada</option>
                            <option value="US">United States</option>
                        </select>
                        <label class="help-block error" for="bill_country" id="bill_country-error"></label>
                    </div>
                    <div class="col-3" id="card-expiry-bootstrap">
                        <h4 class="is-req">ZIP</h4>
                        <input id="bill_zip" name="bill_zip" class="input" placeholder="Zip code/Postal code"
                               value="<?php echo $billZip ?? ""; ?>">
                        <label class="help-block" for="bill_zip" id="bill_zip-error"></label>
                    </div>
                    <div class="col-3" id="card-expiry-bootstrap">
                        <h4 class="is-req">Phone</h4>
                        <input id="bill_phone" name="bill_phone" class="input" placeholder="Phone"
                               value="<?php echo $billPhone ?? ""; ?>">
                        <label class="help-block" for="bill_phone" id="bill_phone-error"></label>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div id="feedback" class="error"></div>
</div>
<script>
    $(document).ready(function () {
        let script = document.createElement('script');
        script.src = "https://libs.na.bambora.com/customcheckout/1/customcheckout.js";
        document.getElementById('driver_bambora').append(script);

        script.onload = function () {
            var customCheckout = customcheckout();

            var isCardNumberComplete = false;
            var isCVVComplete = false;
            var isExpiryComplete = false;
            var isCardholderNameComplete = false;
            var cardNumber = false;
            var cardCvv = false;
            var cardExpiry = false;

            var bamboraCheckoutController = {
                init: function () {
                    console.log('checkout.init()');
                    this.createInputs();
                    this.addListeners();
                    this.selectBillCountry();
                },
                createInputs: function () {
                    console.log('checkout.createInputs()');
                    let color = '#000000';
                    color = variables !== undefined && variables.dark !== undefined && variables.dark ? '#ffffff' : color;
                    var options = {
                        style: {
                            base: {
                                color: color,
                                fontSize: '14px',
                            },
                        }
                    };
                    let brands = JSON.parse('<?php echo json_encode(config_item('payment_brands')); ?>');
                    if(Array.isArray(brands) && brands.length > 0) {
                        options.brands = brands;
                    }
                    // Create and mount the inputs
                    options.placeholder = 'Card number';
                    cardNumber = customCheckout.create('card-number', options);
                    cardNumber.mount('#cc_form #driver_bambora #card-number');
                    options.placeholder = 'CVV';
                    cardCvv = customCheckout.create('cvv', options);
                    cardCvv.mount('#cc_form  #driver_bambora  #card-cvv');
                    options.placeholder = 'MM / YY';
                    cardExpiry = customCheckout.create('expiry', options);
                    cardExpiry.mount('#cc_form #driver_bambora #card-expiry');
                },
                addListeners: function () {
                    var self = this;

                    // listen for submit button
                    if (document.getElementById('cc_form') !== null) {
                        document
                            .getElementById('cc_form')
                            .addEventListener('submit', self.onSubmit.bind(self));
                        document
                            .getElementById('cc_form')
                            .addEventListener('reset', self.onReset.bind(self));
                        document
                            .getElementById('crd_name')
                            .addEventListener('change', self.onChangeCardholder.bind(self));
                    }
                    // $("#card-name").on('change', function(event){
                    //            if(this.value != ""){
                    //                 isCardholderNameComplete = true;
                    //                 self.setPayButton(isCardNumberComplete && isCVVComplete && isExpiryComplete && isCardholderNameComplete);
                    //            } else {
                    //                isCardholderNameComplete = false;
                    //                self.setPayButton(false);
                    //            }
                    //         });
                    customCheckout.on('brand', function (event) {
                        console.log('brand: ' + JSON.stringify(event));

                        var cardLogo = 'none';
                        if (event.brand && event.brand !== 'unknown') {
                            var filePath =
                                'https://cdn.na.bambora.com/downloads/images/cards/' +
                                event.brand +
                                '.svg';
                            cardLogo = 'url(' + filePath + ')';
                        }
                        document.getElementById('card-number').style.backgroundImage = cardLogo;
                        document.getElementById('card-number').style.backgroundPositionX = '100%';
                        document.getElementById('card-number').style.backgroundRepeat = 'no-repeat';
                    });

                    customCheckout.on('blur', function (event) {
                        console.log('blur: ' + JSON.stringify(event));
                    });

                    customCheckout.on('focus', function (event) {
                        console.log('focus: ' + JSON.stringify(event));
                    });

                    customCheckout.on('empty', function (event) {
                        console.log('empty: ' + JSON.stringify(event));

                        if (event.empty) {
                            if (event.field === 'card-number') {
                                isCardNumberComplete = false;
                            } else if (event.field === 'cvv') {
                                isCVVComplete = false;
                            } else if (event.field === 'expiry') {
                                isExpiryComplete = false;
                            }
                            self.setPayButton(false);
                        }
                    });

                    customCheckout.on('complete', function (event) {
                        console.log('complete: ' + JSON.stringify(event));

                        if (event.field === 'card-number') {
                            cardCvv.focus();
                            isCardNumberComplete = true;
                            self.hideErrorForId('card-number');
                        } else if (event.field === 'cvv') {
                            cardExpiry.focus();
                            isCVVComplete = true;
                            self.hideErrorForId('card-cvv');
                        } else if (event.field === 'expiry') {
                            isExpiryComplete = true;
                            self.hideErrorForId('card-expiry');
                        }


                        self.setPayButton(
                            isCardNumberComplete && isCVVComplete && isExpiryComplete && isCardholderNameComplete
                        );
                    });

                    customCheckout.on('error', function (event) {
                        console.log('error: ' + JSON.stringify(event));

                        if (event.field === 'card-number') {
                            isCardNumberComplete = false;
                            self.showErrorForId('card-number', event.message);
                        } else if (event.field === 'cvv') {
                            isCVVComplete = false;
                            self.showErrorForId('card-cvv', event.message);
                        } else if (event.field === 'expiry') {
                            isExpiryComplete = false;
                            self.showErrorForId('card-expiry', event.message);
                        }
                        self.setPayButton(false);
                    });
                    if(document.getElementById('bill_country')) {
                        document
                            .getElementById('bill_country')
                            .addEventListener('change', self.onChangeBillCountry.bind(self));
                    }
                },
                selectBillCountry: function () {
                    let country = document.getElementById('bill_country');
                    if (country === null)
                        return;
                    let str = country.dataset.billCountry.toLowerCase().trim();
                    let options = country.querySelectorAll('option');
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value.toLowerCase().trim() === str || options[i].label.toLowerCase().trim() === str) {
                            options[i].selected = true;
                            this.selectBillState(country, true);
                        }
                    }
                },
                onChangeBillCountry: function (event) {
                    this.selectBillState(event.currentTarget);
                },
                selectBillState: function (element, init = false) {
                    let country = element.querySelector("option:checked");
                    let state = document.getElementById('bill_state');
                    if (state === null)
                        return;
                    let checked = state.querySelectorAll("option:checked");
                    for (let i = 0; i < checked.length; i++) {
                        checked[i].selected = false;
                    }
                    let find = false;
                    let selected = false;
                    let optgroups = state.querySelectorAll("optgroup");
                    for (let i = 0; i < optgroups.length; i++) {
                        if (optgroups[i].id === 'bill_state_' + country.value) {
                            selected = this.changeStateOptGroup(optgroups[i], true, state.dataset.billState.toLowerCase().trim());
                            find = true;
                        } else {
                            this.changeStateOptGroup(optgroups[i], false);
                        }
                    }
                    if (!find) {
                        this.changeStateOptGroup(document.getElementById('bill_state_Outside'), true);
                        document.getElementById('bill_state_Outside').selected = true;
                    } else {
                        this.changeStateOptGroup(document.getElementById('bill_state_Outside'), false);
                        if (!selected) {
                            document.getElementById('bill_state_None').selected = true;
                        }
                    }
                },
                changeStateOptGroup: function (element, enable, state = false) {
                    element.style.display = enable ? "block" : "none";
                    enable ? element.removeAttribute('disabled') : element.setAttribute('disabled', 'disabled');
                    let options = element.querySelectorAll('option');
                    if (options.length === 0)
                        return;
                    let selected = false;
                    for (let i = 0; i < options.length; i++) {
                        options[i].style.display = enable ? "block" : "none";
                        enable ? options[i].removeAttribute('disabled') : options[i].setAttribute('disabled', 'disabled');
                        if (enable && state !== false && (options[i].value.toLowerCase().trim() === state || options[i].label.toLowerCase().trim() === state)) {
                            options[i].selected = true;
                            selected = true;
                        }
                    }
                    return selected;
                },
                onSubmit: function (event) {
                    var self = this;

                    console.log('checkout.onSubmit()');

                    event.preventDefault();
                    self.setPayButton(false);
                    self.toggleProcessingScreen();

                    var callback = function (result) {
                        console.log('token result : ' + JSON.stringify(result));

                        if (result.error) {
                            self.processTokenError(result.error);
                        } else {
                            self.processTokenSuccess(result.token);
                        }
                    };

                    console.log('checkout.createToken()');
                    customCheckout.createToken(callback);
                },
                onReset: function (event) {
                    var self = this;

                    console.log('checkout.onReset()');

                    event.preventDefault();
                    self.setPayButton(false);
                    cardNumber.clear();
                    self.hideErrorForId('card-number');
                    isCardNumberComplete = false;
                    cardCvv.clear();
                    self.hideErrorForId('card-cvv');
                    isCVVComplete = false;
                    cardExpiry.clear();
                    self.hideErrorForId('card-expiry');
                    isExpiryComplete = false;
                    $("#crd_name").val("");
                    isCardholderNameComplete = false;
                    $('#card-form').modal('hide');

                    setTimeout(function () {
                        $('#crd_name').focus();
                    },500);
                },
                onChangeCardholder: function (event) {
                    var self = this;

                    console.log('checkout.onChangeCardholder()');

                    event.preventDefault();
                    if (event.target.value != "") {
                        isCardholderNameComplete = true;
                        self.hideErrorForId('cardholder-name');
                        self.setPayButton(isCardNumberComplete && isCVVComplete && isExpiryComplete && isCardholderNameComplete);
                    } else {
                        isCardholderNameComplete = false;
                        self.showErrorForId('cardholder-name', "Cardholder Name is empty")
                        self.setPayButton(false);
                    }
                },
                hideErrorForId: function (id) {
                    console.log('hideErrorForId: ' + id);

                    var element = document.getElementById(id);

                    if (element !== null) {
                        var errorElement = document.getElementById(id + '-error');
                        if (errorElement !== null) {
                            errorElement.innerHTML = '';
                        }

                        var bootStrapParent = document.getElementById(id + '-bootstrap');
                        if (bootStrapParent !== null) {
                            bootStrapParent.classList.remove('has-error');
                            bootStrapParent.classList.add('has-success');
                        }
                    } else {
                        console.log('showErrorForId: Could not find ' + id);
                    }
                },
                showErrorForId: function (id, message) {
                    console.log('showErrorForId: ' + id + ' ' + message);

                    var element = document.getElementById(id);

                    if (element !== null) {
                        var errorElement = document.getElementById(id + '-error');
                        if (errorElement !== null) {
                            errorElement.innerHTML = message;
                        }

                        var bootStrapParent = document.getElementById(id + '-bootstrap');
                        if (bootStrapParent !== null) {
                            bootStrapParent.classList.add('has-error');
                            bootStrapParent.classList.remove('has-success');
                        }
                    } else {
                        console.log('showErrorForId: Could not find ' + id);
                    }
                },
                setPayButton: function (enabled) {
                    console.log('checkout.setPayButton() disabled: ' + !enabled);

                    var payButton = document.getElementById('cc-form-submit');
                    if (!payButton)
                        return;
                    if (enabled) {
                        payButton.disabled = false;
                        payButton.className = 'btn btn-primary';
                    } else {
                        payButton.disabled = true;
                        payButton.className = 'btn btn-primary disabled';
                    }
                },
                toggleProcessingScreen: function () {
                    var processingScreen = document.getElementById('processing-screen');
                    if (processingScreen) {
                        processingScreen.classList.toggle('visible');
                    }
                },
                showErrorFeedback: function (message) {
                    var xMark = '\u2718';
                    this.feedback = document.getElementById('feedback');
                    this.feedback.innerHTML = xMark + ' ' + message;
                    this.feedback.classList.add('error');
                },
                showSuccessFeedback: function (message) {
                    var checkMark = '\u2714';
                    this.feedback = document.getElementById('feedback');
                    this.feedback.innerHTML = checkMark + ' ' + message;
                    this.feedback.classList.add('success');
                },
                processTokenError: function (error) {
                    error = JSON.stringify(error, undefined, 2);
                    console.log('processTokenError: ' + error);

                    this.showErrorFeedback(
                        'Error creating token: </br>' + JSON.stringify(error, null, 4)
                    );
                    this.setPayButton(true);
                    this.toggleProcessingScreen();
                },
                processTokenSuccess: function (token) {
                    console.log('processTokenSuccess: ' + token);

                    //this.showSuccessFeedback('Success! Created token: ' + token);

                    if (typeof processCardForm === "function") {
                        processCardForm(token)
                    }
                    this.setPayButton(true);
                    this.toggleProcessingScreen();
                },
            };

            bamboraCheckoutController.init();
            // swap card cvv and expiry
            $('#card-cvv-bootstrap').insertAfter($('#card-expiry-bootstrap'));
        }
    });
</script>
