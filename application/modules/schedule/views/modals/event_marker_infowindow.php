<div class="map-pin"
     data-map-crew-type="{{if estimate.estimates_services_crew[0]!=undefined }}{{:estimate.estimates_services_crew[0].crew_name }}{{/if}}"
     data-estimator="<?php /*$workorder_data['estimator_id']*/ ?>"
     data-map-wo-status="{{:wo_status}}"
     data-map-wo-price="{{:estimate.sum_actual_without_tax}}"
     data-map-wo-id="{{:id}}"  <?php /*echo $mapAttrs;*/ ?> >{{:estimate.client.client_address}}</div>