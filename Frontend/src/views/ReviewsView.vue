<template>
  <div class="reviews-container">
    <!-- HEADER: Tabs, Search & Filter Controls -->
    <div class="header-container" ref="headerContainerRef">
      <div class="tabs-wrapper">
        <h3
          class="title-text clickable"
          :class="{ 'active-tab': activeTab === 'all' || !isLoggedIn }"
          @click="switchTab('all')"
          role="tab"
          :aria-selected="activeTab === 'all' || !isLoggedIn"
          tabindex="0"
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
          tabindex="0"
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
            role="button"
            tabindex="0"
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
            role="button"
            tabindex="0"
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
                class="form-control search-input"
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
              <label for="tableFilter" class="filter-label"
                >Sort My Reviews:</label
              >
              <select
                id="tableFilter"
                v-model="tableReviewFilter"
                class="form-control custom-filter-select"
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
              <label for="publicFilter" class="filter-label"
                >Sort All Reviews:</label
              >
              <select
                id="publicFilter"
                v-model="publicReviewFilter"
                class="form-control custom-filter-select"
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

    <!-- LOADING Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="loading-text">Loading Reviews...</div>
      </div>
    </div>

    <!-- MAIN Content Area -->
    <div v-else class="content-area">
      <div
        v-if="errorMessages"
        class="status-message text-danger alert"
        role="alert"
      >
        <i class="bi bi-exclamation-triangle-fill"></i> {{ errorMessages }}
      </div>
      <div class="data-container">
        <!-- TABLE VIEW (My Reviews) -->
        <div v-show="isLoggedIn && activeTab === 'my'" class="table-section">
          <div class="table-wrapper" ref="tableWrapperRef">
            <div
              v-if="
                !paginatedFavourites.length &&
                filteredFavourites.length === 0 &&
                !searchQuery
              "
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
                  <th class="text-center">Created</th>
                  <th class="text-center">Updated</th>
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
                  <td
                    data-label="Evaluation"
                    class="text-center evaluation-cell"
                  >
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
                  <td
                    data-label="Created"
                    class="text-center fst-italic date-cell"
                  >
                    {{ formatDate(favourite.created_at) }}
                  </td>
                  <td
                    data-label="Updated"
                    class="text-center fst-italic date-cell"
                  >
                    {{ formatDate(favourite.updated_at) }}
                  </td>
                  <td
                    data-label="Operations"
                    class="text-nowrap text-center operations-cell"
                  >
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
        <!-- GRID VIEW (All Reviews) -->
        <div
          v-show="activeTab === 'all' || !isLoggedIn"
          class="public-reviews-section"
        >
          <div class="reviews-wrapper" ref="reviewsWrapperRef">
            <div
              v-if="
                !paginatedPublicReviews.length &&
                filteredPublicReviews.length === 0 &&
                !searchQuery
              "
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
                :ref="(el) => (cardRefs[review.id] = el)"
                :class="{ 'is-expanded': isReviewExpanded(review.id) }"
                :style="getCardStyle(review.id)"
              >
                <div class="card-inner">
                  <div class="review-header">
                    <span class="review-author">
                      <i class="bi bi-person-fill me-1"></i>
                      {{ review.userName || "Anonymous" }}
                    </span>
                    <span class="review-date">
                      <i class="bi bi-calendar3 me-1"></i>
                      {{ formatDate(review.updated_at) }}
                      <span
                        v-if="review.created_at !== review.updated_at"
                        class="edited-indicator"
                      >
                        (edited)
                      </span>
                    </span>
                  </div>
                  <div class="film-title-rating">
                    <div class="review-film-title">
                      <i class="bi bi-film me-1"></i>
                      {{ review.filmTitle || "Unknown Film" }}
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
                  </div>
                  <div class="review-content">
                    <p :ref="(el) => (reviewContentRefs[review.id] = el)">
                      {{ review.content }}
                    </p>
                  </div>
                  <!-- Instead of a button, use a text link styled as a link -->
                  <a
                    v-if="doesReviewNeedTruncation(review.id)"
                    @click.prevent="toggleReadMore(review.id)"
                    class="read-more-link"
                    role="button"
                    tabindex="0"
                    :aria-expanded="isReviewExpanded(review.id)"
                  >
                    {{
                      isReviewExpanded(review.id) ? "Show less" : "Read more"
                    }}
                  </a>
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

    <!-- Modal for Delete/Update Operations -->
    <div v-show="isModalVisible">
      <ReviewForm
        v-if="modalState === 'Update'"
        :itemForm="modalItem"
        :films="films"
        :isUpdate="true"
        @saveItem="saveItemHandler"
      />
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useAuthStore } from "@/stores/useAuthStore";
import { BASE_URL } from "../helpers/baseUrls";
import { nextTick } from "vue";

