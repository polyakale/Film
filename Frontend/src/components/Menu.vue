<template>
  <nav class="navbar navbar-expand-lg sticky-top" aria-label="Main navigation">
    <div class="container-fluid">
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-label="Toggle navigation"
        aria-expanded="false"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <div>
          <RouterLink to="/" class="d-flex align-items-center me-3">
            <img class="rounded" src="/iconForNav.png" alt="Logo" style="height: 40px;" />
            </RouterLink>
        </div>
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <RouterLink
              to="/films"
              class="nav-link"
              :class="{ 'active-route': $route.path === '/films' }"
            >
              <i class="bi bi-film"></i>
              <span class="nav-link-text">Films</span>
            </RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink
              to="/people"
              class="nav-link"
              :class="{ 'active-route': $route.path === '/people' }"
            >
              <i class="bi bi-people-fill"></i>
              <span class="nav-link-text">People</span>
            </RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink
              to="/reviews"
              class="nav-link"
              :class="{ 'active-route': $route.path === '/reviews' }"
            >
              <i class="bi bi-pencil-square"></i>
              <span class="nav-link-text">Reviews</span>
            </RouterLink>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle d-flex align-items-center"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-person"></i>
              <span v-if="stateAuth.user" class="username-span">
                {{ stateAuth.user }}
                <span class="online-indicator"></span>
              </span>
              <span v-else class="username-span">Account</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <RouterLink
                  class="dropdown-item"
                  to="/login"
                  v-if="!stateAuth.user"
                >
                  <i class="bi bi-box-arrow-in-right"></i>
                  Login
                </RouterLink>
              </li>
              <li>
                <RouterLink
                  class="dropdown-item"
                  to="/registration"
                  v-if="!stateAuth.user"
                >
                  <i class="bi bi-person-plus"></i>
                  Registration
                </RouterLink>
              </li>
              <li>
                <RouterLink
                  class="dropdown-item"
                  to="/profile"
                  v-if="stateAuth.user"
                >
                  <i class="bi bi-person-circle"></i>
                  Profile
                </RouterLink>
              </li>
              <li v-if="stateAuth.user">
                <a
                  class="dropdown-item"
                  href="#"
                  @click.prevent="logout()"
                  :disabled="loading"
                >
                  <i class="bi bi-box-arrow-right"></i>
                  <span v-if="!loading">Logout</span>
                  <span v-else class="logout-loading">
                    <span
                      class="spinner-border spinner-border-sm"
                      role="status"
                      aria-hidden="true"
                    ></span>
                    Logging out...
                  </span>
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
  // Register components used in the template
  components: {
    RouterLink,
  },
  // Component's reactive data
  data() {
    return {
      // Access the authentication store
      stateAuth: useAuthStore(),
      // Loading state for logout action
      loading: false,
    };
  },
  // Methods available to the component
  methods: {
    // Asynchronous logout method
    async logout() {
      const url = `${BASE_URL}/users/logout`; // Construct logout URL
      // Prepare request headers
      const headers = {
        Accept: "application/json",
        Authorization: `Bearer ${this.stateAuth.token}`, // Include auth token
      };
      this.loading = true; // Set loading state
      try {
        // Send POST request to logout endpoint
        await axios.post(url, null, { headers });
        // Clear stored authentication data on successful logout
        this.stateAuth.clearStoredData();
        // Redirect to the home page
        this.$router.push("/");
      } catch (error) {
        // Log error if logout fails
        console.error("Logout failed:", error);
        // TODO: Implement user-friendly error notification (e.g., toast)
      } finally {
        // Reset loading state regardless of success or failure
        this.loading = false;
        this.stateAuth.clearStoredData();
      }
    },
  },
};
</script>

<style scoped>
/* Dark cinematic theme with gold accents for the navbar */
.navbar {
  background: #1f1f1f !important; /* Dark background */
  border-bottom: 3px solid #383838; /* Subtle border */
  padding: 0.5rem 1rem !important; /* Navbar padding */
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5); /* Shadow for depth */
  /* sticky-top will add position: sticky, top: 0 */
  z-index: 1030; /* Ensure navbar stays above other content (Bootstrap default z-index for sticky) */
}

