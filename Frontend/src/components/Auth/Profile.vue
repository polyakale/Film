<template>
  <div class="profile-container">
    <div class="profile-card">
      <div class="profile-header">
        <h2><i class="bi bi-person-circle me-2"></i> User Profile</h2>
      </div>
      <div class="profile-body">
        <div class="profile-info">
          <h3 class="section-title">
            <div class="title-text-wrapper">
              <i class="bi bi-info-circle"></i>
              <span>Account Details</span>
            </div>
            <div class="delete-account-section">
              <button
                type="button"
                class="btn btn-delete"
                @click="deleteAccount"
                title="Delete Account"
              >
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </h3>

          <div class="info-item">
            <strong class="info-label">Name:</strong>
            <div v-if="!isEditingName" class="name-display info-value">
              <span>{{ user }}</span>
              <i
                class="bi bi-pencil edit-icon ms-2"
                @click="startEditing"
                title="Edit Name"
              ></i>
            </div>

            <form
              v-else
              class="name-edit-form info-value"
              @submit.prevent="saveName"
            >
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
              <p v-if="nameError" class="status-message error-message mt-2">
                {{ nameError }}
              </p>
            </form>
          </div>

          <div class="info-item">
            <strong class="info-label">Email:</strong>
            <span class="info-value">{{ email }}</span>
          </div>

          <div class="info-item">
            <strong class="info-label">Role:</strong>
            <span class="info-value">{{ roleName }}</span>
          </div>
        </div>

        <div v-if="!showPasswordChangeForm" class="password-change-toggle">
          <a
            href="#"
            @click.prevent="showPasswordChangeForm = true"
            class="toggle-link"
          >
            Change password?
          </a>
        </div>

        <div v-else class="password-section">
          <h3 class="section-title">
            <i class="bi bi-key me-2"></i> Change Password
          </h3>
          <form @submit.prevent="changePassword" class="password-form">
            <div class="form-group">
              <label for="current-password" class="form-label"
                >Current Password</label
              >
              <div class="input-container">
                <input
                  id="current-password"
                  :type="passwordVisible.current ? 'text' : 'password'"
                  v-model="password.current"
                  class="form-control"
                  placeholder="Enter current password"
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
              <label for="new-password" class="form-label"
                >New Password (8-16 characters)</label
              >
              <div class="input-container">
                <input
                  id="new-password"
                  :type="passwordVisible.new ? 'text' : 'password'"
                  v-model="password.new"
                  class="form-control"
                  placeholder="Enter new password"
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
                  :class="{
                    active: passwordStrength >= 1,
                    moderate: passwordStrength >= 2,
                    strong: passwordStrength >= 3,
                    'very-strong': passwordStrength >= 4,
                  }"
                ></div>
                <div
                  class="strength-bar"
                  :class="{
                    active: passwordStrength >= 2,
                    moderate: passwordStrength >= 2,
                    strong: passwordStrength >= 3,
                    'very-strong': passwordStrength >= 4,
                  }"
                ></div>
                <div
                  class="strength-bar"
                  :class="{
                    active: passwordStrength >= 3,
                    strong: passwordStrength >= 3,
                    'very-strong': passwordStrength >= 4,
                  }"
                ></div>
                <div
                  class="strength-bar"
                  :class="{
                    active: passwordStrength >= 4,
                    'very-strong': passwordStrength >= 4,
                  }"
                ></div>
                <div class="strength-text" id="password-strength-text">
                  {{ strengthText }}
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="confirm-password" class="form-label"
                >Confirm New Password</label
              >
              <div class="input-container">
                <input
                  id="confirm-password"
                  :type="passwordVisible.confirm ? 'text' : 'password'"
                  v-model="password.confirm"
                  class="form-control"
                  placeholder="Confirm new password"
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
              <button
                type="button"
                class="btn btn-cancel"
                @click="cancelPasswordChange"
              >
                Cancel
              </button>
            </div>

            <p v-if="message" :class="['status-message', messageClass]">
              <i
                v-if="messageClass === 'error-message'"
                class="bi bi-exclamation-triangle-fill me-2"
              ></i>
              <i
                v-if="messageClass === 'success-message'"
                class="bi bi-check-circle-fill me-2"
              ></i>
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
      showPasswordChangeForm: false, // New state to control password form visibility
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
          `${BASE_URL}/users/change-password/${this.id}`, // Your API endpoint
          requestData,
          config
        );

        // Success: show message and clear fields
        this.showMessage("Password changed successfully!", "success-message");
        this.password = { current: "", new: "", confirm: "" }; // Reset password fields
        this.passwordVisible = { current: false, new: false, confirm: false }; // Hide passwords
        this.showPasswordChangeForm = false; // Hide the form again on success
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
    // Cancel password change and hide the form
    cancelPasswordChange() {
      this.password = { current: "", new: "", confirm: "" }; // Reset password fields
      this.passwordVisible = { current: false, new: false, confirm: false }; // Hide passwords
      this.message = ""; // Clear any messages
      this.messageClass = "";
      this.showPasswordChangeForm = false; // Hide the form
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
  background-color: #1a1a1a; /* Slightly lighter base background */
  background-image: url("https://source.unsplash.com/1600x900/?dark,abstract,texture,subtle"); /* Added subtle texture keyword */
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  padding: 1.5rem; /* Increased padding */
  font-family: "Roboto", sans-serif, system-ui; /* Changed font */
}

