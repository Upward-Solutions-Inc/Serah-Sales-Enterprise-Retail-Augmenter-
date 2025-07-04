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
              Showing {{ startItem }} to {{ endItem }} of {{ ingredients.length }} items
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

        <!-- Data Table -->
        <div class="flex-grow-1">
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
                <tr v-for="(item, index) in paginatedItems" :key="item.id" class="text-center">
                  <td>{{ (currentPage - 1) * ingredientsPerPage + index + 1 }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="mr-2">
                        <div class="avatars-w-60">
                          <img
                            :src="resolveImage(item.image)"
                            @error="handleImageError($event, item)"
                            class="rounded-circle avatar-bordered"
                          />
                          <span class="status bg-success"></span>
                        </div>
                      </div>
                      <div>
                        <a href="javascript:void(0)">{{ item.ingredient_name }}</a>
                      </div>
                    </div>
                  </td>
                  <td>{{ item.brand || 'N/A' }}</td>
                  <td>{{ item.category || 'N/A' }}</td>
                  <td>{{ item.measurement_type || 'N/A' }}</td>
                  <td>{{ formatAmount(item.amount, item.unit) }}</td>
                  <td>
                    <div class="dropdown">
                      <i class="fas fa-ellipsis-v" data-toggle="dropdown" style="cursor: pointer;"></i>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" @click="viewIngredient(item.id)">View</a>
                        <a class="dropdown-item" href="#" @click="editIngredient(item.id)">Edit</a>
                        <a class="dropdown-item text-danger" href="#" @click="deleteIngredient(item.id)">Delete</a>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-if="ingredients.length === 0 && !isLoading" class="no-data-found-wrapper text-center p-primary">
              <img src="/images/no_data.svg" alt="" class="mb-primary" />
                <p class="mb-0 text-center">Nothing to show here</p>
                <p class="mb-0 text-center text-secondary font-size-90">
                  Please add a new entity or manage the data table to see the content here
                </p>
                <p class="mb-0 text-center text-secondary font-size-90">Thank you</p>
            </div>
          </div>

          <!-- Mobile Cards -->
          <div class="d-md-none">
            <div
              v-if="!isLoading && ingredients.length"
              v-for="item in paginatedItems"
              :key="item.id"
              class="card p-3 mb-2"
            >
              <div><strong>ID:</strong> {{ item.id }}</div>
              <div class="text-center d-flex align-items-center mb-2">
                <div class="mr-2">
                  <div class="avatars-w-60">
                    <img
                      :src="resolveImage(item.image)"
                      @error="handleImageError($event, item)"
                      class="rounded-circle avatar-bordered"
                    />
                    <span class="status bg-success"></span>
                  </div>
                </div>
                <div>
                  <strong>{{ item.ingredient_name }}</strong>
                </div>
              </div>
              <div><strong>Brand:</strong> {{ item.brand }}</div>
              <div><strong>Category:</strong> {{ item.category }}</div>
              <div><strong>Type:</strong> {{ item.measurement_type }}</div>
              <div><strong>Amount:</strong> {{ formatAmount(item.amount, item.unit) }} </div>
              <div class="text-right mt-2">
                <button class="btn btn-sm btn-primary" @click="viewIngredient(item.id)">View</button>
                <button class="btn btn-sm btn-warning" @click="editIngredient(item.id)">Edit</button>
                <button class="btn btn-sm btn-danger" @click="deleteIngredient(item.id)">Delete</button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <nav v-if="totalPages > 1" class="mt-2 w-100 d-block">
            <div class="d-flex justify-content-center d-md-none">
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Prev</a>
                </li>
                <li
                  class="page-item"
                  v-for="page in visiblePages"
                  :key="page"
                  :class="{ active: currentPage === page }"
                >
                  <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                </li>
              </ul>
            </div>
            <div class="d-none d-md-flex justify-content-end">
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Prev</a>
                </li>
                <li
                  class="page-item"
                  v-for="page in visiblePages"
                  :key="page"
                  :class="{ active: currentPage === page }"
                >
                  <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
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

    <!-- Add Modal -->
    <div class="modal fade" id="addIngredientsModal" tabindex="-1" role="dialog" @hidden.bs.modal="resetModal">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ modalMode === 'view' ? 'View Ingredient' : (modalMode === 'edit' ? 'Edit Ingredient' : 'Add Ingredient') }}
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Image:</label>
              <div class="custom-image-upload-wrapper">
                <!-- preview -->
                <div class="image-area d-flex" style="width: 100%; height: 120px; overflow: hidden;">
                  <img
                    v-if="newIngredients.imagePreview"
                    :src="newIngredients.imagePreview"
                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;"
                  />
                </div>
                <div class="input-area">
                  <label id="upload-label" for="formData_product_thumbnail">Upload file</label>
                  <input
                    id="formData_product_thumbnail"
                    type="file"
                    class="form-control d-none"
                    @change="handleFileUpload"
                    :disabled="modalMode === 'view'"
                  />
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Ingredient Name:</label>
              <input type="text" class="form-control" v-model="newIngredients.ingredient_name" :disabled="modalMode === 'view'"/>
              <small class="text-danger" v-if="errors.ingredient_name">{{ errors.ingredient_name }}</small>
            </div>
            <div class="form-group">
              <label>Brand:</label>
              <input type="text" class="form-control" v-model="newIngredients.brand" :disabled="modalMode === 'view'" />
            </div>
            <div class="form-group">
              <label>Category:</label>
              <input type="text" class="form-control" v-model="newIngredients.category" :disabled="modalMode === 'view'" />
            </div>
            <div class="form-group">
              <label>Measurement Type:</label>
              <select class="form-control" v-model="newIngredients.measurement_type" :disabled="modalMode === 'view'">
                <option disabled value="">Select type</option>
                <option v-for="m in measures" :key="m.type" :value="m.type">
                  {{ m.type }}
                </option>
              </select>
              <small class="text-danger" v-if="errors.measurement_type">{{ errors.measurement_type }}</small>
            </div>
            <div class="form-group">
              <label>Unit:</label>
              <select class="form-control" v-model="newIngredients.unit" :disabled="modalMode === 'view'">
                <option disabled value="">Select unit</option>
                <option v-for="u in filteredUnits()" :key="u.unit" :value="u.unit">
                  {{ u.label }} ({{ u.unit }})
                </option>
              </select>
              <small class="text-danger" v-if="errors.unit">{{ errors.unit }}</small>
            </div>
            <div class="form-group">
              <label>Amount:</label>
              <input type="number" step="any" class="form-control" v-model="newIngredients.amount" :disabled="modalMode === 'view'"/>
              <small class="text-danger" v-if="errors.amount">{{ errors.amount }}</small>
            </div>
          </div>
          <div class="modal-footer" v-if="modalMode !== 'view'">
            <button class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
            <button
              class="btn btn-primary"
              :disabled="isSaving"
              @click="modalMode === 'edit' ? updateIngredient() : saveIngredients()"
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
import api, { ProductIngredients } from '../../api.js';
import Loader from '../../components/Loader.vue';
import Swal from 'sweetalert2';

