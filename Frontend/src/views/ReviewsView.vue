<template>
  <div class="film-reviews">
    <h3 class="text-center my-4">Reviews</h3>
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div
          class="spinner-border m-0 p-0 text-center"
          role="status"
          v-if="favourites.length == 0 && !loading"
        >
          <span class="visually-hidden m-0">Loading...</span>
        </div>
        <div
          v-if="favourites.length > 0"
          class="col-12 col-lg-10 tabla-container"
        >
          <table
            class="table table-bordered table-hover table-striped shadow-sm rounded"
          >
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
                  {{ favourite.filmName || "Unknown Film" }}
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
      </div>
      <div
        class="d-flex justify-content-center my-3"
        v-if="favourites.length > 0"
      >
        <Paginator
          :pageNumber="currentPage"
          :numberOfPages="totalPages"
          :pagesArray="pagesArray"
          @paging="handlePageChange"
        />
      </div>
    </div>
    <Modal
      :title="title"
      :yes="yes"
      :no="no"
      :size="size"
      @yesEvent="yesEventHandler"
    >
      <div v-if="state == 'Delete'">{{ messageYesNo }}</div>
      <ReviewForm
        v-if="state == 'Create' || state == 'Update'"
        :itemForm="item"
        @saveItem="saveItemHandler"
      />
    </Modal>
  </div>
</template>

<script>
import Modal from "@/components/Modal.vue";
import Paginator from "@/components/Paginator.vue";
import OperationsCrud from "@/components/OperationsCrud.vue";
import { useAuthStore } from "@/stores/useAuthStore";
import { BASE_URL } from "../helpers/baseUrls";
import axios from "axios";
import ReviewForm from "@/components/forms/ReviewForm.vue";
import * as bootstrap from "bootstrap";

class Item {
  constructor(id = null, userId = null, filmId = null, evaluation = null) {
    this.id = id;
    this.userId = userId;
    this.filmId = filmId;
    this.evaluation = evaluation;
  }
}

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
      item: new Item(),
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
      return Number(value) ? Number(value).toFixed(1) : "N/A";
    },
    async fetchFavourites() {
      try {
        this.loading = true;
        const token = this.authStore.token;
        const response = await axios.get(`${BASE_URL}/favourites`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (response.data?.data) {
          // Convert evaluations first
          this.favourites = response.data.data.map((fav) => ({
            ...fav,
            evaluation: Number(fav.evaluation) || 0,
          }));

          // Fetch user and film data in parallel
          const enrichedFavourites = await Promise.all(
            this.favourites.map(async (fav) => {
              try {
                const [userResponse, filmResponse] = await Promise.all([
                  axios.get(`${BASE_URL}/users/${fav.userId}`, {
                    headers: { Authorization: `Bearer ${token}` },
                  }),
                  axios.get(`${BASE_URL}/films/${fav.filmId}`, {
                    headers: { Authorization: `Bearer ${token}` },
                  }),
                ]);

                return {
                  ...fav,
                  userName: userResponse.data.name || "Unknown User",
                  filmName: filmResponse.data.name || "Unknown Film",
                };
              } catch (error) {
                return {
                  ...fav,
                  userName: "Unknown User",
                  filmName: "Unknown Film",
                };
              }
            })
          );

          this.favourites = enrichedFavourites;
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
        this.errorMessages = "Az értékelés nem törölhető.";
      }
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
      this.currentPage = pageInfo.pageNumber;
    },
    onClickDeleteButton(item) {
      this.state = "Delete";
      this.title = "Törlés";
      this.messageYesNo = `Valóban törölni akarod a(z) ${item.userName} nevű értékelést?`;
      this.yes = "Igen";
      this.no = "Nem";
      this.selectedRowId = item.id;
    },
    onClickUpdate(item) {
      this.state = "Update";
      this.title = "Értékelés módosítása";
      this.yes = null;
      this.no = "Mégsem";
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
.star-rating {
  font-size: 1.1rem;
  line-height: 1;
}

.bi {
  font-size: 1.25rem;
  vertical-align: middle;
}

.date {
  color: #6c757d;
  font-size: 0.9rem;
}

.review-card {
  border: 1px solid #ddd;
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 8px;
}

.text-warning {
  color: #ffc107 !important;
}
</style>
