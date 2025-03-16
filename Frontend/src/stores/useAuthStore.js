import { defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    id: Number(localStorage.getItem("id")) || null,
    user: localStorage.getItem("user") || null,
    positionId: Number(localStorage.getItem("positionId")) || null,
    token: localStorage.getItem("currentToken") || null,
  }),
  actions: {
    setId(id) {
      localStorage.setItem("id", id);
      this.id = id;
    },
    setUser(user) {
      localStorage.setItem("user", user);
      this.user = user;
    },
    setPositionId(positionId) {
      localStorage.setItem("positionId", positionId);
      this.positionId = positionId;
    },
    setToken(token) {
      localStorage.setItem("currentToken", token);
      this.token = token;
    },
    clearStoredData() {
      localStorage.removeItem("currentToken");
      localStorage.removeItem("user");
      localStorage.removeItem("id");
      localStorage.removeItem("positionId");
      this.id = null;
      this.user = null;
      this.positionId = null;
      this.token = null;
    },
  },
});