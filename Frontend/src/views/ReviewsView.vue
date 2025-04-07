<template>
  <div class="film-reviews">
    <div class="header-container" ref="headerContainerRef">
      <div class="tabs-wrapper">
        <h3 class="title-text clickable" :class="{ 'active-tab': activeTab === 'all' || !isLoggedIn }"
          @click="switchTab('all')" role="tab" :aria-selected="activeTab === 'all' || !isLoggedIn">
          All Reviews
        </h3>
        <h3 v-if="isLoggedIn" class="title-text clickable" :class="{ 'active-tab': activeTab === 'my' }"
          @click="switchTab('my')" role="tab" :aria-selected="activeTab === 'my'">
          My Reviews
        </h3>
      </div>

      <div class="controls-container">
        <div class="control-buttons">
          <div class="control-item" @click="toggleControl('search')" title="Search Reviews"
            aria-label="Toggle Search Input" :aria-expanded="showSearch">
            <i class="bi bi-search"></i>
          </div>
          <div class="control-item" @click="
            toggleControl(activeTab === 'my' ? 'tableFilter' : 'publicFilter')
            " title="Filter Reviews" aria-label="Toggle Filter Options" :aria-expanded="activeTab === 'my' ? showTableFilter : showPublicFilter
              ">
            <i class="bi bi-funnel"></i>
          </div>
        </div>

        <transition name="fade-slide">
          <div v-if="showSearch" class="search-container">
            <div class="search-wrapper">
              <input type="text" v-model="searchQuery" placeholder="Search reviews..." class="search-input"
                @input="handleSearch" aria-label="Search Reviews Input" />
            </div>
          </div>
        </transition>

        <transition name="fade-slide">
          <div v-if="showTableFilter && isLoggedIn && activeTab === 'my'" class="filter-container">
            <div class="filter-wrapper position-relative">
              <select id="tableFilter" v-model="tableReviewFilter" class="custom-filter-select"
                aria-label="Sort My Reviews">
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
          <div v-if="showPublicFilter && (activeTab === 'all' || !isLoggedIn)" class="filter-container">
            <div class="filter-wrapper position-relative">
              <select id="publicFilter" v-model="publicReviewFilter" class="custom-filter-select"
                aria-label="Sort All Reviews">
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
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="loading-text">Loading...</div>
      </div>
    </div>

    <div v-else class="content-area">
      <div v-if="errorMessages" class="error-message alert alert-danger" role="alert">
        {{ errorMessages }}
      </div>

      <div class="data-container">
        <div v-show="isLoggedIn && activeTab === 'my'" class="table-section">
          <div class="table-wrapper" ref="tableWrapperRef">
            <div v-if="
              !paginatedFavourites.length &&
              filteredFavourites.length === 0 &&
              !searchQuery
            " class="no-data-message">
              You haven't added any reviews yet.
            </div>
            <div v-else-if="!paginatedFavourites.length && searchQuery" class="no-data-message">
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
                <tr v-for="favourite in paginatedFavourites" :key="favourite.id">
                  <td data-label="Film" class="film">
                    {{ favourite.filmTitle || "Unknown Film" }}
                  </td>
                  <td data-label="Evaluation" class="text-center">
                    <div class="star-rating d-inline-flex align-items-center">
                      <i v-for="starIndex in 5" :key="starIndex" class="bi mx-1 text-warning"
                        :class="getStarClass(favourite, starIndex)" :aria-label="`Star ${starIndex}`"></i>
                      <small class="text-muted ms-2">
                        ({{ formatEvaluation(favourite.evaluation) }})
                      </small>
                    </div>
                  </td>
                  <td data-label="Date" class="date">
                    {{ formatDate(favourite.created_at) }}
                  </td>
                  <td data-label="Date" class="date">
                    {{ formatDate(favourite.updated_at) }}
                  </td>
                  <td class="text-nowrap text-center">
                    <OperationsCrud @onClickDelete="onClickDelete(favourite)" @onClickUpdate="onClickUpdate(favourite)"
                      :data="favourite" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pagination-container" ref="tablePaginationRef"
            v-if="filteredFavourites.length > dynamicItemsPerPage">
            <Paginator :pageNumber="currentPage" :numberOfPages="totalFavPages" :pagesArray="favPagesArray"
              @paging="handlePageChange" />
          </div>
        </div>

        <div v-show="activeTab === 'all' || !isLoggedIn" class="public-reviews-section">
          <div class="reviews-wrapper" ref="reviewsWrapperRef">
            <div v-if="
              !paginatedPublicReviews.length &&
              filteredPublicReviews.length === 0 &&
              !searchQuery
            " class="no-data-message">
              No public reviews available yet.
            </div>
            <div v-else-if="!paginatedPublicReviews.length && searchQuery" class="no-data-message">
              No reviews found matching your search criteria.
            </div>
            <div v-else class="reviews-grid">
              <div v-for="review in paginatedPublicReviews" :key="review.id" class="review-card">
                <div class="review-header">
                  <div class="review-meta">
                    <span class="review-author">{{
                      review.userName || "Anonymous"
                    }}</span>
                    <div v-if="review.created_at !== review.updated_at">
                      <span class="review-date">{{
                        formatDate(review.updated_at)
                      }}</span> (edited)
                    </div>
                    <div v-else>
                      <span class="review-date">{{
                        formatDate(review.created_at)
                      }}</span>
                    </div>
                  </div>
                </div>
                <div class="review-film-title">
                  {{ review.filmTitle || "Unknown Film" }}
                </div>
                <div class="review-content">
                  {{ review.content }}
                </div>
                <div class="star-rating d-inline-flex align-items-center">
                  <i v-for="starIndex in 5" :key="`star-${review.id}-${starIndex}`" class="bi mx-1 text-warning"
                    :class="getStarClass(review, starIndex)" :aria-label="`Star ${starIndex}`"></i>
                  <small class="text-muted ms-2">
                    ({{ formatEvaluation(review.evaluation) }})
                  </small>
                </div>
              </div>
            </div>
          </div>
          <div class="pagination-container" ref="publicPaginationRef"
            v-if="filteredPublicReviews.length > dynamicItemsPerPage">
            <Paginator :pageNumber="currentPublicPage" :numberOfPages="totalPublicPages" :pagesArray="publicPagesArray"
              @paging="handlePublicPageChange" />
          </div>
        </div>
      </div>
    </div>

    <div v-show="isModalVisible">
      <Modal ref="modalRef" :title="modalTitle" :yes="modalYes" :no="modalNo" :size="modalSize"
        @yesEvent="yesEventHandler" @close="closeModal">
        <div v-if="modalState === 'Delete'">{{ modalMessageYesNo }}</div>
        <ReviewForm v-if="modalState === 'Update'" :itemForm="modalItem" :films="films" :isUpdate="true"
          @saveItem="saveItemHandler" />
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
import Paginator from "@/components/Paginator.vue";
import OperationsCrud from "@/components/OperationsCrud.vue";
import ReviewForm from "@/components/forms/ReviewForm.vue";

