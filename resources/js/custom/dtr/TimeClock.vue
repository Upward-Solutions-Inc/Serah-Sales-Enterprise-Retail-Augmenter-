<template>
    <div class="content-wrapper">
      <div class="row">
        <div class="col-sm-3">
          <h4>Time Clock</h4>
        </div>
      </div>
  
      <div class="row no-gutter">

        <!-- Face Capture -->
        <div v-if="showFacePopup" class="face-popup">  
          <button class="close-btn" @click="closeFacePopup">&times;</button>
          <div class="video-wrapper">
            <video id="video" autoplay muted playsinline></video>
            <div class="face-overlay">
              <Loader v-if="cameraInitializing" class="face-loader" />
              <p class="hint">Align your face</p></div>
          </div>
          <p class="instruction">Please align your face inside the oval.</p>
        </div>

        <div class="col-12 col-md-4 col-lg-3 mt-5">
          <div class="card text-center shadow mt-4 p-3">
            <h6 class="card-title">QR Scanner</h6>
            <div id="qr-scanner" style="width: 100%;"></div>
            <p class="text-muted small mt-2">Scan to Clock In/Out</p>
            <div class="card-footer bg-transparent small text-muted">
              Today:&nbsp;{{ currentDateTime }}
            </div>
          </div>
        </div>
  
        <div class="col-lg-9 mt-2">
          <div class="datatable">

            <div class="my-2 d-flex justify-content-between">
              <p class="text-muted mb-0">
                Showing {{ startItem }} to {{ endItem }} of {{ logs.length }} records
              </p>

              <div class="d-flex align-items-center">
                <input v-model="searchName" type="text" placeholder="Search name" class="form-control mr-2" style="max-width: 200px;" />

                <input ref="datePicker" class="form-control mr-2" placeholder="Select date range" style="max-width: 200px;" />

                <select v-model="selectedShift" class="form-control mr-2" style="max-width: 160px;">
                  <option value="">All Shifts</option>
                  <option>Morning</option>
                  <option>Afternoon</option>
                  <option>Night</option>
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
                    <td>{{ log.user.first_name }} {{ log.user.last_name }}</td>
                    <td>{{ log.shift }}</td>
                    <td>{{ formatTime(log.clock_in) }}</td>
                    <td>{{ formatTime(log.clock_out) }}</td>
                    <td>{{ log.late_minutes }} min</td>
                    <td>{{ log.overtime_minutes }} min</td>
                    <td>{{ formatWorkHours(log.total_work_hours) }}</td>
                  </tr>
                </tbody>
              </table>

              <!-- Mobile Card View -->
              <div class="d-md-none">
                <div v-if="paginatedLogs.length" v-for="log in paginatedLogs" :key="log.id" class="card mb-2 p-3 shadow-sm">
                  <div><strong>Date:</strong> {{ formatDate(log.date) }}</div>
                  <div><strong>Employee:</strong> {{ log.user.first_name }} {{ log.user.last_name }}</div>
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
  import Loader from '../components/Loader.vue'
  import api, { TimeClock } from '../api.js'
  import axios from 'axios'
  import { Html5QrcodeScanner } from "html5-qrcode"
  import * as faceapi from 'face-api.js'
  import Swal from 'sweetalert2'
  import flatpickr from 'flatpickr'
  import 'flatpickr/dist/flatpickr.css'
  
  export default {
    name: 'TimeClock',
    components: { Loader },
    data() {
      return {
        selectedDateRange: null,
        selectedShift: '',
        searchName: '',
        currentDateTime: '',
        cameraInitializing: false,
        captureTriggered: false,
        showFacePopup: false,
        user: null,
        scannedUserId: null,
        logs: [],
        isClockedIn: false,
        loading: false,
        currentPage: 1,
        perPage: 10
      }
    },
    computed: {
      btnText() {
        return this.loading ? 'Processing...' : (this.isClockedIn ? 'Clock Out' : 'Clock In')
      },
      btnClass() {
        return this.isClockedIn ? 'btn-danger' : 'btn-success'
      },
      startItem() {
        return this.logs.length === 0 ? 0 : (this.perPage * (this.currentPage - 1)) + 1;
      },
      endItem() {
        return Math.min(this.startItem + this.paginatedLogs.length - 1, this.logs.length);
      },
      totalPages() {
        return Math.ceil(this.filteredLogs.length / this.perPage);
      },
      visiblePages() {
        const range = 2
        let start = Math.max(this.currentPage - range, 1)
        let end = Math.min(this.currentPage + range, this.totalPages)

        const pages = []
        for (let i = start; i <= end; i++) pages.push(i)
        return pages
      },
      filteredLogs() {
        return this.logs.filter(log => {
          const dateMatch = this.selectedDateRange && this.selectedDateRange.start && this.selectedDateRange.end
            ? new Date(log.date) >= new Date(this.selectedDateRange.start) &&
              new Date(log.date) <= (d => (d.setHours(23, 59, 59, 999), d))(new Date(this.selectedDateRange.end))
            : true;

          const shiftMatch = this.selectedShift ? log.shift === this.selectedShift : true;

          const nameMatch = this.searchName
            ? `${log.user.first_name} ${log.user.last_name}`.toLowerCase().includes(this.searchName.toLowerCase())
            : true;

          return dateMatch && shiftMatch && nameMatch;
        });
      },
      paginatedLogs() {
        const start = (this.currentPage - 1) * this.perPage;
        return this.filteredLogs.slice(start, start + this.perPage);
      }
    },
    watch: {
      searchName() { this.currentPage = 1 },
      selectedDateRange: {
        handler() { this.currentPage = 1 },
        deep: true
      },
      selectedShift() { this.currentPage = 1 }
    },
    mounted() {
      this.fetchLogs()
      this.startClock()
      this.initQrScanner()

      flatpickr(this.$refs.datePicker, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        onChange: ([start, end]) => {
          this.selectedDateRange = { start, end }
        }
      })
    },
    methods: {
      clearFilters() {
        this.selectedDateRange = null
        this.selectedShift = ''
        this.searchName = ''
        this.currentPage = 1
        if (this.$refs.datePicker && this.$refs.datePicker._flatpickr) {
          this.$refs.datePicker._flatpickr.clear()
        }
      },
      changePage(page) {
        if (page >= 1 && page <= this.totalPages) this.currentPage = page;
      },
      updateTime() {
        const now = new Date()
        const options = {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
        }
        this.currentDateTime = now.toLocaleString('en-US', options)
      },
      fetchLogs() {
        api.get(TimeClock.logs).then(res => {
          this.logs = res.data
        })
      },
      handleClock() {
        this.loading = true
        // console.log('Submitting to /dtr/clock', this.scannedUserId)
        axios.post('/dtr/clock', {
          user_id: this.scannedUserId
        })
          .then(res => {
            Swal.fire({
              icon: res.data.status === 'success' ? 'success' : 'error',
              title: res.data.message,
              timer: 2000,
              showConfirmButton: false
            })
            this.fetchLogs()
          })
          .catch(() => {
            Swal.fire({ icon: 'error', title: 'Clock Action Failed' })
          })
          .finally(() => {
            this.loading = false
          })
      },
      startClock() {
        this.updateTime()
        setInterval(this.updateTime, 1000)
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
      formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
      },
      formatWorkHours(hours) {
        let h = Math.floor(hours)
        let m = Math.round((hours - h) * 60)
        return `${h} h ${m} min`
      },
      closeFacePopup() {
        this.showFacePopup = false
        this.initQrScanner()
        const video = document.getElementById('video')
        if (video?.srcObject) {
          video.srcObject.getTracks().forEach(track => track.stop())
          video.srcObject = null
        }
      },
      initQrScanner() {
        if (this.scannerInstance) return
        this.scannerInstance = new Html5QrcodeScanner("qr-scanner", {
          fps: 10,
          qrbox: 200
        })
        this.scannerInstance.render(this.onQrSuccess, () => {})
      },
      
      onQrSuccess(decodedText) {
        // console.log("QR Scanned:", decodedText)
        try {
          const data = JSON.parse(decodedText)

          if (!data.id) {
            Swal.fire({
              icon: 'error',
              title: 'Invalid QR Code',
              text: 'This QR does not contain a valid user ID.',
              timer: 2500,
              showConfirmButton: false
            })

            if (this.scannerInstance) {
              this.scannerInstance.clear().then(() => {
                document.getElementById('qr-scanner').innerHTML = ''
                this.initQrScanner() // ✅ restart scanner
              })
            }
            return
          }
          this.scannedUserId = data.id
          // console.log('Scanned ID:', this.scannedUserId) 

        } catch (e) {
          console.error("Invalid QR data format", e)
          return
        }
        if (this.scannerInstance) {
          this.scannerInstance.clear().then(() => {
            document.getElementById('qr-scanner').innerHTML = ''
          })
        }
        this.showFacePopup = true
        this.initFaceCapture()
      },

      async initFaceCapture() {
        this.cameraInitializing = true
        await faceapi.nets.tinyFaceDetector.loadFromUri('/models/tiny_face_detector')
        const video = document.getElementById('video')
        const stream = await navigator.mediaDevices.getUserMedia({ video: true })
        video.srcObject = stream
        video.onloadedmetadata = () => {
          video.play()
          this.cameraInitializing = false
          this.detectFace(video, stream)
        }
      },
      detectFace(video, stream) {
        const runDetect = async () => {
          const result = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
          if (result && !this.captureTriggered) {
            this.captureTriggered = true
            document.querySelector('.face-overlay').classList.add('detected')
            setTimeout(() => {
              this.captureFace(video, stream)
              this.captureTriggered = false
            }, 2000)
          } else {
            document.querySelector('.face-overlay').classList.remove('detected')
            requestAnimationFrame(runDetect)
          }
        }
        runDetect()
      },
      captureFace(video, stream) {
        const canvas = document.createElement('canvas')
        canvas.width = video.videoWidth
        canvas.height = video.videoHeight
        canvas.getContext('2d').drawImage(video, 0, 0)
        const imgData = canvas.toDataURL('image/jpeg')
        stream.getTracks().forEach(track => track.stop())
        video.srcObject = null
        this.showFacePopup = false

        api.post(TimeClock.uploadFace, {
          image: imgData,
          user_id: this.scannedUserId
        }).then(res => {
          // console.log('Face saved:', res.data.path)
          this.handleClock()
        }).catch(err => {
          console.error('Upload error:', err)
          Swal.fire({ icon: 'error', title: 'Face Upload Failed' })
        })

        this.scannerInstance = null
        this.initQrScanner()
      }
    }
  }
  </script>
  
  <style scoped>
  .table-responsive {
    margin-top: 20px;
  }
  .face-popup {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .video-wrapper {
    width: 480px;
    height: 540px;
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: #000;
  }
  video {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .instruction {
    color: #fff;
    margin-top: 10px;
    font-size: 14px;
  }
  .face-overlay {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    pointer-events: none;
  }
  .face-overlay::before {
    content: '';
    border: 3px solid white;
    border-radius: 50% / 40%;
    width: 65%;
    height: 75%;
    transition: border-color 0.3s ease;
  }
  .face-overlay.detected::before {
    border-color: #28a745 !important;
  }
  .face-loader {
    position: absolute;
    width: 60px;
    height: 60px;
    z-index: 2;
  }
  .hint {
    position: absolute;
    top: 10px;
    background: rgba(0,0,0,0.6);
    color: #fff;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 14px;
  }
  .close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: transparent;
    border: none;
    font-size: 30px;
    color: white;
    cursor: pointer;
    z-index: 1060;
  }
  .card div {
    font-size: 14px;
    margin-bottom: 3px;
  }
  .camera-loader-wrapper {
    width: 480px;
    height: 540px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
    border-radius: 12px;
    position: relative;
    z-index: 1;
  }
  .pagination .page-item.active .page-link {
    background-color: var(--bs-primary, #007bff); /* fallback if var fails */
    color: white !important;
    border-color: var(--bs-primary, #007bff);
  }
  .pagination .page-link:hover {
    background-color: #007bff !important;
    color: white !important;
    border-color: #007bff !important;
  }
  </style>  