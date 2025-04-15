<template>
  <div class="container">
    <!-- Oldal fejléc -->
    <h1>Films</h1>
    <p v-if="debug">{{ stateAuth }}</p>
    <!-- Debug módban megjeleníti az auth állapotot -->

    <!-- Keresés és rendezés -->
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

    <!-- Admin gombok -->
    <div v-if="isAdmin" class="admin-actions">
      <button @click="openAddFilmModal" class="add-film-button">
        Add New Film
      </button>
    </div>

    <!-- Filmkártyák kezdete -->
    <div v-if="films.length" class="films-grid">
      <!-- Minden film egy kártya -->
      <div
        v-for="film in filteredFilms"
        :key="film.id"
        class="film-card"
        :class="{ 'film-card-rated': rated(film, stateAuth.id) }"
        @click="openFilmDetail(film)"
      >
        <!-- Csillag ikon értékeléshez -->
        <span class="star-icon" @click.stop="openRatingModal(film)">★</span>

        <!-- Film adatok -->
        <h2 class="film-title">{{ film.title }}</h2>
        <p class="film-info">
          <strong>Production Year:</strong> {{ film.production }}
        </p>
        <p class="film-info"><strong>Length:</strong> {{ film.length }} min</p>
        <p class="film-info">
          <strong>Presentation:</strong> {{ formatDate(film.presentation) }}
        </p>
        <p class="film-info">
          <strong>Evaluation:</strong> {{ film.evaluation || "Not rated yet." }}
        </p>

        <!-- IMDb link -->
        <a
          v-if="film.imdbLink"
          :href="formatImdbUrl(film.imdbLink)"
          target="_blank"
          rel="noopener noreferrer"
          class="imdb-link"
          >View on IMDb</a
        >

        <!-- Admin műveletek (szerkesztés/törlés) -->
        <div v-if="isAdmin" class="film-actions">
          <button @click.stop="openEditFilmModal(film)" class="edit-button">
            Edit
          </button>
          <button @click.stop="deleteFilm(film.id)" class="delete-button">
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Film részletező modal -->
    <div
      v-if="selectedFilm"
      class="modal-dialog film-detail-modal modal-dialog-scrollable"
    >
      <div class="modal-content">
        <span class="close" @click="closeFilmDetail">&times;</span>
        <h2 class="film-title2">{{ selectedFilm.title }}</h2>
        <div class="film-details">
          <p><strong>Production Year:</strong> {{ selectedFilm.production }}</p>
          <p><strong>Length:</strong> {{ selectedFilm.length }} minutes</p>
          <p>
            <strong>Presentation Date:</strong>
            {{ formatDate(selectedFilm.presentation) }}
          </p>
          <p>
            <strong>Evaluation:</strong>
            {{ selectedFilm.evaluation || "Not rated yet" }}
          </p>

          <!-- Szereplők és stáb -->
          <div class="d-flex">
            <h2 class="cast-crew-title text-nowrap">Cast & Crew:</h2>
            <button
              type="button"
              v-if="isAdmin && stateCastCrew != 'new'"
              class="ms-2 btn btn-outline-success"
              @click="onClickNewPerson()"
            >
              <i class="bi bi-person-fill-add"></i>
            </button>

            <button
              type="button"
              v-if="isAdmin && stateCastCrew == 'new'"
              class="ms-2 btn btn-outline-primary"
              @click="onClickSavePerson()"
            >
              <i class="bi bi-floppy-fill"></i>
            </button>

            <!-- People list -->
            <select
              v-if="stateCastCrew == 'new'"
              class="form-select"
              aria-label="Default select example"
              v-model="currentTask.personId"
            >
              <option
                v-for="person in peopleAZ"
                :key="person.id"
                :value="person.id"
              >
                {{ person.name }}
              </option>
            </select>
            <!-- Roles list -->
            <select
              v-if="stateCastCrew == 'new'"
              class="form-select"
              aria-label="Default select example"
              v-model="currentTask.roleId"
            >
              <option v-for="role in rolesAZ" :key="role.id" :value="role.id">
                {{ role.role }}
              </option>
            </select>
          </div>
          <div class="cast-crew" v-if="filmPeopleRoles.length">
            <div v-for="(item, index) in filmPeopleRoles" :key="index">
              <button
                type="button"
                v-if="isAdmin"
                class="me-2 btn btn-outline-danger"
                @click="deletePerson(item.id)"
              >
                <i class="bi bi-person-x-fill"></i>
              </button>
              {{ item.name }} - {{ item.role }}
            </div>
          </div>
          <!-- Admin/ Cast&Crew szerkesztő -->

          <a
            v-if="selectedFilm.imdbLink"
            :href="formatImdbUrl(selectedFilm.imdbLink)"
            target="_blank"
            class="imdb-link"
            >View on IMDb</a
          >
        </div>
      </div>
    </div>

    <!-- Film hozzáadása modal -->
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

    <!-- Film szerkesztése modal -->
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

    <!-- Értékelés modal -->
    <div v-if="showRatingModal" class="modal">
      <div class="modal-content">
        <h2 class="film-title">Rate: {{ currentFilm.title }}</h2>
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
            >★</span
          >
        </div>
        <div class="comment-section">
          <label>Comment (optional):</label>
          <textarea v-model="currentComment" rows="4"></textarea>
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
class Task {
  constructor(id = null, filmId = null, personId = null, roleId = null) {
    this.id = id;
    this.filmId = filmId;
    this.personId = personId;
    this.roleId = roleId;
  }
}
import axios from "axios";
import { BASE_URL } from "../helpers/baseUrls";
import { DEBUG } from "../helpers/baseUrls";
import { useAuthStore } from "@/stores/useAuthStore";

