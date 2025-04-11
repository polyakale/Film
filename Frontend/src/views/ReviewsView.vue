<template>
  <div class="film-reviews">
    <div class="header-container" ref="headerContainerRef">
      <div class="tabs-wrapper">
        <h3
          class="title-text clickable"
          :class="{ 'active-tab': activeTab === 'all' || !isLoggedIn }"
          @click="switchTab('all')"
          role="tab"
          :aria-selected="activeTab === 'all' || !isLoggedIn"
        >
          All Reviews
        </h3>
        <h3
          v-if="isLoggedIn"
          class="title-text clickable"
          :class="{ 'active-tab': activeTab === 'my' }"
          @click="switchTab('my')"
          role="tab"
          :aria-selected="activeTab === 'my'"
        >
          My Reviews
        </h3>
      </div>

      <div class="controls-container">
        <div class="control-buttons">
          <div
            class="control-item"
            @click="toggleControl('search')"
            title="Search Reviews"
            aria-label="Toggle Search Input"
            :aria-expanded="showSearch"
          >
            <i class="bi bi-search"></i>
          </div>
          <div
            class="control-item"
            @click="
              toggleControl(activeTab === 'my' ? 'tableFilter' : 'publicFilter')
            "
            title="Filter/Sort Reviews"
            aria-label="Toggle Filter/Sort Options"
            :aria-expanded="
              activeTab === 'my' ? showTableFilter : showPublicFilter
            "
          >
            <i class="bi bi-funnel"></i>
          </div>
        </div>

        <transition name="fade-slide">
          <div v-if="showSearch" class="search-container dropdown-panel">
            <div class="search-wrapper">
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search reviews..."
                class="search-input"
                @input="handleSearch"
                aria-label="Search Reviews Input"
              />
            </div>
          </div>
        </transition>

        <transition name="fade-slide">
          <div
            v-if="showTableFilter && isLoggedIn && activeTab === 'my'"
            class="filter-container dropdown-panel"
          >
            <div class="filter-wrapper">
              <label for="tableFilter" class="filter-label">Sort My Reviews:</label>
              <select
                id="tableFilter"
                v-model="tableReviewFilter"
                class="custom-filter-select"
                aria-label="Sort My Reviews"
              >
                <option value="ABC">Film Title (A-Z)</option>
                <option value="highToLow">Evaluation: High to Low</option>
                <option value="lowToHigh">Evaluation: Low to High</option>
                <option value="newest">Date: Newest First</option>
                <option value="oldest">Date: Oldest First</option>
              </select>
            </div>
          </div>
        </transition>

        <transition name="fade-slide">
          <div
            v-if="showPublicFilter && (activeTab === 'all' || !isLoggedIn)"
            class="filter-container dropdown-panel"
          >
            <div class="filter-wrapper">
               <label for="publicFilter" class="filter-label">Sort All Reviews:</label>
              <select
                id="publicFilter"
                v-model="publicReviewFilter"
                class="custom-filter-select"
                aria-label="Sort All Reviews"
              >
                <option value="ABC">Film Title (A-Z)</option>
                <option value="highToLow">Evaluation: High to Low</option>
                <option value="lowToHigh">Evaluation: Low to High</option>
                <option value="newest">Date: Newest First</option>
                <option value="oldest">Date: Oldest First</option>
              </select>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="loading-text">Loading Reviews...</div>
      </div>
    </div>

    <div v-else class="content-area">
      <div
        v-if="errorMessages"
        class="error-message alert"
        role="alert"
      >
        {{ errorMessages }}
      </div>

      <div class="data-container">
        <div v-show="isLoggedIn && activeTab === 'my'" class="table-section">
          <div class="table-wrapper" ref="tableWrapperRef">
            <div
              v-if="!paginatedFavourites.length && filteredFavourites.length === 0 && !searchQuery"
              class="no-data-message"
            >
              You haven't added any reviews yet.
            </div>
            <div
              v-else-if="!paginatedFavourites.length && searchQuery"
              class="no-data-message"
            >
              No reviews found matching your search criteria.
            </div>
            <table v-else class="custom-table">
              <thead>
                <tr>
                  <th>Film</th>
                  <th class="text-center">Evaluation</th>
                  <th>Created</th>
                  <th>Updated</th>
                  <th class="text-center">Operations</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="favourite in paginatedFavourites"
                  :key="favourite.id"
                >
                  <td data-label="Film" class="film-title-cell">
                    {{ favourite.filmTitle || "Unknown Film" }}
                  </td>
                  <td data-label="Evaluation" class="text-center evaluation-cell">
                    <div class="star-rating d-inline-flex align-items-center">
                      <i
                        v-for="starIndex in 5"
                        :key="starIndex"
                        class="bi star-icon"
                        :class="getStarClass(favourite, starIndex)"
                        :aria-label="`Star ${starIndex}`"
                      ></i>
                      <small class="evaluation-text">
                        ({{ formatEvaluation(favourite.evaluation) }})
                      </small>
                    </div>
                  </td>
                  <td data-label="Created" class="date-cell">
                    {{ formatDate(favourite.created_at) }}
                  </td>
                   <td data-label="Updated" class="date-cell">
                    {{ formatDate(favourite.updated_at) }}
                  </td>
                  <td data-label="Operations" class="text-nowrap text-center operations-cell">
                    <OperationsCrud
                      @onClickDelete="onClickDelete(favourite)"
                      @onClickUpdate="onClickUpdate(favourite)"
                      :data="favourite"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            class="pagination-wrapper"
            ref="tablePaginationRef"
            v-if="totalFavPages > 1"
          >
            <Paginator
              :pageNumber="currentPage"
              :numberOfPages="totalFavPages"
              @paging="handlePageChange"
            />
          </div>
        </div>

        <div
          v-show="activeTab === 'all' || !isLoggedIn"
          class="public-reviews-section"
        >
          <div class="reviews-wrapper" ref="reviewsWrapperRef">
            <div
              v-if="!paginatedPublicReviews.length && filteredPublicReviews.length === 0 && !searchQuery"
              class="no-data-message"
            >
              No public reviews available yet.
            </div>
            <div
              v-else-if="!paginatedPublicReviews.length && searchQuery"
              class="no-data-message"
            >
              No reviews found matching your search criteria.
            </div>
            <div v-else class="reviews-grid">
              <div
                v-for="review in paginatedPublicReviews"
                :key="review.id"
                class="review-card"
              >
                <div class="review-header">
                  <div class="review-meta">
                    <span class="review-author"><i class="bi bi-person-fill me-1"></i>{{
                      review.userName || "Anonymous"
                    }}</span>
                    <span class="review-date"><i class="bi bi-calendar3 me-1"></i>{{
                        formatDate(review.updated_at)
                      }}
                      <span v-if="review.created_at !== review.updated_at" class="edited-indicator">(edited)</span>
                    </span>
                  </div>
                </div>
                 <div class="review-film-title">
                   <i class="bi bi-film me-1"></i>{{ review.filmTitle || "Unknown Film" }}
                </div>
                <div class="star-rating d-inline-flex align-items-center">
                  <i
                    v-for="starIndex in 5"
                    :key="`star-${review.id}-${starIndex}`"
                    class="bi star-icon"
                    :class="getStarClass(review, starIndex)"
                    :aria-label="`Star ${starIndex}`"
                  ></i>
                   <small class="evaluation-text">
                        ({{ formatEvaluation(review.evaluation) }})
                    </small>
                </div>
                <div class="review-content">
                  <p>{{ review.content }}</p>
                </div>
              </div>
            </div>
          </div>
           <div
            class="pagination-wrapper"
            ref="publicPaginationRef"
            v-if="totalPublicPages > 1"
          >
            <Paginator
              :pageNumber="currentPublicPage"
              :numberOfPages="totalPublicPages"
              @paging="handlePublicPageChange"
            />
          </div>
        </div>
      </div>
    </div>

    <div v-show="isModalVisible">
      <Modal
        ref="modalRef"
        :title="modalTitle"
        :yes="modalYes"
        :no="modalNo"
        :size="modalSize"
        @yesEvent="yesEventHandler"
        @close="closeModal"
      >
        <div v-if="modalState === 'Delete'" class="modal-delete-message">{{ modalMessageYesNo }}</div>
        <ReviewForm
          v-if="modalState === 'Update'"
          :itemForm="modalItem"
          :films="films"
          :isUpdate="true"
          @saveItem="saveItemHandler"
        />
      </Modal>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useAuthStore } from "@/stores/useAuthStore";
