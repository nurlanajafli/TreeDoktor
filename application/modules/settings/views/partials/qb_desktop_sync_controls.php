<input class="btn btn-danger" type="button" id="desktopExport" value="Export"/>
<input type="file" class="filestyle qb-desktop-import" data-icon="false" data-classbutton="btn btn-default" data-classinput="form-control inline input-s" id="filestyle-0" style="position: fixed; left: -500px;">
<label for="filestyle-0" class="btn btn-danger"><span>Import</span></label>
<?php if(!empty($qbDesktopLogsForSelect2)): ?>
    <div>
        <input class="select2 w-70 p-top-5" id="desktop-logs" data-href="#desktop-logs-value"/>
        <span class="p-left-10"><strong>Logs</strong></span>
    </div>
<?php endif; ?>