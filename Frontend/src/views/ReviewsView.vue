<template>
  <div class="film-reviews">
    <h3>Favourites</h3>

    <!-- Loading indicator -->
    <div v-if="loading" class="loading">Loading...</div>

    <!-- Error message -->
    <div v-if="error" class="error-message">{{ error }}</div>

    <!-- Favourites list -->
    <div
      v-for="favourite in favourites"
      :key="favourite.id"
      class="review-card"
    >
      <div class="review-header">
        <!-- Display user name (fetched using userId) -->
        <span class="user"
          >User: {{ favourite.userName || "Unknown User" }}</span
        >

        <!-- Display film name (fetched using filmId) -->
        <span class="film"
          >Film: {{ favourite.filmName || "Unknown Film" }}</span
        >

        <!-- Display evaluation -->
        <div class="stars">★ {{ favourite.evaluation }}</div>

        <!-- Display formatted date -->
        <span class="date">{{ formatDate(favourite.created_at) }}</span>
      </div>
    </div>

    <!-- No favourites message -->
    <div v-if="favourites.length === 0 && !loading" class="text-muted">
      No favourites found for this film.
    </div>
  </div>
</template>
    
  <script>
import axios from "axios";
import { useAuthStore } from "@/stores/useAuthStore";
import { BASE_URL } from "../helpers/baseUrls";

export default {
  props: ["filmId"],
  data() {
    return {
      favourites: [], // All favourites
      loading: false, // Loading state
      error: null, // Error state
      authStore: useAuthStore(),
    };
  },
  methods: {
    async fetchFavourites() {
      this.loading = true;

      try {
        // Get the token from the auth store
        const token = this.authStore.token;

        // Fetch the favourites with the Authorization token in the header
        const response = await axios.get(`${BASE_URL}/favourites`, {
          headers: {
            Authorization: `Bearer ${token}`, // Pass token in the Authorization header
          },
        });

        console.log("Fetched favourites:", response.data);

        if (response.data && Array.isArray(response.data.data)) {
          this.favourites = response.data.data;

          // Fetch all user and film data in parallel using Promise.all
          const userPromises = this.favourites.map((favourite) =>
            axios
              .get(`${BASE_URL}/users/${favourite.userId}`, {
                headers: { Authorization: `Bearer ${token}` }, // Add token for user data request
              })
              .then((res) => res.data)
          );
          const filmPromises = this.favourites.map((favourite) =>
            axios
              .get(`${BASE_URL}/films/${favourite.filmId}`, {
                headers: { Authorization: `Bearer ${token}` }, // Add token for film data request
              })
              .then((res) => res.data)
          );

          // Wait for all promises to resolve
          const users = await Promise.all(userPromises);
          const films = await Promise.all(filmPromises);

          // Update favourites with user and film data
          this.favourites.forEach((favourite, index) => {
            favourite.userName = users[index].name || "Unknown User";
            favourite.filmName = films[index].name || "Unknown Film";
          });
        } else {
          console.error("Invalid data format:", response.data);
          this.error = "Data format is incorrect.";
        }
      } catch (error) {
        console.error("Error fetching favourites:", error);
        this.error = "Error fetching favourites. Please try again later.";
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      if (!date) return "N/A";
      const d = new Date(date);
      return isNaN(d) ? "N/A" : d.toLocaleString("hu-HU");
    },
  },

  mounted() {
    this.fetchFavourites();
  },
};
</script>
    
  <style scoped>
/* Maintain your original styles */
.review-card {
  border: 1px solid #ddd;
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 8px;
}
.review-header {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 0.5rem;
}
.stars {
  color: gold;
  font-weight: bold;
}
.date {
  margin-left: auto;
  color: #ff9999;
  text-shadow: #ffffff, 1px;
}
.text-muted {
  color: #666;
  font-style: italic;
}
.loading {
  font-size: 1.2rem;
  color: #007bff;
}
.error-message {
  font-size: 1.2rem;
  color: red;
}
</style>
  