import { BASE_URL } from "../helpers/baseUrls";

// Import Child Components
import Modal from "@/components/Modal.vue";
import Paginator from "@/components/Paginator.vue"; // Assuming this is styled
import OperationsCrud from "@/components/OperationsCrud.vue"; // Needs styling if not done
import ReviewForm from "@/components/forms/ReviewForm.vue"; // Needs styling if not done

export default {
  components: { Paginator, OperationsCrud, Modal, ReviewForm },
  data() {
    return {
      // Component state properties
      authStore: null,
      loading: false,
      errorMessages: null,
      activeTab: "my", // Default to 'my' if logged in, else 'all'
      tableReviewFilter: "newest", // Default sort for 'my' reviews table
      publicReviewFilter: "newest", // Default sort for 'all' reviews
      searchQuery: "",
      debounceTimer: null,
      films: [],
      favourites: [], // User's reviews ('my' tab)
      publicReviews: [], // All public reviews ('all' tab)

      // --- Pagination ---
      currentPage: 1, // Current page for 'my' reviews
      currentPublicPage: 1, // Current page for 'all' reviews
      itemsPerPage: 9, // ** FIXED number of items per page **

      // Controls visibility
      showSearch: false,
      showTableFilter: false,
      showPublicFilter: false,

      // Modal state properties
      isModalVisible: false,
      modalItem: {},
      modalMessageYesNo: null,
      modalState: "Read",
      modalTitle: null,
      modalYes: null,
      modalNo: null,
      modalSize: null,
      selectedRowId: null,
    };
  },
  computed: {
    /**
     * Checks if the user is currently logged in.
     */
    isLoggedIn() {
      return !!this.authStore?.id; // Simplified check
    },

    /**
     * Filters and sorts the user's favourite reviews.
     */
    filteredFavourites() {
      return this.filterAndSortReviews(
        this.favourites,
        this.searchQuery,
        this.tableReviewFilter,
        false // isPublic = false
      );
    },

    /**
     * Filters and sorts all public reviews.
     */
    filteredPublicReviews() {
      return this.filterAndSortReviews(
        this.publicReviews,
        this.searchQuery,
        this.publicReviewFilter,
        true // isPublic = true
      );
    },

    // --- Pagination Computed Properties (Using fixed itemsPerPage) ---

    /**
     * Calculates total pages for 'My Reviews'.
     */
    totalFavPages() {
      return Math.ceil(this.filteredFavourites.length / this.itemsPerPage);
    },

    /**
     * Gets the slice of 'My Reviews' for the current page.
     */
    paginatedFavourites() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredFavourites.slice(start, end);
    },

    /**
     * Calculates total pages for 'All Reviews'.
     */
    totalPublicPages() {
      return Math.ceil(this.filteredPublicReviews.length / this.itemsPerPage);
    },

    /**
     * Gets the slice of 'All Reviews' for the current page.
     */
    paginatedPublicReviews() {
      const start = (this.currentPublicPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredPublicReviews.slice(start, end);
    },
  },
  methods: {
    /**
     * Internal helper method for making API calls using Axios.
     */
    async _fetchApi(url, config = {}, method = "get", data = null) {
      this.loading = true;
      this.errorMessages = null;
      const token = this.authStore?.token;
      const headers = {
        Accept: "application/json",
        ...(method !== "get" &&
          method !== "delete" && { "Content-Type": "application/json" }),
        ...(token && { Authorization: `Bearer ${token}` }),
        ...config.headers,
      };

      try {
        const axiosConfig = { ...config, headers, method, url };
        if (data) {
          axiosConfig.data = data;
        }
        const response = await axios(axiosConfig);
        return response.data;
      } catch (error) {
        console.error(
          `API Error (${method.toUpperCase()} ${url}):`,
          error.response || error
        );
        this.errorMessages = `Operation failed: ${
          error.response?.data?.message ||
          error.message ||
          "An unknown error occurred."
        }`;
         // Automatically clear error after some time
        setTimeout(() => { this.errorMessages = null; }, 7000);
        return null;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Fetches the logged-in user's favourite reviews.
     */
    async fetchFavourites() {
      if (!this.isLoggedIn) return;
      const userId = this.authStore.id;
      const responseData = await this._fetchApi(
        `${BASE_URL}/favouritesByUserId/${userId}`
      );
      if (responseData?.data) {
        this.favourites = responseData.data.map((fav) => ({
          ...fav,
          evaluation: Number(fav.evaluation) || 0,
        }));
      } else {
        this.favourites = [];
      }
      // Reset page if current page becomes invalid after fetch/filter
      if (this.currentPage > this.totalFavPages) {
        this.currentPage = Math.max(1, this.totalFavPages);
      }
    },

    /**
     * Fetches all public reviews.
     */
    async fetchAllReviews() {
      const responseData = await this._fetchApi(`${BASE_URL}/favourites`);
      if (responseData?.data) {
        this.publicReviews = responseData.data.map((review) => ({
          ...review,
          userName: review.userName || "Anonymous",
          filmTitle: review.filmTitle || "Unknown Film",
          evaluation: Number(review.evaluation) || 0,
          content: review.content,
        }));
      } else {
        this.publicReviews = [];
      }
       // Reset page if current page becomes invalid after fetch/filter
       if (this.currentPublicPage > this.totalPublicPages) {
        this.currentPublicPage = Math.max(1, this.totalPublicPages);
      }
    },

    /**
     * Fetches the list of films.
     */
    async fetchFilms() {
      const responseData = await this._fetchApi(`${BASE_URL}/films`);
      this.films = responseData?.data || [];
    },

    /**
     * Switches the active tab.
     */
    switchTab(tab) {
      if (tab === "my" && !this.isLoggedIn) return; // Cannot switch to 'my' if not logged in

      this.activeTab = tab;
      this.currentPage = 1; // Reset pagination for both on tab switch
      this.currentPublicPage = 1;
      this.searchQuery = ""; // Clear search on tab switch
      this.errorMessages = null; // Clear errors

      // Close any open controls
      this.showSearch = false;
      this.showTableFilter = false;
      this.showPublicFilter = false;

      // Fetch data if needed
      if (tab === "all" && this.publicReviews.length === 0) {
        this.fetchAllReviews();
      } else if (tab === "my" && this.favourites.length === 0 && this.isLoggedIn) {
        this.fetchFavourites();
      }
    },

    /**
     * Handles input in the search field with debouncing.
     */
    handleSearch() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        // Reset pagination when search query changes
        this.currentPage = 1;
        this.currentPublicPage = 1;
      }, 300); // 300ms debounce time
    },

    /**
     * Toggles the visibility of the search or filter dropdowns, ensuring only one is open.
     */
    toggleControl(type) {
        // Determine the target state for the clicked control
        let nextShowSearch = this.showSearch;
        let nextShowTableFilter = this.showTableFilter;
        let nextShowPublicFilter = this.showPublicFilter;

        if (type === 'search') {
            nextShowSearch = !this.showSearch; // Toggle the clicked one
            // If opening search, close others
            if (nextShowSearch) {
                nextShowTableFilter = false;
                nextShowPublicFilter = false;
            }
        } else if (type === 'tableFilter') {
            nextShowTableFilter = !this.showTableFilter;
            // If opening table filter, close others
            if (nextShowTableFilter) {
                nextShowSearch = false;
                nextShowPublicFilter = false;
            }
        } else if (type === 'publicFilter') {
            nextShowPublicFilter = !this.showPublicFilter;
            // If opening public filter, close others
            if (nextShowPublicFilter) {
                nextShowSearch = false;
                nextShowTableFilter = false;
            }
        }

        // Apply the new states
        this.showSearch = nextShowSearch;
        this.showTableFilter = nextShowTableFilter;
        this.showPublicFilter = nextShowPublicFilter;
    },


    // --- CRUD Methods ---

    /**
     * Sets up and shows the delete confirmation modal.
     */
    onClickDelete(item) {
      this.modalState = "Delete";
      this.modalTitle = "Confirm Deletion";
      this.modalMessageYesNo = `Are you sure you want to delete your review for "${
        item.filmTitle || "this film"
      }"? This action cannot be undone.`;
      this.modalYes = "Yes, Delete";
      this.modalNo = "No, Cancel";
      this.modalSize = null; // Default size
      this.selectedRowId = item.id;
      this.isModalVisible = true;
    },

    /**
     * Sets up and shows the update review modal.
     */
    onClickUpdate(item) {
      this.modalState = "Update";
      this.selectedRowId = item.id;
      // Ensure evaluation is a number for the form
      this.modalItem = {
        ...item,
        evaluation: Number(item.evaluation) || 0,
      };
      this.modalTitle = `Update Review for "${
        item.filmTitle || "Unknown Film"
      }"`;
      this.modalYes = null; // Hide Yes button for update form
      this.modalNo = "Cancel";
      this.modalSize = "lg"; // Larger modal for the form
      this.isModalVisible = true;
    },

    /**
     * Performs the delete API call.
     */
    async deleteReview() {
      if (!this.selectedRowId) return;
      const result = await this._fetchApi(
        `${BASE_URL}/favourites/${this.selectedRowId}`, {}, "delete"
      );
      if (result !== null) {
        await this.fetchFavourites(); // Refresh list
      }
      this.closeModal(); // Close modal regardless of success/failure
    },

    /**
     * Handles saving the updated review from the form.
     */
    async saveItemHandler(formData) {
      if (this.modalState !== "Update" || !this.selectedRowId) return;
      const payload = {
        evaluation: formData.evaluation,
        filmId: formData.filmId,
        content: formData.content,
      };
      const result = await this._fetchApi(
        `${BASE_URL}/favourites/${this.selectedRowId}`, {}, "patch", payload
      );
      if (result !== null) {
        await this.fetchFavourites(); // Refresh list
      }
      this.closeModal(); // Close modal regardless of success/failure
    },

    /**
     * Handles the 'yes' button click in the modal (only for Delete).
     */
    yesEventHandler() {
      if (this.modalState === "Delete") {
        this.deleteReview();
      }
    },

    /**
     * Closes the modal and resets its state.
     */
    closeModal() {
      this.isModalVisible = false;
      // Use timeout to prevent state reset before modal fade-out completes
      setTimeout(() => {
        this.modalState = "Read";
        this.modalItem = {};
        this.selectedRowId = null;
        this.modalTitle = null;
        this.modalMessageYesNo = null;
        this.modalYes = null;
        this.modalNo = null;
        this.modalSize = null;
      }, 300); // Adjust timing if needed (matches Bootstrap fade duration)
    },

    // --- Helper & Formatting Methods ---

    /**
     * Shared method to filter and sort reviews.
     */
    filterAndSortReviews(sourceArray, query, filterType, isPublic = false) {
      let filtered = [...sourceArray];

      // Apply search query filter
      if (query) {
        const lowerQuery = query.toLowerCase();
        filtered = filtered.filter(review =>
          (review.filmTitle || "").toLowerCase().includes(lowerQuery) ||
          (review.userName || "").toLowerCase().includes(lowerQuery) || // Search username too
          (!isPublic && (review.content || "").toLowerCase().includes(lowerQuery)) // Search content only for 'my' reviews
        );
      }

      // Apply sorting
      switch (filterType) {
        case "ABC":
          filtered.sort((a, b) => (a.filmTitle || "").localeCompare(b.filmTitle || ""));
          break;
        case "highToLow":
          filtered.sort((a, b) => (Number(b.evaluation) || 0) - (Number(a.evaluation) || 0));
          break;
        case "lowToHigh":
          filtered.sort((a, b) => (Number(a.evaluation) || 0) - (Number(b.evaluation) || 0));
          break;
        case "newest":
          // Sort by updated_at primarily, then created_at as fallback
          filtered.sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at));
          break;
        case "oldest":
           // Sort by created_at primarily, then updated_at as fallback
          filtered.sort((a, b) => new Date(a.created_at || a.updated_at) - new Date(b.created_at || b.updated_at));
          break;
      }
      return filtered;
    },

    /**
     * Determines the appropriate Bootstrap Icon class for star ratings.
     */
    getStarClass(item, starIndex) {
      const evaluation = Number(item.evaluation) || 0;
      if (evaluation >= starIndex) return "bi-star-fill";
      if (evaluation >= starIndex - 0.5) return "bi-star-half"; // Use half star for .5 ratings
      return "bi-star";
    },

    /**
     * Formats the evaluation number to one decimal place.
     */
    formatEvaluation(value) {
      const num = Number(value);
      return isNaN(num) ? "N/A" : num.toFixed(1);
    },

    /**
     * Formats a date string into 'YYYY Month DD - HH:MM:SS' format (using English month names).
     */
    formatDate(dateString) {
      if (!dateString) return "N/A";
      try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "Invalid Date";

        // Define options for formatting
        const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };

        // Format parts using English locale for consistency with example "July"
        const formattedDate = new Intl.DateTimeFormat('en-US', dateOptions).format(date);
        const formattedTime = new Intl.DateTimeFormat('en-US', timeOptions).format(date);

        // Combine parts with the desired separator
        return `${formattedDate} - ${formattedTime}`;

      } catch (e) {
        console.warn("Error formatting date:", dateString, e);
        return "N/A"; // Fallback
      }
    },


    // --- Pagination Event Handlers ---

    /**
     * Handles page change for 'My Reviews'.
     */
    handlePageChange(pageInfo) {
      if (typeof pageInfo.pageNumber === "number") {
        this.currentPage = pageInfo.pageNumber;
      }
    },

    /**
     * Handles page change for 'All Reviews'.
     */
    handlePublicPageChange(pageInfo) {
      if (typeof pageInfo.pageNumber === "number") {
        this.currentPublicPage = pageInfo.pageNumber;
      }
    },
  },
  watch: {
    /**
     * Watches for changes in the login state to update UI/data.
     */
    isLoggedIn(loggedIn, wasLoggedIn) {
        // If user logs out, switch to 'all' tab and clear personal data
      if (!loggedIn && wasLoggedIn) {
        this.activeTab = 'all';
        this.favourites = [];
        // Fetch all reviews if not already loaded
        if (this.publicReviews.length === 0) {
            this.fetchAllReviews();
        }
      }
      // If user logs in, potentially fetch their reviews if 'my' tab is active
      else if (loggedIn && !wasLoggedIn) {
        this.activeTab = 'my'; // Default to 'my' tab on login
        this.fetchFavourites();
      }
    },

    /**
     * Watches the 'My Reviews' filter/sort selection.
     */
    tableReviewFilter() {
      this.currentPage = 1; // Reset page when filter changes
    },

    /**
     * Watches the 'All Reviews' filter/sort selection.
     */
    publicReviewFilter() {
      this.currentPublicPage = 1; // Reset page when filter changes
    },
  },
  /**
   * Lifecycle hook called after the component instance has been mounted.
   */
  mounted() {
    this.authStore = useAuthStore();
    // Set initial tab based on login state
    this.activeTab = this.isLoggedIn ? "my" : "all";

    // Fetch initial data based on active tab
    this.fetchFilms(); // Always fetch films for the update form
    if (this.activeTab === "my") {
      this.fetchFavourites();
    } else {
      this.fetchAllReviews();
    }
    // Removed resize listener and dynamic calculation
  },
  /**
   * Lifecycle hook called right before the component instance is unmounted (Vue 3).
   */
  beforeUnmount() {
    // Removed resize listener cleanup
    clearTimeout(this.debounceTimer); // Clear any pending debounce timers
  },
};
</script>

