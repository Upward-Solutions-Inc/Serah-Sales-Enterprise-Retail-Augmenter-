<template>
  <div class="content-wrapper position-relative">
    <div class="row">
      <div class="col-sm-3">
        <h4>Recipes</h4>
      </div>
    </div>

    <div class="col-lg-12 mt-2">
      <div class="datatable d-flex flex-column" style="min-height: 90vh;">
        <!-- Filters -->
        <div class="my-2 row">
          <div class="col-md-6 col-12 mb-2 d-flex align-items-center">
            <p class="text-muted mb-0">
              Showing {{ startItem }} to {{ endItem }} of {{ recipes.length }} items
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
              data-target="#addRecipeModal"
            >
              Add Recipe
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
                <tr v-for="recipe in paginatedRecipes" :key="recipe.id" class="text-center">
                  <td>{{ recipe.id }}</td>
                  <td>{{ recipe.name }}</td>
                  <td>{{ recipe.ingredients }} item(s)</td>
                  <td>
                    <div class="dropdown">
                      <i class="fas fa-ellipsis-v" data-toggle="dropdown" style="cursor: pointer;"></i>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" @click="viewRecipe(recipe)">View</a>
                        <a class="dropdown-item text-danger" href="#" @click="deleteRecipe(recipe.id)">Delete</a>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-if="recipes.length === 0 && !isLoading" class="no-data-found-wrapper text-center p-primary">
              <img src="/images/no_data.svg" alt="" class="mb-primary" />
              <p class="mb-0">Nothing to show here</p>
              <p class="mb-0 text-center text-secondary font-size-90">Please add a new recipe to see content.</p>
            </div>
          </div>

          <!-- Mobile Cards -->
          <div class="d-md-none">
            <div v-if="!isLoading && recipes.length" v-for="recipe in paginatedRecipes" :key="recipe.id" class="card p-3 mb-2">
              <div><strong>ID:</strong> {{ recipe.id }}</div>
              <div><strong>Name:</strong> {{ recipe.name }}</div>
              <div><strong>Ingredients:</strong> {{ recipe.ingredients }} item(s)</div>
              <div class="text-right mt-2">
                <button class="btn btn-sm btn-primary" @click="viewRecipe(recipe)">View</button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <nav v-if="totalPages > 1" class="mt-2">
            <ul class="pagination justify-content-end">
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
          </nav>
        </div>
      </div>
    </div>

    <div class="modal fade" id="addRecipeModal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Create Recipe</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <!-- Product and Category -->
            <div class="form-group">
              <label>Product:</label>
              <select class="form-control" v-model="selectedProduct">
                <option disabled value="">Select product</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
              <div v-if="productError" class="text-danger mt-1"><small>{{ productError }}</small></div>
            </div>

            <div class="form-group">
              <label>Category:</label>
              <select class="form-control" v-model="selectedCategory" :disabled="categoryDisabled">
                <option disabled value="">Select category</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <div v-if="categoryError" class="text-danger mt-1"><small>{{ categoryError }}</small></div>
            </div>

            <!-- Inline Add Ingredient -->
            <div class="form-row align-items-end ingredient-row">
              <div class="form-group col-12 mb-0">
                <div class="d-flex">
                  <div class="flex-grow-1 mr-2">
                    <label>Ingredient:</label>
                    <select class="form-control" v-model="selectedIngredientId">
                      <option disabled value="">Select ingredient</option>
                      <option v-for="ing in allIngredients" :key="ing.id" :value="ing.id">
                        {{ ing.name }} ({{ ing.amount }} {{ ing.unit }})
                      </option>
                    </select>
                    <small v-if="!selectedIngredientId && ingredientError" class="text-danger error-helper">Ingredient is required.</small>
                    <small v-else class="error-helper">&nbsp;</small>
                  </div>
                  <div class="d-flex flex-column" style="min-width:230px;">
                    <label>Amount:</label>
                    <div class="d-flex">
                      <input type="number" class="form-control mr-2" v-model="ingredientAmount" />
                    </div>
                    <small v-if="!ingredientAmount && ingredientError" class="text-danger error-helper">Amount is required.</small>
                    <small v-else-if="amountExceeds" class="text-danger error-helper">Amount exceeds remaining measurement.</small>
                    <small v-else class="error-helper">&nbsp;</small>
                  </div>
                  <div class=" mt-4 flex-grow-0">
                      <button class="mt-2 btn btn-success" style="height: 40px;" @click="confirmAddIngredient">
                        <i class="fas fa-plus"></i>
                        Add
                      </button>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="ingredientError" class="text-danger mb-2"><small>{{ ingredientError }}</small></div>

            <!-- Ingredient List -->
            <div class="row mt-3">
              <div class="col-12">
                <div class="recipe-ingredients-list">
                  <div
                    v-for="(ingredient, index) in recipeIngredients"
                    :key="index"
                    class="d-flex align-items-center mb-2"
                    style="background: transparent; padding: 10px; border-radius: 6px; color: var(--text-color, #e0e0e0);"
                  >
                    <img :src="ingredient.image" class="mr-2" style="width: 24px; height: 24px; filter: brightness(0.8);" />
                    <span class="flex-grow-1">
                      {{ ingredient.name }}
                    </span>
                    <span class="d-flex align-items-center">
                      <span class="ingredient-amount mr-2">{{ ingredient.amount }}</span>
                      <span>{{ ingredient.unit }}</span>
                    </span>
                  </div>
                  <div v-if="recipeError" class="text-danger mt-1"><small>{{ recipeError }}</small></div>
                </div>
              </div>
            </div>

            <!-- Ingredient Error Message -->
            <div v-if="ingredientError" class="alert alert-danger mt-2"><small>{{ ingredientError }}</small></div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
            <button class="btn btn-primary" @click="saveRecipe">Save</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import api, { ProductRecipes } from '../../api.js';
