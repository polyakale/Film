<template>
  <div class="container">
    <h1>People</h1>

    <div class="search-container">
      <input
        type="text"
        v-model="searchQuery"
        @input="searchPeople"
        placeholder="Search people..."
        class="search-input"
      />
    </div>

    <div v-if="isAdmin" class="admin-actions">
      <button @click="openAddPersonModal" class="add-people-button">
        Add New Person
      </button>
    </div>

    <div v-if="people.length" class="people-grid">
      <div v-for="person in filteredPeople" :key="person.id" class="person-card">
        <img 
          :src="getImageUrl(person.photo)" 
          @error="handleImageError"
          alt="Person image" 
          class="person-image" 
        />
        
        <div v-if="!person.imdbLink" class="name-container no-imdb">
          {{ person.name }}
        </div>
        
        <div v-else>
          <a :href="person.imdbLink" target="_blank" rel="noopener noreferrer" class="imdb-link">
            {{ person.name }}
          </a>
        </div>
        
        <ul class="name-list">
          <li v-for="(name, index) in person.names" :key="index">
            {{ name }}
          </li>
        </ul>

        <div v-if="isAdmin" class="people-actions">
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

    <div v-if="showAddPersonModal" class="modal">
      <div class="modal-content">
        <h2>Add New Person</h2>
        <form @submit.prevent="submitNewPerson">
          <label>Name:</label>
          <input v-model="newPerson.name" required />

          <label>Photo URL (optional):</label>
          <input v-model="newPerson.photo" placeholder="Leave empty for default image" />

          <label>IMDb Link:</label>
          <input v-model="newPerson.imdbLink" />

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

    <div v-if="showEditPersonModal" class="modal">
      <div class="modal-content">
        <h2>Edit Person</h2>
        <form @submit.prevent="submitEditedPerson">
          <label>Name:</label>
          <input v-model="editingPerson.name" required />

          <label>Photo URL (optional):</label>
          <input v-model="editingPerson.photo" placeholder="Leave empty for default image" />

          <label>IMDb Link:</label>
          <input v-model="editingPerson.imdbLink" />

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
        this.people = Array.isArray(response.data.data) ? response.data.data : [];
        this.filteredPeople = this.people;
      } catch (error) {
        console.error("Error:", error);
      }
    },
    
    getImageUrl(photo) {
      return photo ? `/Images/${photo}` : "/Images/Missing.png";
    },
    
    handleImageError(event) {
      event.target.src = "/Images/Missing.png";
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
          photo: this.newPerson.photo.trim() === "" ? "Missing.png" : this.newPerson.photo,
          imdbLink: this.newPerson.imdbLink,
          gender: this.newPerson.gender === "true",
        };
        await axios.post(this.urlApi, data, { headers });
        this.fetchPeopleFromBackend();
        this.closeAddPersonModal();
      } catch (error) {
        console.error("Error adding new person:", error.response?.data || error.message);
      }
    },

    searchPeople() {
      const query = this.searchQuery.toLowerCase();
      this.filteredPeople = this.people.filter(person =>
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
          photo: this.editingPerson.photo.trim() === "" ? "Missing.png" : this.editingPerson.photo,
          imdbLink: this.editingPerson.imdbLink,
          gender: this.editingPerson.gender,
        };
        await axios.patch(
          `${this.urlApi}/${this.editingPerson.id}`,
          data,
          { headers }
        );
        this.fetchPeopleFromBackend();
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
        } catch (error) {
          console.error("Error deleting person:", error);
        }
      }
    },
  },
};
</script>

<style scoped>
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

.name-container {
  padding: 10px;
  margin-top: 10px;
  background: #666;
  color: white;
  border-radius: 5px;
  font-size: 1.2em;
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

.add-people-button {
  padding: 10px 20px;
  background: #4caf50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
}

.add-people-button:hover {
  background: #45a049;
}

.people-actions {
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
  z-index: 1000;
}

.modal-content {
  background: #1e1e1e;
  padding: 20px;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  color: white;
}

.modal-content form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.modal-content label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.modal-content input,
.modal-content select {
  width: 100%;
  padding: 8px;
  border-radius: 5px;
  border: 1px solid #444;
  background: #333;
  color: white;
}

.modal-content button {
  padding: 10px;
  margin-top: 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
}

.modal-content button[type="submit"] {
  background: #4caf50;
  color: white;
}

.modal-content button[type="button"] {
  background: #666;
  color: white;
}
</style>