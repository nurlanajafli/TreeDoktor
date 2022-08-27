<?php $this->load->view('includes/header'); ?>
<link href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/vendors/notebook/js/datepicker/datepicker.css'); ?>" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
<style type="text/css">
    .popover {
        max-width: 300px!important;
        background-color: #fff;
        color: #717171;
    }
    .notes .tooltip-inner {
        white-space: pre-wrap;
    }
    .editable-buttons {
        margin-bottom: 15px;
    }
    @media (min-width: 768px) {
        .form-inline .form-control {
            width: -webkit-fill-available!important;
        }
        .form-inline .form-group {
            display: block;
        }
    }
    .tooltip .tooltip-inner {
        max-height: 300px;
        overflow: hidden;
    }
    .job-actions {
        text-decoration: none!important;
        font-size: 18px;
        cursor: pointer;
        margin-left: 5px;
    }
    .job-actions.action-edit {
        color: #5e8a3a;
    }
    .job-actions.action-remove {
        color: #f34f3d;
    }
    .job-actions.action-execute {
        color: brown;
    }
    #editjob textarea {
        resize: none;
    }
    .btn {
        outline: none !important;
    }
    .ml-5 {
        margin-left: 5px !important;
    }
    .mr-5 {
        margin-right: 5px !important;
    }
    .wpx-80 {
        width: 80px;
    }
    .dataTables_date_filter {
        display: inline-block;
        margin-left: 10px;
    }
    @media screen and (max-width: 560px) {
        #update_job_form table tr{
            display: flex;
            flex-direction: column;
            width: 100%;
        }
        #update_job_form table td{
            width: 100%;
        }
    }
</style>
<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li>Jobs</li>
    </ul>
    <section class="panel panel-default">
        <header class="panel-heading">
            <div class="pull-right m-r-sm">
                <select class="form-control changeStatus">
                    <option value="all"<?php if('all' == $type) echo ' selected'; ?>> All jobs</option>
                    <option value="free"<?php if('free' == $type) echo ' selected'; ?>> Free jobs</option>
                    <option value="failed"<?php if('failed' == $type) echo ' selected'; ?>>Failed jobs</option>
                    <option value="completed"<?php if('completed' == $type) echo ' selected'; ?>>Completed jobs</option>
                </select>
            </div>
            <div class="clear"></div>
        </header>

        <div class="m-bottom-10 p-sides-10">
            <table class="table table-striped m-n" id="jobTable">
                <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Driver</th>
                    <th class="text-center">Payload</th>
                    <th class="text-center">Attempts</th>
                    <th class="text-center">Completed</th>
                    <th class="text-center">Available At</th>
                    <th class="text-center">Reserved At</th>
                    <th class="text-center">Created At</th>
                    <th class="text-center">Output</th>
                    <th class="text-center">Worker Pid</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </section>
</section>

<script type="text/x-jsrender" id="jobeditform-modal-tmp">
<div id="editjob" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: block;">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="update_job_form" method="post" action="/job/edit">
				<div class="modal-body">
					<h5 class="p-bottom-20">Edit job data</h5>
					<input type="hidden" id="job_id" name="job_id" value="{{:job_id}}">
					<table class="table table-striped b-a b-light m-t-n-xxs m-b-none">
						<tbody>
						    {{if  !job_is_completed && job_attempts && job_reserved_at != 0}}
                                <tr>
                                    <td class="w-200">
                                        <label class="control-label">Reset failed:</label>
                                    </td>
                                    <td class="p-left-30">
                                        <button type="button" class="btn btn-danger reset-failed">
                                            <i class="fa fa-rotate-left"></i>
                                        </button>
                                         <span style="margin-left: 10px;" class="badge edit-badge" data-toggle="tooltip" data-placement="right" data-html="true" data-original-title="NOTE: This job is failed. If you want to run it, please click on the button then save changes.">
                                            <i class="fa fa-info"></i>
                                        </span>
                                    </td>
                                </tr>
                            {{/if}}

                            {{if job_is_completed && job_reserved_at}}
                                <tr>
                                    <td class="w-200">
                                        <label class="control-label">Make free:</label>
                                    </td>
                                    <td class="p-left-30">
                                        <button type="button" class="btn btn-success make-free">
                                           <i class="fa fa-reply"></i>
                                        </button>
                                        <span class="badge edit-badge" style="margin-left: 10px;" data-toggle="tooltip" data-placement="right" data-html="true" data-original-title="NOTE: This job is already completed. If you want to run it again , please click on the button then save changes.">
                                            <i class="fa fa-info"></i>
                                        </span>
                                    </td>
                                </tr>
                            {{/if}}

                            {{props job_payload}}
                                {{if !key.includes('_id')}}
                                    <tr>
                                        <td class="w-200">
                                            <label class="control-label">Payload {{>key}}</label>
                                        </td>
                                        <td class="p-left-30">
                                            {{if prop.length<=140}}
                                                <input name="job_payload_{{>key}}" value="{{>prop}}" class="form-control">
                                            {{else}}
                                                <textarea name="job_payload_{{>key}}" class="form-control" rows="10">{{>prop}}</textarea>
                                            {{/if}}
                                        </td>
                                    </tr>
                                {{/if}}
                            {{/props}}
					    </tbody>
					</table>
				</div>
				<div class="modal-footer">
					<button class="btn close-job" data-dismiss="modal" aria-hidden="true" type="button">Close</button>
					<input type="submit" name="submit" value="Save Changes" class="btn btn-info update__job">
				</div>
			</form>
		</div>
	</div>
