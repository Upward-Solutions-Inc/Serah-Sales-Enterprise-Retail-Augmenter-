@extends('layout.tenant')

@section('title', __('Time Clock'))

@section('contents')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-3">
                <h4>Time Clock</h4>
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
                        <button id="clockInBtn" class="btn btn-success w-100">Clock In</button>
                        <button id="clockOutBtn" class="btn btn-danger w-100">Clock Out</button>
                        <p id="statusMessage" class="mt-3"></p>
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
                                        <span class="font-size-default">Shift</span>
                                    </th>
                                    <th track-by="2" class="datatable-th pt-0">
                                        <span class="font-size-default">Clock In</span>
                                    </th>
                                    <th track-by="3" class="datatable-th pt-0">
                                        <span class="font-size-default">Clock Out</span>
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
                                <!-- contents here tr -->
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

            document.getElementById("clockInBtn").addEventListener("click", function () {
                fetch("/timeclock/clock-in", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                        "Content-Type": "application/json",
                    },
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("statusMessage").innerText = data.message;
                })
                .catch(() => alert("Clock In Failed!"));
                    });

            document.getElementById("clockOutBtn").addEventListener("click", function () {
                fetch("/timeclock/clock-out", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                        "Content-Type": "application/json",
                    },
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById("statusMessage").innerText = data.message;
                })
                .catch(() => alert("Clock Out Failed!"));
            });
        });
    </script>
@endsection