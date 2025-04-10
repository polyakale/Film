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
              <p v-if="nameError" class="status-message error-message">{{ nameError }}</p>
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
                <span @click="toggleVisibility('current')" class="eye-icon" title="Toggle visibility">
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
                <span @click="toggleVisibility('new')" class="eye-icon" title="Toggle visibility">
                  <i
                    :class="
                      passwordVisible.new ? 'bi bi-eye-slash' : 'bi bi-eye'
                    "
                  ></i>
                </span>
              </div>
              <div class="password-strength-meter" v-if="password.new">
                <div class="strength-bar" :class="{ active: passwordStrength >= 1 }"></div>
                <div class="strength-bar" :class="{ active: passwordStrength >= 2 }"></div>
                <div class="strength-bar" :class="{ active: passwordStrength >= 3 }"></div>
                <div class="strength-bar" :class="{ active: passwordStrength >= 4 }"></div>
                <div class="strength-text" id="password-strength-text">{{ strengthText }}</div>
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
                <span @click="toggleVisibility('confirm')" class="eye-icon" title="Toggle visibility">
                  <i
                    :class="
                      passwordVisible.confirm ? 'bi bi-eye-slash' : 'bi bi-eye'
                    "
                  ></i>
                </span>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center action-buttons-row">
              <div class="form-group submit-group mb-0"> <button type="submit" class="btn btn-submit" :disabled="loading">
                  <span v-if="!loading">Change Password</span>
                  <div v-if="loading" class="spinner" role="status" aria-label="Loading"></div>
                </button>
              </div>
              <div class="delete-account-section">
                <button type="button" class="btn btn-delete" @click="deleteAccount" title="Delete Account">
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
      password: { // Object to hold password fields
        current: "",
        new: "",
        confirm: "",
      },
      loading: false, // Loading indicator state for async operations
      message: "", // Feedback message for user actions
      messageClass: "", // CSS class for the feedback message (e.g., success or error)
      passwordVisible: { // Flags to control password field visibility
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
        case 1: return "Weak";
        case 2: return "Moderate";
        case 3: return "Strong";
        case 4: return "Very Strong";
        default: return "";
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
        this.showMessage("New password must be between 8 and 16 characters.", "error-message");
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
          error.response?.data?.message || "Password change failed. Please check current password and try again.";
        this.showMessage(errorMsg, "error-message");
      } finally {
        this.loading = false; // Hide loading indicator
      }
    },
    // Handle account deletion
    async deleteAccount() {
      // Confirm with the user before proceeding
      if (window.confirm("Are you absolutely sure you want to delete your account? This action cannot be undone.")) {
        try {
          // Make the API call to delete the user account
          await axios.delete(`${BASE_URL}/users/${this.id}`, { // Your API endpoint
            headers: { Authorization: `Bearer ${this.token}` }, // Requires authentication
          });
          // Clear local auth data (Pinia store and potentially localStorage/sessionStorage)
          this.clearStoredData();
          // Redirect to login page after successful deletion
          this.$router.push("/login");
        } catch (error) {
          console.error("Error deleting account:", error);
          this.showMessage(error.response?.data?.message || "Failed to delete account. Please try again.", "error-message");
        }
      }
    },
    // Handle name change submission
    async saveName() {
        this.nameError = ""; // Clear previous errors
        // Basic validation: check if name is empty
        if (!this.newName || !this.newName.trim()) {
            this.nameError = "Name cannot be empty.";
            return;
        }
        // Basic validation: check if name exceeds max length
        if (this.newName.length > 50) {
            this.nameError = "Name cannot exceed 50 characters.";
            return;
        }
        // No change needed if name hasn't changed
        if (this.newName.trim() === this.user) {
            this.isEditingName = false;
            return;
        }

        try {
            // Make the API call to update the name
            await axios.patch(
            `${BASE_URL}/users/update-name`, // Your API endpoint
            { name: this.newName.trim() }, // Send trimmed name
            {
                headers: {
                Authorization: `Bearer ${this.token}`, // Requires authentication
                },
            }
            );

            // Update the name in the Pinia store
            useAuthStore().setUserName(this.newName.trim());
            this.isEditingName = false; // Exit editing mode
            this.showMessage("Name updated successfully!", "success-message");

        } catch (error) {
            console.error("Name update error:", error);
            this.nameError =
            error.response?.data?.message || "Failed to update name. Please try again.";
            // Display error inline for name editing
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
      }
  }
};
</script>

<style scoped>
/* Import Google Fonts (optional, using system fonts otherwise) */
/* @import url('https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Poppins:wght@400;700&display=swap'); */

/* === Background & Container === */
.profile-container {
  display: flex;
  justify-content: center;
  /* Vertically center the card */
  align-items: center;
  min-height: 90vh;
  /* Using a slightly darker, less distracting background */
  background-color: #111; /* Fallback solid color */
  /* Removed the dark linear-gradient overlay */
  background-image: url("https://source.unsplash.com/1600x900/?dark,abstract,texture");
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  padding: 2rem 1rem;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; /* System font stack */
}

