<template>
  <div class="modal-overlay">
    <div class="review-modal">
      <form @submit.prevent="onClickSubmit" class="review-form">
        <div class="form-grid user-info">
          <!-- Rating Input -->
          <div class="form-group star-input-group">
            <label>Rating*</label>
            <div class="star-input">
              <span
                v-for="starIndex in 5"
                :key="starIndex"
                @click="setRating(starIndex, $event)"
                @keydown.enter.space="setRating(starIndex, $event)"
                tabindex="0"
                role="radio"
                :aria-checked="itemForm.evaluation >= starIndex"
                :aria-label="`${starIndex} stars`"
                class="star-wrapper"
                :title="`${starIndex} stars`"
              >
                <i class="bi star-icon" :class="getStarClass(starIndex)"></i>
              </span>
              <span class="rating-display" v-if="itemForm.evaluation > 0">
                ({{ itemForm.evaluation.toFixed(1) }}/5.0)
              </span>
            </div>
          </div>

          <!-- Film Select -->
          <div class="form-group film-select-group">
            <label for="filmSelect">Film*</label>
            <select
              id="filmSelect"
              v-model="itemForm.filmId"
              class="form-control film-select"
              required
              :disabled="isUpdate"
            >
              <option value="" disabled>Select Film</option>
              <option v-if="isUpdate && currentFilm" :value="currentFilm.id">
                {{ currentFilm.title }}
              </option>
              <template v-else>
                <option
                  v-for="film in availableFilms"
                  :key="film.id"
                  :value="film.id"
                >
                  {{ film.title }}
                </option>
              </template>
            </select>
            <small
              v-if="!isUpdate && isFilmReviewed(itemForm.filmId)"
              class="text-danger error-inline"
            >
              You have already reviewed this film.
            </small>
          </div>
        </div>

        <!-- Optional Review Comment -->
        <div class="form-group">
          <label for="reviewContent">Review Comment (Optional)</label>
          <textarea
            id="reviewContent"
            v-model="itemForm.content"
            class="form-control review-input"
            placeholder="Share your thoughts..."
            rows="5"
          ></textarea>
        </div>

        <!-- Form Action -->
        <div class="form-actions">
          <div>
            <button
              type="submit"
              class="btn btn-submit"
              :disabled="isSubmitting || !validateForm()"
            >
              <span v-if="isSubmitting">
                <span class="spinner" role="status" aria-hidden="true"></span>
                Saving...
              </span>
              <span v-else>Save Review</span>
            </button>
          </div>
          <div class="cancel-button">
            <button
              type="button"
              class="btn btn-cancel"
              @click="$emit('closeModal')"
            >
              <span>Cancel</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    itemForm: {
      type: Object,
      required: true,
      default: () => ({ filmId: "", evaluation: 0, userId: null, content: "" }),
    },
    films: { type: Array, default: () => [] },
    favourites: { type: Array, default: () => [] },
    isUpdate: { type: Boolean, default: false },
  },
  data() {
    return {
      isSubmitting: false,
    };
  },
  computed: {
    availableFilms() {
      if (
        this.isUpdate ||
        !Array.isArray(this.films) ||
        !Array.isArray(this.favourites)
      ) {
        return this.films;
      }
      const reviewedFilmIds = new Set(
        this.favourites
          .filter((fav) => fav.userId === this.itemForm.userId)
          .map((fav) => fav.filmId)
      );
      return this.films.filter((film) => !reviewedFilmIds.has(film.id));
    },
    currentFilm() {
      if (this.itemForm.filmId && Array.isArray(this.films)) {
        return this.films.find((f) => f.id === this.itemForm.filmId);
      }
      return null;
    },
  },
  methods: {
    isFilmReviewed(filmId) {
      if (this.isUpdate || !filmId) return false;
      return this.favourites.some(
        (fav) => fav.filmId === filmId && fav.userId === this.itemForm.userId
      );
    },
    getStarClass(starIndex) {
      const evalValue = this.itemForm.evaluation || 0;
      if (evalValue >= starIndex) return "bi-star-fill";
      if (evalValue >= starIndex - 0.5) return "bi-star-half";
      return "bi-star";
    },
    setRating(starIndex, event) {
      if (this.itemForm.evaluation === starIndex) {
        this.itemForm.evaluation = starIndex - 0.5;
      } else {
        this.itemForm.evaluation = starIndex;
      }
      this.itemForm.evaluation = Math.max(0.5, this.itemForm.evaluation);
    },
    validateForm() {
      const isValid = this.itemForm.filmId && this.itemForm.evaluation > 0;
      if (!this.isUpdate && this.isFilmReviewed(this.itemForm.filmId)) {
        return false;
      }
      return isValid;
    },
    onClickSubmit() {
      if (this.isSubmitting || !this.validateForm()) return;
      this.isSubmitting = true;
      this.$emit("saveItem", {
        ...this.itemForm,
        evaluation: Number(this.itemForm.evaluation),
        content: this.itemForm.content?.trim() || null,
      });
      setTimeout(() => {
        this.isSubmitting = false;
      }, 1000);
    },
  },
};
</script>

<style scoped>
/* Modal overlay with blur effect on background */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

