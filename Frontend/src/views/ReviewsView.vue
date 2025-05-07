<template>
  <div class="reviews-container">
    <div class="header-container" ref="headerContainerRef">
      <div class="tabs-wrapper">
        <h3 class="title-text clickable" :class="{ 'active-tab': activeTab === 'all' || !isLoggedIn }"
          @click="switchTab('all')" role="tab" :aria-selected="activeTab === 'all' || !isLoggedIn" tabindex="0">
          All Reviews
        </h3>
        <h3 v-if="isLoggedIn" class="title-text clickable" :class="{ 'active-tab': activeTab === 'my' }"
          @click="switchTab('my')" role="tab" :aria-selected="activeTab === 'my'" tabindex="0">
          My Reviews
        </h3>
      </div>
      <div class="controls-container">
        <div class="control-buttons">
          <div class="control-item" @click="toggleControl('search')" title="Search Reviews"
            aria-label="Toggle Search Input" :aria-expanded="showSearch" role="button" tabindex="0">
            <i class="bi bi-search"></i>
          </div>
          <div class="control-item" @click="
            toggleControl(activeTab === 'my' ? 'tableFilter' : 'publicFilter')
            " title="Filter/Sort Reviews" aria-label="Toggle Filter/Sort Options" :aria-expanded="activeTab === 'my' ? showTableFilter : showPublicFilter
              " role="button" tabindex="0">
            <i class="bi bi-funnel"></i>
          </div>
        </div>
        <transition name="fade-slide">
          <div v-if="showSearch" class="search-container dropdown-panel">
            <div class="search-wrapper">
              <input type="text" v-model="searchQuery" placeholder="Search reviews..." class="form-control search-input"
                @input="handleSearch" aria-label="Search Reviews Input" />
            </div>
          </div>
        </transition>
        <transition name="fade-slide">
          <div v-if="showTableFilter && isLoggedIn && activeTab === 'my'" class="filter-container dropdown-panel">
            <div class="filter-wrapper">
              <label for="tableFilter" class="filter-label">Sort My Reviews:</label>
              <select id="tableFilter" v-model="tableReviewFilter" class="form-control custom-filter-select"
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
          <div v-if="showPublicFilter && (activeTab === 'all' || !isLoggedIn)" class="filter-container dropdown-panel">
            <div class="filter-wrapper">
              <label for="publicFilter" class="filter-label">Sort All Reviews:</label>
              <select id="publicFilter" v-model="publicReviewFilter" class="form-control custom-filter-select"
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
        <div class="spinner" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="loading-text">Loading Reviews...</div>
      </div>
    </div>

    <div v-else class="content-area">
      <div v-if="errorMessages" class="status-message text-danger alert" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ errorMessages }}
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
                  <th class="text-center">Created</th>
                  <th class="text-center">Updated</th>
                  <th class="text-center">Operations</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="favourite in paginatedFavourites" :key="favourite.id">
                  <td data-label="Film" class="film-title-cell">
                    {{ favourite.filmTitle || "Unknown Film" }}
                  </td>
                  <td data-label="Evaluation" class="text-center evaluation-cell">
                    <div class="star-rating d-inline-flex align-items-center">
                      <i v-for="starIndex in 5" :key="starIndex" class="bi star-icon"
                        :class="getStarClass(favourite, starIndex)" :aria-label="`Star ${starIndex}`"></i>
                      <small class="evaluation-text">
                        ({{ formatEvaluation(favourite.evaluation) }})
                      </small>
                    </div>
                  </td>
                  <td data-label="Created" class="text-center fst-italic date-cell">
                    {{ formatDate(favourite.created_at) }}
                  </td>
                  <td data-label="Updated" class="text-center fst-italic date-cell">
                    {{ formatDate(favourite.updated_at) }}
                  </td>
                  <td data-label="Operations" class="text-nowrap text-center operations-cell">
                    <OperationsCrud @onClickDelete="onClickDelete(favourite)" @onClickUpdate="onClickUpdate(favourite)"
                      :data="favourite" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pagination-wrapper" ref="tablePaginationRef" v-if="totalFavPages > 1">
            <Paginator :pageNumber="currentPage" :numberOfPages="totalFavPages" @paging="handlePageChange" />
          </div>
        </div>

        <div v-show="activeTab === 'all' || !isLoggedIn" class="public-reviews-section" ref="reviewsWrapperRef">
          <div class="reviews-scroll-content">
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
              <div v-for="review in paginatedPublicReviews" :key="review.id" class="review-card"
                :ref="(el) => (cardRefs[review.id] = el)" :class="{ 'is-expanded': isReviewExpanded(review.id) }"
                :style="getCardStyle(review.id)">
                <div class="card-inner">
                  <div class="review-header">
                    <span class="review-author">
                      <i class="bi bi-person-fill me-1"></i>
                      {{ review.userName || "Anonymous" }}
                    </span>
                    <span class="review-date">
                      <i class="bi bi-calendar3 me-1"></i>
                      {{ formatDate(review.updated_at) }}
                      <span v-if="review.created_at !== review.updated_at" class="edited-indicator">
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
                      <i v-for="starIndex in 5" :key="`star-${review.id}-${starIndex}`" class="bi star-icon"
                        :class="getStarClass(review, starIndex)" :aria-label="`Star ${starIndex}`"></i>
                      <small class="evaluation-text">
                        ({{ formatEvaluation(review.evaluation) }})
                      </small>
                    </div>
                  </div>
                  <div class="review-content" :ref="(el) => (reviewContentRefs[review.id] = el)">
                    <p :class="{ truncated: !isReviewExpanded(review.id) && doesReviewNeedTruncation(review.id) }">
                      {{ review.content }}
                    </p>
                    <a v-if="doesReviewNeedTruncation(review.id)" @click.prevent="toggleReadMore(review.id)"
                      class="read-more-link" role="button" tabindex="0" :aria-expanded="isReviewExpanded(review.id)">
                      {{
                        isReviewExpanded(review.id) ? "Show less" : "Read more"
                      }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="pagination-wrapper" ref="publicPaginationRef" v-if="totalPublicPages > 1">
            <Paginator :pageNumber="currentPublicPage" :numberOfPages="totalPublicPages"
              @paging="handlePublicPageChange" />
          </div>
        </div>
      </div>
    </div>

    <div v-if="isModalVisible" class="modal-container" @click.self="closeModal">
      <div v-if="modalState === 'Update'" class="modal-content modal-content-form" role="dialog" aria-modal="true"
        :aria-labelledby="modalFormTitleId">
        <h3 :id="modalFormTitleId" class="visually-hidden">{{ modalTitle || 'Update Review' }}</h3>
        <ReviewForm :itemForm="modalItem" :films="films" :isUpdate="true" @saveItem="saveItemHandler"
          @closeModal="closeModal" />
      </div>

      <div v-else-if="modalState === 'Delete'" class="modal-content delete-modal" role="dialog" aria-modal="true"
        :aria-labelledby="modalConfirmTitleId" aria-describedby="modalConfirmDescId">
        <h5 class="modal-title" :id="modalConfirmTitleId">{{ modalTitle }}</h5>
        <p class="modal-message" :id="modalConfirmDescId">{{ modalMessageYesNo }}</p>
        <div class="modal-actions">
          <button @click="yesEventHandler" class="btn btn-yes">{{ modalYes || 'Yes' }}</button>
          <button @click="closeModal" class="btn btn-cancel">{{ modalNo || 'Cancel' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useAuthStore } from "@/stores/useAuthStore";
import { BASE_URL } from "../helpers/baseUrls";
import { nextTick, ref, onMounted, onBeforeUnmount, computed, watch } from "vue";

// Import Child Components
import Paginator from "@/components/Paginator.vue";
import OperationsCrud from "@/components/OperationsCrud.vue";
import ReviewForm from "@/components/forms/ReviewForm.vue";

// --- Constants ---
const DEFAULT_TABLE_ITEMS_PER_PAGE = 10;
const DEFAULT_GRID_ITEMS_PER_PAGE = 12;

export default {
  components: { Paginator, OperationsCrud, ReviewForm },
  setup() {
    // --- Refs for DOM elements ---
    const headerContainerRef = ref(null);
    const tableWrapperRef = ref(null);
    const reviewsWrapperRef = ref(null);
    const tablePaginationRef = ref(null);
    const publicPaginationRef = ref(null);
    const cardRefs = ref({});
    const reviewContentRefs = ref({});

    // --- Reactive State ---
    const authStore = useAuthStore();
    const loading = ref(false);
    const errorMessages = ref(null);
    const activeTab = ref(authStore?.id ? "my" : "all");
    const tableReviewFilter = ref("newest");
    const publicReviewFilter = ref("newest");
    const searchQuery = ref("");
    const films = ref([]);
    const favourites = ref([]);
    const publicReviews = ref([]);
    const currentPage = ref(1);
    const currentPublicPage = ref(1);
    const itemsPerPage = ref(authStore?.id ? DEFAULT_TABLE_ITEMS_PER_PAGE : DEFAULT_GRID_ITEMS_PER_PAGE);
    const showSearch = ref(false);
    const showTableFilter = ref(false);
    const showPublicFilter = ref(false);
    const isModalVisible = ref(false);
    const modalItem = ref({});
    const modalMessageYesNo = ref(null);
    const modalState = ref("Read"); // Possible states: Read, Update, Delete
    const modalTitle = ref(null);
    const modalYes = ref(null); // Text for 'Yes' button in confirmation
    const modalNo = ref(null);  // Text for 'No' button in confirmation
    // modalSize is no longer used directly as CSS handles sizing
    const selectedRowId = ref(null);
    const expandedState = ref({});
    const truncatedState = ref({});

    // Accessibility refs for modals
    const modalFormTitleId = 'modal-form-title';
    const modalConfirmTitleId = 'modal-confirm-title';
    const modalConfirmDescId = 'modal-confirm-description';


    // --- Timers ---
    let debounceTimer = null;

    // --- Computed Properties ---
    const isLoggedIn = computed(() => !!authStore?.id);

    const filteredFavourites = computed(() => {
      return filterAndSortReviews(
        favourites.value,
        searchQuery.value,
        tableReviewFilter.value,
        false
      );
    });

    const filteredPublicReviews = computed(() => {
      return filterAndSortReviews(
        publicReviews.value,
        searchQuery.value,
        publicReviewFilter.value,
        true
      );
    });

    const totalFavPages = computed(() => {
      return Math.ceil(filteredFavourites.value.length / DEFAULT_TABLE_ITEMS_PER_PAGE);
    });

    const paginatedFavourites = computed(() => {
      const start = (currentPage.value - 1) * DEFAULT_TABLE_ITEMS_PER_PAGE;
      const end = start + DEFAULT_TABLE_ITEMS_PER_PAGE;
      return filteredFavourites.value.slice(start, end);
    });

    const totalPublicPages = computed(() => {
      const perPage = Math.max(1, itemsPerPage.value);
      return Math.ceil(filteredPublicReviews.value.length / perPage);
    });

    const paginatedPublicReviews = computed(() => {
      const perPage = Math.max(1, itemsPerPage.value);
      const start = (currentPublicPage.value - 1) * perPage;
      const end = start + perPage;
      const paginated = filteredPublicReviews.value.slice(start, end);
      nextTick(() => checkAllTruncation(paginated));
      return paginated;
    });

    // --- Methods ---
    const _fetchApi = async (url, config = {}, method = "get", data = null) => {
      loading.value = true;
      errorMessages.value = null;
      const token = authStore?.token;
      const headers = {
        Accept: "application/json",
        ...(method !== "get" && method !== "delete" && { "Content-Type": "application/json" }),
        ...(token && { Authorization: `Bearer ${token}` }),
        ...config.headers,
      };
      try {
        const axiosConfig = { ...config, headers, method, url };
        if (data) axiosConfig.data = data;
        const response = await axios(axiosConfig);
        return response.data;
      } catch (error) {
        console.error(`API Error (${method.toUpperCase()} ${url}):`, error.response || error);
        errorMessages.value = `Operation failed: ${error.response?.data?.message || error.message || "An unknown error occurred."}`;
        setTimeout(() => { errorMessages.value = null; }, 7000);
        return null;
      } finally {
        loading.value = false;
      }
    };

    const fetchFavourites = async () => {
      if (!isLoggedIn.value) return;
      const userId = authStore.id;
      const responseData = await _fetchApi(`${BASE_URL}/favouritesByUserId/${userId}`);
      if (responseData?.data) {
        favourites.value = responseData.data.map((fav) => ({
          ...fav,
          evaluation: Number(fav.evaluation) || 0,
        }));
      } else {
        favourites.value = [];
      }
      if (currentPage.value > totalFavPages.value && totalFavPages.value > 0) {
        currentPage.value = totalFavPages.value;
      } else if (currentPage.value < 1 && filteredFavourites.value.length > 0) {
        currentPage.value = 1;
      } else if (filteredFavourites.value.length === 0) {
        currentPage.value = 1;
      }
    };

    const fetchAllReviews = async () => {
      const responseData = await _fetchApi(`${BASE_URL}/favourites`);
      if (responseData?.data) {
        publicReviews.value = responseData.data.map((review) => ({
          ...review,
          userName: review.userName || "Anonymous",
          filmTitle: review.filmTitle || "Unknown Film",
          evaluation: Number(review.evaluation) || 0,
          content: review.content,
        }));
      } else {
        publicReviews.value = [];
      }
      updateItemsPerPage();
      if (currentPublicPage.value > totalPublicPages.value && totalPublicPages.value > 0) {
        currentPublicPage.value = totalPublicPages.value;
      } else if (currentPublicPage.value < 1 && filteredPublicReviews.value.length > 0) {
        currentPublicPage.value = 1;
      } else if (filteredPublicReviews.value.length === 0) {
        currentPublicPage.value = 1;
      }
    };

    const fetchFilms = async () => {
      const responseData = await _fetchApi(`${BASE_URL}/films`);
      films.value = (responseData && responseData.data) || [];
    };

    const updateItemsPerPage = () => {
      if (activeTab.value === "all" || !isLoggedIn.value) {
        itemsPerPage.value = DEFAULT_GRID_ITEMS_PER_PAGE;
      } else {
        itemsPerPage.value = DEFAULT_TABLE_ITEMS_PER_PAGE;
      }
      nextTick(() => {
        if (activeTab.value === 'all' || !isLoggedIn.value) {
          if (currentPublicPage.value > totalPublicPages.value && totalPublicPages.value > 0) {
            currentPublicPage.value = totalPublicPages.value;
          } else if (currentPublicPage.value < 1 && totalPublicPages.value > 0) {
            currentPublicPage.value = 1;
          } else if (totalPublicPages.value === 0) {
            currentPublicPage.value = 1;
          }
        } else {
          if (currentPage.value > totalFavPages.value && totalFavPages.value > 0) {
            currentPage.value = totalFavPages.value;
          } else if (currentPage.value < 1 && totalFavPages.value > 0) {
            currentPage.value = 1;
          } else if (totalFavPages.value === 0) {
            currentPage.value = 1;
          }
        }
      });
    };


    const switchTab = async (tab) => {
      if (tab === "my" && !isLoggedIn.value) return;
      if (activeTab.value === tab) return;

      activeTab.value = tab;
      searchQuery.value = "";
      errorMessages.value = null;
      showSearch.value = false;
      showTableFilter.value = false;
      showPublicFilter.value = false;
      expandedState.value = {};
      truncatedState.value = {};

      updateItemsPerPage();

      if (tab === "all") {
        currentPublicPage.value = 1;
        if (publicReviews.value.length === 0) {
          await fetchAllReviews();
        }
      } else if (tab === "my") {
        currentPage.value = 1;
        if (favourites.value.length === 0 && isLoggedIn.value) {
          await fetchFavourites();
        }
      }
    };

    const handleSearch = () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        currentPage.value = 1;
        currentPublicPage.value = 1;
      }, 300);
    };

    const toggleControl = (type) => {
      if (type === "search") {
        showSearch.value = !showSearch.value;
        showTableFilter.value = false;
        showPublicFilter.value = false;
      } else if (type === "tableFilter") {
        showTableFilter.value = !showTableFilter.value;
        showSearch.value = false;
        showPublicFilter.value = false;
      } else if (type === "publicFilter") {
        showPublicFilter.value = !showPublicFilter.value;
        showSearch.value = false;
        showTableFilter.value = false;
      }
    };

    const onClickDelete = (item) => {
      modalState.value = "Delete";
      modalTitle.value = "Confirm Deletion";
      modalMessageYesNo.value = `Are you sure you want to delete your review for "${item.filmTitle || "this film"}"? This action cannot be undone.`;
      modalYes.value = "Yes"; // User's CSS uses "Yes"
      modalNo.value = "Cancel"; // User's CSS uses "Cancel"
      selectedRowId.value = item.id;
      isModalVisible.value = true;
    };

    const onClickUpdate = (item) => {
      modalState.value = "Update";
      selectedRowId.value = item.id;
      modalItem.value = { ...item, evaluation: Number(item.evaluation) || 0 };
      modalTitle.value = `Update Review for "${item.filmTitle || "Unknown Film"}"`;
      // modalSize.value = "lg"; // No longer used, CSS handles size
      isModalVisible.value = true;
    };

    const deleteReview = async () => {
      if (!selectedRowId.value) return;
      const result = await _fetchApi(
        `${BASE_URL}/favourites/${selectedRowId.value}`,
        {},
        "delete"
      );
      if (result !== null) {
        await fetchFavourites();
      }
      closeModal();
    };

    const saveItemHandler = async (formData) => {
      if (modalState.value !== "Update" || !selectedRowId.value) return;
      const payload = {
        evaluation: Number(formData.evaluation),
        filmId: formData.filmId,
        content: formData.content,
      };
      const result = await _fetchApi(
        `${BASE_URL}/favourites/${selectedRowId.value}`,
        {},
        "patch",
        payload
      );
      if (result !== null) {
        await fetchFavourites();
        // Optionally refetch all reviews if the updated one could appear there
        // if (publicReviews.value.some(r => r.id === selectedRowId.value)) {
        //   await fetchAllReviews();
        // }
      }
      closeModal();
    };

    const yesEventHandler = () => {
      if (modalState.value === "Delete") {
        deleteReview();
      }
    };

    const closeModal = () => {
      isModalVisible.value = false;
      // It's good practice to reset modal related states after it closes,
      // especially if the modal content depends on them.
      // A timeout can be used if there's a closing animation.
      setTimeout(() => {
        modalState.value = "Read"; // Reset to a neutral state
        modalItem.value = {};
        selectedRowId.value = null;
        modalTitle.value = null;
        modalMessageYesNo.value = null;
        modalYes.value = null;
        modalNo.value = null;
      }, 300); // Match CSS transition if any, or remove if no transition
    };

    const filterAndSortReviews = (sourceArray, query, filterType, isPublic = false) => {
      let filtered = [...sourceArray];
      if (query) {
        const lowerQuery = query.toLowerCase().trim();
        if (lowerQuery) {
          filtered = filtered.filter((review) => {
            const titleMatch = (review.filmTitle || "").toLowerCase().includes(lowerQuery);
            const userMatch = isPublic && (review.userName || "").toLowerCase().includes(lowerQuery);
            const contentMatch = !isPublic && (review.content || "").toLowerCase().includes(lowerQuery);
            return titleMatch || userMatch || contentMatch;
          });
        }
      }
      switch (filterType) {
        case "ABC":
          filtered.sort((a, b) => (a.filmTitle || "").localeCompare(b.filmTitle || "", undefined, { sensitivity: 'base' }));
          break;
        case "highToLow":
          filtered.sort((a, b) => (Number(b.evaluation) || 0) - (Number(a.evaluation) || 0));
          break;
        case "lowToHigh":
          filtered.sort((a, b) => (Number(a.evaluation) || 0) - (Number(b.evaluation) || 0));
          break;
        case "newest":
          filtered.sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at));
          break;
        case "oldest":
          filtered.sort((a, b) => new Date(a.created_at || a.updated_at) - new Date(b.created_at || b.updated_at));
          break;
      }
      return filtered;
    };

    const getStarClass = (item, starIndex) => {
      const evaluation = Number(item.evaluation) || 0;
      if (evaluation >= starIndex) return "bi-star-fill";
      if (evaluation >= starIndex - 0.5) return "bi-star-half";
      return "bi-star";
    };

    const formatEvaluation = (value) => {
      const num = Number(value);
      return isNaN(num) ? "N/A" : num.toFixed(1);
    };

    const formatDate = (dateString) => {
      if (!dateString) return "N/A";
      try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "Invalid Date";
        return date.toLocaleDateString("en-CA", { year: "numeric", month: "2-digit", day: "2-digit" });
      } catch (e) {
        console.warn("Error formatting date:", dateString, e);
        return "N/A";
      }
    };

    const handlePageChange = (pageInfo) => {
      if (typeof pageInfo.pageNumber === "number" && pageInfo.pageNumber !== currentPage.value) {
        currentPage.value = pageInfo.pageNumber;
      }
    };

    const handlePublicPageChange = (pageInfo) => {
      if (typeof pageInfo.pageNumber === "number" && pageInfo.pageNumber !== currentPublicPage.value) {
        currentPublicPage.value = pageInfo.pageNumber;
        nextTick(() => {
          if (reviewsWrapperRef.value) {
            const scrollContent = reviewsWrapperRef.value.querySelector('.reviews-scroll-content');
            if (scrollContent) scrollContent.scrollTop = 0;
            else reviewsWrapperRef.value.scrollTop = 0;
          }
        });
      }
    };

    // --- Read More / Less Methods ---
    const isReviewExpanded = (reviewId) => !!expandedState.value[reviewId];
    const doesReviewNeedTruncation = (reviewId) => !!truncatedState.value[reviewId];
    const toggleReadMore = (reviewId) => { expandedState.value[reviewId] = !expandedState.value[reviewId]; };

    const checkTruncation = (reviewId) => {
      const contentElement = reviewContentRefs.value[reviewId];
      if (contentElement) {
        const needsTruncation = contentElement.scrollHeight > contentElement.clientHeight + 1;
        if (truncatedState.value[reviewId] !== needsTruncation) {
          truncatedState.value[reviewId] = needsTruncation;
        }
      } else {
        if (truncatedState.value[reviewId] !== false) {
          truncatedState.value[reviewId] = false;
        }
      }
    };

    const checkAllTruncation = (reviewsToCheck) => {
      const newTruncatedState = { ...truncatedState.value };
      let changed = false;
      reviewsToCheck.forEach(review => {
        const contentElement = reviewContentRefs.value[review.id];
        let needsTruncation = false;
        if (contentElement && contentElement.offsetParent !== null) { // Check if element is visible
          needsTruncation = contentElement.scrollHeight > contentElement.clientHeight + 1;
        }
        if (newTruncatedState[review.id] !== needsTruncation) {
          newTruncatedState[review.id] = needsTruncation;
          changed = true;
        }
      });
      if (changed) truncatedState.value = newTruncatedState;
    };

    const getCardStyle = (reviewId) => {
      const expanded = isReviewExpanded(reviewId);
      if (expanded) return { maxHeight: 'none', overflow: 'visible' }; // Or a very large max-height like '1000px'
      return {}; // Collapsed max-height is now primarily CSS driven by --card-max-height-collapsed
    };

    // --- Watchers ---
    watch(isLoggedIn, (newValue, oldValue) => {
      if (newValue !== oldValue) {
        expandedState.value = {};
        truncatedState.value = {};
        activeTab.value = newValue ? 'my' : 'all';
        switchTab(activeTab.value);
      }
    });

    watch(tableReviewFilter, () => { currentPage.value = 1; });
    watch(publicReviewFilter, () => { currentPublicPage.value = 1; });

    watch(paginatedPublicReviews, (newPaginatedList) => {
      nextTick(() => checkAllTruncation(newPaginatedList));
    }, { deep: true, immediate: false });


    // --- Lifecycle Hooks ---
    onMounted(async () => {
      fetchFilms();
      updateItemsPerPage(); // Set initial itemsPerPage

      if (activeTab.value === "my" && isLoggedIn.value) {
        await fetchFavourites();
      } else {
        activeTab.value = 'all';
        await fetchAllReviews();
      }
    });

    onBeforeUnmount(() => {
      clearTimeout(debounceTimer);
    });

    return {
      headerContainerRef, tableWrapperRef, reviewsWrapperRef, tablePaginationRef, publicPaginationRef, cardRefs, reviewContentRefs,
      authStore, loading, errorMessages, activeTab, tableReviewFilter, publicReviewFilter, searchQuery, films,
      favourites, publicReviews, currentPage, currentPublicPage, itemsPerPage, showSearch, showTableFilter, showPublicFilter,
      isModalVisible, modalItem, modalMessageYesNo, modalState, modalTitle, modalYes, modalNo, selectedRowId,
      expandedState, truncatedState, modalFormTitleId, modalConfirmTitleId, modalConfirmDescId,
      isLoggedIn, filteredFavourites, filteredPublicReviews, totalFavPages, paginatedFavourites, totalPublicPages, paginatedPublicReviews,
      switchTab, handleSearch, toggleControl, onClickDelete, onClickUpdate, saveItemHandler, yesEventHandler, closeModal,
      getStarClass, formatEvaluation, formatDate, handlePageChange, handlePublicPageChange,
      isReviewExpanded, doesReviewNeedTruncation, toggleReadMore, getCardStyle,
    };
  },
  beforeUpdate() {
    // This is useful in Options API for resetting refs that are collected in loops.
    // In Composition API with `ref({})` for `cardRefs` and `reviewContentRefs`,
    // Vue handles the updates to these objects more directly when elements are added/removed.
    // So, explicitly clearing them here might not be necessary if refs are bound as
    // :ref="(el) => (cardRefs[item.id] = el)"
    // this.cardRefs = {};
    // this.reviewContentRefs = {};
  },
  updated() {
    // This hook is called after the component has updated its DOM tree.
    // Useful for DOM-dependent operations.
    // For example, if `checkAllTruncation` needed to run after *any* DOM update affecting the grid:
    // if (this.activeTab === 'all' || !this.isLoggedIn) {
    //    this.checkAllTruncation(this.paginatedPublicReviews);
    // }
  }
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
  --card-max-height-collapsed: 300px;

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

