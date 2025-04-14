<template>
  <div class="content-wrapper position-relative">
    <div class="row">
      <div class="col-sm-3">
        <h4>Employee Card</h4>
      </div>
    </div>

    <div class="col-lg-12 mt-2">
      <div class="datatable">
        <div class="my-2 d-flex justify-content-between">
          <div class="d-flex align-items-center">
            <p class="text-muted mb-0">
              Showing {{ pagination.start }} to {{ pagination.end }} items of {{ users.length }}
            </p>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="table-responsive shadow pt-primary position-relative d-none d-md-block">
          <loader
            class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center"
            style="top: 0; left: 0; z-index: 1050;"
            :visible="isLoading"
            v-if="isLoading"
          />
          <table v-else class="table table-striped table-borderless">
            <thead>
              <tr>
                <th class="text-center">User ID</th>
                <th class="text-center">Name</th>
                <th class="text-center">Email</th>
                <th class="text-center">Role</th>
                <th class="text-center">Branch</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in paginatedUsers" :key="user.id" class="text-center">
                <td>{{ user.id }}</td>
                <td>{{ user.first_name }} {{ user.last_name }}</td>
                <td>{{ user.email || 'N/A' }}</td>
                <td>{{ user.role || 'N/A' }}</td>
                <td>{{ user.branch || 'N/A' }}</td>
                <td>
                  <div class="dropdown">
                    <i class="fas fa-ellipsis-v" data-toggle="dropdown" style="cursor: pointer;"></i>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="#" @click="generateQr(user)">Print</a>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="users.length === 0 && !isLoading" class="text-center p-primary">
            <img src="/images/no_data.svg" alt="" class="mb-primary" />
            <p class="mb-0">Nothing to show here</p>
            <p class="text-secondary font-size-90">Please add a new entity or manage the data table to see content.</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-3">
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">Prev</a>
              </li>
              <li
                class="page-item"
                v-for="n in Array.from({ length: totalPages }, (_, i) => i + 1)"
                :key="n"
                :class="{ active: currentPage === n }"
              >
                <a class="page-link" href="#" @click.prevent="goToPage(n)">{{ n }}</a>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">Next</a>
              </li>
            </ul>
          </nav>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none">
          <div v-if="!isLoading && users.length" v-for="user in paginatedUsers" :key="user.id" class="card p-3 mb-2">
            <div><strong>User ID:</strong> {{ user.id }}</div>
            <div><strong>Name:</strong> {{ user.first_name }} {{ user.last_name }}</div>
            <div><strong>Email:</strong> {{ user.email || 'N/A' }}</div>
            <div><strong>Role:</strong> {{ user.role || 'N/A' }}</div>
            <div><strong>Branch:</strong> {{ user.branch || 'N/A' }}</div>
            <div class="text-right mt-2">
              <button class="btn btn-sm btn-primary" @click="generateQr(user)">Print</button>
            </div>
          </div>
        </div>

        <!-- QR Modal -->
        <div class="modal fade" id="qrModal" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
              <div class="modal-header py-2">
                <h5 class="modal-title w-100 text-center m-0">Digital ID</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="qrSvg = null">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body text-center p-3">
                <div ref="svgContainer" v-html="qrSvg" style="display: none;"></div>
                <canvas ref="canvas" width="300" height="300" style="display: none;"></canvas>

                <div class="flip-card mx-auto">
                  <div class="flip-card-inner">
                    <div class="flip-card-front text-center p-3">
                      <h5 class="mb-2">THE NEST CORP</h5>
                      <img :src="selectedUser.image || 'https://www.svgrepo.com/show/452030/avatar-default.svg'" class="id-photo" />
                      <h6 class="mt-2">{{ selectedUser.first_name }} {{ selectedUser.last_name }}</h6>
                      <p>ID: {{ selectedUser.id || 'N/A' }}</p>
                      <p>Role: {{ selectedUser.role || 'N/A' }}</p>
                      <p>Branch: {{ selectedUser.branch || 'N/A' }}</p>
                    </div>
                    <div class="flip-card-back d-flex flex-column justify-content-center align-items-center p-3">
                      <div v-if="pngDataUrl">
                        <img :src="pngDataUrl" style="width: 250px;" />
                      </div>
                      <p class="mt-3 mb-1 font-italic small">Please scan to log time</p>
                      <hr style="width: 80%; border-top: 1px dashed #000;" />
                      <p class="small mb-0">Employee Signature</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer justify-content-center border-0 pt-0">
                <button class="btn btn-secondary btn-sm mr-2" data-dismiss="modal" @click="qrSvg = null">Close</button>
                <button class="btn btn-primary btn-sm" @click="downloadQr">Download</button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import Loader from '../components/Loader.vue'
