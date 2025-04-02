<template>
  <div class="film-reviews">
    <div class="header-container">
      <div class="tabs-wrapper">
        <!-- Reversed tab order -->
        <h3
          class="title-text clickable"
          :class="{ 'active-tab': activeTab === 'all' || !isLoggedIn }"
          @click="switchTab('all')"
        >
          All Reviews
        </h3>
        <h3
          class="title-text clickable"
          :class="{ 'active-tab': activeTab === 'my' }"
          @click="switchTab('my')"
          v-if="isLoggedIn"
        >
          My Reviews
        </h3>
      </div>

      <!-- Collapsible controls container -->
      <div class="controls-container">
        <!-- Filter toggle buttons -->
        <div class="control-buttons">
          <div class="control-item" @click="toggleControl('search')">
            <i class="bi bi-search"></i>
          </div>
          <div
            class="control-item"
            @click="
              toggleControl(activeTab === 'my' ? 'tableFilter' : 'publicFilter')
            "
          >
            <i class="bi bi-funnel"></i>
          </div>
        </div>

        <!-- Collapsible search -->
        <div v-if="showSearch" class="search-container">
          <div class="search-wrapper">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search reviews..."
              class="search-input"
              @input="handleSearch"
            />
          </div>
        </div>

        <!-- Collapsible public filter -->
        <div
          v-if="showPublicFilter && (activeTab === 'all' || !isLoggedIn)"
          class="filter-container"
        >
          <div class="filter-wrapper position-relative">
            <select
              id="publicFilter"
              v-model="publicReviewFilter"
              class="custom-filter-select"
            >
              <option value="ABC">Film Title (A-Z)</option>
              <option value="highToLow">Evaluation: High to Low</option>
              <option value="lowToHigh">Evaluation: Low to High</option>
            </select>
          </div>
        </div>

        <!-- Collapsible table filter -->
        <div
          v-if="showTableFilter && isLoggedIn && activeTab === 'my'"
          class="filter-container"
        >
          <div class="filter-wrapper position-relative">
            <select
              id="tableFilter"
              v-model="tableReviewFilter"
              class="custom-filter-select"
            >
              <option value="ABC">Film Title (A-Z)</option>
              <option value="highToLow">Evaluation: High to Low</option>
              <option value="lowToHigh">Evaluation: Low to High</option>
              <option value="newest">Date: Newest First</option>
              <option value="oldest">Date: Oldest First</option>
            </select>
          </div>
        </div>
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

    <div v-else>
      <div class="container">
        <div v-show="isLoggedIn && activeTab === 'my'" class="table-container">
          <div class="table-wrapper" :style="{ height: tableHeight + 'px' }">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>Film</th>
                  <th class="text-center">Evaluation</th>
                  <th>Date</th>
                  <th class="text-center">
                    <div
                      class="d-flex align-items-center justify-content-center gap-2"
                    >
                      <span>Operations</span>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="favourite in paginatedFavourites"
                  :key="favourite.id"
                >
                  <td data-label="Film" class="film">
                    {{ favourite.filmTitle || "Unknown Film" }}
                  </td>
                  <td data-label="Evaluation" class="text-center">
                    <div class="star-rating d-inline-flex align-items-center">
                      <i
                        v-for="starIndex in 5"
                        :key="starIndex"
                        class="bi mx-1 text-warning"
                        :class="{
                          'bi-star-fill': getEvaluation(favourite) >= starIndex,
                          'bi-star-half':
                            getEvaluation(favourite) + 0.5 >= starIndex &&
                            getEvaluation(favourite) < starIndex,
                          'bi-star': getEvaluation(favourite) + 0.5 < starIndex,
                        }"
                      >
                      </i>
                      <small class="text-muted ms-2"
                        >({{ formatEvaluation(favourite.evaluation) }})</small
                      >
                    </div>
                  </td>
                  <td data-label="Date" class="date">
                    {{ formatDate(favourite.created_at) }}
                  </td>
                  <td class="text-nowrap text-center">
                    <OperationsCrud
                      @onClickDelete="onClickDelete"
                      @onClickUpdate="onClickUpdate"
                      :data="favourite"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            class="pagination-container"
            v-if="filteredFavourites.length >= dynamicItemsPerPage"
          >
            <Paginator
              :pageNumber="currentPage"
              :numberOfPages="totalPages"
              :pagesArray="pagesArray"
              @paging="handlePageChange"
            />
          </div>
        </div>

        <div
          v-if="activeTab === 'all' || !isLoggedIn"
          class="public-reviews-container"
        >
          <div
            class="reviews-wrapper"
            :style="{ height: reviewsHeight + 'px' }"
          >
            <div
              v-if="filteredPublicReviews.length > 0"
              class="reviews-container"
            >
              <div
                v-for="review in paginatedPublicReviews"
                :key="review.id"
                class="review-card"
              >
                <div class="review-header">
                  <div class="review-meta">
                    <span class="review-author">{{
                      review.userName || "Anonymous"
                    }}</span>
                    <span class="review-date">{{
                      formatDate(review.created_at)
                    }}</span>
                  </div>
                </div>
                <div class="review-film-title">
                  {{ review.filmTitle || "Unknown Film" }}
                </div>
                <div class="review-content">
                  {{ review.content || randomDefaultReview() }}
                </div>
                <div class="star-rating d-inline-flex align-items-center">
                  <i
                    v-for="starIndex in 5"
                    :key="`star-${review.id}-${starIndex}`"
                    class="bi mx-1 text-warning"
                    :class="{
                      'bi-star-fill': getEvaluation(review) >= starIndex,
                      'bi-star-half':
                        getEvaluation(review) + 0.5 >= starIndex &&
                        getEvaluation(review) < starIndex,
                      'bi-star': getEvaluation(review) + 0.5 < starIndex,
                    }"
                  >
                  </i>
                  <small class="text-muted ms-2"
                    >({{ formatEvaluation(review.evaluation) }})</small
                  >
                </div>
              </div>
            </div>
            <div v-else class="text-center my-4">
              <p class="text-muted">No reviews available yet.</p>
            </div>
          </div>
          <div
            class="pagination-container"
            v-if="filteredPublicReviews.length >= dynamicItemsPerPage"
          >
            <Paginator
              :pageNumber="currentPublicPage"
              :numberOfPages="totalPublicPages"
              :pagesArray="publicPagesArray"
              @paging="handlePublicPageChange"
            />
          </div>
        </div>
      </div>

      <Modal
        ref="modalRef"
        :title="title"
        :yes="yes"
        :no="no"
        :size="size"
        @yesEvent="yesEventHandler"
      >
        <div v-if="state === 'Delete'">{{ messageYesNo }}</div>
        <ReviewForm
          v-if="state === 'Update'"
          :itemForm="item"
          :films="films"
          :isUpdate="true"
          @saveItem="saveItemHandler"
        />
      </Modal>
    </div>
  </div>
