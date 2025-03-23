<template>
  <div class="film-reviews">
    <!-- Toggle Button (admin only) -->
    <div v-if="isAdmin" class="toggle-container">
      <button class="btn-toggle" @click="toggleViewMode">
        <i :class="viewMode === 'admin' ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
        <span class="toggle-text">
          Switch to {{ viewMode === 'admin' ? 'Guest' : 'Admin' }} View
        </span>
      </button>
    </div>

    <!-- Full-page Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="loading-text">Loading...</div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else>
      <h3 class="text-center my-4">Reviews</h3>
      <div class="container">
        <!-- Admin View -->
        <div v-if="viewMode === 'admin'" class="admin-view">
          <div v-if="favourites.length > 0" class="col-12 col-lg-10 tabla-container">
            <table class="table custom-table">
              <thead class="table-dark">
                <tr>
                  <th v-if="debug">#</th>
                  <th>User</th>
                  <th>Film</th>
                  <th class="text-center">Evaluation</th>
                  <th>Date</th>
                  <th class="text-center">Operations</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="favourite in paginatedFavourites" :key="favourite.id" class="review-card">
                  <td data-label="#" v-if="debug">{{ favourite.id }}</td>
                  <td data-label="User" class="user">
                    {{ favourite.userName || "Unknown User" }}
                  </td>
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
                          'bi-star-half': getEvaluation(favourite) + 0.5 >= starIndex && getEvaluation(favourite) < starIndex,
                          'bi-star': getEvaluation(favourite) + 0.5 < starIndex
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
                      @onClickDeleteButton="onClickDeleteButton"
                      @onClickUpdate="onClickUpdate"
                      :data="favourite"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pagination-container" v-if="favourites.length > 0">
            <Paginator
              :pageNumber="currentPage"
              :numberOfPages="totalPages"
              :pagesArray="pagesArray"
              @paging="handlePageChange"
            />
          </div>
        </div>

        <!-- Guest View -->
        <div v-else class="guest-view">
          <div class="guest-review-form">
            <div class="user-info">
              <span class="username">{{ username }}</span>
            </div>
            <select v-model="selectedFilmId" class="film-select">
              <option value="" disabled>Select Film</option>
              <option v-for="film in films" :key="film.id" :value="film.id">
                {{ film.title }}
              </option>
            </select>
            <div class="star-input">
              <span v-for="starIndex in 5" :key="starIndex" @click="setRating(starIndex)">
                <i :class="rating >= starIndex ? 'bi bi-star-fill' : 'bi bi-star'" class="star-icon"></i>
              </span>
              <span class="rating-display">({{ rating }})</span>
            </div>
            <textarea
              v-model="reviewText"
              class="review-input"
              placeholder="Write a public review..."
            ></textarea>
            <div class="review-actions">
              <button
                class="btn-submit"
                @click="submitReview"
                :disabled="submitting || !reviewText.trim() || !selectedFilmId || rating === 0"
              >
                Submit Review
              </button>
              <span v-if="errorMessage" class="error-message">{{ errorMessage }}</span>
            </div>
          </div>
          <div class="public-reviews" v-if="publicReviews.length > 0">
            <div v-for="review in publicReviews" :key="review.id" class="review-card">
              <div class="review-header">
                <div class="review-meta">
                  <span class="review-author">{{ review.userName || 'Anonymous' }}</span>
                  <span class="review-date">{{ formatDate(review.created_at) }}</span>
                </div>
              </div>
              <div class="review-content">
                {{ review.content || 'It was a great film!' }}
              </div>
              <div class="star-rating d-inline-flex align-items-center ml-3">
                <i
                  v-for="starIndex in 5"
                  :key="`star-${review.id}-${starIndex}`"
                  class="bi mx-1 text-warning"
                  :class="{
                    'bi-star-fill': getEvaluation(review) >= starIndex,
                    'bi-star-half': getEvaluation(review) + 0.5 >= starIndex && getEvaluation(review) < starIndex,
                    'bi-star': getEvaluation(review) + 0.5 < starIndex
                  }"
                ></i>
                <small class="text-muted ms-2">
                  ({{ formatEvaluation(review.evaluation) }})
                </small>
              </div>
            </div>
          </div>
          <div v-else class="no-reviews">
            No reviews yet. Be the first to write one!
          </div>
        </div>
      </div>
      <Modal :title="title" :yes="yes" :no="no" :size="size" @yesEvent="yesEventHandler">
        <div v-if="state === 'Delete'">{{ messageYesNo }}</div>
        <ReviewForm
          v-if="state === 'Create' || state === 'Update'"
          :itemForm="item"
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
import * as bootstrap from "bootstrap";

