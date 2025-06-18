<template>
  <div class="content-wrapper position-relative">
    <div class="row">
      <div class="col-sm-3">
        <h4>Measurements</h4>
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
              data-target="#addMeasurementModal"
            >
              Add Measurements
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
                  <th v-for="(label, index) in headers" :key="index" class="text-center">
                    <span>{{ label }}</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(measure, index) in paginatedUsers" :key="measure.id" class="text-center">
                  <td>{{ (currentPage - 1) * measuresPerPage + index + 1 }}</td>
                  <td>{{ measure.label }}</td>
                  <td>{{ measure.unit }}</td>
                  <td>{{ measure.type }}</td>
                  <td>{{ measure.multiplier }}</td>
                  <td>
                    <div class="dropdown">
                      <i class="fas fa-ellipsis-v" data-toggle="dropdown" style="cursor: pointer;"></i>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" @click="editMeasurement(measure.id)">Edit</a>
                        <a class="dropdown-item text-danger" href="#" @click="deleteMeasurement(measure.id)">Delete</a>
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

          <!-- Mobile Cards -->
          <div class="d-md-none">
            <div v-for="(measure, index) in paginatedUsers" :key="measure.id" class="card p-3 mb-2">
              <div><strong>ID: </strong>{{ (currentPage - 1) * measuresPerPage + index + 1 }}</div>
              <div><strong>Measurement Label: </strong>{{ measure.label }}</div>
              <div><strong>Unit: </strong>{{ measure.unit }}</div>
              <div><strong>Measurement Type: </strong>{{ measure.type }}</div>
              <div><strong>Multiplier: </strong>{{ measure.multiplier }}</div>
              <div class="text-right mt-2">
                <button class="btn btn-sm btn-primary" @click="editMeasurement(measure.id)">Edit</button>
                <button class="btn btn-sm btn-danger" @click="deleteMeasurement(measure.id)">Delete</button>
              </div>
            </div>
          </div>

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
    <div class="modal fade" id="addMeasurementModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editingId ? 'Edit Measurement' : 'Add Measurement' }}
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Measurement Label:</label>
              <input type="text" class="form-control" v-model="newMeasure.label" />
            </div>
            <div class="form-group">
              <label>Measurement Type:</label>
              <input type="text" class="form-control" v-model="newMeasure.type" />
            </div>
            <div class="form-group">
              <label>Unit:</label>
              <input type="text" class="form-control" v-model="newMeasure.unit" />
            </div>
            <div class="form-group">
              <label>Multiplier:</label>
              <input type="number" class="form-control" v-model="newMeasure.multiplier" />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
            <button class="btn btn-primary"
            :disabled="isSaving"
            @click="editingId ? updateMeasurement() : saveMeasurement()"
            >
              {{ isSaving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api, { ProductMeasurements } from '../../api.js';
import Loader from '../../components/Loader.vue';
import Swal from 'sweetalert2';

export default {
  name: 'ProductMeasurements',
  components: { Loader },
  data() {
    return {
      searchQuery: '',
      isLoading: false,
      currentPage: 1,
      measuresPerPage: 10,
      editingId: null,
      isSaving: false,

      // Default new measure object
      newMeasure: {
        type: '',
        unit: '',
        label: '',
        multiplier: '',
        base_unit: ''
      },
      headers: ['#', 'Measurement Label', 'Unit', 'Measurement Type', 'Multiplier', 'Action'],
      measures: []
    }
  },
  computed: {
    filteredMeasures() {
      return this.measures.filter(m =>
        `${m.type} ${m.unit} ${m.label}`.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.measuresPerPage;
      return this.filteredMeasures.slice(start, start + this.measuresPerPage);
    },
    totalPages() {
      return Math.ceil(this.filteredMeasures.length / this.measuresPerPage);
    },
    visiblePages() {
      return Array.from({ length: this.totalPages }, (_, i) => i + 1)
    },
    startItem() {
      return (this.currentPage - 1) * this.measuresPerPage + 1
    },
    endItem() {
      return Math.min(this.currentPage * this.measuresPerPage, this.filteredMeasures.length);
    }
  },
  mounted() {
    this.fetchMeasurements();
    $('#addMeasurementModal').on('hidden.bs.modal', this.resetModal);
  },
  methods: {
    //helper functions
    resetModal() {
      this.newMeasure = { type: '', unit: '', label: '', multiplier: '', base_unit: '' };
      this.editingId = null;
      this.isSaving = false;
    },
    clearSearch() {
      this.searchQuery = ''
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
      }
    },

    // core funtions
    fetchMeasurements() {
      this.isLoading = true;
      api.get(ProductMeasurements.fetchList)
        .then(res => { this.measures = res.data; })
        .finally(() => { this.isLoading = false; });
    },
    saveMeasurement() {
      if (!this.newMeasure.type || !this.newMeasure.unit || !this.newMeasure.label || !this.newMeasure.multiplier) {
        Swal.fire('Warning', 'All fields are required.', 'warning');
        return;
      }

      this.newMeasure.base_unit = this.newMeasure.unit;
      this.isSaving = true;

      api.post(ProductMeasurements.store, this.newMeasure)
        .then(res => {
          this.measures.unshift(res.data);
          this.newMeasure = { type: '', unit: '', label: '', multiplier: '', base_unit: '' };
          $('#addMeasurementModal').modal('hide');
          Swal.fire('Success', 'Measurement saved successfully.', 'success');
        })
        .catch(() => {
          Swal.fire('Error', 'Failed to save measurement.', 'error');
        })
        .finally(() => {
          this.isSaving = false;
        });
    },
    editMeasurement(id) {
      api.get(ProductMeasurements.show(id))
        .then(res => {
          this.newMeasure = { ...res.data };
          this.editingId = id;
          $('#addMeasurementModal').modal('show');
        })
        .catch(() => {
          Swal.fire('Error', 'Failed to load data.', 'error')
        });
    },
    updateMeasurement() {
      if (!this.editingId) return;

      this.newMeasure.base_unit = this.newMeasure.unit;
      this.isSaving = true;

      api.put(ProductMeasurements.update(this.editingId), this.newMeasure)
        .then(res => {
          const index = this.measures.findIndex(m => m.id === this.editingId);
          if (index !== -1) this.measures.splice(index, 1, res.data);
          $('#addMeasurementModal').modal('hide');
          this.editingId = null;
          Swal.fire('Updated', 'Measurement updated successfully.', 'success');
        })
        .catch(() => {
          Swal.fire('Error', 'Failed to update measurement.', 'error');
        })
        .finally(() => {
          this.isSaving = false;
        });
    },
    deleteMeasurement(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the measurement.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
      }).then(result => {
        if (result.isConfirmed) {
          api.delete(ProductMeasurements.delete(id))
            .then(() => {
              this.measures = this.measures.filter(m => m.id !== id);
              Swal.fire('Deleted', 'Measurement deleted successfully.', 'success');
            })
            .catch(() => Swal.fire('Error', 'Failed to delete measurement.', 'error'));
        }
      });
    }
  }
}
</script>