</template>

<script>
import Modal from "@/components/Modal.vue";
import Paginator from "@/components/Paginator.vue";
import OperationsCrud from "@/components/OperationsCrud.vue";
import ReviewForm from "@/components/forms/ReviewForm.vue";
import { useAuthStore } from "@/stores/useAuthStore";
import { BASE_URL } from "../helpers/baseUrls";
import axios from "axios";

export default {
  components: { Paginator, OperationsCrud, Modal, ReviewForm },
  data() {
    return {
      activeTab: "my",
      favourites: [],
      publicReviews: [],
      films: [],
      authStore: useAuthStore(),
      currentPage: 1,
      currentPublicPage: 1,
      dynamicItemsPerPage: 14,
      selectedRowId: null,
      errorMessages: null,
      loading: false,
      modal: null,
      item: {},
      messageYesNo: null,
      state: "Read",
      title: null,
      yes: null,
      no: null,
      size: null,
      username: null,
      reviewText: "",
      submitting: false,
      errorMessage: "",
      selectedFilmId: "",
      rating: 0,
      tableHeight: 400,
      reviewsHeight: 400,
      defaultReviews: [
        "A cinematic masterpiece!",
        "Absolutely breathtaking visuals",
        "The soundtrack was phenomenal",
        "One of the best this year",
        "Would definitely watch again",
      ],
      publicReviewFilter: "ABC",
      tableReviewFilter: "ABC",
      searchQuery: "",
      debounceTimer: null,
      showSearch: false,
      showTableFilter: false,
      showPublicFilter: false,
    };
  },
  computed: {
    isLoggedIn() {
      return this.authStore.id && this.authStore.id !== 0;
    },
    filteredFavourites() {
      let favourites = [...this.favourites];

      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        favourites = favourites.filter(
          (fav) =>
            (fav.filmTitle || "").toLowerCase().includes(query) ||
            (fav.content || "").toLowerCase().includes(query)
        );
      }

      // Apply sorting
      if (this.tableReviewFilter === "ABC") {
        favourites.sort((a, b) =>
          (a.filmTitle || "").localeCompare(b.filmTitle || "")
        );
      } else if (this.tableReviewFilter === "highToLow") {
        favourites.sort((a, b) => b.evaluation - a.evaluation);
      } else if (this.tableReviewFilter === "lowToHigh") {
        favourites.sort((a, b) => a.evaluation - b.evaluation);
      } else if (this.tableReviewFilter === "newest") {
        favourites.sort(
          (a, b) => new Date(b.created_at) - new Date(a.created_at)
        );
      } else if (this.tableReviewFilter === "oldest") {
        favourites.sort(
          (a, b) => new Date(a.created_at) - new Date(b.created_at)
        );
      }

      return favourites;
    },
    paginatedFavourites() {
      const start = (this.currentPage - 1) * this.dynamicItemsPerPage;
      return this.filteredFavourites.slice(
        start,
        start + this.dynamicItemsPerPage
      );
    },
    totalPages() {
      return Math.ceil(
        this.filteredFavourites.length / this.dynamicItemsPerPage
      );
    },
    pagesArray() {
      if (this.totalPages <= 5) {
        return Array.from({ length: this.totalPages }, (_, i) => i + 1);
      }
      const firstPage = 1;
      const lastPage = this.totalPages;
      let middlePages = [];
      if (this.currentPage <= 3) {
        middlePages = [2, 3, 4];
      } else if (this.currentPage >= this.totalPages - 2) {
        middlePages = [
          this.totalPages - 3,
          this.totalPages - 2,
          this.totalPages - 1,
        ];
      } else {
        middlePages = [
          this.currentPage,
          this.currentPage + 1,
          this.currentPage + 2,
        ];
      }
      return [firstPage, ...middlePages, lastPage];
    },
    filteredPublicReviews() {
      let reviews = [...this.publicReviews];

      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        reviews = reviews.filter(
          (review) =>
            (review.filmTitle || "").toLowerCase().includes(query) ||
            (review.content || "").toLowerCase().includes(query) ||
            (review.userName || "").toLowerCase().includes(query)
        );
      }

      // Apply sorting
      if (this.publicReviewFilter === "ABC") {
        reviews.sort((a, b) =>
          (a.filmTitle || "").localeCompare(b.filmTitle || "")
        );
      } else if (this.publicReviewFilter === "highToLow") {
        reviews.sort((a, b) => b.evaluation - a.evaluation);
      } else if (this.publicReviewFilter === "lowToHigh") {
        reviews.sort((a, b) => a.evaluation - b.evaluation);
      }
      return reviews;
    },
    paginatedPublicReviews() {
      const start = (this.currentPublicPage - 1) * this.dynamicItemsPerPage;
      return this.filteredPublicReviews.slice(
        start,
        start + this.dynamicItemsPerPage
      );
    },
    totalPublicPages() {
      return Math.ceil(
        this.filteredPublicReviews.length / this.dynamicItemsPerPage
      );
    },
    publicPagesArray() {
      if (this.totalPublicPages <= 5) {
        return Array.from({ length: this.totalPublicPages }, (_, i) => i + 1);
      }
      const firstPage = 1;
      const lastPage = this.totalPublicPages;
      let middlePages = [];
      if (this.currentPublicPage <= 3) {
        middlePages = [2, 3, 4];
      } else if (this.currentPublicPage >= this.totalPublicPages - 2) {
        middlePages = [
          this.totalPublicPages - 3,
          this.totalPublicPages - 2,
          this.totalPublicPages - 1,
        ];
      } else {
        middlePages = [
          this.currentPublicPage,
          this.currentPublicPage + 1,
          this.currentPublicPage + 2,
        ];
      }
      return [firstPage, ...middlePages, lastPage];
    },
  },
  watch: {
    authStore: {
      immediate: true,
      handler(newVal) {
        if (!newVal.isAuthenticated) {
          this.activeTab = "all";
          this.fetchAllReviews();
        }
      },
    },
    activeTab() {
      this.$nextTick(() => {
        this.calculateDimensions();
      });
    },
    tableReviewFilter() {
      this.currentPage = 1;
    },
    publicReviewFilter() {
      this.currentPublicPage = 1;
    },
  },
  mounted() {
    this.modal = this.$refs.modalRef;
    this.activeTab = this.isLoggedIn ? "my" : "all";
    this.fetchFilms();
    if (this.isLoggedIn) {
      this.fetchFavourites();
    } else {
      this.fetchAllReviews();
    }
    this.calculateDimensions();
    window.addEventListener("resize", this.calculateDimensions);
  },
  beforeDestroy() {
    window.removeEventListener("resize", this.calculateDimensions);
  },
  methods: {
    calculateDimensions() {
      const headerHeight =
        document.querySelector(".header-container")?.offsetHeight || 60;
      const filterHeight =
        this.activeTab === "all" || !this.isLoggedIn ? 60 : 0;
      const tableFilterHeight =
        this.activeTab === "my" && this.isLoggedIn ? 60 : 0;
      const paginationHeight = 80;
      const margins = 40;

      const availableHeight =
        window.innerHeight -
        headerHeight -
        filterHeight -
        tableFilterHeight -
        paginationHeight -
        margins;

      this.tableHeight = Math.max(300, availableHeight);
      this.reviewsHeight = Math.max(300, availableHeight);

      const rowHeight = this.activeTab === "my" ? 60 : 120;
      this.dynamicItemsPerPage = Math.max(
        1,
        Math.floor(availableHeight / rowHeight)
      );
    },
    switchTab(tab) {
      if (tab === "my" && !this.isLoggedIn) return;
      this.activeTab = tab;
      this.currentPage = 1;
      this.currentPublicPage = 1;
      if (tab === "all" && this.publicReviews.length === 0) {
        this.fetchAllReviews();
      }
      this.calculateDimensions();
    },
    handleSearch() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.currentPage = 1;
        this.currentPublicPage = 1;
      }, 300);
    },
    toggleControl(type) {
      this.showSearch = type === "search" ? !this.showSearch : false;
      this.showTableFilter =
        type === "tableFilter" ? !this.showTableFilter : false;
      this.showPublicFilter =
        type === "publicFilter" ? !this.showPublicFilter : false;

      // Close other controls when opening a new one
      if (type === "search" && !this.showSearch) {
        this.showTableFilter = false;
        this.showPublicFilter = false;
      }
    },
    async fetchFavourites() {
      try {
        const userId = this.authStore.id;
        this.loading = true;
        const token = this.authStore.token;
        let url = `${BASE_URL}/favouritesByUserId/${userId}`;
        const response = await axios.get(url, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (response.data?.data) {
          this.favourites = response.data.data.map((fav) => ({
            ...fav,
            evaluation: Number(fav.evaluation) || 0,
          }));
        }
      } catch (error) {
        console.error("Error fetching favourites:", error);
        this.errorMessages = "Error fetching data from the server.";
      } finally {
        this.loading = false;
      }
    },
    async fetchAllReviews() {
      try {
        this.loading = true;
        const token = this.authStore.token;
        const response = await axios.get(`${BASE_URL}/favourites`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {},
        });
        if (response.data?.data) {
          this.publicReviews = response.data.data.map((review) => ({
            ...review,
            userName: review.userName || "Anonymous",
            filmTitle: review.filmTitle || "Unknown Film",
            evaluation: Number(review.evaluation) || 0,
          }));
        }
      } catch (error) {
        console.error("Error fetching public reviews:", error);
        this.errorMessages = "Error fetching data from the server.";
      } finally {
        this.loading = false;
      }
    },
    async fetchFilms() {
      try {
        const response = await axios.get(`${BASE_URL}/films`);
        if (response.data?.data) {
          this.films = response.data.data;
        }
      } catch (error) {
        console.error("Error fetching films:", error);
      }
    },
    async saveItemHandler(formData) {
      this.loading = true;
      const token = this.authStore.token;
      const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      };
      try {
        if (this.state === "Update") {
          await this.updateReview(formData, headers);
        }
        await this.fetchFavourites();
        if (
          this.$refs.modalRef &&
          typeof this.$refs.modalRef.hide === "function"
        ) {
          this.$refs.modalRef.hide();
        }
      } catch (error) {
        console.error("Operation failed:", error.response?.data || error);
        this.errorMessages =
          error.response?.data?.message ||
          "Operation failed. Please try again.";
      } finally {
        document.body.style.overflow = "";
        this.loading = false;
        this.state = "Read";
      }
    },
    async deleteReview() {
      const id = this.selectedRowId;
      try {
        await axios.delete(`${BASE_URL}/favourites/${id}`, {
          headers: { Authorization: `Bearer ${this.authStore.token}` },
        });
        await this.fetchFavourites();
        if (this.modal) {
          this.modal.hide();
        }
      } catch (error) {
        console.error("Delete error:", error);
        this.errorMessages = "The review cannot be deleted.";
      }
    },
    async updateReview(data, headers) {
      try {
        const response = await axios.patch(
          `${BASE_URL}/favourites/${this.selectedRowId}`,
          { evaluation: data.evaluation, filmId: data.filmId },
          { headers }
        );
        if (
          this.$refs.modalRef &&
          typeof this.$refs.modalRef.hide === "function"
        ) {
          this.$refs.modalRef.hide();
        }
        return response.data;
      } catch (error) {
        console.error("Update error:", error);
        throw error;
      }
    },
    yesEventHandler() {
      if (this.state === "Delete") {
        this.deleteReview();
      } else if (this.state === "Update") {
        this.updateReview(this.item, this.headers);
      }
    },
    getEvaluation(item) {
      return Number(item.evaluation) || 0;
    },
    formatEvaluation(value) {
      const num = Number(value);
      return Number.isNaN(num) ? "N/A" : num.toFixed(1);
    },
    randomDefaultReview() {
      const index = Math.floor(Math.random() * this.defaultReviews.length);
      return this.defaultReviews[index];
    },
    formatDate(date) {
      try {
        const d = new Date(date);
        return isNaN(d) ? "N/A" : d.toLocaleString("hu-HU");
      } catch {
        return "N/A";
      }
    },
    handlePageChange(pageInfo) {
      this.page = pageInfo.pageNumber;
      this.pageSize = pageInfo.pageSize;
      this.currentPage =
        pageInfo === "..." ? this.totalPages : pageInfo.pageNumber;
    },
    handlePublicPageChange(pageInfo) {
      this.currentPublicPage = pageInfo.pageNumber;
    },
    onClickDelete(item) {
      this.state = "Delete";
      this.title = "Delete Review";
      this.messageYesNo = `Are you sure you want to delete the review by ${item.userName}?`;
      this.yes = "Yes";
      this.no = "No";
      this.size = null;
      this.selectedRowId = item.id;
    },
    onClickUpdate(item) {
      this.state = "Update";
      this.selectedRowId = item.id;
      this.item = { ...item };
      this.title = "Update Review";
      this.yes = null;
      this.no = "Cancel";
      this.size = "lg";
    },
  },
};
</script>