export default {
  components: { Paginator, OperationsCrud, Modal, ReviewForm },
  data() {
    return {
      favourites: [],
      publicReviews: [],
      films: [],
      authStore: useAuthStore(),
      currentPage: 1,
      itemsPerPage: 5,
      selectedRowId: null,
      errorMessages: null,
      loading: false,
      modal: null,
      // For admin CRUD modal
      item: {},
      messageYesNo: null,
      state: "Read",
      title: null,
      yes: null,
      no: null,
      size: null,
      debug: false,
      viewMode: "admin", // "admin" or "guest"
      reviewText: "",
      submitting: false,
      errorMessage: "",
      // For guest review submission
      selectedFilmId: "",
      rating: 0
    };
  },
  computed: {
    isAdmin() {
      return this.authStore.positionId === 1;
    },
    username() {
      return this.authStore.user || "Guest";
    },
    userAvatar() {
      return this.authStore.avatar || "";
    },
    paginatedFavourites() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.favourites.slice(start, start + this.itemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.favourites.length / this.itemsPerPage);
    },
    pagesArray() {
      return Array.from({ length: this.totalPages }, (_, i) => i + 1);
    }
  },
  mounted() {
    if (this.isAdmin) {
      this.fetchFavourites();
    } else {
      this.fetchPublicReviews();
    }
    this.fetchFilms();
    // Initialize Bootstrap modal (ensure the Modal component has id="modal")
    this.modal = new bootstrap.Modal(document.getElementById("modal"), { keyboard: false });
  },
  methods: {
    toggleViewMode() {
      if (this.isAdmin) {
        this.viewMode = this.viewMode === "admin" ? "guest" : "admin";
        if (this.viewMode === "guest") {
          this.fetchPublicReviews();
        } else {
          this.fetchFavourites();
        }
      }
    },
    yesEventHandler() {
      if (this.state === "Delete") {
        this.deleteItemById();
      }
    },
    getEvaluation(item) {
      return Number(item.evaluation) || 0;
    },
    formatEvaluation(value) {
      const num = Number(value);
      return Number.isNaN(num) ? "N/A" : num.toFixed(1);
    },
    async fetchFavourites() {
      try {
        this.loading = true;
        const token = this.authStore.token;
        const response = await axios.get(`${BASE_URL}/favourites`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.data?.data) {
          this.favourites = response.data.data.map(fav => ({
            ...fav,
            evaluation: Number(fav.evaluation) || 0
          }));
          // In admin view, assume favourites data already includes joined fields (userName, filmTitle)
        }
      } catch (error) {
        console.error("Error fetching favourites:", error);
        this.errorMessages = "Error fetching data from the server.";
      } finally {
        this.loading = false;
      }
    },
    async fetchPublicReviews() {
      try {
        this.loading = true;
        const token = this.authStore.token;
        // For guest reviews, use favourites endpoint and filter by isPublic flag
        const response = await axios.get(`${BASE_URL}/favourites`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.data?.data) {
          this.publicReviews = response.data.data.filter(review => review.isPublic).map(review => ({
            ...review,
            userName: review.userName || "Anonymous",
            filmTitle: review.filmTitle || "Unknown Film",
            content: review.content || "It was a great film!",
            created_at: review.created_at,
            userAvatar: review.userAvatar || "",
            evaluation: Number(review.evaluation) || 0
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
    async deleteItemById() {
      const id = this.selectedRowId;
      try {
        await axios.delete(`${BASE_URL}/favourites/${id}`, {
          headers: { Authorization: `Bearer ${this.authStore.token}` }
        });
        if (this.viewMode === "admin") {
          this.fetchFavourites();
        } else {
          this.fetchPublicReviews();
        }
      } catch (error) {
        this.errorMessages = "The review cannot be deleted.";
      }
    },
    formatDate(date) {
      try {
        const d = new Date(date);
        return isNaN(d) ? "N/A" : d.toLocaleString("en-US");
      } catch {
        return "N/A";
      }
    },
    handlePageChange(pageInfo) {
      this.currentPage = pageInfo.pageNumber;
    },
    onClickDeleteButton(item) {
      this.state = "Delete";
      this.title = "Delete Review";
      this.messageYesNo = `Are you sure you want to delete the review by ${item.userName}?`;
      this.yes = "Yes";
      this.no = "No";
      this.selectedRowId = item.id;
    },
    onClickUpdate(item) {
      this.state = "Update";
      this.title = "Update Review";
      this.yes = null;
      this.no = "Cancel";
      this.size = "lg";
      this.item = { ...item };
      this.selectedRowId = item.id;
    },
    saveItemHandler() {
      if (this.state === "Update") this.updateItem();
      if (this.state === "Delete") this.deleteItemById();
      this.modal.hide();
    },
    async submitReview() {
      if (!this.reviewText.trim() || !this.selectedFilmId || this.rating === 0) return;
      this.submitting = true;
      this.errorMessage = "";
      try {
        // If token exists, include it; if not, omit Authorization header for guest submission.
        const token = this.authStore.token;
        const headers = {
          Accept: "application/json",
          "Content-Type": "application/json"
        };
        if (token) {
          headers.Authorization = `Bearer ${token}`;
        }
        // Use a default guest user ID if authStore.id is not set.
        const userId = this.authStore.id || 2;
        await axios.post(`${BASE_URL}/favourites`, {
          filmId: this.selectedFilmId,
          evaluation: this.rating,
          content: this.reviewText,
          isPublic: true,
          userId: userId
        }, { headers });
        this.reviewText = "";
        this.selectedFilmId = "";
        this.rating = 0;
        await this.fetchPublicReviews();
      } catch (error) {
        this.errorMessage = error.response?.data?.message || "Failed to submit review.";
      } finally {
        this.submitting = false;
      }
    },
    setRating(star) {
      this.rating = star;
    }
  }
};
</script>

<style scoped>
:root {
  --primary-color: #8B0000; /* Dark Red */
  --secondary-color: #FFD700; /* Gold */
  --accent-color: #000000;   /* Black */
  --background-dark: #1a1a1a;
  --text-light: #ffffff;
  --text-muted: #cccccc;
}

.film-reviews {
  background: var(--background-dark);
  min-height: 100vh;
  padding-bottom: 120px;
  color: var(--text-light);
}

.toggle-container {
  text-align: center;
  margin: 1rem 0;
}

.btn-toggle {
  background: var(--primary-color);
  color: var(--secondary-color);
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-toggle:hover {
  background: var(--secondary-color);
  color: var(--accent-color);
}

.toggle-text {
  margin-left: 0.5rem;
  font-weight: bold;
}

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

/* Table Styling */
.custom-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 0.5rem;
  background: #FFFFFF;
  border: 2px solid var(--primary-color);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.custom-table thead {
  background: var(--primary-color);
  border-bottom: 3px solid var(--secondary-color);
}

.custom-table th {
  font-weight: 700;
  letter-spacing: 1px;
  color: var(--secondary-color) !important;
  text-transform: uppercase;
}

.custom-table tbody tr {
  background-color: rgba(248, 249, 250, 1);
  transition: background 0.3s;
  color: #000;
}

.custom-table tbody tr:hover {
  background: rgba(240, 240, 240, 1);
  box-shadow: 0 3px 15px rgba(255, 215, 0, 0.2);
}

/* Review Card */
.review-card {
  border: 1px solid #ddd;
  padding: 1rem;
  margin-bottom: 0.5rem;
  border-radius: 8px;
}

/* Star Rating */
.star-rating {
  font-size: 1.1rem;
  line-height: 1;
}

.bi {
  font-size: 1.25rem;
  vertical-align: middle;
}

/* Date Styling */
.date {
  color: var(--text-muted);
  font-size: 0.9rem;
  font-style: italic;
}

/* Fixed Paginator */
.pagination-container {
  position: sticky;
  bottom: 20px;
  margin-top: 2rem;
  display: flex;
  justify-content: center;
  background: rgba(0, 0, 0, 0.9);
  padding: 1rem;
  border-radius: 8px;
  z-index: 100;
  width: fit-content;
  margin-left: auto;
  margin-right: auto;
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

/* Guest Review Form (YouTube Comment Style) */
.guest-review-form {
  background: #f9f9f9;
  border: 1px solid #ccc;
  padding: 1rem;
  border-radius: 8px;
  max-width: 600px;
  margin: 1rem auto;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.username {
  font-weight: bold;
  color: #333;
}

/* Film Selection Dropdown */
.film-select {
  width: 100%;
  padding: 0.5rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

/* Star Input for Rating */
.star-input {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-bottom: 1rem;
}

.star-icon {
  font-size: 1.5rem;
  color: #FFD700;
  cursor: pointer;
  transition: transform 0.2s;
}

.star-icon:hover {
  transform: scale(1.2);
}

.rating-display {
  font-weight: bold;
  margin-left: 0.5rem;
}

/* Review Input Textarea */
.review-input {
  width: 100%;
  min-height: 80px;
  resize: vertical;
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 0.5rem;
  font-size: 1rem;
  color: #333;
}

/* Review Actions */
.review-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.5rem;
}

.btn-submit {
  background: #cc181e;
  color: #fff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-submit:hover {
  background: #b10d12;
}

.error-message {
  color: red;
  font-size: 0.9rem;
}

.public-reviews {
  margin-top: 2rem;
}

.review-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.avatar-small {
  width: 32px;
  height: 32px;
  border-radius: 50%;
}

.review-meta {
  display: flex;
  flex-direction: column;
}

.review-author {
  font-weight: 500;
}

.review-date {
  font-size: 0.8rem;
  color: #666;
}

.review-content {
  white-space: pre-wrap;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .custom-table th,
  .custom-table td {
    padding: 0.5rem;
    font-size: 0.9rem;
  }
  .pagination .page-item .page-link {
    min-width: 30px;
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
  }
}
</style>
