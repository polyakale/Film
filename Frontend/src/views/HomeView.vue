<script setup>
import { ref, onMounted } from "vue";

const isLoaded = ref(false);

// Simulate loading state (remove in production)
onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true;
  }, 300);
});
</script>

<template>
  <main class="hero-container">
    <div class="content-wrapper" :class="{ 'content-loaded': isLoaded }">
      <h1 class="visually-hidden">Hungarian Interwar Film Archive</h1>

      <p class="hero-text">
        <span class="highlight">This website is dedicated to preserving</span>
        <span class="highlight">and collecting old Hungarian films</span>
        <span class="highlight">from the interwar period (1930-1945).</span>
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
  </main>
</template>

<style scoped>
/* Base Styles */
.hero-container {
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
  background-color: #f5f5f5;
  background-image: linear-gradient(135deg, #f9f9f9 25%, transparent 25%),
    linear-gradient(225deg, #f9f9f9 25%, transparent 25%),
    linear-gradient(315deg, #f9f9f9 25%, transparent 25%),
    linear-gradient(45deg, #f9f9f9 25%, transparent 25%);
  background-size: 20px 20px;
  background-position: 0 0, 10px 0, 10px -10px, 0px 10px;
}

.content-wrapper {
  max-width: 1200px;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2.5rem;
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.content-loaded {
  opacity: 1;
  transform: translateY(0);
}

/* Text Styles */
.hero-text {
  font-family: "Playfair Display", serif;
  font-size: clamp(1.5rem, 4vw, 2.5rem);
  line-height: 1.4;
  text-align: center;
  color: #2c3e50;
  max-width: 800px;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  position: relative;
  z-index: 2;
  text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.8);
}

.highlight {
  padding: 0.25em 0.5em;
  background: linear-gradient(
    120deg,
    rgba(255, 215, 0, 0.2) 0%,
    rgba(255, 215, 0, 0.1) 100%
  );
  border-radius: 4px;
  display: inline-block;
}

/* Image Styles */
.image-container {
  position: relative;
  width: 100%;
  max-width: 900px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
    0 5px 10px -5px rgba(0, 0, 0, 0.04);
  transform: scale(0.98);
  transition: transform 0.3s ease;
}

.hero-image {
  width: 100%;
  height: auto;
  object-fit: cover;
  opacity: 0;
  transform: scale(1.05);
  transition: opacity 0.8s ease-out 0.2s, transform 0.8s ease-out 0.2s;
  filter: grayscale(20%) contrast(1.05) brightness(0.95);
}

.image-loaded {
  opacity: 1;
  transform: scale(1);
}

.image-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(255, 255, 255, 0.1) 0%,
    rgba(255, 255, 255, 0.3) 100%
  );
  z-index: 1;
  pointer-events: none;
}

/* Hover Effects */
@media (hover: hover) {
  .image-container:hover {
    transform: scale(1);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
      0 10px 10px -5px rgba(0, 0, 0, 0.04);
  }

  .hero-image:hover {
    filter: grayscale(0%) contrast(1.1) brightness(1);
  }
}

/* Accessibility Styles */
.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* Responsive Styles */
@media (max-width: 1024px) {
  .hero-container {
    padding: 1.5rem;
  }

  .content-wrapper {
    gap: 2rem;
  }
}

@media (max-width: 768px) {
  .hero-container {
    min-height: auto;
    padding: 1.5rem 1rem;
    background-size: 15px 15px;
  }

  .hero-text {
    font-size: clamp(1.25rem, 5vw, 1.75rem);
    gap: 0.75rem;
  }

  .image-container {
    max-width: 100%;
  }
}

@media (max-width: 480px) {
  .hero-text {
    text-align: left;
    align-items: flex-start;
  }

  .highlight {
    padding: 0.15em 0.3em;
  }
}

/* Reduced Motion Preferences */
@media (prefers-reduced-motion: reduce) {
  .content-wrapper,
  .hero-image {
    transition: none !important;
    transform: none !important;
    opacity: 1 !important;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .hero-container {
    background-color: #1a1a1a;
    background-image: linear-gradient(135deg, #2a2a2a 25%, transparent 25%),
      linear-gradient(225deg, #2a2a2a 25%, transparent 25%),
      linear-gradient(315deg, #2a2a2a 25%, transparent 25%),
      linear-gradient(45deg, #2a2a2a 25%, transparent 25%);
  }

  .hero-text {
    color: #f0f0f0;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
  }

  .highlight {
    background: linear-gradient(
      120deg,
      rgba(255, 215, 0, 0.3) 0%,
      rgba(255, 215, 0, 0.15) 100%
    );
  }

  .hero-image {
    filter: grayscale(30%) contrast(1.1) brightness(0.85);
  }
}
</style>