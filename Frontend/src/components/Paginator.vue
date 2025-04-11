<template>
  <nav aria-label="Page navigation" class="pagination-container">
    <ul class="pagination">
      <li class="page-item" :class="{ disabled: pageNumber === 1 }" @click="paging(1)">
        <a class="page-link" href="#" aria-label="First" title="First Page">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
      <li class="page-item" :class="{ disabled: pageNumber === 1 }" @click="paging(pageNumber - 1)">
        <a class="page-link" href="#" aria-label="Previous" title="Previous Page">
          <span aria-hidden="true">&lsaquo;</span>
        </a>
      </li>

      <li v-for="(item, index) in displayPages" :key="index" class="page-item"
          :class="{ active: item === pageNumber, 'ellipsis-item': typeof item === 'object' }">
        <template v-if="typeof item === 'number'">
          <a class="page-link" href="#" @click.prevent="paging(item)">{{ item }}</a>
        </template>
        <template v-else-if="item.type === 'ellipsis'">
          <div class="ellipsis-container" @click.stop="toggleDropdown(item.side)">
            <a class="page-link ellipsis-link" href="#" @click.prevent title="Select page range">...</a>
            <div class="dropdown horizontal-slider" v-if="isDropdownVisible(item.side)" @click.stop>
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

      <li class="page-item" :class="{ disabled: pageNumber === numberOfPages }" @click="paging(pageNumber + 1)">
        <a class="page-link" href="#" aria-label="Next" title="Next Page">
          <span aria-hidden="true">&rsaquo;</span>
        </a>
      </li>
      <li class="page-item" :class="{ disabled: pageNumber === numberOfPages }" @click="paging(numberOfPages)">
        <a class="page-link" href="#" aria-label="Last" title="Last Page">
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
      // Manage which dropdown is open, if any ('left', 'right', or null)
      openDropdownSide: null,
      // Maximum number of continuous page links to show in the middle
      visiblePagesCount: 5 // Adjust as needed (e.g., 3, 5, 7)
    };
  },
  computed: {
    // Builds an array of items representing pages to display.
    // Normal pages are numbers; gaps are represented as objects.
    displayPages() {
      const total = this.numberOfPages;
      const current = this.pageNumber;
      const width = this.visiblePagesCount; // How many page numbers to show in the middle
      const edgeWidth = 1; // Always show the first and last page number

      if (total <= width + (edgeWidth * 2)) {
        // If total pages is small enough, show all pages
        return Array.from({ length: total }, (_, i) => i + 1);
      }

      let pages = [];
      const mainBlockStart = Math.max(edgeWidth + 1, current - Math.floor((width - 1) / 2));
      const mainBlockEnd = Math.min(total - edgeWidth, current + Math.ceil((width - 1) / 2));

      // Adjust block if near the beginning
      const startAdjust = Math.max(0, (edgeWidth + width + 1) - mainBlockEnd);
      const adjustedStart = Math.max(edgeWidth + 1, mainBlockStart - startAdjust);

      // Adjust block if near the end
      const endAdjust = Math.max(0, (mainBlockStart + width) - (total - edgeWidth));
      const adjustedEnd = Math.min(total - edgeWidth, mainBlockEnd + endAdjust);

      // First page(s)
      for (let i = 1; i <= edgeWidth; i++) {
        pages.push(i);
      }

      // Left ellipsis
      if (adjustedStart > edgeWidth + 1) {
        pages.push({ type: 'ellipsis', side: 'left', from: edgeWidth + 1, to: adjustedStart - 1 });
      }

      // Main block of pages
      for (let i = adjustedStart; i <= adjustedEnd; i++) {
        pages.push(i);
      }

      // Right ellipsis
      if (adjustedEnd < total - edgeWidth) {
         pages.push({ type: 'ellipsis', side: 'right', from: adjustedEnd + 1, to: total - edgeWidth });
      }

      // Last page(s)
      for (let i = total - edgeWidth + 1; i <= total; i++) {
         pages.push(i);
      }

      return pages;
    },
  },
  methods: {
    // Emits paging event if page is valid; closes any open dropdowns.
    paging(page) {
      const newPage = Number(page); // Ensure it's a number
      if (newPage >= 1 && newPage <= this.numberOfPages && newPage !== this.pageNumber) {
        this.$emit("paging", { pageNumber: newPage });
      }
       this.closeDropdowns(); // Close dropdown after any paging action attempt
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
      this.openDropdownSide = this.openDropdownSide === side ? null : side;
    },
    // Checks the dropdown visibility for a given side.
    isDropdownVisible(side) {
      return this.openDropdownSide === side;
    },
    // Handle page selection from the slider.
    selectPageFromDropdown(page) {
      this.paging(page); // This will also close the dropdown
    },
    // Close dropdown sliders.
    closeDropdowns(event) {
       // Check if the click was outside the ellipsis containers
       if (event && event.target.closest('.ellipsis-container')) {
            return; // Don't close if click is inside an ellipsis container
       }
       this.openDropdownSide = null;
    }
  },
  mounted() {
    // Global click listener to close dropdowns when clicking outside.
    document.addEventListener('click', this.closeDropdowns);
  },
  beforeUnmount() { // Use beforeUnmount for Vue 3
    document.removeEventListener('click', this.closeDropdowns);
  }
};
</script>

<style scoped>
/* Inherit font from parent or set system font */
.pagination-container {
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  padding: 0.5rem; /* Add some padding inside the container */
  display: inline-block; /* Allow container to size to content */
}

