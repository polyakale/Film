import { defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    id: Number(localStorage.getItem("id")) || null,
    user: localStorage.getItem("user") || null,
    email: localStorage.getItem("email") || null,
    positionId: Number(localStorage.getItem("positionId")) || null,
    token: localStorage.getItem("token") || null,
  }),
  actions: {
    setAuthData(payload) {
      // Update all fields
      this.id = payload.id;
      this.user = payload.name;
      this.email = payload.email;
      this.positionId = payload.positionId;
      this.token = payload.token;

      // Persist to localStorage
      localStorage.setItem("id", payload.id);
      localStorage.setItem("user", payload.name);
      localStorage.setItem("email", payload.email);
      localStorage.setItem("positionId", payload.positionId);
      localStorage.setItem("token", payload.token);
    },
    clearStoredData() {
      // Remove all stored data
      localStorage.removeItem("id");
      localStorage.removeItem("user");
      localStorage.removeItem("email");
      localStorage.removeItem("positionId");
      localStorage.removeItem("token");

      // Reset state
      this.$reset();
    }
  },
});