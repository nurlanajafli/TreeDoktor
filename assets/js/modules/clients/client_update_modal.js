$(function () {
    $(".update__client").click(function () {
        const author = $("input#author").val();
        const client_id = $("input#client_id").val();

        const client_name = $("input#client_name").val();
        const client_contact = $("input#client_contact").val();

        const client_type = $("select#client_type").val();
        const client_status = $("select#client_status").val();

        const client_main_intersection = $("input#client_main_intersection").val();
        const client_address = $("input#change_client_address").val();
        const client_city = $("input#change_client_city").val();
        const client_state = $("input#change_client_state").val();
        const client_zip = $("input#change_client_zip").val();
        const client_country = $("input#change_client_country").val();
        const client_promo_code = $("input#client_promo_code").val();
        const client_unsubscribe = $("select#client_unsubscribe").val();

        let client_address_check = 0;
        let client_main_intersection2 = '';
        let client_address2 = '';
        let client_city2 = '';
        let client_state2 = '';
        let client_zip2 = '';

        if ($('#client_address_check').is(':checked')) {
            client_address_check = $("input#client_address_check").val();
            client_main_intersection2 = $("input#client_main_intersection2").val();
            client_address2 = $("input#route2").val();
            client_city2 = $("input#locality2").val();
            client_state2 = $("input#administrative_area_level_12").val();
            client_zip2 = $("input#postal_code2").val();
        }

        let data = {
            author: author,
            client_id: client_id,
            client_name: client_name,
            client_contact: client_contact,
            client_type: client_type,
            client_status: client_status,
            client_main_intersection: client_main_intersection,
            client_address: client_address,
            client_city: client_city,
            client_state: client_state,
            client_zip: client_zip,
            client_country: client_country,
            client_address_check: client_address_check,
            client_main_intersection2: client_main_intersection2,
            client_address2: client_address2,
            client_city2: client_city2,
            client_state2: client_state2,
            client_zip2: client_zip2,
            client_promo_code: client_promo_code,
            client_unsubscribe: client_unsubscribe,
        };

        // client tax
        const tax_selected = $("select#client_tax").find("option:selected");
        if (tax_selected.data('taxName') !== '') {
            data.client_tax_name = tax_selected.data('taxName');
            data.client_tax_value = tax_selected.data('taxValue');
            data.client_tax_rate = tax_selected.data('taxRate');
        }

        $.ajax({
            type: "POST",
            url: baseUrl + 'clients/update_client/',
            data: data,
            success: function () {
                $("#clientUpdateModal").modal('hide');
                location.reload();
            }
        });
        return false;
    });
});