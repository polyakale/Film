<template>
  <div class="film-reviews">
    <!-- Header Tabs -->
    <div
      class="header-container d-flex align-items-center justify-content-between px-3 py-2"
    >
      <h3
        class="title-text m-0 clickable"
        :class="{ 'active-tab': activeTab === 'my' }"
        @click="switchTab('my')"
        v-if="isLoggedIn"
      >
        My Reviews
      </h3>
      <!-- Filter for All Reviews (moved up) -->
      <div
        v-if="activeTab === 'all' || !isLoggedIn"
        class="filter-container d-flex justify-content-center py-3"
      >
        <div class="filter-wrapper position-relative">
          <label for="publicFilter" class="filter-label">
            <i class="bi bi-funnel-fill me-2"></i>Sort by:
          </label>
          <select
            id="publicFilter"
            v-model="publicReviewFilter"
            class="custom-filter-select"
          >
            <option value="ABC">Film Title (A–Z)</option>
            <option value="highToLow">Evaluation: High to Low</option>
            <option value="lowToHigh">Evaluation: Low to High</option>
          </select>
          <div class="select-arrow">
            <svg width="12" height="7" viewBox="0 0 12 7" fill="none">
              <path d="M1 1L6 6L11 1" stroke="#ffd700" stroke-width="2" />
            </svg>
          </div>
        </div>
      </div>
      <h3
        class="title-text m-0 clickable"
        :class="{ 'active-tab': activeTab === 'all' || !isLoggedIn }"
        @click="switchTab('all')"
      >
        All Reviews
      </h3>
    </div>

    <!-- Loading State -->
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
        <!-- My Reviews Table -->
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
                      <button
                        type="button"
                        class="btn btn-outline-warning btn-sm"
                        @click="onClickCreate"
                      >
                        <i class="bi bi-plus-lg"></i>
                      </button>
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
                      ></i>
                      <small class="text-muted ms-2">
                        ({{ formatEvaluation(favourite.evaluation) }})
                      </small>
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
            v-if="favourites.length >= dynamicItemsPerPage"
          >
            <Paginator
              :pageNumber="currentPage"
              :numberOfPages="totalPages"
              :pagesArray="pagesArray"
              @paging="handlePageChange"
            />
          </div>
        </div>

        <!-- All Reviews Section -->
        <div
          v-if="activeTab === 'all' || !isLoggedIn"
          class="public-reviews-container"
        >
          <div
            class="reviews-wrapper"
            :style="{ height: reviewsHeight + 'px' }"
          >
            <div v-if="filteredPublicReviews.length > 0" class="public-reviews">
              <div class="reviews-container">
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
                    ></i>
                    <small class="text-muted ms-2">
                      ({{ formatEvaluation(review.evaluation) }})
                    </small>
                  </div>
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

      <!-- Modal for Create / Update / Delete -->
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
          v-if="state === 'Create' || state === 'Update'"
          :itemForm="item"
          :films="films"
          :favourites="favourites"
          :username="username"
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
      dynamicItemsPerPage: null,
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
    };
  },
  computed: {
    isLoggedIn() {
      return this.authStore.id && this.authStore.id !== 0;
    },
    paginatedFavourites() {
      const start = (this.currentPage - 1) * this.dynamicItemsPerPage;
      return this.favourites.slice(start, start + this.dynamicItemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.favourites.length / this.dynamicItemsPerPage);
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
      // Calculate available height
      const headerHeight =
        document.querySelector(".header-container")?.offsetHeight || 60;
      const filterHeight =
        this.activeTab === "all" || !this.isLoggedIn ? 60 : 0;
      const paginationHeight = 80;
      const margins = 40;

      const availableHeight =
        window.innerHeight -
        headerHeight -
        filterHeight -
        paginationHeight -
        margins;

      // Set container heights
      this.tableHeight = Math.max(300, availableHeight);
      this.reviewsHeight = Math.max(300, availableHeight);

      // Calculate dynamic items per page
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
        if (this.state === "Create") {
          const exists = this.favourites.some(
            (fav) =>
              fav.filmId === formData.filmId && fav.userId === this.authStore.id
          );
          if (exists) {
            this.errorMessages = "You've already reviewed this film";
            return;
          }
        }
        if (this.state === "Create") {
          await this.createReview(formData, headers);
        } else if (this.state === "Update") {
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
    async createReview(data, headers) {
      const response = await axios.post(
        `${BASE_URL}/favourites`,
        {
          filmId: data.filmId,
          evaluation: data.evaluation,
          userId: data.userId,
        },
        { headers }
      );
      return response.data;
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
      } else if (this.state === "Create") {
        this.createReview(this.item);
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
    showCreateModal() {
      this.state = "Create";
      this.item = {
        filmId: "",
        evaluation: 0,
        userId: this.authStore.id,
      };
      this.title = "Add New Review";
      this.yes = null;
      this.no = "Cancel";
      this.size = "lg";
      this.modal.show();
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
    onClickCreate() {
      this.state = "Create";
      this.title = "New Review";
      this.yes = null;
      this.no = "Cancel";
      this.size = "lg";
      this.item = { filmId: "", evaluation: 0, userId: this.authStore.id };
    },
  },
};
</script>

<style scoped>
/* Root Variables */
:root {
  --primary-color: #8b0000;
  --secondary-color: #ffd700;
  --accent-color: #000000;
  --background-dark: #1a1a1a;
  --text-light: #ffffff;
  --text-muted: #cccccc;
}

/* General Styles */
.film-reviews {
  color: var(--text-light);
}

/* Header Styles */
.header-container {
  background: #1a1a1a;
  border-bottom: 2px solid #ffd700;
  width: 100%;
  flex-shrink: 0;
}

.title-text {
  color: #ffd700;
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  padding: 0.5rem 0;
}

.clickable {
  cursor: pointer;
  transition: opacity 0.2s;
}

.clickable:hover {
  opacity: 0.8;
}

.active-tab {
  border-bottom: 3px solid #ffd700;
  padding-bottom: 0.25rem;
}

/* Filter Styles */
.filter-container {
  background: #1a1a1a;
  border-bottom: 1px solid #2a2a2a;
  flex-shrink: 0;
}

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
  padding: 0;
  margin: 0;
}

/* Column Width Adjustments */
.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
  /* Film column */
  width: 30%;
  min-width: 200px;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
  /* Evaluation column */
  width: 25%;
  min-width: 150px;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3) {
  /* Date column */
  width: 25%;
  min-width: 150px;
}

.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
  /* Operations column */
  width: 20%;
  min-width: 120px;
}

