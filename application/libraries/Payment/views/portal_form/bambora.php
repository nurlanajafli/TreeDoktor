<link rel="stylesheet" href="<?php echo base_url('assets/css/card-js.min.css'); ?>" type="text/css"/>

<style type="text/css">
    #cc_form #driver_bambora #crd_name {
        padding-left: 0;
        font: 400 13.3333px Arial;
    }

    #cc_form #driver_bambora #crd_name::placeholder,
    #cc_form #driver_bambora .additional-form input::placeholder {
        color: #717171;
    }

    /* card images are added to card number */
    #cc_form #driver_bambora #card-number {
        background-image: none;

        background-origin: content-box;
        background-position: calc(100% + 40px) center;
        background-repeat: no-repeat;
        background-size: contain;
    }

    #cc_form #driver_bambora .additional-form .text {
        text-align: left;
    }

    #cc_form #driver_bambora .has-feedback > .form-control {
        padding-left: 35px;
        position: relative;
    }
    #cc_form #driver_bambora .has-feedback > .form-control > i {
        position: absolute;
        left: 11px;
        top: 9px;
        color: #717171;
        font-size: 16px;
    }
    #cc_form #driver_bambora .has-feedback > .form-control > i.fa-credit-card {
        left: 9px;
    }
    #cc_form #driver_bambora .has-feedback > .form-control > i.fa-calendar-o {
        left: 10px;
    }
    #cc_form #driver_bambora #card-expiry-bootstrap {
        padding-right: 5px;
    }
    #cc_form #driver_bambora #card-cvv-bootstrap {
        padding-left: 5px;
    }
    #cc_form #driver_bambora .additional-form .bill-city-wrapper,
    #cc_form #driver_bambora .additional-form .bill-city-wrapper input {
        width: 100%;
        margin-right: 0;
    }
    #cc_form #driver_bambora .additional-form .bill-zip-wrapper,
    #cc_form #driver_bambora .additional-form .bill-state-wrapper {
        float: left;
    }
    #cc_form #driver_bambora .additional-form .bill-phone-wrapper,
    #cc_form #driver_bambora .additional-form .bill-country-wrapper {
        float: right;
        width: 50%;
    }
    #cc_form #driver_bambora .additional-form .bill-phone-wrapper input,
    #cc_form #driver_bambora .additional-form select {
        width: calc(100% - 5px);
        background-color: #FDFDFD;
    }
    #cc_form #driver_bambora .additional-form .bill-phone-wrapper input,
    #cc_form #driver_bambora .additional-form .bill-country-wrapper select {
        margin-left: 5px;
    }
    #cc_form #driver_bambora .additional-form .bill-zip-wrapper input,
    #cc_form #driver_bambora .additional-form .bill-state-wrapper select {
        margin-left: 0;
        margin-right: 5px;
    }
</style>
<div id="driver_bambora" class="drivers">
    <div class="form-group col-xs-12 has-feedback" id="cardholder-name-bootstrap">
        <div id="cardholder-name" class="form-control">
            <i class="fa fa-user"></i>
            <input id="crd_name" name="crd_name" class="form-control" value="" placeholder="Cardholder Name"
                   style="box-sizing: border-box; width: 100%; height: 100%; border: none; overflow: visible; background-color: transparent;">
        </div>
        <label class="help-block" for="cardholder-name" id="cardholder-name-error"></label>
    </div>
    <div class="form-group col-xs-12 has-feedback" id="card-number-bootstrap">
        <div id="card-number" class="form-control">
            <i class="fa fa-credit-card"></i>
        </div>
        <label class="help-block" for="card-number" id="card-number-error"></label>
    </div>

    <div class="clear"></div>
    <div class="form-group col-xs-6 has-feedback" id="card-cvv-bootstrap">
        <div id="card-cvv" class="form-control">
            <i class="fa fa-lock"></i>
        </div>
        <label class="help-block" for="card-cvv" id="card-cvv-error"></label>
    </div>
    <div class="form-group col-xs-6 has-feedback" id="card-expiry-bootstrap">
        <div id="card-expiry" class="form-control">
            <i class="fa fa-calendar-o"></i>
        </div>
        <label class="help-block" for="card-expiry" id="card-expiry-error"></label>
    </div>
    <div class="additional-form col-xs-12 p-left-15">
        <?php if (!$profileExist): ?>
            <div class="text">
                <?php if (!$internalPayment): ?>
                    <a href="#" data-toggle="collapse" data-target="#bill-addr">Edit billing information</a>
                <?php else: ?>
                    Edit billing information
                <?php endif; ?>
            </div>
            <div id="bill-addr" class="bill-addr <?php echo !$internalPayment ? 'collapse' : ''; ?>">
                <div class="bill-fname-wrapper fwrapper">
                    <input id="bill_fname" name="bill_fname" class="name" placeholder="First name"
                           value="<?php echo $billFname ?? ""; ?>">
                </div>
                <div class="bill-lname-wrapper fwrapper">
                    <input id="bill_lname" name="bill_lname" class="name" placeholder="Last name"
                           value="<?php echo $billLname ?? ""; ?>">
                </div>
                <div class="bill-address-wrapper fwrapper">
                    <input id="bill_address" name="bill_address" class="name" placeholder="Address"
                           value="<?php echo $billAddress ?? ""; ?>">
                </div>
                <div class="bill-city-wrapper fwrapper">
                    <input id="bill_city" name="bill_city" class="name" placeholder="City"
                           value="<?php echo $billCity ?? ""; ?>">
                </div>

                <div class="bill-state-wrapper fwrapper">
                    <select name="bill_state" id="bill_state" class="form-control"
                            data-bill-state="<?php echo $billState ?? ""; ?>">
                        <option id="bill_state_None" value="" disabled="" selected>Select your state/province:</option>
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
                </div>
                <div class="bill-country-wrapper fwrapper">
                    <select class="form-control" name="bill_country" id="bill_country"
                            data-bill-country="<?php echo $billCountry ?? ""; ?>">
                        <option value="" selected>-None-</option>
                        <option value="CA">Canada</option>
                        <option value="US">United States</option>
                    </select>
                </div>
                <div class="bill-zip-wrapper fwrapper">
                    <input id="bill_zip" name="bill_zip" class="name" placeholder="Zip code/Postal code"
                           value="<?php echo $billZip ?? ""; ?>">
                </div>
                <div class="bill-phone-wrapper fwrapper">
                    <input id="bill_phone" name="bill_phone" class="name" placeholder="Phone"
                           value="<?php echo $billPhone ?? ""; ?>">
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="clear"></div>
    <div id="feedback" class="col-xs-12"></div>
    <div class="clear"></div>
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
                    //this.selectBillState(document.getElementById('bill_country'));
                    this.selectBillCountry();
                },
                createInputs: function () {
                    console.log('checkout.createInputs()');

                    // Create and mount the inputs
                    var options = {};
                    options.placeholder = 'Card number';
                    let brands = JSON.parse('<?php echo json_encode(config_item('payment_brands')); ?>');
                    if (Array.isArray(brands) && brands.length > 0) {
                        options.brands = brands;
                    }
                    cardNumber = customCheckout.create('card-number', options);
                    cardNumber.mount('#cc_form #driver_bambora #card-number');

                    options = {};
                    options.placeholder = 'CVV';
                    cardCvv = customCheckout.create('cvv', options);
                    cardCvv.mount('#cc_form  #driver_bambora  #card-cvv');

                    options = {};
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

                    if (document.getElementById('bill_country')) {
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
