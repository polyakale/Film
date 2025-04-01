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

    <div v-if="films.length" class="films-grid">
      <div 
        v-for="film in filteredFilms" 
        :key="film.id" 
        class="film-card"
        @click="openFilmDetails(film)"
      >
        <h2 class="film-title">{{ film.title }}</h2>
        <p class="film-info">
          <strong>Production Year:</strong> {{ film.production }}
        </p>
        <p class="film-info"><strong>Length:</strong> {{ film.length }} min</p>
        <p class="film-info">
          <strong>Presentation:</strong> {{ formatDate(film.presentation) }}
        </p>
        <p class="film-info">
          <strong>Evaluation:</strong> {{ film.evaluation || "Not rated yet" }}
        </p>
      </div>
    </div>

    <p v-else>Loading films...</p>

    <!-- Film Details Modal -->
    <div v-if="showFilmDetailsModal" class="modal">
      <div class="modal-content">
        <h2>{{ selectedFilm.title }}</h2>
        <div class="film-details">
          <p><strong>Production Year:</strong> {{ selectedFilm.production }}</p>
          <p><strong>Length:</strong> {{ selectedFilm.length }} min</p>
          <p><strong>Presentation:</strong> {{ formatDate(selectedFilm.presentation) }}</p>
          <p><strong>Evaluation:</strong> {{ selectedFilm.evaluation || "Not rated yet" }}</p>
          
          <div v-if="selectedFilm.imdbLink" class="imdb-container">
            <a
              :href="formatImdbUrl(selectedFilm.imdbLink)"
              target="_blank"
              rel="noopener noreferrer"
              class="imdb-link"
            >
              View on IMDb
            </a>
          </div>

          <div class="film-roles">
            <h3>Cast & Crew</h3>
            <div v-for="role in filmRoles" :key="role.id" class="role-item">
              <p><strong>{{ role.role_name }}:</strong> {{ role.person_name }}</p>
            </div>
          </div>
        </div>

        <div v-if="isAdmin" class="film-actions">
          <button @click="openEditFilmModal(selectedFilm)" class="edit-button">
            Edit Film
          </button>
          <button @click="deleteFilm(selectedFilm.id)" class="delete-button">
            Delete Film
          </button>
        </div>
        <button @click="closeFilmDetailsModal" class="close-button">Close</button>
      </div>
    </div>

    <!-- Add Film Modal -->
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

          <h3>Add Roles</h3>
          <div v-for="(role, index) in newFilm.roles" :key="index" class="role-input">
            <select v-model="role.role_name" required>
              <option value="">Select Role</option>
              <option v-for="role in availableRoles" :value="role">{{ role }}</option>
            </select>
            <input v-model="role.person_name" placeholder="Person name" required />
            <button type="button" @click="removeNewFilmRole(index)" class="remove-role-button">×</button>
          </div>
          <button type="button" @click="addNewFilmRole" class="add-role-button">Add Role</button>

          <div class="form-actions">
            <button type="submit">Save</button>
            <button type="button" @click="closeAddFilmModal">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Film Modal -->
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

          <h3>Edit Roles</h3>
          <div v-for="(role, index) in editingFilm.roles" :key="index" class="role-input">
            <select v-model="role.role_name" required>
              <option value="">Select Role</option>
              <option v-for="role in availableRoles" :value="role">{{ role }}</option>
            </select>
            <input v-model="role.person_name" placeholder="Person name" required />
            <button type="button" @click="removeEditingFilmRole(index)" class="remove-role-button">×</button>
          </div>
          <button type="button" @click="addEditingFilmRole" class="add-role-button">Add Role</button>

          <div class="form-actions">
            <button type="submit">Save</button>
            <button type="button" @click="closeEditFilmModal">Cancel</button>
          </div>
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
      showFilmDetailsModal: false,
      selectedFilm: null,
      filmRoles: [],
      availableRoles: ['Actor', 'Cameraman', 'Director', 'Screenwriter'],
      newFilm: {
        title: "",
        production: "",
        length: "",
        presentation: "",
        imdbLink: "",
        evaluation: null,
        roles: []
      },
      editingFilm: null,
    };
  },
  async mounted() {
    await this.fetchFilmsFromBackend();
    this.isAdmin = this.stateAuth.positionId === 1;
    await this.fetchAvailableRoles();
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
    async fetchAvailableRoles() {
      try {
        const token = this.stateAuth.token;
        const headers = {
          Authorization: `Bearer ${token}`,
        };
        const response = await axios.get(`${BASE_URL}/roles`, { headers });
        if (response.data?.data) {
          this.availableRoles = response.data.data.map(role => role.name);
        }
      } catch (error) {
        console.error("Error fetching role types:", error);
      }
    },

    async fetchFilmRoles(filmId) {
      try {
        const token = this.stateAuth.token;
        const headers = {
          Authorization: `Bearer ${token}`,
        };
        const response = await axios.get(`${BASE_URL}/films/${filmId}/roles`, { headers });
        return response.data?.data || [];
      } catch (error) {
        console.error("Error fetching film roles:", error);
        return [];
      }
    },

    async fetchFilmsFromBackend() {
      try {
        const response = await axios.get(`${BASE_URL}/films`);
        this.films = Array.isArray(response.data?.data) 
          ? response.data.data 
          : [];
        this.filteredFilms = [...this.films];
      } catch (error) {
        console.error("Error loading films:", error);
      }
    },

    async openFilmDetails(film) {
      this.selectedFilm = film;
      this.filmRoles = await this.fetchFilmRoles(film.id);
      this.showFilmDetailsModal = true;
    },

    closeFilmDetailsModal() {
      this.showFilmDetailsModal = false;
      this.selectedFilm = null;
      this.filmRoles = [];
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

    addNewFilmRole() {
      this.newFilm.roles.push({
        role_name: "",
        person_name: ""
      });
    },

    removeNewFilmRole(index) {
      this.newFilm.roles.splice(index, 1);
    },

    addEditingFilmRole() {
      this.editingFilm.roles.push({
        role_name: "",
        person_name: ""
      });
    },

    removeEditingFilmRole(index) {
      this.editingFilm.roles.splice(index, 1);
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
        roles: []
      };
    },

    async submitNewFilm() {
      try {
        const token = this.stateAuth.token;
        const headers = {
          Authorization: `Bearer ${token}`,
        };

        const filmResponse = await axios.post(
          this.urlApi,
          {
            title: this.newFilm.title,
            production: this.newFilm.production,
            length: this.newFilm.length,
            presentation: this.newFilm.presentation,
            imdbLink: this.newFilm.imdbLink
          },
          { headers }
        );

        if (this.newFilm.roles.length > 0) {
          await Promise.all(
            this.newFilm.roles.map(role => 
              axios.post(
                `${BASE_URL}/films/${filmResponse.data.data.id}/roles`,
                {
                  role_name: role.role_name,
                  person_name: role.person_name
                },
                { headers }
              )
            )
          );
        }

        await this.fetchFilmsFromBackend();
        this.closeAddFilmModal();
      } catch (error) {
        console.error("Error adding film:", error);
      }
    },

    async openEditFilmModal(film) {
      const roles = await this.fetchFilmRoles(film.id);
      this.editingFilm = { 
        ...film,
        roles: roles.map(role => ({
          role_name: role.role_name,
          person_name: role.person_name,
          id: role.id
        }))
      };
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
          Authorization: `Bearer ${token}`,
        };

        await axios.patch(
          `${this.urlApi}/${this.editingFilm.id}`,
          {
            title: this.editingFilm.title,
            production: this.editingFilm.production,
            length: this.editingFilm.length,
            presentation: this.editingFilm.presentation,
            imdbLink: this.editingFilm.imdbLink
          },
          { headers }
        );

        const existingRoles = await this.fetchFilmRoles(this.editingFilm.id);
        
        const rolesToDelete = existingRoles.filter(existingRole => 
          !this.editingFilm.roles.some(role => role.id === existingRole.id)
        );
        
        await Promise.all(
          rolesToDelete.map(role => 
            axios.delete(
              `${BASE_URL}/films/${this.editingFilm.id}/roles/${role.id}`,
              { headers }
            )
          )
        );

        await Promise.all(
          this.editingFilm.roles.map(role => {
            if (role.id) {
              return axios.patch(
                `${BASE_URL}/films/${this.editingFilm.id}/roles/${role.id}`,
                {
                  role_name: role.role_name,
                  person_name: role.person_name
                },
                { headers }
              );
            } else {
              return axios.post(
                `${BASE_URL}/films/${this.editingFilm.id}/roles`,
                {
                  role_name: role.role_name,
                  person_name: role.person_name
                },
                { headers }
              );
            }
          })
        );

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
            Authorization: `Bearer ${token}`,
          };

          await axios.delete(`${BASE_URL}/films/${filmId}`, { headers });
          await this.fetchFilmsFromBackend();
        } catch (error) {
          console.error("Error deleting film:", error);
        }
      }
    }
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
  cursor: pointer;
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
  display: flex;
  justify-content: center;
  gap: 10px;
}

