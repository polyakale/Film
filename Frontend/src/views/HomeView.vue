<script setup>
</script>

<template>
  <main>
    <div class="middle-image"></div>
  </main>
</template>

<style scoped>
.middle-image {
  position: fixed;
  top: 0px;
  left: 200px;
  right: 200px;
  bottom: 400px;
  height: 100%;
  background: url("@/assets/filmBgImage.png") center/cover no-repeat;
  background-size: auto 70%;
  opacity: 0.9;
  z-index: -2;
  pointer-events: none;
}
</style>