<style scoped>
/* General Styles */
.film-reviews {
  color: white;
  height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Header Styles */
.header-container {
  background: #1a1a1a;
  border-bottom: 2px solid #ffd700;
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  gap: 1rem;
}

.tabs-wrapper {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex: 1;
  min-width: 0;
  justify-content: flex-end;
}

/*Style for tabs (My Reviews / All Reviews) */
.title-text {
  color: #ffd700; /* Gold color for inactive tabs */
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  padding: 0.5rem 1rem;
  white-space: nowrap;
  text-align: center;
  border-radius: 4px;
  transition: all 0.2s ease;
  flex: 1;
}

.clickable {
  cursor: pointer;
  transition: opacity 0.2s;
}

.clickable:hover {
  opacity: 0.8;
}

.active-tab {
  background: #ffd700;
  color: #1a1a1a !important; /* Dark color for text on gold background */
  font-weight: 700;
}

/* Controls container */
.controls-container {
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
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
  background: #2a2a2a;
  transition: all 0.3s ease;
}

.control-item:hover {
  background: #ffd700;
  color: #1a1a1a;
}

.control-item i {
  font-size: 1.1rem;
}

/* Collapsible containers */
.search-container,
.filter-container {
  position: absolute;
  right: 0;
  top: 100%;
  z-index: 100;
  background: #1a1a1a;
  border: 1px solid #ffd70033;
  border-radius: 8px;
  padding: 0.5rem;
  margin-top: 0.5rem;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.search-wrapper {
  min-width: 250px;
}

.filter-wrapper {
  min-width: 200px;
}

/* Search Container */
.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: #2a2a2a;
  border-radius: 25px;
  padding: 0.5rem 1rem;
  transition: all 0.3s ease;
}

.search-wrapper:hover {
  box-shadow: 0 0 12px rgba(255, 215, 0, 0.15);
}

.search-input {
  background: transparent;
  border: none;
  color: #ffd700;
  width: 100%;
  padding: 0.25rem 0;
  font-size: 0.95rem;
}

.search-input::placeholder {
  color: #ffd70099;
}

.search-input:focus {
  outline: none;
}

/* Filter Styles */
.filter-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
  background: #2a2a2a;
  border-radius: 25px;
  padding: 0.25rem 1rem;
  transition: all 0.3s ease;
}

