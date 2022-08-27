/*************************/
/* mail amazon identity  */
/*        start          */
/*************************/
$(document).ready(function() {
    // check identity status
    // init status
    verifyReady($('#email_identity_status_field > i').text());

    $(document).on('click', '#verify_identity', function (e) {
        e.preventDefault();
        let identityField = $('#email_for_identity');
        let identity = identityField.val(),
            is_domain = false;

        if (!isValidEmail(identity) && !isValidDomain(identity)) {
            $(identityField).css('border-color', 'red');
            message('Oops! Incorrect email or domain. Please try another one!.', 'danger');
            return false;
        }
        if (isValidDomain(identity)) {
            is_domain = true;
        }

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: baseUrl + 'settings/verify_identity',
            data: {domain: identity, is_domain: is_domain},
            global: false,
            success: function (resp) {
                let verificationAttributes = JSON.parse(resp.amazonIdentity.verificationAttributes);
                let dkimAttributes = JSON.parse(resp.amazonIdentity.dkimAttributes);
                let identity_id = resp.amazonIdentity.identity_id;

                if (resp.status != true) {
                    message('Oops! Cannot verify a new identity. Please try another one!.', 'danger');
                } else {
                    $('#identity_id').data('identity-id', identity_id);
                    verifyReady(verificationAttributes.VerificationStatus);
                    message('Successfully added new identity. Please see below section', 'success');
                }
            }
        });
    });

    $(document).on('click', '#check_status', function (e) {
        e.preventDefault();
        // let identityId = $(this).parents('tr').data('identity_id');
        let identityId = $('#identity_id').data('identity-id');

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: baseUrl + 'settings/check_identity',
            data: {identityId: identityId},
            global: false,
            success: function (resp) {
                if (resp.status != true) {
                    message('Oops! Cannot check identity status. Please try a bit later.', 'danger');
                } else {
                    let identity = resp.identity;

                    if (!resp.identity.identity === undefined) $(`[data-identity-id="${identityId}"]`).remove();
                    else {
                        let status = JSON.parse(identity.verificationAttributes);

                        verifyReady(status.VerificationStatus);
                    }

                    message('Successfully updated identity.', 'success');
                }
            }
        });
    });

    $('#email_for_identity').keyup(function () {
        let data = $(this).val();

        if (!isValidDomain(data) && !isValidEmail(data)) {
            verifyError();
            return;
        }

        if (isValidDomain(data) || isValidEmail(data)) {
            checkIssetVerify(data);
        }
    });

    function isValidDomain(domain) {
        return /^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/.test(domain)
    }

    function isValidEmail(email) {
        return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)
    }

    function checkIssetVerify(data) {
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: base_url + 'brands/ajax_isset_verify',
            data: {data: data},
            global: false,
            success: function (response) {
                if (response.status) {
                    let status = response.data.VerificationStatus;
                    verifyReady(status);
                }
                if (!response.status) {
                    verifyReady('Unverified');
                }
            }
        });
    }

    function verifyError() {
        $('#email_identity_status_field > i').text('Waiting for typing complete...').css('color', '#797979');
        $('#verify_field button').attr('disabled', true);
    }

    function verifyReady(status) {
        // hide all mail check element
        if (!status.length) {
            $('#check_status').css('display', 'none');
            $('#verify_identity').css('display', 'none');
            $('#email_identity_status_field').css('display', 'none');

            return;
        }

        let statusColor = {
            'Success': '#5cb85c',
            'Failed': '#d9534f',
            'Pending': '#5bc0de',
            'Unverified': '#f0ad4e'
        };

        // show status field
        $('#email_identity_status_field').css('display', 'inline-block');

        if (status == 'Success') {
            $('#check_status').css('display', 'none');
            $('#verify_identity').css('display', 'none');
        } else if (status == 'Unverified' || status == 'Failed') {
            $('#check_status').css('display', 'none');
            $('#verify_identity').css('display', 'block');
        } else {
            $('#check_status').css('display', 'block');
            $('#verify_identity').css('display', 'none');
        }

        $('#email_identity_status_field > i').text(status).css('color', statusColor[status]);
        $('#verify_field button').attr('disabled', false);
    }

    function message(msg, type, parent = 'body') {
        $('#errorMessage').remove();
        $(parent).append('<div class="alert alert-' + type + ' alert-message" id="errorMessage" style="display:none; top: 95px; right: 25px; left: unset;"><button type="button" class="close m-l-sm" data-dismiss="alert">×</button><strong>' + msg + '</strong></div>');
        $('#errorMessage').fadeIn();
        setTimeout(function () {
            $('#errorMessage').fadeOut(function () {
                $('#errorMessage').remove();
            });
        }, 10000);
    }
});
/*************************/
/* mail amazon identity  */
/*         end           */
/*************************/