/* Visually hidden class for accessibility */
.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
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
  position: sticky;
  top: 0;
  z-index: 20;
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

/* === Section Layout (Table and Grid) === */
.table-section,
.public-reviews-section {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  height: 100%;
}

/* === Scrollable Wrappers (Table and Grid Content) === */
.table-wrapper,
.reviews-scroll-content {
  flex-grow: 1;
  overflow-y: auto;
  padding-right: 10px;
  scrollbar-width: thin;
  scrollbar-color: #5a6268 var(--bg-secondary);
}

.table-wrapper::-webkit-scrollbar,
.reviews-scroll-content::-webkit-scrollbar {
  width: 10px;
}

.table-wrapper::-webkit-scrollbar-track,
.reviews-scroll-content::-webkit-scrollbar-track {
  background: var(--bg-secondary);
  border-radius: 5px;
}

.table-wrapper::-webkit-scrollbar-thumb,
.reviews-scroll-content::-webkit-scrollbar-thumb {
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
  min-width: 75px;
  padding: 0;
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
  color: var(--text-muted) !important;
}


/* === Grid (Review Card) Styles === */
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
  padding-bottom: 1rem;
}

.review-card {
  background: var(--bg-secondary);
  border-radius: 8px;
  border: 1px solid var(--border-color);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.4);
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  overflow: hidden;
  color: var(--text-primary);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out, max-height 0.4s ease-out;
  max-height: var(--card-max-height-collapsed);
}

