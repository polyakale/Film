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

      let startPage = Math.max(
        1,
        this.pageNumber - Math.floor(visiblePages / 2)
      );
      let endPage = Math.min(
        this.numberOfPages,
        this.pageNumber + Math.floor(visiblePages / 2)
      );

      if (endPage - startPage + 1 < visiblePages) {
        if (startPage === 1) {
          endPage = Math.min(this.numberOfPages, visiblePages);
        } else {
          startPage = Math.max(1, this.numberOfPages - visiblePages + 1);
        }
      }
      let pages = Array.from(
        { length: endPage - startPage + 1 },
        (_, i) => startPage + i
      );

      if (startPage > 1) {
        pages.unshift(1);
        if (startPage > 2) {
          pages.splice(1, 0, "...");
        }
      }
      if (endPage < this.numberOfPages) {
        pages.push(this.numberOfPages);
        if (endPage < this.numberOfPages - 1) {
          pages.splice(pages.length - 1, 0, "...");
        }
      }
      return pages;
    },
  },
};
</script>

<style scoped>
/* Pagination Styles - Matching Table Theme */
.pagination-container {
  background: #1a1a1a;
  border: 2px solid #ffd700;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
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
  transition: transform 0.2s ease;
}

.pagination .page-item .page-link {
  display: block;
  padding: 0.5rem 0.75rem;
  border: 1px solid #ffd700;
  border-radius: 5px;
  background: #383838;
  color: #ffd700;
  min-width: 40px;
  text-align: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.pagination .page-item.active .page-link {
  background: #870606 !important;
  border-color: #b80000;
  color: #ffd700 !important;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(139, 0, 0, 0.4);
}

.pagination .page-item:hover .page-link {
  background: #2a2a2a;
  transform: translateY(-2px);
}

.pagination .page-item.disabled .page-link {
  background: #2a2a2a;
  border-color: #1f1f1f;
  color: #666 !important;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.pagination .page-item .page-link:focus {
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.25);
}

@media (max-width: 768px) {
  .pagination .page-item .page-link {
    min-width: 32px;
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
  }
}
</style>