import Loader from '../../components/Loader.vue';
import Swal from 'sweetalert2';

export default {
  name: 'ProductRecipe',
  components: { Loader },
  data() {
    return {
      searchQuery: '',
      isLoading: false,
      currentPage: 1,
      recipesPerPage: 10,

      selectedProduct: '',
      selectedCategory: '',
      categoryDisabled: false,
      recipeIngredients: [],
      products: [],
      categories: [],

      selectedIngredientId: '',
      ingredientAmount: '',
      allIngredients: [],

      headers: ['#', 'Recipe Name', 'Ingredients', 'Action'],
     
      newRecipe: {
        name: '',
        ingredients: ''
      },
      recipes: [],
      editingRecipeId: null,
      isEditMode: false,

      ingredientError: '', // <-- error for ingredient
      productError: '', // <-- error for product
      categoryError: '', // <-- error for category
      recipeError: '', // <-- error for recipe
      amountExceeds: false,
    }
  },
  computed: {
    filteredRecipes() {
      if (!this.searchQuery) return this.recipes
      return this.recipes.filter(recipe =>
        recipe.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    },
    paginatedRecipes() {
      const start = (this.currentPage - 1) * this.recipesPerPage
      return this.filteredRecipes.slice(start, start + this.recipesPerPage)
    },
    totalPages() {
      return Math.ceil(this.filteredRecipes.length / this.recipesPerPage)
    },
    visiblePages() {
      return Array.from({ length: this.totalPages }, (_, i) => i + 1)
    },
    startItem() {
      return (this.currentPage - 1) * this.recipesPerPage + 1
    },
    endItem() {
      return Math.min(this.currentPage * this.recipesPerPage, this.filteredRecipes.length)
    }
  },
  watch: {
    selectedProduct(newVal) {
      const selected = this.products.find(p => p.id === newVal);
      if (selected && selected.category_id) {
        this.selectedCategory = selected.category_id;
        this.categoryDisabled = true;
      } else {
        this.selectedCategory = '';
        this.categoryDisabled = false;
      }
      if (newVal) this.productError = '';
    },
    selectedCategory(newVal) {
      if (newVal) this.categoryError = '';
    },
    selectedIngredientId(newVal) {
      if (newVal && this.ingredientAmount) {
        this.ingredientError = '';
      }
      this.amountExceeds = false; // clear error when changing ingredient
    },
    ingredientAmount(newVal) {
      if (newVal && this.selectedIngredientId) {
        this.ingredientError = '';
      }
      // Check if entered amount exceeds available
      const found = this.allIngredients.find(i => i.id === this.selectedIngredientId);
      if (found && newVal && parseFloat(newVal) > parseFloat(found.amount)) {
        this.amountExceeds = true;
      } else {
        this.amountExceeds = false;
      }
    },
    recipeIngredients(newVal) {
      if (newVal.length > 0) this.recipeError = '';
    }
  },
  mounted() {
    this.fetchProducts();
    this.fetchIngredients();
    this.fetchRecipes();
    // Clear modal fields when modal is closed
    $('#addRecipeModal').on('hidden.bs.modal', () => {
      this.clearRecipeModalFields();
    });
  },
  methods: {
    clearSearch() {
      this.searchQuery = ''
    },
    fetchProducts() {
      this.isLoading = true;
      api.get(ProductRecipes.fetchDropdowns) // <-- use correct endpoint
        .then(res => {
          this.products = res.data.products;
          this.categories = res.data.categories;
        })
        .catch(() => {
          Swal.fire('Error', 'Failed to load product/category data.', 'error');
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    fetchIngredients() {
      api.get(ProductRecipes.fetchIngredients)
        .then(res => {
          this.allIngredients = res.data;
        })
        .catch(() => {
          Swal.fire('Error', 'Failed to load ingredients.', 'error');
        });
    },
    fetchRecipes() {
      this.isLoading = true;
      api.get(ProductRecipes.fetchList)
        .then(res => {
          this.recipes = (res.data.recipes || []).map(r => ({
            id: r.id,
            name: r.product_name || r.name || 'Unnamed',
            ingredients: r.ingredients ? r.ingredients.length : 0
          }));
        })
        .catch(() => {
          Swal.fire('Error', 'Failed to load recipes.', 'error');
        })
        // .catch((error) => {
        //   let message = 'Failed to load recipes.';
        //   if (error.response) {
        //     // Server responded with a status code outside 2xx
        //     message += `\nStatus: ${error.response.status}`;
        //     if (error.response.data && error.response.data.message) {
        //       message += `\nMessage: ${error.response.data.message}`;
        //     } else if (typeof error.response.data === 'string') {
        //       message += `\n${error.response.data}`;
        //     }
        //   } else if (error.request) {
        //     // Request was made but no response received
        //     message += '\nNo response from server.';
        //   } else {
        //     // Something else happened
        //     message += `\n${error.message}`;
        //   }
        //   Swal.fire('Error', message, 'error');
        // })
        .finally(() => {
          this.isLoading = false;
        });
    },

    // core functions
    addIngredientField() {
      this.recipeIngredients.push({
        name: 'New Ingredient',
        image: '/path/to/default.png',
        unit: 'g',
        amount: 0
      })
    },
    getIngredientRemaining(id) {
      const ing = this.allIngredients.find(i => i.id === id);
      return ing ? ing.amount : 0;
    },
    getIngredientUnit(id) {
      const ing = this.allIngredients.find(i => i.id === id);
      return ing ? ing.unit : '';
    },
    confirmAddIngredient() {
      const found = this.allIngredients.find(i => i.id === this.selectedIngredientId)
      if (!found || !this.ingredientAmount) {
        this.ingredientError = 'Please select an ingredient and amount.';
        return;
      }
      if (parseFloat(this.ingredientAmount) > parseFloat(found.amount)) {
        this.amountExceeds = true;
        return;
      } else {
        this.amountExceeds = false;
      }
      this.recipeIngredients.push({
        id: found.id,
        name: found.name,
        unit: found.unit,
        image: found.image || '/placeholder.png',
        amount: this.ingredientAmount
      })
      this.selectedIngredientId = ''
      this.ingredientAmount = ''
      this.ingredientError = '';
      this.amountExceeds = false;
      $('#addIngredientModal').modal('hide')
    },

    viewRecipe(recipe) {
      api.get(ProductRecipes.show(recipe.id)).then(res => {
        const r = res.data;
        this.selectedProduct = r.product_id;
        this.selectedCategory = this.products.find(p => p.id === r.product_id)?.category_id || '';
        this.recipeIngredients = (r.ingredients || []).map(i => ({
          id: i.ingredient_id,
          name: this.allIngredients.find(a => a.id === i.ingredient_id)?.name || '',
          unit: i.unit,
          image: this.allIngredients.find(a => a.id === i.ingredient_id)?.image || '',
          amount: i.amount
        }));
        this.editingRecipeId = r.id;
        this.isEditMode = true;
        $('#addRecipeModal').modal('show');
      });
    },
    deleteRecipe(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the recipe and all its ingredients.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
      }).then(result => {
        if (result.isConfirmed) {
          api.delete(ProductRecipes.delete(id)).then(() => {
            Swal.fire('Deleted!', 'Recipe has been deleted.', 'success');
            this.fetchRecipes();
          }).catch(() => {
            Swal.fire('Error', 'Failed to delete recipe.', 'error');
          });
        }
      });
    },
    saveRecipe() {
      let hasError = false;
      if (!this.selectedProduct) {
        this.productError = 'Product is required.';
        hasError = true;
      } else {
        this.productError = '';
      }
      if (!this.selectedCategory) {
        this.categoryError = 'Category is required.';
        hasError = true;
      } else {
        this.categoryError = '';
      }
      if (!this.recipeIngredients.length) {
        this.recipeError = 'At least one ingredient is required.';
        hasError = true;
      } else {
        this.recipeError = '';
      }
      if (hasError) return;
      // Determine category_id: use selectedCategory if set, else use product's category_id, else null
      let categoryId = this.selectedCategory;
      if (!categoryId) {
        const selectedProduct = this.products.find(p => p.id === this.selectedProduct);
        categoryId = selectedProduct && selectedProduct.category_id ? selectedProduct.category_id : null;
      }
      const payload = {
        product_id: this.selectedProduct,
        category_id: categoryId,
        ingredients: this.recipeIngredients.map(i => ({
          ingredient_id: i.id,
          amount: i.amount,
          unit: i.unit
        }))
      };
      const request = this.isEditMode
        ? api.put(ProductRecipes.update(this.editingRecipeId), payload)
        : api.post(ProductRecipes.store, payload);
      request.then(res => {
        if (res.data.success) {
          Swal.fire('Success', `Recipe ${this.isEditMode ? 'updated' : 'created'} successfully!`, 'success');
          this.fetchRecipes();
          $('#addRecipeModal').modal('hide');
          this.clearRecipeModalFields();
        } else {
          Swal.fire('Error', res.data.message || 'Failed to save recipe.', 'error');
        }
      }).catch(err => {
        Swal.fire('Error', err.response?.data?.message || 'Failed to save recipe.', 'error');
      });
    },
    clearRecipeModalFields() {
      this.selectedProduct = '';
      this.selectedCategory = '';
      this.categoryDisabled = false;
      this.recipeIngredients = [];
      this.selectedIngredientId = '';
      this.ingredientAmount = '';
      this.newRecipe = { name: '', ingredients: '' };
      this.ingredientError = '';
      this.productError = '';
      this.categoryError = '';
      this.recipeError = '';
      this.amountExceeds = false;
      this.editingRecipeId = null;
      this.isEditMode = false;
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
      }
    },
  }
}
</script>

<style scoped>
.error-helper {
  min-height: 20px;
}
.recipe-ingredients-list {
  /* Remove background and set adaptive text color */
  background: transparent !important;
  color: var(--text-color, #e0e0e0);
}
.recipe-ingredients-list input.form-control {
  background: transparent !important;
  color: var(--text-color, #e0e0e0) !important;
  border: 1px solid #444 !important;
}
.recipe-ingredients-list img {
  filter: brightness(0.8);
}
.ingredient-amount {
  display: inline-block;
  min-width: 40px;
  text-align: right;
  background: transparent;
  color: var(--text-color, #e0e0e0);
  border: none;
  font-weight: 500;
  font-size: 1rem;
  margin-right: 4px;
}
</style>