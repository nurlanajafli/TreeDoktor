<script type="text/x-jsrender" id="domain-info-modal-tmp">
    <div class="accordion" id="accordionExample">
        <div class="card">
            <div class="card-header" id="domainVerification">
                <h2 class="mb-0">
                    <button class="btn btn-link collapsed bold" type="button" data-toggle="collapse" data-target="#collapseDomainVerification{{>templateId}}" aria-expanded="false" aria-controls="collapseDomainVerification{{>templateId}}">
                        Verification
                    </button>
                </h2>
            </div>
            <div id="collapseDomainVerification{{>templateId}}" class="collapse" aria-labelledby="domainVerification" data-parent="#accordionExample">
                <div class="card-body">
                    <p class="verificationStatus"><strong>Status: </strong> <span>{{>verificationAttributes.VerificationStatus}}</span></p>
                    {{if verificationAttributes.VerificationStatus === 'Pending'}}
                        <p><strong>Record Type: </strong> <span>TXT (Text)</span></p>
                        <p><strong>TXT Name*: </strong> <span>_amazonses.{{>identity}}</span></p>
                        {{if verificationAttributes.VerificationToken}}<p class="txtValue"><strong>TXT Value: </strong> <span>{{>verificationAttributes.VerificationToken}}</span></p> {{/if}}

                        {{if is_domain}}
                        <p>
                            A TXT record is a type of DNS record that provides additional information about your domain.
                            The procedure for adding TXT records to your domain's DNS settings depends on who provides your DNS service.
                            For general information, see <a href="https://docs.aws.amazon.com/ses/latest/DeveloperGuide/dns-txt-records.html">Amazon SES Domain Verdeation TXT Records.</a>
                        </p>
                        <p>
                            <strong>NOTE:</strong> If your DNS provider does not allow underscores in record names, you can omit _amazonses from the record name.
                            To help you easily identify this record within your domain DNS settings, you can optionally prefix the recoord value with amazonses.
                        </p>
                        <p>
                            To complete verification of this domain, you must add a TXT record in the domain's DNS settings with the values above.
                            When Amazon Web Services has confirmed that these values are present in the DNS settings for the domain, the Status al- the domain will change to "verified".
                            This may take up to 72 hours.
                        </p>
                        {{/if}}

                        {{if !is_domain}}
                        <div class="card">
                            <div class="card-body p-n">
                                <p>
                                Dear Customer </br>
                                We have received a request to authorize this email address for use with Amazon SES.
                                If you requested this verification, please check your email to confirm that you are authorized to use this email address:
                                Your request will not be processed unless you confirm the address using URL sent your email.
                                The link expires 24 hours after your original verification request.
                                If you did NOT request to verify this email address, do not click on the link.
                                Please note that many times, the situation isn't a phishing attempt, but either a misunderstanding of how to use our service,
                                or someone setting up email-sending capabilities on your behalf as part of a legitimate service, but without having fully communicated the procedure first.
                                If you are still concerned, please forward this notification to <strong>ses-enforcement@amazon.com</strong> and let us know in the forward that you did not request the verification.
                                </p>
                            </div>
                         </div>
                        {{/if}}
                    {{/if}}
                </div>
            </div>
        </div>
        {{if is_domain}}
        <div class="card">
            <div class="card-header" id="dkimVerification">
                <h2 class="mb-0">
                    <button class="btn btn-link collapsed bold" type="button" data-toggle="collapse" data-target="#collapsedkimVerification{{>templateId}}" aria-expanded="false" aria-controls="collapsedkimVerification{{>templateId}}">
                        DKIM
                    </button>
                </h2>
            </div>
            <div id="collapsedkimVerification{{>templateId}}" class="collapse" aria-labelledby="dkimVerification" data-parent="#accordionExample">
                <div class="card-body">
                    <p class="DkimVerificationStatus">
                        <strong>DKIM Verification Status:</strong> {{>dkimAttributes.DkimVerificationStatus}}
                    </p>
                    {{if verificationAttributes.VerificationStatus === 'Pending'}}
                        <p>
                            settings for your domain have been generated.</strong> The information below must he added to your domain's DNS records.
                            How you update the DNS settings depends on who provides your DNS service; if your DNS service provided by a domain name registrar,
                            please contact that registrar to update your DNS records.
                        </p>
                        <p>
                            To enable DKIM signing for your domain, the records below must be entered in your DNS settings.
                            AWS will automatically detect the presence of these records, and allow DKIM signing at that time. Note that verification of these settings may take up to 72 hours.
                        </p>

                        <table class="table table-striped m-n" id="DkimTokens">
                            <tr>
                                <th>Name</th>
                                <th style="width: 100px">Type</th>
                                <th>Value</th>
                            </tr>
                            {{props dkimTokens ~identity=identity}}
                            <tr>
                                <td class="">
                                    {{>prop}}._domainkey.{{:~identity.trim()}}
                                </td>
                                <td>
                                   CNAME
                                </td>
                                <td class="">
                                    {{>prop}}.dkim.amazonses.com
                                </td>
                            </tr>
                            {{/props}}
                        </table>
                    {{/if}}
                </div>
            </div>
        </div>
        {{/if}}
    </div>
