<script setup>

</script>

<template>
  <div>
    <h1>Hungarian Interwar Film Archive</h1>

    <p>
      This website is dedicated to preserving and collecting old Hungarian films
      from the interwar period (1930-1945).
    </p>

    <div class="image-container" aria-hidden="true">
      <img
        src="@/assets/filmBgImage.png"
        alt="Vintage Hungarian film reel and projector"
        class="hero-image"
        :class="{ 'image-loaded': isLoaded }"
        loading="lazy"
        decoding="async"
        width="1200"
        height="800"
      />
      <div class="image-overlay"></div>
    </div>
  </div>
</template>

<style scoped>
</style>