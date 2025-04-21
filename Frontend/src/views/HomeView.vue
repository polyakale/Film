<template>
  <main class="home-container-wrapper">
    <div class="scaling-wrapper">
      <div class="retro-screen-container">
        <div class="hero-content">
          <div class="image-wrapper">
            <img
              src="../assets/filmBgImage.png"
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
      </div>
    </div>
  </main>
</template>

<style scoped>
/* ==================== Main Wrapper ==================== */
.home-container-wrapper {
  width: 100%;
  min-height: 90vh; /* Use min-height */
  background-color: #000; /* Black theater background */
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem; /* Padding around the scaling wrapper */
  box-sizing: border-box;
  overflow: hidden; /* Prevent scrollbars */
}

/* ==================== Scaling Container (Maintains 4:3 Ratio) ==================== */
.scaling-wrapper {
  width: 100%;
  max-width: 800px; /* Max width of the screen area */
  aspect-ratio: 4 / 3; /* Enforce 4:3 aspect ratio */
  display: flex; /* Needed for inner container to fill */
}

/* ==================== Retro Screen Container (Fixed Ratio Content) ==================== */
.retro-screen-container {
  width: 100%;
  height: 100%;
  background-color: #2B1A0F; /* Dark brown background */
  position: relative; /* Context for overlays and effects */
  overflow: hidden; /* Clip effects to the screen */
  display: flex; /* Center hero-content */
  justify-content: center;
  align-items: center;
  border-radius: 5px; /* Slight rounding */
  /* Deeper shadow for screen effect */
  box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.7),
              0 15px 40px rgba(0, 0, 0, 0.9);
}

/* ==================== Film Grain Overlay ==================== */
.retro-screen-container::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  /* Using provided base64 grain */
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4XiDAAAAUVBMVEWFhYWDg4N3d3dtbW17e3t1dXWBgYGHh4d5eXlzc3OLi4ubm5uVlZWPj4+NjY19fX2JiYl/f39ra2uRkZGZmZlpaWmXl5dvb29xcXGTk5NnZ2c8TV1mAAAAG3RSTlNAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEAvEOwtAAAFVklEQVR4XpWWB67c2BUFb3g557T/hRo9/WUMZHlgr4Bg8Z4qQgQJlHI4A8SzFVrapvmTF9O7dmYRFZ60YiBhJRCgh1FYhiLAmdvX0CzTOpNE77ME0Zty/nWWzchDtiqrmQDeuv3powQ5ta2eN0FY0InkqDD73lT9c9lEzwUNqgFHs9VQce3TVClFCQrSTfOiYkVJQBmpbq2L6iZavPnAPcoU0dSw0SUTqz/GtrGuXfbyyBniKykOWQWGqwwMA7QiYAxi+IlPdqo+hYHnUt5ZPfnsHJyNiDtnpJyayNBkF6cWoYGAMY92U2hXHF/C1M8uP/ZtYdiuj26UdAdQQSXQErwSOMzt/XWRWAz5GuSBIkwG1H3FabJ2OsUOUhGC6tK4EMtJO0ttC6IBD3kM0pe0tJwMdSfjZo+EEISaeTr9P3wYrGjXqyC1krcKdhMpxEnt5JetoulscpyzhXNasa3Wh6XkaJu1JJZqWxmUqWGSuKAH2");
  background-size: cover; /* Adjust size as needed */
  opacity: 0.2; /* Adjust intensity */
  mix-blend-mode: overlay;
  pointer-events: none; /* Allow clicks through */
  z-index: 1; /* Below damage/text */
}

/* ==================== Hero Content & Image ==================== */
.hero-content {
  position: absolute; /* Position inside retro-screen */
  inset: 0; /* Cover the retro-screen */
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  z-index: 2; /* Above grain */
}

.image-wrapper {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  pointer-events: none; /* Image is background */
}

.hero-image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover; /* Cover the container */
  object-position: center;
  /* Flicker animation */
  animation: imageFlicker 4s infinite ease-in-out;
}

/* Placeholder styling */
.loading-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #555;
  font-style: italic;
}

/* ==================== Header Overlay with Mechanical Shaking ==================== */
.header-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  /* Keep transform for centering */
  transform: translate(-50%, -50%);
  text-align: center;
  width: 90%; /* Relative width */
  max-width: 600px; /* Max width */
  padding: 0; /* Remove padding, rely on text shadows */
  z-index: 15; /* Above damage overlay */
  /* Apply animations */
  animation: gateWeave 3s steps(54) infinite, textFlicker 0.042s steps(1) infinite;
  /* Remove background/backdrop/shadow from previous styles */
  background-color: transparent;
  backdrop-filter: none;
  box-shadow: none;
  border-radius: 0;
}