/* === Profile Card === */
.profile-card {
  background: #1f1f1f; /* Solid dark background */
  border: 1px solid #383838; /* Subtle dark border */
  padding: 2rem 2.5rem;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.7); /* Slightly stronger shadow */
  color: #b0b0b0; /* Primary text color (light gray) */
  width: 100%;
  max-width: 500px;
  text-align: left;
  border-radius: 8px;
}

/* === Header === */
.profile-header {
  text-align: center;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #383838;
}
.profile-header h2 {
  /* Using Cinzel Decorative if imported, otherwise fallback */
  font-family: 'Cinzel Decorative', serif, system-ui;
  font-size: 1.8rem;
  color: #ffd700; /* Gold accent */
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.5);
  font-weight: 700; /* Bold */
  letter-spacing: 1px;
}

/* === Profile Body & Info === */
.profile-body {
    padding-top: 1rem;
}
.profile-info {
  font-size: 1rem;
  margin-bottom: 2rem;
}
.profile-info p {
  margin-bottom: 0.8rem;
  line-height: 1.6;
}
.profile-info strong {
  color: #ffd700; /* Gold for labels */
  font-weight: 700;
  margin-right: 0.5em;
}

/* === Name Editing Section === */
.name-section {
    margin-bottom: 1rem;
}
.name-display {
    display: flex;
    align-items: center;
}
.name-display p {
    margin-bottom: 0;
}
.edit-icon {
  margin-left: 0.75rem;
  cursor: pointer;
  color: #ffd700; /* Gold icon */
  font-size: 1.1rem;
  transition: color 0.3s ease;
  vertical-align: middle;
}
.edit-icon:hover {
  color: #ffc107; /* Darker gold on hover */
}
.name-edit-form {
  margin-top: 0.5rem;
}
.input-group {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-wrap: wrap;
}
.input-group .form-control {
    flex-grow: 1;
    min-width: 150px;
}
.button-group {
    display: flex;
    gap: 0.5rem;
}

/* === Section Titles === */
.section-title {
  /* Using Poppins if imported, otherwise fallback */
  font-family: 'Poppins', sans-serif, system-ui;
  font-size: 1.25rem;
  font-weight: 700;
  color: #ffd700; /* Gold title */
  margin-top: 1.5rem;
  margin-bottom: 1rem;
  padding-bottom: 0.4rem;
  border-bottom: 1px solid #383838; /* Dark border */
}

/* === Forms & Inputs === */
.password-form, .name-edit-form {
  padding-top: 0.5rem;
}
.form-group {
  margin-bottom: 1.25rem;
}
label {
  display: block;
  font-size: 0.9rem;
  color: #b0b0b0; /* Light gray label */
  font-weight: 700;
  margin-bottom: 0.4rem;
}
.input-container {
  position: relative;
}
.form-control {
  width: 100%;
  padding: 10px 40px 10px 12px;
  font-size: 1rem;
  background: #2a2a2a; /* Slightly lighter dark bg */
  border: 1px solid #383838; /* Dark border */
  color: #ffffff; /* White text for inputs */
  border-radius: 4px;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.form-control::placeholder {
    color: rgba(176, 176, 176, 0.6); /* Lighter placeholder */
}
.form-control:focus {
  outline: none;
  border-color: #ffd700; /* Gold border on focus */
  box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2); /* Gold focus glow */
}
.eye-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #ffd700; /* Gold icon */
  cursor: pointer;
  font-size: 1.2rem;
  z-index: 2;
  transition: color 0.3s ease;
}
.eye-icon:hover {
    color: #ffc107; /* Darker gold on hover */
}

/* === Password Strength Meter (Original Structure Styling) === */
.password-strength-meter {
  display: flex;
  align-items: center;
  margin-top: 0.6rem;
  gap: 4px; /* Gap between bars */
  height: 18px;
}
.password-strength-meter .strength-bar {
  flex: 1;
  height: 6px;
  background: #383838; /* Dark background for inactive bars */
  border-radius: 3px;
  transition: background-color 0.3s ease;
}
/* Gold color for active bars */
.password-strength-meter .strength-bar.active {
  background: #ffd700; /* Gold accent */
}
.password-strength-meter .strength-text {
  font-size: 0.85rem;
  font-weight: bold;
  min-width: 70px;
  text-align: right;
  color: #b0b0b0; /* Default text color */
  transition: color 0.3s ease;
}
/* Change text color based on strength */
.password-strength-meter .strength-text:not(:empty) { /* Apply color only when text exists */
    color: #ffd700; /* Gold color when showing strength */
}


