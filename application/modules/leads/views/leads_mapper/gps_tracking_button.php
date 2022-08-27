<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_owner() || is_cl_permission_all()) : ?>

    <div class="affix bg-white toggle-parent gps-button-container">
    <label id="toggle_gps" class="switch-mini" style="margin-bottom: 3px !important;padding-left: 3px;padding-right: 3px;font-size: 10px;">
        <div>GPS Tracking</div>
        <i class="glyphicon glyphicon-globe gps-icon text-info"></i>
        <input type="checkbox">
        <span></span>
    </label>
</div>

<div class="affix bg-white toggle-parent show-offline-container" style="display: none;">
    <label id="toggle_offline_gps" class="switch-mini" style="margin-bottom: 3px !important; padding-left: 3px; padding-right: 3px; font-size: 10px">
        <div>Show Offline</div>
        <input type="checkbox">
        <span style="margin-left: 10px;"></span>
    </label>
</div>

<?php endif; ?>
