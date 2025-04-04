<template>
    <div class="content-wrapper position-relative">
      <div class="row">
        <div class="col-sm-3">
          <h4>Employee Card</h4>
        </div>
      </div>
      <!-- Table Placeholder -->
      <div class="col-lg-12 mt-2">
        <div class="datatable">
          <div class="my-2 d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <p class="text-muted mb-0">Showing {{ pagination.start }} to {{ pagination.end }} items of {{ users.length }}</p>
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
                  <th class="datatable-th pt-0 text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(user, index) in users" :key="user.id" class="text-center">
                  <td>{{ user.id }}</td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.role }}</td>
                  <td>
                    <div class="dropdown">
                      <i class="fas fa-ellipsis-v" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;"></i>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" @click.prevent="viewUser(user)">View</a>
                        <a class="dropdown-item" href="#" @click.prevent="editUser(user)">Edit</a>
                        <a class="dropdown-item text-danger" href="#" @click.prevent="deleteUser(user)">Delete</a>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="users.length === 0 && !isLoading" class="no-data-found-wrapper text-center p-primary">
              <img src="/images/no_data.svg" alt="" class="mb-primary">
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
  </template>
  
  <script>
  import Loader from '../components/Loader.vue'

  export default {
    name: 'EmployeeId',
    components: {
        Loader
    },
    data() {
      return {
        isLoading: true,
        users: [],
        pagination: { start: 0, end: 0 }
      }
    },
    methods: {
      fetchUsers() {
        // Simulate API call
        setTimeout(() => {
          this.users = [
            { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Admin' },
            { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'User' },
            { id: 3, name: 'Alice Brown', email: 'alice@example.com', role: 'Editor' }
          ];
          this.pagination.start = 1;
          this.pagination.end = this.users.length;
          this.isLoading = false;
        }, 1000);
      },
      viewUser(user) {
        alert(`Viewing user: ${user.name}`);
      },
      editUser(user) {
        alert(`Editing user: ${user.name}`);
      },
      deleteUser(user) {
        if (confirm(`Are you sure you want to delete ${user.name}?`)) {
          this.users = this.users.filter(u => u.id !== user.id);
          this.pagination.end = this.users.length;
        }
      }
    },
    mounted() {
      this.fetchUsers();
    }
  }
  </script>
  
  <style scoped>
  .table-responsive {
    margin-top: 20px;
  }
  </style>  