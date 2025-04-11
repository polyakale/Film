<template>
  <div class="profile-container">
    <div class="profile-card">
      <div class="profile-header">
        <h2>🎭 User Profile</h2>
      </div>
      <div class="profile-body">
        <div class="profile-info">
          <h3 class="section-title">👤 Account Details</h3>
          <div class="name-section">
            <div v-if="!isEditingName" class="name-display">
              <p>
                <strong>Name:</strong> {{ user }}
                <i
                  class="bi bi-pencil-square edit-icon"
                  @click="startEditing"
                  title="Edit Name"
                ></i>
              </p>
            </div>

            <form v-else class="name-edit-form" @submit.prevent="saveName">
              <div class="input-group">
                <input
                  type="text"
                  v-model="newName"
                  class="form-control"
                  placeholder="Enter new name"
                  required
                  maxlength="50"
                  aria-label="New name input"
                />
                <div class="button-group">
                  <button type="submit" class="btn btn-save">Save</button>
                  <button
                    type="button"
                    class="btn btn-cancel"
                    @click="cancelEditing"
                  >
                    Cancel
                  </button>
                </div>
              </div>
              <p v-if="nameError" class="status-message error-message">
                {{ nameError }}
              </p>
            </form>
          </div>
          <p><strong>Email:</strong> {{ email }}</p>
          <p><strong>Role:</strong> {{ roleName }}</p>
        </div>

        <div class="password-section">
          <h3 class="section-title">🔒 Change Password</h3>
          <form @submit.prevent="changePassword" class="password-form">
            <div class="form-group">
              <label for="current-password">Current Password</label>
              <div class="input-container">
                <input
                  id="current-password"
                  :type="passwordVisible.current ? 'text' : 'password'"
                  v-model="password.current"
                  class="form-control"
                  placeholder="Enter your current password"
                  required
                  aria-label="Current password input"
                />
                <span
                  @click="toggleVisibility('current')"
                  class="eye-icon"
                  title="Toggle visibility"
                >
                  <i
                    :class="
                      passwordVisible.current ? 'bi bi-eye-slash' : 'bi bi-eye'
                    "
                  ></i>
                </span>
              </div>
            </div>

            <div class="form-group">
              <label for="new-password">New Password (8-16 characters)</label>
              <div class="input-container">
                <input
                  id="new-password"
                  :type="passwordVisible.new ? 'text' : 'password'"
                  v-model="password.new"
                  class="form-control"
                  placeholder="Enter your new password"
                  required
                  minlength="8"
                  maxlength="16"
                  aria-label="New password input"
                  aria-describedby="password-strength-text"
                />
                <span
                  @click="toggleVisibility('new')"
                  class="eye-icon"
                  title="Toggle visibility"
                >
                  <i
                    :class="
                      passwordVisible.new ? 'bi bi-eye-slash' : 'bi bi-eye'
                    "
                  ></i>
                </span>
              </div>
              <div class="password-strength-meter" v-if="password.new">
                <div
                  class="strength-bar"
                  :class="{ active: passwordStrength >= 1 }"
                ></div>
                <div
                  class="strength-bar"
                  :class="{ active: passwordStrength >= 2 }"
                ></div>
                <div
                  class="strength-bar"
                  :class="{ active: passwordStrength >= 3 }"
                ></div>
                <div
                  class="strength-bar"
                  :class="{ active: passwordStrength >= 4 }"
                ></div>
                <div class="strength-text" id="password-strength-text">
                  {{ strengthText }}
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="confirm-password">Confirm New Password</label>
              <div class="input-container">
                <input
                  id="confirm-password"
                  :type="passwordVisible.confirm ? 'text' : 'password'"
                  v-model="password.confirm"
                  class="form-control"
                  placeholder="Confirm your new password"
                  required
                  aria-label="Confirm new password input"
                />
                <span
                  @click="toggleVisibility('confirm')"
                  class="eye-icon"
                  title="Toggle visibility"
                >
                  <i
                    :class="
                      passwordVisible.confirm ? 'bi bi-eye-slash' : 'bi bi-eye'
                    "
                  ></i>
                </span>
              </div>
            </div>

            <div
              class="d-flex justify-content-between align-items-center action-buttons-row"
            >
              <div class="form-group submit-group mb-0">
                <button
                  type="submit"
                  class="btn btn-submit"
                  :disabled="loading"
                >
                  <span v-if="!loading">Change Password</span>
                  <div
                    v-if="loading"
                    class="spinner"
                    role="status"
                    aria-label="Loading"
                  ></div>
                </button>
              </div>
              <div class="delete-account-section">
                <button
                  type="button"
                  class="btn btn-delete"
                  @click="deleteAccount"
                  title="Delete Account"
                >
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>

            <p v-if="message" :class="['status-message', messageClass]">
              {{ message }}
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// Import Vue features and Pinia store utilities
import { mapState, mapActions } from "pinia";
import { useAuthStore } from "@/stores/useAuthStore";
import axios from "axios"; // For making HTTP requests
import { BASE_URL } from "@/helpers/baseUrls"; // Your API base URL

