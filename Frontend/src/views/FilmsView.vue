<template>
  <div class="container">
    <h1>Films</h1>

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
      <div v-for="film in filteredFilms" :key="film.id" class="film-card">
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
  </div>
</template>

<script>
import axios from "axios";
import { BASE_URL } from "../helpers/baseUrls";
import { useAuthStore } from "@/stores/useAuthStore";

export default {
  data() {
    return {
      urlApi: `${BASE_URL}/films`,
      stateAuth: useAuthStore(),
      films: [],
      isAdmin: false,
      searchQuery: "",
      sortOption: "abc",
      filteredFilms: [],
      showAddFilmModal: false,
      showEditFilmModal: false,
      newFilm: {
        title: "",
        production: "",
        length: "",
        presentation: "",
        imdbLink: "",
        evaluation: null,
      },
      editingFilm: null,
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
        // 1. Lekérjük a filmeket
        const filmsResponse = await axios.get(
          `${BASE_URL}/queryFilmsWithEvaluation`
        );
        this.films = Array.isArray(filmsResponse.data.data)
          ? filmsResponse.data.data
          : [];

        this.filteredFilms = [...this.films];
        console.log("Filmek", this.filteredFilms);
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
        evaluation: null,
      };
    },

    async submitNewFilm() {
      try {
        const token = this.stateAuth.token;
        const headers = {
          Authorization: `Bearer ${token}`,
        };

        // 1. Film létrehozása
        const filmData = {
          title: this.newFilm.title,
          production: this.newFilm.production,
          length: this.newFilm.length,
          presentation: this.newFilm.presentation,
          imdbLink: this.newFilm.imdbLink,
        };

        const filmResponse = await axios.post(this.urlApi, filmData, {
          headers,
        });

        // 2. Értékelés hozzáadása (ha van)
        if (this.newFilm.evaluation) {
          await axios.post(
            `${BASE_URL}/films/${filmResponse.data.data.id}/evaluation`,
            { value: this.newFilm.evaluation },
            { headers }
          );
        }

        // 3. Frissítjük a listát
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

        // 1. Film adatainak frissítése
        const filmData = {
          title: this.editingFilm.title,
          production: this.editingFilm.production,
          length: this.editingFilm.length,
          presentation: this.editingFilm.presentation,
          imdbLink: this.editingFilm.imdbLink,
        };

        const url = `${this.urlApi}/${this.editingFilm.id}`;
        await axios.patch(url, filmData, {headers});

        // // 2. Értékelés frissítése
        // await axios.patch(
        //   `${BASE_URL}/films/${this.editingFilm.id}/evaluation`,
        //   { value: this.editingFilm.evaluation || null },
        //   { headers }
        // );

        // 3. Frissítjük a listát
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

          const url = `${this.urlApi}/${filmId}`;
          // 1. Film törlése
          console.log("törlés", url, headers);

          await axios.delete(url, { headers });

          // 3. Frissítjük a listát
          await this.fetchFilmsFromBackend();
        } catch (error) {
          console.error("Error deleting film:", error);
        }
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
</style>