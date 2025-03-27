<template>
  <form @submit.prevent="onClickSubmit" class="review-form">
    <div class="user-info">
      <span class="username">{{ username }}</span>
      
      <!-- Star Rating -->
      <div class="star-input">
        <span v-for="starIndex in 5" :key="starIndex" @click="setRating(starIndex, $event)">
          <i class="bi star-icon" :class="getStarClass(starIndex)"></i>
        </span>
        <span class="rating-display">({{ itemForm.evaluation.toFixed(1) }})</span>
      </div>
      
      <!-- Film Selection -->
      <select v-model="itemForm.filmId" class="film-select" required>
        <option value="" disabled>Select Film</option>
        <option v-for="film in films" :key="film.id" :value="film.id">
          {{ film.title }}
        </option>
      </select>
    </div>

    <!-- Review Content -->
    <textarea
      v-model="itemForm.content"
      class="review-input"
      placeholder="Write your review..."
      required
    ></textarea>

    <div class="form-actions">
      <button type="submit" class="btn-submit" :disabled="isSubmitting">
        {{ isSubmitting ? 'Saving...' : 'Save' }}
      </button>
    </div>
  </form>
</template>

<script>
export default {
  props: {
    itemForm: {
      type: Object,
      default: () => ({
        filmId: '',
        evaluation: 0,
        content: '',
        userId: null
      })
    },
    films: Array,
    username: String
  },
  data() {
    return {
      isSubmitting: false
    }
  },
  computed: {
    fullStars() {
      return Math.floor(this.itemForm.evaluation);
    },
    hasHalfStar() {
      return this.itemForm.evaluation % 1 >= 0.5;
    }
  },
  methods: {
    getStarClass(starIndex) {
      return {
        'bi-star-fill': starIndex <= this.fullStars,
        'bi-star-half': starIndex === this.fullStars + 1 && this.hasHalfStar,
        'bi-star': starIndex > this.fullStars + (this.hasHalfStar ? 0.5 : 0)
      };
    },
    setRating(starIndex, event) {
      const rect = event.target.getBoundingClientRect();
      const isHalfStar = event.clientX - rect.left < rect.width / 2;
      this.itemForm.evaluation = isHalfStar ? starIndex - 0.5 : starIndex;
    },
    onClickSubmit() {
      if (!this.validateForm()) return;
      this.isSubmitting = true;
      this.$emit('saveItem', this.itemForm);
    },
    validateForm() {
      return this.itemForm.filmId && this.itemForm.evaluation > 0;
    }
  }
};
</script>

<style scoped>
/* Reuse your existing styles from guest-review-form */
.review-form {
  padding: 1rem;
}
.star-icon {
  font-size: 1.5rem;
  cursor: pointer;
}
/* ... other existing styles ... */
</style>