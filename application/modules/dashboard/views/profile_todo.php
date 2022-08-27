<!-- Create New Task -->
<script>
    $(document).mouseup(function (e) {
        var container = $(".perfomance-block");
        if (container.has(e.target).length === 0) {
            $(container).find('.tab-pane.active').removeClass('active');
            $(container).find('li.active').removeClass('active')
        }
    });
</script>
<aside class="col-md-4 b-l">
    <section class="vbox panel panel-default m-n">
        <?php if ((isset($workerTrigger) && $workerTrigger) || isset($estimatorTrigger) && $estimatorTrigger) : ?>
            <h4 class="font-thin padder panel-heading m-n"><strong>Performance
                    Goals <?php echo $this->session->userdata('firstname'); ?> <?php echo $this->session->userdata('lastname'); ?></strong>
            </h4>
            <div class="perfomance-block">
                <header class="panel-heading bg-light">
                    <ul class="nav nav-tabs nav-justified">
                        <?php if (isset($estimatorTrigger) && $estimatorTrigger) : ?>
                            <li class=""><a class="perfomance-tab" href="#est_perfomance" data-toggle="tab">Estimator Goals</a></li>
                            <li class=""><a class="perfomance-tab" href="#comp_perfomance" data-toggle="tab">Company Goals</a></li>
                        <?php endif; ?>
                        <?php if (isset($workerTrigger) && $workerTrigger) : ?>
                            <li class=""><a class="perfomance-tab" href="#worker_mhrs" data-toggle="tab">Worker Goals</a></li>
                            <li class=""><a class="perfomance-tab" href="#company_mhrs" data-toggle="tab">Company Worker Goals</a></li>
                        <?php endif; ?>
                    </ul>
                </header>
                <div class="tab-content">
                    <?php if (isset($estimatorTrigger) && $estimatorTrigger) : ?>
                        <div class="tab-pane"
                             id="est_perfomance"><?php $this->load->view('motivation_perfomance/estimator_confirmed_perfomance'); ?></div>
                        <div class="tab-pane"
                             id="comp_perfomance"><?php $this->load->view('motivation_perfomance/company_confirmed_perfomance'); ?></div>
                    <?php endif; ?>
                    <?php if (isset($workerTrigger) && $workerTrigger) : ?>
                        <div class="tab-pane"
                             id="worker_mhrs"><?php $this->load->view('motivation_perfomance/worker_mhrs_perfomance'); ?></div>
                        <div class="tab-pane"
                             id="company_mhrs"><?php $this->load->view('motivation_perfomance/company_mhrs_perfomance'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <!--Header-->
        <h4 class="font-thin padder panel-heading m-n">To-Do List
            <a class="btn btn-success btn-xs pull-right addTask" type="button" href="#">
                <i class="fa fa-plus-square"></i>
            </a>
        </h4>

        <!-- /Header -->
        <div class="scrollable todo-s">
            <!-- Add new task accordion-->
            <div id="newTask" class="collapse border-bottom filled_dark_grey">
                <!-- New Task Form -->
                <form id="newTaskForm" class="panel-default form-horizontal" todo-action="add">
                    <div id="borderless_form" class="filled_white">
                        <?php $options = array(
                            'id' => 'task_description',
                            'name' => 'task_description',
                            'label' => 'Task Description',
                            'rows' => '6',
                            'class' => 'input-block-level',
                            'placeholder' => 'Do something?!');
                        ?>
                        <?php echo form_textarea($options) ?>
                    </div>
                    <!-- New Taks Submit -->
                    <header class="panel-heading font-bold form-horizontal">
                        <button class="btn btn-success btn-xs pull-right" type="submit"
                                style="margin-top: 5px;margin-left: 20px;">
                            &nbsp;&nbsp;&nbsp;<i class="fa fa-check"></i>&nbsp;&nbsp;&nbsp;
                        </button>
                        <div class="checkbox pull-right">
                            <label class="checkbox-custom pull-right">
                                <input type="checkbox" name="task_urgency" id="task_urgency" value="2">
                                <i class="fa fa-fw fa-square-o"></i>
                                Urgent
                            </label>
                        </div>
                        <div class="clear"></div>
                    </header>
                    <!-- /New Taks Submit -->
                </form>
            </div>
            <!-- /Add new task accordion-->
            <!-- /Create New Task -->
            <!-- Active Task List -->
            <ul id="taskList" class="list-group">
                <?php if ($todo_list): ?>
                    <?= loop_view('dashboard/partials/todo_item', $todo_list, 'todo'); ?>
                <?php endif; ?>
            </ul>
            <!-- /Active Task List ends -->
            <!-- Completed Task List -->
            <ul id="taskCompleteList" class="list-group">
                <?php if ($completed_list): ?>
                    <?= loop_view('dashboard/partials/todo_completed_item', $completed_list, 'todo'); ?>
                <?php endif; ?>
            </ul>
            <!-- /Completed Task List ends -->
        </div>
    </section>
    </section>
</aside>
<script>
    var baseUrl = "<?= base_url() ?>";
    $(document).on('click','.addTask',function(event){
        event.preventDefault();
        $('#newTask').slideToggle();
        $('#newTask .task_description').focus();
    });
    $(document).ready(function () {
        $(document).on('submit','.todo-s form', function (event) {
            event.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);
            $.ajax({
                type: "POST",
                url: baseUrl + 'dashboard/todo/ajax_' + $form.attr('todo-action'),
                data: formData,
                mimeType: "multipart/form-data",
                contentType: false,
                dataType: 'json',
                cache: false,
                processData: false,
                beforeSend: function (jqXHR) {
                    jqXHR.action = $form.attr('todo-action');
                }
            }).done(function (data, status, jqXHR) {
                if (data.status == 'error') {
                    errorMessage(data.error)
                    return false;
                }
                if (data.status == 'ok') {
                    switch (jqXHR.action) {
                        case "add":
                            $(data.html).prependTo('#taskList');
                            $('#taskItem-' + data.id + ' input[type=checkbox][name=task_urgency]').checkbox();
                            $('#newTaskForm #task_description').val("");
                            if($('#newTaskForm input[type=checkbox]')[0].checked) {
                                $('#newTaskForm input[type=checkbox]').checkbox('toggle');
                            }
                            $('#newTask').slideToggle();
                            break;
                        case "edit":
                        case "assign":
                            if($('#taskItem-' + data.id).length){
                                $('#taskItem-' + data.id).replaceWith(data.html)
                                $('#taskItem-' + data.id + ' input[type=checkbox][name=task_urgency]').checkbox();
                            }
                            break;
                    }

                    return false;
                }
            });
            event.preventDefault();
        });

        $(document).on('click', '.actionTask', function (event) {
            event.preventDefault();
            var data = $(this).data();
            var action = $(this).attr('todo-action');
            $.ajax({
                type: "POST",
                url: baseUrl + 'dashboard/todo/ajax_' + action,
                data: data,
                dataType: 'json',
                cache: false,
                beforeSend: function (jqXHR) {
                    jqXHR.action = action;
                }
            }).done(
                function (data, status, jqXHR) {
                    if (data.status == 'error') {
                        errorMessage(data.error)
                        return false;
                    }
                    if (data.status == 'ok') {
                        switch (jqXHR.action) {
                            case "complete":
                                complete(data.id, data.html, data.time);
                                break;
                            case "revert":
                                complete(data.id, data.html, data.time, true)
                                break;
                            case "delete":
                                if($('#taskItem-' + data.id).length){
                                    $('#taskItem-' + data.id).slideUp('slow').remove()
                                } else if($('#taskCompleteItem-' + data.id)){
                                    $('#taskCompleteItem-' + data.id).slideUp('slow').remove()
                                }
                                break;
                        }
                        return false;
                    }
                }
            );
            event.preventDefault();
        });
    });

    function complete(id, html, time, rev = false) {
        var oldTask, lessEl, newTask, newOffset, oldOffset, temp;
        if (rev) {
            oldTask = $('#taskCompleteItem-' + id);
            lessEl = findLessTime('taskList', time);
            if (lessEl == false) {
                $(html).appendTo('#taskList');
            } else {
                $(lessEl).before(html);
            }
            newTask = $('#taskItem-' + id);
            $('#taskItem-' + id + ' input[type=checkbox][name=task_urgency]').checkbox();
        } else {
            oldTask = $('#taskItem-' + id);
            lessEl = findLessTime('taskCompleteList', time);
            if (lessEl == false) {
                $(html).appendTo('#taskCompleteList');
            } else {
                $(lessEl).before(html);
            }
            newTask = $('#taskCompleteItem-' + id);
        }
        newOffset = newTask.offset();
        oldOffset = oldTask.offset();
        temp = newTask.clone().appendTo('body');
        temp
            .css('position', 'absolute')
            .css('left', oldOffset.left)
            .css('top', oldOffset.top)
            .css('zIndex', 11000);
        newTask.hide();
        oldTask.hide();
        temp.animate({'top': newOffset.top, 'left': newOffset.left}, 'slow', function () {
            newTask.show();
            oldTask.remove();
            temp.remove();
        });
    }

    function findLessTime(list, time) {
        var li = false;
        $('#' + list + ' li').each(function (idx, el) {
            if ($(el).data('time') <= time) {
                li = el;
                return false;
            }
            return true;
        });
        return li;
    }
</script>
