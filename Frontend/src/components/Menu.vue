<template>
  <nav class="navbar navbar-expand-lg bg-body-tertiary" style="padding: 0">
    <div class="container-fluid">
      <!-- Toggler button -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Main navigation content -->
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- Left side: Menu icon -->
        <ul class="navbar-nav me-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-list"></i>
            </a>
            <ul class="dropdown-menu">
              <li>
                <RouterLink class="dropdown-item" to="/films">
                  <i class="bi bi-film"></i>
                  Films
                </RouterLink>
              </li>
              <li>
                <RouterLink class="dropdown-item" to="/people">
                  <i class="bi bi-people-fill"></i>
                  People
                </RouterLink>
              </li>
              <li>
                <RouterLink class="dropdown-item" to="/reviews">
                  <i class="bi bi-pencil-square"></i>
                  Reviews
                </RouterLink>
              </li>
            </ul>
          </li>
        </ul>

        <!-- Center: Title -->
        <div class="navbar-nav mx-auto">
          <RouterLink class="nav-link active fw-bold fs-4" to="/">
            Old Hungarian Films
          </RouterLink>
        </div>

        <!-- Right side: Profile -->
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person"></i>
              <span v-if="stateAuth.user">{{ stateAuth.user }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <RouterLink class="dropdown-item" to="/login" v-if="!stateAuth.user">
                  <i class="bi bi-box-arrow-in-right"></i>
                  Login
                </RouterLink>
              </li>
              <li>
                <RouterLink class="dropdown-item" to="/registration" v-if="!stateAuth.user">
                  <i class="bi bi-person-plus"></i>
                  Registration
                </RouterLink>
              </li>
              <li>
                <RouterLink class="dropdown-item" to="/profile" v-if="stateAuth.user">
                  <i class="bi bi-person-circle"></i>
                  Profile
                </RouterLink>
              </li>
              <li>
                <a class="dropdown-item logout-item" href="#" v-if="stateAuth.user" @click.prevent="logout()">
                  <i class="bi bi-box-arrow-right"></i>
                  Logout
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import { RouterLink } from "vue-router";
import { useAuthStore } from "@/stores/useAuthStore";
import axios from "axios";
import { BASE_URL } from "@/helpers/baseUrls";

export default {
  data() {
    return {
      stateAuth: useAuthStore(),
    };
  },
  methods: {
    async logout() {
      const url = `${BASE_URL}/users/logout`;
      const headers = {
        Accept: "application/json",
        Authorization: `Bearer ${this.stateAuth.token}`,
      };

      try {
        await axios.post(url, null, { headers });
      } catch (error) {
        console.log(error);
      }
      this.stateAuth.clearStoredData();
      this.$router.push("/");
    },
  },
};
</script>

<style scoped>
/* Improved spacing system */
:root {
  --nav-padding: 2.5rem;
  --nav-item-spacing: 1rem;
}

/* Navbar container */
.navbar {
  background: rgba(0, 0, 0, 0.7) !important;
  padding: 0 var(--nav-padding) !important;
}

/* Left side menu */
.navbar-nav.me-auto {
  margin-left: var(--nav-item-spacing) !important;
}

/* Right side profile */
.navbar-nav.ms-auto {
  margin-right: var(--nav-item-spacing) !important;
}

/* Dropdown menus */
.dropdown-menu {
  background: rgba(32, 6, 6, 0.7) !important;
  border-radius: 0 !important;
  /* Removes rounded edges */
  box-shadow: none !important;
  /* Removes shadow */
  border: none !important;
  margin-top: 0.4rem !important;
  text-decoration: none;
}

/* Navigation links */
.nav-link {
  padding: 0.75rem 1rem !important;
  color: white !important;
  transition: opacity 0.2s ease;
}

.nav-link:hover {
  opacity: 0.8;
}

/* Dropdown items */
.dropdown-item {
  padding: 0.5rem 1rem !important;
  color: white !important;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background: rgba(255, 255, 255, 0.1) !important;
  color: white !important;
}

/* Active state */
.nav-link.active {
  font-weight: bold;
  font-family: 'Times New Roman', Times, serif;
}

/* User name */
.navbar-nav span {
  margin-left: 0.75rem !important;
  font-weight: 500;
}

/* Toggler button */
.navbar-toggler {
  border-color: rgba(255, 255, 255, 0.5);
  margin-right: var(--nav-item-spacing);
}

.navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
  transition: opacity 0.2s ease;

}

.navbar-toggler:hover .navbar-toggler-icon {
  opacity: 0.8;
}
</style>