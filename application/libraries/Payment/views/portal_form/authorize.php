<div id="driver_authorize" class="drivers">
    <div class="jsCards" id="crd_form">
        <input id="crd_name" name="crd_name" class="name">
        <input id="crd_num" name="crd_num" class="card-number">
        <input id="crd_exp_m" name="crd_exp_m" class="expiry-month">
        <input id="crd_exp_y" name="crd_exp_y" class="expiry-year">
        <input id="crd_cvv" name="crd_cvv" class="cvc">
    </div>
    <div class="additional-form">
        <div id="showBillingInfo">Show billing information</div>
        <div id="hideBillingInfo">Hide billing information</div>
        <div id="bill-addr" class="bill-addr <?php echo !$internalPayment ? 'collapse' : ''; ?>">
            <div>
                <div class="input-label">First name</div>
                <input id="bill_fname" name="bill_fname" class="name" placeholder="First name" value="<?php echo $billFname ?? ""; ?>">
            </div>
            <div>
                <div class="input-label">Last name</div>
                <input id="bill_lname" name="bill_lname" class="name" placeholder="Last name" value="<?php echo $billLname ?? ""; ?>">
            </div>
            <div>
                <div class="input-label">Address</div>
                <input id="bill_address" name="bill_address" class="name" placeholder="Address" value="<?php echo $billAddress ?? ""; ?>">
            </div>
            <div>
                <div class="input-label">City</div>
                <input id="bill_city" name="bill_city" class="name" placeholder="City" value="<?php echo $billCity ?? ""; ?>">
            </div>
            <div>
                <div class="input-label">State</div>
                <input id="bill_state" name="bill_state" class="name" placeholder="State" value="<?php echo $billState ?? ""; ?>">
            </div>
            <div>
                <div class="input-label">Country</div>
                <input id="bill_country" name="bill_country" class="name" placeholder="Country" value="<?php echo $billCountry ?? ""; ?>">
            </div>
            <div>
                <div class="input-label">Zip code/Postal code</div>
                <input id="bill_zip" name="bill_zip" class="name" placeholder="Zip code/Postal code" value="<?php echo $billZip ?? ""; ?>">
            </div>
            <div>
                <div class="input-label">Phone</div>
                <input id="bill_phone" name="bill_phone" class="name" placeholder="Phone" value="<?=numberTo('097890765467')?>">
            </div>
        </div>
    </div>
    <div id="feedback"></div>
</div>
<script src="<?php echo base_url(); ?>assets/js/card-js.min.js?v=1.01"></script>
<script>
    $(document).ready(function () {
        let PHONE_NUMBER_MASK = "<?php echo config_item('phone_inputmask'); ?>";
        let script = document.createElement('script');
        script.src = "<?php echo $isSandbox ? 'https://jstest.authorize.net/v1/Accept.js' : 'https://js.authorize.net/v1/Accept.js' ?>";
        document.getElementById('driver_authorize').append(script);
        script.onload = function () {
            $('#crd_form').CardJs();

            var additionalData = {};
            var cvc = false;
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
                        if(className.indexOf('card-number') != -1) {
                            $('#crd_name')[0].focus();
                        } else if(className.indexOf('expiry') != -1) {
                            $('#crd_cvv').focus();
                        }
                    }
                },
                onSubmit: function (event) {

                    //parent.postMessage({portal_pay:true, sending: true}, '*');

                    var self = this;

                    var cardForm = $('#crd_form');

                    var cardNumber = cardForm.CardJs('cardNumber');
                    var cardNumberWithoutSpaces = CardJs.numbersOnlyString(cardNumber);
                    var cardType = cardForm.CardJs('cardType');
                    var name = cardForm.CardJs('name');
                    var expiryMonth = cardForm.CardJs('expiryMonth');
                    var expiryYear = cardForm.CardJs('expiryYear');
                    cvc = cardForm.CardJs('cvc');
                    console.log('checkout.onSubmit()');

                    event.preventDefault();
                    self.setPayButton(false);
                    self.toggleProcessingScreen();

                    var callback = function (response) {
                        console.log('token result : ' + JSON.stringify(response));
                        try {
                            if (response.messages.resultCode === "Error") {
                                self.processTokenError(response.messages);
                            } else {
                                parent.postMessage({portal_pay:true, sending: true}, '*');
                                self.processTokenSuccess(response.opaqueData);
                            }
                        } catch (error) {
                            console.log(error);
                            parent.postMessage({portal_pay:true, error: true}, '*');
                        }
                    };

                    var isSandbox = <?php echo ($isSandbox) ? "true" : "false"; ?>;

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
                        if(additionalData[el.name].length !== 0) {
                            $(el).val(additionalData[el.name]);
                        }
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
                    console.log('showErrorFeedback');
                    var xMark = '\u2718';
                    this.feedback = document.getElementById('feedback');
                    this.feedback.innerHTML = xMark + ' ' + message;
                    this.feedback.classList.add('error');
                },
                showSuccessFeedback: function (message) {
                    console.log('showSuccessFeedback');
                    var checkMark = '\u2714';
                    this.feedback = document.getElementById('feedback');
                    this.feedback.innerHTML = checkMark + ' ' + message;
                    this.feedback.classList.add('success');
                },
                processTokenError: function (error) {
                    parent.postMessage({payed: false, error: true}, '*');
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

                    if (typeof processCardFormPortal === "function") {
                        processCardFormPortal(token, additional)
                    }
                    //this.setPayButton(true);
                    this.toggleProcessingScreen();
                },
            };

            authorizeCheckoutController.init();
            // hide by Andrew
            // swap card number and name
            // $('.card-number-wrapper').insertAfter($('.name-wrapper'));
            // end
        };

        $('#bill_phone').inputmask({"mask":PHONE_NUMBER_MASK, removeMaskOnSubmit: true });
    });
</script>
