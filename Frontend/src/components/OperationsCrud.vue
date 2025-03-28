<template>
  <div>
    <button
      type="button"
      class="btn btn-outline-primary btn-sm"
      data-bs-toggle="modal"
      data-bs-target="#modal"
      @click="onClickUpdate(data)"
    >
      <i class="bi bi-pencil"></i>
    </button>

    <button
      type="button"
      class="btn btn-outline-danger btn-sm ms-2"
      data-bs-toggle="modal"
      data-bs-target="#modal"
      @click="onClickDelete(data)"
    >
      <i class="bi bi-trash3"></i>
    </button>
  </div>
</template>

<script>
export default {
  props: ["data"],
  emits: ["onClickDelete", "onClickUpdate"],
  methods: {
    onClickDelete(data) {
      // Renamed from onClickDeleteButton
      this.$emit("onClickDelete", data);
    },
    onClickUpdate(data) {
      this.$emit("onClickUpdate", data);
    },
  },
};
</script>

<style>
</style>
