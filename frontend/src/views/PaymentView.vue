<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Finalizar Pagamento</h1>
        <p class="text-gray-600 mt-2">Complete sua compra e tenha acesso imediato ao curso</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Resumo do Curso -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumo do Pedido</h3>
            
            <div v-if="course" class="space-y-4">
              <div class="flex items-start space-x-4">
                <img :src="course.image || '/placeholder-course.jpg'" 
                     :alt="course.title" 
                     class="w-16 h-12 object-cover rounded">
                <div class="flex-1">
                  <h4 class="font-medium text-gray-900">{{ course.title }}</h4>
                  <p class="text-sm text-gray-600">{{ course.duration || 'Acesso vitalício' }}</p>
                </div>
              </div>
              
              <div class="border-t pt-4">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                  <span>Subtotal:</span>
                  <span>{{ formatCurrency(course.price) }}</span>
                </div>
                
                <div v-if="installmentData && installmentData.has_interest" 
                     class="flex justify-between text-sm text-gray-600 mb-2">
                  <span>Juros ({{ installmentData.interest_rate }}% a.m.):</span>
                  <span>{{ formatCurrency(installmentData.total_amount - course.price) }}</span>
                </div>
                
                <div class="flex justify-between text-lg font-semibold text-gray-900 border-t pt-2">
                  <span>Total:</span>
                  <span>{{ formatCurrency(finalAmount) }}</span>
                </div>
                
                <div v-if="installmentData && selectedInstallments > 1" 
                     class="text-sm text-gray-600 mt-2">
                  {{ selectedInstallments }}x de {{ formatCurrency(installmentData.installment_value) }}
                  <span v-if="!installmentData.has_interest" class="text-green-600 font-medium">
                    sem juros
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulário de Pagamento -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <!-- Seleção do Gateway -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Escolha a forma de pagamento</h3>
              <div class="grid grid-cols-2 gap-4">
                <button @click="selectedGateway = 'asaas'"
                        :class="['p-4 border-2 rounded-lg transition-all', 
                                selectedGateway === 'asaas' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300']">
                  <div class="text-sm font-medium">Asaas</div>
                  <div class="text-xs text-gray-500">PIX, Cartão, Boleto</div>
                </button>
                
                <button @click="selectedGateway = 'getnet'"
                        :class="['p-4 border-2 rounded-lg transition-all', 
                                selectedGateway === 'getnet' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300']">
                  <div class="text-sm font-medium">Getnet</div>
                  <div class="text-xs text-gray-500">PIX, Cartão, Boleto</div>
                </button>
              </div>
            </div>

            <!-- Abas de Métodos de Pagamento -->
            <div class="mb-6">
              <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                  <button @click="selectedMethod = 'pix'"
                          :class="['py-2 px-1 border-b-2 font-medium text-sm', 
                                  selectedMethod === 'pix' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                    PIX
                  </button>
                  <button @click="selectedMethod = 'credit_card'"
                          :class="['py-2 px-1 border-b-2 font-medium text-sm', 
                                  selectedMethod === 'credit_card' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                    Cartão de Crédito
                  </button>
                  <button @click="selectedMethod = 'boleto'"
                          :class="['py-2 px-1 border-b-2 font-medium text-sm', 
                                  selectedMethod === 'boleto' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                    Boleto
                  </button>
                </nav>
              </div>
            </div>

            <!-- Conteúdo dos Métodos de Pagamento -->
            <div class="space-y-6">
              <!-- PIX -->
              <div v-if="selectedMethod === 'pix'" class="space-y-4">
                <h4 class="font-semibold text-gray-900">Pagamento via PIX</h4>
                <p class="text-gray-600 text-sm">
                  Pagamento instantâneo e seguro. O curso será liberado automaticamente após a confirmação.
                </p>
                
                <div v-if="pixResult" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <h5 class="font-semibold text-blue-900 mb-2">QR Code PIX</h5>
                  <div class="text-center mb-4" v-if="pixResult.qr_code_image">
                    <img :src="'data:image/png;base64,' + pixResult.qr_code_image" 
                         alt="QR Code PIX" 
                         class="mx-auto max-w-48">
                  </div>
                  <div class="bg-white rounded p-3 mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Código PIX (Copiar e Colar):
                    </label>
                    <div class="flex">
                      <input type="text" 
                             :value="pixResult.qr_code" 
                             readonly 
                             class="flex-1 p-2 border border-gray-300 rounded-l text-sm">
                      <button @click="copyPixCode" 
                              class="px-4 py-2 bg-primary-600 text-white rounded-r hover:bg-primary-700">
                        Copiar
                      </button>
                    </div>
                  </div>
                  <p class="text-sm text-blue-700">
                    Valor: {{ formatCurrency(pixResult.amount) }}
                  </p>
                </div>
              </div>

              <!-- Cartão de Crédito -->
              <div v-if="selectedMethod === 'credit_card'" class="space-y-4">
                <h4 class="font-semibold text-gray-900">Cartão de Crédito</h4>
                
                <!-- Seleção de Parcelas -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Parcelas:
                  </label>
                  <select v-model="selectedInstallments" 
                          @change="calculateInstallments"
                          class="w-full p-2 border border-gray-300 rounded-md">
                    <option v-for="option in installmentOptions" 
                            :key="option.installments" 
                            :value="option.installments">
                      {{ option.installments }}x de {{ formatCurrency(option.installment_value) }}
                      <span v-if="option.has_interest"> (com juros)</span>
                      <span v-else> (sem juros)</span>
                    </option>
                  </select>
                </div>

                <!-- Dados do Cartão -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Número do Cartão:
                    </label>
                    <input v-model="cardForm.number" 
                           type="text" 
                           placeholder="0000 0000 0000 0000"
                           @input="formatCardNumber"
                           maxlength="19"
                           class="w-full p-2 border border-gray-300 rounded-md">
                  </div>
                  
                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Nome no Cartão:
                    </label>
                    <input v-model="cardForm.holderName" 
                           type="text" 
                           placeholder="Nome completo"
                           class="w-full p-2 border border-gray-300 rounded-md">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Validade:
                    </label>
                    <input v-model="cardForm.expiry" 
                           type="text" 
                           placeholder="MM/AA"
                           @input="formatExpiry"
                           maxlength="5"
                           class="w-full p-2 border border-gray-300 rounded-md">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      CVV:
                    </label>
                    <input v-model="cardForm.cvv" 
                           type="text" 
                           placeholder="123"
                           @input="formatCVV"
                           maxlength="4"
                           class="w-full p-2 border border-gray-300 rounded-md">
                  </div>
                </div>

                <div v-if="creditCardResult" class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <h5 class="font-semibold text-green-900 mb-2">Pagamento Processado!</h5>
                  <p class="text-green-700">
                    Status: {{ creditCardResult.status === 'paid' ? 'Aprovado' : 'Processando' }}
                  </p>
                  <p class="text-green-700">
                    Valor: {{ formatCurrency(creditCardResult.amount) }}
                  </p>
                  <p class="text-green-700">
                    Parcelas: {{ creditCardResult.installments }}x de {{ formatCurrency(creditCardResult.installment_value) }}
                  </p>
                </div>
              </div>

              <!-- Boleto -->
              <div v-if="selectedMethod === 'boleto'" class="space-y-4">
                <h4 class="font-semibold text-gray-900">Boleto Bancário</h4>
                <p class="text-gray-600 text-sm">
                  O boleto vence em 3 dias. Após o pagamento, o curso será liberado em até 2 dias úteis.
                </p>
                
                <div v-if="boletoResult" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                  <h5 class="font-semibold text-yellow-900 mb-2">Boleto Gerado</h5>
                  <div class="space-y-2">
                    <p class="text-yellow-700">
                      Valor: {{ formatCurrency(boletoResult.amount) }}
                    </p>
                    <p class="text-yellow-700">
                      Vencimento: {{ formatDate(boletoResult.due_date) }}
                    </p>
                    <div class="flex space-x-2">
                      <a :href="boletoResult.boleto_url" 
                         target="_blank" 
                         class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        <i class="fas fa-download mr-2"></i>
                        Baixar Boleto
                      </a>
                      <button @click="copyBarcode" 
                              class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        Copiar Código
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Botão de Pagamento -->
            <div class="mt-8 pt-6 border-t">
              <button @click="processPayment" 
                      :disabled="processing || !isFormValid"
                      :class="['w-full py-3 px-4 rounded-md font-semibold', 
                              processing || !isFormValid 
                                ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                                : 'bg-primary-600 text-white hover:bg-primary-700']">
                <span v-if="processing" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Processando...
                </span>
                <span v-else>
                  {{ getPaymentButtonText() }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

export default {
  name: 'PaymentView',
  setup() {
    const route = useRoute()
    const router = useRouter()

    // Estado
    const course = ref(null)
    const loading = ref(true)
    const processing = ref(false)
    const selectedGateway = ref('asaas')
    const selectedMethod = ref('pix')
    const selectedInstallments = ref(1)
    const installmentOptions = ref([])
    const installmentData = ref(null)

    // Resultados
    const pixResult = ref(null)
    const creditCardResult = ref(null)
    const boletoResult = ref(null)

    // Formulário do cartão
    const cardForm = ref({
      number: '',
      holderName: '',
      expiry: '',
      cvv: ''
    })

    // Computed
    const finalAmount = computed(() => {
      if (!course.value) return 0
      if (installmentData.value) {
        return installmentData.value.total_amount
      }
      return course.value.price
    })

    const isFormValid = computed(() => {
      if (selectedMethod.value === 'pix') return true
      if (selectedMethod.value === 'boleto') return true
      if (selectedMethod.value === 'credit_card') {
        return cardForm.value.number && 
               cardForm.value.holderName && 
               cardForm.value.expiry && 
               cardForm.value.cvv
      }
      return false
    })

    // Métodos
    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value || 0)
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('pt-BR')
    }

    const loadCourse = async () => {
      try {
        const courseId = route.params.courseId
        if (!courseId) {
          console.error('ID do curso não encontrado')
          router.push('/courses')
          return
        }

        const response = await api.get(`/courses/${courseId}`)
        course.value = response.data.data
      } catch (error) {
        console.error('Erro ao carregar curso:', error)
      }
    }

    const generateInstallmentOptions = () => {
      if (!course.value) return

      const options = []
      const maxInstallments = 12
      
      for (let i = 1; i <= maxInstallments; i++) {
        const installmentValue = course.value.price / i
        const hasInterest = i > 3
        const interestRate = hasInterest ? 2.99 + (i - 4) * 0.5 : 0
        const totalAmount = hasInterest ? 
          course.value.price * Math.pow(1 + interestRate / 100, i) : 
          course.value.price

        options.push({
          installments: i,
          installment_value: totalAmount / i,
          total_amount: totalAmount,
          interest_rate: interestRate,
          has_interest: hasInterest
        })
      }

      installmentOptions.value = options
      if (options.length > 0) {
        selectedInstallments.value = 1
        installmentData.value = options[0]
      }
    }

    const calculateInstallments = () => {
      const option = installmentOptions.value.find(opt => opt.installments === selectedInstallments.value)
      if (option) {
        installmentData.value = option
      }
    }

    const processPayment = async () => {
      if (!course.value) return

      processing.value = true
      pixResult.value = null
      creditCardResult.value = null
      boletoResult.value = null

      try {
        let response
        const baseData = {
          course_id: course.value.id,
          gateway: selectedGateway.value
        }

        if (selectedMethod.value === 'pix') {
          response = await api.post('/payment/pix', baseData)
          if (response.data.success) {
            pixResult.value = response.data.data
          }
        } else if (selectedMethod.value === 'credit_card') {
          const [month, year] = cardForm.value.expiry.split('/')
          const cardData = {
            ...baseData,
            installments: selectedInstallments.value,
            card_number: cardForm.value.number.replace(/\s/g, ''),
            card_holder_name: cardForm.value.holderName,
            card_expiry_month: month,
            card_expiry_year: `20${year}`,
            card_cvv: cardForm.value.cvv
          }
          
          response = await api.post('/payment/credit-card', cardData)
          if (response.data.success) {
            creditCardResult.value = response.data.data
          }
        } else if (selectedMethod.value === 'boleto') {
          response = await api.post('/payment/boleto', baseData)
          if (response.data.success) {
            boletoResult.value = response.data.data
          }
        }

        if (!response?.data?.success) {
          console.error('Erro no pagamento:', response?.data?.message)
        }
      } catch (error) {
        console.error('Erro ao processar pagamento:', error)
      } finally {
        processing.value = false
      }
    }

    const copyPixCode = () => {
      if (pixResult.value?.qr_code) {
        navigator.clipboard.writeText(pixResult.value.qr_code)
        console.log('Código PIX copiado!')
      }
    }

    const copyBarcode = () => {
      if (boletoResult.value?.barcode) {
        navigator.clipboard.writeText(boletoResult.value.barcode)
        console.log('Código do boleto copiado!')
      }
    }

    const getPaymentButtonText = () => {
      if (selectedMethod.value === 'pix') return `Gerar PIX - ${formatCurrency(finalAmount.value)}`
      if (selectedMethod.value === 'credit_card') return `Pagar ${formatCurrency(finalAmount.value)}`
      if (selectedMethod.value === 'boleto') return `Gerar Boleto - ${formatCurrency(finalAmount.value)}`
      return 'Processar Pagamento'
    }

    // Métodos de formatação
    const formatCardNumber = (event) => {
      let value = event.target.value.replace(/\D/g, '')
      value = value.replace(/(\d{4})(?=\d)/g, '$1 ')
      cardForm.value.number = value
    }

    const formatExpiry = (event) => {
      let value = event.target.value.replace(/\D/g, '')
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4)
      }
      cardForm.value.expiry = value
    }

    const formatCVV = (event) => {
      let value = event.target.value.replace(/\D/g, '')
      cardForm.value.cvv = value
    }

    // Watchers
    watch(selectedInstallments, calculateInstallments)

    // Lifecycle
    onMounted(async () => {
      loading.value = true
      await loadCourse()
      generateInstallmentOptions()
      loading.value = false
    })

    return {
      // Estado
      course,
      loading,
      processing,
      selectedGateway,
      selectedMethod,
      selectedInstallments,
      installmentOptions,
      installmentData,
      
      // Resultados
      pixResult,
      creditCardResult,
      boletoResult,
      
      // Formulário
      cardForm,
      
      // Computed
      finalAmount,
      isFormValid,
      
      // Métodos
      formatCurrency,
      formatDate,
      processPayment,
      copyPixCode,
      copyBarcode,
      getPaymentButtonText,
      calculateInstallments,
      formatCardNumber,
      formatExpiry,
      formatCVV
    }
  }
}
</script>