.review-card.is-expanded {
  max-height: 1200px;
}


.review-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.5);
}

.card-inner {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  flex-grow: 1;
}


.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
  color: var(--text-muted);
  flex-wrap: wrap;
  gap: 0.5rem;
}

.review-author {
  font-weight: 500;
  color: var(--text-primary);
  display: inline-flex;
  align-items: center;
}

.review-date {
  display: inline-flex;
  align-items: center;
}

.edited-indicator {
  font-style: italic;
  margin-left: 0.3em;
  font-size: 0.9em;
}

.film-title-rating {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}


.review-film-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--accent-gold);
  display: inline-flex;
  align-items: center;
  flex-shrink: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.review-content {
  font-size: 0.9rem;
  color: var(--text-secondary);
  line-height: 1.5;
  margin-top: 0.25rem;
}

.review-content p {
  margin: 0;
}

.review-content p.truncated {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  max-height: calc(1.5em * 3);
}

.review-card.is-expanded .review-content p.truncated {
  -webkit-line-clamp: unset;
  max-height: none;
  overflow: visible;
}


.read-more-link {
  color: var(--accent-blue);
  text-decoration: none;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  margin-top: 0.5rem;
  display: inline-block;
  transition: color 0.2s ease;
}

.read-more-link:hover,
.read-more-link:focus {
  color: var(--accent-gold);
  text-decoration: underline;
}

