<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gerenciar Pagamentos</h1>
          <p class="text-gray-600">Acompanhe transações e boletos dos alunos</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" class="input">
              <option value="">Todos</option>
              <option value="pending">Pendente</option>
              <option value="paid">Pago</option>
              <option value="failed">Falhou</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Método</label>
            <select v-model="filters.method" class="input">
              <option value="">Todos</option>
              <option value="credit_card">Cartão de Crédito</option>
              <option value="boleto">Boleto</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Curso</label>
            <select v-model="filters.course" class="input">
              <option value="">Todos os cursos</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">
                {{ course.name }}
              </option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="loadPayments"
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
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Receita Total</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.totalRevenue) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Pagamentos</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.totalPayments }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Pendentes</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.pendingPayments }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Falharam</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.failedPayments }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Payments Table -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aluno
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Curso
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Valor
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Método
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="payment in payments" :key="payment.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ payment.user?.name }}</div>
                  <div class="text-sm text-gray-500">{{ payment.user?.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ payment.course?.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getMethodBadgeClass(payment.payment_method)">
                    {{ getMethodText(payment.payment_method) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getStatusBadgeClass(payment.status)">
                    {{ getStatusText(payment.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(payment.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    v-if="payment.boleto_url"
                    @click="openBoleto(payment.boleto_url)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    Ver Boleto
                  </button>
                  <button
                    @click="viewPaymentDetails(payment)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    Detalhes
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="payments.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum pagamento encontrado</h3>
          <p class="mt-1 text-sm text-gray-500">Os pagamentos aparecerão aqui quando os alunos comprarem cursos.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { adminService, courseService } from '@/services'

export default {
  name: 'AdminPaymentsView',
  setup() {
    const loading = ref(true)
    const payments = ref([])
    const courses = ref([])
    
    const filters = ref({
      status: '',
      method: '',
      course: ''
    })

    const stats = ref({
      totalRevenue: 0,
      totalPayments: 0,
      pendingPayments: 0,
      failedPayments: 0
    })

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value || 0)
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const getStatusBadgeClass = (status) => {
      const classes = {
        'paid': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'failed': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const getStatusText = (status) => {
      const texts = {
        'paid': 'Pago',
        'pending': 'Pendente',
        'failed': 'Falhou',
        'cancelled': 'Cancelado'
      }
      return texts[status] || status
    }

    const getMethodBadgeClass = (method) => {
      const classes = {
        'credit_card': 'bg-blue-100 text-blue-800',
        'boleto': 'bg-purple-100 text-purple-800'
      }
      return classes[method] || 'bg-gray-100 text-gray-800'
    }

    const getMethodText = (method) => {
      const texts = {
        'credit_card': 'Cartão',
        'boleto': 'Boleto'
      }
      return texts[method] || method
    }

    const loadPayments = async () => {
      try {
        loading.value = true
        const data = await adminService.getPayments(filters.value)
        payments.value = data.data || data.payments || []
        
        // Calcular estatísticas
        stats.value = {
          totalRevenue: payments.value.filter(p => p.status === 'paid').reduce((sum, p) => sum + Number(p.amount), 0),
          totalPayments: payments.value.length,
          pendingPayments: payments.value.filter(p => p.status === 'pending').length,
          failedPayments: payments.value.filter(p => p.status === 'failed').length
        }
      } catch (error) {
        console.error('Erro ao carregar pagamentos:', error)
      } finally {
        loading.value = false
      }
    }

    const loadCourses = async () => {
      try {
        const data = await courseService.getCourses()
        courses.value = data.data || data
      } catch (error) {
        console.error('Erro ao carregar cursos:', error)
      }
    }

    const clearFilters = () => {
      filters.value = {
        status: '',
        method: '',
        course: ''
      }
      loadPayments()
    }

    const openBoleto = (url) => {
      window.open(url, '_blank')
    }

    const viewPaymentDetails = (payment) => {
      // TODO: Implementar modal de detalhes do pagamento
      console.log('Ver detalhes do pagamento:', payment)
    }

    onMounted(async () => {
      await Promise.all([
        loadPayments(),
        loadCourses()
      ])
    })

    return {
      loading,
      payments,
      courses,
      filters,
      stats,
      formatCurrency,
      formatDate,
      getStatusBadgeClass,
      getStatusText,
      getMethodBadgeClass,
      getMethodText,
      loadPayments,
      clearFilters,
      openBoleto,
      viewPaymentDetails
    }
  }
}
</script>