/* === Buttons === */
.btn { /* Base button style */
  padding: 10px 18px;
  font-size: 1rem;
  font-weight: 700;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  font-family: inherit; /* Use card's font */
  line-height: 1.5; /* Ensure consistent line height */
}
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Primary Button (Save, Submit) */
.btn-save, .btn-submit {
  background: #ffd700; /* Gold background */
  color: #1f1f1f; /* Dark text on gold */
  min-width: 100px;
}
.btn-save:hover:not(:disabled), .btn-submit:hover:not(:disabled) {
  background: #ffc107; /* Darker gold on hover */
  box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
}
.submit-group {
    flex-grow: 1; /* Allow submit button to take space */
}
.btn-submit {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 40px; /* Consistent height */
}

/* Cancel Button */
.btn-cancel {
  background: #2a2a2a; /* Dark background */
  color: #b0b0b0; /* Light gray text */
  border: 1px solid #383838; /* Dark border */
  min-width: 100px;
}
.btn-cancel:hover:not(:disabled) {
  background: #383838; /* Slightly lighter dark on hover */
  color: #ffffff; /* White text on hover */
}

/* Delete Button - Updated Style */
.btn-delete {
  background: #cc181e; /* Solid red background */
  color: #ffffff; /* White icon */
  border: none; /* Removed border */
  padding: 10px 18px; /* Match primary button padding */
  font-size: 1rem; /* Adjusted icon size slightly if needed */
  /* line-height: 1.5; */ /* Match base button line-height */
  display: inline-flex; /* Align icon nicely */
  align-items: center;
  justify-content: center;
}
.btn-delete i { /* Ensure icon vertical alignment */
    vertical-align: middle;
}
.btn-delete:hover:not(:disabled) {
  background: #a30f13; /* Darker red on hover */
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(204, 24, 30, 0.3); /* Subtle shadow on hover */
}

/* Layout for action buttons */
.action-buttons-row {
    margin-top: 1.5rem; /* Original structure uses d-flex */
    gap: 1rem; /* Add gap between buttons */
}
.submit-group.mb-0 { /* Ensure no margin if class is added */
    margin-bottom: 0 !important;
}

/* === Loading Spinner === */
.spinner {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(31, 31, 31, 0.3); /* Match button text color base */
  border-radius: 50%;
  border-top-color: #1f1f1f; /* Match button text color */
  animation: spin 0.8s linear infinite;
  display: inline-block;
}

/* === Status Messages === */
.status-message {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 4px;
  font-size: 0.95rem;
  text-align: center;
  font-weight: bold;
  border: 1px solid transparent; /* Base border */
}
/* Inline error for name edit */
.name-edit-form .error-message {
    color: #cc181e; /* Red text */
    font-size: 0.9rem;
    margin-top: 0.5rem;
    text-align: left;
    padding: 0; /* No padding/background */
    border: none;
    background: none;
}
/* General status messages */
.status-message.error-message {
  color: #cc181e; /* Red text */
  background-color: rgba(204, 24, 30, 0.1);
  border-color: #cc181e; /* Red border */
}
.status-message.success-message {
  color: #28a745; /* Green text */
  background-color: rgba(40, 167, 69, 0.1);
  border-color: #28a745; /* Green border */
}

/* === Spinner Animation === */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* === Responsive Design === */
@media (max-width: 576px) {
  .profile-card {
    max-width: 95%;
    padding: 1.5rem;
  }
  .profile-header h2 {
    font-size: 1.6rem;
  }
  .section-title {
    font-size: 1.15rem;
  }
  .form-control {
    padding: 8px 35px 8px 10px;
    font-size: 0.95rem;
  }
  .btn {
    padding: 9px 15px; /* Slightly smaller padding on mobile */
    font-size: 0.95rem;
  }
  /* Keep delete button padding consistent with other buttons on mobile */
  .btn-delete {
      padding: 9px 15px;
      font-size: 1rem; /* Keep icon size reasonable */
  }

  .action-buttons-row {
      flex-direction: column; /* Stack buttons vertically */
      align-items: stretch; /* Make buttons full width */
      gap: 1rem; /* Add gap between stacked buttons */
  }
  .submit-group {
      order: 1; /* Ensure submit button is first */
      width: 100%; /* Take full width */
  }
  .delete-account-section {
      order: 2; /* Delete button below */
      text-align: center; /* Center delete button */
      width: 100%; /* Take full width */
  }
  .btn-delete {
      display: inline-flex; /* Allow centering, keep flex for icon */
      width: auto; /* Don't force full width */
      padding: 9px 20px; /* Adjust padding as needed */
  }
  .input-group { /* For name edit */
      flex-direction: column; /* Stack input and buttons */
      align-items: stretch;
  }
  .button-group { /* For name edit */
      margin-top: 0.5rem;
      justify-content: flex-end; /* Align buttons right */
  }
}

</style>
