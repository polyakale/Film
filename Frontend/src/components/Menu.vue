<template>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <!-- Toggler button -->
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Main navigation content -->
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- Left side: Menu icon -->
        <ul class="navbar-nav me-auto">
          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
            >
              <i class="bi bi-list"></i>
            </a>
            <ul class="dropdown-menu">
              <li>
                <RouterLink class="dropdown-item" to="/films">Films</RouterLink>
              </li>
              <li>
                <RouterLink class="dropdown-item" to="/people"
                  >People</RouterLink
                >
              </li>
              <li>
                <RouterLink class="dropdown-item" to="/reviews"
                  >Reviews</RouterLink
                >
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
            <a
              class="nav-link dropdown-toggle"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
            >
              <i class="bi bi-person"></i>
              <span v-if="stateAuth.user">{{ stateAuth.user }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <RouterLink
                  class="dropdown-item"
                  to="/login"
                  v-if="!stateAuth.user"
                >
                  Login
                </RouterLink>
              </li>
              <li>
                <RouterLink
                  class="dropdown-item"
                  to="/registration"
                  v-if="!stateAuth.user"
                >
                  Registration
                </RouterLink>
              </li>
              <li>
                <RouterLink
                  class="dropdown-item"
                  to="/profile"
                  v-if="stateAuth.user"
                >
                  Profile
                </RouterLink>
              </li>
              <li>
                <a
                  class="dropdown-item text-danger"
                  href="#"
                  v-if="stateAuth.user"
                  @click.prevent="logout()"
                >
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

<style>
.navbar-nav .nav-link {
  padding: 0.5rem 1rem;
}
.navbar-brand {
  font-size: 1.5rem;
  font-weight: 500;
}
.dropdown-menu {
  margin-top: 0.5rem;
}
</style>