export default {
  name: 'ProductIngredients',
  components: { Loader },
  data() {
    return {
      searchQuery: '',
      isLoading: false,
      currentPage: 1,
      ingredientsPerPage: 10,
      fallbackImage: '/storage/err/image.png',

      headers: ['#', 'Ingredient Name', 'Brand', 'Category', 'Measurement Type', 'Amount', 'Action'],
      measures: [], // for dropdowns only
      ingredients: [],

      newIngredients: {
        ingredient_name: '',
        measurement_type: '',
        unit: '',
        amount: '',
        brand: '',
        category: '',
        imageFile: null,
        imagePreview: null
      },

      errors: {
        ingredient_name: null,
        measurement_type: null,
        unit: null,
        amount: null
      },

      modalMode: 'create', // or 'edit' or 'view'
      editingId: null,
      isSaving: false
    };
  },
  computed: {
    filteredIngredients() {
      if (!this.searchQuery) return this.ingredients;
      return this.ingredients.filter(i =>
        `${i.ingredient_name} ${i.unit} ${i.measurement_type}`
          .toLowerCase()
          .includes(this.searchQuery.toLowerCase())
      );
    },
    paginatedItems() {
      const start = (this.currentPage - 1) * this.ingredientsPerPage;
      return this.filteredIngredients.slice(start, start + this.ingredientsPerPage);
    },
    totalPages() {
      return Math.ceil(this.filteredIngredients.length / this.ingredientsPerPage);
    },
    visiblePages() {
      return Array.from({ length: this.totalPages }, (_, i) => i + 1);
    },
    startItem() {
      return (this.currentPage - 1) * this.ingredientsPerPage + 1;
    },
    endItem() {
      return Math.min(this.currentPage * this.ingredientsPerPage, this.filteredIngredients.length);
    }
  },
  watch: {
    'newIngredients.ingredient_name'(val) {
      if (val) this.errors.ingredient_name = null;
    },
    'newIngredients.measurement_type'(val) {
      if (val) this.errors.measurement_type = null;
    },
    'newIngredients.unit'(val) {
      if (val) this.errors.unit = null;
    },
    'newIngredients.amount'(val) {
      if (val && !isNaN(val)) this.errors.amount = null;
    }
  },
  methods: {
    // helper functions
    formatAmount(amount, unit) {
      // Remove .00 if present, otherwise return as is
      if (amount == null || amount === '') return '';
      let formatted = parseFloat(amount);
      formatted = Number.isInteger(formatted) ? formatted.toString() : formatted.toFixed(2).replace(/\.00$/, '');
      return `${formatted} ${unit}`.trim();
    },
    handleImageError(event, item) {
      event.target.onerror = null;
      item.image = this.fallbackImage;
      event.target.src = this.fallbackImage;
    },
    resolveImage(path) {
      if (!path) return this.fallbackImage;
      return path.startsWith('/storage') ? path : `/storage/ingredients/${path}`;
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) this.currentPage = page;
    },
    resetModal() {
      this.modalMode = 'create';
      this.editingId = null;
      this.newIngredients = {
        ingredient_name: '',
        measurement_type: '',
        unit: '',
        amount: ''
      };
      this.clearErrors();
    },
    clearErrors() {
      this.errors = {
        ingredient_name: null,
        measurement_type: null,
        unit: null,
        amount: null
      };
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      this.newIngredients.imageFile = file || null;
      this.newIngredients.imagePreview = file ? URL.createObjectURL(file) : null;
    },

    // API calls
    fetchMeasurements() {
      return api.get(ProductIngredients.fetchMeasurements).then(res => {
        this.measures = res.data;
      });
    },
    fetchIngredientsList() {
      this.isLoading = true;
      api
        .get(ProductIngredients.fetchList)
        .then(res => {
          this.ingredients = res.data;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    filteredUnits() {
      const selected = this.measures.find(m => m.type === this.newIngredients.measurement_type);
      return selected ? selected.units : [];
    },
    saveIngredients() {
      this.errors = {
        ingredient_name: !this.newIngredients.ingredient_name ? 'Ingredient name is required.' : null,
        measurement_type: !this.newIngredients.measurement_type ? 'Measurement type is required.' : null,
        unit: !this.newIngredients.unit ? 'Unit is required.' : null,
        amount: (!this.newIngredients.amount || isNaN(this.newIngredients.amount)) ? 'Valid amount is required.' : null
      };

      if (Object.values(this.errors).some(Boolean)) return;

      const formData = new FormData();
      formData.append('ingredient_name', this.newIngredients.ingredient_name);
      formData.append('measurement_type', this.newIngredients.measurement_type);
      formData.append('unit', this.newIngredients.unit);
      formData.append('amount', parseInt(this.newIngredients.amount));
      formData.append('brand', this.newIngredients.brand || '');
      formData.append('category', this.newIngredients.category || '');

      if (this.newIngredients.imageFile) {
        formData.append('image', this.newIngredients.imageFile);
      }

      this.isSaving = true;
      api.post(ProductIngredients.store, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
        .then(res => {
          this.ingredients.unshift(res.data);
          this.newIngredients = {
            ingredient_name: '',
            measurement_type: '',
            unit: '',
            amount: '',
            brand: '',
            category: '',
            imageFile: null
          };
          $('#addIngredientsModal').modal('hide');
          Swal.fire('Success', 'Ingredient added successfully.', 'success');
        })
        .catch(() => {
          Swal.fire('Error', 'Failed to save ingredient.', 'error');
        })
        .finally(() => {
          this.isSaving = false;
        });
    },
    viewIngredient(id) {
      this.fetchMeasurements().then(() => {
        api.get(ProductIngredients.show(id)).then(res => {
          this.newIngredients = {
            ...res.data,
            amount: res.data.amount ? parseInt(res.data.amount) : '',
            imagePreview: res.data.image || null,
            imageFile: null
          };
          this.modalMode = 'view';
          $('#addIngredientsModal').modal('show');
        });
      });
    },
    editIngredient(id) {
      const ingredient = this.ingredients.find(i => i.id === id);
      if (!ingredient) return;

      this.fetchMeasurements().then(() => {
        this.newIngredients = {
          ...ingredient,
          amount: ingredient.amount ? parseInt(ingredient.amount) : '',
          imagePreview: ingredient.image || null,
          imageFile: null
        };
        this.editingId = id;
        this.modalMode = 'edit';
        $('#addIngredientsModal').modal('show');
      });
    },
    updateIngredient() {
      if (!this.editingId) return;

      const formData = new FormData();
      formData.append('_method', 'PUT'); // Tell Laravel to treat as PUT
      formData.append('ingredient_name', this.newIngredients.ingredient_name);
      formData.append('measurement_type', this.newIngredients.measurement_type);
      formData.append('unit', this.newIngredients.unit);
      formData.append('amount', parseInt(this.newIngredients.amount));
      formData.append('brand', this.newIngredients.brand || '');
      formData.append('category', this.newIngredients.category || '');
      if (this.newIngredients.imageFile) {
        formData.append('image', this.newIngredients.imageFile);
      }

      this.isSaving = true;
      api.post(ProductIngredients.update(this.editingId), formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      .then(res => {
        const index = this.ingredients.findIndex(i => i.id === this.editingId);
        if (index !== -1) this.ingredients.splice(index, 1, res.data);
        this.editingId = null;
        $('#addIngredientsModal').modal('hide');
        Swal.fire('Updated', 'Ingredient updated successfully.', 'success');
      })
      .catch(() => {
        Swal.fire('Error', 'Failed to update.', 'error')
      })
      .finally(() => {
        this.isSaving = false;
      });
    },
    deleteIngredient(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the ingredient.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
      }).then(result => {
        if (result.isConfirmed) {
          api.delete(ProductIngredients.delete(id))
            .then(() => {
              this.ingredients = this.ingredients.filter(i => i.id !== id);
              Swal.fire('Deleted', 'Ingredient has been deleted.', 'success');
            })
            .catch(() => Swal.fire('Error', 'Failed to delete.', 'error'));
        }
      });
    }
  },
  mounted() {
    this.fetchMeasurements();
    this.fetchIngredientsList();

    $('#addIngredientsModal').on('hidden.bs.modal', () => {
      this.resetModal();
    });
  }
};
</script>