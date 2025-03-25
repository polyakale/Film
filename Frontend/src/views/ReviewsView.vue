<template>
  <div class="film-reviews">
    <div v-if="isAdmin" class="header-container">
      <h3 class="text-center my-4 title-text">Reviews</h3>
      <div class="toggle-container">
        <button class="btn-toggle" @click="toggleViewMode">
          <i
            :class="
              viewMode === 'admin' ? 'bi bi-toggle-on' : 'bi bi-toggle-off'
            "
          ></i>
          <span class="toggle-text">
            Switch to {{ viewMode === "admin" ? "Guest" : "Admin" }} View
          </span>
        </button>
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
        <div v-if="viewMode === 'admin'" class="admin-view">
          <div
            v-if="favourites.length > 0"
            class="col-12 col-lg-10 tabla-container"
          >
            <table class="custom-table">
              <thead>
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
                <tr
                  v-for="favourite in paginatedFavourites"
                  :key="favourite.id"
                  class="review-card"
                >
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

        <div v-else class="guest-view">
          <div class="guest-review-form">
            <div class="user-info">
              <span class="username">{{ username }}</span>
              <div class="star-input">
                <span
                  v-for="starIndex in 5"
                  :key="starIndex"
                  @click="setRating(starIndex, $event)"
                >
                  <i
                    class="bi star-icon"
                    :class="{
                      'bi-star-fill': starIndex <= fullStars,
                      'bi-star-half': starIndex === halfStar && hasHalfStar,
                      'bi-star':
                        starIndex > fullStars &&
                        !(starIndex === halfStar && hasHalfStar),
                    }"
                  ></i>
                </span>
                <span class="rating-display"> ({{ rating.toFixed(1) }}) </span>
              </div>
              <select v-model="selectedFilmId" class="film-select">
                <option value="" disabled>Select Film</option>
                <option v-for="film in films" :key="film.id" :value="film.id">
                  {{ film.title }}
                </option>
              </select>
            </div>
            <div class="input-group">
              <textarea
                v-model="reviewText"
                class="review-input"
                placeholder="Write a review..."
              ></textarea>
              <div class="review-actions">
                <button
                  class="btn-submit"
                  @click="submitReview"
                  :disabled="
                    submitting ||
                    !reviewText.trim() ||
                    !selectedFilmId ||
                    rating === 0
                  "
                >
                  Submit
                </button>
                <span v-if="errorMessage" class="error-message">{{
                  errorMessage
                }}</span>
              </div>
            </div>
          </div>
          <div class="public-reviews" v-if="publicReviews.length > 0">
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
          </div>
          <div v-else class="no-reviews">
            No reviews yet. Be the first to write one!
          </div>
        </div>
      </div>
      <Modal
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
import * as bootstrap from "bootstrap"; // Import Bootstrap for modal functionality

