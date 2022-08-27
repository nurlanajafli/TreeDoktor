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
    .input:focus, .expiry:focus {
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
        .expiry-container, .cvc-container {
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
    body.darkmode input {
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
</style>
<div id="driver_authorize">
    <div id="crd_form" class="row">
        <input id="crd_name" name="crd_name" class="name input">
        <input id="crd_num" name="crd_num" class="card-number input">
        <input id="crd_exp_m" name="crd_exp_m" class="expiry-month">
        <input id="crd_exp_y" name="crd_exp_y" class="expiry-year">
        <input id="crd_cvv" name="crd_cvv" class="cvc input">
    </div>
    <div class="additional-form">
        <input type="checkbox" id="showInfoCheckbox" hidden>
        <label class="hideinfo" for="showInfoCheckbox">
            <div class="showinfotext">Show billing information</div>
        </label>
        <label class="showinfo" for="showInfoCheckbox">
            <div class="showinfotext">Hide billing information</div>
        </label>
        <div class="showinfo">
            <div class="row">
                <div class="col-2">
                    <h4 class="is-req">First name</h4>
                    <input id="bill_fname" name="bill_fname" class="name input"
                           value="<?php echo $billFname ?? ""; ?>">
                </div>
                <div class="col-2">
                    <h4 class="is-req">Last name</h4>
                    <input id="bill_lname" name="bill_lname" class="name input"
                           value="<?php echo $billLname ?? ""; ?>">
                </div>
            </div>
            <div id="bill-addr" class="row">
                <div class="col-3">
                    <h4 class="is-req">Address</h4>
                    <input id="bill_address" name="bill_address" class="name input"
                           value="<?php echo $billAddress ?? ""; ?>">
                </div>
                <div class="col-3">
                    <h4 class="is-req">City</h4>
                    <input id="bill_city" name="bill_city" class="name input"
                           value="<?php echo $billCity ?? ""; ?>">
                </div>
                <div class="col-3">
                    <h4 class="is-req">State</h4>
                    <input id="bill_state" name="bill_state" class="name input"
                           value="<?php echo $billState ?? ""; ?>">
                </div>
                <div class="col-3">
                    <h4 class="is-req">Country</h4>
                    <input id="bill_country" name="bill_country" class="name input"
                           value="<?php echo $billCountry ?? ""; ?>">
                </div>
                <div class="col-3">
                    <h4 class="is-req">Zip code/Postal code</h4>
                    <input id="bill_zip" name="bill_zip" class="name input"
                           value="<?php echo $billZip ?? ""; ?>">
                </div>
                <div class="col-3">
                    <h4 class="is-req">Phone</h4>
                    <input id="bill_phone" name="bill_phone" class="name input"
                           value="<?php echo $billPhone ?? ""; ?>">
                </div>
            </div>
        </div>
    </div>
    <div id="feedback"></div>
</div>
<script src="<?php echo base_url(); ?>assets/js/card-js.min.js?v=1.01"></script>
<script>
    $(document).ready(function () {
        let script = document.createElement('script');
        script.src = "<?php echo $isSandbox ? 'https://jstest.authorize.net/v1/Accept.js' : 'https://js.authorize.net/v1/Accept.js' ?>";
        document.getElementById('driver_authorize').append(script);
        script.onload = function () {
            $('#crd_form').CardJs();
            var additionalData = {};

            var authorizeCheckoutController = {
                init: function () {
                    console.log('checkout.init()');
                    $('#cc_form .additional-form input').each(function(idx, el) {
                        additionalData[el.name] = el.value;
                    });
                    this.addListeners();
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
                            .getElementById('crd_num').addEventListener('keyup', self.onkeyup);
                        document
                            .getElementsByClassName('expiry')[0].addEventListener('keyup', self.onkeyup);
                    }
                },
                onkeyup: function(event){
                    let className = $(this).attr('class');
                    let maxLength = $(this).attr('maxLength');
                    let length = $(this).val().length;
                    if(maxLength && length && maxLength > 0 && length > 0 && maxLength == length){
                        if(className.indexOf('card-number') != -1)
                            $('.expiry')[0].focus();
                        else if(className.indexOf('expiry') != -1)
                            $('#crd_cvv').focus();
                    }
                },
                onSubmit: function (event) {

                    var self = this;

                    var cardForm = $('#crd_form');

                    var cardNumber = cardForm.CardJs('cardNumber');
                    var cardNumberWithoutSpaces = CardJs.numbersOnlyString(cardNumber);
                    var cardType = cardForm.CardJs('cardType');
                    var name = cardForm.CardJs('name');
                    var expiryMonth = cardForm.CardJs('expiryMonth');
                    var expiryYear = cardForm.CardJs('expiryYear');
                    var cvc = cardForm.CardJs('cvc');
                    console.log('checkout.onSubmit()');

                    event.preventDefault();
                    self.setPayButton(false);
                    self.toggleProcessingScreen();

                    var callback = function (response) {
                        console.log('token result : ' + JSON.stringify(response));

                        if (response.messages.resultCode === "Error") {
                            self.processTokenError(response.messages);
                        } else {
                            self.processTokenSuccess(response.opaqueData);
                        }
                    };

                    var isSandbox =  <?php echo ($isSandbox) ? "true" : "false"; ?>;

                    var authData = {};
                    authData.clientKey = "<?php echo $publicKey; ?>";
                    authData.apiLoginID = "<?php echo $loginId; ?>";

                    var cardData = {};
                    cardData.cardNumber = cardNumberWithoutSpaces;
                    cardData.month = expiryMonth;
                    cardData.year = expiryYear;
                    cardData.cardCode = cvc;
                    cardData.fullName = name;

                    var secureData = {};
                    secureData.authData = authData;
                    secureData.cardData = cardData;

                    console.log('checkout.createToken()');
                    Accept.dispatchData(secureData, callback);

                },
                onReset: function (event) {

                    var self = this;
                    console.log('checkout.onReset()');

                    $('#cc_form input').not('#cc_form .additional-form input').each(function(idx, el) {
                        $(el).val('');
                    });
                    $('#cc_form .additional-form input').each(function(idx, el) {
                        if(additionalData[el.name].length !== 0)
                            $(el).val(additionalData[el.name]);
                    });
                    event.preventDefault();
                    setTimeout(function () {
                        $('#crd_name').focus();
                    },500);
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
                    var i = 0;
                    var string = '';
                    //var error;
                    while (i < error.message.length) {
                        console.log(
                            error.message[i].code + ": " +
                            error.message[i].text
                        );
                        string = string + error.message[i].code + ": " + error.message[i].text + "</br>";
                        i = i + 1;
                    }
                    this.showErrorFeedback(
                        'Error creating token: </br>' + string
                    );
                    this.setPayButton(true);
                    this.toggleProcessingScreen();
                },
                processTokenSuccess: function (token) {
                    console.log('processTokenSuccess: ' + token);

                    //this.showSuccessFeedback('Success! Created token: ' + token);
                    var additional = {};
                    $('#cc_form .additional-form input').each(function(idx, el) {
                        additional[el.name] = el.value;
                    });

                    if (typeof processCardForm === "function") {
                        processCardForm(token, additional)
                    }
                    this.setPayButton(true);
                    this.toggleProcessingScreen();
                },
            };

            authorizeCheckoutController.init();
            // swap card number and name
            $('.card-number-wrapper').insertAfter($('.name-wrapper'));
        };
    });
</script>
