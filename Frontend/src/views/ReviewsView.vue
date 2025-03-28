<template>
  <div class="film-reviews">
    <div class="header-container">
      <h3 class="text-center my-4 title-text">Reviews</h3>
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
        <div>
          <div
            v-if="favourites.length >= 0"
            class="col-12 col-lg-10 tabla-container"
          >
            <table class="custom-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Film</th>
                  <th class="text-center">Evaluation</th>
                  <th>Date</th>
                  <th class="text-center">
                    Operations
                    <button
                      type="button"
                      class="btn btn-outline-success"
                      @click="onClickCreate"
                    >
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="favourite in paginatedFavourites"
                  :key="favourite.id"
                  class="review-card"
                >
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
                      @onClickCreate="onClickCreate"
                      :data="favourite"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            class="pagination-container"
            v-if="favourites.length >= itemsPerPage"
          >
            <Paginator
              :pageNumber="currentPage"
              :numberOfPages="totalPages"
              :pagesArray="pagesArray"
              @paging="handlePageChange"
            />
          </div>
          <!-- For an all reviews part later... -->
          <!-- <div class="public-reviews" v-if="publicReviews.length > 0">
            <div
              v-for="review in publicReviews"
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
              <div class="review-content">
                {{ review.content || randomDefaultReview() }}
              </div>
              <div class="star-rating d-inline-flex align-items-center ml-3">
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
          </div> -->
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
          v-if="state === 'Create' || state === 'Update'"
          :itemForm="item"
          :films="films"
          :username="username"
          @saveItem="saveItemHandler"
        />
      </Modal>
    </div>
  </div>
</template>

<script>
import Modal from "@/components/Modal.vue"; // Assuming this is the correct path
import Paginator from "@/components/Paginator.vue"; // Assuming this is the correct path
import OperationsCrud from "@/components/OperationsCrud.vue"; // Assuming this is the correct path
import ReviewForm from "@/components/forms/ReviewForm.vue"; // Assuming this is the correct path
import { useAuthStore } from "@/stores/useAuthStore"; // Assuming this is the correct path
import { BASE_URL } from "../helpers/baseUrls"; // Assuming this is the correct path
import axios from "axios";