export default {
  components: { Paginator, OperationsCrud, Modal, ReviewForm },
  data() {
    return {
      favourites: [], // For admin view
      publicReviews: [], // For guest view
      films: [], // List of films for the dropdown
      authStore: useAuthStore(),
      currentPage: 1,
      itemsPerPage: 5,
      selectedRowId: null, // ID of the selected review for deletion/update
      errorMessages: null,
      loading: false,
      modal: null, // Instance of the Bootstrap modal
      // For admin CRUD modal
      item: {},
      messageYesNo: null,
      state: "Read", // "Read", "Create", "Update", "Delete"
      title: null,
      yes: null,
      no: null,
      size: null,
      debug: false, // Flag to show/hide debug info
      viewMode: "admin", // "admin" or "guest"
      reviewText: "", // Text of the review being submitted by a guest
      submitting: false, // Flag to indicate if a review is being submitted
      errorMessage: "", // Error message for review submission
      // For guest review submission
      selectedFilmId: "", // ID of the selected film for review
      rating: 0, // Star rating given by the guest
      // Default review texts for missing content
      defaultReviews: [
        "It was a great film!",
        "An unforgettable experience.",
        "I really enjoyed this movie.",
        "A masterpiece of cinema.",
        "It was a decent watch.",
        "A remarkable performance by the cast.",
        "Simply outstanding!",
        "A solid film with a compelling story.",
      ],
    };
  },
  computed: {
    isAdmin() {
      return this.authStore.positionId === 1; // Check if the user is an admin
    },
    username() {
      return this.authStore.user || "Guest"; // Get username or "Guest"
    },
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
      // Get the subset of reviews for the current page (admin view)
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
    // Fetch data and initialize modal when the component is mounted
    if (this.isAdmin) {
      this.viewMode = "admin";
      this.fetchFavourites(); // Fetch data for admin view
    } else {
      this.viewMode = "guest";
      this.fetchPublicReviews(); // Fetch data for guest view
    }
    this.fetchFilms(); // Fetch the list of films for the dropdown
    // Initialize Bootstrap modal
    this.modal = new bootstrap.Modal(document.getElementById("modal"), {
      keyboard: false, // Prevent closing with keyboard
    });
  },
  methods: {
    toggleViewMode() {
      // Switch between admin and guest views
      if (this.isAdmin) {
        this.viewMode = this.viewMode === "admin" ? "guest" : "admin";
        if (this.viewMode === "guest") {
          this.fetchPublicReviews(); // Fetch public reviews for guest view
        } else {
          this.fetchFavourites(); // Fetch all reviews for admin view
        }
      }
    },
    yesEventHandler() {
      // Handle "Yes" button click in the modal
      if (this.state === "Delete") {
        this.deleteItemById(); // Delete the selected review
      }
    },
    getEvaluation(item) {
      // Helper function to get the evaluation (or default to 0)
      return Number(item.evaluation) || 0;
    },
    formatEvaluation(value) {
      // Helper function to format the evaluation value
      const num = Number(value);
      return Number.isNaN(num) ? "N/A" : num.toFixed(1);
    },
    randomDefaultReview() {
      // Get a random default review
      const index = Math.floor(Math.random() * this.defaultReviews.length);
      return this.defaultReviews[index];
    },
    async fetchFavourites() {
      // Fetch all reviews (for admin view)
      try {
        this.loading = true;
        const token = this.authStore.token;
        const response = await axios.get(`${BASE_URL}/favourites`, {
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
    async fetchPublicReviews() {
      // Fetch public reviews (for guest view)
      try {
        this.loading = true;
        const token = this.authStore.token;
        const response = await axios.get(`${BASE_URL}/favourites`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {},
        });
        if (response.data?.data) {
          this.publicReviews = response.data.data
            .filter((review) => review.isPublic) // Filter for public reviews
            .map((review) => ({
              ...review,
              userName: review.userName || "Anonymous",
              filmTitle: review.filmTitle || "Unknown Film",
              evaluation: Number(review.evaluation) || 0, // Ensure evaluation is a number
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
    async deleteItemById() {
      // Delete a review by its ID (admin view)
      const id = this.selectedRowId;
      try {
        await axios.delete(`${BASE_URL}/favourites/${id}`, {
          headers: { Authorization: `Bearer ${this.authStore.token}` },
        });
        if (this.viewMode === "admin") {
          this.fetchFavourites(); // Refresh admin view
        } else {
          this.fetchPublicReviews(); // Refresh guest view
        }
        this.modal.hide(); // Hide the modal after deletion
      } catch (error) {
        this.errorMessages = "The review cannot be deleted.";
      }
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
      // Handle page change in the paginator (admin view)
      if (pageInfo === "...") {
        this.currentPage = this.totalPages;
      } else {
        this.currentPage = pageInfo.pageNumber;
      }
    },
    onClickDeleteButton(item) {
      // Handle delete button click (admin view)
      this.state = "Delete";
      this.title = "Delete Review";
      this.messageYesNo = `Are you sure you want to delete the review by ${item.userName}?`;
      this.yes = "Yes";
      this.no = "No";
      this.selectedRowId = item.id; // Store the ID of the review to delete
      this.modal.show(); // Show the modal
    },
    onClickUpdate(item) {
      // Handle update button click (admin view)
      this.state = "Update";
      this.title = "Update Review";
      this.yes = null;
      this.no = "Cancel";
      this.size = "lg";
      this.item = { ...item }; // Store the review data for editing
      this.selectedRowId = item.id; // Store the ID of the review to update
      this.modal.show(); // Show the modal
    },
    saveItemHandler() {
      // Handle save button click in the modal (admin view)
      if (this.state === "Update") this.updateItem(); // Call update function
      if (this.state === "Delete") this.deleteItemById(); // Call delete function
      this.modal.hide(); // Hide the modal after saving
    },
    async submitReview() {
      // Submit a review (guest view)
      if (!this.selectedFilmId || this.rating === 0) return;

      this.submitting = true;
      this.errorMessage = "";

      try {
        const token = this.authStore.token;
        const headers = {
          "Content-Type": "application/json",
          ...(token && { Authorization: `Bearer ${token}` }),
        };

        const userId = this.authStore.id || 2; // Use a default user ID if not logged in
        const selectedFilm = this.films.find(
          (f) => f.id === this.selectedFilmId
        );

        // Create review object without content/isPublic since DB doesn't have these
        const reviewData = {
          filmId: this.selectedFilmId,
          evaluation: this.rating,
          userId: userId,
        };

        const response = await axios.post(
          `${BASE_URL}/favourites`,
          reviewData,
          { headers }
        );

        // Manually construct review object for display in guest view
        this.publicReviews.unshift({
          id: response.data.data.id,
          filmId: this.selectedFilmId,
          filmTitle: selectedFilm?.title || "Unknown Film",
          evaluation: this.rating,
          userName: this.username,
          created_at: new Date().toISOString(),
          // Client-side only fields:
          content: this.reviewText, // Store in memory only
        });

        this.reviewText = "";
        this.selectedFilmId = "";
        this.rating = 0;
      } catch (error) {
        this.errorMessage =
          error.response?.data?.message ||
          "Failed to submit review. Please try again.";
        console.error("Review submission error:", error);
      } finally {
        this.submitting = false;
      }
    },
    setRating(starIndex, event) {
      // Set the star rating (guest view)
      const target =
        event.currentTarget.querySelector("i") || event.currentTarget;
      const rect = target.getBoundingClientRect();
      const clickX = event.clientX - rect.left;
      const isHalfStar = clickX < rect.width / 2;
      this.rating = isHalfStar ? starIndex - 0.5 : starIndex;
    },
  },
};
</script>

<style scoped>
/* Root Variables */
:root {
  --primary-color: #8b0000;
  /* Dark Red */
  --secondary-color: #ffd700;
  /* Gold */
  --accent-color: #000000;
  /* Black */
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
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding: 0 1rem;
}

.title-text {
  color: var(--text-light);
  margin: 0;
}

.toggle-container {
  color: #ffd700;
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

/* Table Styles */
.custom-table {
  width: 120%;
  border-collapse: separate;
  border-spacing: 0 0.5rem;
  background: #ffffff;
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
  text-transform: uppercase;
}

.custom-table tbody tr {
  transition: background 0.3s;
  color: #000000;
}

.custom-table tbody tr:hover {
  background: rgba(240, 240, 240, 1);
  box-shadow: 0 3px 15px rgba(255, 215, 0, 0.2);
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
  background: rgba(105, 91, 91, 0.9);
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

/* Guest Review Form Styles */
.guest-review-form {
  background: #383838;
  border: 3px solid #1f1f1f;
  padding: 1rem;
  border-radius: 0px;
  max-width: 1256px;
  min-width: 230px;
  margin: 0px auto;
  color: #ffd700;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  width: 100%;
  flex-wrap: wrap;
}

.username {
  font-weight: bold;
  color: #ffd700;
  margin-right: 1rem;
}

.film-and-stars {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  width: 100%;
}

.film-select {
  width: 60%;
  padding: 0.5rem;
  background: #383838;
  border: 3px solid #1f1f1f;
  border-radius: 0px;
  font-size: 0.9rem;
  color: #b0b0b0;
  flex: 1;
  min-width: 200px;
}

/* Star Input Styles */
.star-input {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-bottom: 0rem;
  margin-left: 0;
}

.star-icon {
  font-size: 1.5rem;
  color: #ffd700;
  cursor: pointer;
  transition: transform 0.2s;
}

.star-icon:hover {
  transform: scale(1.2);
}

.rating-display {
  font-weight: bold;
  margin-left: 0.5rem;
  font-size: 0.9rem;
}

/* Review Input Styles */
.input-group {
  display: flex;
  align-items: flex-end; /* Align button with textarea bottom */
}

.review-input {
  width: 80%;
  height: 40px;
  resize: vertical;
  border: none;
  background: #383838;
  border-bottom: 2px solid #1a1a1a;
  /* White bottom border */
  flex: 1; /* Take remaining space */
  margin-right: 20px; /* Fixed gap between textarea and button */
  border-radius: 0px;
  padding: 0.5rem;
  font-size: 0.9rem;
  color: #b0b0b0;
  box-sizing: border-box;
  outline: none;
  /* Remove default focus border */
}

/* Add the focus effect for the bottom border */
.review-input:focus {
  border-bottom: 2px solid #fff;
  /* White border on focus */
  color: #fff;
  /* Keep text color white on focus */
}

/* Review Actions Styles */
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
  padding: 0.5rem 1.5rem;
  border-radius: 60px;
  font-weight: bold;
  cursor: pointer;
  white-space: nowrap;
  /* Remove all margins - positioning handled by flexbox */
}
.btn-submit:hover {
  background: #b10d12;
}

.error-message {
  color: red;
  font-size: 0.9rem;
}

/* Submitted Review Styles */
.public-reviews {
  margin-top: 2rem;
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

/* Responsive Styles */
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
  .input-group {
    flex-direction: column;
    align-items: flex-end;
  }

  .review-input {
    margin-right: 0;
    margin-bottom: 15px;
    width: 100%;
  }
  .film-and-stars {
    flex-direction: column;
    align-items: flex-start;
  }
  .film-select {
    width: 100%;
    margin-bottom: 1rem;
  }
  .star-input {
    margin-left: 0;
  }
}
</style>
