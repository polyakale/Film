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
  <div>
    <Menu/>

    <!-- Left column with repeating filmReel image -->
    <div class="filmReel-left"></div>

    <!-- Right column with repeating filmReel image -->
    <div class="filmReel-right"></div>

    <!-- Your main content -->
    <div class="content">
      <RouterView />
    </div>
  </div>
</template>


<style scoped>
.filmReel-left {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 20vw;
  height: 100vh;
  background: url('@/assets/filmReel.png') repeat-y;
  background-size: auto 150%; /* scale the image larger vertically */
  z-index: -1;           /* Make sure it's behind other content */
}

.filmReel-right {
  position: fixed;
  bottom: 0;
  right: 0;
  width: 182px;
  height: 100vh;
  background: url('@/assets/filmReel.png') repeat-y;
  background-size: auto 150%; /* scale the image larger vertically */
  z-index: -1;
}

/* Optional: Adjust your content area so it doesn't overlap the fixed columns */
.content {
  margin-left: 17vw;
  margin-right: 17vw;
}
</style>