.filter-wrapper:hover {
  box-shadow: 0 0 12px rgba(255, 215, 0, 0.15);
}

.filter-label {
  color: #ffd700;
  font-weight: 500;
  margin: 0;
  white-space: nowrap;
}

.custom-filter-select {
  background: transparent;
  color: #ffd700;
  border: none;
  padding: 0.5rem 2rem 0.5rem 1rem;
  font-size: 0.95rem;
  appearance: none;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  z-index: 2;
}

.custom-filter-select:focus {
  outline: none;
  box-shadow: none;
}

.custom-filter-select option {
  background: #383838;
  color: #ffd700;
}

.select-arrow {
  position: absolute;
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  z-index: 1;
  transition: transform 0.2s ease;
}

.custom-filter-select:hover + .select-arrow {
  transform: translateY(-50%) scale(1.1);
}

/* Loading Overlay Styles */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(40, 40, 40, 0.95);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(3px);
}

.loading-content {
  text-align: center;
}

.loading-text {
  color: var(--secondary-color);
  font-size: 1.5rem;
  margin-top: 1rem;
  animation: pulse 1.5s infinite;
  text-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
}

@keyframes pulse {
  0% {
    opacity: 0.6;
    transform: scale(0.95);
  }
  50% {
    opacity: 1;
    transform: scale(1);
  }
  100% {
    opacity: 0.6;
    transform: scale(0.95);
  }
}

