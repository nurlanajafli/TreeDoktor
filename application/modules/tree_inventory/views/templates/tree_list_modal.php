<div class="modal fade" id="inventory-list-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">
          Tree Inventory 
          
          <form class="get-pdf" id="get-pdf" data-type="ajax" data-url="<?php echo site_url('tree_inventory/update_screen/'.$client->client_id); ?>" data-callback="TreeInventoryMap.open_pdf">
            <div id="map-screen-form"></div>
            <input type="hidden" name="ti_lead_id" value="<?php if(isset($ti_lead_id)): echo $ti_lead_id; endif; ?>">
            <button type="submit" class="btn btn-sm">PDF</button>
          </form>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </h5>
      </div>
      <div class="modal-body">
        
          <div class="row">

            <div class="col-md-6">
              <section class="panel panel-default m-n">
                <div class="list-group no-radius alt">
                  <a class="list-group-item text-decoration-none" href="#">
                    <i class="fa fa-user text-info"></i>&nbsp;
                    <span><?php echo $client->client_name; ?></span>
                  </a>
                  <a class="list-group-item text-info text-decoration-none" href="#" data-email="<?php echo $client->cc_email; ?>">
                    <i class="fa fa-map-marker text-info"></i>&nbsp;
                    <?php echo client_address((array)$client); ?>
                  </a>
                </div>
              </section>

            </div>
            <div class="col-md-6">
              <section class="panel panel-default m-n">
                <div class="list-group no-radius alt">
                  <a class="list-group-item text-decoration-none" href="#" class="createCall" data-client-id="<?php echo $client->client_id; ?>" data-number="<?php echo $client->cc_phone_clean; ?>">
                    <i class="fa fa-phone text-info"></i>&nbsp;
                    <?php echo numberTo($client->cc_phone); ?>
                  </a>
                  <a class="list-group-item text-decoration-none" href="#" data-email="<?php echo $client->cc_email; ?>">
                    <i class="fa fa-envelope text-info"></i>&nbsp;
                    <?php echo $client->cc_email; ?>
                  </a>
                </div>
              </section>
            </div>
            
          </div>
          <hr class="m-top-10 m-bottom-10">  

          <div id="map-screen" class="m-bottom-10">
              <div class="text-center" style="height: 100px;">
              <img src="<?php echo base_url('assets/img/ajax-loader.gif'); ?>">
              </div>
          </div>

          <table class="table table-striped">
            <thead>
              <tr>
                <th class="text-center">Tree</th>
                <th class="text-center">Tree #</th>
                <th width="17%">Tree Type</th>
                <th class="text-center">Size</th>
                <th>Priority</th>
                <th width="10%">Work Type</th>
                <th width="35%">Note</th>
                <th>Cost</th>
                <th>Stump</th>
              </tr>
            </thead>
            <tbody id="tree-list-table">
              
            </tbody>
            <tfoot id="tree-list-table-totals"></tfoot>
          </table>

          <br>

          <table class="table table-striped">
            <thead>
              <tr>
                <td>Key to Priorities</td>
                <td width="65%">Key to Work Type</td>
              </tr>
            </thead>
            <tbody>
              <tr>
              <td>
                <p><strong>Low</strong> = Pruning for aesthetic purposes, optional</p>
                <p><strong>Mid</strong> = General trimming</p>
                <p><strong>High</strong> = Priority pruning</p>
              </td>
              <td>
                <?php if(isset($work_types) && !empty($work_types)): ?>
                    <?php foreach($work_types as $type): ?>
                    <div class="col-md-6 col-sm-6"><p><strong><?php echo $type->ip_name_short; ?>:</strong><?php echo $type->ip_name; ?></p></div>
                    <?php endforeach; ?>
                <?php endif; ?>
              </td>
              </tr>
            </tbody>
          </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script type="text/x-jsrender" id="map-screen-form-tmp">
  <input type="hidden" name="map_image" value="{{:map_image}}">
</script>
<script type="text/x-jsrender" id="map-screen-tmp">
  <img src="{{:map_image}}" width="100%">
</script>

<script type="text/x-jsrender" id="tree-list-table-tmp">
  <tr>
    <td>
      {{if ti_file!=NULL }}
        <img data-toggle="popover" data-trigger="hover" data-html="true" data-placement="right" data-content='<div class="panel panel-default m-n"><img src="{{:ti_file}}" alt="{{:ti_tree_type}}" width="200px" height="auto"></div>' src="{{:ti_file}}" alt="{{:ti_tree_type}}" class="img-circle" width="60px" height="60px">
      {{/if}}
    </td>
    <td class="text-center">{{:ti_tree_number}}</td>
    <td><span class="text-capitalize">{{:~showString((tree_type)?tree_type.trees_name_eng:'')}}</span> {{if tree_type && tree_type.trees_name_lat}}({{:tree_type.trees_name_lat}}){{/if}}</td>
    <td class="text-center">{{:ti_size}}</td>
    <td class="text-center">{{:~priority_labels(ti_tree_priority)}}</td>
    <td>{{:~work_types_string(work_types)}}</td>
    <td>{{:ti_remark}}</td>
    <td>{{:~currency_format(ti_cost)}}</td>
    <td>{{:~currency_format(ti_stump_cost)}}</td>
  </tr>
</script>

<script type="text/x-jsrender" id="tree-list-table-totals-tmp">
  <tr>
    <td colspan="7"><strong>Sum:</strong></td>
    <th>{{:total_cost}}</th>
    <th>{{:total_stump}}</th>
  </tr>

  <tr>
    <td colspan="7"><strong>Tax:</strong></td>
    <th>{{:tax_cost}}</th>
    <th>{{:tax_stump}}</th>
  </tr>

  <tr>
    <td colspan="7"><strong>Total:</strong></td>
    <th>{{:grand_total_cost}}</th>
    <th>{{:grand_total_stump}}</th>
  </tr>

</script>
