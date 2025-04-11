<template>
  <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
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

        <div class="navbar-nav mx-auto">
          <RouterLink to="/" class="d-flex align-items-center">
            <img src="../public/icon.png" />
          </RouterLink>
        </div>

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
  data() {
    return {
      stateAuth: useAuthStore(),
      loading: false,
    };
  },
  methods: {
    async logout() {
      const url = `${BASE_URL}/users/logout`;
      const headers = {
        Accept: "application/json",
        Authorization: `Bearer ${this.stateAuth.token}`,
      };

      this.loading = true;
      try {
        await axios.post(url, null, { headers });
        this.stateAuth.clearStoredData();
        this.$router.push("/");
      } catch (error) {
        console.error("Logout failed:", error);
        // Add your preferred toast/notification system here
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Dark cinematic theme with gold accents */
.navbar {
  background: #1f1f1f !important;
  border-bottom: 3px solid #383838;
  padding: 0.5rem 1rem !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

.container-fluid {
  padding: 0;
}

.navbar-toggler {
  border: 2px solid #ffd700;
  padding: 0.25rem 0.5rem;
}

.navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 215, 0, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.navbar-brand.gradient-title {
  background-image: url("../public/icon.png");
  transition: all 0.3s ease;
}

.navbar-brand.gradient-title:hover {
  text-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
}

.nav-link {
  color: #b0b0b0 !important;
  padding: 0.5rem 1rem !important;
  border-bottom: 2px solid transparent;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.nav-link:hover {
  color: #ffd700 !important;
  border-bottom-color: rgba(255, 215, 0, 0.5);
}

.nav-link i {
  margin-right: 0.5rem;
  font-size: 1.1rem;
}

.active-route {
  color: #ffd700 !important;
  border-bottom: 2px solid #ffd700;
  font-weight: bold;
}

.dropdown-menu {
  background: #383838 !important;
  border: 2px solid #1f1f1f !important;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
}

.dropdown-item {
  color: #b0b0b0 !important;
  padding: 0.5rem 1rem !important;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background: rgba(255, 215, 0, 0.1) !important;
  color: #ffd700 !important;
}

.dropdown-item i {
  width: 20px;
  text-align: center;
  margin-right: 0.75rem;
}

.username-span {
  color: #ffd700;
  font-family: "Cinzel", serif;
  margin-left: 0.5rem;
}

.online-indicator {
  background: #4caf50;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
  margin-left: 0.5rem;
}

.logout-item {
  color: #cc181e !important;
}

.logout-item:hover {
  background: rgba(204, 24, 30, 0.1) !important;
}

.logout-loading {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

@media (max-width: 991px) {
  .navbar-collapse {
    background: #1f1f1f;
    padding: 1rem;
    margin-top: 0.5rem;
    border: 2px solid #383838;
  }

  .navbar-nav {
    gap: 0.5rem;
  }

  .dropdown-menu {
    background: #2a2a2a !important;
    margin-left: 1rem;
    width: calc(100% - 2rem);
  }
}
</style>
