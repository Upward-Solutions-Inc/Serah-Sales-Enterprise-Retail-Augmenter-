<template>
  <div class="content-wrapper position-relative">
    <div class="row">
      <div class="col-sm-3">
        <h4>Ingredients</h4>
      </div>
    </div>

    <div class="col-lg-12 mt-2">
      <div class="datatable d-flex flex-column" style="min-height: 90vh;">
        <!-- Filters -->
        <div class="my-2 row">
          <div class="col-md-6 col-12 mb-2 d-flex align-items-center">
            <p class="text-muted mb-0">
              Showing {{ startItem }} to {{ endItem }} of {{ measures.length }} items
            </p>
          </div>
          <div class="col-md-6 col-12 d-flex align-items-center justify-content-md-end flex-column flex-md-row">
            <input
              type="text"
              v-model="searchQuery"
              class="form-control mb-2 mb-md-0"
              placeholder="Search..."
              style="max-width: 300px; height: 40px;"
            />
            <button
              class="btn btn-primary ml-md-3"
              style="height: 42px; width: 100%; max-width: 280px;"
              data-toggle="modal" 
              data-target="#addIngredientsModal"
            >
              Add Ingredients
            </button>
          </div>
        </div>

        <!-- Data View -->
        <div class="flex-grow-1">
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
                  <th v-for="(label, index) in headers" :key="index" class="datatable-th pt-0 text-center fixed-col">
                      <span class="font-size-default">{{ label }}</span>
                  </th>
                  </tr>
              </thead>
              <tbody>
                <tr v-for="measure in paginatedUsers" :key="measure.id" class="text-center">
                  <td>{{ measure.id }}</td>
                  <td>{{ measure.name || 'N/A' }}</td>
                  <td>{{ measure.measure || 'N/A' }}</td>
                  <td>{{ measure.unit || 'N/A' }}</td>
                  <td>{{ measure.amount || 'N/A' }}</td>
                  <td>
                    <div class="dropdown">
                      <i class="fas fa-ellipsis-v" data-toggle="dropdown" style="cursor: pointer;"></i>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">View</a>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-if="measures.length === 0 && !isLoading" class="no-data-found-wrapper text-center p-primary">
              <img src="/images/no_data.svg" alt="" class="mb-primary" />
              <p class="mb-0">Nothing to show here</p>
              <p class="mb-0 text-center text-secondary font-size-90">Please add a new entity or manage the data table to see content.</p>
            </div>

          </div>

          <!-- Mobile View -->
          <div class="d-md-none">
            <div v-if="!isLoading && measures.length" v-for="measure in paginatedUsers" :key="measure.id" class="card p-3 mb-2">
              <div><strong>ID:</strong> {{ measure.id }}</div>
              <div><strong>Measurement:</strong> {{ measure.name || 'N/A' }}</div>
              <div><strong>Unit:</strong> {{ measure.unit || 'N/A' }}</div>
              <div><strong>Amount:</strong> {{ measure.amount || 'N/A' }}</div>
              <div class="text-right mt-2">
                <button class="btn btn-sm btn-primary" @click="generateQr(measure)">View</button>
              </div>
            </div>
          </div>

          <!-- pagination -->
          <nav v-if="totalPages > 1" :class="['mt-2', 'w-100', 'd-block']">
            <div class="d-flex justify-content-center d-md-none"> <!-- Mobile view bottom center -->
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Prev</a>
                </li>
                <li class="page-item" v-for="page in visiblePages" :key="page" :class="{ active: currentPage === page }">
                  <a class="page-link" href="#" @click.prevent="changePage(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                </li>
              </ul>
            </div>

            <div class="d-none d-md-flex justify-content-end"> <!-- Desktop view -->
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Prev</a>
                </li>
                <li class="page-item" v-for="page in visiblePages" :key="page" :class="{ active: currentPage === page }">
                  <a class="page-link" href="#" @click.prevent="changePage(page)">
                    {{ page }}
                  </a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                </li>
              </ul>
            </div>
          </nav>

        </div>
      </div>
    </div>

    <!-- Add Measurement Modal -->
    <div class="modal fade" id="addIngredientsModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add Ingredients</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Ingredient Name:</label>
              <input type="text" class="form-control" v-model="newIngredients.name" />
            </div>
            <div class="form-group">
              <label>Measurement Type:</label>
              <select class="form-control" v-model="newIngredients.measure">
                <option disabled value="">Select measurement type</option>
                <option v-for="m in measures" :key="m.type" :value="m.type">
                  {{ m.type }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Unit:</label>
              <select class="form-control" v-model="newIngredients.unit">
                <option disabled value="">Select unit</option>
                <option v-for="unit in filteredUnits()" :key="unit.unit" :value="unit.unit">
                  {{ unit.label }} ({{ unit.unit }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Amount:</label>
              <input type="number" step="any" class="form-control" v-model="newIngredients.amount" />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
            <button class="btn btn-primary" @click="saveIngredients">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api, { ProductIngredients } from '../../api.js'
import Loader from '../../components/Loader.vue'

export default {
  name: 'ProductMeasurements',
  components: {
      Loader
  },
  data() {
    return {
      searchQuery: '',
      isLoading: false,
      currentPage: 1,
      measuresPerPage: 5,
      newIngredients: {
        name: '',
        measure: '',
        unit: '',
        amount: ''
      },
      measures: [],

      headers: [
          '#',
          'Ingredient Name',
          'Measurement Type',
          'Unit',
          'Amount',
          'Action'
      ]
    }
  },
  computed: {
    filteredUsers() {
      if (!this.searchQuery) return this.measures
      return this.measures.filter(measure =>
        `${measure.name} ${measure.unit}`.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    },
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.measuresPerPage
      return this.filteredUsers.slice(start, start + this.measuresPerPage)
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.measuresPerPage)
    },
    visiblePages() {
      return Array.from({ length: this.totalPages }, (_, i) => i + 1)
    },
    startItem() {
      return (this.currentPage - 1) * this.measuresPerPage + 1
    },
    endItem() {
      return Math.min(this.currentPage * this.measuresPerPage, this.filteredUsers.length)
    }
  },
  mounted() {
    this.fetchMeasurements()
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
    fetchMeasurements() {
      this.isLoading = true
      api.get(ProductIngredients.fetchMeasurements)
        .then(res => {
          this.measures = res.data
        })
        .finally(() => {
          this.isLoading = false
        })
    },
    filteredUnits() {
      const selected = this.measures.find(m => m.type === this.newIngredients.measure)
      return selected ? selected.units : []
    },

    saveIngredients() {
      if (!this.newIngredients.name || !this.newIngredients.unit || !this.newIngredients.amount) {
        alert('Please fill out all fields.')
        return
      }
      const id = this.measures.length + 1
      this.measures.push({ id, ...this.newIngredients })
      this.newIngredients = { name: '', measure:'', unit: '', amount: '' }
      $('#addIngredientsModal').modal('hide')
    }
  }
}
</script>