.profile-card {
  background: rgba(
    40,
    40,
    40,
    0.9
  ); /* Lighter card background with more transparency */
  border: 1px solid #555; /* Softer border */
  padding: 2rem 2.5rem; /* Adjusted padding */
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6); /* Softer, wider shadow */
  color: #e0e0e0; /* Lighter text color */
  width: 100%;
  max-width: 600px; /* Increased max width */
  border-radius: 10px; /* More rounded corners */
  backdrop-filter: blur(8px); /* Stronger blur effect */
  overflow: hidden; /* Ensure content stays within rounded corners */
}

/* === Header === */
.profile-header {
  text-align: center;
  margin-bottom: 2rem; /* Increased margin */
  padding-bottom: 1rem; /* Increased padding */
  border-bottom: 2px solid #ffd700; /* Thicker yellow border */
}
.profile-header h2 {
  font-family: "Montserrat", sans-serif, system-ui; /* Changed font */
  font-size: 2rem; /* Adjusted font size */
  color: #ffd700; /* Yellow accent color */
  text-shadow: 0 0 10px rgba(255, 215, 0, 0.7); /* Yellow glow */
  font-weight: 700;
  letter-spacing: 1.5px; /* Increased letter spacing */
}

/* === Profile Info === */
.profile-body {
  padding-top: 1rem; /* Increased padding */
}
.profile-info {
  font-size: 1rem; /* Slightly larger font size */
  margin-bottom: 2rem; /* Increased margin */
  padding: 1.5rem; /* Added padding */
  background: rgba(50, 50, 50, 0.7); /* Distinct background */
  border-radius: 8px;
  border: 1px solid #666; /* Border for the info block */
}
.info-item {
  display: flex;
  align-items: center;
  margin-bottom: 1rem; /* Increased margin between items */
  padding-bottom: 1rem;
  border-bottom: 1px dashed rgba(255, 255, 255, 0.1); /* Dashed separator */
}
.info-item:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}
.info-label {
  color: #ffd700; /* Yellow color for labels */
  font-weight: 700;
  margin-right: 1rem; /* Increased margin */
  min-width: 80px; /* Ensure labels align */
}
.info-value {
  color: #e0e0e0; /* Lighter text color */
  flex-grow: 1;
  word-break: break-word; /* Prevent long words from overflowing */
}

/* === Name Editing Section === */
.name-section .info-value {
  display: flex;
  align-items: center;
}
.name-display {
  display: flex;
  align-items: center;
  flex-grow: 1;
}
.name-display span {
  flex-grow: 1;
}
.edit-icon {
  cursor: pointer;
  color: #ffd700; /* Yellow edit icon */
  font-size: 1.1rem; /* Slightly larger icon */
  transition: color 0.25s ease;
}
.edit-icon:hover {
  color: #ffc107; /* Lighter yellow on hover */
}
.name-edit-form {
  margin-top: 0; /* Remove top margin */
  flex-grow: 1;
}
.input-group {
  display: flex;
  gap: 0.5rem; /* Increased gap */
  align-items: center;
  flex-wrap: wrap;
}
.input-group .form-control {
  flex: 1;
  min-width: 150px; /* Increased min width */
  background: #333; /* Darker input background */
  border: 1px solid #666; /* Softer border */
  color: #fff;
  padding: 10px 12px; /* Adjusted padding */
  font-size: 1rem; /* Adjusted font size */
}
.button-group {
  display: flex;
  gap: 0.5rem; /* Increased gap */
}

/* === Password Change Toggle === */
.password-change-toggle {
  text-align: center;
  margin-top: 1.5rem; /* Space above the link */
  margin-bottom: 1.5rem; /* Space below the link */
}

.toggle-link {
  color: #ffd700; /* Yellow link color */
  text-decoration: none;
  font-size: 1rem;
  font-weight: 600;
  transition: color 0.2s ease;
}

