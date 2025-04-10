<template>
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-3">
          <h4>Time Clock</h4>
        </div>
      </div>
  
      <div class="row no-gutter">
        <div class="col-12 col-md-4 col-lg-3 mt-5">
          <div class="card text-center shadow pt-4">
            <h6 class="card-title">Employee Profile</h6>
            <div class="d-flex justify-content-center">
              <img
                :src="user?.profile || '/images/avatar.png'"
                class="rounded-circle"
                style="width: 80px; height: 80px; object-fit: cover;"
              />
            </div>
            <div class="card-body">
              <h6 class="mb-1">{{ user?.name }}</h6>
              <p class="text-muted small">({{ user?.role || 'Employee' }})</p>
              <button :class="btnClass" class="btn w-100" @click="handleClock">
                <span>{{ btnText }}</span>
                <span class="spinner-border spinner-border-sm ml-2" v-if="loading"></span>
              </button>
            </div>
            <div class="card-footer bg-transparent small text-muted">
              Today: <span>{{ currentTime }}</span>
            </div>
          </div>
        </div>
  
        <div class="col-lg-9 mt-2">
          <!-- Logs Table -->
          <div class="datatable">
            <div class="my-2 d-flex justify-content-between">
              <p class="text-muted mb-0">Showing {{ logs.length }} records</p>
            </div>
  
            <div class="table-responsive custom-scrollbar table-view-responsive shadow pt-primary">
              <table class="table table-striped table-borderless text-center">
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
                  <tr v-for="log in logs" :key="log.id">
                    <td>{{ formatDate(log.date) }}</td>
                    <td>{{ log.user.first_name }} {{ log.user.last_name }}</td>
                    <td>{{ log.shift }}</td>
                    <td>{{ formatTime(log.clock_in) }}</td>
                    <td>{{ formatTime(log.clock_out) }}</td>
                    <td>{{ log.late_minutes }} min</td>
                    <td>{{ log.overtime_minutes }} min</td>
                    <td>{{ formatWorkHours(log.total_work_hours) }}</td>
                  </tr>
                  <tr v-if="logs.length === 0">
                    <td colspan="8">
                      <img src="/images/no_data.svg" alt="" class="mb-primary">
                      <p class="mb-0">Nothing to show here</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import api, { timeClock } from '../api.js'
  import Swal from 'sweetalert2'
  
  export default {
    name: 'TimeClock',
    data() {
      return {
        user: null,
        logs: [],
        currentTime: '',
        isClockedIn: false,
        loading: false
      }
    },
    computed: {
      btnText() {
        return this.loading ? 'Processing...' : (this.isClockedIn ? 'Clock Out' : 'Clock In')
      },
      btnClass() {
        return this.isClockedIn ? 'btn-danger' : 'btn-success'
      }
    },
    methods: {
      fetchStatus() {
        api.get(timeClock.status).then(res => {
          this.isClockedIn = res.data.clocked_in
          this.user = res.data.user
        })
      },
      fetchLogs() {
        api.get(timeClock.logs).then(res => {
          this.logs = res.data
        })
      },
      handleClock() {
        this.loading = true
        const url = this.isClockedIn ? timeClock.clockOut : timeClock.clockIn
        api.post(url).then(res => {
          Swal.fire({
            icon: res.data.status === 'success' ? 'success' : 'error',
            title: res.data.message,
            timer: 2500,
            showConfirmButton: false
          })
          this.fetchStatus()
          this.fetchLogs()
        }).catch(() => {
          Swal.fire({ icon: 'error', title: 'Clock Action Failed' })
        }).finally(() => {
          this.loading = false
        })
      },
      startClock() {
        setInterval(() => {
          this.currentTime = new Date().toLocaleString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true
          })
        }, 1000)
      },
      formatTime(time) {
        if (!time || time === '00:00:00') return '-'
        let [hour, minute] = time.split(':')
        let ampm = hour >= 12 ? 'PM' : 'AM'
        hour = hour % 12 || 12
        return `${hour}:${minute} ${ampm}`
      },
      formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
      },
      formatWorkHours(hours) {
        let h = Math.floor(hours)
        let m = Math.round((hours - h) * 60)
        return `${h} h ${m} min`
      }
    },
    mounted() {
      this.fetchStatus()
      this.fetchLogs()
      this.startClock()
    }
  }
  </script>
  
  <style scoped>
  .table-responsive {
    margin-top: 20px;
  }
  </style>  