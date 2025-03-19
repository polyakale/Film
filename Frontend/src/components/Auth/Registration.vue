<template>
  <div class="container">
    <div class="row my-5">
      <div class="col-md-6 mx-auto">
        <div class="card">
          <h5 class="card-header">Registration</h5>
          <div class="card-body">
            <form @submit.prevent="userRegister">
              <!-- Name Field -->
              <div class="form-group mb-3">
                <label for="name" class="form-label">Name*</label>
                <input id="name" type="text" v-model="user.name" placeholder="Name*" class="form-control" required />
                <small v-if="nameError" class="text-danger">{{ nameError }}</small>
              </div>
              <!-- Email Field -->
              <div class="form-group mb-3">
                <label for="email" class="form-label">Email*</label>
                <input id="email" type="email" v-model="user.email" placeholder="Email*" class="form-control"
                  required />
                <small v-if="emailError" class="text-danger">{{ emailError }}</small>
              </div>
              <!-- Password Field -->
              <div class="form-group mb-3">
                <label for="password" class="form-label">Password*</label>
                <input id="password" type="password" v-model="user.password" placeholder="Password*"
                  class="form-control" required />
                <small v-if="passwordError" class="text-danger">{{ passwordError }}</small>
              </div>

              <!-- Submit Button & Status -->
              <div class="form-group mb-3">
                <div class="d-flex align-items-center">
                  <button type="submit" class="btn btn-primary me-4" :disabled="isLoading">
                    Register
                  </button>
                  <div class="spinner-border m-0 p-0" role="status" v-if="isLoading">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <span v-if="statusMessage" :class="messageClass" class="ms-2">
                    {{ statusMessage }}
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
import { useAuthStore } from "@/stores/useAuthStore";
import axios from "axios";
import { BASE_URL } from "@/helpers/baseUrls";

export default {
  data() {
    return {
      user: {
        name: "",
        email: "test@example.com",
        password: "1234567",
        // Default role: set to Guest (positionId 2)
        positionId: 2,
      },
      isLoading: false,
      statusMessage: "",
      messageClass: "",
      // Validation error messages
      nameError: "",
      emailError: "",
      passwordError: "",
      store: useAuthStore(),
    };
  },
  methods: {
    validateForm() {
      let valid = true;
      this.nameError = "";
      this.emailError = "";
      this.passwordError = "";

      if (!this.user.name.trim()) {
        this.nameError = "Name is required.";
        valid = false;
      }
      // Basic email regex validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!this.user.email || !emailRegex.test(this.user.email)) {
        this.emailError = "Please enter a valid email.";
        valid = false;
      }
      if (!this.user.password || this.user.password.length < 6) {
        this.passwordError = "Password must be at least 6 characters.";
        valid = false;
      }
      return valid;
    },
    async userRegister() {
      if (!this.validateForm()) return;

      this.statusMessage = "";
      this.isLoading = true;
      try {
        const response = await axios.post(
          `${BASE_URL}/users`,
          this.user,
          {
            headers: {
              Accept: "application/json",
              "Content-Type": "application/json",
            },
          }
        );

        // Expected API response:
        // { message: 'ok', data: { id, name, email, positionId, token } }
        const data = response.data.data;

        // Use a unified method to set auth data in your store
        this.store.setAuthData({
          id: data.id,
          name: data.name,
          email: data.email,
          positionId: data.positionId,
          token: data.token,
        });

        this.messageClass = "text-success";
        this.statusMessage = "Registration successful!";
        // Optionally, reset the form fields
        this.user = { name: "", email: "", password: "", positionId: 2 };
        // Redirect to home or dashboard as needed
        this.$router.push("/");
      } catch (error) {
        console.error("Registration error:", error);
        this.messageClass = "text-danger";
        this.statusMessage =
          error.response?.data?.message || "Registration failed";
        this.store.clearStoredData();
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
.container {
  margin-top: 2rem;
}

.text-success {
  color: #28a745;
}

.text-danger {
  color: #dc3545;
}
</style>