export default {
  components: { Paginator, OperationsCrud, Modal, ReviewForm },
  data() {
    return {
      // Component state properties
      authStore: null, // Will be initialized in mounted()
      loading: false, // Indicates if API calls are in progress
      errorMessages: null, // Stores error messages for display
      activeTab: "my", // 'my' or 'all'
      tableReviewFilter: "ABC", // Sort order for 'my' reviews table
      publicReviewFilter: "ABC", // Sort order for 'all' reviews
      searchQuery: "", // Input model for search
      debounceTimer: null, // Timer for debouncing search input
      films: [], // List of films for the update form dropdown
      favourites: [], // User's reviews ('my' tab)
      publicReviews: [], // All public reviews ('all' tab)

      // For Paginator
      currentPage: 1, // Current page for 'my' reviews
      currentPublicPage: 1, // Current page for 'all' reviews
      dynamicItemsPerPage: 10, // Default, will be calculated dynamically

      // Controls there visibility
      showSearch: false,
      showTableFilter: false,
      showPublicFilter: false,

      // Modal state properties
      isModalVisible: false, // Controls modal visibility via v-model
      modalItem: {}, // Data for the item being updated/deleted
      modalMessageYesNo: null, // Confirmation message for delete
      modalState: "Read", // Current modal mode: 'Read', 'Delete', 'Update'
      modalTitle: null, // Modal title
      modalYes: null, // Text for the 'Yes' button (null hides it)
      modalNo: null, // Text for the 'No' button
      modalSize: null, // Modal size (e.g., 'lg')
      selectedRowId: null, // ID of the item being acted upon
    };
  },
  computed: {
    /**
     * Checks if the user is currently logged in based on the auth store state.
     */
    isLoggedIn() {
      return this.authStore && this.authStore.id && this.authStore.id !== 0;
    },

    /**
     * Filters and sorts the user's favourite reviews based on search query and table filter.
     */
    filteredFavourites() {
      return this.filterAndSortReviews(
        this.favourites,
        this.searchQuery,
        this.tableReviewFilter,
        false
      );
    },

    /**
     * Filters and sorts all public reviews based on search query and public filter.
     */
    filteredPublicReviews() {
      return this.filterAndSortReviews(
        this.publicReviews,
        this.searchQuery,
        this.publicReviewFilter,
        true
      );
    },

    // --- Pagination Computed Properties ---

    /**
     * Calculates the total number of pages for the 'My Reviews' table.
     */
    totalFavPages() {
      if (!this.dynamicItemsPerPage) return 0;
      return Math.ceil(
        this.filteredFavourites.length / this.dynamicItemsPerPage
      );
    },

    /**
     * Gets the slice of 'My Reviews' for the current page.
     */
    paginatedFavourites() {
      const start = (this.currentPage - 1) * this.dynamicItemsPerPage;
      const end = start + this.dynamicItemsPerPage;
      return this.filteredFavourites.slice(start, end);
    },

    /**
     * Generates the array of page numbers/ellipses for the 'My Reviews' paginator.
     */
    favPagesArray() {
      return this.generatePagesArray(this.totalFavPages, this.currentPage);
    },

    /**
     * Calculates the total number of pages for the 'All Reviews' list.
     */
    totalPublicPages() {
      if (!this.dynamicItemsPerPage) return 0;
      return Math.ceil(
        this.filteredPublicReviews.length / this.dynamicItemsPerPage
      );
    },

    /**
     * Gets the slice of 'All Reviews' for the current page.
     */
    paginatedPublicReviews() {
      const start = (this.currentPublicPage - 1) * this.dynamicItemsPerPage;
      const end = start + this.dynamicItemsPerPage;
      return this.filteredPublicReviews.slice(start, end);
    },

    /**
     * Generates the array of page numbers/ellipses for the 'All Reviews' paginator.
     */
    publicPagesArray() {
      return this.generatePagesArray(
        this.totalPublicPages,
        this.currentPublicPage
      );
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
        this.errorMessages = `Operation failed: ${error.response?.data?.message ||
          error.message ||
          "An unknown error occurred."
          }`;
        return null;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Fetches the logged-in user's favourite reviews from the API.
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
      this.$nextTick(this.calculateDimensions);
    },

    /**
     * Fetches all public reviews from the API.
     */
    async fetchAllReviews() {
      const responseData = await this._fetchApi(`${BASE_URL}/favourites`);
      if (responseData?.data) {
        this.publicReviews = responseData.data.map((review) => ({
          ...review,
          userName: review.userName || "Anonymous",
          filmTitle: review.filmTitle || "Unknown Film",
          evaluation: Number(review.evaluation) || 0,
          content: review.content || this.randomDefaultReview(),
        }));
      } else {
        this.publicReviews = [];
      }
      this.$nextTick(this.calculateDimensions);
    },

    /**
     * Fetches the list of films, typically used for dropdowns in forms.
     */
    async fetchFilms() {
      const responseData = await this._fetchApi(`${BASE_URL}/films`);
      if (responseData?.data) {
        this.films = responseData.data;
      } else {
        this.films = [];
      }
    },

    /**
     * Switches the active tab between 'My Reviews' and 'All Reviews'.
     */
    switchTab(tab) {
      if (tab === "my" && !this.isLoggedIn) return;

      this.activeTab = tab;
      this.currentPage = 1;
      this.currentPublicPage = 1;
      this.searchQuery = "";
      this.errorMessages = null;

      this.showSearch = false;
      this.showTableFilter = false;
      this.showPublicFilter = false;

      if (tab === "all" && this.publicReviews.length === 0) {
        this.fetchAllReviews();
      } else if (
        tab === "my" &&
        this.favourites.length === 0 &&
        this.isLoggedIn
      ) {
        this.fetchFavourites();
      } else {
        this.$nextTick(this.calculateDimensions);
      }
    },

    /**
     * Handles input in the search field with debouncing.
     */
    handleSearch() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.currentPage = 1;
        this.currentPublicPage = 1;
        this.$nextTick(this.calculateDimensions);
      }, 300);
    },

    /**
     * Toggles the visibility of the search or filter dropdowns.
     */
    toggleControl(type) {
      const isOpening =
        (type === "search" && !this.showSearch) ||
        (type === "tableFilter" && !this.showTableFilter) ||
        (type === "publicFilter" && !this.showPublicFilter);

      this.showSearch =
        type === "search"
          ? !this.showSearch
          : isOpening
            ? false
            : this.showSearch;
      this.showTableFilter =
        type === "tableFilter"
          ? !this.showTableFilter
          : isOpening
            ? false
            : this.showTableFilter;
      this.showPublicFilter =
        type === "publicFilter"
          ? !this.showPublicFilter
          : isOpening
            ? false
            : this.showPublicFilter;
    },

    // --- CRUD Methods ---

    /**
     * Sets up the modal state for deleting a review and shows the modal.
     */
    onClickDelete(item) {
      this.modalState = "Delete";
      this.modalTitle = "Confirm Deletion";
      this.modalMessageYesNo = `Are you sure you want to delete your review for "${item.filmTitle || "this film"
        }"? This action cannot be undone.`;
      this.modalYes = "Yes, Delete";
      this.modalNo = "No, Cancel";
      this.modalSize = null;
      this.selectedRowId = item.id;
      this.isModalVisible = true; // Show modal
    },

    /**
     * Sets up the modal state for updating a review and shows the modal.
     */
    onClickUpdate(item) {
      this.modalState = "Update";
      this.selectedRowId = item.id;
      this.modalItem = {
        ...item,
        evaluation: Number(item.evaluation) || 0,
        filmId: item.filmId,
      };
      this.modalTitle = `Update Review for "${item.filmTitle || "Unknown Film"
        }"`;
      this.modalYes = null;
      this.modalNo = "Cancel";
      this.modalSize = "lg";
      this.isModalVisible = true; // Show modal
    },

    /**
     * Performs the actual delete API call after confirmation.
     */
    async deleteReview() {
      if (!this.selectedRowId) return;

      const result = await this._fetchApi(
        `${BASE_URL}/favourites/${this.selectedRowId}`,
        {},
        "delete"
      );

      if (result !== null) {
        await this.fetchFavourites();
        this.isModalVisible = false; // Hide modal on success
        // State reset is now handled in closeModal
      }
      // Reset state even on failure, handled by closeModal triggered by user action
      // this.modalState = 'Read'; // OLD: Removed from here
    },

    /**
     * Handles the 'saveItem' event emitted by the ReviewForm component.
     */
    async saveItemHandler(formData) {
      if (this.modalState !== "Update" || !this.selectedRowId) return;

      const payload = {
        evaluation: formData.evaluation,
        filmId: formData.filmId,
        content: formData.content,
      };

      const result = await this._fetchApi(
        `${BASE_URL}/favourites/${this.selectedRowId}`,
        {},
        "patch",
        payload
      );

      if (result !== null) {
        await this.fetchFavourites();
        // Hide the modal first
        this.isModalVisible = false;
        // Then, after the modal is hidden, reset its state
        this.$nextTick(() => {
          this.modalState = "Read";
          this.modalItem = {};
          this.selectedRowId = null;
          this.modalTitle = null;
          this.modalMessageYesNo = null;
          this.modalYes = null;
          this.modalNo = null;
          this.modalSize = null;
        });
      }
    },

    /**
     * Handles the 'yesEvent' emitted by the Modal component (for Delete confirmation).
     */
    yesEventHandler() {
      if (this.modalState === "Delete") {
        this.deleteReview();
        // Modal closing is handled within deleteReview on success
      }
    },

    /**
     * Closes the modal and resets its state.
     * Called via v-model update or @close event from Modal.
     */
    closeModal() {
      this.isModalVisible = false;
      // Reset other states...
      // Add a small delay to allow Bootstrap cleanup
      setTimeout(() => {
        this.modalState = 'Read';
        this.modalItem = {};
        this.selectedRowId = null;
      }, 300);
    },

    // --- Helper & Formatting Methods ---

    /**
     * Shared method to filter and sort an array of reviews.
     */
    filterAndSortReviews(sourceArray, query, filterType, isPublic = false) {
      let filtered = [...sourceArray];

      if (query) {
        const lowerQuery = query.toLowerCase();
        filtered = filtered.filter(
          (review) =>
            (review.filmTitle || "").toLowerCase().includes(lowerQuery) ||
            // Remove content check for public reviews ↓
            (isPublic
              ? (review.userName || "").toLowerCase().includes(lowerQuery)
              : (review.content || "").toLowerCase().includes(lowerQuery))
        );
      }

      switch (filterType) {
        case "ABC":
          filtered.sort((a, b) =>
            (a.filmTitle || "").localeCompare(b.filmTitle || "")
          );
          break;
        case "highToLow":
          filtered.sort(
            (a, b) => (Number(b.evaluation) || 0) - (Number(a.evaluation) || 0)
          );
          break;
        case "lowToHigh":
          filtered.sort(
            (a, b) => (Number(a.evaluation) || 0) - (Number(b.evaluation) || 0)
          );
          break;
        case "newest":
          filtered.sort(
            (a, b) => new Date(b.updated_at) - new Date(a.updated_at)
          );
          break;
        case "oldest":
          filtered.sort(
            (a, b) => new Date(a.created_at) - new Date(b.updated_at)
          );
          break;
      }
      return filtered;
    },

    /**
     * Generates the array for the Paginator component's `pagesArray` prop.
     */
    generatePagesArray(total, current) {
      if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
      }
      const pages = new Set([1, total]);
      const rangeStart = Math.max(2, current - 1);
      const rangeEnd = Math.min(total - 1, current + 1);

      for (let i = rangeStart; i <= rangeEnd; i++) {
        pages.add(i);
      }

      const result = [];
      let lastPage = 0;
      const sortedPages = Array.from(pages).sort((a, b) => a - b);
      sortedPages.forEach((p) => {
        if (lastPage > 0 && p - lastPage > 1) {
          result.push("...");
        }
        result.push(p);
        lastPage = p;
      });
      return result;
    },

    /**
     * Determines the appropriate Bootstrap Icon class for star ratings.
     */
    getStarClass(item, starIndex) {
      const evaluation = Number(item.evaluation) || 0;
      if (evaluation >= starIndex) {
        return "bi-star-fill";
      } else if (evaluation + 0.75 >= starIndex) {
        return "bi-star-half";
      } else {
        return "bi-star";
      }
    },

    /**
     * Formats the evaluation number to one decimal place.
     */
    formatEvaluation(value) {
      const num = Number(value);
      return Number.isNaN(num) ? "N/A" : num.toFixed(1);
    },

    /**
     * Formats a date string into a more readable format using Hungarian locale.
     */
    formatDate(dateString) {
      if (!dateString) return "N/A";
      try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "Invalid Date";
        return new Intl.DateTimeFormat("hu-HU", {
          year: "numeric",
          month: "short",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        }).format(date);
      } catch (e) {
        console.warn("Error formatting date:", dateString, e);
        return "N/A";
      }
    },

    /**
     * Returns a random default review string (placeholder).
     */
    randomDefaultReview() {
      const index = Math.floor(Math.random() * this.defaultReviews.length);
      return this.defaultReviews[index];
    },

    // --- Pagination Event Handlers ---

    /**
     * Handles the 'paging' event from the 'My Reviews' Paginator.
     */
    handlePageChange(pageInfo) {
      if (typeof pageInfo.pageNumber === "number") {
        this.currentPage = pageInfo.pageNumber;
      }
    },

    /**
     * Handles the 'paging' event from the 'All Reviews' Paginator.
     */
    handlePublicPageChange(pageInfo) {
      if (typeof pageInfo.pageNumber === "number") {
        this.currentPublicPage = pageInfo.pageNumber;
      }
    },

    // --- Dynamic Layout Calculation ---

    /**
     * Calculates the number of items that should fit per page based on container height.
     */
    calculateDimensions() {
      const tableRowHeight = 60;
      const reviewCardHeight = 60;

      let containerHeight = 0;
      let itemHeight = 0;

      if (this.activeTab === "my" && this.$refs.tableWrapperRef) {
        containerHeight = this.$refs.tableWrapperRef.clientHeight;
        itemHeight = tableRowHeight;
      } else if (this.activeTab === "all" && this.$refs.reviewsWrapperRef) {
        containerHeight = this.$refs.reviewsWrapperRef.clientHeight;
        itemHeight = reviewCardHeight;
      }

      if (containerHeight > 0 && itemHeight > 0) {
        this.dynamicItemsPerPage = Math.max(
          1,
          Math.floor(containerHeight / itemHeight)
        );
      } else {
        this.dynamicItemsPerPage = 10;
      }

      if (this.currentPage > this.totalFavPages) {
        this.currentPage = Math.max(1, this.totalFavPages);
      }
      if (this.currentPublicPage > this.totalPublicPages) {
        this.currentPublicPage = Math.max(1, this.totalPublicPages);
      }
    },
  },
  watch: {
    /**
     * Watches for changes in the login state.
     */
    isLoggedIn(loggedIn, wasLoggedIn) {
      if (!loggedIn && wasLoggedIn) {
        this.switchTab("all");
        this.favourites = [];
      } else if (loggedIn && !wasLoggedIn) {
        if (this.activeTab === "my") {
          this.fetchFavourites();
        }
      }
    },

    /**
     * Watches the 'My Reviews' filter selection.
     */
    tableReviewFilter() {
      this.currentPage = 1;
      this.$nextTick(this.calculateDimensions);
    },

    /**
     * Watches the 'All Reviews' filter selection.
     */
    publicReviewFilter() {
      this.currentPublicPage = 1;
      this.$nextTick(this.calculateDimensions);
    },
  },
  /**
   * Lifecycle hook called after the component instance has been mounted.
   */
  mounted() {
    this.authStore = useAuthStore();
    this.activeTab = this.isLoggedIn ? "my" : "all";
    this.fetchFilms();

    if (this.activeTab === "my") {
      this.fetchFavourites();
    } else {
      this.fetchAllReviews();
    }

    this.$nextTick(() => {
      this.calculateDimensions();
    });
    window.addEventListener("resize", this.calculateDimensions);
  },
  /**
   * Lifecycle hook called right before the component instance is destroyed.
   */
  beforeDestroy() {
    window.removeEventListener("resize", this.calculateDimensions);
    clearTimeout(this.debounceTimer);
  },
};
</script>

