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
  <div class="container-fluid">
    <Menu />

    <img src="./assets/filmReel.png" class="imageDec" alt="...">

    <div>
      <RouterView />
    </div>
  </div>
</template>

<style scoped>

</style>