// Import Child Components
import Paginator from "@/components/Paginator.vue";
import OperationsCrud from "@/components/OperationsCrud.vue";
import ReviewForm from "@/components/forms/ReviewForm.vue";

// --- Constants ---
const DEFAULT_TABLE_ITEMS_PER_PAGE = 10;
const FIXED_CARD_WIDTH_PX = 330; // Fallback card width

export default {
  name: "ReviewsView",
  components: { Paginator, OperationsCrud, ReviewForm },
  data() {
    return {
      authStore: null,
      loading: false,
      errorMessages: null,
      activeTab: "my",
      tableReviewFilter: "newest",
      publicReviewFilter: "newest",
      searchQuery: "",
      debounceTimer: null,
      itemsUpdateTimer: null,
      resizeObserver: null,
      films: [],
      favourites: [],
      publicReviews: [],
      currentPage: 1,
      currentPublicPage: 1,
      itemsPerPage: DEFAULT_TABLE_ITEMS_PER_PAGE,
      showSearch: false,
      showTableFilter: false,
      showPublicFilter: false,
      isModalVisible: false,
      modalItem: {},
      modalMessageYesNo: null,
      modalState: "Read",
      modalTitle: null,
      modalYes: null,
      modalNo: null,
      modalSize: null,
      selectedRowId: null,
      // Read More / Truncation State
      cardRefs: {},
      reviewContentRefs: {},
      expandedState: {},
      truncatedState: {},
      initialCardMaxHeight: null,
    };
  },
  computed: {
    isLoggedIn() {
      return !!this.authStore?.id;
    },
    filteredFavourites() {
      return this.filterAndSortReviews(
        this.favourites,
        this.searchQuery,
        this.tableReviewFilter,
        false
      );
    },
    filteredPublicReviews() {
      const filtered = this.filterAndSortReviews(
        this.publicReviews,
        this.searchQuery,
        this.publicReviewFilter,
        true
      );
      nextTick(() => {
        this.checkAllTruncation();
      });
      return filtered;
    },
    totalFavPages() {
      const tableItemsPerPage = DEFAULT_TABLE_ITEMS_PER_PAGE;
      return Math.ceil(this.filteredFavourites.length / tableItemsPerPage);
    },
    paginatedFavourites() {
      const tableItemsPerPage = DEFAULT_TABLE_ITEMS_PER_PAGE;
      const start = (this.currentPage - 1) * tableItemsPerPage;
      const end = start + tableItemsPerPage;
      return this.filteredFavourites.slice(start, end);
    },
    totalPublicPages() {
      return Math.ceil(this.filteredPublicReviews.length / this.itemsPerPage);
    },
    paginatedPublicReviews() {
      const start = (this.currentPublicPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      const paginated = this.filteredPublicReviews.slice(start, end);
      nextTick(() => {
        this.checkAllTruncation();
      });
      return paginated;
    },
  },
  methods: {
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
        if (data) axiosConfig.data = data;
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
        setTimeout(() => {
          this.errorMessages = null;
        }, 7000);
        return null;
      } finally {
        this.loading = false;
      }
    },
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
      if (this.currentPage > this.totalFavPages && this.totalFavPages > 0) {
        this.currentPage = this.totalFavPages;
      } else if (this.currentPage < 1) {
        this.currentPage = 1;
      }
    },
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
      this.updateItemsPerPage();
      if (
        this.currentPublicPage > this.totalPublicPages &&
        this.totalPublicPages > 0
      ) {
        this.currentPublicPage = this.totalPublicPages;
      } else if (this.currentPublicPage < 1) {
        this.currentPublicPage = 1;
      }
      await nextTick();
      this.measureAndSetInitialHeight();
      this.checkAllTruncation();
    },
    async fetchFilms() {
      const responseData = await this._fetchApi(`${BASE_URL}/films`);
      this.films = (responseData && responseData.data) || [];
    },
    switchTab(tab) {
      if (tab === "my" && !this.isLoggedIn) return;
      if (this.activeTab === tab) return;
      this.activeTab = tab;
      this.searchQuery = "";
      this.errorMessages = null;
      this.showSearch = false;
      this.showTableFilter = false;
      this.showPublicFilter = false;
      this.expandedState = {};
      this.truncatedState = {};
      this.initialCardMaxHeight = null;
      if (tab === "all") {
        this.currentPublicPage = 1;
        if (this.publicReviews.length === 0) {
          this.fetchAllReviews();
        } else {
          this.updateItemsPerPage();
          nextTick(() => {
            this.measureAndSetInitialHeight();
            this.checkAllTruncation();
          });
        }
      } else if (tab === "my") {
        this.currentPage = 1;
        if (this.favourites.length === 0 && this.isLoggedIn) {
          this.fetchFavourites();
        }
        this.itemsPerPage = DEFAULT_TABLE_ITEMS_PER_PAGE;
      }
      nextTick(() => {
        this.setupResizeObserver();
      });
    },
    handleSearch() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.currentPage = 1;
        this.currentPublicPage = 1;
      }, 300);
    },
    toggleControl(type) {
      if (type === "search") {
        this.showSearch = !this.showSearch;
        this.showTableFilter = false;
        this.showPublicFilter = false;
      } else if (type === "tableFilter") {
        this.showTableFilter = !this.showTableFilter;
        this.showSearch = false;
        this.showPublicFilter = false;
      } else if (type === "publicFilter") {
        this.showPublicFilter = !this.showPublicFilter;
        this.showSearch = false;
        this.showTableFilter = false;
      }
    },
    onClickDelete(item) {
      this.modalState = "Delete";
      this.modalTitle = "Confirm Deletion";
      this.modalMessageYesNo = `Are you sure you want to delete your review for "${
        item.filmTitle || "this film"
      }"? This action cannot be undone.`;
      this.modalYes = "Yes";
      this.modalNo = "No";
      this.modalSize = null;
      this.selectedRowId = item.id;
      this.isModalVisible = true;
    },
    onClickUpdate(item) {
      this.modalState = "Update";
      this.selectedRowId = item.id;
      this.modalItem = { ...item, evaluation: Number(item.evaluation) || 0 };
      this.modalTitle = `Update Review for "${
        item.filmTitle || "Unknown Film"
      }"`;
      this.modalYes = null;
      this.modalNo = null;
      this.modalSize = "lg";
      this.isModalVisible = true;
    },
    async deleteReview() {
      if (!this.selectedRowId) return;
      const result = await this._fetchApi(
        `${BASE_URL}/favourites/${this.selectedRowId}`,
        {},
        "delete"
      );
      if (result !== null) {
        await this.fetchFavourites();
      }
      this.closeModal();
    },
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
      }
      this.closeModal();
    },
    yesEventHandler() {
      if (this.modalState === "Delete") {
        this.deleteReview();
      }
    },
    closeModal() {
      this.isModalVisible = false;
      setTimeout(() => {
        this.modalState = "Read";
        this.modalItem = {};
        this.selectedRowId = null;
        this.modalTitle = null;
        this.modalMessageYesNo = null;
        this.modalYes = null;
        this.modalNo = null;
        this.modalSize = null;
      }, 300);
    },
    filterAndSortReviews(sourceArray, query, filterType, isPublic = false) {
      let filtered = [...sourceArray];
      if (query) {
        const lowerQuery = query.toLowerCase();
        filtered = filtered.filter((review) => {
          const titleMatch = (review.filmTitle || "")
            .toLowerCase()
            .includes(lowerQuery);
          const userMatch = (review.userName || "")
            .toLowerCase()
            .includes(lowerQuery);
          const contentMatch =
            !isPublic &&
            (review.content || "").toLowerCase().includes(lowerQuery);
          return titleMatch || userMatch || contentMatch;
        });
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
            (a, b) =>
              new Date(b.updated_at || b.created_at) -
              new Date(a.updated_at || a.created_at)
          );
          break;
        case "oldest":
          filtered.sort(
            (a, b) =>
              new Date(a.created_at || a.updated_at) -
              new Date(b.created_at || b.updated_at)
          );
          break;
      }
      return filtered;
    },
    getStarClass(item, starIndex) {
      const evaluation = Number(item.evaluation) || 0;
      if (evaluation >= starIndex) return "bi-star-fill";
      if (evaluation >= starIndex - 0.5) return "bi-star-half";
      return "bi-star";
    },
    formatEvaluation(value) {
      const num = Number(value);
      return isNaN(num) ? "N/A" : num.toFixed(1);
    },
    formatDate(dateString) {
      if (!dateString) return "N/A";
      try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "Invalid Date";
        return date.toLocaleDateString("en-CA", {
          year: "numeric",
          month: "2-digit",
          day: "2-digit",
        });
      } catch (e) {
        console.warn("Error formatting date:", dateString, e);
        return "N/A";
      }
    },
    handlePageChange(pageInfo) {
      if (typeof pageInfo.pageNumber === "number") {
        this.currentPage = pageInfo.pageNumber;
      }
    },
    handlePublicPageChange(pageInfo) {
      if (typeof pageInfo.pageNumber === "number") {
        this.currentPublicPage = pageInfo.pageNumber;
      }
      nextTick(() => {
        this.checkAllTruncation();
      });
    },
    // --- Improved "Items Per Page" Calculation ---
    calculateGridItemsPerPage() {
      const container = this.$refs.reviewsWrapperRef;
      if (!container) return DEFAULT_TABLE_ITEMS_PER_PAGE;
      const { clientWidth: containerWidth, clientHeight: containerHeight } =
        container;
      let card = container.querySelector(".review-card");
      if (!card) return DEFAULT_TABLE_ITEMS_PER_PAGE;
      // Use measured initial height if available
      const effectiveCardHeight =
        this.initialCardMaxHeight || card.offsetHeight || 350;
      const effectiveCardWidth = card.offsetWidth || FIXED_CARD_WIDTH_PX;
      let gridRowGap = 0,
        gridColumnGap = 0;
      const grid = container.querySelector(".reviews-grid");
      if (grid) {
        const computedStyles = window.getComputedStyle(grid);
        gridRowGap = parseFloat(computedStyles.rowGap) || 0;
        gridColumnGap = parseFloat(computedStyles.columnGap) || 0;
      }
      const totalRowUnit = effectiveCardHeight + gridRowGap;
      const rows = Math.max(
        1,
        Math.floor((containerHeight + gridRowGap) / totalRowUnit)
      );
      const totalColumnUnit = effectiveCardWidth + gridColumnGap;
      const columns = Math.max(
        1,
        Math.floor((containerWidth + gridColumnGap) / totalColumnUnit)
      );
      return rows * columns;
    },
    updateItemsPerPage() {
      this.$nextTick(() => {
        if (
          (this.activeTab === "all" || !this.isLoggedIn) &&
          this.$refs.reviewsWrapperRef
        ) {
          const computedItems = this.calculateGridItemsPerPage();
          if (this.itemsPerPage !== computedItems) {
            this.itemsPerPage = computedItems > 0 ? computedItems : 1;
            if (
              this.currentPublicPage > this.totalPublicPages &&
              this.totalPublicPages > 0
            ) {
              this.currentPublicPage = this.totalPublicPages;
            } else if (this.currentPublicPage < 1) {
              this.currentPublicPage = 1;
            }
            nextTick(() => {
              this.checkAllTruncation();
            });
          }
        } else {
          this.itemsPerPage = DEFAULT_TABLE_ITEMS_PER_PAGE;
        }
      });
    },
    debouncedUpdateItemsPerPage() {
      clearTimeout(this.itemsUpdateTimer);
      this.itemsUpdateTimer = setTimeout(() => {
        this.measureAndSetInitialHeight();
        this.updateItemsPerPage();
      }, 250);
    },
    setupResizeObserver() {
      if (this.resizeObserver) {
        this.resizeObserver.disconnect();
        this.resizeObserver = null;
      }
      window.removeEventListener("resize", this.debouncedUpdateItemsPerPage);
      const targetElement =
        this.activeTab === "all" || !this.isLoggedIn
          ? this.$refs.reviewsWrapperRef
          : null;
      if (targetElement) {
        if ("ResizeObserver" in window) {
          this.resizeObserver = new ResizeObserver(
            this.debouncedUpdateItemsPerPage
          );
          this.resizeObserver.observe(targetElement);
        } else {
          window.addEventListener("resize", this.debouncedUpdateItemsPerPage);
        }
      }
    },
    // --- Read More / Less Methods ---
    measureAndSetInitialHeight() {
      if (this.activeTab === "all" || !this.isLoggedIn) {
        const firstCardId = this.paginatedPublicReviews[0]?.id;
        if (firstCardId) {
          const cardElement = this.cardRefs[firstCardId];
          if (cardElement) {
            cardElement.style.maxHeight = "none";
            const measuredHeight = cardElement.offsetHeight;
            cardElement.style.maxHeight = "";
            this.initialCardMaxHeight =
              measuredHeight > 0 ? measuredHeight : 350;
          } else {
            this.initialCardMaxHeight = 350;
          }
        } else {
          this.initialCardMaxHeight = 350;
        }
      } else {
        this.initialCardMaxHeight = null;
      }
    },
    getCardStyle(reviewId) {
      if (
        !this.initialCardMaxHeight ||
        (this.activeTab !== "all" && this.isLoggedIn)
      )
        return {};
      const expanded = this.isReviewExpanded(reviewId);
      let maxHeight = expanded ? "10000px" : `${this.initialCardMaxHeight}px`;
      if (expanded) {
        const cardElement = this.cardRefs[reviewId];
        if (cardElement) {
          maxHeight = `${cardElement.scrollHeight}px`;
        }
      }
      return { maxHeight, transition: "max-height 0.4s ease-in-out" };
    },
    isReviewExpanded(reviewId) {
      return !!this.expandedState[reviewId];
    },
    doesReviewNeedTruncation(reviewId) {
      return !!this.truncatedState[reviewId];
    },
    toggleReadMore(reviewId) {
      if (!this.initialCardMaxHeight) return;
      this.expandedState[reviewId] = !this.isReviewExpanded(reviewId);
    },
    checkTruncation(reviewId) {
      const contentElement = this.reviewContentRefs[reviewId];
      if (contentElement) {
        const needsTruncation =
          contentElement.scrollHeight > contentElement.clientHeight + 1;
        if (this.truncatedState[reviewId] !== needsTruncation) {
          this.truncatedState[reviewId] = needsTruncation;
        }
      } else {
        this.truncatedState[reviewId] = false;
      }
    },
    checkAllTruncation() {
      if (this.activeTab === "all" || !this.isLoggedIn) {
        this.paginatedPublicReviews.forEach((review) => {
          this.checkTruncation(review.id);
        });
      }
    },
  },
  watch: {
    isLoggedIn(newVal, oldVal) {
      this.expandedState = {};
      this.truncatedState = {};
      this.initialCardMaxHeight = null;
      nextTick(() => {
        this.setupResizeObserver();
      });
    },
    tableReviewFilter() {
      this.currentPage = 1;
    },
    publicReviewFilter() {
      this.currentPublicPage = 1;
    },
    itemsPerPage() {
      nextTick(() => {
        this.checkAllTruncation();
      });
    },
  },
  beforeUpdate() {
    this.cardRefs = {};
    this.reviewContentRefs = {};
  },
  updated() {
    this.checkAllTruncation();
    if (
      (this.activeTab === "all" || !this.isLoggedIn) &&
      !this.initialCardMaxHeight &&
      this.paginatedPublicReviews.length > 0
    ) {
      this.measureAndSetInitialHeight();
    }
  },
  mounted() {
    this.authStore = useAuthStore();
    this.activeTab = this.authStore?.id ? "my" : "all";
    this.itemsPerPage =
      this.activeTab === "my" ? DEFAULT_TABLE_ITEMS_PER_PAGE : 1;
    this.fetchFilms();
    const fetchInitialData = async () => {
      if (this.activeTab === "my" && this.isLoggedIn) {
        await this.fetchFavourites();
      } else {
        await this.fetchAllReviews();
      }
      this.setupResizeObserver();
      nextTick(() => {
        if (
          (this.activeTab === "all" || !this.isLoggedIn) &&
          !this.initialCardMaxHeight
        ) {
          this.measureAndSetInitialHeight();
          this.checkAllTruncation();
          this.updateItemsPerPage();
        }
      });
    };
    fetchInitialData();
  },
  beforeUnmount() {
    clearTimeout(this.debounceTimer);
    clearTimeout(this.itemsUpdateTimer);
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }
    window.removeEventListener("resize", this.debouncedUpdateItemsPerPage);
  },
};
</script>

