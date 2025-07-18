<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade _event" id="viewTimesheet">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('si_ts_edit_timesheet'); ?></h4>
            </div>

            <?php echo form_open('si_timesheet/save_timesheet', array('id' => 'calendar-event-form')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <?php if ($timesheet->staff_id != get_staff_user_id()) { ?>
                            <div class="alert alert-info">
                                <?php echo _l('si_ts_timesheet_created_by', '<a href="' . admin_url('profile/' . $timesheet->staff_id) . '" target="_blank">' . get_staff_full_name($timesheet->staff_id)) . '</a>'; ?>
                            </div>
                        <?php } ?>

                        <?php if (($timesheet->staff_id == get_staff_user_id() || is_admin()) && $editable) { ?>

                            <h4>
                                <a href="<?php echo admin_url('tasks/view/' . $timesheet->task_id); ?>" onclick="init_task_modal(<?php echo htmlspecialchars($timesheet->task_id); ?>); return false;">
                                    <?php echo htmlspecialchars($timesheet->name); ?>
                                </a>
                            </h4>

                            <p><?php echo htmlspecialchars($timesheet->subtext); ?></p>
                            <hr/>

                            <div class="clearfix mtop15">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php echo form_hidden('id', $timesheet->id); ?>
                                    </div>

                                    <div class="col-md-6">
                                        <?php echo render_datetime_input('start', 'task_log_time_start', _dt(date('Y-m-d H:i:s', $timesheet->start_time))); ?>
                                    </div>

                                    <div class="col-md-6">
                                        <?php echo render_datetime_input('end', 'task_log_time_end', (!is_null($timesheet->end_time) ? _dt(date('Y-m-d H:i:s', $timesheet->end_time)) : '')); ?>
                                        <label class="text-danger"><?php echo _l('si_ts_time_spend'); ?> : <a class="text-danger" id="si_total_hours"><?php echo seconds_to_time_format(!is_null($timesheet->end_time) ? ($timesheet->end_time - $timesheet->start_time) : (time() - $timesheet->start_time)); ?></a> <?php echo _l('si_ts_time_spend_hours'); ?></label>
                                    </div>

                                    <div class="col-md-12">
                                        <?php echo render_textarea('note', 'note', $timesheet->note, array('rows' => 5)); ?>
                                    </div>

                                    <div class="col-md-12 mtop15">
                                        <div class="form-group">
                                            <label for="tags" class="control-label"><?php echo _l('tags'); ?></label>
                                            <input type="text" id="tags" name="tags" class="tagsinput form-control" data-role="tagsinput" value="<?php echo htmlspecialchars($tags); ?>">
                                        </div>
                                    </div>

                                </div>
                            </div>

                        <?php } else { ?>

                            <h4>
                                <a href="<?php echo admin_url('tasks/view/' . $timesheet->task_id); ?>" onclick="init_task_modal(<?php echo htmlspecialchars($timesheet->task_id); ?>); return false;">
                                    <?php echo htmlspecialchars($timesheet->name); ?>
                                </a>
                            </h4>

                            <p><?php echo htmlspecialchars($timesheet->subtext); ?></p>
                            <a href="<?php echo admin_url('profile/' . $timesheet->staff_id); ?>">
                                <?php echo staff_profile_image($timesheet->staff_id, array('staff-profile-xs-image')); ?>
                                <?php echo get_staff_full_name($timesheet->staff_id); ?>
                            </a>
                            <hr />

                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="bold"><?php echo _l('note'); ?></h5>
                                    <p><?php echo htmlspecialchars($timesheet->note); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="bold"><?php echo _l('utility_calendar_new_event_start_date'); ?></h5>
                                    <p><?php echo _dt(date('Y-m-d H:i:s', $timesheet->start_time)); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (is_date(date('Y-m-d H:i:s', $timesheet->end_time))) { ?>
                                        <h5 class="bold"><?php echo _l('utility_calendar_new_event_end_date'); ?></h5>
                                        <p><?php echo _dt(date('Y-m-d H:i:s', $timesheet->end_time)); ?></p>
                                    <?php } ?>
                                </div>
                            </div>

                        <?php } ?>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <?php if (($timesheet->staff_id == get_staff_user_id() || is_admin()) && $editable) { ?>
                        <button type="button" class="btn btn-danger" onclick="delete_timesheet(<?php echo htmlspecialchars($timesheet->id); ?>); return false"><?php echo _l('delete_event'); ?></button>
                        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                    <?php } ?>
                </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<link href="<?php echo base_url('assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css'); ?>" rel="stylesheet">
<script src="<?php echo base_url('assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js'); ?>"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
window.addEventListener('load', function() {
    jQuery(function($) {
        // Initialize tagsinput
        if ($('#tags').length) {
            $('#tags').tagsinput({
                trimValue: true,
                tagClass: function(item) { return 'label label-info'; }
            });
        }

        // Calculate time spent
        function calculateTimeSpent() {
            var start = $('input[name="start"]').val();
            var end = $('input[name="end"]').val();

            if (start && end) {
                var startDate = moment(start, 'MM/DD/YYYY h:mm A');
                var endDate = moment(end, 'MM/DD/YYYY h:mm A');

                if (startDate.isValid() && endDate.isValid() && endDate > startDate) {
                    var diffMs = endDate.diff(startDate);
                    var duration = moment.duration(diffMs);

                    var totalMinutes = duration.asMinutes();
                    var hours = Math.floor(totalMinutes / 60);
                    var minutes = Math.floor(totalMinutes % 60);

                    var hoursDisplay = hours < 10 ? '0' + hours : hours;
                    var minutesDisplay = minutes < 10 ? '0' + minutes : minutes;

                    $('#si_total_hours').text(hoursDisplay + ':' + minutesDisplay);
                } else {
                    $('#si_total_hours').text('00:00');
                }
            }
        }

        $('input[name="start"], input[name="end"]').on('change', calculateTimeSpent);
    });
});
</script>

<style>
.bootstrap-tagsinput span.label-info {
    background-color: #007bff !important;
    color: #fff !important;
}
</style>
