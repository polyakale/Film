<script setup>
import { onMounted, ref } from 'vue';

const filmBgImage = ref(null);

onMounted(() => {
  // Preload the background film image
  const img = new Image();
  img.src = '/path/to/your/filmBgImage.png'; // Replace with the actual path if needed
  img.onload = () => {
    filmBgImage.value = img;
  };
});
</script>

<template>
  <main class="home-container">
    <div class="hero-content">
      <div class="image-wrapper">
        <img
          v-if="filmBgImage"
          :src="filmBgImage.src"
          alt="Vintage Hungarian film reel and projector"
          loading="lazy"
          decoding="async"
          class="hero-image"
        />
      </div>
      <section class="header-section header-overlay">
        <h1 class="main-title">
          Hungarian Interwar Film Archive (HIFA)
        </h1>
        <p class="subtitle-text">
          Dedicated to preserving and celebrating Hungarian films from the interwar period (1929–1945)
        </p>
      </section>
    </div>
    <div class="projector-beam"></div>
    <div class="damage-overlay"></div>
  </main>
</template>

<style scoped>
/* ==================== Import Broadway Font ==================== */
@font-face {
  font-family: 'Broadway';
  src: url("/Frontend/src/assets/");
  font-weight: bold;
  font-style: normal;
}

/* ==================== Container (800x600, 4:3) ==================== */
.home-container {
  width: 800px;
  height: 600px;
  margin: 0 auto;
  background-color: #2B1A0F; /* Dark background */
  position: relative;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0;
  /* Remove opacity on the container to prevent invisibility */
}

/* ==================== Film Grain Overlay (Optional - Adjust opacity as needed) ==================== */
.home-container::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  /* Replace the Base64 string with your authentic grain texture if available */
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4XiDAAAAUVBMVEWFhYWDg4N3d3dtbW17e3t1dXWBgYGHh4d5eXlzc3OLi4ubm5uVlZWPj4+NjY19fX2JiYl/f39ra2uRkZGZmZlpaWmXl5dvb29xcXGTk5NnZ2c8TV1mAAAAG3RSTlNAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEAvEOwtAAAFVklEQVR4XpWWB67c2BUFb3g557T/hRo9/WUMZHlgr4Bg8Z4qQgQJlHI4A8SzFVrapvmTF9O7dmYRFZ60YiBhJRCgh1FYhiLAmdvX0CzTOpNE77ME0Zty/nWWzchDtiqrmQDeuv3powQ5ta2eN0FY0InkqDD73lT9c9lEzwUNqgFHs9VQce3TVClFCQrSTfOiYkVJQBmpbq2L6iZavPnAPcoU0dSw0SUTqz/GtrGuXfbyyBniKykOWQWGqwwMA7QiYAxi+IlPdqo+hYHnUt5ZPfnsHJyNiDtnpJyayNBkF6cWoYGAMY92U2hXHF/C1M8uP/ZtYdiuj26UdAdQQSXQErwSOMzt/XWRWAz5GuSBIkwG1H3FabJ2OsUOUhGC6tK4EMtJO0ttC6IBD3kM0pe0tJwMdSfjZo+EEISaeTr9P3wYrGjXqyC1krcKdhMpxEnt5JetoulscpyzhXNasa3Wh6XkaJu1JJZqWxmUqWGSuKAH2");
  background-size: cover;
  opacity: 0.25; /* Adjust this value to control intensity */
  mix-blend-mode: overlay;
  z-index: 1;
}

/* ==================== Hero Content & Image ==================== */
.hero-content {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  z-index: 2;
}

.image-wrapper {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  pointer-events: none;
}

.hero-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

/* ==================== Header Overlay with Mechanical Shaking ==================== */
.header-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  width: 100%;
  padding: 0;
  z-index: 5;
  /* Mechanical gate weave: discrete horizontal shifts ±2px every 3s, plus a brief flicker */
  animation: gateWeave 3s steps(54) infinite, flicker 0.042s steps(1) infinite;
}

@keyframes gateWeave {
  0% { transform: translate(-50%, -50%) translateX(0); }
  33% { transform: translate(-50%, -50%) translateX(2px); }
  66% { transform: translate(-50%, -50%) translateX(-2px); }
  100% { transform: translate(-50%, -50%) translateX(0); }
}

/* Flicker simulating a 24Hz carbon-arc lamp (5% dip) */
@keyframes flicker {
  0%   { opacity: 1; }
  50%  { opacity: 0.95; }
  100% { opacity: 1; }
}

/* ==================== Text Styling ==================== */
.main-title {
  font-family: 'Broadway', serif;
  font-size: 72px;
  color: #E3CA7A;
  text-shadow: 2px 2px 0px #582E1B;
  letter-spacing: 2px;
  text-transform: uppercase;
  line-height: 1;
  margin-bottom: 0.5em;
  mix-blend-mode: multiply;
  animation: flicker 0.042s steps(1) infinite, frameJitter 0.3s steps(1) infinite;
}

.subtitle-text {
  font-family: 'Broadway', serif;
  font-size: 36px;
  color: #E3CA7A;
  text-shadow: 1px 1px 0px #582E1B;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  line-height: 1;
  mix-blend-mode: multiply;
  animation: flicker 0.042s steps(1) infinite, frameJitter 0.3s steps(1) infinite;
}

/* Frame Jitter: simulate occasional 0.5px vertical jump */
@keyframes frameJitter {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(0.5px); }
}

/* ==================== Projector Beam Effect ==================== */
.projector-beam {
  position: absolute;
  top: -100%;
  left: 50%;
  width: 800px;
  height: 2000px;
  background: linear-gradient(
    to bottom,
    transparent 0%,
    rgba(255, 255, 255, 0.05) 20%,
    rgba(255, 255, 255, 0.1) 30%,
    rgba(255, 255, 255, 0.05) 40%,
    transparent 60%
  );
  transform: translateX(-50%) rotate(-20deg);
  animation: panLight 7s infinite;
  pointer-events: none;
  z-index: -1;
}

@keyframes panLight {
  0% { transform: translateX(-50%) rotate(-20deg); }
  100% { transform: translateX(-50%) rotate(-18deg); }
}

/* ==================== Damage Effects Overlay (Optional) ==================== */
.damage-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 10;
  background-image: linear-gradient(
      to right,
      transparent,
      rgba(255, 255, 255, 0.06) 10%,
      transparent 20%,
      transparent 80%,
      rgba(255, 255, 255, 0.06) 90%,
      transparent 100%
    );
  background-size: 400% 1px;
  animation: moveScratches 2s linear infinite;
  mix-blend-mode: overlay;
}

@keyframes moveScratches {
  from { background-position: 0 0; }
  to { background-position: -400% 0; }
}
</style>
