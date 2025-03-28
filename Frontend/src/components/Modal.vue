<template>
  <!-- Modal -->
  <div class="modal fade" id="modal" tabindex="-1">
    <div
      class="modal-dialog modal-dialog-centered"
      :class="{
        'modal-xl': size == 'xl',
        'modal-lg': size == 'lg',
        'modal-sm': size == 'sm',
      }"
    >
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">{{ title }}</h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <slot></slot>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
            v-if="no"
          >
            {{ no }}
          </button>
          <button
            type="button"
            class="btn btn-primary"
            v-if="yes"
            @click="onClickYesButton()"
            data-bs-dismiss="modal"
          >
            {{ yes }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["title", "yes", "no", "size"],
  emits: ["yesEvent", "noEvent"],

  methods: {
    onClickYesButton() {
      this.$emit("yesEvent");
    },
    hide() {
      if (this.$el && this.$el.classList.contains("show")) {
        const modal = bootstrap.Modal.getInstance(this.$el);
        if (modal) modal.hide();
      }
    },
    show() {
      const modal = new bootstrap.Modal(this.$el);
      modal.show();
    },
  },
};
</script>

<style>
/* For modal style */
.modal-content {
  background: #383838;
  border: 3px solid #1f1f1f;
  color: #ffd700;
}

.modal-header {
  border-bottom: 2px solid #1f1f1f;
}

.modal-title {
  color: #ffd700 !important;
}

.btn-close {
  filter: invert(1);
}

.modal-body {
  padding: 20px;
}
</style>