<style scoped>
/* === Theme Variables & Base Styles === */
.reviews-container {
  --bg-base: #111;
  --bg-primary: #1f1f1f;
  --bg-secondary: #2a2a2a;
  --bg-tertiary: #333333;
  --text-primary: #ffffff;
  --text-secondary: #eeeeee;
  --text-muted: #aaaaaa;
  --accent-gold: #ffd700;
  --accent-red: #e53e3e;
  --accent-green: #4caf50;
  --accent-blue: #2196f3;
  --border-color: #383838;
  --focus-ring-color: rgba(255, 215, 0, 0.3);

  height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background-color: var(--bg-base);
  background-image: url("https://source.unsplash.com/1600x900/?dark,abstract,texture");
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
    Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
}

/* === Header Styles === */
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
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

.tabs-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-grow: 1;
  min-width: 0;
}

.title-text {
  color: var(--text-secondary);
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
  padding: 0.6rem 1.1rem;
  white-space: nowrap;
  text-align: center;
  border-radius: 6px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  cursor: pointer;
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

/* === Controls === */
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

/* === Dropdown Panels === */
.dropdown-panel {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  z-index: 100;
  background-color: var(--bg-primary);
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

/* === Form Controls === */
.form-control {
  width: 100%;
  padding: 10px 12px;
  font-size: 1rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  border-radius: 4px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
  box-sizing: border-box;
}

.form-control::placeholder {
  color: var(--text-muted);
}

.form-control:focus {
  outline: none;
  border-color: var(--accent-gold);
  box-shadow: 0 0 0 3px var(--focus-ring-color);
}

.search-input {
  padding: 0.6rem 1rem;
  font-size: 0.95rem;
}

.filter-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 0.4rem;
}

.custom-filter-select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23aaaaaa' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1em 1em;
  padding-right: 2.5rem;
  padding-top: 0.6rem;
  padding-bottom: 0.6rem;
  font-size: 0.95rem;
}