.pagination {
  list-style: none;
  display: flex;
  justify-content: center; /* Center pagination items */
  align-items: center;
  gap: 0.3rem; /* Slightly reduced gap */
  padding: 0;
  margin: 0;
}

.page-item {
  /* position: relative; */ /* Removed as dropdown positioning is handled differently */
  cursor: pointer;
  transition: opacity 0.2s ease; /* Use opacity for disabled state */
  border-radius: 4px; /* Match button rounding */
}

.page-link {
  display: block;
  padding: 0.5rem 0.85rem; /* Adjusted padding */
  /* Consistent background, border, text color */
  background: #2a2a2a;
  border: 1px solid #383838;
  color: #b0b0b0;
  min-width: 38px; /* Slightly adjusted min-width */
  text-align: center;
  text-decoration: none;
  border-radius: 4px; /* Match button rounding */
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
  line-height: 1.4; /* Ensure consistent height */
  font-weight: 600; /* Slightly bolder text */
}

/* Hover state */
.page-item:not(.disabled):not(.active):hover .page-link {
  background: #383838; /* Lighter dark bg */
  border-color: #555; /* Slightly lighter border */
  color: #ffd700; /* Gold text on hover */
}

/* Active state - Gold background */
.page-item.active .page-link {
  background: #ffd700 !important;
  border-color: #ffd700 !important;
  color: #1f1f1f !important; /* Dark text on gold */
  font-weight: 700; /* Boldest */
  box-shadow: 0 1px 5px rgba(255, 215, 0, 0.3);
  cursor: default;
}

/* Disabled state */
.page-item.disabled {
    cursor: not-allowed;
    opacity: 0.5; /* Fade out disabled items */
}
.page-item.disabled .page-link {
  /* Keep styles subtle for disabled */
  background: #2a2a2a;
  border-color: #383838;
  color: #666 !important;
  box-shadow: none;
}

/* Focus state (accessibility) */
.page-link:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.3); /* Gold focus ring */
  z-index: 2; /* Ensure focus ring is visible */
}

/* Ellipsis specific styling */
.ellipsis-item .page-link {
    font-weight: bold;
    letter-spacing: 1px;
}

.ellipsis-container {
  position: relative; /* Needed for absolute positioning of the dropdown */
  display: block; /* Ensure it takes up space like other items */
}

/* Horizontal slider dropdown */
.dropdown.horizontal-slider {
  position: absolute;
  /* Position above the ellipsis */
  bottom: calc(100% + 5px); /* 5px gap */
  left: 50%;
  transform: translateX(-50%);
  /* Styling consistent with theme */
  background: #1f1f1f; /* Darker background */
  border: 1px solid #383838; /* Dark border */
  border-radius: 5px;
  z-index: 10;
  padding: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5); /* Consistent shadow */
  overflow-x: auto; /* Allow horizontal scroll */
  overflow-y: hidden;
  white-space: nowrap;
  max-width: 280px; /* Limit width */
  max-height: 60px; /* Limit height if needed */
  scrollbar-width: thin; /* For Firefox */
  scrollbar-color: #555 #383838; /* Scrollbar colors */
}
/* Webkit scrollbar styling */
.dropdown.horizontal-slider::-webkit-scrollbar {
  height: 6px;
}
.dropdown.horizontal-slider::-webkit-scrollbar-track {
  background: #2a2a2a;
  border-radius: 3px;
}
.dropdown.horizontal-slider::-webkit-scrollbar-thumb {
  background-color: #555;
  border-radius: 3px;
  border: 1px solid #2a2a2a;
}
.dropdown.horizontal-slider::-webkit-scrollbar-thumb:hover {
  background-color: #777;
}


.scroll-container {
  display: inline-block; /* Allows container to size to content */
}

.slider-inner {
  display: flex;
  flex-direction: row;
  gap: 0.4rem; /* Gap between slider pages */
}

.slider-page {
  display: inline-block;
  padding: 0.4rem 0.7rem; /* Slightly smaller padding */
  /* Consistent styling with page links */
  background: #2a2a2a;
  border: 1px solid #383838;
  color: #b0b0b0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem; /* Slightly smaller font */
  font-weight: 600;
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.slider-page:hover {
  background: #383838;
  border-color: #555;
  color: #ffd700; /* Gold text on hover */
}

/* Responsive adjustments */
@media (max-width: 768px) {
  /* Reduce visible pages on smaller screens if desired */
  /* Example: this.visiblePagesCount could be adjusted based on screen width in JS, */
  /* or just accept fewer visible numbers due to space constraints */

  .page-link {
    min-width: 34px; /* Adjust min-width */
    padding: 0.4rem 0.6rem; /* Smaller padding */
    font-size: 0.9rem; /* Smaller font */
  }
  .pagination {
      gap: 0.2rem; /* Tighter gap */
  }
  .pagination-container {
      padding: 0.4rem;
  }
}

@media (max-width: 480px) {
    /* Further adjustments for very small screens */
    .page-link {
        min-width: 30px;
        padding: 0.3rem 0.5rem;
        font-size: 0.85rem;
    }
    .pagination {
        gap: 0.1rem;
    }
     /* Hide First/Last buttons on very small screens to save space */
    .page-item:first-child,
    .page-item:last-child {
        /* display: none; */ /* Uncomment to hide */
    }
}

</style>