.toggle-link:hover {
  color: #ffc107; /* Lighter yellow on hover */
  text-decoration: underline;
}

/* === Section Titles === */
.section-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-family: "Montserrat", sans-serif;
  font-size: 1.4rem; /* Slightly larger for improved readability */
  font-weight: 700;
  color: #ffd700; /* Classic yellow accent */
  margin: 0 0 1rem;
  padding-bottom: 0.3rem; /* Reduced padding for a sleeker look */
  border-bottom: 2px dashed #555; /* Dashed border for a refined, less heavy feel */
}

.title-text-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem; /* Space between icon and text */
}

.title-text-wrapper i {
  /* Optional: adjust the icon size or margin as needed */
  font-size: 1.2em;
}

.delete-account-section {
  margin-left: auto; /* Ensures the delete button is pushed to the far right */
  /* Removed flex-grow and min-width from here */
}
/* === Forms & Inputs === */
.password-form {
  padding-top: 0.5rem; /* Adjusted padding */
}
.form-group {
  margin-bottom: 1.2rem; /* Increased margin */
}
.form-label {
  /* Explicitly style label class */
  display: block;
  font-size: 0.9rem; /* Adjusted font size */
  color: #ccc;
  font-weight: 700;
  margin-bottom: 0.4rem; /* Adjusted margin */
}

.input-container {
  position: relative;
}
.form-control {
  width: 100%;
  padding: 10px 38px 10px 12px; /* Adjusted padding */
  font-size: 1rem; /* Adjusted font size */
  background: #333; /* Darker input background */
  border: 1px solid #666; /* Softer border */
  color: #fff;
  border-radius: 4px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
  box-sizing: border-box;
}
.form-control::placeholder {
  color: rgba(255, 255, 255, 0.6); /* Lighter placeholder */
}
.form-control:focus {
  outline: none;
  border-color: #ffd700; /* Yellow focus color */
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.4); /* Yellow focus glow */
}
.eye-icon {
  position: absolute;
  right: 12px; /* Adjusted position */
  top: 50%;
  transform: translateY(-50%);
  color: #ffd700; /* Yellow eye icon */
  font-size: 1.1rem; /* Adjusted size */
  z-index: 2;
  cursor: pointer;
  transition: color 0.25s ease;
}
.eye-icon:hover {
  color: #ffc107; /* Lighter yellow on hover */
}

/* === Password Strength Meter === */
.password-strength-meter {
  display: flex;
  align-items: center;
  gap: 3px; /* Increased gap */
  height: 18px; /* Increased height */
  margin-top: 0.5rem; /* Increased margin */
  background: rgba(50, 50, 50, 0.7); /* Background for meter container */
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid #666;
}
.password-strength-meter .strength-bar {
  flex: 1;
  height: 6px; /* Increased height */
  background: #555; /* Darker inactive color */
  border-radius: 3px; /* More rounded */
  transition: background-color 0.4s ease; /* Slower transition */
}
.password-strength-meter .strength-bar.active {
  background: #ff4500; /* Orange for weak */
}
.password-strength-meter .strength-bar.moderate {
  background: #ffd700; /* Gold for moderate */
}
.password-strength-meter .strength-bar.strong {
  background: #ffd700; /* Yellow for strong - Cohesive with accent */
}
.password-strength-meter .strength-bar.very-strong {
  background: #00bfff; /* Deep Sky Blue for very strong */
}

.password-strength-meter .strength-text {
  font-size: 0.85rem; /* Slightly larger font size */
  font-weight: bold;
  min-width: 70px; /* Increased min width */
  text-align: right;
  color: #e0e0e0; /* Lighter text color */
}

/* === Buttons === */
.btn {
  padding: 10px 16px; /* Adjusted padding */
  font-size: 1rem; /* Adjusted font size */
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
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.btn-save,
.btn-submit {
  background: #ffd700; /* Yellow save/submit button */
  color: #1f1f1f;
  min-width: 90px; /* Increased min width */
  box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3); /* Yellow shadow */
}
.btn-save:hover:not(:disabled),
.btn-submit:hover:not(:disabled) {
  background: #ffc107; /* Lighter yellow on hover */
  box-shadow: 0 4px 10px rgba(255, 215, 0, 0.5); /* Stronger yellow shadow */
  transform: translateY(-1px);
}
.btn-save:active:not(:disabled),
.btn-submit:active:not(:disabled) {
  transform: translateY(0px);
  box-shadow: 0 1px 3px rgba(255, 215, 0, 0.3); /* Smaller active shadow */
}