export default {
  data() {
    return {
      debug: DEBUG, // Debug mód beállítása
      urlApi: `${BASE_URL}/films`, // API endpoint
      stateAuth: useAuthStore(), // Pinia auth store
      films: [], // Film lista
      rolesAZ: [], // Szerepkörök listája
      peopleAZ: [], // Személyek listája
      filmPeopleRoles: [], // Filmhez tartozó szerepkörök és személyek
      isAdmin: false, // Admin jogosultság
      searchQuery: "", // Keresés szövege
      sortOption: "abc", // Rendezési opció
      filteredFilms: [], // Szűrt filmek
      showAddFilmModal: false, // Film hozzáadása modal állapota
      showEditFilmModal: false, // Film szerkesztése modal állapota
      showRatingModal: false, // Értékelés modal állapota
      newFilm: {
        // Új film adatai
        title: "",
        production: "",
        length: "",
        presentation: "",
        imdbLink: "",
      },
      currentTask: new Task(),
      editingFilm: null, // Szerkesztett film
      currentFilm: null, // Aktuálisan kiválasztott film
      currentRating: 0, // Aktuális értékelés
      hoverRating: null, // Egérrel érintett csillag
      currentComment: "", // Komment szövege
      existingCommentId: null, // Létező komment ID
      selectedFilm: null, // Nagyított film
      stateCastCrew: "read",
      
    };
  },

  // Komponens betöltésekor lefutó metódusok
  async mounted() {
    await this.fetchFilmsFromBackend(); // Filmek betöltése
    await this.getrolesAZ(); // Szerepkörök betöltése
    await this.getpeopleAZ(); // Személyek betöltése
    this.isAdmin = this.stateAuth.positionId === 1; // Admin jog ellenőrzése
  },

  // Figyelő metódusok (adatok változására reagál)
  watch: {
    searchQuery() {
      this.searchFilms();
    }, // Keresés változás
    sortOption() {
      this.sortFilms();
    }, // Rendezés változás
  },

  methods: {
    // Filmek betöltése API-ról
    async fetchFilmsFromBackend() {
      try {
        const response = await axios.get(
          `${BASE_URL}/queryFilmsWithEvaluation`
        );
        this.films = Array.isArray(response.data.data)
          ? response.data.data
          : [];
        this.filteredFilms = [...this.films];
      } catch (error) {
        console.error("Error loading films:", error);
      }
    },

    // Szerepkörök betöltése
    async getrolesAZ() {
      try {
        const response = await axios.get(`${BASE_URL}/rolesAZ`);
        this.rolesAZ = Array.isArray(response.data.data)
          ? response.data.data
          : [];
      } catch (error) {
        console.error("Error loading roles:", error);
      }
    },

    // Személyek betöltése
    async getpeopleAZ() {
      try {
        const response = await axios.get(`${BASE_URL}/peopleAZ`);
        this.peopleAZ = Array.isArray(response.data.data)
          ? response.data.data
          : [];
      } catch (error) {
        console.error("Error loading people:", error);
      }
    },

    // Filmhez tartozó szerepkörök és személyek betöltése
    async fetchFilmPeopleRoles(filmId) {
      try {
        const response = await axios.get(
          `${BASE_URL}/filmPeopleRoles/${filmId}`
        );
        this.filmPeopleRoles = Array.isArray(response.data.data)
          ? response.data.data
          : [];
        console.log("Valami", this.filmPeopleRoles);
      } catch (error) {
        console.error("Error loading film people:", error);
      }
    },

    // Dátum formázása
    formatDate(dateString) {
      if (!dateString) return "Unknown";
      return new Date(dateString).toISOString().split("T")[0];
    },

    // IMDb URL formázása
    formatImdbUrl(imdbLink) {
      if (!imdbLink) return "#";
      return imdbLink.startsWith("http")
        ? imdbLink
        : `https://www.imdb.com/title/${imdbLink}/`;
    },

    // Filmek keresése
    searchFilms() {
      const query = this.searchQuery.toLowerCase();
      this.filteredFilms = this.films.filter(
        (film) =>
          film.title.toLowerCase().includes(query) ||
          String(film.production).includes(query) ||
          String(film.length).includes(query) ||
          (film.evaluation && String(film.evaluation).includes(query))
      );
      this.sortFilms(); // Újrarendezés keresés után
    },

    // Filmek rendezése
    sortFilms() {
      if (this.sortOption === "abc") {
        this.filteredFilms.sort((a, b) => a.title.localeCompare(b.title));
      } else if (this.sortOption === "production") {
        this.filteredFilms.sort((a, b) => a.production - b.production);
      } else if (this.sortOption === "length") {
        this.filteredFilms.sort((a, b) => a.length - b.length);
      } else if (this.sortOption === "evaluation") {
        this.filteredFilms.sort(
          (a, b) => (b.evaluation || 0) - (a.evaluation || 0)
        );
      }
    },

    // Film részletek megnyitása
    async openFilmDetail(film) {
      this.selectedFilm = film;
      await this.fetchFilmPeopleRoles(film.id);
      this.stateCastCrew = "read";
    },

    // Film részletek bezárása
    closeFilmDetail() {
      this.selectedFilm = null;
      this.filmPeopleRoles = [];
    },

    // Új film modal megnyitása
    openAddFilmModal() {
      this.showAddFilmModal = true;
    },

    // Új film modal bezárása
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

    // Új film mentése
    async submitNewFilm() {
      try {
        await axios.post(BASE_URL, this.newFilm, {
          headers: { 
            Accept: application/json,
            Authorization: `Bearer ${this.stateAuth.token}` },
        });
        await this.fetchFilmsFromBackend();
        this.closeAddFilmModal();
      } catch (error) {
        console.error("Error adding film:", error);
      }
    },

    // Film szerkesztése modal megnyitása
    openEditFilmModal(film) {
      this.editingFilm = { ...film };
      this.showEditFilmModal = true;
    },

    // Film szerkesztése modal bezárása
    closeEditFilmModal() {
      this.showEditFilmModal = false;
      this.editingFilm = null;
    },

    onClickNewPerson() {
      this.stateCastCrew = "new";
    },

    onClickSavePerson() {
      this.stateCastCrew = "read";
    },

    // Film mentése szerkesztés után
    async submitEditedFilm() {
      try {
        await axios.patch(
          `${BASE_URL}/${this.editingFilm.id}`,
          this.editingFilm,
          { headers: { Authorization: `Bearer ${this.stateAuth.token}` } }
        );
        await this.fetchFilmsFromBackend();
        this.closeEditFilmModal();
      } catch (error) {
        console.error("Error editing film:", error);
      }
    },

    // Film törlése
    async deleteFilm(filmId) {
      if (confirm("Are you sure?")) {
        try {
          await axios.delete(`${BASE_URL}/${filmId}`, {
            headers: { Authorization: `Bearer ${this.stateAuth.token}` },
          });
          await this.fetchFilmsFromBackend();
        } catch (error) {
          console.error("Error deleting film:", error);
        }
      }
    },

    async deletePerson(id) {
      if (confirm("Are you sure?")) {
        this.stateCastCrew = "delete";
        try {
          await axios.delete(`${BASE_URL}/tasks/${id}`, {
            headers: { Authorization: `Bearer ${this.stateAuth.token}` },
          });
          await this.fetchFilmPeopleRoles(this.selectedFilm.id);
        } catch (error) {
          console.error("Error deleting film:", error);
        }
      }
    },

    // Ellenőrzi, hogy a felhasználó értékelte-e a filmet
    rated(film, userId) {
      return film.usersId?.split(",").includes(userId.toString());
    },

    // Értékelés modal megnyitása
    async openRatingModal(film) {
      this.currentFilm = film;
      this.showRatingModal = true;
      this.currentRating = this.rated(film, this.stateAuth.id)
        ? film.evaluation
        : 0;

      // Meglévő komment betöltése (ha van)
      if (this.rated(film, this.stateAuth.id)) {
        try {
          const response = await axios.get(
            `${BASE_URL}/favourites/${this.stateAuth.id}/${film.id}`
          );
          if (response.data.data?.content) {
            this.currentComment = response.data.data.content;
            this.existingCommentId = response.data.data.id;
          }
        } catch (error) {
          console.error("Error loading comment:", error);
        }
      }
    },

    // Értékelés modal bezárása
    closeRatingModal() {
      this.showRatingModal = false;
      this.currentFilm = null;
      this.currentRating = 0;
      this.currentComment = "";
    },

    // Csillag értékelés hover effekt
    handleHover(n, event) {
      const star = event.target;
      const rect = star.getBoundingClientRect();
      const posX = event.clientX - rect.left;
      this.hoverRating = posX < rect.width / 2 ? n - 0.5 : n;
    },

    // Értékelés beállítása
    setRating(n) {
      this.currentRating = this.currentRating === n ? n - 0.5 : n;
    },

    // Értékelés elküldése
    async submitRating() {
      try {
        const ratingData = {
          userId: this.stateAuth.id,
          filmId: this.currentFilm.id,
          evaluation: this.currentRating,
          content: this.currentComment || null,
        };

        if (this.rated(this.currentFilm, this.stateAuth.id)) {
          await axios.patch(
            `${BASE_URL}/favourites/${
              this.existingCommentId || this.stateAuth.id
            }/${this.currentFilm.id}`,
            ratingData,
            { headers: { Authorization: `Bearer ${this.stateAuth.token}` } }
          );
        } else {
          await axios.post(`${BASE_URL}/favouriteFilmByUser`, ratingData, {
            headers: { Authorization: `Bearer ${this.stateAuth.token}` },
          });
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

.film-title2 {
  font-size: 1.8em;
  margin-bottom: 10px;
  color: #f5c518;
}

.cast-crew {
  height: 350px;
  overflow-y: auto;
  margin-top: 20px;
  padding: 15px;
  background: #2d2d2d;
  border-radius: 8px;
}

.cast-crew-title {
  color: #f5c518;
  margin-bottom: 10px;
  font-size: 1.2em;
}

.cast-crew div {
  margin: 5px 0;
  padding: 5px;
  border-bottom: 1px solid #3d3d3d;
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
  position: relative;
  cursor: pointer;
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

.film-detail-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.film-detail-modal .modal-content {
  background: #2a2a2a;
  padding: 30px;
  border-radius: 15px;
  width: 80%;
  max-width: 600px;
  position: relative;
  color: white;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
}

.film-detail-modal .close {
  position: absolute;
  top: 15px;
  right: 25px;
  color: #aaa;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}

.film-detail-modal .close:hover {
  color: white;
}

.film-details {
  text-align: left;
  line-height: 1.6;
}

.film-details p {
  margin: 10px 0;
}
</style>