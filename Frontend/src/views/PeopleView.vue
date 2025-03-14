<template>
  <div class="container">
    <h1>People</h1>
    
    <div v-if="people.length" class="people-grid">
      <div v-for="person in people" :key="person.peopleName" class="person-card">
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
      </div>
    </div>

    <p v-else>Loading...</p>
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
      return photo ? `/Images/${photo}` : "/Images/default.jpg"; // Ha nincs kép, akkor alapértelmezett
    },
  },
};
</script>

<style scoped>
/* Általános konténer */
.container {
  max-width: 1200px;
  margin: auto;
  padding: 20px;
  text-align: center;
}

/* Kártyás grid */
.people-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  justify-content: center;
}

/* Kártya stílus */
.person-card {
  background: rgba(10, 10, 10, 0.2); /* Áttetsző fekete háttér */
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
  padding: 15px;
  text-align: center;
  transition: transform 0.2s ease-in-out;
  backdrop-filter: blur(10px); /* Enyhén elmosódott hatás */
}

.person-card:hover {
  transform: scale(1.05);
}


/* Kép stílus */
.person-image {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 8px;
}

/* Név */
.person-name {
  font-size: 1.4em;
  margin-top: 10px;
  color: #fff;
}

/* Alternatív nevek lista */
.name-list {
  list-style: none;
  padding: 0;
  font-size: 0.9em;
  color: #bbb;
}

/* IMDb link */
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
</style>
