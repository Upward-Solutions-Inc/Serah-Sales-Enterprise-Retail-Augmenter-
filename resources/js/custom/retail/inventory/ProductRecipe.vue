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
            </div>

            <div class="form-group">
              <label>Category:</label>
              <select class="form-control" v-model="selectedCategory" disabled>
                <option disabled value="">Select category</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>

            <!-- Inline Add Ingredient -->
            <div class="form-row align-items-end">
              <div class="form-group col-md-6">
                <label>Ingredient:</label>
                <select class="form-control" v-model="selectedIngredientId">
                  <option disabled value="">Select ingredient</option>
                  <option v-for="ing in allIngredients" :key="ing.id" :value="ing.id">
                    {{ ing.name }} ({{ ing.unit }})
                  </option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label>Amount:</label>
                <input type="number" class="form-control" v-model="ingredientAmount" />
              </div>
              <div class="form-group col-md-2">
                <button class="btn btn-success w-100" @click="confirmAddIngredient">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>

            <!-- Ingredient List -->
            <div class="recipe-ingredients-list mt-3">
              <div
                v-for="(ingredient, index) in recipeIngredients"
                :key="index"
                class="d-flex align-items-center mb-2"
                style="background: #fdf9ef; padding: 10px; border-radius: 6px;"
              >
                <img :src="ingredient.image" class="mr-2" style="width: 24px; height: 24px;" />
                <span class="flex-grow-1">
                  <strong>(C)</strong> {{ ingredient.name }}
                </span>
                <input
                  type="number"
                  class="form-control text-right mx-2"
                  style="width: 80px;"
                  v-model="ingredient.amount"
                />
                <span>{{ ingredient.unit }}</span>
              </div>
            </div>
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
      recipes: []
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
      } else {
        this.selectedCategory = '';
      }
    }
  },
  mounted() {
    this.fetchProducts();
    this.fetchIngredients();
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
    fetchProducts() {
      this.isLoading = true;
      api.get(ProductRecipes.fetchProducts)
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

    // core functions
    addIngredientField() {
      this.recipeIngredients.push({
        name: 'New Ingredient',
        image: '/path/to/default.png',
        unit: 'g',
        amount: 0
      })
    },
    confirmAddIngredient() {
      const found = this.allIngredients.find(i => i.id === this.selectedIngredientId)
      if (!found || !this.ingredientAmount) return Swal.fire('Error', 'Please select an ingredient and amount.', 'error')

      this.recipeIngredients.push({
        id: found.id,
        name: found.name,
        unit: found.unit,
        image: found.image || '/placeholder.png',
        amount: this.ingredientAmount
      })

      this.selectedIngredientId = ''
      this.ingredientAmount = ''
      $('#addIngredientModal').modal('hide')
    },

    viewRecipe(recipe) {
      alert(`Viewing recipe: ${recipe.name}`)
    },
    deleteRecipe(id) {
      this.recipes = this.recipes.filter(r => r.id !== id)
    },
    saveRecipe() {
      if (!this.newRecipe.name || !this.newRecipe.ingredients) {
        alert('Please fill out all fields.')
        return
      }
      const id = this.recipes.length + 1
      this.recipes.push({ id, ...this.newRecipe })
      this.newRecipe = { name: '', ingredients: '' }
      $('#addRecipeModal').modal('hide')
    }
  }
}
</script>