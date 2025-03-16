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
                  <p><strong>Name:</strong> {{ store.user }}</p>
                  <p><strong>Email:</strong> {{ userEmail }}</p>
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
                  <input type="password" v-model="password.current" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <input type="password" v-model="password.new" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" v-model="password.confirm" class="form-control" required>
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

<script>
import { useAuthStore } from '@/stores/useAuthStore';
import axios from 'axios';
import { BASE_URL } from '@/helpers/baseUrls';

export default {
  data() {
    return {
      store: useAuthStore(),
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      loading: false,
      message: '',
      messageClass: ''
    };
  },
  computed: {
    roleName() {
      return this.store.positionId === 1 ? 'Administrator' : 'Guest';
    },
    userEmail() {
      // Assuming email is stored in the store or needs to be fetched
      return this.store.userEmail || 'N/A';
    }
  },
  methods: {
    async changePassword() {
      if (this.password.new !== this.password.confirm) {
        this.showMessage('New passwords do not match', 'text-danger');
        return;
      }

      this.loading = true;
      try {
        const response = await axios.post(
          `${BASE_URL}/users/change-password`,
          {
            current_password: this.password.current,
            new_password: this.password.new,
            new_password_confirmation: this.password.confirm
          },
          {
            headers: {
              Authorization: `Bearer ${this.store.token}`,
              'Content-Type': 'application/json'
            }
          }
        );

        this.showMessage('Password changed successfully!', 'text-success');
        this.password = { current: '', new: '', confirm: '' };
      } catch (error) {
        console.error('Password change error:', error);
        const message = error.response?.data?.message || 'Password change failed';
        this.showMessage(message, 'text-danger');
      } finally {
        this.loading = false;
      }
    },
    showMessage(text, style) {
      this.message = text;
      this.messageClass = style;
      setTimeout(() => {
        this.message = '';
        this.messageClass = '';
      }, 5000);
    }
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