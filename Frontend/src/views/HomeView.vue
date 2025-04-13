<script setup>
// Assuming filmBgImage is correctly imported from your assets
import filmBgImage from "@/assets/filmBgImage.png";
</script>

<template>
  <main class="home-container">
    <div class="hero-content">
      <div class="image-wrapper">
        <img
          :src="filmBgImage"
          alt="Vintage Hungarian film reel and projector"
          loading="lazy"
          decoding="async"
          class="hero-image"
        />
      </div>

      <section class="header-section header-overlay">
        <h1 class="main-title">🎥 Hungarian Interwar Film Archive</h1>
        <p class="subtitle-text">
          Dedicated to preserving and celebrating Hungarian films from the interwar period (1929–1945)
        </p>
      </section>
    </div>
  </main>
</template>

<style scoped>
/* === Theme Variables === */
.home-container {
  /* Using variables assumed to be defined globally or inherited */
  --overlay-bg: rgba(31, 31, 31, 0.85); /* Darker overlay from your version */
}

/* === Base Container Styles === */
.home-container {
  height: 90vh; /* Full viewport height */
  overflow: hidden; /* Prevent scrolling */
  background-color: var(--bg-primary, #1f1f1f); /* Fallback color */
  color: var(--text-primary, #ffffff); /* Fallback color */
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  /* Reintroduce padding for breathing room */
  padding: 1rem;
  display: flex; /* Use flexbox for centering */
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
}

/* === Hero Content Wrapper === */
.hero-content {
  position: relative; /* Positioning context for overlay */
  max-width: 1200px; /* Max content width */
  width: 100%;
  max-height: 100%; /* Ensure it fits within padded container */
  display: flex; /* Use flex to help center image within */
  justify-content: center;
  align-items: center;
}

/* === Image Section === */
.image-wrapper {
  width: 100%;
  max-height: 100%;
  display: flex; /* Center image if it's smaller than wrapper */
  justify-content: center;
  align-items: center;
}

.hero-image {
  display: block; /* Remove extra space below image */
  width: 100%; /* Scale width */
  height: auto; /* Maintain aspect ratio */
  object-fit: contain; /* Fit without cropping, maintain aspect ratio */
  object-position: center; /* Ensure centered if letterboxed */
  /* Adjust max-height based on container padding (1rem top + 1rem bottom) */
  max-height: calc(100vh - 2rem);
}

/* === Overlay Improvements === */
.header-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: min(90%, 800px); /* Responsive width */
  background-color: var(--overlay-bg);
  backdrop-filter: blur(4px); /* Keep blur effect */
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6); /* Keep enhanced shadow */
  text-align: center;
  color: var(--text-primary, #ffffff); /* Ensure text color contrasts with overlay */
  /* Add fade-in animation */
  animation: fadeInOverlay 0.8s ease-out forwards;
  opacity: 0; /* Start hidden for animation */
}

/* === Typography Enhancements === */
.main-title {
  font-family: 'Cinzel Decorative', serif, system-ui; /* Keep distinctive font */
  font-size: clamp(1.8rem, 4vw, 2.5rem); /* Fluid typography */
  color: var(--accent-gold, #ffd700); /* Accent color */
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.4); /* Text shadow for pop */
  line-height: 1.2;
  margin-bottom: 1rem;
}

.subtitle-text {
  font-size: clamp(0.9rem, 2vw, 1.1rem); /* Fluid typography */
  line-height: 1.5;
  color: var(--text-primary, #ffffff); /* Ensure readability */
}

/* === Keyframes for Animation === */
@keyframes fadeInOverlay {
  from {
    opacity: 0;
    transform: translate(-50%, -45%); /* Start slightly higher */
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%); /* End centered */
  }
}


/* === Responsive Refinements === */
@media (max-width: 768px) {
  .home-container {
    padding: 0.5rem; /* Reduce padding */
  }
  .hero-image {
     /* Adjust max-height for new padding (0.5rem top + 0.5rem bottom) */
     max-height: calc(90vh - 1rem);
   }
  .header-overlay {
    padding: 1.5rem;
    width: min(95%, 600px); /* Adjust width */
  }
}

@media (max-width: 480px) {
  .header-overlay {
    padding: 1.25rem;
    border-radius: 6px; /* Slightly smaller radius */
  }
  /* Font sizes adjusted via clamp(), specific overrides likely not needed */
}
</style>