.spinner-border {
  width: 4rem;
  height: 4rem;
  border-width: 0.3em;
  color: var(--secondary-color);
  filter: drop-shadow(0 0 8px rgba(255, 215, 0, 0.4));
}

/* Container Styles */
.container {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: 0;
  margin: 0;
}

/* Table Container Styles */
.table-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 300px;
}

.table-wrapper::-webkit-scrollbar {
  width: 0;
  height: 0;
  background: transparent;
}

/* Custom Table Styling */
.custom-table {
  width: 65vw;
  border-collapse: collapse;
  background: #1a1a1a;
  border: 1px solid #ffd700;
  color: #c9ab00;
  margin: 0 auto;
  position: fixed;
  top: 18vh;
  left: 50%;
  transform: translateX(-50%);
  flex-shrink: 0;
}

.custom-table thead {
  background: #1a1a1a;
  border-bottom: 3px solid #ffd700;
}

.custom-table th {
  padding: 0.75rem 1rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  color: #ffd700;
}

.custom-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #2a2a2a;
  padding-top: 2px;
  padding-bottom: 2px;
}

.custom-table tbody tr {
  transition: background-color 0.2s ease;
}

.custom-table tbody tr:hover {
  background: #2a2a2a;
}

/* Public Reviews (Grid) Container */
.public-reviews-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 300px;
  width: 100%;
  max-width: 1500px;
  margin: 0 auto;
  padding: 0 15px;
  box-sizing: border-box;
}

