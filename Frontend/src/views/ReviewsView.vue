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
                    <template v-for="index in 5" :key="index">
                      <!-- Full Star -->
                      <i
                        v-if="favourite.evaluation >= index"
                        class="bi bi-star-fill text-warning mx-1"
                      ></i>

                      <!-- Half Star -->
                      <i
                        v-else-if="favourite.evaluation + 0.5 >= index"
                        class="bi bi-star-half text-warning mx-1"
                      ></i>

                      <!-- Empty Star -->
                      <i v-else class="bi bi-star text-warning mx-1"></i>
                    </template>
                    <small class="text-muted ms-2">
                      ({{ favourite.evaluation.toFixed(1) }})
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
import * as bootstrap from "bootstrap"; // Import bootstrap

class Item {
  constructor(id = null, userId = null, filmId = null, evaluation = null) {
    this.id = id;
    this.userId = userId;
    this.filmId = filmId;
    this.evaluation = evaluation;
  }
}
export default {
  components: { Paginator, OperationsCrud, Modal, ReviewForm }, // Add Modal and ReviewForm
  data() {
    return {
      favourites: [], // All favourites
      authStore: useAuthStore(),
      currentPage: 1,
      itemsPerPage: 5,
      selectedRowId: null,
      errorMessages: null,
      loading: false,
      modal: null,
      item: new Item(),
      messageYesNo: null,
      state: "Read", //CRUD: Create, Read, Update, Delete
      title: null,
      yes: null,
      no: null,
      size: null,
      debug: false,
    };
  },
  mounted() {
    this.fetchFavourites();
    this.modal = new bootstrap.Modal("#modal", {
      // Initialize modal
      keyboard: false,
    });
  },
  computed: {
    paginatedFavourites() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.favourites.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.favourites.length / this.itemsPerPage);
    },
    pagesArray() {
      const pages = [];
      for (let i = 1; i <= this.totalPages; i++) {
        pages.push(i);
      }
      return pages;
    },
  },
  methods: {
    async fetchFavourites() {
      try {
        this.loading = true;
        // Get the token from the auth store
        const token = this.authStore.token;

        // Fetch the favourites with the Authorization token in the header
        const response = await axios.get(`${BASE_URL}/favourites`, {
          headers: {
            Authorization: `Bearer ${token}`, // Pass token in the Authorization header
          },
        });

        console.log("Fetched favourites:", response.data);

        if (response.data && Array.isArray(response.data.data)) {
          this.favourites = response.data.data;

          // Fetch all user and film data in parallel using Promise.all
          const userPromises = this.favourites.map(
            (favourite) =>
              axios
                .get(`${BASE_URL}/users/${favourite.userId}`, {
                  headers: { Authorization: `Bearer ${token}` }, // Add token for user data request
                })
                .then((res) => res.data)
                .catch(() => ({ name: "Unknown User" })) // Fallback if user fetch fails
          );

          const filmPromises = this.favourites.map(
            (favourite) =>
              axios
                .get(`${BASE_URL}/films/${favourite.filmId}`, {
                  headers: { Authorization: `Bearer ${token}` }, // Add token for film data request
                })
                .then((res) => res.data)
                .catch(() => ({ name: "Unknown Film" })) // Fallback if film fetch fails
          );

          // Wait for all promises to resolve
          const users = await Promise.all(userPromises);
          const films = await Promise.all(filmPromises);

          // Update favourites with user and film data
          this.favourites.forEach((favourite, index) => {
            favourite.userName = users[index].name || "Unknown User";
            favourite.filmName = films[index].name || "Unknown Film";
          });
        } else {
          console.error("Invalid data format:", response.data);
          this.errorMessages = "Invalid data format from server";
        }
      } catch (error) {
        console.error("Error fetching favourites:", error);
        this.errorMessages = "Error fetching data from the server.";
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      if (!date) return "N/A";
      const d = new Date(date);
      return isNaN(d) ? "N/A" : d.toLocaleString("hu-HU");
    },
    handlePageChange(pageInfo) {
      this.currentPage = pageInfo.pageNumber;
    },
    onClickCloseErrorMessage() {
      this.errorMessages = null;
      this.loading = false;
      this.state = "Read";
    },
    onClickDeleteButton(item) {
      this.state = "Delete";
      this.title = "Törlés";
      this.messageYesNo = `Valóban törölni akarod a(z) ${item.userName} nevű értékelést?`;
      this.yes = "Igen";
      this.no = "Nem";
      this.size = null;
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

    onClickCreate() {
      this.title = "Új értékelés";
      this.yes = null;
      this.no = "Mégsem";
      this.size = "lg";
      this.state = "Create";
      this.item = new Item();
    },
    async deleteItemById() {
      const id = this.selectedRowId;
      const token = this.authStore.token;

      const url = `${BASE_URL}/favourites/${id}`;
      const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      };

      try {
        const response = await axios.delete(url, { headers });
        this.getCollections();
      } catch (error) {
        this.errorMessages = "Az értékelés nem törölhető.";
      }
    },
    async updateItem() {
      this.loading = true;
      const id = this.selectedRowId;
      const url = `${BASE_URL}/favourites/${id}`;
      const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${this.authStore.token}`,
      };

      const data = {
        userId: this.item.userId,
        filmId: this.item.filmId,
        evaluation: this.item.evaluation,
      };
      try {
        const response = await axios.patch(url, data, { headers });
        this.getCollections();
      } catch (error) {
        this.errorMessages = "A módosítás nem sikerült.";
      }
      this.state = "Read";
    },

    async createItem() {
      const token = this.authStore.token;
      const url = `${BASE_URL}/favourites`;
      const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      };

      const data = {
        userId: this.item.userId,
        filmId: this.item.filmId,
        evaluation: this.item.evaluation,
      };
      try {
        const response = await axios.post(url, data, { headers });
        this.getCollections();
      } catch (error) {
        this.errorMessages = "A bővítés nem sikerült.";
      }
      this.state = "Read";
    },
    saveItemHandler() {
      if (this.state === "Update") {
        this.updateItem();
      } else if (this.state === "Create") {
        this.createItem();
      } else if (this.state === "Delete") {
        this.deleteItemById();
      }

      this.modal.hide();
    },
    yesEventHandler() {
      if (this.state == "Delete") {
        this.deleteItemById();
        this.goToPage(1);
      }
    },
  },
};
</script>

<style scoped>
.star-rating {
  font-size: 1.1rem;
  line-height: 1;
}

.bi-star-fill,
.bi-star-half,
.bi-star {
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
.review-header {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 0.5rem;
}
.stars {
  color: gold;
  font-weight: bold;
}
.star-rating {
  display: inline-flex;
  font-size: 1.25rem;
  line-height: 1;
  color: gold;
}

.star-container {
  position: relative;
  display: inline-block;
  width: 1em;
}
</style>