import api, { PayrollReports, EmployeeId } from '../api.js'

export default {
  name: 'EmployeeId',
  components: { Loader },
  data() {
    return {
      isLoading: true,
      users: [],
      currentPage: 1,
      perPage: 10,
      qrSvg: null,
      pngDataUrl: null,
      selectedUser: {},
      pagination: { start: 0, end: 0 }
    }
  },
  computed: {
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.perPage
      const end = start + this.perPage
      this.pagination.start = this.users.length ? start + 1 : 0
      this.pagination.end = Math.min(end, this.users.length)
      return this.users.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.users.length / this.perPage)
    }
  },
  watch: {
    qrSvg(newVal) {
      if (newVal) {
        this.pngDataUrl = null
        this.$nextTick(() => this.convertSvgToPng())
      }
    }
  },
  methods: {
    fetchUsers() {
      this.isLoading = true
      api.get(PayrollReports.fetchUsers)
        .then(res => {
          this.users = res.data
        })
        .catch(err => {
          console.error(err)
          this.users = []
        })
        .finally(() => {
          this.isLoading = false
        })
    },
    goToPage(page) {
      if (page < 1 || page > this.totalPages) return
      this.currentPage = page
    },
    generateQr(user) {
      this.qrSvg = null
      this.pngDataUrl = null
      this.selectedUser = user

      api.post(EmployeeId.generateQr, {
        id: user.id,
        name: `${user.first_name} ${user.last_name}`,
        email: user.email,
        role: user.role,
        branch: user.branch
      }).then(res => {
        this.qrSvg = res.data
        $('#qrModal').modal('show')
      })
    },
    convertSvgToPng() {
      const container = this.$refs.svgContainer
      if (!container) return
      const svgElement = container.querySelector('svg')
      if (!svgElement) return

      const svgData = new XMLSerializer().serializeToString(svgElement)
      const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' })
      const url = URL.createObjectURL(svgBlob)

      const img = new Image()
      img.onload = () => {
        const canvas = this.$refs.canvas
        const ctx = canvas.getContext('2d')
        ctx.clearRect(0, 0, canvas.width, canvas.height)
        ctx.drawImage(img, 0, 0)
        this.pngDataUrl = canvas.toDataURL('image/png')
        URL.revokeObjectURL(url)
      }
      img.src = url
    },
    downloadQr() {
      const link = document.createElement('a')
      link.href = this.pngDataUrl
      link.download = 'user-qr.png'
      link.click()
    }
  },
  mounted() {
    this.fetchUsers()
  }
}
</script>

<style scoped>
.table-responsive {
  margin-top: 20px;
}
.flip-card {
  background-color: transparent;
  width: 280px;
  height: 420px;
  perspective: 1000px;
  border-radius: 8px;
  cursor: pointer;
}
.flip-card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.8s;
  transform-style: preserve-3d;
  border-radius: 8px;
}
.flip-card:hover .flip-card-inner {
  transform: rotateY(180deg);
}
.flip-card-front,
.flip-card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
.flip-card-front {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 15px;
}
.flip-card-back {
  transform: rotateY(180deg);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.id-photo {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 12px;
  border: 2px solid #0056b3;
  margin: 10px 0;
}


.table td, .table th {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 150px;
}

.pagination {
  justify-content: flex-end !important;
}
</style>