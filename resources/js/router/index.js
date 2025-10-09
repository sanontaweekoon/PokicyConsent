import { createRouter, createWebHistory } from 'vue-router'
import http from '../services/AuthService'

// import Layouts
import FrontendLayout from "../layouts/Auth.vue"
import BackendLayout from '../layouts/Backend.vue'

// Frontend
import Login from '../views/frontend/Login.vue'

// Backend
import Policys from '../views/backend/Policys.vue'

const routes =[{
    path: '/login',
    component: FrontendLayout,
    children: [{
      name: 'Login',
      path: '',
      component: Login
    }],
  },
  {
    path: '/backend',
    component: BackendLayout,
    children: [{
        path: 'policys',
        name: 'Policys',
        component: Policys
    }],
    meta: {requiresAuth: true}
  },
  {
    path: '/',
    redirect: '/backend'
  }

]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach(async (to, from, next) => {
  if (!to.meta?.requiresAuth) return next()

  const token = localStorage.getItem('auth_token')

  if (!token)
    router.push('/login');
    // return next({ name: 'Login', query: { redirect: to.fullPath } })

  if (!http.defaults.headers.common['Authorization']){
    http.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }
  next()
})

export default router

