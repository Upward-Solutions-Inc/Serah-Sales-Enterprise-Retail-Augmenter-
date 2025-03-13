@extends('layout.tenant')

@section('title', __('Clock In/Out'))

@section('contents') {{-- Must match @yield('contents') in tenant layout --}}
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-3">
                <h4>Clock In/Out</h4>
            </div>
            <div class="col-sm-9 d-flex justify-content-end">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#dtrConfigModal">
                    Dtr Configuration
                </button>
            </div>
        </div>

        <div class="row no-gutter">
            <div class="col-lg-3 mt-5">
                <div class="card text-center shadow-sm pt-4">
                    <h6 class="card-title">Employee Profile</h6>
                    <div class="d-flex justify-content-center">
                        <img src="{{ auth()->user()->profile_picture ?? 'https://static.vecteezy.com/system/resources/thumbnails/024/983/914/small_2x/simple-user-default-icon-free-png.png' }}" 
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
                        Today: <p id="currentDateTime"></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 mt-2">
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

    <!-- Modal -->
    <div class="modal fade" id="dtrConfigModal" tabindex="-1" aria-labelledby="dtrConfigModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dtrConfigModalLabel">Dtr Configuration</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Tab Navigation -->
                    <div class="row">
                        <ul class="nav nav-tabs" id="dtrTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                                    General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="specific-tab" data-toggle="tab" href="#specific" role="tab">
                                    Specific
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
                                            <input type="number" class="form-control" placeholder="Enter minutes">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Morning Shift In</label>
                                            <input type="time" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Morning Shift Out</label>
                                            <input type="time" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Grace Period (Afternoon Shift)</label>
                                            <input type="number" class="form-control" placeholder="Enter minutes">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Afternoon Shift In</label>
                                            <input type="time" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Afternoon Shift Out</label>
                                            <input type="time" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Surplus Time Considered As Overtime (Default 30 min)</label>
                                            <input type="number" class="form-control" value="30">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Specific Tab -->
                        <div class="tab-pane fade" id="specific" role="tabpanel">
                            <p>Specific configuration settings go here...</p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
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
                    hour12: true 
                };
                document.getElementById("currentDateTime").innerText = now.toLocaleString('en-US', options);
            }
            updateDateTime();
            setInterval(updateDateTime, 1000); 

            document.getElementById("clockInBtn").addEventListener("click", function () {
                fetch("/dtr/clock-in", {
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
                fetch("/dtr/clock-out", {
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