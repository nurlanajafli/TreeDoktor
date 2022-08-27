<style>
    .note-email-logs {
        margin-top: 12px;
    }
    .note-email-logs h6 {
        margin-bottom: 5px;
    }
    .email-stat-row-date {
        font-size: 11px;
        font-weight: bold;
        color: #777;
        font-style: italic;
    }
    ul.emails-stat-details-list {
        padding-left: 10px;
        margin: 0;
    }
    ul.emails-stat-details-list > li {
        margin-bottom: 4px;
    }
    .emails-stat-details-list {
        max-height: calc( 17px * 10);
        overflow: hidden;
    }
    .emails-stat-details-list.active {
        max-height: 100%;
    }
    .note-email-toggle {
        margin-top: 10px;
        font-weight: bold;
        display: block;
        cursor: pointer;
    }

</style>

<script type="text/x-jsrender" id="client-notes-tmp">
    <?php $this->load->view('clients/notes/client_notes_tmp'); ?>
</script>

<script type="text/x-jsrender" id="client-notes-body-tmp">
    <?php $this->load->view('clients/notes/client_notes_body_tmp'); ?>
</script>

<script type="text/x-jsrender" id="client-sms-notes-tmp">
    <?php $this->load->view('clients/notes/client_sms_notes_tmp'); ?>
</script>

<script type="text/x-jsrender" id="client-call-notes-tmp">
    <?php $this->load->view('clients/notes/client_calls_notes_tmp'); ?>
</script>
