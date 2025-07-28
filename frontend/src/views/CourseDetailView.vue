<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-screen">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Course Details -->
    <div v-else-if="course" class="relative">
      <!-- Hero Section -->
      <div class="bg-gradient-to-r from-primary-600 to-primary-800 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div>
              <h1 class="text-4xl font-bold text-white mb-4">{{ course.name }}</h1>
              <p class="text-xl text-blue-100 mb-6">{{ course.description }}</p>
              
              <div class="flex items-center space-x-6 text-blue-100">
                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ course.sessions?.length || 0 }} sessões
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  {{ course.enrollments_count || 0 }} alunos
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow-xl p-6">
              <div v-if="course.image" class="mb-6">
                <img 
                  :src="course.image" 
                  :alt="course.name"
                  class="w-full h-48 object-cover rounded-lg"
                />
              </div>

              <div class="text-center mb-6">
                <div class="text-3xl font-bold text-gray-900 mb-2">
                  {{ formatCurrency(course.price) }}
                </div>
                <p class="text-gray-600">Acesso vitalício ao curso</p>
              </div>

              <!-- Purchase Section -->
              <div v-if="!isAuthenticated">
                <router-link 
                  to="/login" 
                  class="btn-primary w-full mb-3"
                >
                  Fazer Login para Comprar
                </router-link>
                <p class="text-sm text-gray-500 text-center">
                  Ou 
                  <router-link to="/register" class="text-primary-600 hover:text-primary-500">
                    cadastre-se gratuitamente
                  </router-link>
                </p>
              </div>

              <div v-else-if="isEnrolled">
                <div class="alert-success mb-4">
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  Você já está matriculado neste curso!
                </div>
                <router-link 
                  to="/dashboard" 
                  class="btn-secondary w-full"
                >
                  Ir para Meus Cursos
                </router-link>
              </div>

              <div v-else>
                <button
                  @click="showPaymentModal = true"
                  class="btn-primary w-full mb-3"
                  :disabled="purchasing"
                >
                  {{ purchasing ? 'Processando...' : 'Comprar Agora' }}
                </button>
                <div class="flex items-center justify-center text-sm text-gray-500">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  Pagamento 100% seguro
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Course Content -->
      <div class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
              <!-- Course Sessions -->
              <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Conteúdo do Curso</h2>
                
                <div v-if="course.sessions && course.sessions.length > 0" class="space-y-4">
                  <div 
                    v-for="(session, index) in course.sessions" 
                    :key="session.id"
                    class="border border-gray-200 rounded-lg p-4"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                          <span class="text-sm font-medium text-primary-600">{{ index + 1 }}</span>
                        </div>
                        <div>
                          <h3 class="text-lg font-medium text-gray-900">{{ session.title }}</h3>
                          <p class="text-sm text-gray-500">{{ session.description }}</p>
                        </div>
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ formatDateTime(session.start_datetime) }}
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="text-center py-8">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-gray-900">Sessões em breve</h3>
                  <p class="mt-1 text-sm text-gray-500">As datas das aulas serão divulgadas em breve.</p>
                </div>
              </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
              <!-- Course Info -->
              <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Curso</h3>
                
                <div class="space-y-4">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-gray-600">{{ course.sessions?.length || 0 }} sessões</span>
                  </div>

                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <span class="text-sm text-gray-600">Certificado de conclusão</span>
                  </div>

                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-sm text-gray-600">Acesso vitalício</span>
                  </div>

                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-sm text-gray-600">Suporte completo</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Modal -->
      <div v-if="showPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Finalizar Compra</h3>
              <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="mb-6">
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">{{ course.name }}</h4>
                <p class="text-2xl font-bold text-primary-600">{{ formatCurrency(course.price) }}</p>
              </div>
            </div>

            <div class="space-y-4">
              <!-- Payment Method Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Forma de Pagamento
                </label>
                <div class="space-y-2">
                  <label class="flex items-center">
                    <input
                      v-model="paymentMethod"
                      type="radio"
                      value="credit_card"
                      class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300"
                    />
                    <span class="ml-2 text-sm text-gray-900">Cartão de Crédito</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      v-model="paymentMethod"
                      type="radio"
                      value="boleto"
                      class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300"
                    />
                    <span class="ml-2 text-sm text-gray-900">Boleto Bancário</span>
                  </label>
                </div>
              </div>

              <!-- Credit Card Form -->
              <div v-if="paymentMethod === 'credit_card'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Número do Cartão
                  </label>
                  <input
                    v-model="cardForm.number"
                    type="text"
                    placeholder="1234 5678 9012 3456"
                    class="input"
                  />
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Validade
                    </label>
                    <input
                      v-model="cardForm.expiry"
                      type="text"
                      placeholder="MM/AA"
                      class="input"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      CVV
                    </label>
                    <input
                      v-model="cardForm.cvv"
                      type="text"
                      placeholder="123"
                      class="input"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nome no Cartão
                  </label>
                  <input
                    v-model="cardForm.holderName"
                    type="text"
                    placeholder="João Silva"
                    class="input"
                  />
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="showPaymentModal = false"
                  class="btn-secondary"
                >
                  Cancelar
                </button>
                <button
                  @click="processPurchase"
                  :disabled="purchasing"
                  class="btn-primary"
                >
                  {{ purchasing ? 'Processando...' : 'Finalizar Compra' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 15.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Curso não encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">O curso que você está procurando não existe.</p>
        <div class="mt-6">
          <router-link to="/courses" class="btn-primary">
            Ver Todos os Cursos
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { courseService, paymentService, enrollmentService } from '@/services'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'CourseDetailView',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const authStore = useAuthStore()
    
    const loading = ref(true)
    const purchasing = ref(false)
    const course = ref(null)
    const showPaymentModal = ref(false)
    const paymentMethod = ref('credit_card')
    const isEnrolled = ref(false)

    const cardForm = ref({
      number: '',
      expiry: '',
      cvv: '',
      holderName: ''
    })

    const isAuthenticated = computed(() => authStore.isAuthenticated)

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value || 0)
    }

    const formatDateTime = (dateTime) => {
      return new Date(dateTime).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const loadCourse = async () => {
      try {
        loading.value = true
        const data = await courseService.getCourse(route.params.id)
        course.value = data.data || data
        
        // Verificar se o usuário já está matriculado
        if (isAuthenticated.value) {
          await checkEnrollment()
        }
      } catch (error) {
        console.error('Erro ao carregar curso:', error)
        course.value = null
      } finally {
        loading.value = false
      }
    }

    const checkEnrollment = async () => {
      try {
        const enrollments = await enrollmentService.getEnrollments()
        isEnrolled.value = enrollments.some(e => e.course_id === parseInt(route.params.id))
      } catch (error) {
        console.error('Erro ao verificar matrícula:', error)
      }
    }

    const processPurchase = async () => {
      try {
        purchasing.value = true

        const paymentData = {
          course_id: course.value.id,
          payment_method: paymentMethod.value,
          amount: course.value.price
        }

        if (paymentMethod.value === 'credit_card') {
          paymentData.card = {
            number: cardForm.value.number.replace(/\s/g, ''),
            expiry_month: cardForm.value.expiry.split('/')[0],
            expiry_year: '20' + cardForm.value.expiry.split('/')[1],
            cvv: cardForm.value.cvv,
            holder_name: cardForm.value.holderName
          }
        }

        const result = await paymentService.createPayment(paymentData)
        
        if (result.success) {
          showPaymentModal.value = false
          
          if (paymentMethod.value === 'boleto' && result.data.boleto_url) {
            // Mostrar modal de sucesso com link do boleto
            alert('Boleto gerado com sucesso! Você será redirecionado para o pagamento.')
            window.open(result.data.boleto_url, '_blank')
          } else {
            alert('Pagamento processado com sucesso! Bem-vindo ao curso!')
          }
          
          router.push('/dashboard')
        }
      } catch (error) {
        console.error('Erro ao processar compra:', error)
        alert('Erro ao processar pagamento. Tente novamente.')
      } finally {
        purchasing.value = false
      }
    }

    onMounted(() => {
      loadCourse()
    })

    return {
      loading,
      purchasing,
      course,
      showPaymentModal,
      paymentMethod,
      isEnrolled,
      cardForm,
      isAuthenticated,
      formatCurrency,
      formatDateTime,
      processPurchase
    }
  }
}
</script>
