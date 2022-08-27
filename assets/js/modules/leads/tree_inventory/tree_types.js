$(document).ready( function () {
  dataTable = $('#trees-table').DataTable({
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
      row.setAttribute('data-trees-id', data.trees_id)
    },
    "order": [[0, "desc"]],
    ajax: {
      method: 'get',
      url: base_url + 'leads/tree_inventory/tree_types/get_trees',
      dataSrc: function (response) {
        json = response.data;

        $('.js-total-trees').text(response.totalTrees);

        return json;
      },
    },
    columnDefs: [
      {
        'targets': 0,
        'data': 'trees_id',
      },

      {
        'targets': 1,
        'data': 'trees_name_eng',
      },

      {
        'targets': 2,
        'data': 'trees_name_lat',
      },

      {
        'targets': 3,
        'orderable': false,
        'render': function (data, type, row, meta) {
          return `
                    <button class="btn btn-xs btn-default js-edit-tree">
                        <i class="fa fa-pencil"></i>
                    </button>

                    <button class="btn btn-xs btn-default js-delete-tree">
                        <i class="fa fa-trash-o"></i>
                    </button>
                `;
        }
      },
    ]
  })

  $(document).on('click', '.js-create-tree-btn', function() {
    document.getElementById('create-trees-form').reset();
    $('#create-trees-modal').modal();
  })

  $(document).on('click', '.js-create-tree', function() {
    let sendData = new FormData($('#create-trees-form').get(0));

    $.ajax({
      method: 'post',
      data: sendData,
      contentType: false,
      processData: false,
      url: base_url + 'leads/tree_inventory/tree_types/create_tree',
      dataType: 'json',

      success: function (response) {
        if (response.status == 'ok') {
          $('#create-trees-modal').modal('hide');
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

  $(document).on('click', '.js-delete-tree', function () {
    if (! confirm('Are you sure ?')) {
      return;
    }

    let treeId = $(this).closest('tr').attr('data-trees-id')

    $.ajax({
      method: 'post',
      data: {
        'tree_id': treeId
      },
      url: base_url + 'leads/tree_inventory/tree_types/delete_tree',
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

  $(document).on('click', '.js-update-tree', function () {
    let treeId = $(this).closest('tr').attr('data-trees-id')
    let sendData = new FormData($('#edit-trees-form').get(0));

    $.ajax({
      method: 'post',
      data: sendData,
      contentType: false,
      processData: false,
      url: base_url + 'leads/tree_inventory/tree_types/update_tree',
      dataType: 'json',

      success: function (response) {
        if (response.status == 'ok') {
          $('#edit-trees-modal').modal('hide');
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

  $(document).on('click', '.js-edit-tree', function () {
    let editTreesModal = $('#edit-trees-modal');
    let currentRow = $(this).closest('tr');

    let currentTreeName = currentRow.find('td').eq(1).text();
    let currentTreeLatinName = currentRow.find('td').eq(2).text();
    let currentTreeId = currentRow.attr('data-trees-id');

    editTreesModal.find('#edit-trees-english-name-input').val(currentTreeName)
    editTreesModal.find('#edit-trees-latin-name-input').val(currentTreeLatinName);
    editTreesModal.find('#trees-id-input').val(currentTreeId);

    editTreesModal.modal();
  })

  $(document).on('click', "#search-tree-btn", function() {
    dataTable.search($('#search-trees-input').val()).draw();
  });
});
