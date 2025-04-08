<template>
  <div class="container">
    <h1>Films</h1>
    <p v-if="debug">{{ stateAuth }}</p>

    <div class="search-sort-container">
      <input
        type="text"
        v-model="searchQuery"
        @input="searchFilms"
        placeholder="Search films..."
        class="search-input"
      />
      <select v-model="sortOption" @change="sortFilms" class="sort-select">
        <option value="abc">ABC</option>
        <option value="production">Production Year</option>
        <option value="length">Length</option>
        <option value="evaluation">Evaluation</option>
      </select>
    </div>

    <div v-if="isAdmin" class="admin-actions">
      <button @click="openAddFilmModal" class="add-film-button">
        Add New Film
      </button>
    </div>
    <!-- Kártyák -->
    <div v-if="films.length" class="films-grid">
      <div
        v-for="film in filteredFilms"
        :key="film.id"
        class="film-card"
        :class="{ 'film-card-rated': rated(film, stateAuth.id) }"
      >
        <span class="star-icon" @click="openRatingModal(film)">★</span>
        <h2 class="film-title">{{ film.title }}</h2>
        <p class="film-info">
          <strong>Production Year:</strong> {{ film.production }}
        </p>
        <p class="film-info"><strong>Length:</strong> {{ film.length }} min</p>
        <p class="film-info">
          <strong>Presentation:</strong> {{ formatDate(film.presentation) }}
        </p>
        <p class="film-info">
          <strong>Evaluation:</strong>
          {{ film.evaluation || "This film hasn't been rated yet." }}
        </p>
        <a
          v-if="film.imdbLink"
          :href="formatImdbUrl(film.imdbLink)"
          target="_blank"
          rel="noopener noreferrer"
          class="imdb-link"
        >
          View on IMDb
        </a>

        <div v-if="isAdmin" class="film-actions">
          <button @click="openEditFilmModal(film)" class="edit-button">
            Edit
          </button>
          <button @click="deleteFilm(film.id)" class="delete-button">
            Delete
          </button>
        </div>
      </div>
    </div>

    <p v-else>Loading films...</p>

    <div v-if="showAddFilmModal" class="modal">
      <div class="modal-content">
        <h2>Add New Film</h2>
        <form @submit.prevent="submitNewFilm">
          <label>Title:</label>
          <input v-model="newFilm.title" required />

          <label>Production Year:</label>
          <input v-model="newFilm.production" type="number" required />

          <label>Length (minutes):</label>
          <input v-model="newFilm.length" type="number" required />

          <label>Presentation Date:</label>
          <input v-model="newFilm.presentation" type="date" required />

          <label>IMDb Link:</label>
          <input v-model="newFilm.imdbLink" />

          <button type="submit">Save</button>
          <button type="button" @click="closeAddFilmModal">Cancel</button>
        </form>
      </div>
    </div>

    <div v-if="showEditFilmModal" class="modal">
      <div class="modal-content">
        <h2>Edit Film</h2>
        <form @submit.prevent="submitEditedFilm">
          <label>Title:</label>
          <input v-model="editingFilm.title" required />

          <label>Production Year:</label>
          <input v-model="editingFilm.production" type="number" required />

          <label>Length (minutes):</label>
          <input v-model="editingFilm.length" type="number" required />

          <label>Presentation Date:</label>
          <input v-model="editingFilm.presentation" type="date" required />

          <label>IMDb Link:</label>
          <input v-model="editingFilm.imdbLink" />

          <button type="submit">Save</button>
          <button type="button" @click="closeEditFilmModal">Cancel</button>
        </form>
      </div>
    </div>

    <div v-if="showRatingModal" class="modal">
      <div class="modal-content">
        <h2 class="film-title">Please rate the movie</h2>
        <div class="rating-stars">
          <span
            v-for="n in 5"
            :key="n"
            @click="setRating(n)"
            @mousemove="handleHover(n, $event)"
            @mouseleave="hoverRating = null"
            :class="{
              'star-filled': (hoverRating || currentRating) >= n,
              'star-half':
                (hoverRating || currentRating) >= n - 0.5 &&
                (hoverRating || currentRating) < n,
            }"
          >
            ★
          </span>
        </div>
        <div class="comment-section">
          <label>Your Comment (optional):</label>
          <textarea v-model="currentComment" rows=""></textarea>
        </div>
        <div class="rating-actions">
          <button @click="submitRating" class="submit-button">Submit</button>
          <button @click="closeRatingModal" class="cancel-button">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { BASE_URL } from "../helpers/baseUrls";
import { DEBUG } from "../helpers/baseUrls";
import { useAuthStore } from "@/stores/useAuthStore";

