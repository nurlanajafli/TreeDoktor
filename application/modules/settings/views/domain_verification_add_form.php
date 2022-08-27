<!--test for dev-->
<?php if($defaultEmailDriver === 'amazon'): ?>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default" id="verify_new_domain_form">
            <div class="panel-heading">Verify a New Identity</div>
            <div class="panel-body text-sm p-n">
                <table class="table table-striped m-n">
                    <tr>
                        <td class="identity-txt">
                            To verify a new <span class="identity_type_txt">email address</span>, enter it below and click the <strong>Verify This <span class="identity_type_txt text-capitalize">Email Address</span></strong> button.
                        </td>
                    </tr>
                    <tr style="<?php if(!$this->session->userdata('system_user')) : ?>visibility: collapse;<?php endif; ?>">
                        <td>
                            <label class="col-lg-6" style="display: flex; flex-wrap: wrap; align-items: center;">
                                <input type="radio" data-type="domain" name="identity_type"> Add Domain
                            </label>
                            <label class="col-lg-6" style="display: flex; flex-wrap: wrap; align-items: center;">
                                <input type="radio" data-type="email" name="identity_type" checked> Add Email
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label class="col-lg-12" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;">
                                <strong class=""><span class="identity_type_txt text-capitalize">Email Address</span>: </strong>
                                <input type="text" name="identity_value" class="form-control col-8" placeholder="example@test.com" style="width: 85%"/>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td class="domain-txt hidden">
                            DomainKeys Identified Mail (DKIM) provides proof that the email you send originates from your domain and is authentic.
                            DKIM signatures are stored in your domain's DNS system.
                        </td>
                    </tr>
