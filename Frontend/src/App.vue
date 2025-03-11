<script>
import { RouterLink, RouterView } from "vue-router";
import Menu from "@/components/Menu.vue";

export default {
  components: {
    Menu,
  },
};
</script>

<template>
  <div class="page-wrapper">
    <Menu />

    <!-- Background elements -->
    <div class="filmReel-left"></div>

    <div class="filmReel-right"></div>

    <!-- Main content -->
    <div class="content">
      <RouterView />
    </div>
  </div>
</template>

<style scoped>
/* This container will grow with the content height */
.page-wrapper {
  position: relative;
  min-height: 100vh;
  overflow-x: hidden;
}

/* Left background extends with the page and repeats vertically */
.filmReel-left {
  position: fixed; /* Ensures the background stays fixed */
  top: 0;
  left: 0;
  width: 20vw;
  height: 100%; /* Ensures it spans the entire height of the page */
  background: url('@/assets/filmReel2.png') repeat-y;
  background-size: 60%; /* Adjust vertical scaling of the image */
  z-index: -1;
}

/* Right background extends with the page and repeats vertically */
.filmReel-right {
  position: fixed; /* Ensures the background stays fixed */
  top: 0;
  right: 0;
  width: 23.4vh;
  height: 100%; /* Ensures it spans the entire height of the page */
  background: url('@/assets/filmReel2.png') repeat-y;
  background-size: 100%; /* Use the natural size for tiling vertically */
  z-index: -1;
}

/* Main content margins ensure no overlap with side backgrounds */
.content {
  margin-left: 17vw; /* Ensures content is shifted to the right */
  margin-right: 17vw; /* Ensures content is shifted to the left */
}
</style>