/* === Loading Overlay === */
.loading-overlay {
  position: absolute;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.8);
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
  animation: spin 0.8s linear infinite;
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
  padding: 1.5rem;
  gap: 1.5rem;
}

.status-message.alert {
  flex-shrink: 0;
  padding: 0.8rem;
  border-radius: 4px;
  font-size: 0.95rem;
  text-align: center;
  font-weight: bold;
  border: 1px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.status-message.text-danger {
  color: var(--accent-red);
  background-color: rgba(229, 62, 62, 0.1);
  border-color: rgba(229, 62, 62, 0.4);
}

.data-container {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.table-section,
.public-reviews-section {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  height: 100%;
}

/* === Scrollable Wrappers === */
.table-wrapper,
.reviews-wrapper {
  flex-grow: 1;
  overflow-y: auto;
  padding-right: 10px;
  scrollbar-width: thin;
  scrollbar-color: #5a6268 var(--bg-secondary);
}

.table-wrapper::-webkit-scrollbar,
.reviews-wrapper::-webkit-scrollbar {
  width: 10px;
}

.table-wrapper::-webkit-scrollbar-track,
.reviews-wrapper::-webkit-scrollbar-track {
  background: var(--bg-secondary);
  border-radius: 5px;
}

.table-wrapper::-webkit-scrollbar-thumb,
.reviews-wrapper::-webkit-scrollbar-thumb {
  background-color: #5a6268;
  border-radius: 5px;
  border: 2px solid var(--bg-secondary);
}

/* === Table Styles === */
.custom-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}

.custom-table th,
.custom-table td {
  padding: 0.8rem 1rem;
  border: 1px solid var(--border-color);
  vertical-align: middle;
}

.custom-table th {
  background: var(--bg-secondary);
  color: var(--accent-gold);
  font-weight: 700;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.75px;
  text-align: left;
  position: sticky;
  top: 0;
  z-index: 1;
}

.custom-table td {
  background: var(--bg-primary);
  color: var(--text-primary);
  font-size: 0.9rem;
}

.custom-table tbody tr:hover td {
  background-color: var(--bg-secondary);
}

.custom-table .film-title-cell {
  font-weight: 600;
  color: var(--text-primary);
}

.custom-table .evaluation-cell {
  min-width: 140px;
}

.custom-table .operations-cell {
  min-width: 90px;
}

.star-rating {
  font-size: 1rem;
}

.star-icon {
  color: var(--accent-gold);
  margin: 0 1px;
}

.evaluation-text {
  padding-left: 0.2rem;
  font-size: 0.8rem;
  color: var(--text-primary);
  font-weight: 500;
}

.date-cell {
  font-size: 0.75rem !important;
  font-weight: italic;
  color: var(--text-muted) !important;
}

/* === Grid (Review Card) Styles === */
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 0.8rem;
  padding-bottom: 0.5rem;
}