<style scoped>
/* === Define a Consistent Color Palette via CSS Variables === */
.film-reviews {
  /* Central Color Variables for a Dark Theme */
  --bg-base: #121212;        /* Base background */
  --bg-primary: #1f1f1f;       /* Header and similar elements */
  --bg-secondary: #2a2a2a;     /* Dropdowns and secondary backgrounds */
  --bg-tertiary: #333333;      /* Cards and content backgrounds */
  --bg-dropdown: #1f1f1f;      /* Dropdown panels */

  --text-primary: #ffffff;     /* Primary text – white */
  --text-secondary: #eeeeee;   /* Secondary text */
  --text-muted: #aaaaaa;       /* Muted text (for auxiliary info) */

  --accent-gold: #ffd700;      /* Gold accent (for highlights, buttons, etc.) */
  --accent-red: #ff4d4d;       /* Red accent for error messages */
  --border-color: #444444;     /* General border color */
  --focus-ring-color: rgba(255, 215, 0, 0.4); /* For focus outlines */

  height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background-color: var(--bg-base);
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI',
    Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

/* === Header Section === */
.header-container {
  background: var(--bg-primary);
  border-bottom: 2px solid var(--accent-gold);
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.5rem;
  gap: 1rem;
  flex-shrink: 0;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.tabs-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-grow: 1;
  min-width: 0;
}

.title-text {
  color: var(--text-primary);
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
  padding: 0.6rem 1.1rem;
  white-space: nowrap;
  text-align: center;
  border-radius: 6px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.clickable:hover {
  color: var(--accent-gold);
  background-color: var(--bg-secondary);
  border-color: var(--border-color);
}

.active-tab {
  background: var(--accent-gold) !important;
  color: var(--bg-primary) !important;
  font-weight: 700;
  border-color: var(--accent-gold) !important;
  box-shadow: 0 1px 4px rgba(255, 215, 0, 0.2);
}

/* === Controls (Search/Filter) === */
.controls-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  position: relative;
  flex-shrink: 0;
}

.control-buttons {
  display: flex;
  gap: 0.5rem;
}

.control-item {
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-secondary);
  color: var(--text-muted);
  transition: all 0.2s ease;
  border: 1px solid var(--border-color);
}