/* === No Data Message === */
.no-data-message {
  padding: 2rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 1.1rem;
  font-style: italic;
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
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

/* === MODAL STYLES (User Provided) === */
/* ---- Modal Container ---- */
.modal-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  /* semi-transparent overlay */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  /* Ensure modal is on top */
}

/* ---- Generic Modal Content (base for both delete and form) ---- */
.modal-content {
  background: var(--bg-primary, #1f1f1f);
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.8);
  width: 90%;
  /* Responsive width */
}

/* ---- Delete Modal Specific Content Styling ---- */
.modal-content.delete-modal {
  max-width: 400px;
  /* Max width for smaller confirmation modals */
  text-align: center;
}

/* ---- Update Form Modal Specific Content Styling ---- */
.modal-content.modal-content-form {
  max-width: 700px;
  /* Wider for forms */
  text-align: left;
  /* Forms are typically left-aligned */
  /* ReviewForm component will handle its internal layout and scrolling if needed */
}


/* ---- Modal Titles & Messages (Primarily for Delete Modal) ---- */
.modal-title {
  /* Used by delete modal's h5 */
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #a71010;
  /* Specific red color from user's CSS */
}

.modal-message {
  /* Used by delete modal's p */
  font-size: 1rem;
  margin-bottom: 1.5rem;
  color: var(--text-primary);
  opacity: 0.7;
}