export default {
  data() {
    return {
      isEditingName: false, // Flag to control name edit state
      newName: "", // Initialize empty, set in mounted
      nameError: "", // Error message for name validation/update
      password: {
        // Object to hold password fields
        current: "",
        new: "",
        confirm: "",
      },
      loading: false, // Loading indicator state for async operations
      message: "", // Feedback message for user actions
      messageClass: "", // CSS class for the feedback message (e.g., success or error)
      passwordVisible: {
        // Flags to control password field visibility
        current: false,
        new: false,
        confirm: false,
      },
    };
  },
  computed: {
    // Map state properties from the Pinia auth store
    ...mapState(useAuthStore, ["user", "email", "positionId", "token", "id"]),
    // Determine user role name based on positionId
    roleName() {
      // Assuming positionId 1 is Admin, others are Guest
      return this.positionId === 1 ? "Administrator" : "Guest";
    },
    // Compute password strength based on criteria met (0-4).
    passwordStrength() {
      let strength = 0;
      const pass = this.password.new;
      if (!pass) return 0;

      if (pass.length >= 8) strength++; // Length requirement (min 8)
      if (pass.length >= 12) strength++; // Bonus for longer length
      if (/[A-Z]/.test(pass) && /[a-z]/.test(pass)) strength++; // Upper and lower case
      if (/[0-9]/.test(pass)) strength++; // Digit
      if (/[^A-Za-z0-9]/.test(pass)) strength++; // Special character

      // Cap strength at 4 for the meter
      return Math.min(strength, 4);
    },
    // Return a text description based on password strength.
    strengthText() {
      // Show text only when strength is calculated (password has input)
      if (!this.password.new) return "";
      switch (this.passwordStrength) {
        case 0:
        case 1:
          return "Weak";
        case 2:
          return "Moderate";
        case 3:
          return "Strong";
        case 4:
          return "Very Strong";
        default:
          return "";
      }
    },
  },
  methods: {
    // Toggle visibility of password fields
    toggleVisibility(field) {
      this.passwordVisible[field] = !this.passwordVisible[field];
    },
    // Display a status message for a limited time
    showMessage(text, style) {
      this.message = text;
      this.messageClass = style;
      // Clear message after 5 seconds
      setTimeout(() => {
        this.message = "";
        this.messageClass = "";
      }, 5000);
    },
    // Enter name editing mode
    startEditing() {
      this.isEditingName = true;
      this.newName = this.user; // Pre-fill with current name from store
      this.nameError = ""; // Clear previous errors
    },
    // Cancel name editing mode
    cancelEditing() {
      this.isEditingName = false;
      this.newName = this.user; // Reset input to current name
      this.nameError = "";
    },
    // Handle password change submission
    async changePassword() {
      // Basic validation: check if new passwords match
      if (this.password.new !== this.password.confirm) {
        this.showMessage("New passwords do not match.", "error-message");
        return;
      }
      // Basic validation: check new password length
      if (this.password.new.length < 8 || this.password.new.length > 16) {
        this.showMessage(
          "New password must be between 8 and 16 characters.",
          "error-message"
        );
        return;
      }

      this.loading = true; // Show loading indicator
      this.message = ""; // Clear previous messages
      try {
        // Prepare request data
        const requestData = {
          current_password: this.password.current,
          new_password: this.password.new,
          new_password_confirmation: this.password.confirm,
        };
        // Include email if it's a guest user (no token) - adjust if needed
        if (!this.token) {
          requestData.email = this.email;
        }
        // Prepare request configuration
        const config = {
          headers: {
            "Content-Type": "application/json",
            // Add Authorization header if token exists
            ...(this.token && { Authorization: `Bearer ${this.token}` }),
          },
        };

        // Make the API call to change password
        await axios.patch(
          `${BASE_URL}/users/change-password`, // Your API endpoint
          requestData,
          config
        );

        // Success: show message and clear fields
        this.showMessage("Password changed successfully!", "success-message");
        this.password = { current: "", new: "", confirm: "" }; // Reset password fields
        this.passwordVisible = { current: false, new: false, confirm: false }; // Hide passwords
      } catch (error) {
        console.error("Password change error:", error);
        // Extract error message from response or provide a default
        const errorMsg =
          error.response?.data?.message ||
          "Password change failed. Please check current password and try again.";
        this.showMessage(errorMsg, "error-message");
      } finally {
        this.loading = false; // Hide loading indicator
      }
    },
    // Handle account deletion
    async deleteAccount() {
      // Confirm with the user before proceeding
      if (
        window.confirm(
          "Are you absolutely sure you want to delete your account? This action cannot be undone."
        )
      ) {
        try {
          // Make the API call to delete the user account
          await axios.delete(`${BASE_URL}/users/${this.id}`, {
            // Your API endpoint
            headers: { Authorization: `Bearer ${this.token}` }, // Requires authentication
          });
          // Clear local auth data (Pinia store and potentially localStorage/sessionStorage)
          this.clearStoredData();
          // Redirect to login page after successful deletion
          this.$router.push("/login");
        } catch (error) {
          console.error("Error deleting account:", error);
          this.showMessage(
            error.response?.data?.message ||
              "Failed to delete account. Please try again.",
            "error-message"
          );
        }
      }
    },
    // Handle name change submission
    async saveName() {
      this.nameError = "";
      if (!this.newName.trim()) {
        this.nameError = "Name cannot be empty.";
        return;
      }
      if (this.newName.length > 50) {
        this.nameError = "Name cannot exceed 50 characters.";
        return;
      }
      if (this.newName.trim() === this.user) {
        this.isEditingName = false;
        return;
      }

      try {
        await axios.patch(
          `${BASE_URL}/users/update-name/${this.id}`,
          { name: this.newName.trim() },
          { headers: { Authorization: `Bearer ${this.token}` } }
        );

        useAuthStore().setUserName(this.newName.trim());
        this.isEditingName = false;
        this.showMessage("Name updated successfully!", "success-message");
      } catch (error) {
        console.error("Name update error:", error);
        this.nameError =
          error.response?.data?.message ||
          "Failed to update name. Please try again.";

        if (error.response?.data?.errors?.name) {
          this.nameError = error.response.data.errors.name[0];
        }
      }
    },
    // Map the action from the Pinia store to clear auth data
    ...mapActions(useAuthStore, ["clearStoredData"]),
  },
  mounted() {
    // Initialize newName with the user's current name when component mounts
    // Ensure user state is loaded before assigning
    if (this.user) {
      this.newName = this.user;
    }
  },
  watch: {
    // Watch the user state from Pinia in case it loads after mount
    user(newUserValue) {
      if (!this.isEditingName && newUserValue) {
        this.newName = newUserValue;
      }
    },
  },
};
</script>