/* Updated Table Styles */
.custom-table {
  /* display: flex; */
  justify-content: center;
  background: #1a1a1a;
  border: 1px solid #ffd700;
  width: fit-content;
  margin: 0 auto; /*Centers horizontally*/ /* Add these for fixed positioning */
  position: fixed;
  top: 18vh; /* Adjust distance from bottom as needed */
  left: 50%;
  transform: translateX(-50%); /* Ensures true centering */
  flex-shrink: 0; /* Already present, but good to keep */
  color: #c9ab00;
  width: 66vw;
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

.public-reviews-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 300px;
  width: 100%;
  max-width: 1500px; /* Add reasonable max-width */
  margin: 0 auto; /* Center the container */
  padding: 0 15px; /* Add horizontal padding */
}

.table-container,
.public-reviews-container {
  padding-bottom: 5px; /* Adjust this value based on paginator height */
}

/* Reviews Grid Container */
.reviews-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  grid-auto-rows: minmax(220px, auto);
  gap: 20px;
  width: 95%;
  max-width: 1600px;
  margin: 0 auto;
  padding: 15px 10px 80px; /* Extra bottom padding for paginator */
  box-sizing: border-box;
}

/* Review Card Styling */
.review-card {
  background: #2a2a2a;
  border: 1px solid #ffd70033;
  border-radius: 8px;
  padding: 18px;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
  box-sizing: border-box;
}

.review-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(255, 215, 0, 0.1);
  border-color: #ffd70066;
}

/* Scrollable Wrapper */
.reviews-wrapper {
  overflow-y: auto;
  flex: 1;
  scrollbar-width: thin;
  scrollbar-color: #ffd700 #2a2a2a;
}

.reviews-wrapper::-webkit-scrollbar {
  width: 6px;
}

.reviews-wrapper::-webkit-scrollbar-track {
  background: #2a2a2a;
}

.reviews-wrapper::-webkit-scrollbar-thumb {
  background-color: #ffd700;
  border-radius: 3px;
}

/* Content Area Styling */
.review-header {
  margin-bottom: 12px;
}

.review-meta {
  display: flex;
  gap: 1rem;
}

.review-author {
  font-weight: bold;
}

.review-date {
  color: var(--text-muted);
  font-size: 13px;
}

.review-film-title {
  color: #ffd700;
  font-size: 1.2rem;
  margin: 8px 0;
  font-weight: 600;
}

.review-content {
  flex-grow: 1;
  margin: 10px 0;
  line-height: 1.5;
  overflow: hidden;
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
  /* Keep existing styles for centering and appearance */
  display: flex;
  justify-content: center;
  background: #1a1a1a;
  border: 1px solid #ffd700;
  padding: 1rem;
  border-radius: 8px;
  width: fit-content; /* Or set a specific max-width */
  margin: 0 auto; /* Centers horizontally */
  z-index: 100;

  /* Add these for fixed positioning */
  position: fixed;
  bottom: 1rem; /* Adjust distance from bottom as needed */
  left: 50%;
  transform: translateX(-50%); /* Ensures true centering */
  flex-shrink: 0; /* Already present, but good to keep */
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
  .custom-table {
    font-size: 0.85rem;
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

  .header-container {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .title-text {
    font-size: 1.2rem;
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
}
</style>