<template>
  <div class="profile-container">
    <div class="profile-card">
      <div class="profile-header">
        <h2>🎭 User Profile</h2>
      </div>
      <div class="profile-body">
        <!-- User Information -->
        <div class="profile-info">
          <h3 class="section-title">👤 Account Details</h3>
          <p><strong>Name:</strong> {{ user }}</p>
          <p><strong>Email:</strong> {{ email }}</p>
          <p><strong>Role:</strong> {{ roleName }}</p>
        </div>

        <!-- Password Change Form -->
        <div class="password-section">
          <h3 class="section-title">🔒 Change Password</h3>
          <form @submit.prevent="changePassword" class="password-form">
            <div class="form-group">
              <label>Current Password</label>
              <div class="input-container">
                <input
                  :type="passwordVisible ? 'text' : 'password'"
                  v-model="password.current"
                  class="form-control"
                  placeholder="Enter your current password"
                  required
                />
                <span @click="togglePasswordVisibility" class="eye-icon">
                  <i
                    :class="passwordVisible ? 'bi bi-eye-slash' : 'bi bi-eye'"
                  ></i>
                </span>
              </div>
            </div>
            <div class="form-group">
              <label>New Password</label>
              <div class="input-container">
                <input
                  :type="passwordVisible ? 'text' : 'password'"
                  v-model="password.new"
                  class="form-control"
                  placeholder="Enter your new password"
                  required
                />
                <span @click="togglePasswordVisibility" class="eye-icon">
                  <i
                    :class="passwordVisible ? 'bi bi-eye-slash' : 'bi bi-eye'"
                  ></i>
                </span>
              </div>
            </div>
            <div class="form-group">
              <label>Confirm New Password</label>
              <div class="input-container">
                <input
                  :type="passwordVisible ? 'text' : 'password'"
                  v-model="password.confirm"
                  class="form-control"
                  placeholder="Confirm your new password"
                  required
                />
                <span @click="togglePasswordVisibility" class="eye-icon">
                  <i
                    :class="passwordVisible ? 'bi bi-eye-slash' : 'bi bi-eye'"
                  ></i>
                </span>
              </div>
            </div>
            <div class="form-group">
              <button type="submit" class="btn-submit" :disabled="loading">
                Change Password
              </button>
              <div v-if="loading" class="spinner"></div>
            </div>
            <p v-if="message" :class="['status-message', messageClass]">
              {{ message }}
            </p>
          </form>
        </div>

        <!-- Delete Account Button -->
        <div class="delete-account-section">
          <button class="btn-delete" @click="deleteAccount">
            Delete Account
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/useAuthStore";
import axios from "axios";
import { BASE_URL } from "@/helpers/baseUrls";

const store = useAuthStore();
const router = useRouter();

// Reactive state
const password = ref({
  current: "", // Pre-filled with current password
  new: "",
  confirm: "",
});
const loading = ref(false);
const message = ref("");
const messageClass = ref("");
const passwordVisible = ref(false); // State for toggle password visibility

// Computed properties
const user = computed(() => store.user);
const email = computed(() => store.email || "N/A");
const roleName = computed(() =>
  store.positionId === 1 ? "Administrator" : "Guest"
);

// Pre-fill current password on component mount
onMounted(() => {
  password.value.current = "••••••••"; // Placeholder for current password
});

// Show message temporarily
const showMessage = (text, style) => {
  message.value = text;
  messageClass.value = style;
  setTimeout(() => {
    message.value = "";
    messageClass.value = "";
  }, 5000);
};

// Change password
const changePassword = async () => {
  if (password.value.new !== password.value.confirm) {
    showMessage("New passwords do not match", "error-message");
    return;
  }
  loading.value = true;
  try {
    await axios.patch(
      `${BASE_URL}/users/change-password`,
      {
        current_password: password.value.current,
        new_password: password.value.new,
        new_password_confirmation: password.value.confirm,
      },
      {
        headers: {
          Authorization: `Bearer ${store.token}`,
          "Content-Type": "application/json",
        },
      }
    );
    showMessage("Password changed successfully!", "success-message");
    password.value = { current: "••••••••", new: "", confirm: "" }; // Reset form
  } catch (error) {
    console.error("Password change error:", error);
    const errorMsg = error.response?.data?.message || "Password change failed";
    showMessage(errorMsg, "error-message");
  } finally {
    loading.value = false;
  }
};

// Delete account
const deleteAccount = async () => {
  if (window.confirm("Are you sure you want to delete your account?")) {
    try {
      await axios.delete(`${BASE_URL}/users/${store.id}`, {
        headers: {
          Authorization: `Bearer ${store.token}`,
        },
      });
      store.clearStoredData();
      router.push("/login");
    } catch (error) {
      console.error("Error deleting account:", error);
      showMessage("Failed to delete account", "error-message");
    }
  }
};

// Toggle password visibility
const togglePasswordVisibility = () => {
  passwordVisible.value = !passwordVisible.value;
};
</script>

<style scoped>
/* === Background & Container === */
.profile-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 85vh;
  background: url("https://source.unsplash.com/1600x900/?cinema,retro")
    center/cover no-repeat;
  backdrop-filter: blur(8px);
}

/* === Profile Card === */
.profile-card {
  background: rgba(0, 0, 0, 0.85);
  border: 2px solid rgba(255, 215, 0, 0.4);
  padding: 2.5rem;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.5);
  color: white;
  width: 100%;
  max-width: 450px;
  text-align: center;
}

/* === Header === */
.profile-header {
  font-family: "Cinzel Decorative", serif;
  font-size: 1.8rem;
  color: #ffd700;
  text-shadow: 1px 1px 3px rgba(255, 215, 0, 0.6);
  margin-bottom: 1rem;
}

/* === Profile Info === */
.profile-info {
  text-align: left;
  font-size: 1.1rem;
}

.profile-info p {
  margin-bottom: 0.5rem;
}

.profile-info strong {
  color: #ffd700;
}

/* === Password Form === */
.password-section {
  text-align: left;
}

.password-form {
  text-align: left;
  padding-top: 1rem;
}

.form-group {
  margin-bottom: 1rem;
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
  transition: all 0.3s ease-in-out;
}

.form-control:focus {
  outline: none;
  border-color: #ffd700;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

/* === Eye Icon === */
.eye-icon {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #ffd700;
  cursor: pointer;
  font-size: 1.2rem;
}

.input-container {
  position: relative;
}

/* === Buttons === */
.btn-submit {
  width: 100%;
  padding: 12px;
  font-size: 1.1rem;
  font-weight: bold;
  color: black;
  background: #ffd700;
  border: none;
  transition: all 0.3s ease-in-out;
  cursor: pointer;
}

.btn-submit:hover {
  background: #ffc107;
  box-shadow: 0 0 15px rgba(255, 215, 0, 0.6);
}

/* === Delete Account Button === */
.delete-account-section {
  margin-top: 2rem;
  text-align: center;
}

.btn-delete {
  width: 100%;
  padding: 12px;
  font-size: 1.1rem;
  font-weight: bold;
  color: white;
  background: #ff4d4d;
  border: none;
  transition: all 0.3s ease-in-out;
  cursor: pointer;
}

.btn-delete:hover {
  background: #e60000;
  box-shadow: 0 0 15px rgba(255, 77, 77, 0.6);
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
