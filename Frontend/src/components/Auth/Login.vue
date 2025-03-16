<template>
  <div class="container">
    <div class="row my-5">
      <div class="col-md-6 mx-auto">
        <div class="card">
          <h5 class="card-header">Login</h5>
          <div class="card-body">
            <form @submit.prevent="userAuth">
              <!-- Email Field -->
              <div class="form-group mb-3">
                <input type="text" v-model="user.email" placeholder="Email*" class="form-control" />
              </div>

              <!-- Role Selection (Radio Buttons) -->
              <div class="form-group mb-3">
                <label class="d-block mb-2">Login as*</label>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" v-model="user.positionId" id="loginAdmin" :value="1" />
                  <label class="form-check-label" for="loginAdmin">
                    Admin
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" v-model="user.positionId" id="loginGuest" :value="2" />
                  <label class="form-check-label" for="loginGuest">
                    Guest
                  </label>
                </div>
              </div>

              <!-- Password Field -->
              <div class="form-group mb-3">
                <input type="password" v-model="user.password" placeholder="Password*" class="form-control" />
              </div>

              <!-- Submit Button & Status -->
              <div class="form-group mb-3">
                <div class="d-flex align-items-center">
                  <button type="submit" class="btn btn-primary me-4">
                    Login
                  </button>
                  <div class="spinner-border m-0 p-0" role="status" v-if="errorMessage === '...'">
                    <span class="visually-hidden m-0">Loading...</span>
                  </div>
                  <span v-if="errorMessage !== '...'">{{ errorMessage }}</span>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "@/stores/useAuthStore";
import axios from "axios";
import { BASE_URL } from "@/helpers/baseUrls";

export default {
  data() {
    return {
      user: {
        email: "test@example.com",
        password: "1234567",
        positionId: 2, // Default to Guest (id:2)
      },
      store: useAuthStore(),
      errorMessage: null,
    };
  },
  methods: {
    async userAuth() {
      this.errorMessage = "...";
      try {
        const response = await axios.post(
          `${BASE_URL}/users/login`,
          {
            email: this.user.email,
            password: this.user.password,
            positionId: this.user.positionId, // Include positionId in the request
          },
          {
            headers: {
              Accept: "application/json",
              "Content-Type": "application/json",
            },
          }
        );

        // Save user data to store
        this.store.setId(response.data.data.id);
        this.store.setUser(response.data.data.name);
        this.store.setPositionId(response.data.data.positionId);
        this.store.setToken(response.data.data.token);

        // Redirect based on positionId
        switch (response.data.data.positionId) {
          case 1: // Admin
            this.$router.push("/admin-dashboard");
            break;
          case 2: // Guest
            this.$router.push("/guest-dashboard");
            break;
          default:
            this.$router.push("/");
        }

        this.errorMessage = "Login successful!";
      } catch (error) {
        console.error("Login error:", error);
        this.errorMessage = error.response?.data?.message || "Login failed";
        this.store.clearStoredData();
      }
    },
  },
};
</script>

<style scoped>
/* Add any additional styles if needed */
</style>