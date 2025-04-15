<template>
  <div class="registration-container">
    <div class="registration-card">
      <div class="registration-header">
        <h2>Registration</h2>
      </div>
      <div class="registration-body">
        <form @submit.prevent="userRegister">
          <div class="form-group">
            <label for="name" class="form-label">Name*</label>
            <input
              id="name"
              type="text"
              v-model="user.name"
              placeholder="Enter your name"
              class="form-control"
              required
              aria-label="Name input"
            />
            <small v-if="nameError" class="text-danger error-inline">{{ nameError }}</small>
          </div>
          <div class="form-group">
            <label for="email" class="form-label">Email*</label>
            <input
              id="email"
              type="email"
              v-model="user.email"
              placeholder="Enter your email"
              class="form-control"
              required
              aria-label="Email input"
            />
            <small v-if="emailError" class="text-danger error-inline">{{ emailError }}</small>
          </div>
          <div class="form-group">
            <label for="password" class="form-label">Password*</label>
            <input
              id="password"
              type="password"
              v-model="user.password"
              placeholder="Enter your password (min 6 chars)"
              class="form-control"
              required
              aria-label="Password input"
            />
            <small v-if="passwordError" class="text-danger error-inline">{{ passwordError }}</small>
          </div>

          <div class="form-group submit-group">
             <button type="submit" class="btn btn-submit" :disabled="isLoading">
              <span v-if="!isLoading">Register</span>
              <div
                v-if="isLoading"
                class="spinner"
                role="status"
                aria-label="Loading"
              ></div>
            </button>
          </div>
          <p v-if="statusMessage" :class="['status-message', messageClass]">
             <i v-if="messageClass === 'text-danger'" class="bi bi-exclamation-triangle-fill"></i>
             <i v-if="messageClass === 'text-success'" class="bi bi-check-circle-fill"></i>
            {{ statusMessage }}
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { ref } from "vue";
import { useRouter } from "vue-router";
// Ensure these helpers/stores are correctly imported in your project
import { BASE_URL } from "@/helpers/baseUrls";
import { useAuthStore } from "@/stores/useAuthStore";

const router = useRouter();
const store = useAuthStore();

// Reactive state for registration form
const user = ref({
  name: "",
  email: "guest@example.com", // Default/Test values
  password: "guest123",      // Default/Test values
  positionId: 2, // Default role: Guest
});

// State variables for UI feedback
const isLoading = ref(false);
const statusMessage = ref("");
const messageClass = ref(""); // Will be 'text-danger' or 'text-success'
const nameError = ref("");
const emailError = ref("");
const passwordError = ref("");

// Form validation logic
const validateForm = () => {
  let valid = true;
  // Clear previous errors
  nameError.value = "";
  emailError.value = "";
  passwordError.value = "";

  // Validate name
  if (!user.value.name.trim()) {
    nameError.value = "Name is required.";
    valid = false;
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!user.value.email || !emailRegex.test(user.value.email)) {
    emailError.value = "Please enter a valid email address.";
    valid = false;
  }

  // Validate password length
  if (!user.value.password || user.value.password.length < 6) {
    passwordError.value = "Password must be at least 6 characters long.";
    valid = false;
  }
  return valid;
};

// Handle user registration submission
const userRegister = async () => {
  // Validate form before proceeding
  if (!validateForm()) return;

  // Reset status and start loading indicator
  statusMessage.value = "";
  messageClass.value = "";
  isLoading.value = true;

  try {
    // Send registration request to the backend
    const response = await axios.post(`${BASE_URL}/users`, user.value, {
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });

    // Assuming backend returns user data and token upon successful registration
    const data = response.data.data;

    // Store authentication data in Pinia store
    store.setAuthData({
      id: data.id,
      name: data.name,
      email: data.email,
      positionId: data.positionId,
      token: data.token, // Ensure backend sends token on registration
    });

    // Set success message
    messageClass.value = "text-success"; // Use class for styling
    statusMessage.value = "Registration successful! Redirecting...";

    // Redirect user to profile page after a short delay
    setTimeout(() => {
        router.push("/profile");
    }, 1500); // 1.5 second delay

  } catch (error) {
    // Log error for debugging
    console.error("Registration error:", error);
    // Set error message
    messageClass.value = "text-danger"; // Use class for styling
    // Display specific error from backend if available, otherwise generic message
    statusMessage.value =
      error.response?.data?.message || "Registration failed. Please try again.";
    // Optionally clear stored data if registration fails mid-auth process
    // store.clearStoredData();
  } finally {
    // Stop loading indicator
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* Styles adapted from vue_login_styled */

/* === Core Theme Colors & Fonts === */
.registration-container { /* Renamed */
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

.registration-card { /* Renamed */
  background: rgba(31, 31, 31, 0.9);
  border: 1px solid #383838;
  padding: 2rem 2.5rem; /* Keep larger size */
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.7);
  color: #fefefe;
  width: 100%;
  max-width: 500px; /* Keep larger size */
  border-radius: 6px;
  text-align: center;
}

/* === Header === */
.registration-header { /* Renamed */
  text-align: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #383838;
}
.registration-header h2 { /* Renamed */
  font-family: "Cinzel Decorative", serif, system-ui;
  font-size: 2.1rem; /* Keep larger size */
  color: #ffd700;
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* === Registration Body/Form === */
.registration-body { /* Renamed */
  padding-top: 0.5rem;
}

/* === Forms & Inputs === */
.form-group {
  margin-bottom: 1.25rem;
  text-align: left;
}
/* Style for label element directly */
label {
  display: block;
  font-size: 0.9rem; /* Keep larger size */
  color: #ccc;
  font-weight: 700;
  margin-bottom: 0.4rem;
}
/* Removed .input-container and .eye-icon styles as they are not used here */
.form-control {
  width: 100%;
  /* Adjusted padding (no eye icon needed) */
  padding: 12px 14px;
  font-size: 1.05rem; /* Keep larger size */
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

/* Style for inline error messages */
.error-inline {
    display: block; /* Ensure it takes its own line */
    font-size: 0.8rem; /* Smaller font size */
    margin-top: 0.25rem; /* Space above */
    font-weight: bold;
}
/* Inherit text-danger color */
.text-danger {
    color: #e53e3e; /* Match status message error color */
}


/* === Buttons === */
.btn {
  padding: 12px 18px; /* Keep larger size */
  font-size: 1.1rem; /* Keep larger size */
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
  margin-top: 1.25rem;
  padding: 0.8rem; /* Keep larger size */
  border-radius: 4px;
  font-size: 0.95rem; /* Keep larger size */
  text-align: center;
  font-weight: bold;
  border: 1px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
/* Use text-danger class from script */
.status-message.text-danger {
  color: #e53e3e;
  background-color: rgba(229, 62, 62, 0.1);
  border-color: rgba(229, 62, 62, 0.4);
}
/* Use text-success class from script */
.status-message.text-success {
  color: #48bb78;
  background-color: rgba(72, 187, 120, 0.1);
  border-color: rgba(72, 187, 120, 0.4);
}

/* === Responsive Adjustments === */
@media (max-width: 576px) {
  .registration-card { /* Renamed */
    max-width: 95%; /* Allow slightly wider on small screens */
    padding: 1.5rem 1rem; /* Adjust padding */
  }
  .registration-header h2 { /* Renamed */
    font-size: 1.8rem; /* Adjust font size */
  }
  .form-control {
    padding: 10px 12px; /* Revert padding */
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
  .error-inline {
      font-size: 0.75rem; /* Adjust inline error size */
  }
}

</style>