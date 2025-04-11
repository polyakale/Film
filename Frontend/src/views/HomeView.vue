<script setup>
import filmBgImage from "@/assets/filmBgImage.png";
</script>

<template>
  <main class="home-container">
    <div class="content-wrapper">
      <!-- Header Section -->
      <section class="header-section">
        <h1 class="main-title">🎥 Hungarian Interwar Film Archive</h1>
        <p class="subtitle-text">
          Dedicated to preserving and celebrating Hungarian films from the interwar period (1929–1945)
        </p>
      </section>

      <!-- Image Section -->
      <section class="image-section">
        <div class="image-wrapper">
          <img
            :src="filmBgImage"
            alt="Vintage Hungarian film reel and projector"
            loading="lazy"
            decoding="async"
            class="hero-image"
          />
        </div>
      </section>
    </div>
  </main>
</template>

<style scoped>
/* === Consistent Theme Variables === */
.home-container {
  --bg-primary: #1f1f1f;
  --bg-secondary: #2a2a2a;
  --text-primary: #ffffff;
  --text-secondary: #b0b0b0;
  --accent-gold: #ffd700;
  --border-color: #383838;
}

/* === Base Container Styles === */
.home-container {
  min-height: 90vh;
  background-color: var(--bg-primary);
  color: var(--text-primary);
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  padding: 2rem 1.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.content-wrapper {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}

/* === Header Section === */
.header-section {
  text-align: center;
  margin-bottom: 2.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.main-title {
  font-family: 'Cinzel Decorative', serif, system-ui;
  font-size: 2.2rem;
  color: var(--accent-gold);
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.3);
  margin-bottom: 1rem;
}

.subtitle-text {
  font-size: 1.1rem;
  color: var(--text-secondary);
  line-height: 1.6;
  max-width: 800px;
  margin: 0 auto;
}

/* === Image Section === */
.image-section {
  background: var(--bg-secondary);
  padding: 1rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
}

.image-wrapper {
  /* Remove overflow hiding so nothing is cut off */
  /* overflow: hidden; */
}

.hero-image {
  width: 100%;
  height: auto;
  /* Use 'object-fit: contain' to ensure the full image appears */
  object-fit: contain;
  /* Removed aspect-ratio so that the image scales fully with its natural dimensions */
}

/* === Responsive Design === */
@media (max-width: 768px) {
  .home-container {
    padding: 1.5rem 1rem;
  }

  .main-title {
    font-size: 1.8rem;
  }

  .subtitle-text {
    font-size: 1rem;
    padding: 0 1rem;
  }

  .image-section {
    padding: 0.5rem;
  }
}

@media (max-width: 480px) {
  .main-title {
    font-size: 1.6rem;
  }

  .subtitle-text {
    font-size: 0.95rem;
  }
}
</style>
