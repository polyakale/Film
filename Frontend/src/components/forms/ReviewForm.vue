<template>
  <form
    @submit.prevent="onClickSubmit"
    class="row g-4 needs-validation was-validated"
  >
    <div class="col-md-4 position-relative">
      <label for="userId" class="form-label">User ID:</label>
      <input
        type="number"
        class="form-control"
        id="userId"
        required
        v-model="itemForm.userId"
      />
      <div class="invalid-feedback">Please provide a User ID.</div>
    </div>

    <div class="col-md-4 position-relative">
      <label for="filmId" class="form-label">Film ID:</label>
      <input
        type="number"
        class="form-control"
        id="filmId"
        required
        v-model="itemForm.filmId"
      />
      <div class="invalid-feedback">Please provide a Film ID.</div>
    </div>

    <div class="col-md-4 position-relative">
      <label for="evaluation" class="form-label">Evaluation:</label>
      <input
        type="number"
        class="form-control"
        id="evaluation"
        required
        v-model="itemForm.evaluation"
        min="1"
        max="5"
      />
      <div class="invalid-feedback">Please provide an evaluation (1-5).</div>
    </div>

    <button type="submit" class="btn btn-success">Save</button>
  </form>
</template>
  
  <script>
export default {
  props: ["itemForm", "debug"],
  emits: ["saveItem"],
  methods: {
    onClickSubmit() {
      this.$emit("saveItem", this.itemForm);
    },
  },
};
</script>
  
  <style>
</style>
  