<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h2>Login</h2>
      </div>
      <div class="login-body">
        <form @submit.prevent="userAuth">
          <!-- Email Field -->
          <div class="form-group">
            <label for="email">Email</label>
            <input
              id="email"
              type="email"
              v-model="user.email"
              placeholder="Enter your email"
              class="form-control"
              required
            />
          </div>

          <!-- Password Field with Toggle -->
          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-container">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="user.password"
                placeholder="Enter your password"
                class="form-control"
                required
              />
              <span @click="showPassword = !showPassword" class="eye-icon">
                <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </span>
            </div>
          </div>

          <!-- Submit Button & Loading Spinner -->
          <div class="form-group">
            <button type="submit" class="btn-submit" :disabled="isLoading">
              Log In
            </button>
            <div v-if="isLoading" class="spinner"></div>
          </div>

          <!-- Error Message -->
          <p v-if="errorMessage" class="status-message error-message">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ errorMessage }}
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { BASE_URL } from "@/helpers/baseUrls";
import { useAuthStore } from "@/stores/useAuthStore";

// Access the store using Pinia
const store = useAuthStore();
// Access Vue Router
const router = useRouter();

const showPassword = ref(false);

// Reactive state for the login form
const user = ref({
  email: "admin@example.com",
  password: "admin123",
});
const isLoading = ref(false);
const errorMessage = ref("");

// Handle user login
const userAuth = async () => {
  isLoading.value = true;
  errorMessage.value = ""; // Clear any existing error message before login attempt

  try {
    const response = await axios.post(
      `${BASE_URL}/users/login`,
      {
        email: user.value.email,
        password: user.value.password,
      },
      {
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
      }
    );

    // Handle response and store user data
    const { user: loggedInUser, token } = response.data.data;

    // Set authentication data
    store.setAuthData({
      id: loggedInUser.id,
      name: loggedInUser.name,
      email: loggedInUser.email,
      positionId: loggedInUser.positionId,
      token: token,
    });

    // Redirect user based on role
    const redirectRoute =
      loggedInUser.positionId === 1 ? "/admin-dashboard" : "/guest-dashboard";
    router.push(redirectRoute); // Using router.push instead of this.$router.push

    // Clear any error messages in case of successful login
    errorMessage.value = "";
  } catch (error) {
    console.error("Login error:", error);
    // Only set errorMessage if there's an actual login failure
    errorMessage.value =
      error.response?.data?.message ||
      "Login failed. Please check your credentials.";
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.input-container {
  position: relative;
}

.eye-icon {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #ffd700;
  cursor: pointer;
  font-size: 1.2rem;
  z-index: 2;
}

/* === Background & Container === */
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 80vh;
  background: url("https://source.unsplash.com/1600x900/?cinema,retro")
    center/cover no-repeat;
  backdrop-filter: blur(8px);
}

/* === Login Card === */
.login-card {
  background: rgba(0, 0, 0, 0.85);
  border: 2px solid rgba(255, 215, 0, 0.4);
  padding: 2.5rem;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.5);
  color: white;
  width: 100%;
  max-width: 450px;
  text-align: center;
  /* Removed border-radius for sharp edges */
}

/* === Header === */
.login-header {
  font-family: "Cinzel Decorative", serif;
  font-size: 1.8rem;
  color: #ffd700;
  text-shadow: 1px 1px 3px rgba(255, 215, 0, 0.6);
  margin-bottom: 1rem;
}

/* === Form Fields === */
.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  font-size: 0.9rem;
  color: #f5f5f5;
  font-weight: bold;
}

.form-control {
  width: 100%;
  padding: 10px;
  font-size: 1rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  /* Removed border-radius for sharp edges */
  transition: all 0.3s ease-in-out;
}

/* === Input Focus Effect === */
.form-control:focus {
  outline: none;
  border-color: #ffd700;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

/* === Button === */
.btn-submit {
  width: 100%;
  padding: 12px;
  font-size: 1.1rem;
  font-weight: bold;
  color: black;
  background: #ffd700;
  border: none;
  /* Removed border-radius for sharp edges */
  transition: all 0.3s ease-in-out;
  cursor: pointer;
}

.btn-submit:hover {
  background: #ffc107;
  box-shadow: 0 0 15px rgba(255, 215, 0, 0.6);
}

/* === Loading Spinner === */
.spinner {
  margin: 10px auto;
  width: 30px;
  height: 30px;
  border: 4px solid rgba(255, 215, 0, 0.3);
  border-radius: 50%;
  border-top: 4px solid #ffd700;
  animation: spin 1s linear infinite;
}

/* === Status Messages === */
.status-message {
  margin-top: 10px;
}

.error-message {
  color: #ff6666;
  font-weight: bold;
}

.success-message {
  color: #28a745;
}

/* === Spinner Animation === */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

/* === Fade-in Animation === */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
