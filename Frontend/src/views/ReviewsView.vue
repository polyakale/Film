<template>
  <div class="film-reviews">
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
        <div class="row d-flex justify-content-center">
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
        </div>
        <!-- Fixed Paginator -->
        <div class="pagination-container" v-if="favourites.length > 0">
          <Paginator
            :pageNumber="currentPage"
            :numberOfPages="totalPages"
            :pagesArray="pagesArray"
            @paging="handlePageChange"
          />
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
    };
  },
  mounted() {
    this.fetchFavourites();
    this.modal = new bootstrap.Modal("#modal", { keyboard: false });
  },
  computed: {
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
  methods: {
    yesEventHandler() {
      if (this.state === "Delete") {
        this.deleteItemById();
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
        // Now using a backend join query, API should return userName and filmTitle already
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
  padding-bottom: 100px;
  color: var(--text-light);
}

/* Enhanced Loading Overlay */
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

/* Fixed Paginator at Bottom */
.pagination-container {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--accent-color);
  padding: 10px 20px;
  border-radius: 30px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
  border: 1px solid var(--secondary-color);
  z-index: 1000;
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