.review-card {
  background: var(--bg-secondary);
  border-radius: 6px;
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
  padding: 1.25rem;
  gap: 0.75rem;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out,
    max-height 0.4s ease-in-out;
  overflow: hidden;
  color: var(--text-primary);
}

.review-card:hover {
  transform: translateY(1.5px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.6);
}

.review-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.review-author {
  font-weight: 500;
  color: var(--text-primary);
}

.edited-indicator {
  font-style: italic;
  margin-left: 0.3em;
  font-size: 0.9rem;
}

.review-film-title {
  font-size: 1.05rem;
  font-weight: 650;
  color: var(--accent-gold);
}

.film-title-rating {
  display: flex;
  justify-content: space-between;
}

.review-content {
  font-size: 0.9rem;
  color: var(--text-primary);
  line-height: 1.5;
  margin-top: 0.25rem;
}

.review-content p {
  margin: 0;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  -webkit-line-clamp: 3;
}

.review-card.is-expanded .review-content p {
  -webkit-line-clamp: unset;
}

/* "Read more" link styled as simple textual link */
.read-more-link {
  color: var(--accent-gold);
  text-decoration: none;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  margin-top: 0.5rem;
}
.read-more-link:hover,
.read-more-link:focus {
  text-decoration: underline;
}

/* === Pagination === */
.pagination-wrapper {
  padding: 1rem 0 0.5rem 0;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
  border-top: 1px solid var(--border-color);
  margin-top: auto;
  background: var(--bg-primary);
  position: sticky;
  bottom: 0;
  z-index: 10;
}

/* === Responsive Adjustments === */
@media (max-width: 1024px) {
  .reviews-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    padding: 20px;
  }
  .review-card {
    min-height: 320px;
  }
}

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
    background: transparent;
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
  .reviews-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
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
  .dropdown-panel {
    padding: 0.75rem;
  }
  .form-control {
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
    padding: 1rem;
  }
}

/* === Animation === */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
