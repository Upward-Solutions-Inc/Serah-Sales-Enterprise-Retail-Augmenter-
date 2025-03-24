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
        <form action="{{ route('dtr.store') }}" method="POST">
            @csrf

            <!-- Morning Shift -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Morning Shift Start</label>
                        <input type="text" class="form-control timepicker" name="morning_shift_start" 
                               value="{{ $dtrConfig->morning_shift_start ?? '08:00 AM' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Morning Shift End</label>
                        <input type="text" class="form-control timepicker" name="morning_shift_end" 
                               value="{{ $dtrConfig->morning_shift_end ?? '12:00 PM' }}">
                    </div>
                </div>
            </div>

            <!-- Afternoon Shift -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Afternoon Shift Start</label>
                        <input type="text" class="form-control timepicker" name="afternoon_shift_start" 
                               value="{{ $dtrConfig->afternoon_shift_start ?? '01:00 PM' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Afternoon Shift End</label>
                        <input type="text" class="form-control timepicker" name="afternoon_shift_end" 
                               value="{{ $dtrConfig->afternoon_shift_end ?? '05:00 PM' }}">
                    </div>
                </div>
            </div>

            <!-- Night Shift -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Night Shift Start</label>
                        <input type="text" class="form-control timepicker" name="night_shift_start" 
                               value="{{ $dtrConfig->night_shift_start ?? '05:00 PM' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Night Shift End</label>
                        <input type="text" class="form-control timepicker" name="night_shift_end" 
                               value="{{ $dtrConfig->night_shift_end ?? '03:00 AM' }}">
                    </div>
                </div>
            </div>

            <!-- Grace Period & Overtime -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Grace Period (Minutes)</label>
                        <input type="number" class="form-control minutepicker" name="grace_period" 
                               value="{{ $dtrConfig->grace_period ?? 5 }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Overtime Threshold (Minutes)</label>
                        <input type="number" class="form-control minutepicker" name="overtime" 
                               value="{{ $dtrConfig->overtime ?? 15 }}">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-3 text-right">
                <button type="button" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        @if(session()->has('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        flatpickr(".minutepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "i", // Only minutes
            time_24hr: true,
            defaultHour: 0, // Set hour to 0 to prevent hour selection
            defaultMinute: 5, // Default value
            minuteIncrement: 1, // Allows selecting minute increments
        });

        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            time_24hr: false
        });

    });
</script>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection