<template>
  <nav aria-label="Page navigation example">
    <ul class="pagination">
      <li
        class="page-item"
        :class="{ disabled: pageInfo.pageNumber == 1 }"
        @click="onClickPrevious()"
      >
        <span class="page-link" aria-hidden="true"> &laquo; </span>
      </li>

      <li
        class="page-item"
        v-for="page in pagesArray"
        :key="page"
        @click="onClickPageNumber(page)"
      >
        <span class="page-link">{{ page }}</span>
      </li>

      <li
        class="page-item"
        :class="{
          disabled: pageInfo.pageNumber == numberOfPages,
        }"
        @click="onClickNext()"
      >
        <span class="page-link" aria-hidden="true"> &raquo; </span>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  props: ["pageNumber", "numberOfPages", "pagesArray"],
  data() {
    return {
      pageInfo: {
        pageNumber: this.pageNumber,
      },
    };
  },
  watch: {
    pageNumber(data) {
      this.pageInfo.pageNumber = data;
    },
  },
  methods: {
    onClickPrevious() {
      this.pageInfo.pageNumber = Math.max(1, this.pageInfo.pageNumber - 1);
      this.$emit("paging", this.pageInfo);
    },
    onClickPageNumber(page) {
      this.pageInfo.pageNumber = page;
      this.$emit("paging", this.pageInfo);
    },
    onClickNext() {
      this.pageInfo.pageNumber = Math.min(
        this.numberOfPages,
        this.pageInfo.pageNumber + 1
      );
      this.$emit("paging", this.pageInfo);
    },
  },
};
</script>

<style scoped>
.page-link {
  cursor: pointer;
}
</style>
