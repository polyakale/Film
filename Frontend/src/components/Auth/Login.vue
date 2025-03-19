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
                  <!-- Loading Spinner -->
                  <div class="spinner-border m-0 p-0" role="status" v-if="errorMessage === '...'">
                    <span class="visually-hidden m-0">Loading...</span>
                  </div>
                  <!-- Error Message -->
                  <span v-if="errorMessage !== '...'" class="text-danger">
                    {{ errorMessage }}
                  </span>
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
import axios from "axios";
import { useAuthStore } from "@/stores/useAuthStore";
import { BASE_URL } from "@/helpers/baseUrls";

export default {
  data() {
    return {
      user: {
        email: "admin@example.com",
        password: "admin123",
      },
      store: useAuthStore(),
      errorMessage: null,
    };
  },
  methods: {
    async userAuth() {
      this.errorMessage = "..."; // Show loading state
      try {
        // Send login request
        const response = await axios.post(
          `${BASE_URL}/users/login`,
          {
            email: this.user.email,
            password: this.user.password,
          },
          {
            headers: {
              Accept: "application/json",
              "Content-Type": "application/json",
            },
          }
        );

        console.log("Login response:", response.data);

        // Destructure the nested 'data' property from the API response
        const { user, token } = response.data.data;

        // Set authentication data in the store
        this.store.setAuthData({
          id: user.id,
          name: user.name,
          email: user.email,
          positionId: user.positionId,
          token: token,
        });

        // Redirect based on user role (positionId)
        const redirectRoute =
          user.positionId === 1 ? "/admin-dashboard" : "/guest-dashboard";
        this.$router.push(redirectRoute);

        // Show success message
        this.errorMessage = "Login successful!";
      } catch (error) {
        console.error("Login error:", error);

        // Handle error messages
        this.errorMessage =
          error.response?.data?.message || // Backend error message
          error.message || // Axios error message
          "Login failed. Please try again."; // Fallback message

        // Clear stored data on error
        this.store.clearStoredData();
      }
    },
  },
};
</script>

<style scoped>
.text-danger {
  color: #dc3545;
  font-size: 0.9rem;
}
</style>