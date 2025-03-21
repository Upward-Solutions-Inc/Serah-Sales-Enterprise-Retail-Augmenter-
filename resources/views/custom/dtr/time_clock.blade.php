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
                        <button id="clockOutBtn" class="btn btn-danger w-100" style="display: none;">Clock Out</button>
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
                        <table class="table table-striped table-borderless text-center">
                            <thead>
                                <tr>
                                    <th class="datatable-th pt-0">
                                        <span class="font-size-default">Date</span>
                                    </th>
                                    <th class="datatable-th pt-0">
                                        <span class="font-size-default">Employee</span>
                                    </th>
                                    <th class="datatable-th pt-0">
                                        <span class="font-size-default">Shift</span>
                                    </th>
                                    <th class="datatable-th pt-0">
                                        <span class="font-size-default">Clock In</span>
                                    </th>
                                    <th class="datatable-th pt-0">
                                        <span class="font-size-default">Clock Out</span>
                                    </th>
                                    <th class="datatable-th pt-0">
                                        <span class="font-size-default">Late</span>
                                    </th>
                                    <th class="datatable-th pt-0">
                                        <span class="font-size-default">Overtime</span>
                                    </th>
                                    <th class="datatable-th pt-0">
                                        <span class="font-size-default">Total Work Hours</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody">
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="no-data-found-wrapper text-center p-primary">
                                            <img src="http://127.0.0.1:8000/images/no_data.svg" alt="" class="mb-primary">
                                            <p class="mb-0 text-center">Nothing to show here</p>
                                            <p class="mb-0 text-center text-secondary font-size-90">
                                                Please add a new entity or manage the data table to see the content here
                                            </p>
                                            <p class="mb-0 text-center text-secondary font-size-90">Thank you</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-center">
                        
                        <!-- Items per page dropdown -->
                        <div class="d-flex align-items-center">
                            <div class="dropdown keep-inside-clicks-open">
                                <button type="button" id="show-pagination-attendance-request-table" data-toggle="dropdown" 
                                    class="btn btn-filter d-flex align-items-center" aria-expanded="true">
                                    10
                                    <img src="https://payday.gainhq.com/images/chevron-down.svg" alt="" 
                                        style="height: 16px; width: 16px; margin-left: 5px;">
                                </button>
                                <div class="dropdown-menu dropdown-menu-attendance-request-table">
                                    <a href="#" class="dropdown-item active disabled">10</a>
                                    <a href="#" class="dropdown-item">20</a>
                                    <a href="#" class="dropdown-item">50</a>
                                    <a href="#" class="dropdown-item">100</a>
                                </div>
                            </div>
                            <label class="text-muted ml-2 mb-0">Items showing per page</label>
                        </div>

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination mb-0 justify-content-center justify-content-md-end">
                                <li class="page-item disabled">
                                    <a href="#" aria-label="Previous" class="page-link">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-left">
                                            <line x1="19" y1="12" x2="5" y2="12"></line>
                                            <polyline points="12 19 5 12 12 5"></polyline>
                                        </svg>
                                    </a>
                                </li>
                                <li class="page-item active"><a href="#" class="page-link">1</a></li>
                                <li class="page-item"><a href="#" class="page-link">2</a></li>
                                <li class="page-item"><a href="#" class="page-link">3</a></li>
                                <li class="page-item"><a href="#" class="page-link">4</a></li>
                                <li class="page-item"><a href="#" class="page-link">5</a></li>
                                <li class="page-item">
                                    <a href="#" aria-label="Next" class="page-link">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

            </div>
        </div>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tableBody = document.getElementById("logsTableBody");
        const clockInBtn = document.getElementById("clockInBtn");
        const clockOutBtn = document.getElementById("clockOutBtn");
        const statusMessage = document.getElementById("statusMessage");


        updateDateTime();
        setInterval(updateDateTime, 1000);
        checkClockStatus();
        reloadTable();


        // Real-time Clock Display
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


        // Check Clock Status
        function checkClockStatus() {
            clockInBtn.style.display = "none";
            clockOutBtn.style.display = "none";

            fetch("/timeclock/status")
                .then(response => response.json())
                .then(data => {
                    if (data.clocked_in) {
                        clockInBtn.style.display = "none";
                        clockOutBtn.style.display = "block";
                    } else {
                        clockInBtn.style.display = "block";
                        clockOutBtn.style.display = "none";
                    }
                })
                .catch(error => console.error("Failed to check clock status:", error));
        }


        // Function to show loader & disable button
        function setLoading(button, loading) {
            if (loading) {
                button.disabled = true;
                button.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Processing...`;
            } else {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText;
            }
        }


        // Function to Fetch & Render Table Data
        function reloadTable() {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center sr-only"></td></tr>';

            fetch("/timeclock/logs")
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = data.map(log => `
                        <tr>
                            <td>${formatDate(log.date)}</td>
                            <td>${log.user.first_name} ${log.user.last_name}</td>
                            <td>${log.shift}</td>
                            <td>${formatTime(log.clock_in)}</td>
                            <td>${formatTime(log.clock_out)}</td>
                            <td>${log.late_minutes} min</td>
                            <td>${log.overtime_minutes} min</td>
                            <td>${formatWorkHours(log.total_work_hours)}</td>
                        </tr>
                    `).join(""); 
                })
                .catch(error => {
                    console.error("❌ Failed to reload table:", error);
                    tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">⚠ Error loading data</td></tr>`;
                });
        }


        // Clock-In Event
        clockInBtn.dataset.originalText = clockInBtn.innerHTML;
        clockInBtn.addEventListener("click", function () {
            setLoading(clockInBtn, true);
            fetch("/timeclock/clock-in", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "Content-Type": "application/json",
                },
            })
            .then(response => response.json())
            .then(data => {
                setLoading(clockInBtn, false);
                checkClockStatus();
                reloadTable();
                Swal.fire({
                    icon: data.status === "success" ? "success" : "error",
                    title: data.status === "success" ? "Clocked In!" : "Error",
                    text: data.message,
                    timer: 2500,
                    showConfirmButton: false
                });
            })
            .catch(error => {
                alert("Clock In Failed!");
                setLoading(clockInBtn, false);
                console.error(error);
                Swal.fire({
                    icon: "error",
                    title: "Clock In Failed",
                    text: "Something went wrong!",
                });
            });
        });


        // Clock-Out Event
        clockOutBtn.dataset.originalText = clockOutBtn.innerHTML;
        clockOutBtn.addEventListener("click", function () {
            setLoading(clockOutBtn, true);
            fetch("/timeclock/clock-out", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "Content-Type": "application/json",
                },
            })
            .then(response => response.json())
            .then(data => {
                setLoading(clockOutBtn, false);
                checkClockStatus();
                reloadTable();
                Swal.fire({
                    icon: data.status === "success" ? "success" : "error",
                    title: data.status === "success" ? "Clocked Out!" : "Error",
                    text: data.message,
                    timer: 2500,
                    showConfirmButton: false
                });
            })
            .catch(error => {
                alert("Clock Out Failed!");
                setLoading(clockOutBtn, false);
                console.error(error);
                Swal.fire({
                    icon: "error",
                    title: "Clock Out Failed",
                    text: "Something went wrong!",
                });
            });
        });


        // Function to Format Data
        function formatTime(time) {
            if (!time || time === "00:00:00") return "-";
            let [hour, minute] = time.split(":"); // Extract HH and MM
            let ampm = hour >= 12 ? "PM" : "AM";
            hour = hour % 12 || 12; // Convert 24-hour format to 12-hour
            return `${hour}:${minute} ${ampm}`;
        }

        function formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        }

        function formatWorkHours(hours) {
            let h = Math.floor(hours);
            let m = Math.round((hours - h) * 60);
            return `${h} h ${m} min`;
        }


        // ✅ WebSocket Listener for Real-time Updates
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: 'local', // This should match your PUSHER_APP_KEY
            wsHost: window.location.hostname,
            wsPort: 6001, // Port where WebSockets is running
            forceTLS: false,
            disableStats: true,
        });

        Echo.channel('dtr-logs')
        .listen('.DtrLogUpdated', (data) => {
            console.log("📡 Received Broadcast:", data);
            reloadTable(); // Update UI on broadcast event
        });

    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.2/echo.iife.js"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>

@endsection