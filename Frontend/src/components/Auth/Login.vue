<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h2>Login</h2>
      </div>
      <div class="login-body">
        <form @submit.prevent="userAuth">
          <div class="form-group">
            <label for="email">Email</label>
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
            <label for="password">Password</label>
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
  background: rgba(31, 31, 31, 0.9);
  border: 1px solid #383838;
  /* Increased padding */
  padding: 2rem 2.5rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.7);
  color: #fefefe;
  width: 100%;
  /* Increased max-width */
  max-width: 500px;
  border-radius: 6px;
  text-align: center;
}

/* === Header === */
.login-header {
  text-align: center;
  margin-bottom: 2rem; /* Increased margin */
  padding-bottom: 1rem; /* Increased padding */
  border-bottom: 1px solid #383838;
}
.login-header h2 {
  font-family: "Cinzel Decorative", serif, system-ui;
  /* Increased font size */
  font-size: 2.1rem;
  color: #ffd700;
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* === Login Body/Form === */
.login-body {
  padding-top: 0.5rem;
}

/* === Forms & Inputs === */
.form-group {
  margin-bottom: 1.25rem; /* Increased margin */
  text-align: left;
}
label {
  display: block;
  /* Increased font size */
  font-size: 0.9rem;
  color: #ccc;
  font-weight: 700;
  margin-bottom: 0.4rem; /* Increased space */
}
.input-container {
  position: relative;
}
.form-control {
  width: 100%;
  /* Increased padding */
  padding: 12px 45px 12px 14px;
  /* Increased font size */
  font-size: 1.05rem;
  background: #2a2a2a;
  border: 1px solid #383838;
  color: #fff;
  border-radius: 4px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
  box-sizing: border-box;
}
.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}
.form-control:focus {
  outline: none;
  border-color: #ffd700;
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.3);
}
.eye-icon {
  position: absolute;
  right: 14px; /* Adjusted position */
  top: 50%;
  transform: translateY(-50%);
  color: #ffd700;
  cursor: pointer;
  font-size: 1.2rem; /* Adjusted size */
  z-index: 2;
  transition: color 0.25s ease;
}
.eye-icon:hover {
  color: #ffc107;
}


/* === Buttons === */
.btn {
  /* Increased padding */
  padding: 12px 18px;
  /* Increased font size */
  font-size: 1.1rem;
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
  opacity: 0.6;
  cursor: not-allowed;
}
.btn-submit {
  background: #ffd700;
  color: #1f1f1f;
}
.btn-submit:hover:not(:disabled) {
  background: #ffc107;
  box-shadow: 0 4px 10px rgba(255, 215, 0, 0.4);
  transform: translateY(-1px);
}
.btn-submit:active:not(:disabled) {
    transform: translateY(0px);
}

.submit-group {
    text-align: center;
}


/* === Loading Spinner === */
.spinner {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
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
  margin-top: 1.25rem; /* Increased space */
  padding: 0.8rem; /* Increased padding */
  border-radius: 4px;
  /* Increased font size */
  font-size: 0.95rem;
  text-align: center;
  font-weight: bold;
  border: 1px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.status-message.error-message {
  color: #e53e3e;
  background-color: rgba(229, 62, 62, 0.1);
  border-color: rgba(229, 62, 62, 0.4);
}
.status-message.success-message {
  color: #48bb78;
  background-color: rgba(72, 187, 120, 0.1);
  border-color: rgba(72, 187, 120, 0.4);
}

/* === Responsive Adjustments === */
@media (max-width: 576px) {
  .login-card {
    /* Use original smaller size on small screens */
    max-width: 420px;
    padding: 1.5rem 1.5rem; /* Adjust padding */
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
