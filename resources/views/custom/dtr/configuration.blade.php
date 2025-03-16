@extends('layout.tenant')

@section('title', __('Schedule'))

@section('contents')
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-3">
            <h4>Schedule</h4>
        </div>
    </div>

    <div class="container mt-4">
        <h5>DTR Schedule</h5>

        <!-- Tab Navigation -->
        <div class="row">
            <ul class="nav nav-tabs custom-tabs" id="dtrTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                        Regular Shift
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="specific-tab" data-toggle="tab" href="#specific" role="tab">
                        Night Shift
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content mt-3">
            <!-- General Tab -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <form>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Grace Period (Morning Shift)</label>
                                <input type="text" class="form-control timepicker" name="grace_period_morning" 
                                       value="{{ $dtrConfig->grace_period_morning ?? '08:05 AM' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Morning Shift Start</label>
                                <input type="text" class="form-control timepicker" name="morning_shift_start" 
                                       value="{{ $dtrConfig->morning_shift_start ?? '08:00 AM' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Morning Shift End</label>
                                <input type="text" class="form-control timepicker" name="morning_shift_end" 
                                       value="{{ $dtrConfig->morning_shift_end ?? '12:00 PM' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Grace Period (Afternoon Shift)</label>
                                <input type="text" class="form-control timepicker" name="grace_period_afternoon" 
                                       value="{{ $dtrConfig->grace_period_afternoon ?? '01:05 PM' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Afternoon Shift Start</label>
                                <input type="text" class="form-control timepicker" name="afternoon_shift_start" 
                                       value="{{ $dtrConfig->afternoon_shift_start ?? '01:00 PM' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Afternoon Shift Out</label>
                                <input type="text" class="form-control timepicker" name="afternoon_shift_out" 
                                       value="{{ $dtrConfig->afternoon_shift_out ?? '05:00 PM' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Overtime Threshold</label>
                                <input type="text" class="form-control timepicker" name="overtime_threshold" 
                                       value="{{ $dtrConfig->overtime_threshold ?? '05:30 PM' }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Specific Tab -->
            <div class="tab-pane fade" id="specific" role="tabpanel">
                <form action="">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Grace Period (Night Shift)</label>
                                <input type="text" class="form-control timepicker" name="grace_period_night" 
                                       value="{{ $dtrConfig->grace_period_night ?? '05:05 PM' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Night Shift Start</label>
                                <input type="text" class="form-control timepicker" name="night_shift_start" 
                                       value="{{ $dtrConfig->night_shift_start ?? '05:00 PM' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Night Shift End</label>
                                <input type="text" class="form-control timepicker" name="night_shift_end" 
                                       value="{{ $dtrConfig->night_shift_end ?? '03:00 AM' }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="mt-3 text-right">
                <button type="button" class="btn btn-secondary">Cancel</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K", // 12-hour format with AM/PM
            time_24hr: false
        });
    });
</script>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection