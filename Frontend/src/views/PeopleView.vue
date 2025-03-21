<template>
  <div class="container">
    <h1>People</h1>

    <!-- Kereső -->
    <div class="search-container">
      <input
        type="text"
        v-model="searchQuery"
        @input="searchPeople"
        placeholder="Search people..."
        class="search-input"
      />
    </div>

    <!-- Admin gombok -->
    <div v-if="isAdmin" class="admin-actions">
      <button @click="openAddPersonModal" class="add-film-button">
        Add New Person
      </button>
    </div>

    <div v-if="people.length" class="people-grid">
      <div v-for="person in filteredPeople" :key="person.peopleName" class="person-card">
        <!-- Kép -->
        <img :src="getImageUrl(person.photo)" alt="Image" class="person-image" />
        <!-- Név -->
        <h2 class="person-name">{{ person.peopleName }}</h2>
        <!-- Alternatív nevek -->
        <ul class="name-list">
          <li v-for="(name, index) in person.names" :key="index">
            {{ name }}
          </li>
        </ul>

        <!-- IMDb link -->
        <div v-if="person.imdbLink">
          <a :href="person.imdbLink" target="_blank" rel="noopener noreferrer" class="imdb-link">
            {{ person.name }}
          </a>
        </div>

        <!-- Admin műveletek -->
        <div v-if="isAdmin" class="film-actions">
          <button @click="openEditPersonModal(person)" class="edit-button">
            Edit
          </button>
          <button @click="deletePerson(person.id)" class="delete-button">
            Delete
          </button>
        </div>
      </div>
    </div>

    <p v-else>Loading...</p>

    <!-- Új személy hozzáadása modal -->
    <div v-if="showAddPersonModal" class="modal">
      <div class="modal-content">
        <h2>Add New Person</h2>
        <form @submit.prevent="submitNewPerson">
          <label>Name:</label>
          <input v-model="newPerson.name" required />

          <label>Photo URL (optional):</label>
          <input v-model="newPerson.photo" />

          <label>IMDb Link:</label>
          <input v-model="newPerson.imdbLink" />

          <!-- Nem kiválasztása -->
          <label>Gender:</label>
          <select v-model="newPerson.gender" required>
            <option value="">Select gender</option>
            <option value="true">Male</option>
            <option value="false">Female</option>
          </select>

          <button type="submit">Save</button>
          <button type="button" @click="closeAddPersonModal">Cancel</button>
        </form>
      </div>
    </div>

    <!-- Személy szerkesztése modal -->
    <div v-if="showEditPersonModal" class="modal">
      <div class="modal-content">
        <h2>Edit Person</h2>
        <form @submit.prevent="submitEditedPerson">
          <label>Name:</label>
          <input v-model="editingPerson.name" required />

          <label>Photo URL (optional):</label>
          <input v-model="editingPerson.photo" />

          <label>IMDb Link:</label>
          <input v-model="editingPerson.imdbLink" />

          <!-- Nem kiválasztása -->
          <label>Gender:</label>
          <select v-model="editingPerson.gender" required>
            <option value="">Select gender</option>
            <option value="true">Male</option>
            <option value="false">Female</option>
          </select>

          <button type="submit">Save</button>
          <button type="button" @click="closeEditPersonModal">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { BASE_URL } from "../helpers/baseUrls";
import axios from "axios";
import { useAuthStore } from "@/stores/useAuthStore";

export default {
  data() {
    return {
      urlApi: `${BASE_URL}/people`,
      stateAuth: useAuthStore(),
      people: [],
      isAdmin: true,
      searchQuery: "",
      filteredPeople: [],
      showAddPersonModal: false,
      showEditPersonModal: false,
      newPerson: {
        name: "",
        photo: "",
        imdbLink: "",
        gender: "",
      },
      editingPerson: null,
    };
  },
  async mounted() {
    await this.fetchPeopleFromBackend();
    this.filteredPeople = this.people;
    this.isAdmin = this.stateAuth.positionId == 1 ? true : false;
  },
  watch: {
    searchQuery() {
      this.searchPeople();
    },
  },
  methods: {
    async fetchPeopleFromBackend() {
      try {
        const response = await axios.get(`${BASE_URL}/people`);
        this.people = Array.isArray(response.data.data)
          ? response.data.data
          : [];
        this.filteredPeople = this.people;
      } catch (error) {
        console.error("Error:", error);
      }
    },
    getImageUrl(photo) {
      return photo ? `/Images/${photo}` : "/Images/default.jpg";
    },
    searchPeople() {
      const query = this.searchQuery.toLowerCase();
      this.filteredPeople = this.people.filter(
        (person) =>
          person.name.toLowerCase().includes(query) 
      );
    },
    openAddPersonModal() {
      this.showAddPersonModal = true;
    },
    closeAddPersonModal() {
      this.showAddPersonModal = false;
      this.newPerson = {
        name: "",
        photo: "",
        imdbLink: "",
        gender: "",
      };
    },
    async submitNewPerson() {
      try {
        const token = this.stateAuth.token;
        const headers = {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        };
        const data = {
          name: this.newPerson.name,
          photo: this.newPerson.photo || null,
          imdbLink: this.newPerson.imdbLink,
          gender: this.newPerson.gender === "true",
        };
        const response = await axios.post(this.urlApi, data, { headers });
        this.fetchPeopleFromBackend();
        this.filteredPeople = this.people;
        this.closeAddPersonModal();
      } catch (error) {
        console.error("Error adding new person:", error.response?.data || error.message);
      }
    },
    openEditPersonModal(person) {
      this.editingPerson = { ...person };
      this.showEditPersonModal = true;
    },
    closeEditPersonModal() {
      this.showEditPersonModal = false;
      this.editingPerson = null;
    },
    async submitEditedPerson() {
      try {
        const token = this.stateAuth.token;
        const headers = {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        };
        const data = {
          name: this.editingPerson.name,
          photo: this.editingPerson.photo || null,
          imdbLink: this.editingPerson.imdbLink,
          gender: this.editingPerson.gender,
        };
        const response = await axios.patch(
          `${this.urlApi}/${this.editingPerson.id}`,
          data,
          { headers }
        );
        const index = this.people.findIndex(
          (person) => person.id === this.editingPerson.id
        );
        this.people.splice(index, 1, response.data.data);
        this.filteredPeople = this.people;
        this.closeEditPersonModal();
      } catch (error) {
        console.error("Error editing person:", error);
      }
    },
    async deletePerson(personId) {
      if (confirm("Are you sure you want to delete this person?")) {
        try {
          const token = this.stateAuth.token;
          const headers = {
            Accept: "application/json",
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          };
          await axios.delete(`${this.urlApi}/${personId}`, { headers });
          this.fetchPeopleFromBackend();
          this.filteredPeople = this.people;
        } catch (error) {
          console.error("Error deleting person:", error);
        }
      }
    },
  },
};
</script>

<style scoped>
/* Az eredeti stílusok maradnak változatlanok */
.container {
  max-width: 1200px;
  margin: auto;
  padding: 20px;
  text-align: center;
}

.search-container {
  margin-bottom: 20px;
}

.search-input {
  padding: 8px;
  width: 50%;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.people-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
  justify-content: center;
}

.person-card {
  background: rgba(10, 10, 10, 0.2);
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
  padding: 15px;
  text-align: center;
  transition: transform 0.2s ease-in-out;
  backdrop-filter: blur(10px);
}

.person-card:hover {
  transform: scale(1.05);
}

.person-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
}

.person-name {
  font-size: 1.4em;
  margin-top: 10px;
  color: #fff;
}

.name-list {
  list-style: none;
  padding: 0;
  font-size: 0.9em;
  color: #bbb;
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