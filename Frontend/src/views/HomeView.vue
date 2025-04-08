<script setup>
// Import the image. We assume the image is in the same directory.
import filmBgImage from "@/assets/filmBgImage.png";
</script>

<template>
  <main
    class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center py-8 px-4 sm:px-6 md:px-8 lg:px-10 xl:px-12"
  >
    <div class="max-w-4xl w-full space-y-12">
      <!-- Title Section -->
      <section class="text-center">
        <p
          class="mt-6 text-base sm:text-lg md:text-xl lg:text-2xl text-gray-300"
        >
          Dedicated to preserving and celebrating Hungarian films from the interwar period (1929–1945).
        </p>
      </section>

      <!-- Responsive Image Section -->
      <section class="w-full overflow-x-auto">
        <div class="min-w-[320px] md:min-w-full">
          <img
            :src="filmBgImage"
            alt="Vintage Hungarian film reel and projector"
            loading="lazy"
            decoding="async"
            class="img-fluid"
            style="aspect-ratio: 12 / 8; object-fit: cover;"
          />
        </div>
      </section>
    </div>
  </main>
</template>

<style scoped>
/* Ensure that the main container and image are fully responsive */
main {
  box-sizing: border-box;
}

img {
  display: block;
  margin: 0 auto;
}
</style>
