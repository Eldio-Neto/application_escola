<template>
  <div class="max-w-4xl mx-auto p-6 space-y-8">
    <div class="bg-white rounded-lg shadow-md p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">
        Teste de Integração - Asaas
      </h1>
      
      <!-- Status da Conexão -->
      <div class="mb-6">
        <button
          @click="testConnection"
          :disabled="loading"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? 'Testando...' : 'Testar Conexão Asaas' }}
        </button>
        
        <div v-if="connectionResult" class="mt-4 p-4 rounded-md" :class="connectionResult.success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
          <p class="font-medium">{{ connectionResult.message }}</p>
          <p v-if="connectionResult.environment" class="text-sm">Ambiente: {{ connectionResult.environment }}</p>
          <p v-if="connectionResult.account_name && connectionResult.account_name !== 'N/A'" class="text-sm">Conta: {{ connectionResult.account_name }}</p>
        </div>
      </div>

      <!-- Seleção de Curso -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Selecionar Curso para Teste
        </label>
        <select 
          v-model="selectedCourse" 
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Selecione um curso...</option>
          <option v-for="course in courses" :key="course.id" :value="course">
            {{ course.title }} - R$ {{ course.price }}
          </option>
        </select>
      </div>

      <!-- Abas de Pagamento -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in paymentTabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.id
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Formulário PIX -->
      <div v-if="activeTab === 'pix'" class="space-y-4">
        <h3 class="text-lg font-medium text-gray-900">Pagamento PIX - Asaas</h3>
        <button
          @click="processPixPayment"
          :disabled="!selectedCourse || processing"
          class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 disabled:opacity-50"
        >
          {{ processing ? 'Gerando PIX...' : 'Gerar PIX' }}
        </button>
      </div>

      <!-- Formulário Cartão -->
      <div v-if="activeTab === 'card'" class="space-y-4">
        <h3 class="text-lg font-medium text-gray-900">Cartão de Crédito - Asaas</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número do Cartão</label>
            <input
              v-model="cardForm.number"
              type="text"
              placeholder="0000 0000 0000 0000"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome no Cartão</label>
            <input
              v-model="cardForm.holderName"
              type="text"
              placeholder="NOME DO TITULAR"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Validade</label>
            <div class="flex space-x-2">
              <input
                v-model="cardForm.expiryMonth"
                type="text"
                placeholder="MM"
                maxlength="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
              <input
                v-model="cardForm.expiryYear"
                type="text"  
                placeholder="AAAA"
                maxlength="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
            <input
              v-model="cardForm.cvv"
              type="text"
              placeholder="000"
              maxlength="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
        </div>
        <button
          @click="processCreditCard"
          :disabled="!selectedCourse || processing || !isCardFormValid"
          class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
        >
          {{ processing ? 'Processando...' : 'Processar Cartão' }}
        </button>
      </div>

      <!-- Formulário Boleto -->
      <div v-if="activeTab === 'boleto'" class="space-y-4">
        <h3 class="text-lg font-medium text-gray-900">Boleto Bancário - Asaas</h3>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Dias para Vencimento</label>
          <select 
            v-model="boletoForm.dueDays"
            class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="3">3 dias</option>
            <option value="7">7 dias</option>
            <option value="15">15 dias</option>
            <option value="30">30 dias</option>
          </select>
        </div>
        <button
          @click="processBoletoPayment"
          :disabled="!selectedCourse || processing"
          class="bg-orange-600 text-white px-6 py-2 rounded-md hover:bg-orange-700 disabled:opacity-50"
        >
          {{ processing ? 'Gerando Boleto...' : 'Gerar Boleto' }}
        </button>
      </div>

      <!-- Resultado do Pagamento -->
      <div v-if="paymentResult" class="mt-6 p-4 rounded-md" :class="paymentResult.success ? 'bg-green-50' : 'bg-red-50'">
        <h4 class="font-medium" :class="paymentResult.success ? 'text-green-800' : 'text-red-800'">
          {{ paymentResult.message }}
        </h4>
        
        <!-- PIX QR Code -->
        <div v-if="paymentResult.pix_qr_code_image" class="mt-4">
          <p class="text-sm text-gray-600 mb-2">QR Code PIX:</p>
          <img :src="paymentResult.pix_qr_code_image" alt="QR Code PIX" class="max-w-xs">
          <div v-if="paymentResult.pix_copy_paste" class="mt-2">
            <p class="text-sm text-gray-600">Código PIX (Copia e Cola):</p>
            <textarea 
              :value="paymentResult.pix_copy_paste" 
              readonly
              class="w-full h-20 p-2 text-xs bg-gray-100 border rounded"
            ></textarea>
          </div>
        </div>

        <!-- URL do Boleto -->
        <div v-if="paymentResult.boleto_url" class="mt-4">
          <a 
            :href="paymentResult.boleto_url" 
            target="_blank"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Ver Boleto
          </a>
        </div>

        <!-- Detalhes do Erro -->
        <div v-if="paymentResult.error" class="mt-2">
          <p class="text-sm text-red-600">{{ paymentResult.error }}</p>
          <details v-if="paymentResult.details && paymentResult.details.length" class="mt-2">
            <summary class="text-sm text-red-500 cursor-pointer">Ver detalhes</summary>
            <pre class="text-xs text-red-600 mt-1">{{ JSON.stringify(paymentResult.details, null, 2) }}</pre>
          </details>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'AsaasTestView',
  setup() {
    const authStore = useAuthStore()
    
    const loading = ref(false)
    const processing = ref(false)
    const connectionResult = ref(null)
    const courses = ref([])
    const selectedCourse = ref('')
    const activeTab = ref('pix')
    const paymentResult = ref(null)

    const paymentTabs = [
      { id: 'pix', name: 'PIX' },
      { id: 'card', name: 'Cartão de Crédito' },
      { id: 'boleto', name: 'Boleto' }
    ]

    const cardForm = ref({
      number: '4111111111111111', // Cartão de teste
      holderName: 'TESTE ASAAS',
      expiryMonth: '12',
      expiryYear: '2025',
      cvv: '123'
    })

    const boletoForm = ref({
      dueDays: 3
    })

    const isCardFormValid = computed(() => {
      return cardForm.value.number.length >= 13 &&
             cardForm.value.holderName.length >= 3 &&
             cardForm.value.expiryMonth.length === 2 &&
             cardForm.value.expiryYear.length === 4 &&
             cardForm.value.cvv.length >= 3
    })

    const testConnection = async () => {
      loading.value = true
      try {
        const response = await fetch('/api/asaas/test-connection', {
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'Content-Type': 'application/json'
          }
        })
        connectionResult.value = await response.json()
      } catch (error) {
        connectionResult.value = {
          success: false,
          message: 'Erro na conexão: ' + error.message
        }
      } finally {
        loading.value = false
      }
    }

    const loadCourses = async () => {
      try {
        const response = await fetch('/api/courses', {
          headers: {
            'Authorization': `Bearer ${authStore.token}`
          }
        })
        const data = await response.json()
        courses.value = data.data || []
      } catch (error) {
        console.error('Erro ao carregar cursos:', error)
      }
    }

    const processPixPayment = async () => {
      if (!selectedCourse.value) return
      
      processing.value = true
      paymentResult.value = null
      
      try {
        const response = await fetch('/api/asaas/pix', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            course_id: selectedCourse.value.id
          })
        })

        const result = await response.json()
        paymentResult.value = {
          success: response.ok,
          message: result.message,
          pix_qr_code_image: result.pix_qr_code_image,
          pix_copy_paste: result.pix_copy_paste,
          error: result.error,
          details: result.details
        }
      } catch (error) {
        paymentResult.value = {
          success: false,
          message: 'Erro na requisição',
          error: error.message
        }
      } finally {
        processing.value = false
      }
    }

    const processCreditCard = async () => {
      if (!selectedCourse.value || !isCardFormValid.value) return
      
      processing.value = true
      paymentResult.value = null
      
      try {
        const response = await fetch('/api/asaas/credit-card', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            course_id: selectedCourse.value.id,
            card_number: cardForm.value.number.replace(/\s/g, ''),
            cardholder_name: cardForm.value.holderName,
            expiration_month: cardForm.value.expiryMonth,
            expiration_year: cardForm.value.expiryYear,
            security_code: cardForm.value.cvv,
            installments: 1
          })
        })

        const result = await response.json()
        paymentResult.value = {
          success: response.ok,
          message: result.message,
          error: result.error,
          details: result.details
        }
      } catch (error) {
        paymentResult.value = {
          success: false,
          message: 'Erro na requisição',
          error: error.message
        }
      } finally {
        processing.value = false
      }
    }

    const processBoletoPayment = async () => {
      if (!selectedCourse.value) return
      
      processing.value = true
      paymentResult.value = null
      
      try {
        const response = await fetch('/api/asaas/boleto', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            course_id: selectedCourse.value.id,
            due_days: boletoForm.value.dueDays
          })
        })

        const result = await response.json()
        paymentResult.value = {
          success: response.ok,
          message: result.message,
          boleto_url: result.boleto_url,
          error: result.error,
          details: result.details
        }
      } catch (error) {
        paymentResult.value = {
          success: false,
          message: 'Erro na requisição',
          error: error.message
        }
      } finally {
        processing.value = false
      }
    }

    onMounted(() => {
      loadCourses()
    })

    return {
      loading,
      processing,
      connectionResult,
      courses,
      selectedCourse,
      activeTab,
      paymentTabs,
      paymentResult,
      cardForm,
      boletoForm,
      isCardFormValid,
      testConnection,
      processPixPayment,
      processCreditCard,
      processBoletoPayment
    }
  }
}
</script>