<style scoped>
/* General Styles */
.film-reviews {
  color: white;
  /* Assuming a dark theme */
  height: 90vh;
  /* Example height, adjust as needed */
  display: flex;
  flex-direction: column;
  overflow: hidden;
  /* Prevent whole page scroll */
  background-color: #121212;
  /* Dark background */
}

/* Header Styles */
.header-container {
  background: #1a1a1a;
  border-bottom: 2px solid #ffd700;
  /* Gold accent */
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  /* Allow wrapping on smaller screens */
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  gap: 1rem;
  flex-shrink: 0;
  /* Prevent header from shrinking */
}

.tabs-wrapper {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-grow: 1;
  /* Allow tabs to take space */
  min-width: 0;
  justify-content: flex-start;
  /* Align tabs left */
}

.title-text {
  color: #bdbdbd;
  /* Lighter grey for inactive tabs */
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  padding: 0.5rem 1rem;
  white-space: nowrap;
  text-align: center;
  border-radius: 4px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  /* Placeholder for border */
}

.clickable {
  cursor: pointer;
}

.clickable:hover {
  color: #ffffff;
  /* Brighter on hover */
  background-color: rgba(255,
      215,
      0,
      0.1);
  /* Subtle gold background on hover */
}

.active-tab {
  background: #ffd700;
  color: #1a1a1a !important;
  /* Dark text on active tab */
  font-weight: 700;
  border-color: #ffd700;
}