<style scoped>
/* === Core Theme Colors & Fonts === */
.profile-container {
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

.profile-card {
  background: #1f1f1f;
  border: 1px solid #383838;
  padding: 1.5rem 2rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.7);
  color: #fefefe;
  width: 100%;
  max-width: 480px;
  border-radius: 6px;
}

/* === Header === */
.profile-header {
  text-align: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #383838;
}
.profile-header h2 {
  font-family: "Cinzel Decorative", serif, system-ui;
  font-size: 1.6rem;
  color: #ffd700;
  text-shadow: 0 0 6px rgba(255, 215, 0, 0.4);
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* === Profile Info === */
.profile-body {
  padding-top: 0.5rem;
}
.profile-info {
  font-size: 0.95rem;
  margin-bottom: 1rem;
}
.profile-info p {
  margin: 0.4rem 0;
  line-height: 1.4;
}
.profile-info strong {
  color: #ffd700;
  font-weight: 700;
  margin-right: 0.25em;
}

/* === Name Editing Section === */
.name-section {
  margin-bottom: 0.75rem;
}
.name-display {
  display: flex;
  align-items: center;
}
.name-display p {
  margin: 0;
}
.edit-icon {
  margin-left: 0.5rem;
  cursor: pointer;
  color: #ffd700;
  font-size: 1rem;
  transition: color 0.25s ease;
}
.edit-icon:hover {
  color: #ffc107;
}
.name-edit-form {
  margin-top: 0.5rem;
}
.input-group {
  display: flex;
  gap: 0.25rem;
  align-items: center;
  flex-wrap: wrap;
}
.input-group .form-control {
  flex: 1;
  min-width: 120px;
}
.button-group {
  display: flex;
  gap: 0.25rem;
}

/* === Section Titles === */
.section-title {
  font-family: "Poppins", sans-serif, system-ui;
  font-size: 1.15rem;
  font-weight: 700;
  color: #ffd700;
  margin: 0.75rem 0;
  padding-bottom: 0.25rem;
  border-bottom: 1px solid #383838;
}

/* === Forms & Inputs === */
.password-form,
.name-edit-form {
  padding-top: 0.25rem;
}
.form-group {
  margin-bottom: 0.75rem;
}
label {
  display: block;
  font-size: 0.85rem;
  color: #ccc;
  font-weight: 700;
  margin-bottom: 0.25rem;
}
.input-container {
  position: relative;
}
.form-control {
  width: 100%;
  padding: 8px 36px 8px 10px;
  font-size: 0.95rem;
  background: #2a2a2a;
  border: 1px solid #383838;
  color: #fff;
  border-radius: 4px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
}
.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}
.form-control:focus {
  outline: none;
  border-color: #ffd700;
  box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3);
}
.eye-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #ffd700;
  cursor: pointer;
  font-size: 1rem;
  z-index: 2;
  transition: color 0.25s ease;
}
.eye-icon:hover {
  color: #ffc107;
}

