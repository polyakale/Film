<script setup>
</script>

<template>
  <main class="main-container">
    <p class="text-style text-center">
      This website is to preserve and collect old Hungarian films from the time
      between the two world wars.
    </p>
    <div class="image-container">
      <img
        src="@/assets/filmBgImage.png"
        class="responsive-image"
        alt="Film background"
        loading="lazy"
        :style="{ opacity: 0.9 }"
      />
    </div>
  </main>
</template>

<style scoped>
.main-container {
  width: 100%;
  overflow: hidden;
  position: relative;
}

.text-style {
  font-size: 1.5rem;
  margin: 0px;
}

.image-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
  padding: 1rem;
  box-sizing: border-box;
}

.responsive-image {
  max-width: 100%;
  height: auto;
  object-fit: contain;
  pointer-events: none;
  user-select: none;
  transition: opacity 0.3s ease;
}

/* Responsive breakpoints */
@media (max-width: 768px) {
  .image-container {
    padding: 0.5rem;
  }

  .responsive-image {
    max-height: 60vh;
  }
}

@media (max-width: 480px) {
  .responsive-image {
    max-height: 50vh;
  }
}

/* Reduced motion preference */
@media (prefers-reduced-motion) {
  .responsive-image {
    transition: none;
  }
}
</style>