export default {
  components: { Paginator, OperationsCrud, Modal, ReviewForm },
  data() {
    return {
      showReviewForm: false,
      favourites: [], // For admin view
      publicReviews: [], // For guest view
      films: [], // List of films for the dropdown
      authStore: useAuthStore(),
      currentPage: 1,
      itemsPerPage: 10,
      selectedRowId: null, // ID of the selected review for deletion/update
      errorMessages: null,
      loading: false,
      modal: null, // Instance of the Bootstrap modal
      item: {},
      messageYesNo: null,
      state: "Read", // "Read", "Create", "Update", "Delete"
      title: null,
      yes: null,
      no: null,
      size: null,
      username: null, // Username of the logged-in user
      reviewText: "", // Text of the review being submitted by a guest
      submitting: false, // Flag to indicate if a review is being submitted
      errorMessage: "", // Error message for review submission
      selectedFilmId: "", // ID of the selected film for review
      rating: 0, // Star rating given by the guest
    };
  },
  computed: {
    fullStars() {
      return Math.floor(this.rating); // Get the number of full stars
    },
    hasHalfStar() {
      return this.rating % 1 >= 0.5; // Check if there's a half star
    },
    halfStar() {
      return this.fullStars + 1; // Get the index of the half star
    },
    paginatedFavourites() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.favourites.slice(start, start + this.itemsPerPage);
    },
    totalPages() {
      // Calculate the total number of pages (admin view)
      return Math.ceil(this.favourites.length / this.itemsPerPage);
    },
    pagesArray() {
      // Generate an array of page numbers for the paginator (admin view)
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
  },
  mounted() {
    this.modal = this.$refs.modalRef;
    this.fetchFavourites();
    this.fetchFilms();
  },
  methods: {
    async fetchFavourites() {
      // Fetch all reviews (for admin view)
      try {
        const userId = this.authStore.id;
        this.loading = true;
        const token = this.authStore.token;
        let url = `${BASE_URL}/favouritesByUserId/${userId}`;
        console.log(url);

        const response = await axios.get(url, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (response.data?.data) {
          this.favourites = response.data.data.map((fav) => ({
            ...fav,
            evaluation: Number(fav.evaluation) || 0, // Ensure evaluation is a number
          }));
        }
      } catch (error) {
        console.error("Error fetching favourites:", error);
        this.errorMessages = "Error fetching data from the server.";
      } finally {
        this.loading = false;
      }
    },
    // async fetchAllReviews() {
    //   try {
    //     this.loading = true;
    //     const token = this.authStore.token;
    //     const response = await axios.get(`${BASE_URL}/favourites`, {
    //       headers: token ? { Authorization: `Bearer ${token}` } : {},
    //     });
    //     if (response.data?.data) {
    //       this.publicReviews = response.data.data
    //         .filter((review) => review.isPublic) // Filter for public reviews
    //         .map((review) => ({
    //           ...review,
    //           userName: review.userName || "Anonymous",
    //           filmTitle: review.filmTitle || "Unknown Film",
    //           evaluation: Number(review.evaluation) || 0, // Ensure evaluation is a number
    //         }));
    //     }
    //   } catch (error) {
    //     console.error("Error fetching public reviews:", error);
    //     this.errorMessages = "Error fetching data from the server.";
    //   } finally {
    //     this.loading = false;
    //   }
    // },
    async fetchFilms() {
      // Fetch the list of films
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
        this.loading = false;
        this.state = "Read";
      }
    },
    async deleteReview() {
      const id = this.selectedRowId;
      try {
        await axios.delete(`${BASE_URL}/favourites/${id}`, {
          headers: {
            Authorization: `Bearer ${this.authStore.token}`,
          },
        });
        await this.fetchFavourites(); // Wait for refresh
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

        // Close modal safely
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
    // non-async methods
    yesEventHandler() {
      // Handle "Yes" button click in the modal
      if (this.state === "Delete") {
        this.deleteReview(); // Delete the selected review
      } else if (this.state === "Update") {
        this.updateReview(this.item, this.headers);
      } else if (this.state === "Create") {
        this.createReview(this.item, this.headers);
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
      // Get a random default review
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
      // Helper function to format dates
      try {
        const d = new Date(date);
        return isNaN(d) ? "N/A" : d.toLocaleString("hu-HU");
      } catch {
        return "N/A";
      }
    },
    handlePageChange(pageInfo) {
      // Handle page change in the paginator
      this.page = pageInfo.pageNumber;
      this.pageSize = pageInfo.pageSize;
      if (pageInfo === "...") {
        this.currentPage = this.totalPages;
      } else {
        this.currentPage = pageInfo.pageNumber;
      }
    },
    onClickDelete(item) {
      this.state = "Delete";
      this.title = "Delete Review";
      this.messageYesNo = `Are you sure you want to delete the review by ${item.userName}?`;
      this.yes = "Yes";
      this.no = "No";
      this.size = null;
      this.selectedRowId = item.id; // Store the ID of the review to delete
    },
    onClickUpdate(item) {
      this.state = "Update";
      this.selectedRowId = item.id;
      this.item = {
        ...item,
      };
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
      this.item = {
        filmId: "",
        evaluation: 0,
        userId: this.authStore.id,
      };
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
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1rem;
  padding: 0 1rem;
}

.title-text {
  color: var(--text-light);
  margin: 0;
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

/* Updated Table Styles */
.custom-table {
  width: 120%;
  border-collapse: collapse;
  background: #383838;
  border: 2px solid #1f1f1f;
  color: #ffd700;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  font-size: 0.95rem;
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
}

.custom-table tbody tr {
  transition: background-color 0.2s ease;
}

.custom-table tbody tr:hover {
  background: #2a2a2a;
}

/* Text Center Alignment for Specific Columns */
.custom-table th.text-center,
.custom-table td.text-center {
  text-align: center;
}

/* Review Card Styles */
.review-card {
  border: 1px solid #ddd;
  padding: 1rem;
  margin-bottom: 0.5rem;
  border-radius: 0px;
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
  position: sticky;
  bottom: 0%;
  margin-top: 2rem;
  display: flex;
  justify-content: center;
  background: #1a1a1a;
  border: 1px solid #ffd700;
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
}
</style>