/* === Password Strength Meter === */
.password-strength-meter {
  display: flex;
  align-items: center;
  gap: 2px;
  height: 16px;
  margin-top: 0.25rem;
}
.password-strength-meter .strength-bar {
  flex: 1;
  height: 4px;
  background: #383838;
  border-radius: 2px;
  transition: background-color 0.25s ease;
}
.password-strength-meter .strength-bar.active {
  background: #ffd700;
}
.password-strength-meter .strength-text {
  font-size: 0.75rem;
  font-weight: bold;
  min-width: 50px;
  text-align: right;
  color: #ffd700;
}

/* === Buttons === */
.btn {
  padding: 8px 14px;
  font-size: 0.9rem;
  font-weight: 700;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s ease, box-shadow 0.3s ease;
  text-align: center;
  line-height: 1.4;
}
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.btn-save,
.btn-submit {
  background: #ffd700;
  color: #1f1f1f;
  min-width: 80px;
}
.btn-save:hover:not(:disabled),
.btn-submit:hover:not(:disabled) {
  background: #ffc107;
  box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3);
}
.btn-cancel {
  background: #2a2a2a;
  color: #ccc;
  border: 1px solid #383838;
  min-width: 80px;
}
.btn-cancel:hover:not(:disabled) {
  background: #383838;
  color: #fff;
}
.btn-delete {
  background: #cc181e;
  color: #fff;
  padding: 8px 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.btn-delete:hover:not(:disabled) {
  background: #a30f13;
  box-shadow: 0 2px 6px rgba(204, 24, 30, 0.3);
}

/* Layout for action buttons */
.action-buttons-row {
  margin-top: 1rem;
  display: flex;
  gap: 0.5rem;
  align-items: center;
}
.submit-group {
  flex-grow: 1;
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
  margin-top: 0.75rem;
  padding: 0.5rem;
  border-radius: 4px;
  font-size: 0.9rem;
  text-align: center;
  font-weight: bold;
  border: 1px solid transparent;
}
.status-message.error-message {
  color: #cc181e;
  background-color: rgba(204, 24, 30, 0.1);
  border-color: #cc181e;
}
.status-message.success-message {
  color: #28a745;
  background-color: rgba(40, 167, 69, 0.1);
  border-color: #28a745;
}

/* === Profile Table & Reviews Grid === */
.table-section,
.public-reviews-section {
  flex-grow: 1;
  overflow: hidden;
}
.table-wrapper {
  flex-grow: 1;
  overflow-y: auto;
  padding-right: 4px;
}
.custom-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 0.5rem;
}
.custom-table th,
.custom-table td {
  padding: 0.5rem;
  border: 1px solid #444444;
  text-align: left;
}
.custom-table th {
  background: #2a2a2a;
  color: #ffd700;
  font-weight: 600;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}