.container-fluid {
  padding: 0; /* Remove default fluid container padding */
}

/* Toggler button styling */
.navbar-toggler {
  border: 2px solid var(--accent-gold, #ffd700); /* Gold border */
  padding: 0.25rem 0.5rem;
}

/* Toggler icon styling (using SVG for gold color) */
.navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 215, 0, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Navigation link styling */
.nav-link {
  color: var(--text-secondary, #b0b0b0) !important; /* Default link color */
  padding: 0.5rem 1rem !important;
  border-bottom: 2px solid transparent; /* Underline effect on hover/active */
  transition: all 0.3s ease;
  font-size: 0.95rem;
  display: flex; /* Align icon and text */
  align-items: center;
}

/* Hover effect for nav links */
.nav-link:hover {
  color: var(--accent-gold, #ffd700) !important; /* Gold color on hover */
  border-bottom-color: rgba(255, 215, 0, 0.5); /* Subtle gold underline */
}

/* Icon styling within nav links */
.nav-link i {
  margin-right: 0.5rem; /* Space between icon and text */
  font-size: 1.1rem; /* Icon size */
  vertical-align: middle; /* Align icon vertically */
}

/* Active route styling */
.active-route {
  color: var(--accent-gold, #ffd700) !important; /* Gold color for active link */
  border-bottom: 2px solid var(--accent-gold, #ffd700); /* Solid gold underline */
  font-weight: bold;
}

/* Dropdown menu styling */
.dropdown-menu {
  background: #383838 !important; /* Darker background for dropdown */
  border: 2px solid #1f1f1f !important; /* Match navbar border style */
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5); /* Dropdown shadow */
  margin-top: 0.5rem !important; /* Space between toggle and menu */
}

/* Dropdown item styling */
.dropdown-item {
  color: var(--text-secondary, #b0b0b0) !important; /* Default item color */
  padding: 0.5rem 1rem !important;
  transition: all 0.2s ease;
  display: flex; /* Align icon and text */
  align-items: center;
}

/* Hover effect for dropdown items */
.dropdown-item:hover {
  background: rgba(255, 215, 0, 0.1) !important; /* Subtle gold background */
  color: var(--accent-gold, #ffd700) !important; /* Gold text color */
}

/* Icon styling within dropdown items */
.dropdown-item i {
  width: 20px; /* Fixed width for alignment */
  text-align: center;
  margin-right: 0.75rem; /* Space between icon and text */
}

/* Username display styling */
.username-span {
  color: var(--accent-gold, #ffd700); /* Gold color for username */
  font-family: "Cinzel", serif; /* Optional: Distinctive font */
  margin-left: 0.5rem; /* Space next to person icon */
}

/* Online indicator dot */
.online-indicator {
  background: #4caf50; /* Green color for online status */
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
  margin-left: 0.5rem; /* Space next to username */
  vertical-align: middle;
}

/* Logout loading spinner alignment */
.logout-loading .spinner-border-sm {
  vertical-align: middle;
  margin-right: 0.5rem;
}

/* Responsive adjustments for collapsed navbar */
@media (max-width: 991px) {
  .navbar-collapse {
    background: #1f1f1f; /* Match navbar background */
    padding: 1rem;
    margin-top: 0.5rem; /* Space below toggler */
    border: 2px solid #383838; /* Match navbar border */
    border-radius: 0.25rem; /* Slight rounding */
  }

  /* Adjust dropdown menu position/style in collapsed view */
  .dropdown-menu {
    background: #2a2a2a !important; /* Slightly lighter background */
    margin-left: 0; /* Align with parent */
    width: 100%; /* Full width */
    border: none !important; /* Remove border inside collapsed menu */
    box-shadow: none; /* Remove shadow inside collapsed menu */
  }

  .nav-link {
    padding: 0.75rem 1rem !important; /* Increase padding for touch targets */
  }
}
</style>
