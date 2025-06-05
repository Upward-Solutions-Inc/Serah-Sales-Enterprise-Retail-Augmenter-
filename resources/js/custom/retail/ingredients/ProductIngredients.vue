<template>
  <div class="content-wrapper position-relative">
    <div class="row">
      <div class="col-sm-3">
        <h4>Ingredients</h4>
      </div>
    </div>

    <div class="col-lg-12 mt-2">
      <div class="datatable">
        <div class="my-2 d-flex justify-content-between flex-wrap">
          <div class="d-flex align-items-center">
            <p class="text-muted mb-0">
              Showing {{ startItem }} to {{ endItem }} of {{ users.length }} items
            </p>
          </div>

            <div class="d-flex align-items-center">
              <input
                type="text"
                v-model="searchQuery"
                class="form-control mr-2"
                placeholder="Search..."
                style="max-width: 250px;"
              />
              <button class="btn btn-outline-secondary" @click="clearSearch">ADD +</button>
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
                      <a class="dropdown-item" href="#" @click="generateQr(user)">View</a>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="users.length === 0 && !isLoading" class="no-data-found-wrapper text-center p-primary">
            <img src="/images/no_data.svg" alt="" class="mb-primary" />
            <p class="mb-0">Nothing to show here</p>
            <p class="mb-0 text-center text-secondary font-size-90">Please add a new entity or manage the data table to see content.</p>
          </div>
        </div>

        <!-- Pagination -->
        <nav v-if="totalPages > 1" class="mt-2">
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

        <!-- Mobile Cards -->
        <div class="d-md-none">
          <div v-if="!isLoading && users.length" v-for="user in paginatedUsers" :key="user.id" class="card p-3 mb-2">
            <div><strong>User ID:</strong> {{ user.id }}</div>
            <div><strong>Name:</strong> {{ user.first_name }} {{ user.last_name }}</div>
            <div><strong>Email:</strong> {{ user.email || 'N/A' }}</div>
            <div><strong>Role:</strong> {{ user.role || 'N/A' }}</div>
            <div><strong>Branch:</strong> {{ user.branch || 'N/A' }}</div>
            <div class="text-right mt-2">
              <button class="btn btn-sm btn-primary" @click="generateQr(user)">View</button>
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

                      <div class="flip-card-front text-center p-3"  ref="idCardExportFront">
                        <h5 class="mb-2">THE NEST</h5>
                        <img :src="selectedUser.image || 'https://www.svgrepo.com/show/452030/avatar-default.svg'" class="id-photo" />
                        <h6 class="mt-2">{{ selectedUser.first_name }} {{ selectedUser.last_name }}</h6>
                        <p>ID: {{ selectedUser.id || 'N/A' }}</p>
                        <p>Role: {{ selectedUser.role || 'N/A' }}</p>
                        <p>Branch: {{ selectedUser.branch || 'N/A' }}</p>
                      </div>

                      <div class="flip-card-back d-flex flex-column justify-content-center align-items-center p-3" ref="idCardExportBack">
                        <div v-if="pngDataUrl">
                          <img :src="pngDataUrl" style="width: 250px;" />
                        </div>
                          <p class="mb-1 font-italic small">Please scan to log time</p>
                          <hr style="width: 80%; border-top: 1px dashed #000;" />

                        <div class="mt-2 text-left w-100 px-3">
                          <div class="underline-wrap">
                            <p class="small mb-1">Employee Signature:</p>
                            <span></span>
                          </div>
                          <div class="underline-wrap">
                            <p class="small mb-1">Emergency Contact:</p>
                            <span></span>
                          </div>
                          <div class="underline-wrap">
                            <p class="small mb-1">Address:</p>
                            <span></span>
                          </div>
                        </div>
                      </div>

                  </div>
                </div>

              </div>
              <div class="modal-footer justify-content-center border-0 pt-0">
                <button class="btn btn-secondary btn-sm mr-2" data-dismiss="modal" @click="qrSvg = null">Close</button>
                <button class="btn btn-primary btn-sm d-flex align-items-center justify-content-center px-3" :disabled="isLoading" @click="downloadQr">
                  <Loader v-if="isLoading" class="mr-1" style="width: 18px; height: 18px;" />
                  <span v-else>Download</span>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script>

export default {
  name: 'InventoryIngredients',
  data() {
    return {
      searchQuery: '',
      isLoading: false,
      qrSvg: null,
      pngDataUrl: null,
      currentPage: 1,
      usersPerPage: 5,
      selectedUser: {},
      users: [
        {
          id: 1,
          first_name: 'John',
          last_name: 'Doe',
          email: 'john.doe@example.com',
          role: 'Manager',
          branch: 'Manila',
          image: ''
        },
        {
          id: 2,
          first_name: 'Jane',
          last_name: 'Smith',
          email: 'jane.smith@example.com',
          role: 'Cashier',
          branch: 'Cebu',
          image: ''
        },
        {
          id: 3,
          first_name: 'Mike',
          last_name: 'Brown',
          email: 'mike.brown@example.com',
          role: 'Chef',
          branch: 'Davao',
          image: ''
        },
        {
          id: 4,
          first_name: 'Anna',
          last_name: 'Taylor',
          email: 'anna.taylor@example.com',
          role: 'Waiter',
          branch: 'Baguio',
          image: ''
        },
        {
          id: 5,
          first_name: 'Paul',
          last_name: 'Walker',
          email: 'paul.walker@example.com',
          role: 'Manager',
          branch: 'Quezon City',
          image: ''
        }
      ]
    }
  },
  computed: {
    filteredUsers() {
      if (!this.searchQuery) return this.users
      return this.users.filter(user =>
        `${user.first_name} ${user.last_name}`.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    },
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.usersPerPage
      return this.filteredUsers.slice(start, start + this.usersPerPage)
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.usersPerPage)
    },
    visiblePages() {
      return Array.from({ length: this.totalPages }, (_, i) => i + 1)
    },
    startItem() {
      return (this.currentPage - 1) * this.usersPerPage + 1
    },
    endItem() {
      return Math.min(this.currentPage * this.usersPerPage, this.filteredUsers.length)
    }
  },
  methods: {
    clearSearch() {
      this.searchQuery = ''
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
      }
    },
    generateQr(user) {
      this.selectedUser = user
      $('#qrModal').modal('show')
    },
    downloadQr() {
      // dummy download trigger
      alert('Downloading QR...')
    }
  }
}
</script>
