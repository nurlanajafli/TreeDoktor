<?php
/**
 * @var Equipment $eq
 */

use application\modules\equipment\models\Equipment;
use application\modules\equipment\models\EquipmentNote;

?>
    <style>
        #equipment_notes .comment-list .comment-item .comment-body .panel-body div:first-child {
            white-space: pre-wrap;
        }

        #equipment_notes .comment-list .comment-item .action-upload {
            position: absolute;
            z-index: 2;
            top: 0;
            left: 0;
            filter: alpha(opacity=0);
            -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
            opacity: 0;
            background-color: transparent;
            color: transparent;
            height: 100%;
        }

        #equipment_notes .comment-list .comment-item .comment-date {
            position: absolute;
            right: 0;
        }

        #equipment_notes .comment-list .comment-item .comment-id {
            position: absolute;
            left: 46px;
        }

        #equipment_notes .comment-list .comment-item .comment-id {
            position: absolute;
            left: 46px;
        }

        #equipment_notes .has-error .help-block {
            border-top: 1px solid #ddd;
            padding-left: 5px;
            padding-top: 5px;
        }
    </style>
    <section id="equipment_notes" class="vbox" diez-app="EquipmentProfileNotesApp"
             diez-src="equipment/components/profile-notes.js"
             data-equipment-id="<?php echo $eq->eq_id; ?>">
        <section class="scrollable">
            <header class="panel-heading row">
                <div class="col-sm-2 v-middle">
                    <button type="button" class="action-refresh btn btn-sm btn-default" title="Refresh"
                            style="margin-top: 18px;"><i
                                class="fa fa-refresh"></i></button>
                </div>
                <div class="col-sm-5 v-middle">
                    Notes for:
                    <select id="note_filter_for" class="input-sm form-control inline v-middle">
                        <option value="0">All</option>
                        <option value="eq_id">Equipment</option>
                        <option value="repair_id">Repairs</option>
                        <option value="service_report_id">Service Reports</option>
                    </select>
                </div>
                <div class="col-sm-5 v-middle">
                    By type:
                    <select id="note_filter_type" class="input-sm form-control inline v-middle">
                        <option value="0">All</option>
                        <?php foreach (EquipmentNote::TYPES as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </header>
            <div class="wrapper">
                <section class="comment-list block">
                    <!-- comment form -->
                    <article class="comment-item media comment-form">
                        <a class="pull-left thumb-sm avatar"><img src="<?php echo request()->user()->picture; ?>"
                                                                  class="img-circle"></a>
                        <form>
                            <section class="media-body panel panel-default">

                            <textarea name="note_description" class="form-control no-border" rows="3"
                                      placeholder="Input your note here"></textarea>
                                <span class="help-block" id="note_description-error"></span>
                                <input type="hidden" name="eq_id" value="<?php echo $eq->eq_id; ?>">
                                <footer class="panel-footer bg-light lter">
                                    <button class="btn btn-default pull-right btn-sm action-reply-cancel m-l-xs"
                                            style="display: inline-block;">Cancel
                                    </button>
                                    <button type="submit" class="btn btn-info pull-right btn-sm">Submit</button>
                                    <ul class="nav nav-pills nav-sm m-b-none">
                                        <li>
                                            <a href="#" class="btn-file">
                                                <i class="fa fa-paperclip text-muted"></i>
                                                <input type="file" name="file" class="action-upload">
                                            </a>
                                            <span class="help-inline"></span>
                                        </li>
                                    </ul>
                                    <ul class="upload-list nav nav-pills nav-sm m-b-none"></ul>
                                </footer>
                            </section>
                        </form>
                    </article>

                </section>
            </div>
        </section>
    </section>
    <script>
        var note_system_type = <?php echo EquipmentNote::TYPE_SYSTEM; ?>;
        //var PAGE_NUM_URI_SEGMENT = 3;
    </script>
<?php $this->load->view_hb('equipment_note_block'); ?>