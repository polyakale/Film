<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="mb-0">User Profile</h3>
          </div>
          <div class="card-body">
            <!-- User Information -->
            <div class="mb-4">
              <h4>Account Details</h4>
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Name:</strong> {{ user }}</p>
                  <p><strong>Email:</strong> {{ email }}</p>
                  <p><strong>Role:</strong> {{ roleName }}</p>
                </div>
              </div>
            </div>
            <!-- Password Change Form -->
            <div>
              <h4>Change Password</h4>
              <form @submit.prevent="changePassword">
                <div class="mb-3">
                  <label class="form-label">Current Password</label>
                  <input type="password" v-model="password.current" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <input type="password" v-model="password.new" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" v-model="password.confirm" class="form-control" required />
                </div>
                <div class="d-flex align-items-center">
                  <button type="submit" class="btn btn-primary me-3">
                    Change Password
                  </button>
                  <div v-if="loading" class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <div v-if="message" :class="['ms-2', messageClass]">
                    {{ message }}
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useAuthStore } from "@/stores/useAuthStore";
import axios from "axios";
import { BASE_URL } from "@/helpers/baseUrls";

// Access the store using Pinia
const store = useAuthStore();

// Reactive state for the password form
const password = ref({
  current: "",
  new: "",
  confirm: "",
});
const loading = ref(false);
const message = ref("");
const messageClass = ref("");

// Computed properties for user data and role name
const user = computed(() => store.user);
const email = computed(() => store.email || 'N/A');
const roleName = computed(() => store.positionId === 1 ? "Administrator" : "Guest");

// Utility method to show a temporary message
const showMessage = (text, style) => {
  message.value = text;
  messageClass.value = style;
  setTimeout(() => {
    message.value = "";
    messageClass.value = "";
  }, 5000);
};

// Method to change the password using a PATCH request
const changePassword = async () => {
  // Validate that new passwords match
  if (password.value.new !== password.value.confirm) {
    showMessage("New passwords do not match", "text-danger");
    return;
  }

  loading.value = true;
  try {
    // Use PATCH to update the password
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

    showMessage("Password changed successfully!", "text-success");
    password.value = { current: "", new: "", confirm: "" }; // Clear form
  } catch (error) {
    console.error("Password change error:", error);
    const errorMsg = error.response?.data?.message || "Password change failed";
    showMessage(errorMsg, "text-danger");
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #e3e6f0;
}

h3,
h4 {
  color: #2c3e50;
}

.form-label {
  font-weight: 500;
}

.text-success {
  color: #28a745 !important;
}

.text-danger {
  color: #dc3545 !important;
}
</style>
