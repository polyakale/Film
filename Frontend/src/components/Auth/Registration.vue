<template>
  <div class="registration-container">
    <div class="registration-card">
      <div class="registration-header">
        <h2>Registration</h2>
      </div>
      <div class="registration-body">
        <form @submit.prevent="userRegister">
          <div class="form-group">
            <label for="name" class="form-label">Username*</label>
            <input
              id="name"
              type="text"
              v-model="user.name"
              placeholder="Make a username..."
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
            <div class="input-container">
              <input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                v-model="user.password"
                placeholder="Make a password (min 6 chars)"
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

// Reactive state for password visibility
const showPassword = ref(false); // Added showPassword state

// Reactive state for registration form
const user = ref({
  name: "",
  email: "guest0@example.com", // Default/Test values
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
    nameError.value = "Username is required.";
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
  background: rgba(31, 31, 31, 0.95); /* Slightly less transparent */
  border: 1px solid #444; /* Slightly lighter border */
  padding: 2.5rem 3rem; /* Increased padding */
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.8); /* Stronger shadow */
  color: #fefefe;
  width: 100%;
  max-width: 500px;
  border-radius: 8px; /* Slightly larger border radius */
  text-align: center;
  backdrop-filter: blur(5px); /* Added blur effect */
}

/* === Header === */
.registration-header { /* Renamed */
  text-align: center;
  margin-bottom: 2.5rem; /* Increased margin */
  padding-bottom: 1.2rem; /* Increased padding */
  border-bottom: 2px solid #ffd700; /* Thicker gold border */
}
.registration-header h2 { /* Renamed */
  font-family: "Cinzel Decorative", serif, system-ui;
  font-size: 2.2rem; /* Slightly larger font size */
  color: #ffd700;
  text-shadow: 0 0 10px rgba(255, 215, 0, 0.7); /* Stronger glow */
  font-weight: 700;
  letter-spacing: 1px; /* Increased letter spacing */
}

/* === Registration Body/Form === */
.registration-body { /* Renamed */
  padding-top: 1rem; /* Increased padding */
}

/* === Forms & Inputs === */
.form-group {
  margin-bottom: 1.5rem; /* Increased margin */
  text-align: left;
}
/* Style for label element directly */
label {
  display: block;
  font-size: 0.95rem; /* Slightly larger font size */
  color: #ccc;
  font-weight: 700;
  margin-bottom: 0.5rem; /* Increased space */
}

/* Added input container and eye icon styles */
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


/* Style for inline error messages */
.error-inline {
    display: block;
    font-size: 0.85rem; /* Slightly larger font size */
    margin-top: 0.3rem; /* Increased space above */
    font-weight: bold;
    color: #e57373; /* Slightly softer red */
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
/* Use text-danger class from script */
.status-message.text-danger {
  color: #ff8a80; /* Softer red */
  background-color: rgba(229, 62, 62, 0.15); /* Slightly more opaque background */
  border-color: rgba(229, 62, 62, 0.5); /* Slightly more opaque border */
}
/* Use text-success class from script */
.status-message.text-success {
  color: #a5d6a7; /* Softer green */
  background-color: rgba(72, 187, 120, 0.15); /* Slightly more opaque background */
  border-color: rgba(72, 187, 120, 0.5); /* Slightly more opaque border */
}

/* === Responsive Adjustments === */
@media (max-width: 576px) {
  .registration-card { /* Renamed */
    max-width: 95%;
    padding: 1.5rem 1rem; /* Adjust padding */
  }
  .registration-header h2 { /* Renamed */
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
  .error-inline {
      font-size: 0.75rem; /* Adjust inline error size */
  }
}

</style>
