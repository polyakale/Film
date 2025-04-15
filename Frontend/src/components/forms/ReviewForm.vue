<template>
  <form @submit.prevent="onClickSubmit" class="review-form">

    <div class="form-row user-info">
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
           <span class="rating-display" v-if="itemForm.evaluation > 0"
             >({{ itemForm.evaluation.toFixed(1) }}/5.0)</span
           >
         </div>
      </div>

      <div class="form-group film-select-group">
         <label for="filmSelect">Film*</label>
         <select
            id="filmSelect"
            v-model="itemForm.filmId"
            class="form-control film-select"
            required
            :disabled="isUpdate" >
           <option value="" disabled>Select Film</option>

           <option
             v-if="isUpdate && currentFilm"
             :value="currentFilm.id"
           >
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
         <small v-if="!isUpdate && isFilmReviewed(itemForm.filmId)" class="text-danger error-inline">
             You have already reviewed this film.
         </small>
      </div>
    </div>

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


    <div class="form-actions">
      <button
        type="submit"
        class="btn btn-submit"
        :disabled="isSubmitting || !validateForm()"
      >
        <span v-if="isSubmitting">
            <span class="spinner" role="status" aria-hidden="true"></span> Saving...
        </span>
        <span v-else>Save Review</span>
      </button>
    </div>
  </form>
</template>

<script>
// Script remains largely the same, ensure props are passed correctly from parent
export default {
  name: "ReviewForm",
  props: {
    itemForm: { // This object should be reactive (ref or reactive) in the parent
      type: Object,
      required: true,
      default: () => ({
        filmId: "",
        evaluation: 0,
        userId: null, // Make sure userId is passed in
        content: "",
      }),
    },
    films: { // Full list of films
      type: Array,
      default: () => [],
    },
    favourites: { // User's existing reviews/favourites
      type: Array,
      default: () => [],
    },
    isUpdate: { // Flag to indicate if this is an update or new review
        type: Boolean,
        default: false
    }
    // Removed username prop as it's not used here
  },
  data() {
    return {
      isSubmitting: false, // Local submitting state
    };
  },
  computed: {
    // Films available for selection (excluding already reviewed ones, only for NEW reviews)
    availableFilms() {
      if (this.isUpdate || !Array.isArray(this.films) || !Array.isArray(this.favourites)) {
        // In update mode, we don't need to filter here, the select is disabled.
        // Return all films just in case, though it won't be used for selection.
        return this.films;
      }
      // Filter out films already reviewed by the current user
      const reviewedFilmIds = new Set(
          this.favourites
            .filter(fav => fav.userId === this.itemForm.userId) // Filter by current user
            .map(fav => fav.filmId)
      );
      return this.films.filter((film) => !reviewedFilmIds.has(film.id));
    },
    // Get the currently selected film details (useful for displaying title in update mode)
    currentFilm() {
        // Find the film matching the itemForm.filmId from the main films list
        if (this.itemForm.filmId && Array.isArray(this.films)) {
            return this.films.find(f => f.id === this.itemForm.filmId);
        }
        return null;
    }
  },
  methods: {
    // Check if a film has already been reviewed by the current user
    isFilmReviewed(filmId) {
        // This check is mainly relevant when adding a *new* review
        if (this.isUpdate || !filmId) return false; // Don't show error if updating or no film selected

        return this.favourites.some(
            (fav) => fav.filmId === filmId && fav.userId === this.itemForm.userId
        );
    },
    // Determine CSS class for star icons based on current evaluation
    getStarClass(starIndex) {
      const evalValue = this.itemForm.evaluation || 0;
      // Use bi icons classes
      if (evalValue >= starIndex) return "bi-star-fill";
      if (evalValue >= starIndex - 0.5) return "bi-star-half";
      return "bi-star";
    },
    // Set the rating based on star click (allows half-star)
    setRating(starIndex, event) {
      // Simple toggle logic: click full star -> full, click again -> half
       if (this.itemForm.evaluation === starIndex) {
           this.itemForm.evaluation = starIndex - 0.5;
       } else {
           this.itemForm.evaluation = starIndex;
       }
       // Ensure rating is at least 0.5 if clicked
       this.itemForm.evaluation = Math.max(0.5, this.itemForm.evaluation);
    },
    // Validate the form before submission
    validateForm() {
      // Must select a film and provide a rating > 0
      // Content is optional
      const isValid = this.itemForm.filmId && this.itemForm.evaluation > 0;
      // Prevent submission if trying to add review for already reviewed film
      if (!this.isUpdate && this.isFilmReviewed(this.itemForm.filmId)) {
          return false;
      }
      return isValid;
    },
    // Handle form submission
    onClickSubmit() {
      if (this.isSubmitting || !this.validateForm()) return;

      this.isSubmitting = true;
      // Emit the save event with the current form data
      this.$emit("saveItem", {
        ...this.itemForm,
        evaluation: Number(this.itemForm.evaluation), // Ensure evaluation is number
        content: this.itemForm.content?.trim() || null, // Send null if empty/whitespace
      });

      // Reset submitting state after a short delay (or parent should handle it)
      setTimeout(() => {
        this.isSubmitting = false;
      }, 1000); // Example delay
    },
  },
};
</script>