.control-item:hover {
  background: var(--accent-gold);
  color: var(--bg-primary);
  border-color: var(--accent-gold);
}

.control-item i {
  font-size: 1rem;
  transition: transform 0.2s ease;
}

/* === Dropdown Panels (Search/Filter) === */
.dropdown-panel {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  z-index: 100;
  background-color: var(--bg-dropdown);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
  min-width: 280px;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.search-input {
  width: 100%;
  padding: 0.6rem 1rem;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  background-color: var(--bg-secondary);
  color: var(--text-secondary);
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.search-input::placeholder {
  color: #888;
}
.search-input:focus {
  border-color: var(--accent-gold);
  box-shadow: 0 0 0 3px var(--focus-ring-color);
}

.filter-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 0.4rem;
}

.custom-filter-select {
  width: 100%;
  padding: 0.6rem 2.5rem 0.6rem 1rem;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  background-color: var(--bg-secondary);
  color: var(--text-secondary);
  font-size: 0.95rem;
  cursor: pointer;
  outline: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23b0b0b0' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1em 1em;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.custom-filter-select:focus {
  border-color: var(--accent-gold);
  box-shadow: 0 0 0 3px var(--focus-ring-color);
}

/* === Loading Overlay === */
.loading-overlay {
  position: absolute;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 150;
}
.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: var(--text-secondary);
  gap: 1rem;
}
.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 215, 0, 0.3);
  border-radius: 50%;
  border-top-color: var(--accent-gold);
  animation: spin 1s linear infinite;
}
.loading-text {
  font-size: 1.1rem;
  font-weight: 600;
}

