$(document).ready( function () {
  dataTable = $('#work-types-table').DataTable({
    "processing": true,
    "serverSide": true,
    // 'lengthChange': true,
    // 'iDisplayLength': 50,
    // stateSave: true,
    "responsive": true,
    "paging": true,
    "pageLength": 50,
    'sDom': "<'row'>t<'datatable-footer'<'col-sm-6'i><'col-sm-6'p>>",
    "createdRow": function( row, data) {
      row.setAttribute('data-work-type-ip-id', data.ip_id)
    },
    "order": [[0, "desc"]],
    ajax: {
      method: 'get',
      url: base_url + 'leads/tree_inventory/work_types/get_work_types',
      dataSrc: function (response) {
        json = response.data;

        $('.js-total-work-types').text(response.totalWorkTypes);

        return json;
      },
    },
    columnDefs: [
      {
        'targets': 0,
        'data': 'ip_id',
      },

      {
        'targets': 1,
        'data': 'ip_name_short',
      },

      {
        'targets': 2,
        'data': 'ip_name',
      },

      {
        'targets': 3,
        'orderable': false,
        'render': function (data, type, row, meta) {
          return `
                    <button class="btn btn-xs btn-default js-edit-work-type">
                        <i class="fa fa-pencil"></i>
                    </button>

                    <button class="btn btn-xs btn-default js-delete-work-type">
                        <i class="fa fa-trash-o"></i>
                    </button>
                `;
        }
      },
    ]
  })

  $(document).on('click', '.js-create-work-types-btn', function() {
    document.getElementById('create-work-type-form').reset();
    $('#create-work-type-modal').modal();
  })

  $(document).on('click', '.js-create-work-type', function() {
    let sendData = new FormData($('#create-work-type-form').get(0));

    $.ajax({
      method: 'post',
      data: sendData,
      contentType: false,
      processData: false,
      url: base_url + 'leads/tree_inventory/work_types/create_work_type',
      dataType: 'json',

      success: function (response) {
        if (response.status == 'ok') {
          $('#create-work-type-modal').modal('hide');
          dataTable.ajax.reload();
          return;
        }

        if (response.hasOwnProperty('errors')) {
          alert(response.errors[Object.keys(response.errors)[0]][0]);
        } else {
          alert('Error occurred');
        }
      },
    })
  })

  $(document).on('click', '.js-delete-work-type', function () {
    if (! confirm('Are you sure ?')) {
      return;
    }

    let workTypeIpId = $(this).closest('tr').attr('data-work-type-ip-id')

    $.ajax({
      method: 'post',
      data: {
        'ip_id': workTypeIpId
      },
      url: base_url + 'leads/tree_inventory/work_types/delete_work_type',
      dataType: 'json',

      success: function (response) {
        if (response.status == 'ok') {
          dataTable.ajax.reload();
          return;
        }
        alert('Error occurred');
      },
    })
  })

  $(document).on('click', '.js-update-work-type', function () {
    let sendData = new FormData($('#edit-work-types-form').get(0));

    $.ajax({
      method: 'post',
      data: sendData,
      contentType: false,
      processData: false,
      url: base_url + 'leads/tree_inventory/work_types/update_work_type',
      dataType: 'json',

      success: function (response) {
        if (response.status == 'ok') {
          $('#edit-work-types-modal').modal('hide');
          dataTable.ajax.reload();
          return;
        }

        if (response.hasOwnProperty('errors')) {
          alert(response.errors[Object.keys(response.errors)[0]][0]);
        } else {
          alert('Error occurred');
        }
      },
    })
  })

  $(document).on('click', '.js-edit-work-type', function () {
    let editWorkTypesModal = $('#edit-work-types-modal');
    let currentRow = $(this).closest('tr');

    let currentWorkTypeShortName = currentRow.find('td').eq(1).text();
    let currentWorkTypeName = currentRow.find('td').eq(2).text();
    let currentWorkTypeId = currentRow.attr('data-work-type-ip-id');

    editWorkTypesModal.find('#edit-work-type-ip-name-short').val(currentWorkTypeShortName)
    editWorkTypesModal.find('#edit-work-type-ip-name-input').val(currentWorkTypeName);
    editWorkTypesModal.find('#work-types-ip-id-input').val(currentWorkTypeId);

    editWorkTypesModal.modal();
  })

  $(document).on('click', "#search-work-types-btn", function() {
    dataTable.search($('#search-work-types-input').val()).draw();
  });
});