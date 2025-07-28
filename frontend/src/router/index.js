import { createRouter, createWebHistory } from 'vue-router'
import { authService } from '@/services'

// Import components
import HomeView from '@/views/HomeView.vue'
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import CoursesView from '@/views/CoursesView.vue'
import CourseDetailView from '@/views/CourseDetailView.vue'
import DashboardView from '@/views/dashboard/DashboardView.vue'
import AdminDashboardView from '@/views/admin/AdminDashboardView.vue'
import AdminCoursesView from '@/views/admin/AdminCoursesView.vue'
import AdminUsersView from '@/views/admin/AdminUsersView.vue'
import ProfileView from '@/views/ProfileView.vue'
import EnrollmentsView from '@/views/EnrollmentsView.vue'
import GetnetTestView from '@/views/GetnetTestView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { guest: true }
  },
  {
    path: '/courses',
    name: 'courses',
    component: CoursesView
  },
  {
    path: '/courses/:id',
    name: 'course-detail',
    component: CourseDetailView,
    props: true
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    meta: { requiresAuth: true }
  },
  {
    path: '/enrollments',
    name: 'enrollments',
    component: EnrollmentsView,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    name: 'admin-dashboard',
    component: AdminDashboardView,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/courses',
    name: 'admin-courses',
    component: AdminCoursesView,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: AdminUsersView,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/payments',
    name: 'admin-payments',
    component: () => import('@/views/admin/AdminPaymentsView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/test/getnet',
    name: 'getnet-test',
    component: GetnetTestView,
    meta: { requiresAuth: true, requiresAdmin: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = authService.isAuthenticated()
  const user = authService.getStoredUser()
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
    return
  }
  
  // Check if route requires admin role
  if (to.meta.requiresAdmin && (!user || user.user_type !== 'admin')) {
    next('/dashboard')
    return
  }
  
  // Redirect authenticated users away from guest-only pages
  if (to.meta.guest && isAuthenticated) {
    next('/dashboard')
    return
  }
  
  next()
})

export default router
