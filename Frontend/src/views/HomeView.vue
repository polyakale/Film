<template>
  <main class="container">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link
      rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin="anonymous"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Old+Standard+TT:ital,wght@0,400;0,700;1,400&display=swap"
      rel="stylesheet"
    />
    <div class="screen">
      <img
        src="../assets/filmBgImage.png"
        alt="Vintage Hungarian film reel and projector"
        loading="lazy"
        decoding="async"
        class="film-image"
      />
      <section class="title-overlay">
        <h1>Hungarian Interwar Film Archive (H.I.F.A.)</h1>
        <p>
          Dedicated to preserving and celebrating Hungarian films from the
          interwar period (1929–1945)
        </p>
      </section>
      <div class="projector"></div>
      <div class="scratches"></div>
    </div>
  </main>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&display=swap");

/* === Main Container === */
.container {
  min-height: 91vh;
  /* Faded red wallpaper effect (semi-transparent) */
  background: linear-gradient(
    135deg,
    rgba(168, 50, 50, 0.2),
    rgba(92, 13, 13, 0.1)
  );
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem;
  overflow: hidden;
  box-sizing: border-box;
}

/* === Projection Screen === */
.screen {
  --white: #f0f0f0;
  --dark: #1c1c1c;
  --gray1: #901e1e;
  --gray2: #5c0d0d;

  width: 90%;
  max-width: 900px;
  aspect-ratio: 4/3;
  background: var(--gray1);
  position: relative;
  border-radius: 5px;
  box-shadow: inset 0 0 30px var(--dark), 0 15px 40px var(--dark);
  overflow: hidden;
}

/* Grain effect */
.screen::before {
  content: "";
  position: absolute;
  inset: 0;
  background-image: url("data:image/png;base64,...");
  opacity: 0.2;
  mix-blend-mode: overlay;
  pointer-events: none;
  z-index: 2;
}

/* === Film Image === */
.film-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  filter: grayscale(100%) contrast(120%);
  animation: flicker 4s infinite;
  z-index: 1;
}

/* === Text Overlay === */
.title-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  width: 85%;
  max-width: 600px;
  padding: 1rem;
  z-index: 3;
  animation: weave 2s infinite, flicker 0.03s infinite;
}

h1,
p {
  font-family: "Old Standard TT", serif;
  color: var(--white);
  /* Adjusted text-shadow for a less thick effect */
  text-shadow: 1px 1px 0 #000, 2px 2px 1px rgba(0, 0, 0, 0.9); /* Reduced layers and spread */
  /* Kept text-stroke, adjust if needed */
  -webkit-text-stroke: 0.5px rgba(0, 0, 0, 0.7); /* Slightly reduced stroke */
  animation: jitter 0.3s infinite; /* Keep animation */
  font-style: normal;
}

h1 {
  font-size: clamp(2rem, 5.6vw, 3.6rem);
  margin-bottom: 0.5em;
  letter-spacing: 1.5px;
  font-weight: 650;
  text-shadow: 1px 1px 0 #000, 2px 2px 0 #000, 3px 3px 0 #000,
    4px 4px 2px rgba(0, 0, 0, 0.9);
  -webkit-text-stroke: 1px rgba(0, 0, 0, 0.9);
}

p {
  font-size: clamp(1.1rem, 3.3vw, 2.1rem);
  letter-spacing: 1.3px;
  font-weight: bold;
}

/* === Projector Beam === */
.projector {
  position: absolute;
  inset: -50% 0 0 50%;
  transform: translate(-50%) perspective(500px) rotateX(20deg);
  background: radial-gradient(
    ellipse at top center,
    rgba(255, 240, 200, 0.2) 0%,
    rgba(255, 240, 200, 0.1) 30%,
    transparent 70%
  );
  mix-blend-mode: soft-light;
  animation: pan 15s infinite alternate;
  z-index: 1;
}

/* === Film Scratches Effect === */
.scratches {
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    0deg,
    transparent 0px 98%,
    rgba(255, 255, 255, 0.08) 98% 100%
  );
  background-size: 100% 4px;
  animation: move 3s linear infinite;
  pointer-events: none;
  z-index: 4;
}

/* === Animations === */
@keyframes flicker {
  0%,
  100% {
    opacity: 1;
    filter: brightness(1);
  }
  50% {
    opacity: 0.9;
    filter: brightness(0.9);
  }
}

@keyframes weave {
  0%,
  100% {
    transform: translate(-50%, -50%);
  }
  33% {
    transform: translate(-50%, -50%) translateX(1px);
  }
  66% {
    transform: translate(-50%, -50%) translateX(-1px);
  }
}

@keyframes jitter {
  50% {
    transform: translateY(0.5px);
  }
}

@keyframes pan {
  to {
    transform: translate(-50%) perspective(500px) rotateX(20deg) rotateZ(2deg);
  }
}

@keyframes move {
  to {
    background-position: 0 600px;
  }
}

/* === Responsive Improvements === */
@media (max-width: 768px) {
  .screen {
    width: 95%;
  }
  .title-overlay {
    max-width: 90%;
    padding: 0.5rem;
  }
}

@media (max-width: 480px) {
  .screen {
    aspect-ratio: 4/3;
  }
  .title-overlay {
    width: 95%;
    padding: 0.5rem;
  }
  h1 {
    font-size: 2rem;
  }
  p {
    font-size: 1.1rem;
  }
}
</style>