</div>
</script>

<script src="<?php echo base_url(); ?>assets/js/card-js.min.js?v=1.01"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
<script type="text/javascript">

  $(document).ready(function() {
    $.extend( $.fn.dataTableExt.oStdClasses, {
      "sFilterInput": "form-control",
      "sLengthSelect": "form-control col-md-6",
      "dataTables_filter": "form-group",
      "sLength": "dataTables_length row ml-5 mr-5"
    });
    var $jobTable = $('#jobTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "<?=$pagination_url;?>",
        data: function ( d ) {
          d.custom_filter_date = $('#date-range').val();
        }
      },
      columnDefs: [
        { data: 'job_id', className: "text-center", name: "#", targets: 0, render: function (id, type, job, meta) {
            return job.index+1;
          }
        },
        { data: 'job_driver', className: "text-center ", name: "Driver",targets: 1 },
        { data: 'job_payload', className: "text-center job-payload", name: "Payload" , targets: 2},
        { data: 'job_attempts', className: "text-center job-attempts", name: "Attempts", targets: 3},
        { data: 'job_is_completed', className: "text-center job-is-completed", name: "Completed" , targets: 4},
        { data: 'job_available_at', className: "text-center job-available-at", name: "Available At" , targets: 5},
        { data: 'job_reserved_at', className: "text-center job-reserved-at", name: "Reserved At" , targets: 6},
        { data: 'job_created_at', className: "text-center", name: "Created At" , targets: 7},
        { data: 'job_output', className: "text-center job-output", name: "Output" , targets: 8},
        { data: 'job_worker_pid', className: "text-center job-worker-pid", name: "Worker Pid" , targets: 9},
        { data: null, className: "text-center wpx-80", name: "Actions", targets: 10, render: function (id, type, job, meta) {
            $('.badge-output').tooltip();
            return job.action;
          }
        },
      ]
    });

    $('#jobTable_length').after(`<div id="jobTable_date_filter" class="dataTables_date_filter" ><label>Filter by date:<input id="date-range" type="date" class="form-control" placeholder="" aria-controls="jobTable" max="<?=date('Y-m-d'  )?>" value="<?=date('Y-m-d'  )?>" min="1900-01-01"></label></div>`)

    $(document).on('change', '#date-range', function() {
      $jobTable.ajax.reload();

    });

    $('.changeStatus').change(function() {
      location.href = baseUrl + 'jobs/manage/' + $(this).val();
    });

    $(document).on('click', '.action-remove', function () {
      var job_id = parseInt($(this).parents('tr').find('._job_id').val());
      var jobElementId = parseInt($('#job_'+job_id).val());

      if (job_id !== jobElementId) {
        alert ('Error!');
        return false;
      }

      $.post(baseUrl + 'job/delete', {job_id: job_id, global: false}, function (resp) {
        // count --
        if (resp.status != true) {
          message ('Oops! Cannot delete the job.', 'danger');
        }
        else {
          message ('Successfully deleted', 'success');
          $jobTable.ajax.reload();
          // $(`#job_${job_id}`).parents('tr').remove();
          // $('.job-count').text(+$('.job-count').text()-1);
        }
      }, 'json');
    });

    $(document).on('click', '.close-job', function () {
      $('#editjob').remove()
    })

    $(document).on('click', '.action-execute', function () {
      var job_id = parseInt($(this).parents('tr').find('._job_id').val());
      var jobElementId = parseInt($('#job_'+job_id).val());
      let type = location.pathname.split('/')[3];
      type = (type != undefined && type) ? type : 'all';

      if (job_id !== jobElementId) {
        alert ('Error!');
        return false;
      }

      $.post(baseUrl + 'job/execute', {job_id: job_id, global: false}, function (resp) {
        if (resp.status != true) {
          message ('Oops! Cannot execute the job.', 'danger');
        }
        else {
          message ('Successfully executed', 'success');
          $jobTable.ajax.reload();
        }
      }, 'json');
    });

    $(document).on('click', '.action-edit', function () {
      $('#editjob').remove();
      let job_id = parseInt($(this).parents('tr').find('._job_id').val());
      console.log(job_id)
      let job_payload = $(this).parents('tr').find('.job-payload-hidden').val();
      let job_attempts = +$(this).parents('tr').find('.job-attempts-hidden').val();
      let job_is_completed = +$(this).parents('tr').find('.job-is-completed-hidden').val();
      let job_reserved_at = $(this).parents('tr').find('.job-reserved-at-hidden').val();
      try {
        job_payload = JSON.parse(job_payload);
      }catch (e) {}

      var htmlOutput = $('#jobeditform-modal-tmp').render({job_id, job_payload, job_attempts, job_is_completed, job_reserved_at});

      $('body').append(htmlOutput);
      $('.edit-badge').tooltip();
      $('#editjob').show();
    });
  });

  $(document).on('click', '.make-free', function () {
    $('[name="make_free"]').remove();
    $('#update_job_form').append('<input type="hidden" value="1" name="make_free">')
  });

  $(document).on('click', '.reset-failed', function () {
    $('[name="reset_failed"]').remove();
    $('#update_job_form').append('<input type="hidden" value="1" name="reset_failed">')
  });

  $(document).on('submit', '#update_job_form', function (e) {
    e.preventDefault();
    let update_job_form_data = new FormData(this);

    let type = location.pathname.split('/')[3];
    type = (type != undefined && type) ? type : 'all';

    $.ajax({
      cache: false,
      type: "POST",
      //  dataType: "json",
      processData: false,
      contentType: false,
      url:  baseUrl + 'job/edit',
      data: update_job_form_data,
      dataType: 'json',
      success: function (resp) {
        if (resp.status != true) {
          message ('Oops! Cannot edit the job.', 'danger', '#update_job_form');
        } else {
          message('Successfully edited', 'success');
          $('#editjob').remove();

          let job = resp.result;
          let jobRow = $(`#job_${job.job_id}`).parents('tr');
          if (resp.deleteRow && type != 'all') {
            jobRow.remove()
          } else {
            jobRow.find('td.job-payload').text((job.job_payload).substring(0, 50));
            jobRow.find('td.job-payload').prepend(`<input class='_job_id' type='hidden' value='${job.job_id}' id='job_${job.job_id}'><textarea class="job-payload-hidden hidden">${job.job_payload}</textarea>`);
            jobRow.find('td.job-attempts').text(job.job_attempts);
            jobRow.find('td.job-attempts').prepend(`<input class="job-attempts-hidden hidden" type="text" value="${job.job_attempts}">`);
            jobRow.find('td.job-is-completed').text(job.job_is_completed);
            jobRow.find('td.job-is-completed').prepend(`<input class="job-is-completed-hidden hidden" type="text" value="${job.job_is_completed}">`);
            jobRow.find('td.job-reserved-at').text(job.job_reserved_at);
            jobRow.find('td.job-reserved-at').prepend(`<input class="job-reserved-at-hidden hidden" type="text" value="${job.job_reserved_at}">`);
          }
        }
      },
      error: function () {
        alert('SYSTEM ERROR, TRY LATER AGAIN');
      }
    });
  });

  function message (msg, type, parent='body') {
    $(parent).append('<div class="alert alert-'+type+' alert-message" id="errorMessage" style="display:none; top: 95px; right: 25px; left: unset;"><button type="button" class="close m-l-sm" data-dismiss="alert">×</button><strong>' + msg + '</strong></div>');
    $('#errorMessage').fadeIn();
    setTimeout(function () {
      $('#errorMessage').fadeOut(function () {
        $('#errorMessage').remove();
      });
    }, 10000);
  }
</script>
<?php $this->load->view('includes/footer'); ?>