/* === Content Area === */
.content-area {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  padding: 1rem 1.5rem;
  gap: 1rem;
}

.error-message.alert {
  flex-shrink: 0;
  background-color: rgba(204, 24, 30, 0.15);
  color: var(--accent-red);
  padding: 0.75rem 1.25rem;
  border-radius: 6px;
  border: 1px solid var(--accent-red);
  font-weight: 600;
}

.data-container {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* === Table & Reviews Sections === */
.table-section,
.public-reviews-section {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  height: 100%;
}

/* Scrollable Content Wrappers */
.table-wrapper,
.reviews-wrapper {
  flex-grow: 1;
  overflow-y: auto;
  padding-right: 8px;
  scrollbar-width: thin;
  scrollbar-color: #555 #333;
}
.table-wrapper::-webkit-scrollbar,
.reviews-wrapper::-webkit-scrollbar {
  width: 8px;
}
.table-wrapper::-webkit-scrollbar-track,
.reviews-wrapper::-webkit-scrollbar-track {
  background: #333;
  border-radius: 4px;
}
.table-wrapper::-webkit-scrollbar-thumb,
.reviews-wrapper::-webkit-scrollbar-thumb {
  background-color: #555;
  border-radius: 4px;
  border: 2px solid #333;
}

/* === Improved Table Styles with Full Grid Borders === */
.custom-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}