.btn-cancel {
  background: #555; /* Darker cancel button */
  color: #ccc;
  border: 1px solid #666; /* Softer border */
  min-width: 90px; /* Increased min width */
}
.btn-cancel:hover:not(:disabled) {
  background: #666; /* Lighter dark on hover */
  color: #fff;
}
.btn-delete {
  background: transparent; /* Transparent background */
  color: #e53e3e; /* Red icon color */
  padding: 0.5rem; /* Adjusted padding for icon button */
  font-size: 1.2rem; /* Increased icon size */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none; /* Remove border */
  box-shadow: none; /* Remove shadow */
  transition: color 0.2s ease, transform 0.1s ease;
}
.btn-delete:hover:not(:disabled) {
  color: #ff8a80; /* Lighter red on hover */
  transform: translateY(-1px); /* Slight lift on hover */
  box-shadow: none;
}
.btn-delete:active:not(:disabled) {
  transform: translateY(0px);
  box-shadow: none;
}

/* Layout for action buttons */
.action-buttons-row {
  margin-top: 1.5rem; /* Increased margin */
  display: flex;
  gap: 1rem; /* Increased gap */
  align-items: center;
  flex-wrap: wrap; /* Allow wrapping on small screens */
  justify-content: center; /* Center buttons when wrapped */
}
.submit-group {
  flex-grow: 1;
  min-width: 180px; /* Ensure submit button has minimum width */
}
/* Removed flex-grow and min-width from delete-account-section here */

/* === Loading Spinner === */
.spinner {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.4);
  border-radius: 50%;
  border-top-color: #ffd700; /* Yellow spinner color */
  animation: spin 0.8s linear infinite;
  display: inline-block;
  vertical-align: middle;
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
  margin-top: 1rem; /* Increased margin */
  padding: 0.8rem; /* Increased padding */
  border-radius: 4px;
  font-size: 0.95rem; /* Adjusted font size */
  text-align: center;
  font-weight: bold;
  border: 1px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.status-message.error-message {
  color: #ff8a80; /* Softer red */
  background-color: rgba(229, 62, 62, 0.15);
  border-color: rgba(229, 62, 62, 0.5);
}
.status-message.success-message {
  color: #a5d6a7; /* Softer green */
  background-color: rgba(72, 187, 120, 0.15);
  border-color: rgba(72, 187, 120, 0.5);
}

/* === Responsive Adjustments === */
@media (max-width: 576px) {
  .profile-card {
    max-width: 100%;
    padding: 1.5rem; /* Adjusted padding */
  }
  .profile-header h2 {
    font-size: 1.6rem; /* Adjusted font size */
    letter-spacing: 1px;
  }
  .section-title {
    font-size: 1.1rem; /* Adjusted font size */
    margin-top: 1rem;
  }
  .profile-info {
    padding: 1rem; /* Adjusted padding */
  }
  .info-item {
    flex-direction: column; /* Stack info items */
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
  }
  .info-label {
    margin-right: 0;
    margin-bottom: 0.25rem; /* Space below label */
    min-width: auto;
  }
  .input-group {
    flex-direction: column;
    align-items: stretch;
    gap: 0.25rem;
  }
  .button-group {
    margin-top: 0.5rem;
    justify-content: stretch; /* Stretch buttons */
    gap: 0.25rem;
  }
  .button-group .btn {
    flex-grow: 1; /* Allow buttons to grow */
  }
  .form-control {
    padding: 8px 34px 8px 10px; /* Adjusted padding */
    font-size: 0.95rem; /* Adjusted font size */
  }
  .eye-icon {
    right: 10px; /* Adjusted position */
    font-size: 1rem; /* Adjusted size */
  }
  .btn {
    padding: 8px 14px; /* Adjusted padding */
    font-size: 0.9rem; /* Adjusted font size */
  }
  .action-buttons-row {
    flex-direction: column;
    gap: 1rem;
  }
  .submit-group {
    width: 100%; /* Make submit button full width */
    min-width: unset; /* Remove min-width on small screens */
  }
  .delete-account-section {
    width: 100%; /* Make delete button section full width */
    /* Removed min-width from here */
  }
  .btn-delete {
    width: 100%; /* Make delete button full width */
    padding: 0.5rem 0; /* Adjust padding for full-width icon button */
  }
  .password-strength-meter {
    flex-wrap: wrap; /* Allow meter to wrap */
    height: auto;
    padding: 6px;
  }
  .password-strength-meter .strength-bar {
    height: 4px;
  }
  .password-strength-meter .strength-text {
    width: 100%; /* Make text full width */
    text-align: left;
    margin-top: 4px;
  }
}
</style>