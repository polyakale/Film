<template>
  <div class="registration-container">
    <div class="registration-card">
      <div class="registration-header">
        <h2>Registration</h2>
      </div>
      <div class="registration-body">
        <form @submit.prevent="userRegister">
          <!-- Name Field -->
          <div class="form-group">
            <label for="name">Name*</label>
            <input
              id="name"
              type="text"
              v-model="user.name"
              placeholder="Enter your name"
              class="form-control"
              required
            />
            <small v-if="nameError" class="text-danger">{{ nameError }}</small>
          </div>
          <!-- Email Field -->
          <div class="form-group">
            <label for="email">Email*</label>
            <input
              id="email"
              type="email"
              v-model="user.email"
              placeholder="Enter your email"
              class="form-control"
              required
            />
            <small v-if="emailError" class="text-danger">{{
              emailError
            }}</small>
          </div>
          <!-- Password Field -->
          <div class="form-group">
            <label for="password">Password*</label>
            <input
              id="password"
              type="password"
              v-model="user.password"
              placeholder="Enter your password"
              class="form-control"
              required
            />
            <small v-if="passwordError" class="text-danger">{{
              passwordError
            }}</small>
          </div>
          <!-- Submit Button & Loading Spinner -->
          <div class="form-group">
            <button type="submit" class="btn-submit" :disabled="isLoading">
              Register
            </button>
            <div v-if="isLoading" class="spinner"></div>
          </div>
          <!-- Status Message -->
          <p v-if="statusMessage" :class="['status-message', messageClass]">
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
import { BASE_URL } from "@/helpers/baseUrls";
import { useAuthStore } from "@/stores/useAuthStore";

const router = useRouter();
const store = useAuthStore();

const user = ref({
  name: "",
  email: "guest@example.com",
  password: "guest123",
  positionId: 2, // Default role: Guest
});

const isLoading = ref(false);
const statusMessage = ref("");
const messageClass = ref("");
const nameError = ref("");
const emailError = ref("");
const passwordError = ref("");

// Form validation logic
const validateForm = () => {
  let valid = true;
  nameError.value = "";
  emailError.value = "";
  passwordError.value = "";

  if (!user.value.name.trim()) {
    nameError.value = "Name is required.";
    valid = false;
  }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!user.value.email || !emailRegex.test(user.value.email)) {
    emailError.value = "Please enter a valid email.";
    valid = false;
  }
  if (!user.value.password || user.value.password.length < 6) {
    passwordError.value = "Password must be at least 6 characters.";
    valid = false;
  }
  return valid;
};

const userRegister = async () => {
  if (!validateForm()) return;

  statusMessage.value = "";
  isLoading.value = true;

  try {
    const response = await axios.post(`${BASE_URL}/users`, user.value, {
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });

    const data = response.data.data;

    // ✅ Store the token immediately
    store.setAuthData({
      id: data.id,
      name: data.name,
      email: data.email,
      positionId: data.positionId,
      token: data.token, // 🔥 Ensure token is stored
    });

    messageClass.value = "text-success";
    statusMessage.value = "Registration successful!";

    // ✅ Redirect user immediately
    router.push("/profile");
  } catch (error) {
    console.error("Registration error:", error);
    messageClass.value = "text-danger";
    statusMessage.value =
      error.response?.data?.message || "Registration failed";
    store.clearStoredData();
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* === Background & Container === */
.registration-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 85vh;
  background: url("https://source.unsplash.com/1600x900/?cinema,retro")
    center/cover no-repeat;
  backdrop-filter: blur(8px);
}

/* === Registration Card === */
.registration-card {
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
.registration-header {
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
</style>
