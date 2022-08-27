<?php if (is_array($client_files_statuses) && sizeof($client_files_statuses)): ?>
    <ul class="nav nav-tabs mode-nav m-n client-files-tabs">
        <?php foreach ($client_files_statuses as $status => $statusData): ?>
            <li<?php echo $statusData['default'] ? ' class="active"' : ''; ?>>
                <a href="#<?php echo $statusData['name']; ?>_files"
                   class="change-client-files"
                   data-toggle="tab"
                   aria-expanded="true"
                   style="padding: 7px 15px;"
                   data-status="<?php echo $statusData['name']; ?>"
                   data-client_id="<?php echo $client_data->client_id; ?>"
                   data-default-tab="<?php echo $statusData['default'] ? 'true' : 'false'; ?>"
                >
                    <?php echo $statusData['text']; ?> <span class="badge <?php echo $statusData['badge']; ?>">
                        <span class="status-count-no-filter"><?php echo $statusData['count']; ?></span>
                        <span class="status-count-with-filter"></span>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
