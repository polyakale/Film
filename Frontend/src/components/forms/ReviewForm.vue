<template>
  <form @submit.prevent="onClickSubmit" class="review-form">
    <div class="user-info">
      <!-- Star Rating -->
      <div class="star-input">
        <span
          v-for="starIndex in 5"
          :key="starIndex"
          @click="setRating(starIndex, $event)"
        >
          <i class="bi star-icon" :class="getStarClass(starIndex)"></i>
        </span>
        <span class="rating-display"
          >({{ itemForm.evaluation.toFixed(1) }})</span
        >
      </div>

      <!-- Film Selection -->
      <select v-model="itemForm.filmId" class="film-select" required>
        <option value="" disabled>Select Film</option>
        <option
          v-for="film in availableFilms"
          :key="film.id"
          :value="film.id"
          :disabled="isFilmReviewed(film.id)"
        >
          {{ film.title }}
          <span v-if="isFilmReviewed(film.id)"> (Already Reviewed)</span>
        </option>
      </select>
    </div>

    <!-- Review Content -->
    <textarea
      v-model="itemForm.content"
      class="review-input"
      placeholder="Write your review..."
    ></textarea>

    <div class="form-actions">
      <button
        type="submit"
        class="btn-submit"
        :disabled="isSubmitting || !validateForm()"
      >
        {{ isSubmitting ? "Saving..." : "Save" }}
      </button>
    </div>
  </form>
</template>

<script>
export default {
  props: {
    itemForm: {
      type: Object,
      required: true,
      default: () => ({
        filmId: "",
        evaluation: 0,
        userId: null,
        content: "",
      }),
    },
    films: {
      type: Array,
      default: () => [],
    },
    favourites: {
      type: Array,
      default: () => [],
    },
    username: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      isSubmitting: false,
    };
  },
  computed: {
    availableFilms() {
      if (!Array.isArray(this.films) || !Array.isArray(this.favourites)) {
        return [];
      }

      return this.films.filter((film) => !this.isFilmReviewed(film.id));
    },
  },
  methods: {
    isFilmReviewed(filmId) {
      return this.favourites.some(
        (fav) => fav.filmId === filmId && fav.userId === this.itemForm.userId
      );
    },
    getStarClass(starIndex) {
      const evalValue = this.itemForm.evaluation || 0;
      return {
        "bi-star-fill": evalValue >= starIndex,
        "bi-star-half": evalValue >= starIndex - 0.5 && evalValue < starIndex,
        "bi-star": evalValue < starIndex - 0.5,
        "text-warning": true,
      };
    },
    setRating(starIndex, event) {
      const rect = event.target.getBoundingClientRect();
      const isHalfStar = event.clientX - rect.left < rect.width / 2;
      this.itemForm.evaluation = isHalfStar ? starIndex - 0.5 : starIndex;
    },
    onClickSubmit() {
      if (this.isSubmitting || !this.validateForm()) return;

      this.isSubmitting = true;
      this.$emit("saveItem", {
        ...this.itemForm,
        evaluation: Number(this.itemForm.evaluation),
        content: this.itemForm.content,
      });
    },
    validateForm() {
      return (
        this.itemForm.filmId &&
        this.itemForm.evaluation > 0 &&
        this.itemForm.content?.trim().length >= 0
      );
    },
  },
};
</script>

<style scoped>
.review-form {
  background: #383838;
  border: 3px solid #1f1f1f;
  padding: 1rem;
  color: #ffd700;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.star-input {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-left: 0;
}

.star-icon {
  font-size: 1.5rem;
  color: #ffd700;
  cursor: pointer;
  transition: transform 0.2s;
}

.star-icon:hover {
  transform: scale(1.2);
}

.rating-display {
  font-weight: bold;
  margin-left: 0.5rem;
  font-size: 0.9rem;
}

.film-select {
  flex-grow: 1;
  min-width: 200px;
  padding: 0.5rem;
  background: #383838;
  border: 3px solid #1f1f1f;
  border-radius: 0;
  font-size: 0.9rem;
  color: #b0b0b0;
}

.film-select:focus {
  border-color: #ffd700;
  box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25);
}

.film-select option:disabled {
  color: #666;
  background-color: #2a2a2a;
}

.review-input {
  width: 100%;
  height: 100px;
  resize: vertical;
  background: #383838;
  border: 2px solid #1f1f1f;
  border-radius: 0;
  padding: 0.5rem;
  font-size: 0.9rem;
  color: #b0b0b0;
  margin-top: 1rem;
}

.review-input:focus {
  border-color: #ffd700;
  color: #fff;
  box-shadow: none;
}

.form-actions {
  margin-top: 1rem;
  display: flex;
  justify-content: flex-end;
}

.btn-submit {
  background: #cc181e;
  color: #fff;
  border: none;
  padding: 0.5rem 1.5rem;
  border-radius: 60px;
  font-weight: bold;
  transition: background 0.3s;
}

.btn-submit:hover:not(:disabled) {
  background: #b10d12;
}

.btn-submit:disabled {
  background: #666;
  cursor: not-allowed;
  opacity: 0.7;
}
</style>
