<template>
  <div class="container">
    <h1>Films</h1>

    <div v-if="films.length" class="films-grid">
      <div v-for="film in films" :key="film.id" class="film-card">
        
        <!-- Film címe -->
        <h2 class="film-title">{{ film.title }}</h2>

        <!-- Gyártási év -->
        <p class="film-info"><strong>Production Year:</strong> {{ film.production }}</p>

        <!-- Hossz (percben) -->
        <p class="film-info"><strong>Length:</strong> {{ film.length }} min</p>

        <!-- Bemutató dátuma -->
        <p class="film-info"><strong>Presentation:</strong> {{ formatDate(film.presentation) }}</p>

        <!-- IMDb link -->
        <a v-if="film.imdbLink" :href="getImdbUrl(film.imdbLink)" target="_blank" rel="noopener noreferrer" class="imdb-link">
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
      films: [] // Ide kerülnek a backendről kapott filmek
    };
  },
  async mounted() {
    await this.fetchFilmsFromBackend();
  },
  methods: {
    // Filmek lekérése a saját backendből
    async fetchFilmsFromBackend() {
      try {
        const response = await axios.get(`${BASE_URL}/films`);

        // Ellenőrizd, hogy van-e adat, ha nincs, akkor üres tömb marad
        this.films = Array.isArray(response.data.data) ? response.data.data : [];

      } catch (error) {
        console.error("Error fetching films from backend:", error);
        this.films = []; // Ha hiba van, akkor biztosan üres tömb legyen
      }
    },

    // Dátum formázása (év-hónap-nap)
    formatDate(dateString) {
      if (!dateString) return "Unknown";
      const date = new Date(dateString);
      return date.toISOString().split("T")[0]; // Csak YYYY-MM-DD formátum
    },

    // IMDb URL ellenőrzése és generálása
    getImdbUrl(imdbLink) {
      // Ha az IMDb link üres, vagy hibás, adjunk vissza valami mást, pl. helyettesítő linket
      if (!imdbLink || imdbLink === '2' || imdbLink === '1') {
        return '#'; // Ha nincs érvényes link, akkor ne mutassunk semmit
      }
      
      // Ellenőrizzük, hogy a backend biztosan egy érvényes IMDb azonosítót ad-e vissza
      // Pl. tt1234567 formátumú azonosítót várunk, ezért ellenőrizzük a kezdést
      if (imdbLink.startsWith('tt')) {
        return `https://www.imdb.com/title/${imdbLink}/`;
      } else {
        // Ha nem kezdődik 'tt'-vel, akkor nem próbálkozunk generálni linket
        return '#'; // Visszatérünk egy helyettesítő URL-t
      }
    }
  }
};
</script>

<style scoped>
/* Általános konténer */
.container {
  max-width: 900px;
  margin: auto;
  padding: 20px;
  text-align: center;
}

/* Grid rendszer */
.films-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  justify-content: center;
}

/* Kártyák */
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

/* Film cím */
.film-title {
  font-size: 1.4em;
  margin-bottom: 10px;
  color: #f5c518;
}

/* Film infók */
.film-info {
  font-size: 1em;
  margin: 5px 0;
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
