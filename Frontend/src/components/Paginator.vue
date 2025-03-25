<template>
  <nav aria-label="Page navigation">
    <ul class="pagination">
      <li
        class="page-item"
        :class="{
          disabled: pageNumber === 1,
        }"
        @click="paging(1)"
      >
        <a class="page-link" href="#" aria-label="First">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
      <li
        class="page-item"
        :class="{
          disabled: pageNumber === 1,
        }"
        @click="paging(pageNumber - 1)"
      >
        <a class="page-link" href="#" aria-label="Previous">
          <span aria-hidden="true">&lsaquo;</span>
        </a>
      </li>

      <li
        v-for="page in getDisplayPages()"
        :key="page"
        class="page-item"
        :class="{ active: page === pageNumber }"
        @click="paging(page)"
      >
        <a class="page-link" href="#">
          {{ page }}
        </a>
      </li>

      <li
        class="page-item"
        :class="{
          disabled: pageNumber === numberOfPages,
        }"
        @click="paging(pageNumber + 1)"
      >
        <a class="page-link" href="#" aria-label="Next">
          <span aria-hidden="true">&rsaquo;</span>
        </a>
      </li>
      <li
        class="page-item"
        :class="{
          disabled: pageNumber === numberOfPages,
        }"
        @click="paging(numberOfPages)"
      >
        <a class="page-link" href="#" aria-label="Last">
          <span aria-hidden="true">&raquo;</span>
        </a>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: "Paginator",
  props: {
    pageNumber: {
      type: Number,
      required: true,
      default: 1,
    },
    numberOfPages: {
      type: Number,
      required: true,
      default: 1,
    },
    pagesArray: {
      type: Array,
      required: true,
      default: () => [],
    },
  },
  methods: {
    paging(page) {
      if (page >= 1 && page <= this.numberOfPages) {
        this.$emit("paging", { pageNumber: page });
      }
    },
        getDisplayPages() {
      const visiblePages = 5; // Maximum number of visible page numbers
      if (this.numberOfPages <= visiblePages) {
        return Array.from({ length: this.numberOfPages }, (_, i) => i + 1);
      }

      let startPage = Math.max(1, this.pageNumber - Math.floor(visiblePages / 2));
      let endPage = Math.min(this.numberOfPages, this.pageNumber + Math.floor(visiblePages / 2));

      if (endPage - startPage + 1 < visiblePages) {
          if (startPage === 1) {
              endPage = Math.min(this.numberOfPages, visiblePages);
          } else {
              startPage = Math.max(1, this.numberOfPages - visiblePages + 1);
          }
      }
      let pages =  Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);

      if (startPage > 1) {
          pages.unshift(1);
          if (startPage > 2) {
            pages.splice(1, 0, '...');
          }
      }
      if (endPage < this.numberOfPages) {
          pages.push(this.numberOfPages);
          if (endPage < this.numberOfPages - 1) {
            pages.splice(pages.length-1, 0, '...');
          }
      }
      return pages;
    },
  },
};
</script>

<style scoped>
:root {
  --primary-color: #8b0000; /* Dark Red */
  --secondary-color: #ffd700; /* Gold */
  --accent-color: #000000; /* Black */
  --background-dark: #1a1a1a;
  --text-light: #ffffff;
  --text-muted: #cccccc;
}

.pagination {
  list-style: none;
  display: flex;
  gap: 0.5rem;
  padding: 0;
  margin: 0;
}

.pagination .page-item {
  cursor: pointer;
}

.pagination .page-item .page-link {
  display: block;
  padding: 0.5rem 0.75rem;
  border: none;
  border-radius: 15px;
  background: var(--accent-color);
  color: var(--secondary-color);
  min-width: 40px;
  text-align: center;
  transition: all 0.3s ease;
}

.pagination .page-item.active .page-link {
  background: var(--primary-color) !important;
  color: var(--secondary-color) !important;
  font-weight: bold;
}

.pagination .page-item:hover .page-link {
  background: var(--secondary-color);
  color: var(--accent-color) !important;
  transform: translateY(-2px);
}

.pagination .page-item.disabled .page-link {
  color: var(--text-muted);
  background: #885050;
  cursor: not-allowed;
  transform: none;
}
.pagination .page-item .page-link:focus{
    box-shadow: 0 0 0 0.2rem rgba(189, 1, 1, 0.25);
}
</style>