export default {
  data() {
    return {
      debug: DEBUG,
      urlApi: `${BASE_URL}/films`,
      stateAuth: useAuthStore(),
      films: [],
      isAdmin: false,
      searchQuery: "",
      sortOption: "abc",
      filteredFilms: [],
      showAddFilmModal: false,
      showEditFilmModal: false,
      showRatingModal: false,
      newFilm: {
        title: "",
        production: "",
        length: "",
        presentation: "",
        imdbLink: "",
      },
      editingFilm: null,
      currentFilm: null,
      currentRating: 0,
      hoverRating: null,
      ifRated: null,
      selectedEvaulatedFilm: null,
      currentComment: "",
      existingCommentId: null,
    };
  },
  async mounted() {
    await this.fetchFilmsFromBackend();
    this.isAdmin = this.stateAuth.positionId === 1;
  },
  watch: {
    searchQuery() {
      this.searchFilms();
    },
    sortOption() {
      this.sortFilms();
    },
  },
  methods: {
    async queryFilmsWithEvaluation() {
      try {
        const response = await axios.get(`${BASE_URL}/films/evaluations`);
        return response.data.data || [];
      } catch (error) {
        console.error("Error fetching evaluations:", error);
        return [];
      }
    },

    async fetchFilmsFromBackend() {
      try {
        const filmsResponse = await axios.get(
          `${BASE_URL}/queryFilmsWithEvaluation`
        );
        this.films = Array.isArray(filmsResponse.data.data)
          ? filmsResponse.data.data
          : [];

        this.filteredFilms = [...this.films];
      } catch (error) {
        console.error("Error loading films:", error);
      }
    },

    formatDate(dateString) {
      if (!dateString) return "Unknown";
      const date = new Date(dateString);
      return date.toISOString().split("T")[0];
    },

    formatImdbUrl(imdbLink) {
      if (!imdbLink || imdbLink.trim() === "") return "#";
      if (!imdbLink.startsWith("http")) {
        return `https://www.imdb.com/title/${imdbLink}/`;
      }
      return imdbLink;
    },

    searchFilms() {
      const query = this.searchQuery.toLowerCase();
      this.filteredFilms = this.films.filter(
        (film) =>
          film.title.toLowerCase().includes(query) ||
          String(film.production).includes(query) ||
          String(film.length).includes(query) ||
          (film.evaluation && String(film.evaluation).includes(query))
      );
      this.sortFilms();
    },

    sortFilms() {
      if (this.sortOption === "abc") {
        this.filteredFilms.sort((a, b) => a.title.localeCompare(b.title));
      } else if (this.sortOption === "production") {
        this.filteredFilms.sort((a, b) => a.production - b.production);
      } else if (this.sortOption === "length") {
        this.filteredFilms.sort((a, b) => a.length - b.length);
      } else if (this.sortOption === "evaluation") {
        this.filteredFilms.sort((a, b) => {
          const aEval = a.evaluation || 0;
          const bEval = b.evaluation || 0;
          return bEval - aEval;
        });
      }
    },

    openAddFilmModal() {
      this.showAddFilmModal = true;
    },

    closeAddFilmModal() {
      this.showAddFilmModal = false;
      this.newFilm = {
        title: "",
        production: "",
        length: "",
        presentation: "",
        imdbLink: "",
      };
    },

    async submitNewFilm() {
      try {
        const token = this.stateAuth.token;
        const headers = {
          Authorization: `Bearer ${token}`,
        };

        const filmData = {
          title: this.newFilm.title,
          production: this.newFilm.production,
          length: this.newFilm.length,
          presentation: this.newFilm.presentation,
          imdbLink: this.newFilm.imdbLink,
        };

        await axios.post(BASE_URL, filmData, { headers });
        await this.fetchFilmsFromBackend();
        this.closeAddFilmModal();
      } catch (error) {
        console.error("Error adding film:", error);
      }
    },

    openEditFilmModal(film) {
      this.editingFilm = { ...film };
      this.showEditFilmModal = true;
    },

    closeEditFilmModal() {
      this.showEditFilmModal = false;
      this.editingFilm = null;
    },

    async submitEditedFilm() {
      try {
        const token = this.stateAuth.token;
        const headers = {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        };

        const filmData = {
          title: this.editingFilm.title,
          production: this.editingFilm.production,
          length: this.editingFilm.length,
          presentation: this.editingFilm.presentation,
          imdbLink: this.editingFilm.imdbLink,
        };

        await axios.patch(url, filmData, { headers });
        await this.fetchFilmsFromBackend();
        this.closeEditFilmModal();
      } catch (error) {
        console.error("Error editing film:", error);
      }
    },

    async deleteFilm(filmId) {
      if (confirm("Are you sure you want to delete this film?")) {
        try {
          const token = this.stateAuth.token;
          const headers = {
            Accept: "application/json",
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          };

          const url = `${BASE_URL}/${filmId}`;
          await axios.delete(url, { headers });
          await this.fetchFilmsFromBackend();
        } catch (error) {
          console.error("Error deleting film:", error);
        }
      }
    },
    rated(film, id) {
      if (film.usersId) {
        const usersId = film.usersId.split(",");
        return usersId.includes(id.toString());
      }
      return false;
    },
    async openRatingModal(film) {
      this.selectedEvaulatedFilm = film;
      const id = this.stateAuth.id;
      
      // Reset comment fields
      // this.currentComment = "";
      // this.existingCommentId = null;
      
      // Check if user has already rated
      if (film.usersId) {
        const usersId = film.usersId.split(",");
        this.ifRated = usersId.includes(id.toString());
      } else {
        this.ifRated = false;
      }
      
      // If rated, try to fetch existing comment
      if (this.ifRated) {
        try {
          const url = `${BASE_URL}/favourites/${film.userId}/${film.id}`;
          const response = await axios.get(url);
          if (response.data.data && response.data.data.content) {
            this.currentComment = response.data.data.content;
            this.existingCommentId = response.data.data.id;
          }
        } catch (error) {
          console.error("Error fetching comment:", error);
        }
      }
      
      this.currentFilm = film;
      this.showRatingModal = true;
      this.currentRating = this.ifRated ? film.evaluation || 0 : 0;
    },
    closeRatingModal() {
      this.showRatingModal = false;
      this.currentFilm = null;
      this.currentRating = 0;
      this.currentComment = "";
      this.existingCommentId = null;
    },
    handleHover(n, event) {
      const star = event.target;
      const rect = star.getBoundingClientRect();
      const posX = event.clientX - rect.left;
      this.hoverRating = posX < rect.width / 2 ? n - 0.5 : n;
    },
    setRating(n) {
      this.currentRating = this.currentRating === n ? n - 0.5 : n;
    },
    async submitRating() {
      const token = this.stateAuth.token;
      const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      };
      
      this.currentRating = this.currentRating ? this.currentRating : 0;
      
      try {
        if (this.ifRated) {
          // Update existing rating and comment
          const data = {
            evaluation: this.currentRating,
            content: this.currentComment || null
          };
          const url = `${BASE_URL}/favourites/${this.existingCommentId || this.stateAuth.id}/${this.selectedEvaulatedFilm.id}`;
          await axios.patch(url, data, { headers });
        } else {
          // Create new rating with optional comment
          const data = {
            userId: this.stateAuth.id,
            filmId: this.selectedEvaulatedFilm.id,
            evaluation: this.currentRating,
            content: this.currentComment || null,
          };
          const url = `${BASE_URL}/favouriteFilmByUser`;
          await axios.post(url, data, { headers });
        }

        await this.fetchFilmsFromBackend();
        this.closeRatingModal();
      } catch (error) {
        console.error("Error submitting rating:", error);
      }
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 900px;
  margin: auto;
  padding: 20px;
  text-align: center;
}

.search-sort-container {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.submit-button {
  background: #4caf50;
  color: white;
}

.cancel-button {
  background: #f44336;
  color: white;
}

.search-input {
  padding: 8px;
  width: 50%;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.sort-select {
  padding: 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.films-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  justify-content: center;
}

.film-card {
  background: #1e1e1e;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  padding: 15px;
  text-align: center;
  transition: transform 0.2s ease-in-out;
  color: white;
  position: relative;
}

.film-card-rated {
  background: #313131;
}

.film-card:hover {
  transform: scale(1.05);
}

.film-title {
  font-size: 1.4em;
  margin-bottom: 10px;
  color: #f5c518;
}

.film-info {
  font-size: 1em;
  margin: 5px 0;
}

.imdb-link {
  display: inline-block;
  margin-top: 10px;
  padding: 5px 10px;
  background: #f5c518;
  color: black;
  text-decoration: none;
  font-weight: bold;
  border-radius: 5px;
}

.imdb-link:hover {
  background: #e0b211;
}

.admin-actions {
  margin-bottom: 20px;
}

.add-film-button {
  padding: 10px 20px;
  background: #4caf50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
}

.add-film-button:hover {
  background: #45a049;
}

.film-actions {
  margin-top: 15px;
}

.edit-button,
.delete-button {
  padding: 5px 10px;
  margin: 0 5px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9em;
}

.edit-button {
  background: #2196f3;
  color: white;
}

.edit-button:hover {
  background: #1e88e5;
}

.delete-button {
  background: #f44336;
  color: white;
}

.delete-button:hover {
  background: #e53935;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #1e1e1e;
  padding: 20px;
  border-radius: 10px;
  width: 300px;
  color: white;
}

.modal-content form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.modal-content input {
  padding: 5px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.modal-content button {
  padding: 5px 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.star-icon {
  position: absolute;
  bottom: 10px;
  right: 25px;
  font-size: 1.5em;
  color: #f5c518;
  cursor: pointer;
}

.rating-stars {
  font-size: 2em;
  margin: 20px 0;
  display: flex;
  justify-content: center;
  gap: 5px;
}

.rating-stars span {
  cursor: pointer;
  color: #666;
  position: relative;
}

.rating-stars .star-filled {
  color: #f5c518;
}

.rating-stars .star-half::before {
  content: "★";
  position: absolute;
  left: 0;
  width: 50%;
  overflow: hidden;
  color: #f5c518;
}

.rating-actions {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 20px;
}

.rating-actions button {
  padding: 5px 15px;
}

.comment-section {
  margin: 15px 0;
}

.comment-section label {
  display: block;
  margin-bottom: 5px;
}

.comment-section textarea {
  width: 100%;
  padding: 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
  background: #2d2d2d;
  color: white;
}
</style>