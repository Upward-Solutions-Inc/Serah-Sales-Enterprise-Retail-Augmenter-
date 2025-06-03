<template>
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-3">
          <h4>Attendance</h4>
        </div>
      </div>
  
      <div class="row no-gutter">
        <!-- Profile Card -->
        <div class="col-12 col-md-4 col-lg-2 mt-5">
          <div class="card text-center shadow pt-4">
            <h6 class="card-title">Employee Profile</h6>
            <div class="d-flex justify-content-center">
              <img :src="profilePicture" alt="Profile Picture"
                class="rounded-circle"
                style="width: 80px; height: 80px; object-fit: cover;">
            </div>
            <div class="card-body">
              <h6 class="mb-1">{{ name }}</h6>
              <p class="text-muted small">({{ role }})</p>
            </div>
            <div class="card-footer bg-transparent small text-muted">
              Today:&nbsp;{{ currentDateTime }}
            </div>
          </div>
        </div>
  
        <!-- Attendance Table -->
        <div class="col-lg-10 mt-2">
          <div class="datatable">
            <div class="my-2 d-flex justify-content-between align-items-center flex-wrap">
              <p class="text-muted mb-2 mb-md-0">
                Showing {{ startItem }} to {{ endItem }} of {{ logs.length }} records
              </p>

              <div class="d-flex align-items-center">
                <input
                  ref="datePicker"
                  type="text"
                  placeholder="Select Date"
                  class="form-control mr-3"
                  v-model="searchDate"
                  style="max-width: 180px; min-width: 160px; max-height: 48px; min-height: 48px;"
                />

                <select
                  class="form-control"
                  v-model="searchShift"
                  style="max-width: 180px; min-width: 160px; max-height: 48px; min-height: 48px;"
                >
                  <option value="">All Shifts</option>
                  <option value="Morning">Morning</option>
                  <option value="Afternoon">Afternoon</option>
                  <option value="Night">Night</option>
                </select>

                <button class="btn btn-lg btn-outline-secondary mb-2 ml-2" @click="clearFilters">Clear</button>
              </div>
            </div>
  
            <div class="table-responsive custom-scrollbar table-view-responsive shadow pt-primary">
              <table class="table table-striped table-borderless text-center table-sm d-none d-md-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Shift</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Late</th>
                    <th>Overtime</th>
                    <th>Total Work Hours</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="log in paginatedLogs" :key="log.id">
                    <td>{{ formatDate(log.date) }}</td>
                    <td>{{ log.user?.first_name }} {{ log.user?.last_name }}</td>
                    <td>{{ log.shift }}</td>
                    <td>{{ formatTime(log.clock_in) }}</td>
                    <td>{{ formatTime(log.clock_out) }}</td>
                    <td>{{ log.late_minutes }} min</td>
                    <td>{{ log.overtime_minutes }} min</td>
                    <td>{{ formatWorkHours(log.total_work_hours) }}</td>
                  </tr>
                </tbody>
              </table>
  
              <!-- Mobile View -->
              <div class="d-md-none">
                <div v-if="paginatedLogs.length" v-for="log in paginatedLogs" :key="log.id" class="card mb-2 p-3 shadow-sm">
                  <div><strong>Date:</strong> {{ formatDate(log.date) }}</div>
                  <div><strong>Employee:</strong> {{ log.user?.first_name }} {{ log.user?.last_name }}</div>
                  <div><strong>Shift:</strong> {{ log.shift }}</div>
                  <div><strong>Clock In:</strong> {{ formatTime(log.clock_in) }}</div>
                  <div><strong>Clock Out:</strong> {{ formatTime(log.clock_out) }}</div>
                  <div><strong>Late:</strong> {{ log.late_minutes }} min</div>
                  <div><strong>Overtime:</strong> {{ log.overtime_minutes }} min</div>
                  <div><strong>Total Hours:</strong> {{ formatWorkHours(log.total_work_hours) }}</div>
                </div>
                <div v-else class="text-center py-4">
                  <img src="/images/no_data.svg" alt="no data" class="mb-2" />
                  <p class="mb-0">Nothing to show here</p>
                </div>
              </div>

              <div v-if="paginatedLogs.length === 0" class="no-data-found-wrapper text-center p-primary">
                  <img src="/images/no_data.svg" alt="" class="mb-primary">
                  <p class="mb-0 text-center">Nothing to show here</p>
                  <p class="mb-0 text-center text-secondary font-size-90">
                      Please add a new entity or manage the data table to see the content here
                  </p>
                  <p class="mb-0 text-center text-secondary font-size-90">Thank you</p>
              </div>
            </div>
  
            <!-- Pagination -->
            <nav class="mt-2">
              <ul class="pagination justify-content-end">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Prev</a>
                </li>
                <li
                  class="page-item"
                  v-for="page in visiblePages"
                  :key="page"
                  :class="{ active: currentPage === page }"
                >
                  <a class="page-link" href="#" @click.prevent="changePage(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import api, { Attendance } from '../api.js'
  import flatpickr from 'flatpickr'
  import 'flatpickr/dist/flatpickr.css'
  
  export default {
    name: 'Attendance',
    props: {
      name: String,
      role: String,
      profilePicture: String,
      userId: Number
    },
    data() {
      return {
        logs: [],
        currentPage: 1,
        perPage: 10,
        currentDateTime: '',
        searchDate: '',
        searchShift: '',
      }
    },
    mounted() {
      this.fetchLogs()
      this.updateTime()
      this.initFlatpickr()
      setInterval(this.updateTime, 1000)

      Echo.private(`attendance.${this.userId}`)
        .listen('.AttendanceLogUpdated', () => {
          this.fetchLogs();
        });
    },
    watch: {
      searchDate() { this.currentPage = 1 },
      searchShift() { this.currentPage = 1 }
    },
    computed: {
      filteredLogs() {
        return this.logs.filter(log => {
          const matchDate = this.searchDate ? log.date === this.searchDate : true;
          const matchShift = this.searchShift ? log.shift === this.searchShift : true;
          return matchDate && matchShift;
        });
      },
      paginatedLogs() {
        const start = (this.currentPage - 1) * this.perPage;
        return this.filteredLogs.slice(start, start + this.perPage);
      },
      startItem() {
        return this.filteredLogs.length ? (this.perPage * (this.currentPage - 1)) + 1 : 0
      },
      endItem() {
        return Math.min(this.startItem + this.paginatedLogs.length - 1, this.filteredLogs.length)
      },
      totalPages() {
        return Math.ceil(this.filteredLogs.length / this.perPage)
      },
      visiblePages() {
        const range = 2
        const start = Math.max(this.currentPage - range, 1)
        const end = Math.min(this.currentPage + range, this.totalPages)
        return Array.from({ length: end - start + 1 }, (_, i) => start + i)
      }
    },
    methods: {
      clearFilters() {
        this.searchDate = '';
        this.searchShift = '';
        this.currentPage = 1
        if (this.$refs.datePicker && this.$refs.datePicker._flatpickr) {
          this.$refs.datePicker._flatpickr.clear()
        }
      },
      fetchLogs() {
        api.get(Attendance.fetch).then(res => {
          this.logs = res.data
        }).catch(err => {
          console.error('Failed to fetch logs:', err)
        })
      },
      updateTime() {
        const now = new Date()
        this.currentDateTime = now.toLocaleString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric',
          hour: 'numeric',
          minute: '2-digit',
          second: '2-digit',
          hour12: true
        })
      },
      initFlatpickr() {
        flatpickr(this.$refs.datePicker, {
          dateFormat: 'Y-m-d',
          onChange: ([selected]) => {
            this.searchDate = selected ? selected.toISOString().split('T')[0] : ''
            this.currentPage = 1
          }
        })
      },
      changePage(page) {
        if (page >= 1 && page <= this.totalPages) this.currentPage = page
      },
      formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
          month: 'short', day: '2-digit', year: 'numeric'
        })
      },
      formatTime(datetime) {
        if (!datetime) return '-'
        const date = new Date(datetime)
        const hour = date.getHours()
        const minute = date.getMinutes().toString().padStart(2, '0')
        const ampm = hour >= 12 ? 'PM' : 'AM'
        const formattedHour = hour % 12 || 12
        return `${formattedHour}:${minute} ${ampm}`
      },
      formatWorkHours(hours) {
        const h = Math.floor(hours)
        const m = Math.round((hours - h) * 60)
        return `${h} h ${m} min`
      }
    }
  }
  </script>
  <style>
    .card div {
      font-size: 14px;
      margin-bottom: 3px;
    }
    .pagination .page-item .page-link {
      background-color: transparent;
      color: #007bff;
      border: 1px solid #007bff;
      border-radius: 50%;
    }

    .pagination .page-item.active .page-link,
    .pagination .page-item:not(.active) .page-link:hover,
    .pagination .page-item:not(.active) .page-link:focus {
      background-color: #007bff !important;
      color: white !important;
      border-color: #007bff !important;
      box-shadow: none !important;
    }
  </style> 