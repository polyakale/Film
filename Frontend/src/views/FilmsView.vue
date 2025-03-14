<template>
  <div class="container">
    <h1>Films</h1>

    <div v-if="films.length" class="films-grid">
      <div v-for="film in films" :key="film.id" class="film-card">
        
        <h2 class="film-title">{{ film.title }}</h2>

        <p class="film-info"><strong>Production Year:</strong> {{ film.production }}</p>

        <p class="film-info"><strong>Length:</strong> {{ film.length }} min</p>

        <p class="film-info"><strong>Presentation:</strong> {{ formatDate(film.presentation) }}</p>

        <a v-if="film.imdbLink" :href="formatImdbUrl(film.imdbLink)" target="_blank" rel="noopener noreferrer" class="imdb-link">
          View on IMDb
        </a>
      </div>
    </div>

    <p v-else>Loading films...</p>
  </div>
</template>

<script>
import axios from "axios";
import { BASE_URL } from "../helpers/baseUrls";

export default {
  data() {
    return {
      films: [] 
    };
  },
  async mounted() {
    await this.fetchFilmsFromBackend();
  },
  methods: {
    async fetchFilmsFromBackend() {
      try {
        const response = await axios.get(`${BASE_URL}/films`);

        console.log("Backend response:", response.data);

        this.films = Array.isArray(response.data.data) ? response.data.data : [];
        console.log("filmek:", this.films)

      } catch (error) {
        console.error("Error fetching films from backend:", error);
        this.films = []; 
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
    }
  }
};
</script>

<style scoped>
.container {
  max-width: 900px;
  margin: auto;
  padding: 20px;
  text-align: center;
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
</style>
