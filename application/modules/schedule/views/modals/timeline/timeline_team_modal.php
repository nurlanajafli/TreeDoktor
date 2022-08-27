<div class="modal fade timeline-team-modal" id="timeline-team-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 720px">
        <form data-type="ajax" data-callback="ScheduleTimeline.saveTeamCallback" data-url="<?php echo base_url('schedule/ajax_new_team'); ?>" data-global="false">
        <div class="modal-content">
            <div class="modal-header p-top-10 p-bottom-10">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="myModalLabel"><i class="fa fa-truck fa-2x text-success"></i></h5>
            </div>
            <div class="modal-body p-top-10 p-bottom-10 blur" id="timeline-team-modal-body">
                <div class="p-15 m-15 text-center"><img src="/assets/img/loading.gif" style=""></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