.reviews-wrapper {
  overflow: hidden;
  overflow-y: auto;
  flex: 1;
  margin: 0.5rem 0;
}

.reviews-wrapper::-webkit-scrollbar {
  width: 0;
  height: 0;
  background: transparent;
}

/* Grid Layout for Reviews */
.reviews-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  grid-auto-rows: minmax(220px, auto);
  gap: 20px;
  width: 95%;
  max-width: 1600px;
  margin: 0 auto;
  padding: 15px 10px 80px;
  box-sizing: border-box;
  color: #c9ab00;
}

/* Review Card Styles */
.review-card {
  border: 1px solid #ffd70033;
  border-radius: 8px;
  padding: 18px;
  display: flex;
  flex-direction: column;
  height: 100%;
  box-sizing: border-box;
  transition: transform 0.3s ease;
}

.review-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(255, 215, 0, 0.1);
  border-color: #ffd70066;
}

/* Review Content Styles */
.review-header {
  margin-bottom: 0.75rem;
}

.review-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.review-film-title {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--secondary-color);
}

.review-content {
  flex: 1;
  margin-bottom: 1rem;
  line-height: 1.5;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
}

/* Star Rating Styles */
.star-rating {
  font-size: 1.1rem;
  color: #ffd700;
  line-height: 1;
}