.custom-table td {
  background: #1f1f1f;
  color: #fff;
}
.custom-table tbody tr:hover {
  background-color: #2f2f2f;
  transition: background-color 0.3s ease;
}

/* Reviews Grid */
.reviews-wrapper {
  flex-grow: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding-right: 4px;
}
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 0.75rem;
  padding-bottom: 0.75rem;
}
.review-card {
  background: #333333;
  border: 1px solid #444444;
  border-radius: 6px;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  color: #fff;
  text-align: left;
  overflow: hidden;
}
.review-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}
.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  font-size: 0.9rem;
}
.review-film-title {
  font-size: 1rem;
  font-weight: 700;
  color: #ffd700;
}
.review-meta {
  font-size: 0.75rem;
  color: #aaaaaa;
}
.review-content {
  font-size: 0.85rem;
  line-height: 1.4;
}

/* === Pagination Wrapper === */
.pagination-wrapper {
  padding: 0.5rem 0;
  display: flex;
  justify-content: center;
  align-items: center;
  border-top: 1px solid #444444;
  background: #1f1f1f;
}

/* === Responsive Adjustments === */
@media (max-width: 576px) {
  .profile-card {
    max-width: 100%;
    padding: 1.25rem;
  }
  .profile-header h2 {
    font-size: 1.4rem;
  }
  .section-title {
    font-size: 1rem;
    margin-top: 0.75rem;
  }
  .form-control {
    padding: 6px 30px 6px 8px;
    font-size: 0.9rem;
  }
  .btn {
    padding: 7px 12px;
    font-size: 0.85rem;
  }
  .action-buttons-row {
    flex-direction: column;
    gap: 0.5rem;
  }
  .input-group {
    flex-direction: column;
    align-items: stretch;
  }
  .button-group {
    margin-top: 0.5rem;
    justify-content: flex-end;
  }
}
</style>

