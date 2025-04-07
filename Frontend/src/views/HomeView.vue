<script setup>
// Import the image.  We assume the image is in the same directory.
import filmBgImage from '@/assets/filmBgImage.png';
</script>

<template>
  <main class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full space-y-8">
      <div class="text-center">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-red-400 to-yellow-500">
          Hungarian Interwar Film Archive
        </h1>
        <p class="mt-4 text-lg sm:text-xl text-gray-300">
          This website is dedicated to preserving and celebrating Hungarian films from the interwar period (1930-1945).
        </p>
      </div>

      <div aria-hidden="true" class="w-full">
        <img
          :src="filmBgImage"
          alt="Vintage Hungarian film reel and projector"
          loading="lazy"
          decoding="async"
          class="w-full rounded-xl shadow-2xl border border-gray-700/50"
          style="aspect-ratio: 12 / 8; object-fit: cover;"
        />
      </div>

      <div class="text-center py-8">
        <p className="text-gray-400 text-base sm:text-lg">
          Explore the rich history of Hungarian cinema.
        </p>
      </div>
    </div>
  </main>
</template>

<style scoped>
/*
 * Scoped styles apply only to this component.
 * We're using Tailwind CSS, so we'll add some custom styles
 * to ensure the desired layout and prevent horizontal scrolling.
 */

/* Ensure the main content takes up the full viewport height */
main {
  display: flex;
  flex-direction: column; /* Stack items vertically */
  align-items: center;
  justify-content: center; /* Center content vertically */
  padding-top: 3rem;
  padding-bottom: 3rem;
  box-sizing: border-box; /* Include padding in element's total width and height */
  overflow-x: hidden; /* Prevent horizontal scrollbar */
}

div {
  margin-left: auto;
  margin-right: auto;
}

h1 {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  color: white;
  text-align: center;
}

p {
  font-size: 1.1rem;
  line-height: 1.75;
  color: #d1d5db;
  text-align: center;
  margin-bottom: 2rem;
}

img {
  max-width: 100%;
  height: auto;
  border-radius: 0.75rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  display: block;
  margin: 0 auto;
}
</style>
