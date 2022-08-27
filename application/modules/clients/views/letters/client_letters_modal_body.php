
<header class="panel-heading">Email to {{:client.client_name}}</header>
<div class="modal-body">
    <div class="form-horizontal">
        <div class="control-group">
            <label class="control-label">Email to {{:client.client_name}}</label>
            <div class="controls">
                <input id="email-tags" placeholder="Email to..." style="background-color: #fff;"
                       value="{{if client.primary_contact && client.primary_contact.cc_email != undefined }}{{:client.primary_contact.cc_email}}{{/if}}"
                       type="text" multiple="multiple" autocomplete="nope"/>
                <input type="hidden" name="email_tags"  autocomplete="off" autocorrect="off"
                       autocapitalize="off" spellcheck="false">
                <!--<input class="email form-control email-tags"  type="text"
                       value="{{if client.primary_contact && client.primary_contact.cc_email != undefined }}{{:client.primary_contact.cc_email}}{{/if}}"
                       placeholder="Email to..." style="background-color: #fff;"/>-->
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">Email from </label>
            <div class="controls">
                <input class="fromEmail form-control" type="text" value="{{:brand.brand_email}}" placeholder="Email from..." style="background-color: #fff;"/>
            </div>
        </div>


        <div class="control-group">
            <label class="control-label">Email Subject</label>
            <div class="controls">
                <input class="subject form-control" type="text"
                       value="{{:letter.email_template_title}}"
                       placeholder="Email Subject" style="background-color: #fff;"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">Email Text</label>
            <div class="controls">
                        <textarea id="client_template_text" class="form-control" value="">
                            {{:letter.email_template_text}}
                        </textarea>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-success" data-save-template="{{:letter.email_template_id}}" data-client_id="{{:client.client_id}}">
        <span class="btntext" >Send</span>
        <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
             class="preloader">
    </button>
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
</div>

<?php /*
<div id="email-<?php echo $letter['email_template_id']; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Email to <?php echo $client_data->client_name; ?></header>
            <div class="modal-body">
                <div class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label">Email to <?php echo $client_data->client_name; ?></label>
                        <div class="controls">
                            <input class="email form-control" type="text"
                                   value="<?php echo $client_contact && isset($client_contact['cc_email']) ? trim(strtolower($client_contact['cc_email'])) : NULL; ?>"
                                   placeholder="Email to..." style="background-color: #fff;"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Email from </label>
                        <div class="controls">
                            <input class="fromEmail form-control" type="text"
                                <?php if($this->session->userdata('user_id') == $client_data->user_id) : ?>
                                    value="<?php echo $client_data->user_email; ?>"
                                <?php else :?>
                                    value="<?php echo $this->config->item('account_email_address'); ?>"
                                <?php endif; ?>
                                   placeholder="Email from..." style="background-color: #fff;"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Email Subject</label>
                        <div class="controls">
                            <input class="subject form-control" type="text"
                                   value="<?php echo $letter['email_template_title']; ?>"
                                   placeholder="Email Subject" style="background-color: #fff;"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Email Text</label>
                        <div class="controls">
									<textarea id="template_text_<?php echo $letter['email_template_id']; ?>" class="form-control" value="">
										<?php echo $letter['email_template_text']; ?>
									</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" data-save-template="<?php echo $letter['email_template_id']; ?>" data-client_id="<?php echo $client_data->client_id; ?>">
                    <span class="btntext" >Send</span>
                    <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
                         class="preloader">
                </button>
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>
 */ ?>

<!--<script src="<?php /*echo base_url(); */?>assets/js/modules/clients/email_tags.js?v=1.0"></script>-->