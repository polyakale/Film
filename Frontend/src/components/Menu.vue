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
  background: rgba(0, 0, 0, 0.9) !important;
  /* Darker background for better contrast */
  padding: 0 var(--nav-padding) !important;
  border-bottom: 1px solid rgba(255, 215, 0, 0.2);
  opacity: 80%;
  /* Golden border for cinematic feel */
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
  background: rgba(20, 20, 20, 0.98) !important;
  /* Darker background for dropdown */
  border: 1px solid rgba(255, 215, 0, 0.3) !important;
  /* Golden border */
  border-radius: 4px !important;
  /* Slightly rounded corners */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  /* Subtle shadow for depth */
  margin-top: 0.8rem !important;
  min-width: 220px;
  /* Ensure consistent width */
  animation: dropdownEntrance 0.3s ease;
  /* Smooth dropdown animation */
}

@keyframes dropdownEntrance {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Dropdown items */
.dropdown-item {
  color: #e0e0e0 !important;
  /* Light gray text for better readability */
  padding: 0.75rem 1.5rem !important;
  /* Increased padding for better spacing */
  font-family: 'Alegreya Sans', sans-serif;
  /* Elegant font */
  font-size: 1.1rem;
  /* Slightly larger font size */
  transition: all 0.3s ease;
  /* Smooth hover transition */
  display: flex;
  align-items: center;
  gap: 12px;
  /* Space between icon and text */
  position: relative;
  /* For hover effect */
}

/* Golden hover indicator */
.dropdown-item::before {
  content: '';
  position: absolute;
  left: -4px;
  top: 50%;
  transform: translateY(-50%);
  height: 60%;
  width: 3px;
  background: #ffd700;
  /* Golden accent */
  opacity: 0;
  /* Hidden by default */
  transition: opacity 0.3s ease;
}

.dropdown-item:hover {
  background: rgba(255, 215, 0, 0.08) !important;
  /* Subtle golden hover */
  padding-left: 2rem !important;
  /* Animated padding on hover */
  color: #ffd700 !important;
  /* Golden text on hover */
}

.dropdown-item:hover::before {
  opacity: 1;
  /* Show golden indicator on hover */
}

/* Icons */
.dropdown-item i {
  font-size: 1.2rem;
  /* Slightly larger icons */
  width: 24px;
  /* Consistent icon width */
  text-align: center;
  /* Center icons */
}

/* Separator */
.dropdown-divider {
  border-color: rgba(255, 215, 0, 0.15);
  /* Golden divider */
  margin: 0.5rem 0;
  /* Spacing around divider */
}

/* Special logout item */
.dropdown-item.logout-item {
  color: #ff6666 !important;
  /* Red for logout */
}

.dropdown-item.logout-item:hover {
  background: rgba(255, 102, 102, 0.1) !important;
  /* Red hover background */
}

/* Profile section */
.navbar-nav span {
  font-family: 'Cinzel Decorative', cursive;
  /* Vintage font for username */
  letter-spacing: 0.05em;
  /* Slightly spaced letters */
  color: #ffd700;
  /* Golden username */
}

/* Active title */
.nav-link.active {
  font-size: 1.8rem;
  /* Larger title */
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
  /* Subtle text shadow */
  letter-spacing: 0.05em;
  /* Spaced letters for vintage feel */
  color: #ffd700 !important;
  /* Golden title */
}

/* Toggler button */
.navbar-toggler {
  border-color: rgba(255, 255, 255, 0.5);
  /* Light border */
  margin-right: var(--nav-item-spacing);
}

.navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 215, 0, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
  /* Golden toggler icon */
  transition: opacity 0.2s ease;
}

.navbar-toggler:hover .navbar-toggler-icon {
  opacity: 0.8;
  /* Slightly transparent on hover */
}

/* Navigation links */
.nav-link {
  padding: 0.75rem 1rem !important;
  color: white !important;
  transition: opacity 0.2s ease;
}

.nav-link:hover {
  opacity: 0.8;
  /* Slightly transparent on hover */
}
</style>