<style scoped>
/* === Define Theme Variables (Consistent with other components) === */
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
  background: transparent; /* Form background should match modal content bg */
  padding: 1rem 0; /* Padding adjusted for modal context */
  color: var(--text-secondary);
  border: none; /* Remove previous border */
}

/* === Form Row & Groups === */
.form-row {
  display: flex;
  flex-wrap: wrap; /* Allow wrapping on smaller screens */
  gap: 1.5rem; /* Spacing between groups */
  margin-bottom: 1rem;
  align-items: flex-end; /* Align items to bottom for better label alignment */
}

.form-group {
  margin-bottom: 0; /* Remove bottom margin, rely on row gap */
  flex: 1; /* Allow groups to grow */
  min-width: 150px; /* Minimum width for wrapping */
}

.form-group label {
  display: block;
  font-size: 0.9rem;
  color: var(--text-muted); /* Muted label color */
  font-weight: 600;
  margin-bottom: 0.4rem;
}

/* === Star Rating Input === */
.star-input-group {
    flex-basis: 100%; /* Take full width initially */
    min-width: 200px; /* Ensure enough space */
}
@media (min-width: 576px) {
    .star-input-group {
        flex-basis: auto; /* Allow shrinking on larger screens */
    }
}

.star-input {
  display: flex;
  align-items: center;
  gap: 0.3rem; /* Spacing between stars */
  margin-top: 0.25rem; /* Align with input fields */
}

.star-wrapper {
    display: inline-block;
    cursor: pointer;
    padding: 2px; /* Add padding for easier clicking */
    line-height: 1; /* Prevent extra space */
}

.star-icon {
  font-size: 1.8rem; /* Larger stars */
  color: var(--text-muted); /* Default star color (empty) */
  transition: transform 0.2s ease, color 0.2s ease;
}
.star-icon.bi-star-fill,
.star-icon.bi-star-half {
  color: var(--accent-gold); /* Gold for filled/half stars */
}
.star-wrapper:hover .star-icon {
  transform: scale(1.2); /* Scale effect on hover */
}
.star-wrapper:focus {
    outline: 2px solid var(--focus-ring-color); /* Accessibility focus */
    border-radius: 2px;
}


.rating-display {
  font-weight: bold;
  margin-left: 0.75rem;
  font-size: 0.9rem;
  color: var(--text-secondary);
}

/* === Film Select Dropdown === */
.film-select-group {
    flex-grow: 1; /* Allow select to take remaining space */
}
/* Apply form-control styles */
.film-select {
  width: 100%;
  padding: 10px 12px;
  font-size: 1rem;
  background: var(--bg-secondary);
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
    background-color: var(--bg-secondary);
    color: var(--text-primary);
}
/* Disabled options within select are handled by browser, but we can hint */
.film-select option:disabled {
  color: var(--text-muted);
  font-style: italic;
}

/* === Textarea Input === */
/* Apply form-control styles */
.review-input {
  width: 100%;
  padding: 10px 12px;
  font-size: 1rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  border-radius: 4px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
  box-sizing: border-box;
  height: 120px;
  resize: vertical;
  margin-top: 0;
}
.review-input::placeholder { color: var(--text-muted); }
.review-input:focus {
  outline: none;
  border-color: var(--accent-gold);
  box-shadow: 0 0 0 3px var(--focus-ring-color);
}


/* === Form Actions === */
.form-actions {
  margin-top: 1.5rem;
  display: flex;
  justify-content: flex-end; /* Align button to the right */
  padding-top: 1rem;
  border-top: 1px solid var(--border-color); /* Separator line */
}

/* === Submit Button === */
/* Base button styles should be inherited or defined globally/in modal */
.btn {
  padding: 10px 16px; font-size: 1rem; font-weight: 700; border: none; border-radius: 4px;
  cursor: pointer; transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.1s ease;
  text-align: center; line-height: 1.4; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
}
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn:hover:not(:disabled) { transform: translateY(-1px); }
.btn:active:not(:disabled) { transform: translateY(0px); }

/* Submit button specific style */
.btn-submit {
    background-color: var(--accent-gold);
    color: var(--bg-primary); /* Dark text */
}
.btn-submit:hover:not(:disabled) {
    background-color: #ffc107; /* Lighter gold */
    box-shadow: 0 4px 10px rgba(255, 215, 0, 0.4);
}
/* Cancel button style (if added) */
.btn-cancel {
    background-color: #6c757d; color: white;
}
.btn-cancel:hover:not(:disabled) {
    background-color: #5a6268;
}

/* === Spinner inside Button === */
.btn .spinner {
  width: 1em; /* Size relative to button font size */
  height: 1em;
  border: 2px solid currentColor; /* Use button text color */
  border-right-color: transparent !important; /* Hide one side for spin effect */
  border-radius: 50%;
  display: inline-block;
  vertical-align: middle;
  animation: spin 0.6s linear infinite;
  margin-right: 0.5em; /* Space between spinner and text */
}

/* === Inline error text === */
.error-inline {
    display: block;
    font-size: 0.8rem;
    margin-top: 0.25rem;
    font-weight: normal; /* Normal weight for inline errors */
}
.text-danger {
    color: var(--accent-red);
}

/* Spinner animation */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

</style>