/* Main container for the review form with enhanced border and shadow */
.review-modal {
  width: 90%;
  max-width: 600px;
  background: var(--bg-secondary, #1f1f1f);
  border: 2px solid var(--border-color, #383838);
  border-radius: 10px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  padding: 2rem;
}

/* === Define Theme Variables === */
.review-form {
  --bg-base: #111;
  --bg-primary: #1f1f1f;
  --bg-secondary: #2a2a2a;
  --bg-tertiary: #333333;
  --text-primary: #ffffff;
  --text-secondary: #eeeeee;
  --text-muted: #aaaaaa;
  --accent-gold: #ffd700;
  --accent-red: #e53e3e;
  --accent-green: #4caf50;
  --accent-blue: #2196f3;
  --border-color: #383838;
  --focus-ring-color: rgba(255, 215, 0, 0.3);
}

/* === Form Container === */
.review-form {
  background: transparent;
  padding: 0;
  color: var(--text-secondary);
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

/* === Grid Layout for Top Sections === */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem 1.5rem;
  align-items: end;
}

.form-group {
  margin-bottom: 0;
  display: flex;
  flex-direction: column;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  color: var(--text-muted);
  font-weight: 600;
  margin-bottom: 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* === Star Rating Input === */
.star-input-group {
  grid-column: 1 / -1;
}
@media (min-width: 576px) {
  .star-input-group {
    grid-column: auto;
  }
}

.star-input {
  display: flex;
  align-items: center;
  gap: 0.2rem;
  background-color: var(--bg-primary);
  padding: 8px 10px;
  border-radius: 4px;
  border: 1px solid var(--border-color);
  min-height: 40px;
  box-sizing: border-box;
}

.star-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 1px 2px;
  line-height: 1;
  border-radius: 3px;
  transition: background-color 0.2s ease;
}
.star-wrapper:hover {
  background-color: rgba(255, 255, 255, 0.1);
}
.star-wrapper:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px var(--focus-ring-color);
}

.star-icon {
  font-size: 1.6rem;
  color: var(--text-muted);
  transition: transform 0.2s ease, color 0.2s ease;
}
.star-icon.bi-star-fill,
.star-icon.bi-star-half {
  color: var(--accent-gold);
}
.star-wrapper:hover .star-icon {
  transform: scale(1.15);
}

.rating-display {
  font-weight: 600;
  margin-left: 0.75rem;
  font-size: 0.9rem;
  color: var(--text-primary);
}

/* === Film Select Dropdown === */
.film-select {
  width: 100%;
  padding: 8px 12px;
  font-size: 1rem;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  border-radius: 4px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
  box-sizing: border-box;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23aaaaaa' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1em 1em;
  padding-right: 2.5rem;
  min-height: 40px;
}
.film-select:focus {
  outline: none;
  border-color: var(--accent-gold);
  box-shadow: 0 0 0 3px var(--focus-ring-color);
}
.film-select:disabled {
  background-color: var(--bg-tertiary);
  opacity: 0.7;
  cursor: not-allowed;
}
.film-select option {
  background-color: var(--bg-primary);
  color: var(--text-primary);
}
.film-select option:disabled {
  color: var(--text-muted);
  font-style: italic;
}

/* === Textarea Input === */
.review-input {
  width: 100%;
  padding: 10px 12px;
  font-size: 1rem;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  border-radius: 4px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
  box-sizing: border-box;
  height: 120px;
  resize: vertical;
  margin-top: 0;
}
.review-input::placeholder {
  color: var(--text-muted);
}
.review-input:focus {
  outline: none;
  border-color: var(--accent-gold);
  box-shadow: 0 0 0 3px var(--focus-ring-color);
}

/* === Form Actions === */
.form-actions {
  margin-top: 1rem;
  display: flex;
  /* justify-content: flex-start; */
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

/* === Buttons === */
.btn {
  padding: 10px 20px;
  font-size: 1rem;
  font-weight: 700;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s ease, box-shadow 0.2s ease,
    transform 0.1s ease;
  text-align: center;
  line-height: 1.4;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.btn:hover:not(:disabled) {
  transform: translateY(-1px);
}
.btn:active:not(:disabled) {
  transform: translateY(0px);
}
.btn:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px var(--focus-ring-color);
}
.submit-button {
  margin-right: auto;
}
.btn-submit {
  background-color: var(--accent-gold);
  color: var(--bg-primary);
}
.btn-submit:hover:not(:disabled) {
  background-color: #ffc107;
  box-shadow: 0 4px 10px rgba(255, 215, 0, 0.4);
}
.cancel-button {
  margin-left: auto;
}
.btn-cancel {
  background-color: #252525;
  border: #737373 1px solid;
  color: white;
}
.btn-cancel:hover:not(:disabled) {
  background-color: #474747;
  box-shadow: 0 4px 10px rgba(100, 100, 100, 0.4);
}
.btn .spinner {
  width: 1em;
  height: 1em;
  border: 2px solid currentColor;
  border-right-color: transparent !important;
  border-radius: 50%;
  display: inline-block;
  vertical-align: middle;
  animation: spin 0.6s linear infinite;
  margin-right: 0.5em;
}

/* === Error Text === */
.error-inline {
  display: block;
  font-size: 0.8rem;
  margin-top: 0.35rem;
  font-weight: normal;
}
.text-danger {
  color: var(--accent-red);
}

/* Spinner Animation */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