.bi {
  font-size: 1.25rem;
  vertical-align: middle;
}

/* Paginator Styles */
.pagination-container {
  flex-shrink: 0;
  display: flex;
  justify-content: center;
  background: #1a1a1a;
  border: 1px solid #ffd700;
  padding: 1rem;
  border-radius: 8px;
  width: fit-content;
  margin: 0 auto 1rem auto;
  position: fixed;
  bottom: 1rem;
  left: 50%;
  transform: translateX(-50%);
}

.pagination {
  list-style: none;
  display: flex;
  gap: 0.5rem;
  padding: 0;
  margin: 0;
}

.pagination .page-item {
  cursor: pointer;
}

.pagination .page-item .page-link {
  display: block;
  padding: 0.5rem 0.75rem;
  border: none;
  border-radius: 15px;
  background: var(--accent-color);
  color: var(--secondary-color);
  min-width: 40px;
  text-align: center;
  transition: all 0.3s ease;
}

.pagination .page-item.active .page-link {
  background: var(--primary-color) !important;
  color: var(--secondary-color) !important;
  font-weight: bold;
}

.pagination .page-item:hover .page-link {
  background: var(--secondary-color);
  color: var(--accent-color) !important;
  transform: translateY(-2px);
}

/* Operations Styles */
.text-nowrap.text-center {
  min-width: 120px;
}

.text-nowrap.text-center button {
  transition: all 0.3s ease;
  border: 1px solid var(--primary-color) !important;
}

.text-nowrap.text-center button:hover {
  background: var(--primary-color) !important;
  color: var(--secondary-color) !important;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    gap: 0.75rem;
  }

  .tabs-wrapper {
    order: 2;
    width: 100%;
    justify-content: space-between;
  }

  .controls-container {
    order: 1;
    width: 100%;
    justify-content: flex-end;
  }

  .search-container,
  .filter-container {
    right: auto;
    left: 0;
    width: 100%;
  }

  .custom-table {
    width: 95vw;
    font-size: 0.85rem;
    top: 22vh;
  }

  .custom-table th,
  .custom-table td {
    padding: 0.5rem;
  }

  .pagination .page-item .page-link {
    min-width: 30px;
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
  }

  .title-text {
    font-size: 1rem;
    padding: 0.5rem;
  }

  .filter-wrapper {
    padding: 0.25rem 0.75rem;
  }

  .custom-filter-select {
    font-size: 0.85rem;
    padding-right: 1.75rem;
  }

  .select-arrow {
    right: 0.75rem;
  }

  .filter-label {
    font-size: 0.9rem;
  }

  .review-card {
    padding: 12px;
  }

  .review-film-title {
    font-size: 1rem;
  }

  .review-content {
    font-size: 0.9rem;
    -webkit-line-clamp: 3;
  }

  .star-rating {
    font-size: 1rem;
  }
}

/* Additional animations for better UX */
.control-item {
  transition: transform 0.2s ease;
}

.control-item:active {
  transform: scale(0.95);
}

.search-input,
.custom-filter-select {
  transition: all 0.3s ease;
}

.search-input:focus,
.custom-filter-select:focus {
  box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3);
}

/* Accessibility improvements */
.search-input,
.custom-filter-select {
  font-size: 1rem;
}

.search-input::placeholder {
  opacity: 0.7;
}

/* Dark mode compatibility */
@media (prefers-color-scheme: dark) {
  .film-reviews {
    background-color: #121212;
  }

  .custom-table {
    background: #121212;
  }

  .review-card {
    background: #1e1e1e;
  }
}

/* Print styles */
@media print {
  .header-container,
  .controls-container,
  .pagination-container {
    display: none;
  }

  .film-reviews {
    height: auto;
    color: #000;
  }

  .custom-table {
    width: 100%;
    position: static;
    transform: none;
    border: 1px solid #ddd;
    color: #000;
    background: #fff;
  }

  .custom-table th {
    background: #f5f5f5;
    color: #000;
  }

  .review-card {
    background: #fff;
    border: 1px solid #ddd;
    color: #000;
    page-break-inside: avoid;
  }

  .star-rating {
    color: #ffc107;
  }
}
</style>