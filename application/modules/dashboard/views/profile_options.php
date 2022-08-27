<!--Profile Options -->
<div class="filled_white rounded shadow overflow m-top-10 p-5">
   <?php if($this->session->userdata('user_type') == "admin" || $this->session->userdata('CL') == 1 || $this->session->userdata('CL') == 2){ echo anchor('clients/new_client','<i class="icon-user icon-white"></i>&nbsp;&nbsp;Add Clients', 'class="btn btn-info"');}?>
   <?php if($this->session->userdata('user_type') == "admin" || $this->session->userdata('CL') == 1 || $this->session->userdata('CL') == 2){echo anchor('leads/map','<i class="icon-map-marker icon-white"></i>&nbsp;&nbsp;View Leads', 'class="btn btn-inverse"');}?>
</div>
<!--Profile Options -->