import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { useAuthStore } from "@/stores/useAuthStore";

function checkIfNotLogged(){
  const storeAuth = useAuthStore();
  if (!storeAuth.user) {
    return "/login";
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { title: (route) => 'Home Page'}
    },
    {
      path: '/films',
      name: 'films',
      component: () => import('../views/FilmsView.vue'),
      meta: { title: (route) => 'Films' }
    },
    {
      path: '/people',
      name: 'people',
      component: () => import('../views/PeopleView.vue'),
      meta: { title: (route) => 'People' }
    },
    {
      path: '/reviews',
      name: 'reviews',
      component: () => import('../views/ReviewsView.vue'),
      meta: { title: (route) => 'Reviews' }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/components/Auth/Login.vue'),
      meta: { title: (route) => 'Login' }
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/components/Auth/Profile.vue'),
      beforeEnter: [checkIfNotLogged],
      meta: { title: (route) => 'Profile' }
    },
    {
      path: '/registration',
      name: 'registration',
      component: () => import('@/components/Auth/Registration.vue'),
      meta: { title: (route) => 'Registration' }
    },
    {
      path: "/:pathMatch(.*)*",
      name: "NotFound",
      component: HomeView,
      meta: { title: (route) => 'Home Page' }
    },
  ]
});

export default router