@keyframes gateWeave {
  0% { transform: translate(-50%, -50%) translateX(0); }
  33% { transform: translate(-50%, -50%) translateX(1px); } /* Subtle weave */
  66% { transform: translate(-50%, -50%) translateX(-1px); }
  100% { transform: translate(-50%, -50%) translateX(0); }
}

/* Flicker for text (can be same as image flicker or different) */
@keyframes textFlicker {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.95; }
}

/* ==================== Text Styling ==================== */
.main-title {
  /* Using Impact as fallback for Broadway */
  font-family: "Mr Dafoe", cursive;
  font-weight: 400;
  font-style: normal;
  font-size: clamp(10px, 8vw, 72px); /* Responsive font size */
  color: #E3CA7A; /* Creamy yellow */
  text-shadow: 2px 2px 0px #582E1B, 4px 14px 5px rgba(0,0,0,0.5); /* Brown offset + soft shadow */
  letter-spacing: 2px;
  text-transform: uppercase;
  line-height: 1;
  margin-bottom: 0.5em;
  /* Blend mode for retro feel */
  mix-blend-mode: multiply;
  /* Add frame jitter */
  animation: frameJitter 0.3s steps(1) infinite;
}

.subtitle-text {
  /* Using Impact as fallback for Broadway */
  font-family: "Mr Dafoe", cursive;
  font-weight: 400;
  font-style: normal;
  font-size: clamp(15px, 4vw, 36px); /* Responsive font size */
  color: #E3CA7A;
  text-shadow: 1px 1px 0px #582E1B, 2px 2px 3px rgba(0,0,0,0.5);
  letter-spacing: 1.5px;
  text-transform: uppercase;
  line-height: 1;
  mix-blend-mode: multiply;
  /* Add frame jitter */
  animation: frameJitter 0.3s steps(1) infinite;
}

/* Frame Jitter: simulate occasional 0.5px vertical jump */
@keyframes frameJitter {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(0.5px); }
}

/* Flicker Effect for Image */
@keyframes imageFlicker {
  0%, 100% { opacity: 1; filter: brightness(100%); }
  50% { opacity: 0.97; filter: brightness(98%); }
  75% { opacity: 0.99; filter: brightness(99%); }
}

/* ==================== Projector Beam Effect ==================== */
.projector-beam {
  position: absolute;
  top: -50%; /* Start higher */
  left: 50%;
  width: 150%; /* Wider beam */
  height: 250%; /* Longer beam */
  background: radial-gradient(
    ellipse at top center,
    rgba(255, 255, 230, 0.15) 0%, /* Softer, yellowish center */
    rgba(255, 255, 230, 0.1) 20%,
    rgba(255, 255, 230, 0.05) 40%,
    transparent 70% /* Fade out */
  );
  transform-origin: top center;
  transform: translateX(-50%) perspective(500px) rotateX(20deg); /* Perspective */
  animation: panLight 15s infinite alternate ease-in-out; /* Slower, smoother pan */
  pointer-events: none;
  z-index: 0; /* Behind grain and content */
  mix-blend-mode: overlay; /* Blend with background */
}

@keyframes panLight {
  0% { transform: translateX(-50%) perspective(500px) rotateX(20deg) rotateZ(-2deg); }
  100% { transform: translateX(-50%) perspective(500px) rotateX(20deg) rotateZ(2deg); }
}

/* ==================== Damage Effects Overlay (Vertical Scratches) ==================== */
.damage-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 10; /* Above image, below text */
  background-image: linear-gradient(
      to right,
      transparent 0%,
      rgba(255, 255, 255, 0.06) 0.5%, /* Thinner lines */
      transparent 1%,
      transparent 99%,
      rgba(255, 255, 255, 0.05) 99.5%, /* Thinner lines */
      transparent 100%
    );
  background-size: 200px 1px; /* Wider spacing */
  animation: moveScratches 3s linear infinite; /* Slower movement */
  mix-blend-mode: overlay;
}

@keyframes moveScratches {
  from { background-position: 0 0; }
  to { background-position: 0 600px; } /* Move vertically */
}

/* ==================== Responsive Refinements ==================== */
@media (max-width: 850px) {
  /* Adjust breakpoint for scaling wrapper */
  .scaling-wrapper {
    max-width: 95%; /* Allow wrapper to shrink */
  }
  .home-container-wrapper {
    padding: 0.5rem; /* Reduce padding */
  }
}

@media (max-width: 480px) {
  /* Further reduce font sizes if needed */
  .main-title {
    font-size: clamp(30px, 10vw, 40px);
    letter-spacing: 1px;
  }
  .subtitle-text {
    font-size: clamp(16px, 5vw, 20px);
    letter-spacing: 1px;
  }
  .retro-screen-container {
    border-radius: 3px;
  }
}
</style>
