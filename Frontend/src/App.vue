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
    
    <div class="p-2">
      <RouterView />
    </div>
  </div>
</template>

<style scoped>
</style>