/* Controls container */
.controls-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  /* Reduced gap */
  position: relative;
  /* For positioning dropdowns */
  flex-shrink: 0;
  /* Prevent shrinking */
}

.control-buttons {
  display: flex;
  gap: 0.5rem;
}

.control-item {
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  width: 38px;
  /* Slightly larger */
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #2a2a2a;
  color: #bdbdbd;
  transition: all 0.3s ease;
  border: 1px solid #444;
  /* Subtle border */
}

.control-item:hover {
  background: #ffd700;
  color: #1a1a1a;
  border-color: #ffd700;
}

.control-item i {
  font-size: 1.1rem;
  transition: transform 0.2s ease;
}

.control-item:active i {
  transform: scale(0.95);
}

/* Collapsible containers (Search & Filter) */
.search-container,
.filter-container {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  /* Position below the controls */
  z-index: 100;
  background: #2a2a2a;
  /* Slightly lighter than header */
  border: 1px solid #444;
  border-radius: 8px;
  padding: 0.75rem;
  /* More padding */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

/* Animation for dropdowns */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.search-wrapper {
  min-width: 250px;
}

.filter-wrapper {
  min-width: 200px;
}

/* Search Input */
.search-input {
  width: 100%;
  padding: 0.6rem 1rem;
  border-radius: 20px;
  /* Pill shape */
  border: 1px solid #555;
  background-color: #333;
  color: white;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.search-input::placeholder {
  color: #aaa;
}

.search-input:focus {
  border-color: #ffd700;
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
}

/* Filter Select Dropdown */
.custom-filter-select {
  width: 100%;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  /* Consistent rounding */
  border: 1px solid #555;
  background-color: #333;
  color: white;
  font-size: 1rem;
  cursor: pointer;
  outline: none;
  appearance: none;
  /* Remove default arrow */
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23bdbdbd' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1em 1em;
  padding-right: 2.5rem;
  /* Space for custom arrow */
}

.custom-filter-select:focus {
  border-color: #ffd700;
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
}

/* Loading Overlay */
.loading-overlay {
  position: absolute;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 150;
  /* Above content, below modal */
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: white;
  gap: 1rem;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
  color: #ffd700;
  /* Gold spinner */
}

.loading-text {
  font-size: 1.1rem;
}

/* Content Area */
.content-area {
  flex-grow: 1;
  /* Allow content to take remaining space */
  display: flex;
  flex-direction: column;
  overflow: hidden;
  /* Important: Prevents content scroll from affecting page */
  padding: 1rem;
  gap: 1rem;
  /* Space between error message and data */
}

.error-message {
  flex-shrink: 0;
  /* Prevent error message from shrinking */
  background-color: #dc3545;
  /* Bootstrap danger red */
  color: white;
  padding: 0.75rem 1.25rem;
  border-radius: 0.25rem;
  border: 1px solid #d32535;
}

.data-container {
  flex-grow: 1;
  /* Takes up remaining space in content-area */
  display: flex;
  /* Use flex to manage visibility */
  flex-direction: column;
  /* Stack sections vertically */
  overflow: hidden;
  /* Contains the scrolling areas */
}

/* Sections for Table and Cards */
.table-section,
.public-reviews-section {
  flex-grow: 1;
  /* Allow section to fill data-container */
  display: flex;
  flex-direction: column;
  overflow: hidden;
  /* Important */
  height: 100%;
  /* Ensure it takes full height of parent */
}

/* Wrapper for scrollable content */
.table-wrapper,
.reviews-wrapper {
  flex-grow: 1;
  /* Allow this area to scroll */
  overflow-y: auto;
  /* Enable vertical scrolling */
  padding-right: 5px;
  /* Space for scrollbar */
  /* Custom scrollbar styling (optional) */
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

/* Table Styles */
.custom-table {
  width: 100%;
  border-collapse: separate;
  /* Use separate for border-radius */
  border-spacing: 0;
  background: #1e1e1e;
  /* Slightly lighter dark */
  border-radius: 8px;
  overflow: hidden;
  /* Clip content to rounded corners */
}

.custom-table th,
.custom-table td {
  padding: 0.9rem 1rem;
  /* Increased padding */
  text-align: left;
  border-bottom: 1px solid #3a3a3a;
  /* Darker border */
  vertical-align: middle;
}

.custom-table th {
  background-color: #2a2a2a;
  /* Header background */
  color: #ffd700;
  /* Gold header text */
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.custom-table tbody tr:last-child td {
  border-bottom: none;
}

.custom-table tbody tr {
  transition: background-color 0.2s ease;
}

.custom-table tbody tr:hover {
  background-color: #2f2f2f;
  /* Hover effect */
}

.custom-table .film {
  font-weight: 500;
}

.custom-table .date {
  font-size: 0.9em;
  color: #aaa;
}

/* Star Rating Styles */
.star-rating {
  font-size: 1.1rem;
}

/* Adjust size */
.star-rating .bi {
  transition: color 0.2s ease;
}

/* Smooth color transition */
.star-rating .text-muted {
  font-size: 0.9em;
}

/* Public Reviews Card Styles */
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.review-card {
  background: #1e1e1e;
  /* Match film-card background */
  border-radius: 10px;
  /* Match film-card radius */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  /* Match film-card shadow */
  padding: 15px;
  /* Match film-card padding */
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  transition: transform 0.2s ease-in-out;
  /* Match film-card transition */
  color: white;
  /* Match film-card color */
  border: none;
  text-align: left;
  /* Keep text left-aligned for reviews */
}

.review-card:hover {
  transform: scale(1.02);
  /* Match film-card hover */
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
  /* Slightly enhance shadow on hover */
}

/* Keep specific review card content styles */
.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.review-meta {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  font-size: 0.85rem;
  color: #aaa;
}

.review-author {
  font-weight: 600;
  color: #ccc;
}

.review-film-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #ffd700;
  /* Gold title */
}

.review-content {
  font-size: 0.95rem;
  color: #ddd;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* No Data Message */
.no-data-message {
  text-align: center;
  padding: 3rem 1rem;
  color: #aaa;
  font-size: 1.1rem;
  flex-grow: 1;
  /* Center vertically */
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Pagination Container */
.pagination-container {
  padding: 1rem 0 0 0;
  /* Padding top */
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
  /* Prevent shrinking */
  border-top: 1px solid #3a3a3a;
  /* Separator line */
  margin-top: auto;
  /* Push to bottom if content is short */
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    align-items: stretch;
    /* Stretch items full width */
  }

  .tabs-wrapper {
    justify-content: center;
    /* Center tabs on small screens */
    width: 100%;
  }

  .controls-container {
    width: 100%;
    justify-content: center;
    /* Center controls */
  }

  .search-container,
  .filter-container {
    width: calc(100% - 2rem);
    /* Make dropdowns wider */
    left: 1rem;
    right: 1rem;
  }

  .custom-table thead {
    display: none;
    /* Hide table header on small screens */
  }

  .custom-table tbody,
  .custom-table tr,
  .custom-table td {
    display: block;
    width: 100%;
  }

  .custom-table tr {
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    margin-bottom: 1rem;
    padding: 0.5rem;
  }

  .custom-table td {
    text-align: right;
    /* Align content to the right */
    padding-left: 50%;
    /* Make space for the label */
    position: relative;
    border-bottom: none;
    /* Remove inner borders */
  }

  .custom-table td::before {
    content: attr(data-label);
    /* Use data-label for pseudo-header */
    position: absolute;
    left: 10px;
    width: calc(50% - 20px);
    padding-right: 10px;
    white-space: nowrap;
    text-align: left;
    font-weight: bold;
    color: #ffd700;
    /* Gold label */
  }

  .custom-table td.text-center {
    text-align: right;
  }

  /* Override center alignment */
  .custom-table td.text-center::before {
    text-align: left;
  }

  /* Keep label left */

  .reviews-grid {
    /* Adjust grid columns for smaller screens if needed, auto-fit might be sufficient */
    grid-template-columns: 1fr;
    /* Stack cards vertically */
  }
}
</style>
