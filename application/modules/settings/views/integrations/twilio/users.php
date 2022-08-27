<?php
/** @var $users object */
?>
<div class="usergroup-dialog">
    <div class="users-and-groups-pane">
        <table class="users-and-groups-table">
            <tbody>

            <?php foreach ($users as $user) : ?>
                <tr rel="user_<?php echo $user->user->id ?>" class="user">
                    <td style="text-align: center;"><img src="assets/OpenVBX/icons/user-group-picker-person-icon.png"
                                                         width="24" height="21"/></td>
                    <td><?php echo $user->friendlyName ?></td>
                    <td><?php echo $user->user->user_email ?></td>
                    <td style="text-align: right; padding-right: 15px;"><a class="edit-link edit-user" href="">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>