.custom-table th,
.custom-table td {
  padding: 1rem;
  border: 1px solid var(--border-color);
}

.custom-table th {
  background: var(--bg-secondary);
  color: var(--accent-gold);
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  text-align: left;
  /* Optional: subtle vertical gradient for depth */
  background-image: linear-gradient(180deg, var(--bg-secondary), var(--bg-primary));
}

.custom-table td {
  background: var(--bg-primary);
  color: var(--text-primary);
}

/* Hover effects on rows */
.custom-table tbody tr:hover {
  background-color: #2f2f2f;
  transition: background-color 0.3s ease;
}

/* Optional: enforce minimum widths for specific cells */
.custom-table .evaluation-cell {
  min-width: 150px;
}
.custom-table .operations-cell {
  min-width: 100px;
}

/* Specific cell styling */
.custom-table .film-title-cell { 
  font-weight: 600; 
  color: var(--text-primary);
}

.custom-table .date-cell {
  font-size: 0.9em;
  color: var(--text-muted);
}

/* === Star Rating === */
.star-rating {
  font-size: 1rem;
}
.star-icon {
  color: var(--accent-gold);
  margin: 0 1px;
}
.evaluation-text,
.date-cell {
  color: var(--text-muted);
}

/* === Public Reviews Card Styles === */
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
  padding-bottom: 1rem;
}
.review-card {
  background: var(--bg-tertiary);
  border-radius: 8px;
  border: 1px solid var(--border-color);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.4);
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  color: var(--text-primary);
  text-align: left;
}
.review-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.5);
}
.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.review-meta {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  font-size: 0.8rem;
  color: var(--text-muted);
}
.review-author {
  font-weight: 600;
  color: var(--text-primary);
  display: flex;
  align-items: center;
}
.review-date {
  display: flex;
  align-items: center;
}
.edited-indicator {
  font-style: italic;
  margin-left: 0.3em;
  font-size: 0.9em;
}
.review-film-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--accent-gold);
  display: flex;
  align-items: center;
}
.review-content {
  font-size: 0.9rem;
  color: var(--text-primary);
  line-height: 1.5;
  margin-top: 0.25rem;
}
.review-content p {
  margin: 0;
}