</script>
<script type="text/x-jsrender" id="domain-add-row-modal-tmp">
<tr data-identity_id="{{>amazonIdentity.identity_id}}">
    <td class="identityName"> {{> amazonIdentity.identity }}</td>
    <td><span class="badge badge-{{> amazonIdentity.is_domain == '1' ? 'danger' : 'success' }} identityIsDomain">{{> amazonIdentity.is_domain == '1' ? 'domain' : 'email' }}</span></td>
    <td>
        {{if amazonIdentity.verificationAttributes.VerificationStatus}}
            <strong>Status: </strong> <span class="identityVerificationStatus"> {{>amazonIdentity.verificationAttributes.VerificationStatus}}</span>
        {{/if}}</br>
        {{if amazonIdentity.verificationAttributes.VerificationToken && amazonIdentity.verificationAttributes.VerificationStatus != 'Failed'}}
            <strong>Token: </strong> <span class="identityVerificationToken"> {{>amazonIdentity.verificationAttributes.VerificationToken}}</span>
        {{/if}}
    </td>
    <td>
    {{if amazonIdentity.is_domain && amazonIdentity.dkimAttributes.DkimEnabled }}
        {{if amazonIdentity.dkimAttributes.DkimVerificationStatus}}
            <strong>Verification Status: </strong> <span class="identityDkimVerificationStatus"> {{>amazonIdentity.dkimAttributes.DkimVerificationStatus}}</span>
        {{/if}}</br>
        {{if amazonIdentity.dkimAttributes.DkimTokens && amazonIdentity.dkimAttributes.DkimVerificationStatus != 'Failed'}}
            <strong>Tokens: </strong> <span class="identityDkimTokens">
                {{for amazonIdentity.dkimAttributes.DkimTokens}}
                <input type="hidden" value="{{>#data}}" class="identityDkimTokensItem" name="identityDkimTokensItem[]"/>
                  {{>#data}}</br>
                {{/for}}
            </span>
        {{/if}}
    {{/if}}
    </td>
    <td>
        <span>{{>amazonIdentity.last_checked}}</span>
    </td>
     <td>
        {{if amazonIdentity.verificationAttributes.VerificationStatus == 'Pending'}}
            <button class="btn btn-info check-identity">Check <i class="fa fa-angle-right"></i></button>
        {{/if}}
        <button class="btn btn-danger delete-identity"><i class="fa fa-trash-o"></i></button>
    </td>
</tr>
</script>

<script type="text/x-jsrender" id="empty-modal">
<div id="modalTemplate" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{:header}}</h4>
      </div>
      <div class="modal-body">
        {{:body}}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</script>
