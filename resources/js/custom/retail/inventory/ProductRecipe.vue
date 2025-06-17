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
                  <th class="text-center">#</th>
                  <th class="text-center">Recipe Name</th>
                  <th class="text-center">Ingredients</th>
                  <th class="text-center">Action</th>
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

    <!-- Add Recipe Modal -->
    <div class="modal fade" id="addRecipeModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add Recipe</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Recipe Name:</label>
              <input type="text" class="form-control" v-model="newRecipe.name" />
            </div>
            <div class="form-group">
              <label>Number of Ingredients:</label>
              <input type="number" min="1" class="form-control" v-model="newRecipe.ingredients" />
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
export default {
  name: 'ProductRecipe',
  data() {
    return {
      searchQuery: '',
      isLoading: false,
      currentPage: 1,
      recipesPerPage: 5,
      newRecipe: {
        name: '',
        ingredients: ''
      },
      recipes: [
        { id: 1, name: 'Mocha Cafe', ingredients: 5 },
        { id: 2, name: 'Caramel Latte', ingredients: 4 },
        { id: 3, name: 'Espresso Shot', ingredients: 2 },
        { id: 4, name: 'Vanilla Frappe', ingredients: 6 },
        { id: 5, name: 'Iced Americano', ingredients: 3 }
      ]
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
  methods: {
    clearSearch() {
      this.searchQuery = ''
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
      }
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