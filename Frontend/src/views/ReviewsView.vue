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

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="loading-text">Loading...</div>
      </div>
    </div>

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
                      @onClickDeleteButton="onClickDeleteButton"
                      @onClickUpdate="onClickUpdate"
                      :data="favourite"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginator -->
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
          <!-- Review Form -->
          <div class="guest-review-form">
            <div class="user-info">
              <img :src="userAvatar" alt="User Avatar" class="avatar" />
              <span class="username">{{ username }}</span>
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
                :disabled="submitting || !reviewText.trim()"
              >
                Submit Review
              </button>
              <span v-if="errorMessage" class="error-message">{{ errorMessage }}</span>
            </div>
          </div>

          <!-- Public Reviews -->
          <div class="public-reviews" v-if="publicReviews.length > 0">
            <div v-for="review in publicReviews" :key="review.id" class="review-card">
              <div class="review-header">
                <img :src="review.user.avatar || userAvatar" class="avatar-small" />
                <div class="review-meta">
                  <span class="review-author">{{ review.user.name }}</span>
                  <span class="review-date">{{ formatDate(review.created_at) }}</span>
                </div>
              </div>
              <div class="review-content">{{ review.content }}</div>
            </div>
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
      authStore: useAuthStore(),
      currentPage: 1,
      itemsPerPage: 5,
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
      debug: false,
      viewMode: "admin",
      reviewText: "",
      submitting: false,
      errorMessage: "",
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
      return this.authStore.avatar || "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM4QjAwMDAiLz48L3N2Zz4=";
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
    },
  },
  mounted() {
    if (this.isAdmin) {
      this.fetchFavourites();
    } else {
      this.fetchPublicReviews();
    }
    this.modal = new bootstrap.Modal("#modal", { keyboard: false });
  },
  methods: {
    toggleViewMode() {
      if (this.isAdmin) {
        this.viewMode = this.viewMode === 'admin' ? 'guest' : 'admin';
        if (this.viewMode === 'guest') this.fetchPublicReviews();
      }
    },
    async fetchPublicReviews() {
      try {
        const response = await axios.get(`${BASE_URL}/public-reviews`);
        this.publicReviews = response.data.data;
      } catch (error) {
        console.error('Error fetching public reviews:', error);
      }
    },
    async submitReview() {
      if (!this.reviewText.trim()) return;
      this.submitting = true;
      this.errorMessage = "";
      try {
        await axios.post(
          `${BASE_URL}/reviews`,
          {
            content: this.reviewText,
            userId: this.authStore.userId
          },
          {
            headers: {
              Authorization: `Bearer ${this.authStore.token}`,
              "Content-Type": "application/json",
            },
          }
        );
        this.reviewText = "";
        await this.fetchPublicReviews();
      } catch (error) {
        this.errorMessage = error.response?.data?.message || "Failed to submit review.";
      } finally {
        this.submitting = false;
      }
    },
    getEvaluation(favourite) {
      return Number(favourite.evaluation) || 0;
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
    async deleteItemById() {
      const id = this.selectedRowId;
      try {
        await axios.delete(`${BASE_URL}/favourites/${id}`, {
          headers: { Authorization: `Bearer ${this.authStore.token}` },
        });
        this.fetchFavourites();
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
  },
};
</script>

<style scoped>
:root {
  --primary-color: #8B0000;
  --secondary-color: #FFD700;
  --accent-color: #000000;
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
  0% { opacity: 0.6; transform: scale(0.95); }
  50% { opacity: 1; transform: scale(1); }
  100% { opacity: 0.6; transform: scale(0.95); }
}

.spinner-border {
  width: 4rem;
  height: 4rem;
  border-width: 0.3em;
  color: var(--secondary-color);
  filter: drop-shadow(0 0 8px rgba(255, 215, 0, 0.4));
}

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

.review-card {
  border: 1px solid #ddd;
  padding: 1rem;
  margin-bottom: 0.5rem;
  border-radius: 8px;
}

.star-rating {
  font-size: 1.1rem;
  line-height: 1;
}

.bi {
  font-size: 1.25rem;
  vertical-align: middle;
}

.date {
  color: var(--text-muted);
  font-size: 0.9rem;
  font-style: italic;
}

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

.review-card {
  background: #fff;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  color: #333;
}

.avatar-small {
  width: 32px;
  height: 32px;
  border-radius: 50%;
}

.review-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
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