<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h2>Login</h2>
      </div>
      <div class="login-body">
        <form @submit.prevent="userAuth">
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              type="email"
              v-model="user.email"
              placeholder="Enter your email"
              class="form-control"
              required
              aria-label="Email input"
            />
          </div>

          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-container">
              <input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="user.password"
                placeholder="Enter your password"
                class="form-control"
                required
                aria-label="Password input"
              />
              <span
                @click="showPassword = !showPassword"
                class="eye-icon"
                title="Toggle password visibility"
              >
                <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </span>
            </div>
          </div>

          <div class="form-group submit-group">
            <button type="submit" class="btn btn-submit" :disabled="isLoading">
              <span v-if="!isLoading">Log In</span>
              <div
                v-if="isLoading"
                class="spinner"
                role="status"
                aria-label="Loading"
              ></div>
            </button>
          </div>

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
import { ref } from "vue"; // Removed unused 'computed'
import { useRouter } from "vue-router";
// Ensure these helpers/stores are correctly imported in your project
import { BASE_URL } from "@/helpers/baseUrls";
import { useAuthStore } from "@/stores/useAuthStore";

// Access the store using Pinia
const store = useAuthStore();
// Access Vue Router
const router = useRouter();

// Reactive state for password visibility
const showPassword = ref(false);

// Reactive state for the login form
const user = ref({
  email: "admin@example.com", // Default/Test values
  password: "admin123",      // Default/Test values
});
const isLoading = ref(false);
const errorMessage = ref("");

// Handle user login
const userAuth = async () => {
  isLoading.value = true;
  errorMessage.value = ""; // Clear previous errors

  try {
    // Send login request
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

    // Handle successful login response
    const { user: loggedInUser, token } = response.data.data;

    // Store authentication data using Pinia store action
    store.setAuthData({
      id: loggedInUser.id,
      name: loggedInUser.name,
      email: loggedInUser.email,
      positionId: loggedInUser.positionId,
      token: token,
    });

    // Determine redirect route based on user role (positionId)
    // Assuming positionId 1 is Admin, others are Guest
    const redirectRoute =
      loggedInUser.positionId === 1 ? "/admin-dashboard" : "/guest-dashboard";
    router.push(redirectRoute); // Navigate to the appropriate dashboard

  } catch (error) {
    // Log the error for debugging
    console.error("Login error:", error);
    // Display user-friendly error message from response or a default message
    errorMessage.value =
      error.response?.data?.message ||
      "Login failed. Please check your credentials.";
  } finally {
    // Ensure loading state is reset
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* Styles adapted from stuffForAI.txt (Profile Component) */

/* === Core Theme Colors & Fonts === */
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 90vh;
  background-color: #111;
  background-image: url("https://source.unsplash.com/1600x900/?dark,abstract,texture");
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  padding: 1rem;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
    Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
}

.login-card {
  background: rgba(31, 31, 31, 0.95); /* Slightly less transparent */
  border: 1px solid #444; /* Slightly lighter border */
  padding: 2.5rem 3rem; /* Increased padding */
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.8); /* Stronger shadow */
  color: #fefefe;
  width: 100%;
  max-width: 500px; /* Keep larger size */
  border-radius: 8px; /* Slightly larger border radius */
  text-align: center;
  backdrop-filter: blur(5px); /* Added blur effect */
}

/* === Header === */
.login-header {
  text-align: center;
  margin-bottom: 2.5rem; /* Increased margin */
  padding-bottom: 1.2rem; /* Increased padding */
  border-bottom: 2px solid #ffd700; /* Thicker gold border */
}
.login-header h2 {
  font-family: "Cinzel Decorative", serif, system-ui;
  font-size: 2.2rem; /* Slightly larger font size */
  color: #ffd700;
  text-shadow: 0 0 10px rgba(255, 215, 0, 0.7); /* Stronger glow */
  font-weight: 700;
  letter-spacing: 1px; /* Increased letter spacing */
}

/* === Login Body/Form === */
.login-body {
  padding-top: 1rem; /* Increased padding */
}