/* ---- Modal Actions (Primarily for Delete Modal) ---- */
.modal-actions {
  display: flex;
  justify-content: center;
  /* Center buttons for delete modal */
  gap: 1rem;
  margin-top: 1rem;
  /* Added for spacing */
}

/* ---- Button Styles for Modal (Primarily for Delete Modal) ---- */
/* Common modal button base style */
.modal-actions .btn {
  min-width: 100px;
  padding: 10px 16px;
  font-size: 1rem;
  font-weight: 700;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s ease, box-shadow 0.3s ease;
}

/* Style for the "Yes" button */
.btn-yes {
  background: #ffd700;
  /* yellow background */
  color: #1f1f1f;
  /* dark text for contrast */
  box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3);
}

.btn-yes:hover:not(:disabled) {
  background: #ffc107;
  /* Slightly darker yellow */
  box-shadow: 0 4px 10px rgba(255, 215, 0, 0.5);
}

/* Style for the "Cancel" button in modal */
.btn-cancel {
  background: #555;
  /* dark background */
  color: #fff;
  border: 1px solid #777;
  /* Subtle border */
}

.btn-cancel:hover:not(:disabled) {
  background: #666;
  /* Slightly lighter dark */
  border-color: #888;
}


/* === Responsive Adjustments === */
@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    align-items: stretch;
    padding: 0.75rem 1rem;
    position: static;
  }

  .content-area {
    padding: 1rem;
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

  .custom-table thead {
    display: none;
  }

  .custom-table tbody,
  .custom-table tr,
  .custom-table td {
    display: block;
    width: 100%;
    box-sizing: border-box;
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
    border-left: none;
    border-right: none;
    border-top: none;
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

  .review-card {
    max-height: var(--card-max-height-collapsed);
  }

  .review-card.is-expanded {
    max-height: 1200px;
  }

  .review-content p.truncated {
    -webkit-line-clamp: 4;
    max-height: calc(1.5em * 4);
  }

  /* Responsive modal content */
  .modal-content.delete-modal,
  .modal-content.modal-content-form {
    width: 90%;
    padding: 1.5rem;
  }

  .modal-title {
    font-size: 1.2rem;
  }

  .modal-message {
    font-size: 0.9rem;
  }

  .modal-actions .btn {
    padding: 8px 12px;
    font-size: 0.9rem;
    min-width: 80px;
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

  .modal-content.delete-modal,
  .modal-content.modal-content-form {
    padding: 1rem;
    /* Further reduce padding on very small screens */
  }

  .modal-title {
    font-size: 1.1rem;
  }

  .modal-actions {
    flex-direction: column;
    gap: 0.5rem;
  }

  /* Stack buttons on very small screens */
  .modal-actions .btn {
    width: 100%;
  }

  /* Make buttons full width in column layout */
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
