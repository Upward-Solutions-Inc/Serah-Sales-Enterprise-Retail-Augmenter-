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

        <div class="table-responsive custom-scrollbar table-view-responsive shadow pt-primary position-relative">
          <loader
            class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center"
            style="top: 0; left: 0; z-index: 1050;"
            :visible="isLoading"
            v-if="isLoading"
          />
          <table v-else class="table table-striped table-borderless">
            <thead>
              <tr>
                <th class="datatable-th pt-0 text-center">User ID</th>
                <th class="datatable-th pt-0 text-center">Name</th>
                <th class="datatable-th pt-0 text-center">Email</th>
                <th class="datatable-th pt-0 text-center">Role</th>
                <th class="datatable-th pt-0 text-center">Branch</th>
                <th class="datatable-th pt-0 text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id" class="text-center">
                <td>{{ user.id }}</td>
                <td>{{ user.first_name }} {{ user.last_name }}</td>
                <td>{{ user.email || 'N/A' }}</td>
                <td>{{ user.role || 'N/A' }}</td>
                <td>{{ user.branch || 'N/A' }}</td>        
                <td>
                  <div class="dropdown">
                    <i class="fas fa-ellipsis-v" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;"></i>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="#" @click="generateQr(user)">Print</a>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="users.length === 0 && !isLoading" class="no-data-found-wrapper text-center p-primary">
            <img src="/images/no_data.svg" alt="" class="mb-primary" />
            <p class="mb-0">Nothing to show here</p>
            <p class="mb-0 text-secondary font-size-90">
              Please add a new entity or manage the data table to see the content here
            </p>
            <p class="mb-0 text-secondary font-size-90">Thank you</p>
          </div>
        </div>

        <!-- QR Modal -->
        <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
              <div class="modal-header py-2">
                <h5 class="modal-title w-100 text-center m-0">QR Code</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="qrSvg = null">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

              <div class="modal-body text-center p-3">
                <div v-html="qrSvg" class="bg-white p-2 rounded shadow-sm d-inline-block" style="width: auto;"></div>
              </div>

              <div class="modal-footer justify-content-center border-0 pt-0">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal" @click="qrSvg = null">Close</button>
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
      pagination: { start: 0, end: 0 },
      qrSvg: null
    }
  },
  methods: {
    fetchUsers() {
      this.isLoading = true
      api.get(PayrollReports.fetchUsers)
        .then(res => {
          this.users = res.data
          this.pagination.start = 1
          this.pagination.end = this.users.length
        })
        .catch(err => {
          console.error(err)
          this.users = []
        })
        .finally(() => {
          this.isLoading = false
        })
    },
    viewUser(user) {
      alert(`Viewing user: ${user.first_name} ${user.last_name}`)
    },

    generateQr(user) {
      this.qrSvg = null

      api.post(EmployeeId.generateQr, {
        id: user.id,
        name: `${user.first_name} ${user.last_name}`,
        email: user.email,
        role: user.role,
        branch: user.branch
      }, { responseType: 'text' })
      .then(res => {
        this.qrSvg = res.data
        $('#qrModal').modal('show')
      })
      .catch(err => {
        console.error('QR Generation Error:', err)
      })
    },

    downloadQr() {
      const blob = new Blob([this.qrSvg], { type: 'image/svg+xml' })
      const link = document.createElement('a')
      link.href = URL.createObjectURL(blob)
      link.download = 'user-qr.svg'
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
</style>