.edit-button,
.delete-button,
.close-button {
  padding: 8px 15px;
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

.close-button {
  background: #666;
  color: white;
  margin-top: 15px;
}

.close-button:hover {
  background: #555;
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
  z-index: 1000;
}

.modal-content {
  background: #1e1e1e;
  padding: 20px;
  border-radius: 10px;
  width: 80%;
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
  color: white;
  position: relative;
}

.modal-content h2 {
  color: #f5c518;
  margin-bottom: 20px;
}

.film-details {
  text-align: left;
  margin-bottom: 20px;
}

.film-details p {
  margin: 8px 0;
}

.film-roles {
  margin-top: 20px;
}

.film-roles h3 {
  color: #f5c518;
  margin-bottom: 10px;
}

.role-item {
  margin-bottom: 8px;
  padding: 8px;
  background: #2a2a2a;
  border-radius: 5px;
}

.role-input {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
  align-items: center;
}

.role-input select,
.role-input input {
  flex: 1;
  padding: 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.remove-role-button {
  background: #f44336;
  color: white;
  border: none;
  border-radius: 50%;
  width: 25px;
  height: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 14px;
}

.remove-role-button:hover {
  background: #e53935;
}

.add-role-button {
  background: #4caf50;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  margin-bottom: 15px;
}

.add-role-button:hover {
  background: #45a049;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.form-actions button {
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.form-actions button[type="submit"] {
  background: #4caf50;
  color: white;
}

.form-actions button[type="submit"]:hover {
  background: #45a049;
}

.form-actions button[type="button"] {
  background: #666;
  color: white;
}

.form-actions button[type="button"]:hover {
  background: #555;
}
</style>