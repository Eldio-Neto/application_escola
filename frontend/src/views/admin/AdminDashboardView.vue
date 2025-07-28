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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Receita Total</h3>
            <p class="text-3xl font-bold text-green-600">{{ formatCurrency(stats.totalRevenue) }}</p>
            <p class="text-sm text-gray-500 mt-1">Este mês</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <router-link 
          to="/admin/courses" 
          class="card bg-white shadow-lg hover:shadow-xl transition-shadow duration-200 group"
        >
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Gerenciar Cursos</h3>
                <p class="text-gray-600">Criar, editar e administrar cursos</p>
              </div>
            </div>
          </div>
        </router-link>
        
        <router-link 
          to="/admin/users" 
          class="card bg-white shadow-lg hover:shadow-xl transition-shadow duration-200 group"
        >
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center group-hover:bg-success-200 transition-colors">
                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Gerenciar Usuários</h3>
                <p class="text-gray-600">Administrar alunos e instrutores</p>
              </div>
            </div>
          </div>
        </router-link>
        
        <router-link 
          to="/admin/enrollments" 
          class="card bg-white shadow-lg hover:shadow-xl transition-shadow duration-200 group"
        >
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-warning-100 rounded-lg flex items-center justify-center group-hover:bg-warning-200 transition-colors">
                <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Matrículas</h3>
                <p class="text-gray-600">Visualizar e gerenciar matrículas</p>
              </div>
            </div>
          </div>
        </router-link>
        
        <router-link 
          to="/admin/payment-settings" 
          class="card bg-white shadow-lg hover:shadow-xl transition-shadow duration-200 group"
        >
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Configurações de Pagamento</h3>
                <p class="text-gray-600">Gerenciar métodos e taxas</p>
              </div>
            </div>
          </div>
        </router-link>

        <div class="card bg-white shadow-lg">
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Relatórios</h3>
                <p class="text-gray-600">Analytics e métricas</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-2">
                  Em breve
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="card bg-white shadow-lg">
          <div class="card-body p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Configurações</h3>
                <p class="text-gray-600">Configurações gerais do sistema</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-2">
                  Em breve
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

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
        // Simular dados para demonstração
        setTimeout(() => {
          stats.value = {
            totalCourses: 25,
            activeCourses: 20,
            totalUsers: 150,
            totalStudents: 120,
            totalEnrollments: 85,
            activeEnrollments: 65,
            totalRevenue: 12500.00
          }
          loading.value = false
        }, 1000)
      } catch (error) {
        console.error('Erro ao carregar dados do dashboard:', error)
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