/* === No Data Message === */
.no-data-message {
  text-align: center;
  padding: 4rem 1rem;
  color: #888;
  font-size: 1.1rem;
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* === Pagination Wrapper === */
.pagination-wrapper {
  padding: 1rem 0 0.5rem 0;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
  border-top: 1px solid var(--border-color);
  margin-top: auto;
  background: var(--bg-primary);
}

/* === Modal Delete Message === */
.modal-delete-message {
  color: var(--text-secondary);
  font-size: 1.1rem;
  padding: 1rem 0;
  text-align: center;
}

/* === Responsive Adjustments === */
@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    align-items: stretch;
    padding: 0.75rem 1rem;
  }
  .tabs-wrapper {
    justify-content: center;
    width: 100%;
  }
  .controls-container {
    width: 100%;
    justify-content: center;
  }
  .dropdown-panel {
    width: calc(100% - 2rem);
    left: 1rem;
    right: 1rem;
    min-width: unset;
  }
  .content-area {
    padding: 1rem;
  }
  .custom-table thead {
    display: none;
  }
  .custom-table tbody,
  .custom-table tr,
  .custom-table td {
    display: block;
    width: 100%;
  }
  .custom-table tr {
    border: 1px solid var(--border-color);
    border-radius: 6px;
    margin-bottom: 1rem;
    padding: 0.5rem;
    background: var(--bg-secondary);
  }
  .custom-table td {
    text-align: right;
    padding: 0.6rem 0.5rem 0.6rem 50%;
    position: relative;
    border-bottom: 1px dashed var(--border-color);
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }
  .custom-table tr td:last-child {
    border-bottom: none;
  }
  .custom-table td::before {
    content: attr(data-label);
    position: absolute;
    left: 10px;
    width: calc(50% - 20px);
    padding-right: 10px;
    white-space: nowrap;
    text-align: left;
    font-weight: bold;
    color: var(--accent-gold);
    font-size: 0.85rem;
  }
  .custom-table td.text-center {
    text-align: right;
  }
  .custom-table td.text-center::before {
    text-align: left;
  }
  .custom-table .evaluation-cell .star-rating {
    justify-content: flex-end;
  }
  .custom-table .operations-cell {
    padding-left: 10px;
    justify-content: center;
  }
  .custom-table .operations-cell::before {
    display: none;
  }
  .reviews-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .title-text {
    font-size: 0.9rem;
    padding: 0.5rem 0.8rem;
  }
  .control-item {
    width: 32px;
    height: 32px;
  }
  .control-item i {
    font-size: 0.9rem;
  }
  .dropdown-panel {
    padding: 0.75rem;
  }
  .search-input,
  .custom-filter-select {
    font-size: 0.9rem;
    padding: 0.5rem 0.8rem;
  }
  .custom-filter-select {
    padding-right: 2rem;
  }
  .custom-table td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }
  .review-card {
    padding: 0.8rem 1rem;
  }
}

/* Spinner Animation */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
