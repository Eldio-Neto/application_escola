<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gerenciar Usuários</h1>
          <p class="text-gray-600">Visualize e gerencie todos os usuários da plataforma</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nome ou email..."
              class="input"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
            <select v-model="filters.userType" class="input">
              <option value="">Todos</option>
              <option value="admin">Administradores</option>
              <option value="student">Alunos</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="loadUsers"
              class="btn-secondary mr-2"
            >
              Filtrar
            </button>
            <button
              @click="clearFilters"
              class="btn-outline"
            >
              Limpar
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total de Usuários</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.totalUsers }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Alunos</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.totalStudents }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Administradores</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.totalAdmins }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Users Table -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Usuário
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Contato
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cursos
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cadastro
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in filteredUsers" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-primary-600">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="getUserTypeBadgeClass(user.user_type)"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ getUserTypeText(user.user_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div>{{ user.phone || 'Não informado' }}</div>
                  <div class="text-gray-500">{{ user.cpf || 'CPF não informado' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.enrollments_count || 0 }} matriculado(s)
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="viewUserDetails(user)"
                    class="text-primary-600 hover:text-primary-900 mr-3"
                  >
                    Detalhes
                  </button>
                  <button
                    v-if="user.user_type === 'student'"
                    @click="viewUserCourses(user)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Cursos
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="filteredUsers.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum usuário encontrado</h3>
          <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros de busca.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { adminService } from '@/services'

export default {
  name: 'AdminUsersView',
  setup() {
    const loading = ref(true)
    const users = ref([])
    
    const filters = ref({
      search: '',
      userType: ''
    })

    const stats = ref({
      totalUsers: 0,
      totalStudents: 0,
      totalAdmins: 0
    })

    const filteredUsers = computed(() => {
      let result = users.value

      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        result = result.filter(user => 
          user.name.toLowerCase().includes(search) ||
          user.email.toLowerCase().includes(search)
        )
      }

      if (filters.value.userType) {
        result = result.filter(user => user.user_type === filters.value.userType)
      }

      return result
    })

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
    }

    const getUserTypeBadgeClass = (userType) => {
      const classes = {
        'admin': 'bg-purple-100 text-purple-800',
        'student': 'bg-blue-100 text-blue-800'
      }
      return classes[userType] || 'bg-gray-100 text-gray-800'
    }

    const getUserTypeText = (userType) => {
      const texts = {
        'admin': 'Administrador',
        'student': 'Aluno'
      }
      return texts[userType] || userType
    }

    const loadUsers = async () => {
      try {
        loading.value = true
        const data = await adminService.getUsers()
        users.value = data.data || data
        
        // Calcular estatísticas
        stats.value = {
          totalUsers: users.value.length,
          totalStudents: users.value.filter(u => u.user_type === 'student').length,
          totalAdmins: users.value.filter(u => u.user_type === 'admin').length
        }
      } catch (error) {
        console.error('Erro ao carregar usuários:', error)
      } finally {
        loading.value = false
      }
    }

    const clearFilters = () => {
      filters.value = {
        search: '',
        userType: ''
      }
    }

    const viewUserDetails = (user) => {
      // TODO: Implementar modal de detalhes do usuário
      console.log('Ver detalhes do usuário:', user.name)
    }

    const viewUserCourses = (user) => {
      // TODO: Implementar modal com cursos do usuário
      console.log('Ver cursos do usuário:', user.name)
    }

    onMounted(() => {
      loadUsers()
    })

    return {
      loading,
      users,
      filters,
      stats,
      filteredUsers,
      formatDate,
      getUserTypeBadgeClass,
      getUserTypeText,
      loadUsers,
      clearFilters,
      viewUserDetails,
      viewUserCourses
    }
  }
}
</script>
