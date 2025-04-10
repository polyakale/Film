<template>
  <nav aria-label="Page navigation" class="pagination-container">
    <ul class="pagination">
      <!-- First and Previous buttons -->
      <li class="page-item" :class="{ disabled: pageNumber === 1 }" @click="paging(1)">
        <a class="page-link" href="#" aria-label="First">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
      <li class="page-item" :class="{ disabled: pageNumber === 1 }" @click="paging(pageNumber - 1)">
        <a class="page-link" href="#" aria-label="Previous">
          <span aria-hidden="true">&lsaquo;</span>
        </a>
      </li>

      <!-- Page numbers and ellipsis -->
      <li v-for="(item, index) in displayPages" :key="index" class="page-item"
          :class="{ active: item === pageNumber }">
        <!-- Render normal page numbers -->
        <template v-if="typeof item === 'number'">
          <a class="page-link" href="#" @click="paging(item)">{{ item }}</a>
        </template>
        <!-- Render ellipsis as a clickable element that reveals horizontal scrollable slider -->
        <template v-else-if="item.type === 'ellipsis'">
          <div class="ellipsis-container" @click.stop="toggleDropdown(item.side)">
            <a class="page-link" href="#">{{ '...' }}</a>
            <!-- Horizontal slider for skipped pages -->
            <div class="dropdown horizontal-slider" v-if="isDropdownVisible(item.side)">
              <div class="scroll-container">
                <div class="slider-inner">
                  <span v-for="p in getEllipsisPages(item)" :key="p" class="slider-page"
                        @click.stop="selectPageFromDropdown(p)">
                    {{ p }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </template>
      </li>

      <!-- Next and Last buttons -->
      <li class="page-item" :class="{ disabled: pageNumber === numberOfPages }" @click="paging(pageNumber + 1)">
        <a class="page-link" href="#" aria-label="Next">
          <span aria-hidden="true">&rsaquo;</span>
        </a>
      </li>
      <li class="page-item" :class="{ disabled: pageNumber === numberOfPages }" @click="paging(numberOfPages)">
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
    }
  },
  data() {
    return {
      // Manage which dropdown is open, if any
      showLeftDropdown: false,
      showRightDropdown: false,
      // Maximum number of continuous page links to show in the middle
      visiblePagesCount: 5
    };
  },
  computed: {
    // Builds an array of items representing pages to display.  
    // Normal pages are numbers; gaps are represented as objects.
    displayPages() {
      const visiblePages = this.visiblePagesCount;
      let pages = [];

      // If total pages is few enough, show all pages (no ellipsis needed)
      if (this.numberOfPages <= visiblePages + 2) {
        for (let i = 1; i <= this.numberOfPages; i++) {
          pages.push(i);
        }
        return pages;
      }

      // Calculate the main block range; first and last pages always visible
      let startPage = Math.max(2, this.pageNumber - Math.floor(visiblePages / 2));
      let endPage = Math.min(this.numberOfPages - 1, this.pageNumber + Math.floor(visiblePages / 2));

      // Adjust ranges if at the start or end
      if (this.pageNumber - Math.floor(visiblePages / 2) < 2) {
        endPage = startPage + visiblePages - 1;
      }
      if (this.pageNumber + Math.floor(visiblePages / 2) > this.numberOfPages - 1) {
        startPage = endPage - visiblePages + 1;
      }

      // Always include the first page
      pages.push(1);

      // Add left ellipsis if gap exists between first page and main block
      if (startPage > 2) {
        pages.push({ type: 'ellipsis', side: 'left', from: 2, to: startPage - 1 });
      }

      // Main pages
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
      }

      // Add right ellipsis if gap exists between main block and last page
      if (endPage < this.numberOfPages - 1) {
        pages.push({ type: 'ellipsis', side: 'right', from: endPage + 1, to: this.numberOfPages - 1 });
      }

      // Always include the last page
      pages.push(this.numberOfPages);

      return pages;
    },
  },
  methods: {
    // Emits paging event if page is valid; closes any open dropdowns.
    paging(page) {
      if (page >= 1 && page <= this.numberOfPages) {
        this.$emit("paging", { pageNumber: page });
        this.closeDropdowns();
      }
    },
    // Returns an array of page numbers that the ellipsis covers.
    getEllipsisPages(ellipsisItem) {
      let pages = [];
      for (let p = ellipsisItem.from; p <= ellipsisItem.to; p++) {
        pages.push(p);
      }
      return pages;
    },
    // Toggles the visibility of the horizontal slider based on the ellipsis side.
    toggleDropdown(side) {
      if (side === 'left') {
        this.showLeftDropdown = !this.showLeftDropdown;
        this.showRightDropdown = false; // allow only one open slider at a time
      } else if (side === 'right') {
        this.showRightDropdown = !this.showRightDropdown;
        this.showLeftDropdown = false;
      }
    },
    // Checks the dropdown visibility for a given side.
    isDropdownVisible(side) {
      return side === 'left' ? this.showLeftDropdown : this.showRightDropdown;
    },
    // Handle page selection from the slider.
    selectPageFromDropdown(page) {
      this.paging(page);
    },
    // Close both dropdown sliders.
    closeDropdowns() {
      this.showLeftDropdown = false;
      this.showRightDropdown = false;
    }
  },
  mounted() {
    // Global click listener to close dropdowns when clicking outside.
    document.addEventListener('click', this.closeDropdowns);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.closeDropdowns);
  }
};
</script>

<style scoped>
.pagination-container {
  position: relative;
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
.page-item {
  position: relative;
  cursor: pointer;
  transition: transform 0.2s ease;
}
.page-link {
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
.page-item.active .page-link {
  background: #870606 !important;
  border-color: #b80000;
  color: #ffd700 !important;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(139, 0, 0, 0.4);
}
.page-item:hover .page-link {
  background: #2a2a2a;
  transform: translateY(-2px);
}
.page-item.disabled .page-link {
  background: #2a2a2a;
  border-color: #1f1f1f;
  color: #666 !important;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}
.page-link:focus {
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.25);
}
.ellipsis-container {
  position: relative;
  display: inline-block;
}
/* Horizontal slider style */
.dropdown.horizontal-slider {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: #383838;
  border: 1px solid #ffd700;
  border-radius: 5px;
  z-index: 10;
  padding: 0.5rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  /* Allow horizontal scroll when needed */
  overflow-x: auto;
  white-space: nowrap;
  max-width: 300px;
}
.scroll-container {
  display: inline-block;
}
.slider-inner {
  display: flex;
  flex-direction: row;
  gap: 0.5rem;
}
.slider-page {
  display: inline-block;
  padding: 0.5rem 0.75rem;
  border: 1px solid #ffd700;
  border-radius: 5px;
  background: #2a2a2a;
  color: #ffd700;
  cursor: pointer;
  transition: background 0.2s ease;
}
.slider-page:hover {
  background: #1f1f1f;
}
@media (max-width: 768px) {
  .page-link {
    min-width: 32px;
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
  }
}
</style>
