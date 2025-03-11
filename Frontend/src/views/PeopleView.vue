<template>
  <div>
    <h1>People</h1>
    <div v-if="people.length">
      <div v-for="person in people" :key="person.Name" class="person-card" @click="showDetails(person)">
        <img :src="getImageUrl(person.photo)" alt="Image" class="person-image" />
        <a :href="person.imdbLink || '#'" target="_blank" rel="noopener noreferrer">
          <h2>{{ person.Name }}</h2>
        </a>
      </div>
    </div>
    <p v-else>Loading...</p>

    <div v-if="selectedPerson" class="modal">
      <div class="modal-content">
        <span class="close" @click="closeModal">&times;</span>
        <img :src="getImageUrl(selectedPerson.photo)" alt="Image" class="modal-image" />

        <a :href="selectedPerson.imdbLink || '#'" target="_blank" rel="noopener noreferrer">
          <h2>{{ selectedPerson.Name }}</h2>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import { BASE_URL } from "../helpers/baseUrls";
import axios from "axios";

export default {
  data() {
    return {
      urlApi: BASE_URL,
      people: [],
      selectedPerson: null, 
    };
  },
  async mounted() {
    await this.peopleNames();
  },
  methods: {
    async peopleNames() {
      try {
        const url = `${this.urlApi}/people`;
        const response = await axios.get(url);
        this.people = response.data?.data || [];
        console.log(this.people); 
      } catch (error) {
        console.error("Error:", error);
      }
    },
    getImageUrl(photo) {
      return photo ? `/Images/${photo}` : "/Images/default.jpg";
    },
    showDetails(person) {
      this.selectedPerson = person; 
    },
    closeModal() {
      this.selectedPerson = null; 
    },
  },
};
</script>

<style scoped>
.person-card {
  display: inline-block;
  width: 200px;
  margin: 10px;
  padding: 10px;
  text-align: center;
  background: rgba(255, 255, 255, 0.1); 
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  cursor: pointer; 
}

.person-image {
  width: 150px;   
  height: 150px; 
  border-radius: 8px;
  object-fit: cover; 
}

a {
  color: inherit;
  text-decoration: none;
}

a:hover h2 {
  text-decoration: underline;
}

h2 {
  margin-top: 10px;
  font-size: 1.2em;
}


.modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); 
  height: 250px;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  text-align: center;
  border-radius: 10px;
  position: relative;
}

.modal-image {
  width: 200px;  
  height: 200px; 
  object-fit: cover;
  border-radius: 8px;
}

.close {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 24px;
  color: black;
  cursor: pointer;
}
</style>