<!--                                    <tr>-->
<!--                                        <td>-->
<!--                                            <label class="col-lg-12" style="display: flex; flex-wrap: wrap; align-items: center;">-->
<!--                                                <input type="checkbox" name="generate_dkim" class="" style="margin: 0 10px 0 0;"/>-->
<!--                                                <strong class="">Generate DKIM settings </strong>-->
<!--                                            </label>-->
<!--                                        </td>-->
<!--                                    </tr>-->
                </table>
            </div>
            <div class="panel-footer text-sm p-n">
                <div class="text-right">
                    <button id="verify_new_domain" class="btn btn-success" style="margin: 10px;">Verify This <span class="identity_type_txt text-capitalize">Email Address</span></button>
                </div>
            </div>
        </div>

        <?php $this->load->view('domain_verification_info'); ?>

    </div>

    <!--<div class="col-lg-6">
        <div id="domainVerificationStatus"></div>
    </div>-->

    <div class="col-lg-12">
        <div class="panel panel-default" id="verify_new_domain_table">
            <div class="panel-heading">Amazon Identities Verification Info</div>
            <div class="panel-body text-sm p-n">
                <table class="table table-striped m-n">
                    <tr>
                        <th>Amazon identity</th>
                        <th>Identity type</th>
                        <th>Verification attributes</th>
                        <th>DKIM attributes</th>
                        <th>Last Checked</th>
                        <th>Check status</th>
                    </tr>
                    <?php foreach ($amazonIdentities as $amazonIdentity) { ?>
                        <tr data-identity_id="<?=$amazonIdentity->identity_id?>">
                            <?php $verificationAttributes = $amazonIdentity->verificationAttributes ? json_decode($amazonIdentity->verificationAttributes) : [] ?>
                            <?php $dkimAttributes = $amazonIdentity->dkimAttributes ? json_decode($amazonIdentity->dkimAttributes) : [] ?>
                            <td class="identityName"><?= $amazonIdentity->identity ?></td>
                            <td><span class="badge badge-<?=($amazonIdentity->is_domain == '1') ? 'danger' : 'success' ?> identityIsDomain"><?= ($amazonIdentity->is_domain == '1') ? 'domain' : 'email' ?></span></td>
                            <td>
                                <?php if (isset($verificationAttributes->VerificationStatus)) { ?>
                                    <strong>Status: </strong> <span class="identityVerificationStatus"><?= $verificationAttributes->VerificationStatus ?></span></br>
                                    <?php if (isset($verificationAttributes->VerificationToken) && $verificationAttributes->VerificationStatus != 'Failed') { ?>
                                        <strong>Token: </strong> <span class="identityVerificationToken"><?= $verificationAttributes->VerificationToken ?></span>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if(isset($dkimAttributes->DkimEnabled) && $dkimAttributes->DkimEnabled) { ?>
                                    <?php if (isset($dkimAttributes->DkimVerificationStatus)) { ?>
                                        <strong>Verification Status: </strong> <span class="identityDkimVerificationStatus"><?= $dkimAttributes->DkimVerificationStatus ?></span></br>
                                        <?php if (isset($dkimAttributes->DkimTokens) && $dkimAttributes->DkimVerificationStatus != 'Failed') { ?>
                                            <strong>Tokens: </strong> <span class="identityDkimTokens"><?= implode('</br>', $dkimAttributes->DkimTokens); ?>
                                            <?php foreach ($dkimAttributes->DkimTokens as $item){ ?>
                                                <input type="hidden" value="<?=$item?>" class="identityDkimTokensItem" name="identityDkimTokensItem[]"/>
                                            <?php } ?>
                                            </span>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <td>
                                <span><?=$amazonIdentity->last_checked;?></span>
                            </td>
                            <td>
                                <?php if (isset($verificationAttributes->VerificationStatus) && $verificationAttributes->VerificationStatus == 'Pending') { ?>
                                    <button class="btn btn-info check-identity">Check <i class="fa fa-angle-right"></i></button>
                                <?php } ?>
                                <button class="btn btn-danger delete-identity"><i class="fa fa-trash-o"></i></button>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style scoped>
    .card-header {
        border: 1px solid #d4c8c8;
        border-radius: 14px;
    }
    .bold {
        font-weight: bold;
    }
    .identityName {
        cursor: pointer;
        text-decoration: underline;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .accordion {
        background-color: rgb(255 255 255);
    }
    [name=identity_type] {
        margin: unset !important;
        margin-right: 15px !important;
    }
    .accordion h2{
        margin: unset;
    }
    .card-header {
        padding: 10px;
        background-color: whitesmoke;
    }

    .btn-link, #verify_new_domain, .card-header,
    .check-identity, .delete-identity,
    .btn-link:focus, #verify_new_domain:focus, .card-header:focus{
        outline: none !important;
    }

    .accordion .card-body{
        padding: 15px;
    }

    #DkimTokens td, #verify_new_domain_table td {
        word-break: break-all;
    }
    .badge-danger {
        background-color: #fb4040;
    }

    .badge-success {
        background-color: #8ec165;
    }
    .text-capitalize {
        text-transform: capitalize;
    }
</style>

<script>
    $(document).ready(function () {

        // check identity status
        $(document).on('click', '.check-identity', function (e) {
          e.preventDefault();
          var identityId =  $(this).parents('tr').data('identity_id');

          $.post(baseUrl + 'settings/check_identity', {identityId, global: true}, function (resp) {
            if (resp.status != true) {
              message ('Oops! Cannot check identity status. Please try a bit later.', 'danger');
            } else {
              var identity = resp.identity;

              if (!resp.identity.identity === undefined) $(`[data-identity_id="${identityId}"]`).remove();
              else {
                let amazonIdentity = {
                  identity: identity.identity,
                  is_domain: (identity.is_domain === '1'),
                  verificationAttributes: JSON.parse(identity.verificationAttributes),
                  dkimAttributes: JSON.parse(identity.dkimAttributes),
                  identity_id: identity.identity_id,
                  last_checked: identity.last_checked
                };
                // add row to table for added domain (delete for showing fresh data)
                let $row = $('#domain-add-row-modal-tmp').render({ amazonIdentity });
                if ( !$(`[data-identity_id="${identity.identity_id}"]`).length) {
                  let $row = $('#domain-add-row-modal-tmp').render({ amazonIdentity });
                  $('#verify_new_domain_table').find('table').append($row);
                } else {
                  $(`[data-identity_id="${identity.identity_id}"]`).html($($row).html())
                }
              }

              message ('Successfully updated identity.', 'success');
            }
          }, 'json')
        });

        // delete amazon identities
      $(document).on('click', '.delete-identity', function (e) {
        e.preventDefault();
        var identityId =  $(this).parents('tr').data('identity_id');

        $.post(baseUrl + 'settings/delete_identity', {identityId, global: true}, function (resp) {
          if (resp.status != true) {
            message ('Oops! Cannot delete identity. Please try a bit later.', 'danger');
          } else {
            $(`[data-identity_id="${identityId}"]`).remove();
            message ('Successfully deleted identity.', 'success');
          }
        }, 'json')
      });

        // change corresponding identity text regardless type
        $('[name=identity_type]').click(function (e) {
            let type = $(this).data('type');
            var identity_txt = type === 'email' ? 'email address' : 'domain';
            var identity_value = type === 'email' ? 'example@test.com' : 'domain.name';
            var identity_type = type === 'email' ? type : 'text';
            $('.identity_type_txt').text(identity_txt);

            if (type === 'email') {
              $('.domain-txt').addClass('hidden');
            } else {
              $('.domain-txt').removeClass('hidden');
            }

            $('input[name="identity_value"]').attr({
              'placeholder': identity_value,
              'type': identity_type
            });
        })

        // add new identity (email or domain)
        $('#verify_new_domain').click(function (e) {
            e.preventDefault();
            let $form = $(this).parents('#verify_new_domain_form');
            let $identityName = $form.find('input[name="identity_value"]');
            let identity = $identityName.val();
            let is_domain = $('[name=identity_type]:checked').data('type') === 'domain';
            // let dkim = $form.find('input[name="generate_dkim"]').prop('checked');

            $identityName.css('border-color', '#d9d9d9');
            $identityName.find('.alert.alert-danger').remove();
            $('#accordionExample').remove();

            if (is_domain && !isValidDomain(identity)) {
                $identityName.css('border-color', 'red')
                message ('Oops! Cannot verify a new domain. Please try another one!.', 'danger');
                return false;
            } else if (!is_domain && !isValidEmail (identity)){
                $identityName.css('border-color', 'red')
                message ('Oops! Cannot verify a new email. Please try another one!.', 'danger');
                return false;
            }

            $.post(baseUrl + 'settings/verify_identity', {domain: identity, is_domain, global: true}, function (resp) {
              let verificationAttributes = JSON.parse(resp.amazonIdentity.verificationAttributes);
              let dkimAttributes = JSON.parse(resp.amazonIdentity.dkimAttributes);
              let identity_id = resp.amazonIdentity.identity_id;

                if (resp.status != true) {
                  message ('Oops! Cannot verify a new identity. Please try another one!.', 'danger');
                }
                else {
                    $identityName.val('');
                    let amazonIdentity = {
                        templateId: Math.floor(Math.random() * 1000),
                        identity,
                        is_domain,
                        verificationAttributes,
                        dkimAttributes,
                        identity_id,
                        dkimTokens: resp.dkimTokens ? resp.dkimTokens : dkimAttributes.DkimTokens,
                        last_checked: resp.amazonIdentity.last_checked
                    };
                    var htmlOutput = $('#domain-info-modal-tmp').render(amazonIdentity);

                    // add to the table
                    $('#domainVerificationStatus').append(htmlOutput);
                    if(resp.deletedId) !$(`[data-identity_id="${resp.deletedId}"]`).remove();
                    // add row to table for added domain (delete for showing fresh data)
                    let $row = $('#domain-add-row-modal-tmp').render({ amazonIdentity });

                    if ( !$(`[data-identity_id="${identity_id}"]`).length) {
                      $('#verify_new_domain_table').find('table').append($row);
                    } else {
                      $(`[data-identity_id="${identity_id}"]`).html($($row).html())
                    }
                    message ('Successfully added new identity. Please see below section', 'success');
                }
            }, 'json');
        })

      function isValidDomain (domain) {
        return /^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/.test(domain)
      }
      function isValidEmail (email) {
        return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)
      }

      function message (msg, type, parent='body') {
          $('#errorMessage').remove();
        $(parent).append('<div class="alert alert-'+type+' alert-message" id="errorMessage" style="display:none; top: 95px; right: 25px; left: unset;"><button type="button" class="close m-l-sm" data-dismiss="alert">×</button><strong>' + msg + '</strong></div>');
        $('#errorMessage').fadeIn();
        setTimeout(function () {
          $('#errorMessage').fadeOut(function () {
            $('#errorMessage').remove();
          });
        }, 10000);
      }

      // add info modal for doamins and emails from table
      $(document).on('click', '.identityName', function () {
        $('#modalTemplate').remove();
        let identityName = $(this).parents('tr').find('.identityName').text();
        let identityIsDomain = $(this).parents('tr').find('.identityIsDomain').text();
        let identityVerificationStatus = $(this).parents('tr').find('.identityVerificationStatus').text();
        let identityVerificationToken = $(this).parents('tr').find('.identityVerificationToken').text();
        let identityDkimVerificationStatus = $(this).parents('tr').find('.identityDkimVerificationStatus').text();
        let dkimTokens =  $(this).parents('tr').find('.identityDkimTokens').find('.identityDkimTokensItem').serialize();
        identityIsDomain = identityIsDomain.trim()  === 'domain';
        let amazonIdentity = {
          templateId: Math.floor(Math.random() * 1000),
          identity: identityName.trim(),
          is_domain: identityIsDomain,
          verificationAttributes: {
            VerificationStatus: identityVerificationStatus.trim(),
            VerificationToken: identityVerificationToken.trim()
          },
          dkimAttributes: {
            DkimVerificationStatus: identityDkimVerificationStatus.trim()
          },
          dkimTokens: dkimTokens ? (dkimTokens.replace(/identityDkimTokensItem%5B%5D=/ig, '')).split('&') : []
        };
        console.log(amazonIdentity)
        var htmlOutput = $('#domain-info-modal-tmp').render(amazonIdentity);
        console.log(dkimTokens)
        var emptyModal = $('#empty-modal').render({header: identityName, body: htmlOutput});
        $('body').append(emptyModal);
        $('#modalTemplate').modal('show');
      })
    })
</script>