/* === Forms & Inputs === */
.form-group {
  margin-bottom: 1.5rem; /* Increased margin */
  text-align: left;
}
label {
  display: block;
  font-size: 0.95rem; /* Slightly larger font size */
  color: #ccc;
  font-weight: 700;
  margin-bottom: 0.5rem; /* Increased space */
}
.input-container {
  position: relative;
}
.form-control {
  width: 100%;
  padding: 14px 45px 14px 16px; /* Increased padding */
  font-size: 1.1rem; /* Slightly larger font size */
  background: #2a2a2a;
  border: 1px solid #444; /* Match card border */
  color: #fff;
  border-radius: 4px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
  box-sizing: border-box;
}
.form-control::placeholder {
  color: rgba(255, 255, 255, 0.6); /* Slightly lighter placeholder */
}
.form-control:focus {
  outline: none;
  border-color: #ffd700;
  box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.4); /* Stronger focus glow */
}
.eye-icon {
  position: absolute;
  right: 16px; /* Adjusted position */
  top: 50%;
  transform: translateY(-50%);
  color: #ffd700;
  cursor: pointer;
  font-size: 1.3rem; /* Slightly larger size */
  z-index: 2;
  transition: color 0.25s ease;
}
.eye-icon:hover {
  color: #ffc107;
}


/* === Buttons === */
.btn {
  padding: 14px 20px; /* Increased padding */
  font-size: 1.15rem; /* Slightly larger font size */
  font-weight: 700;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.1s ease;
  text-align: center;
  line-height: 1.4;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}
.btn:disabled {
  opacity: 0.5; /* Slightly more opaque */
  cursor: not-allowed;
}
.btn-submit {
  background: #ffd700;
  color: #1f1f1f;
  box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3); /* Initial shadow */
}
.btn-submit:hover:not(:disabled) {
  background: #ffc107;
  box-shadow: 0 6px 15px rgba(255, 215, 0, 0.5); /* Stronger hover shadow */
  transform: translateY(-2px); /* More pronounced lift */
}
.btn-submit:active:not(:disabled) {
    transform: translateY(0px);
    box-shadow: 0 2px 5px rgba(255, 215, 0, 0.3); /* Smaller active shadow */
}

.submit-group {
    text-align: center;
}


/* === Loading Spinner === */
.spinner {
  width: 22px; /* Slightly larger spinner */
  height: 22px;
  border: 3px solid rgba(255, 255, 255, 0.4); /* Slightly more visible spinner border */
  border-radius: 50%;
  border-top-color: #ffd700;
  animation: spin 0.8s linear infinite;
  display: inline-block;
  vertical-align: middle;
}
.btn .spinner {
    margin: 0;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* === Status Messages === */
.status-message {
  margin-top: 1.5rem; /* Increased margin */
  padding: 1rem; /* Increased padding */
  border-radius: 4px;
  font-size: 1rem; /* Slightly larger font size */
  text-align: center;
  font-weight: bold;
  border: 1px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem; /* Increased gap */
}
.status-message.error-message {
  color: #ff8a80; /* Softer red */
  background-color: rgba(229, 62, 62, 0.15); /* Slightly more opaque background */
  border-color: rgba(229, 62, 62, 0.5); /* Slightly more opaque border */
}
.status-message.success-message {
  color: #a5d6a7; /* Softer green */
  background-color: rgba(72, 187, 120, 0.15); /* Slightly more opaque background */
  border-color: rgba(72, 187, 120, 0.5); /* Slightly more opaque border */
}


/* === Responsive Adjustments === */
@media (max-width: 576px) {
  .login-card {
    max-width: 95%;
    padding: 1.5rem 1rem; /* Adjust padding */
  }
  .login-header h2 {
    font-size: 1.8rem; /* Adjust font size */
  }
  .form-control {
    padding: 10px 40px 10px 12px; /* Revert padding */
    font-size: 1rem; /* Revert font size */
  }
  .btn {
    padding: 10px 16px; /* Revert padding */
    font-size: 1rem; /* Revert font size */
  }
  .status-message {
      font-size: 0.9rem; /* Revert font size */
      padding: 0.75rem; /* Revert padding */
  }
}

</style>
