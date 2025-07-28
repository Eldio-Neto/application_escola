<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Administrativo</h1>
        <p class="text-gray-600">Visão geral da plataforma de cursos</p>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Stats Cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card bg-white shadow-lg">
          <div class="card-body text-center p-6">
            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mx-auto mb-4">
              <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Total de Cursos</h3>
            <p class="text-3xl font-bold text-primary-600">{{ stats.totalCourses }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ stats.activeCourses }} ativos</p>
          </div>
        </div>
        
        <div class="card bg-white shadow-lg">
          <div class="card-body text-center p-6">
            <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center mx-auto mb-4">
              <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Total de Usuários</h3>
            <p class="text-3xl font-bold text-success-600">{{ stats.totalUsers }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ stats.totalStudents }} alunos</p>
          </div>
        </div>
        
        <div class="card bg-white shadow-lg">
          <div class="card-body text-center p-6">
            <div class="w-12 h-12 bg-warning-100 rounded-lg flex items-center justify-center mx-auto mb-4">
              <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Matrículas</h3>
            <p class="text-3xl font-bold text-warning-600">{{ stats.totalEnrollments }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ stats.activeEnrollments }} ativas</p>
          </div>
        </div>
        
        <div class="card bg-white shadow-lg">
          <div class="card-body text-center p-6">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Receita Total</h3>
            <p class="text-3xl font-bold text-green-600">{{ formatCurrency(stats.totalRevenue) }}</p>
            <p class="text-sm text-gray-500 mt-1">Últimos 30 dias</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <router-link 
          to="/admin/courses" 
          class="card bg-white hover:shadow-lg transition-shadow duration-200"
        >
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Gerenciar Cursos</h3>
                <p class="text-gray-600">Criar, editar e organizar cursos</p>
              </div>
            </div>
          </div>
        </router-link>

        <router-link 
          to="/admin/users" 
          class="card bg-white hover:shadow-lg transition-shadow duration-200"
        >
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Gerenciar Usuários</h3>
                <p class="text-gray-600">Ver alunos e suas matrículas</p>
              </div>
            </div>
          </div>
        </router-link>

        <router-link 
          to="/admin/payments" 
          class="card bg-white hover:shadow-lg transition-shadow duration-200"
        >
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Pagamentos</h3>
                <p class="text-gray-600">Acompanhar transações e boletos</p>
              </div>
            </div>
          </div>
        </router-link>
      </div>

    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { adminService } from '@/services'

export default {
  name: 'AdminDashboardView',
  setup() {
    const loading = ref(true)
    const stats = ref({
      totalCourses: 0,
      activeCourses: 0,
      totalUsers: 0,
      totalStudents: 0,
      totalEnrollments: 0,
      activeEnrollments: 0,
      totalRevenue: 0
    })

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value || 0)
    }

    const loadDashboardData = async () => {
      try {
        loading.value = true
        const data = await adminService.getDashboardStats()
        stats.value = data
      } catch (error) {
        console.error('Erro ao carregar dados do dashboard:', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      loadDashboardData()
    })

    return {
      loading,
      stats,
      formatCurrency
    }
  }
}
</script>
