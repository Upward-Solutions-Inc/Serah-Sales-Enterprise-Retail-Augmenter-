@extends('layout.tenant')

@section('title', __('Payslip'))

@section('contents')

<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-3">
            <h4>Payslip</h4>
        </div>
    </div>

    <div class="row no-gutter">
        <div class="col-lg-2 mt-5">
            <div class="card text-center shadow pt-4">
                <h6 class="card-title">Employee Profile</h6>
                <div class="d-flex justify-content-center">
                    <img src="{{ auth()->user()->profile_picture ?? '/images/avatar.png' }}" 
                        alt="Profile Picture"
                        class="rounded-circle"
                        style="width: 80px; height: 80px; object-fit: cover;">
                </div>
                <div class="card-body">
                    <h6 class="mb-1">{{ auth()->user()->name }}</h6>
                    <p class="text-muted small">({{ auth()->user()->role->name ?? 'Employee' }})</p>
                </div>
                <div class="card-footer bg-transparent small text-muted">
                    Today: <p id="currentDateTime" style="display: inline;"></p>
                </div>
            </div>
        </div>

        <div class="col-lg-10 mt-2">
            <div class="datatable">
                <div class="my-2 d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <p class="text-muted mb-0">Showing 0 to 0 items of 0</p>
                    </div>
                </div>
                <div class="table-responsive custom-scrollbar table-view-responsive shadow pt-primary">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th track-by="0" class="datatable-th pt-0">
                                    <span class="font-size-default">Employee</span>
                                </th>
                                <th track-by="1" class="datatable-th pt-0">
                                    <span class="font-size-default">Date</span>
                                </th>
                                <th track-by="2" class="datatable-th pt-0">
                                    <span class="font-size-default">Morning In</span>
                                </th>
                                <th track-by="3" class="datatable-th pt-0">
                                    <span class="font-size-default">Morning Out</span>
                                </th>
                                <th track-by="4" class="datatable-th pt-0">
                                    <span class="font-size-default">Afternoon In</span>
                                </th>
                                <th track-by="5" class="datatable-th pt-0">
                                    <span class="font-size-default">Afternoon Out</span>
                                </th>
                                <th track-by="6" class="datatable-th pt-0 text-right">
                                    <span class="font-size-default">Late</span>
                                </th>
                                <th track-by="6" class="datatable-th pt-0 text-right">
                                    <span class="font-size-default">Overtime</span>
                                </th>
                                <th track-by="7" class="datatable-th pt-0 text-right">
                                    <span class="font-size-default">Total Work Hours</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                    <div class="no-data-found-wrapper text-center p-primary">
                        <img src="http://127.0.0.1:8000/images/no_data.svg" alt="" class="mb-primary">
                        <p class="mb-0 text-center">Nothing to show here</p>
                        <p class="mb-0 text-center text-secondary font-size-90">
                            Please add a new entity or manage the data table to see the content here
                        </p>
                        <p class="mb-0 text-center text-secondary font-size-90">Thank you</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        function updateDateTime() {
            let now = new Date();
            let options = { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric', 
                hour: 'numeric', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: true 
            };
            document.getElementById("currentDateTime").innerText = now.toLocaleString('en-US', options);
        }
        updateDateTime();
        setInterval(updateDateTime, 1000); 
    });
</script>
@endsection