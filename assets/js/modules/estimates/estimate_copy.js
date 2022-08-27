$(document).ready(function(){
    $("#copyEstimateForm-modal #new_client_id").html('').select2({
        minimumInputLength:2,
        placeholder: "Search",

        ajax: {
            url: baseUrl + "clients/ajax_get_reff",
            params:{
                type:'POST',
                global:false,
            },
            dataType: 'json',
            quietMillis: 500,
            data: function (term, page) {
                return {
                    name: term,
                    trigger: search_by_clients
                };
            },
            results: function (data, page) {
                $($('#copyEstimateForm-modal #new_client_id').select2("container")).addClass('search-result');
                return { results: data.items };
            },
            cache: true
        }
    });

    $(document).on('click', '.copyEstimate', function () {
        $(this).hide();
        var data = $('#copyEstimateForm-modal-body>form').serialize();

        var spinner="<div class=\"text-center\" style=\"position: relative;\">\n" +
            "    <i class=\"fa fa-spinner fa fa-spin fa fa-5x\" style=\"font-size: 13em;color: #97cf74;margin-top:20px\"></i>\n" +
            "    <img src='"+baseUrl+"assets/img/processing_modal.svg' width=\"70px\" height=\"70px\" style=\"position: absolute;left: calc(50% - 35px);top: 70px;\" class=\"icon\" />\n" +
            "</div>";
        $('#copyEstimateForm-modal .copyEstimateForm-result').html(spinner);

        $.ajax({
            method: "GET",
            url: baseUrl + 'estimates/copyFast',
            dataType: "json",
            data: data,
            global: false,
            success: function(response){
                    var template="<h4> New estimate: "+response.result.new_lead_id+"</h4>";
                    template+="<a href='"+response.result.open_url+"' class='btn btn-success' target='_blank'> Open</a>";
                    $('#copyEstimateForm-modal .copyEstimateForm-result').html(template);
            }
        });
    })

    $(document).on('change', 'select[name="select_copy_client"]', function () {
        if($(this).val()==2&&$('.select_other_client').hasClass('hidden')){
            $('.select_other_client').removeClass('hidden');
        }else if($(this).val()==1&& !$('.select_other_client').hasClass('hidden')){
            $('.select_other_client').addClass('hidden');
        }
    })

    $(document).on('change', 'input[name="to_status"]', function () {
       var selected_value=$('input[name="to_status"]:checked').val();
        $('div.group_statuses').hide();
        $('div.'+selected_value+'_status').show();
    })
})


